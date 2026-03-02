<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

/**
 * Fix harga tahunan untuk plan Pro
 * Harga tahunan seharusnya = harga bulanan * 12 * diskon
 */
class FixPlanYearlyPricesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Plan Pro: 500rb/bulan -> 5jt/tahun (hemat 1jt)
        $pro = Plan::where('slug', 'pro')->first();
        if ($pro) {
            $pro->update([
                'price_yearly' => 5000000, // 5 juta per tahun (hemat 1jt)
                'price_yearly_original' => 6000000, // 500rb * 12 = 6jt
            ]);
            $this->command->info("✅ Updated Pro plan: Yearly = Rp 5.000.000 (save Rp 1.000.000)");
        }
        
        // Plan Business: 1.5jt/bulan -> 15jt/tahun (sudah benar)
        // Plan Enterprise: 3.5jt/bulan -> 35jt/tahun (sudah benar)
        
        $this->command->info("✅ Plan yearly prices fixed!");
        
        // Tampilkan semua harga setelah update
        $this->command->info("\n=== CURRENT PRICES ===");
        $plans = Plan::where('is_active', true)->get();
        foreach ($plans as $plan) {
            $monthly = number_format($plan->price_monthly, 0, ',', '.');
            $yearly = number_format($plan->price_yearly, 0, ',', '.');
            $savings = number_format(($plan->price_monthly * 12) - $plan->price_yearly, 0, ',', '.');
            $this->command->info("{$plan->name}: Monthly=Rp {$monthly}, Yearly=Rp {$yearly}, Savings=Rp {$savings}");
        }
    }
}
