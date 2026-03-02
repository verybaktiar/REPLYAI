<?php

namespace App\Http\View\Composers;

use App\Models\Payment;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

/**
 * View Composer: Pending Payment Notification
 * 
 * Menyediakan data pembayaran pending untuk ditampilkan di navbar/dashboard
 * Setiap user yang login akan melihat notifikasi jika ada pembayaran pending
 */
class PendingPaymentComposer
{
    /**
     * Bind data to the view.
     */
    public function compose(View $view): void
    {
        $user = Auth::user();
        
        if (!$user) {
            $view->with('pendingPayment', null);
            $view->with('pendingPaymentCount', 0);
            return;
        }
        
        // Cek semua pembayaran pending yang belum expired
        $pendingPayments = Payment::where('user_id', $user->id)
            ->where('status', Payment::STATUS_PENDING)
            ->where('expires_at', '>', now())
            ->with('plan')
            ->orderBy('created_at', 'desc')
            ->get();
        
        $pendingPayment = $pendingPayments->first();
        $pendingPaymentCount = $pendingPayments->count();
        
        $view->with('pendingPayment', $pendingPayment);
        $view->with('pendingPayments', $pendingPayments);
        $view->with('pendingPaymentCount', $pendingPaymentCount);
    }
}
