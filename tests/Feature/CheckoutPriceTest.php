<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\User;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test: Checkout Price Calculation
 * 
 * Memastikan harga tahunan dihitung dengan benar
 */
class CheckoutPriceTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Plan $proPlan;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        
        // Create Pro Plan dengan harga yang benar
        $this->proPlan = Plan::create([
            'name' => 'Pro',
            'slug' => 'pro',
            'price_monthly' => 500000,
            'price_yearly' => 5000000, // 5jt, bukan 500rb!
            'is_active' => true,
        ]);
    }

    /**
     * Test: Harga bulanan plan Pro
     */
    public function test_monthly_price_calculation(): void
    {
        $paymentService = app(PaymentService::class);
        
        $payment = $paymentService->createPayment(
            $this->user,
            $this->proPlan,
            1, // 1 bulan
            null
        );
        
        $this->assertEquals(500000, $payment->amount);
        $this->assertEquals(500000, $payment->total);
    }

    /**
     * Test: Harga tahunan plan Pro
     */
    public function test_yearly_price_calculation(): void
    {
        $paymentService = app(PaymentService::class);
        
        $payment = $paymentService->createPayment(
            $this->user,
            $this->proPlan,
            12, // 12 bulan
            null
        );
        
        // Harga tahunan harus 5.000.000, bukan 500.000
        $this->assertEquals(5000000, $payment->amount);
        $this->assertEquals(5000000, $payment->total);
        
        // Hemat 1.000.000 dari harga bulanan x 12 (6.000.000)
        $savings = (500000 * 12) - 5000000;
        $this->assertEquals(1000000, $savings);
    }

    /**
     * Test: Checkout form dengan durasi tahunan
     */
    public function test_checkout_form_submits_yearly_duration(): void
    {
        $this->actingAs($this->user);
        
        $response = $this->post(route('checkout.process', $this->proPlan->slug), [
            'duration' => '12',
            'promo_code' => null,
            'payment_method' => 'manual',
        ]);
        
        $response->assertRedirect();
        
        // Cek payment di database
        $this->assertDatabaseHas('payments', [
            'user_id' => $this->user->id,
            'plan_id' => $this->proPlan->id,
            'amount' => 5000000,
            'total' => 5000000,
            'duration_months' => 12,
        ]);
    }

    /**
     * Test: Harga tahunan tidak sama dengan harga bulanan
     */
    public function test_yearly_price_not_equal_to_monthly(): void
    {
        $this->assertNotEquals(
            $this->proPlan->price_monthly,
            $this->proPlan->price_yearly,
            'Harga tahunan tidak boleh sama dengan harga bulanan'
        );
        
        // Harga tahunan harus lebih besar dari harga bulanan
        $this->assertGreaterThan(
            $this->proPlan->price_monthly,
            $this->proPlan->price_yearly,
            'Harga tahunan harus lebih besar dari harga bulanan'
        );
    }
}
