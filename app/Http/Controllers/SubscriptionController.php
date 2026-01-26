<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Models\Subscription;
use App\Helpers\SubscriptionHelper;
use App\Services\SubscriptionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Controller: Subscription Management
 * 
 * Controller untuk user mengelola langganan mereka.
 * Menampilkan status langganan, usage, dan opsi upgrade.
 */
class SubscriptionController extends Controller
{
    protected SubscriptionService $subscriptionService;

    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }

    /**
     * Halaman utama langganan (dashboard subscription)
     */
    public function index()
    {
        $user = Auth::user();
        $subscription = $this->subscriptionService->getActiveSubscription($user);
        $plan = $subscription?->plan ?? Plan::where('slug', 'gratis')->first();
        $usageStats = SubscriptionHelper::getUsageStats($user->id);
        $plans = Plan::aktif()->berbayar()->urut()->get();

        return view('pages.subscription.index', compact(
            'subscription',
            'plan',
            'usageStats',
            'plans'
        ));
    }

    /**
     * Halaman upgrade/pilih paket
     */
    public function upgrade(Request $request)
    {
        $plans = Plan::aktif()->berbayar()->urut()->get();
        $currentPlan = SubscriptionHelper::getPlan();
        $feature = $request->get('feature'); // Jika dari feature gate

        return view('pages.subscription.upgrade', compact('plans', 'currentPlan', 'feature'));
    }

    /**
     * Batalkan langganan
     */
    public function cancel(Request $request)
    {
        $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        try {
            $user = Auth::user();
            $this->subscriptionService->cancel($user, $request->reason);

            return redirect()->route('subscription.index')
                ->with('success', 'Langganan berhasil dibatalkan. Anda masih bisa menggunakan layanan sampai tanggal expired.');

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Aktifkan kembali langganan yang dibatalkan
     */
    public function reactivate()
    {
        try {
            $user = Auth::user();
            $this->subscriptionService->reactivate($user);

            return redirect()->route('subscription.index')
                ->with('success', 'Langganan berhasil diaktifkan kembali.');

        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
