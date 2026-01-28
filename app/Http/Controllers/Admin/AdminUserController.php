<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\AdminActivityLog;
use App\Models\ActivityLog;
use App\Models\WaSession;
use App\Models\WaMessage;
use App\Models\WaConversation;
use App\Models\KbArticle;
use App\Models\AutoReplyRule;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

/**
 * Controller untuk manajemen user di SuperAdmin panel.
 * 
 * Fitur:
 * - List semua users dengan search & filter
 * - Detail user dengan subscription info
 * - Toggle VIP status
 * - Assign subscription manual
 */
class AdminUserController extends Controller
{
    /**
     * Tampilkan daftar semua users
     */
    public function index(Request $request): View
    {
        $query = User::query()
            ->with(['subscription.plan'])
            ->withCount(['payments', 'kbArticles', 'autoReplyRules']);

        // Search by name or email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by plan
        if ($request->filled('plan')) {
            if ($request->plan === 'vip') {
                $query->where('is_vip', true);
            } elseif ($request->plan === 'no_subscription') {
                $query->whereDoesntHave('subscription');
            } else {
                $query->whereHas('subscription.plan', function ($q) use ($request) {
                    $q->where('slug', $request->plan);
                });
            }
        }

        // Filter by verification status
        if ($request->filled('verified')) {
            if ($request->verified === 'yes') {
                $query->whereNotNull('email_verified_at');
            } else {
                $query->whereNull('email_verified_at');
            }
        }

        // Filter by suspension status
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_suspended', false);
            } elseif ($request->status === 'suspended') {
                $query->where('is_suspended', true);
            }
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(20);
        $plans = Plan::where('is_active', true)->orderBy('sort_order')->get();

        // Extra Stats for Admin Dashboard
        $stats = [
            'total' => User::count(),
            'active_today' => User::whereDate('last_login_at', today())->count(),
            'new_this_week' => User::where('created_at', '>=', now()->subDays(7))->count(),
            'suspended' => User::where('is_suspended', true)->count(),
        ];

