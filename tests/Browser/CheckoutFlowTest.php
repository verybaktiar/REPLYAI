<?php

namespace Tests\Browser;

use App\Models\Plan;
use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * Browser Test: Checkout Flow
 * 
 * Test end-to-end menggunakan browser Chrome
 * Jalankan: php artisan dusk
 */
class CheckoutFlowTest extends DuskTestCase
{
    /**
     * Test: User bisa melihat perbedaan harga bulanan vs tahunan
     */
    public function test_user_can_see_price_difference(): void
    {
        $user = User::factory()->create();
        
        $plan = Plan::create([
            'name' => 'Pro',
            'slug' => 'pro',
            'price_monthly' => 500000,
            'price_yearly' => 5000000,
            'is_active' => true,
        ]);
        
        $this->browse(function (Browser $browser) use ($user, $plan) {
            $browser->loginAs($user)
                ->visit(route('checkout.index', $plan->slug))
                ->assertSee('Rp 500.000') // Harga bulanan
                ->assertSee('Rp 5.000.000') // Harga tahunan
                ->assertSee('HEMAT'); // Badge hemat
            
            // Pilih tahunan
            $browser->radio('duration', '12')
                ->pause(500)
                ->assertSeeIn('[x-text="duration === \'12\' ? \'Rp 5.000.000\' : \'Rp 500.000\'"]', 'Rp 5.000.000');
        });
    }

    /**
     * Test: Checkout dengan durasi tahunan membuat invoice dengan harga benar
     */
    public function test_yearly_checkout_creates_correct_invoice(): void
    {
        $user = User::factory()->create();
        
        $plan = Plan::create([
            'name' => 'Pro',
            'slug' => 'pro',
            'price_monthly' => 500000,
            'price_yearly' => 5000000,
            'is_active' => true,
        ]);
        
        $this->browse(function (Browser $browser) use ($user, $plan) {
            $browser->loginAs($user)
                ->visit(route('checkout.index', $plan->slug))
                ->radio('duration', '12')
                ->press('Lanjutkan Pembayaran')
                ->waitForLocation('/payment/*')
                ->assertSee('Rp 5.000.000') // Total
                ->assertSee('12 bulan'); // Durasi
        });
    }
}
