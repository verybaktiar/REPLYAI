<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Refund;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminRefundController extends Controller
{
    private function checkAuthorization(): void
    {
        $admin = Auth::guard('admin')->user();
        
        if (!$admin->canManagePayments()) {
            AdminActivityLog::log(
                $admin,
                'unauthorized_refund_access',
                'Attempted to access refunds without authorization',
                ['url' => request()->fullUrl()],
                null,
                8
            );
            abort(403, 'Only Finance and Superadmin can manage refunds.');
        }
    }

    public function index()
    {
        $this->checkAuthorization();
        return view('admin.refunds.index');
    }

    public function approve(Refund $refund)
    {
        $this->checkAuthorization();
        
        if ($refund->status !== 'pending') {
            return back()->with('error', 'Refund ini sudah diproses sebelumnya.');
        }

        $refund->update([
            'status' => 'approved',
            'processed_by' => Auth::guard('admin')->id(),
            'processed_at' => now(),
        ]);

        // Log activity
        AdminActivityLog::log(
            Auth::guard('admin')->user(),
            'approve_refund',
            "Approved refund #{$refund->id} for user {$refund->user->name}",
            ['refund_id' => $refund->id, 'amount' => $refund->amount],
            $refund
        );

        return back()->with('success', 'Refund approved!');
    }

    public function reject(Refund $refund)
    {
        $this->checkAuthorization();
        
        if ($refund->status !== 'pending') {
            return back()->with('error', 'Refund ini sudah diproses sebelumnya.');
        }

        $refund->update([
            'status' => 'rejected',
            'processed_by' => Auth::guard('admin')->id(),
            'processed_at' => now(),
        ]);

        // Log activity
        AdminActivityLog::log(
            Auth::guard('admin')->user(),
            'reject_refund',
            "Rejected refund #{$refund->id} for user {$refund->user->name}",
            ['refund_id' => $refund->id, 'amount' => $refund->amount],
            $refund
        );

        return back()->with('success', 'Refund rejected');
    }
}