        return view('admin.users.index', compact('users', 'plans', 'stats'));
    }

    /**
     * Tampilkan detail user
     */
    public function show(User $user): View
    {
        $user->load(['subscription.plan', 'payments' => function ($q) {
            $q->orderBy('created_at', 'desc')->limit(10);
        }]);

        $plans = Plan::where('is_active', true)->orderBy('sort_order')->get();

        // Usage stats (from UserManagementController)
        $usageStats = [
            'kb_articles' => KbArticle::where('user_id', $user->id)->count(),
            'auto_rules' => AutoReplyRule::where('user_id', $user->id)->count(),
            'wa_sessions' => WaSession::where('user_id', $user->id)->count(),
            'wa_messages' => WaMessage::where('user_id', $user->id)->count(),
            'wa_conversations' => WaConversation::where('user_id', $user->id)->count(),
        ];

        // Recent activity (from UserManagementController)
        $activities = ActivityLog::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get();

        return view('admin.users.show', compact('user', 'plans', 'usageStats', 'activities'));
    }

    /**
     * Toggle VIP status user
     */
    public function toggleVip(User $user)
    {
        $oldStatus = $user->is_vip;
        $user->is_vip = !$user->is_vip;
        $user->save();

        // Log aktivitas
        AdminActivityLog::log(
            Auth::guard('admin')->user(),
            $user->is_vip ? 'set_vip' : 'remove_vip',
            ($user->is_vip ? 'Set VIP' : 'Remove VIP') . " untuk user {$user->name}",
            [
                'user_id' => $user->id,
                'old_status' => $oldStatus,
                'new_status' => $user->is_vip,
            ],
            $user
        );

        return back()->with('success', 
            $user->is_vip 
                ? "User {$user->name} berhasil dijadikan VIP!"
                : "Status VIP user {$user->name} berhasil dicabut."
        );
    }

    /**
     * Toggle verification status user (verify/unverify manually)
     */
    public function toggleVerify(User $user)
    {
        $wasVerified = $user->email_verified_at !== null;
        
        if ($wasVerified) {
            // Unverify user
            $user->email_verified_at = null;
            $action = 'unverify_user';
            $message = "User {$user->name} berhasil di-unverify.";
        } else {
            // Verify user
            $user->email_verified_at = now();
            $action = 'verify_user';
            $message = "User {$user->name} berhasil di-verify!";
        }
        
        $user->save();

        // Log aktivitas
        AdminActivityLog::log(
            Auth::guard('admin')->user(),
            $action,
            $message,
            [
                'user_id' => $user->id,
                'was_verified' => $wasVerified,
                'is_verified' => $user->email_verified_at !== null,
            ],
            $user
        );

        return back()->with('success', $message);
    }

    /**
     * Suspend a user
     */
    public function suspend(User $user): RedirectResponse
    {
        if ($user->is_admin) {
            return back()->with('error', 'Tidak bisa suspend admin');
        }

        $user->update(['is_suspended' => true]);

        // Log to both systems for compatibility
        AdminActivityLog::log(Auth::guard('admin')->user(), 'suspend_user', "Suspend user {$user->email}", ['user_id' => $user->id], $user);
        ActivityLogService::log(ActivityLog::ACTION_USER_SUSPENDED, "User {$user->email} suspended by admin", $user);

        return back()->with('success', "User {$user->name} telah di-suspend");
    }

    /**
     * Activate a suspended user
     */
    public function activate(User $user): RedirectResponse
    {
        $user->update(['is_suspended' => false]);

        // Log to both systems for compatibility
        AdminActivityLog::log(Auth::guard('admin')->user(), 'activate_user', "Activate user {$user->email}", ['user_id' => $user->id], $user);
        ActivityLogService::log(ActivityLog::ACTION_USER_ACTIVATED, "User {$user->email} activated by admin", $user);

        return back()->with('success', "User {$user->name} telah diaktifkan");
    }

    /**
     * Assign subscription manual ke user
     */
    public function assignSubscription(Request $request, User $user)
    {
        $request->validate([
            'plan_id' => 'required|exists:plans,id',
            'duration_months' => 'required|integer|min:1|max:24',
        ]);

        $plan = Plan::findOrFail($request->plan_id);
        
        // Cast to integer to prevent Carbon TypeError
        $durationMonths = (int) $request->duration_months;
        
        // Hitung tanggal berakhir
        $startsAt = Carbon::now();
        $expiresAt = Carbon::now()->addMonths($durationMonths);

        // Cancel subscription lama jika ada
        if ($user->subscription) {
            $user->subscription->update([
                'status' => Subscription::STATUS_CANCELED,
                'cancelled_at' => now(),
            ]);
        }

        // Buat subscription baru
        $subscription = Subscription::create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'billing_cycle' => $durationMonths >= 12 ? 'yearly' : 'monthly',
            'starts_at' => $startsAt,
            'expires_at' => $expiresAt,
            'assigned_by' => Auth::guard('admin')->id(),
        ]);

        // Log aktivitas
        AdminActivityLog::log(
            Auth::guard('admin')->user(),
            'assign_subscription',
            "Assign subscription {$plan->name} ke user {$user->name} untuk {$durationMonths} bulan",
            [
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'plan_name' => $plan->name,
                'duration_months' => $durationMonths,
                'starts_at' => $startsAt->toDateTimeString(),
                'expires_at' => $expiresAt->toDateTimeString(),
            ],
            $subscription
        );

        return back()->with('success', 
            "Subscription {$plan->name} berhasil di-assign ke {$user->name} sampai {$expiresAt->format('d M Y')}!"
        );
    }

    /**
     * Reset usage records user
     */
    public function resetUsage(User $user)
    {
        // Cek apakah UsageRecord model ada
        if (class_exists(\App\Models\UsageRecord::class)) {
            \App\Models\UsageRecord::where('user_id', $user->id)->delete();
        }

        // Log aktivitas
        AdminActivityLog::log(
            Auth::guard('admin')->user(),
            'reset_usage',
            "Reset usage records untuk user {$user->name}",
            ['user_id' => $user->id],
            $user
        );

        return back()->with('success', "Usage records untuk {$user->name} berhasil di-reset!");
    }

    public function create(): View
    {
        $plans = Plan::where('is_active', true)->orderBy('sort_order')->get();
        return view('admin.users.create', compact('plans'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'is_vip' => 'boolean',
            'plan_id' => 'nullable|exists:plans,id',
            'duration_months' => 'nullable|integer|min:1|max:24',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'is_vip' => $request->boolean('is_vip'),
            'email_verified_at' => now(), // Auto verify
        ]);

        // Assign subscription jika dipilih
        if ($request->filled('plan_id') && $request->filled('duration_months')) {
            $plan = Plan::find($request->plan_id);
            Subscription::create([
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'status' => 'active',
                'billing_cycle' => (int) $request->duration_months >= 12 ? 'yearly' : 'monthly',
                'starts_at' => now(),
                'expires_at' => now()->addMonths((int) $request->duration_months),
                'assigned_by' => Auth::guard('admin')->id(),
            ]);
        }

        // Log aktivitas
        AdminActivityLog::log(
            Auth::guard('admin')->user(),
            'create_user',
            "Membuat user baru: {$user->name} ({$user->email})",
            ['user_id' => $user->id, 'email' => $user->email],
            $user
        );

        return redirect()->route('admin.users.index')
            ->with('success', "User {$user->name} berhasil dibuat!");
    }

    public function edit(User $user): View
    {
        $plans = Plan::where('is_active', true)->orderBy('sort_order')->get();
        return view('admin.users.edit', compact('user', 'plans'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|min:8',
            'is_vip' => 'boolean',
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        $user->is_vip = $request->boolean('is_vip');
        
        if ($request->filled('password')) {
            $user->password = bcrypt($request->password);
        }
        
        $user->save();

        // Log aktivitas
        AdminActivityLog::log(
            Auth::guard('admin')->user(),
            'update_user',
            "Update user: {$user->name}",
            ['user_id' => $user->id],
            $user
        );

        return redirect()->route('admin.users.index')
            ->with('success', "User {$user->name} berhasil diupdate!");
    }

    public function destroy(User $user): RedirectResponse
    {
        $userName = $user->name;
        $userId = $user->id;

        // Log sebelum hapus
        AdminActivityLog::log(
            Auth::guard('admin')->user(),
            'delete_user',
            "Hapus user: {$userName}",
            ['user_id' => $userId, 'email' => $user->email]
        );

        // 1. Hapus Subscription
        if ($user->subscription) {
            $user->subscription->delete();
        }

        // 2. Hapus WhatsApp Devices terkait
        \App\Models\WhatsAppDevice::where('user_id', $userId)->delete();

        // 3. Hapus Business Profiles terkait
        \App\Models\BusinessProfile::where('user_id', $userId)->delete();

        // 4. Hapus data pendukung lainnya (KB Articles, Rules, etc)
        \App\Models\KbArticle::where('user_id', $userId)->delete();
        \App\Models\AutoReplyRule::where('user_id', $userId)->delete();
        \App\Models\WaConversation::where('user_id', $userId)->delete();
        \App\Models\WaMessage::where('user_id', $userId)->delete();

        // 5. Hapus Web Widget
        \App\Models\WebWidget::where('user_id', $userId)->delete();
        \App\Models\WebConversation::where('user_id', $userId)->delete();
        \App\Models\WebMessage::where('user_id', $userId)->delete();

        // 6. Akhirnya hapus User
        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', "User {$userName} dan semua datanya berhasil dihapus bersih!");
    }

    /**
     * Impersonate user (login sebagai user)
     */
    public function impersonate(User $user): RedirectResponse
    {
        // Get admin ID before switching auth
        $adminId = Auth::guard('admin')->id();
        
        // Log aktivitas
        AdminActivityLog::log(
            Auth::guard('admin')->user(),
            'impersonate_user',
            "Impersonate sebagai user: {$user->name}",
            ['user_id' => $user->id],
            $user
        );
        
        // Log to ActivityLogService as well
        ActivityLogService::log(ActivityLog::ACTION_IMPERSONATION_START, "Admin started impersonating user #{$user->id}", $user);

        // Login sebagai user
        Auth::login($user);
        
        // IMPORTANT: Set session AFTER login to prevent session regeneration from losing it
        session()->put('impersonating_from_admin', $adminId);
        session()->save();

        return redirect('/dashboard')->with('info', "Anda sekarang login sebagai {$user->name}. Klik 'Kembali ke Admin' untuk keluar.");
    }

    /**
     * Stop impersonating (kembali ke admin)
     */
    public function stopImpersonate(): RedirectResponse
    {
        if (session()->has('impersonating_from_admin')) {
            // Log to ActivityLogService 
            ActivityLogService::log(ActivityLog::ACTION_IMPERSONATION_END, 'Admin stopped impersonating user');
            
            Auth::logout();
            session()->forget('impersonating_from_admin');
        }

        return redirect()->route('admin.dashboard')->with('success', 'Kembali ke mode Admin.');
    }
}

