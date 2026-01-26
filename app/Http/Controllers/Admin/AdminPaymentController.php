<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\AdminActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminPaymentController extends Controller
{
    /**
     * Tampilkan daftar payments
     */
    public function index(Request $request)
    {
        $status = $request->get('status', 'pending');
        
        $query = Payment::with(['user', 'plan'])
            ->orderBy('created_at', 'desc');

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        $payments = $query->paginate(20);

        // Stats untuk badges
        $stats = [
            'pending' => Payment::where('status', 'pending')->count(),
            'paid' => Payment::where('status', 'paid')->count(),
            'rejected' => Payment::where('status', 'rejected')->count(),
        ];

        return view('admin.payments.index', compact('payments', 'stats', 'status'));
    }

    /**
     * Approve payment dan aktivasi subscription
     */
    public function approve(Request $request, Payment $payment)
    {
        if ($payment->status !== 'pending') {
            return back()->with('error', 'Payment ini sudah diproses sebelumnya.');
        }

        try {
            DB::beginTransaction();

            // Update payment status
            $payment->update([
                'status' => 'paid',
                'paid_at' => now(),
                'verified_by' => Auth::guard('admin')->id(),
                'verified_at' => now(),
            ]);

            // Hitung tanggal subscription
            $startDate = now();
            $endDate = $startDate->copy()->addMonths($payment->duration_months);

            // Cek apakah user sudah punya subscription
            $subscription = Subscription::where('user_id', $payment->user_id)->first();

            if ($subscription) {
                // Update subscription yang ada
                $subscription->update([
                    'plan_id' => $payment->plan_id,
                    'status' => 'active',
                    'starts_at' => $startDate,      // Fixed: correct column name
                    'expires_at' => $endDate,       // Fixed: correct column name
                    'grace_period_ends_at' => null,
                ]);
            } else {
                // Buat subscription baru
                $subscription = Subscription::create([
                    'user_id' => $payment->user_id,
                    'plan_id' => $payment->plan_id,
                    'status' => 'active',
                    'starts_at' => $startDate,      // Fixed: correct column name
                    'expires_at' => $endDate,       // Fixed: correct column name
                ]);
            }

            // Log aktivitas
            AdminActivityLog::log(
                Auth::guard('admin')->user(),
                'approve_payment',
                "Approve payment {$payment->invoice_number} untuk user {$payment->user->name}",
                [
                    'payment_id' => $payment->id,
                    'invoice_number' => $payment->invoice_number,
                    'amount' => $payment->total,
                    'subscription_id' => $subscription->id,
                ],
                $payment
            );

            DB::commit();

            return back()->with('success', 'Payment berhasil di-approve dan subscription telah diaktifkan!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Reject payment
     */
    public function reject(Request $request, Payment $payment)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        if ($payment->status !== 'pending') {
            return back()->with('error', 'Payment ini sudah diproses sebelumnya.');
        }

        try {
            $payment->update([
                'status' => 'rejected',
                'admin_notes' => $request->reason,
                'verified_by' => Auth::guard('admin')->id(),
                'verified_at' => now(),
            ]);

            // Log aktivitas
            AdminActivityLog::log(
                Auth::guard('admin')->user(),
                'reject_payment',
                "Reject payment {$payment->invoice_number} untuk user {$payment->user->name}",
                [
                    'payment_id' => $payment->id,
                    'invoice_number' => $payment->invoice_number,
                    'reason' => $request->reason,
                ],
                $payment
            );

            return back()->with('success', 'Payment berhasil di-reject.');

        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
