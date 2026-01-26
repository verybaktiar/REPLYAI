<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\AdminActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

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
    public function index(Request $request)
    {
        $query = User::query()
            ->with(['subscription.plan'])
            ->withCount('payments');

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

        $users = $query->orderBy('created_at', 'desc')->paginate(20);
        $plans = Plan::where('is_active', true)->orderBy('sort_order')->get();

        return view('admin.users.index', compact('users', 'plans'));
    }

    /**
     * Tampilkan detail user
     */
    public function show(User $user)
    {
        $user->load(['subscription.plan', 'payments' => function ($q) {
            $q->orderBy('created_at', 'desc')->limit(10);
        }]);

        $plans = Plan::where('is_active', true)->orderBy('sort_order')->get();

        return view('admin.users.show', compact('user', 'plans'));
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
                'status' => 'cancelled',
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

    /**
     * Form tambah user baru
     */
    public function create()
    {
        $plans = Plan::where('is_active', true)->orderBy('sort_order')->get();
        return view('admin.users.create', compact('plans'));
    }

    /**
     * Simpan user baru
     */
    public function store(Request $request)
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

    /**
     * Form edit user
     */
    public function edit(User $user)
    {
        $plans = Plan::where('is_active', true)->orderBy('sort_order')->get();
        return view('admin.users.edit', compact('user', 'plans'));
    }

    /**
     * Update user
     */
    public function update(Request $request, User $user)
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

    /**
     * Hapus user
     */
    public function destroy(User $user)
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

        // Hapus subscription terkait
        if ($user->subscription) {
            $user->subscription->delete();
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', "User {$userName} berhasil dihapus!");
    }

    /**
     * Impersonate user (login sebagai user)
     */
    public function impersonate(User $user)
    {
        // Simpan admin ID ke session
        session(['impersonating_from_admin' => Auth::guard('admin')->id()]);
        
        // Log aktivitas
        AdminActivityLog::log(
            Auth::guard('admin')->user(),
            'impersonate_user',
            "Impersonate sebagai user: {$user->name}",
            ['user_id' => $user->id],
            $user
        );

        // Login sebagai user
        Auth::login($user);

        return redirect('/dashboard')->with('info', "Anda sekarang login sebagai {$user->name}. Klik 'Kembali ke Admin' untuk keluar.");
    }

    /**
     * Stop impersonating (kembali ke admin)
     */
    public function stopImpersonate()
    {
        if (session()->has('impersonating_from_admin')) {
            Auth::logout();
            session()->forget('impersonating_from_admin');
        }

        return redirect()->route('admin.dashboard')->with('success', 'Kembali ke mode Admin.');
    }
}

