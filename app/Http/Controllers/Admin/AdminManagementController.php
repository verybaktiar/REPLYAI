<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminUser;
use App\Models\AdminActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Controller untuk manajemen admin users.
 * Hanya Superadmin yang bisa mengakses.
 */
class AdminManagementController extends Controller
{
    /**
     * Check authorization
     */
    private function checkAuthorization(): void
    {
        $admin = Auth::guard('admin')->user();
        
        if (!$admin->isSuperAdmin()) {
            AdminActivityLog::log(
                $admin,
                'unauthorized_admin_management',
                'Attempted to access admin management without superadmin privilege',
                ['url' => request()->fullUrl()],
                null,
                9
            );
            abort(403, 'Only Superadmin can manage admin users.');
        }
    }

    /**
     * Tampilkan daftar admin users
     */
    public function index()
    {
        $this->checkAuthorization();
        $admins = AdminUser::withCount('activityLogs')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Stats
        $stats = [
            'total' => AdminUser::count(),
            'active' => AdminUser::where('is_active', true)->count(),
            'inactive' => AdminUser::where('is_active', false)->count(),
            'superadmin' => AdminUser::where('role', AdminUser::ROLE_SUPERADMIN)->count(),
            'with_2fa' => AdminUser::where('two_factor_enabled', true)->count(),
        ];

        // Recent admin activities
        $recentActivities = AdminActivityLog::with('admin')
            ->whereIn('action', ['login', 'failed_login', 'unauthorized_access_attempt'])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        return view('admin.admins.index', compact('admins', 'stats', 'recentActivities'));
    }

    /**
     * Show create form
     */
    public function create()
    {
        $this->checkAuthorization();
        return view('admin.admins.create');
    }

