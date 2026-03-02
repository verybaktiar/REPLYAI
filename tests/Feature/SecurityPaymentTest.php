<?php

namespace Tests\Feature;

use App\Models\Payment;
use App\Models\Plan;
use App\Models\User;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Security Tests: Payment Vulnerabilities
 * 
 * CRITICAL-001: Price Manipulation
 * CRITICAL-002: Multiple Pending Payments
 * CRITICAL-003: Midtrans Callback Bypass
 */
class SecurityPaymentTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Plan $plan;
    protected PaymentService $paymentService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        $this->plan = Plan::create([
            'name' => 'Pro',
            'slug' => 'pro',
            'price_monthly' => 500000,
            'price_yearly' => 5000000,
            'is_active' => true,
        ]);
        $this->paymentService = app(PaymentService::class);
    }

    // ==========================================
    // CRITICAL-001: Price Manipulation Tests
    // ==========================================

    /**
     * Test: Harga tidak bisa dimanipulasi via request
     * Backend harus selalu recalculate dari database
     */
    public function test_price_cannot_be_manipulated(): void
    {
        $this->actingAs($this->user);
        
        // Coba submit dengan manipulasi harga (meskipun form tidak kirim harga)
        // Backend harus tetap pakai harga dari database
        $response = $this->post(route('checkout.process', $this->plan->slug), [
            'duration' => '12',
            'promo_code' => null,
        ]);
        
        $response->assertRedirect();
        
        // Cek database - harga harus dari database, bukan dari request
        $this->assertDatabaseHas('payments', [
            'user_id' => $this->user->id,
            'amount' => 5000000, // Harga tahunan dari DB
            'total' => 5000000,
        ]);
    }

    /**
     * Test: Plan dengan price_yearly = 0 atau null tidak bisa dibeli tahunan
     */
    public function test_cannot_buy_yearly_if_no_yearly_price(): void
    {
        $planNoYearly = Plan::create([
            'name' => 'Basic',
            'slug' => 'basic',
            'price_monthly' => 100000,
            'price_yearly' => 0, // Tidak ada harga tahunan
            'is_active' => true,
        ]);
        
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Harga plan tidak valid');
        
        $this->paymentService->createPayment(
            $this->user,
            $planNoYearly,
            12,
            null
        );
    }

    // ==========================================
    // CRITICAL-002: Multiple Pending Payments
    // ==========================================

    /**
     * Test: Tidak bisa buat multiple pending payments untuk plan yang sama
     */
    public function test_cannot_create_multiple_pending_payments(): void
    {
        // Buat payment pertama
        $payment1 = $this->paymentService->createPayment(
            $this->user,
            $this->plan,
            1,
            null
        );
        
        // Coba buat payment kedua untuk plan yang sama
        $payment2 = $this->paymentService->createPayment(
            $this->user,
            $this->plan,
            12, // Durasi berbeda juga tidak boleh
            null
        );
        
        // Harus return payment yang sama
        $this->assertEquals($payment1->id, $payment2->id);
        
        // Cek hanya ada 1 payment di database
        $this->assertEquals(1, Payment::where('user_id', $this->user->id)->count());
    }

    /**
     * Test: Existing pending payment di-update expires_at saat return
     */
    public function test_existing_payment_expires_at_is_updated(): void
    {
        $payment1 = $this->paymentService->createPayment(
            $this->user,
            $this->plan,
            1,
            null
        );
        
        $oldExpiresAt = $payment1->expires_at;
        
        // Tunggu 1 detik
        sleep(1);
        
        // Coba buat lagi
        $payment2 = $this->paymentService->createPayment(
            $this->user,
            $this->plan,
            1,
            null
        );
        
        // Expires_at harus di-update
        $this->assertNotEquals($oldExpiresAt, $payment2->fresh()->expires_at);
    }

    // ==========================================
    // CRITICAL-003: Midtrans Callback Security
    // ==========================================

    /**
     * Test: Midtrans callback tanpa signature harus ditolak
     */
    public function test_midtrans_webhook_rejects_invalid_signature(): void
    {
        $response = $this->postJson('/api/midtrans/notification', [
            'order_id' => 'INV-2026-00001',
            'status_code' => '200',
            'gross_amount' => '5000000',
            'signature_key' => 'invalid_signature',
        ]);
        
        $response->assertStatus(403);
        $response->assertJson(['status' => 'error']);
    }

    /**
     * Test: Midtrans callback dengan amount mismatch harus ditolak
     */
    public function test_midtrans_webhook_rejects_amount_mismatch(): void
    {
        $payment = $this->paymentService->createPayment(
            $this->user,
            $this->plan,
            12,
            null
        );
        
        $serverKey = config('services.midtrans.server_key');
        $orderId = $payment->invoice_number;
        $statusCode = '200';
        $grossAmount = '1000000'; // Amount salah!
        $signature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);
        
        $response = $this->postJson('/api/midtrans/notification', [
            'order_id' => $orderId,
            'status_code' => $statusCode,
            'gross_amount' => $grossAmount,
            'signature_key' => $signature,
            'transaction_status' => 'settlement',
        ]);
        
        $response->assertStatus(400);
    }

    // ==========================================
    // General Security Tests
    // ==========================================

    /**
     * Test: User tidak bisa lihat payment user lain
     */
    public function test_user_cannot_view_other_user_payment(): void
    {
        $user2 = User::factory()->create();
        
        $payment = $this->paymentService->createPayment(
            $this->user,
            $this->plan,
            1,
            null
        );
        
        $this->actingAs($user2);
        
        $response = $this->get(route('checkout.payment', $payment->invoice_number));
        
        $response->assertStatus(404); // Atau 403
    }

    /**
     * Test: Payment yang sudah paid tidak bisa di-edit
     */
    public function test_paid_payment_cannot_be_modified(): void
    {
        $payment = $this->paymentService->createPayment(
            $this->user,
            $this->plan,
            1,
            null
        );
        
        // Mark as paid
        $payment->update(['status' => Payment::STATUS_PAID, 'paid_at' => now()]);
        
        // Coba upload proof ke payment yang sudah paid
        $this->actingAs($this->user);
        
        $response = $this->post(route('checkout.upload-proof', $payment), [
            'proof' => null,
        ]);
        
        $response->assertRedirect();
        $response->assertSessionHas('error');
    }
}