    /**
     * Store new admin
     */
    public function store(Request $request)
    {
        $this->checkAuthorization();
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:admin_users,email',
            'role' => ['required', Rule::in([AdminUser::ROLE_SUPERADMIN, AdminUser::ROLE_FINANCE, AdminUser::ROLE_SUPPORT])],
            'password' => 'required|min:12|confirmed',
            'is_active' => 'boolean',
        ]);

        $password = $request->password;

        $admin = AdminUser::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($password),
            'role' => $request->role,
            'is_active' => $request->boolean('is_active', true),
            'two_factor_enabled' => false,
        ]);

        // Log activity
        AdminActivityLog::log(
            Auth::guard('admin')->user(),
            'create_admin',
            "Created new admin: {$admin->name} ({$admin->role})",
            [
                'admin_id' => $admin->id,
                'email' => $admin->email,
                'role' => $admin->role,
                'created_by' => Auth::guard('admin')->id()
            ],
            $admin,
            5
        );

        return redirect()->route('admin.admins.index')
            ->with('success', "Admin {$admin->name} berhasil dibuat! Password: {$password}");
    }

    /**
     * Show edit form
     */
    public function edit(AdminUser $admin)
    {
        $this->checkAuthorization();
        // Prevent editing own account through this route (use profile instead)
        if ($admin->id === Auth::guard('admin')->id()) {
            return redirect()->route('admin.admins.index')
                ->with('info', 'Gunakan menu Profile untuk mengedit akun Anda sendiri.');
        }

        return view('admin.admins.edit', compact('admin'));
    }

    /**
     * Update admin
     */
    public function update(Request $request, AdminUser $admin)
    {
        $this->checkAuthorization();
        // Prevent modifying own account
        if ($admin->id === Auth::guard('admin')->id()) {
            return redirect()->route('admin.admins.index')
                ->with('error', 'Tidak bisa mengedit akun sendiri melalui halaman ini.');
        }

        // Prevent modifying the last superadmin
        if ($admin->role === AdminUser::ROLE_SUPERADMIN && $request->role !== AdminUser::ROLE_SUPERADMIN) {
            $superadminCount = AdminUser::where('role', AdminUser::ROLE_SUPERADMIN)->count();
            if ($superadminCount <= 1) {
                return back()->with('error', 'Tidak bisa mengubah role admin terakhir dengan role Superadmin.');
            }
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('admin_users')->ignore($admin->id)],
            'role' => ['required', Rule::in([AdminUser::ROLE_SUPERADMIN, AdminUser::ROLE_FINANCE, AdminUser::ROLE_SUPPORT])],
            'is_active' => 'boolean',
        ]);

        $oldData = $admin->only(['name', 'email', 'role', 'is_active']);

        $admin->update([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'is_active' => $request->boolean('is_active', true),
        ]);

        // Log activity
        AdminActivityLog::log(
            Auth::guard('admin')->user(),
            'update_admin',
            "Updated admin: {$admin->name}",
            [
                'admin_id' => $admin->id,
                'changes' => [
                    'before' => $oldData,
                    'after' => $admin->only(['name', 'email', 'role', 'is_active'])
                ]
            ],
            $admin,
            4
        );

        return redirect()->route('admin.admins.index')
            ->with('success', "Admin {$admin->name} berhasil diupdate!");
    }

    /**
     * Toggle admin active status
     */
    public function toggleStatus(AdminUser $admin)
    {
        $this->checkAuthorization();
        // Prevent disabling self
        if ($admin->id === Auth::guard('admin')->id()) {
            return back()->with('error', 'Tidak bisa menonaktifkan akun sendiri.');
        }

        // Prevent disabling last superadmin
        if ($admin->role === AdminUser::ROLE_SUPERADMIN && $admin->is_active) {
            $activeSuperadminCount = AdminUser::where('role', AdminUser::ROLE_SUPERADMIN)
                ->where('is_active', true)
                ->count();
            if ($activeSuperadminCount <= 1) {
                return back()->with('error', 'Tidak bisa menonaktifkan superadmin terakhir yang aktif.');
            }
        }

        $admin->is_active = !$admin->is_active;
        $admin->save();

        $action = $admin->is_active ? 'activate_admin' : 'deactivate_admin';
        $message = $admin->is_active ? 'diaktifkan' : 'dinonaktifkan';

        AdminActivityLog::log(
            Auth::guard('admin')->user(),
            $action,
            "{$message} admin: {$admin->name}",
            ['admin_id' => $admin->id, 'is_active' => $admin->is_active],
            $admin,
            $admin->is_active ? 3 : 6
        );

        return back()->with('success', "Admin {$admin->name} berhasil {$message}!");
    }

    /**
     * Reset admin password
     */
    public function resetPassword(Request $request, AdminUser $admin)
    {
        $this->checkAuthorization();
        $request->validate([
            'password' => 'required|min:12|confirmed',
        ]);

        $admin->update([
            'password' => Hash::make($request->password),
        ]);

        AdminActivityLog::log(
            Auth::guard('admin')->user(),
            'reset_admin_password',
            "Reset password for admin: {$admin->name}",
            ['admin_id' => $admin->id],
            $admin,
            5
        );

        return back()->with('success', "Password untuk {$admin->name} berhasil direset!");
    }

    /**
     * Generate random secure password
     */
    public function generatePassword()
    {
        $this->checkAuthorization();
        $password = Str::random(16);
        
        return response()->json([
            'password' => $password
        ]);
    }

    /**
     * Show admin activity history
     */
    public function activity(AdminUser $admin)
    {
        $this->checkAuthorization();
        $activities = $admin->activityLogs()
            ->orderByDesc('created_at')
            ->paginate(50);

        return view('admin.admins.activity', compact('admin', 'activities'));
    }

    /**
     * Delete admin (soft delete alternative - just disable)
     */
    public function destroy(AdminUser $admin)
    {
        $this->checkAuthorization();
        // Prevent deleting self
        if ($admin->id === Auth::guard('admin')->id()) {
            return back()->with('error', 'Tidak bisa menghapus akun sendiri.');
        }

        // Prevent deleting last superadmin
        if ($admin->role === AdminUser::ROLE_SUPERADMIN) {
            $superadminCount = AdminUser::where('role', AdminUser::ROLE_SUPERADMIN)->count();
            if ($superadminCount <= 1) {
                return back()->with('error', 'Tidak bisa menghapus superadmin terakhir.');
            }
        }

        $adminName = $admin->name;
        $adminId = $admin->id;

        $admin->delete();

        AdminActivityLog::log(
            Auth::guard('admin')->user(),
            'delete_admin',
            "Deleted admin: {$adminName}",
            ['admin_id' => $adminId],
            null,
            8
        );

        return redirect()->route('admin.admins.index')
            ->with('success', "Admin {$adminName} berhasil dihapus!");
    }
}
