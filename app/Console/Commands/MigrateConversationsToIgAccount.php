<?php

namespace App\Console\Commands;

use App\Models\Conversation;
use App\Models\InstagramAccount;
use Illuminate\Console\Command;

class MigrateConversationsToIgAccount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'conversations:migrate-to-ig-account 
                            {--user= : ID user spesifik (opsional)}
                            {--dry-run : Hanya tampilkan apa yang akan diubah, tanpa mengubah}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrasi conversation lama yang tidak memiliki instagram_account_id ke akun IG aktif user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $specificUserId = $this->option('user');
        
        if ($dryRun) {
            $this->info('🔍 MODE DRY-RUN: Hanya menampilkan perubahan, tidak menyimpan');
        }
        
        // Ambil conversations yang belum punya instagram_account_id
        $query = Conversation::whereNull('instagram_account_id')
            ->whereNotNull('user_id');
            
        if ($specificUserId) {
            $query->where('user_id', $specificUserId);
        }
        
        $orphanConversations = $query->get();
        
        if ($orphanConversations->isEmpty()) {
            $this->info('✅ Tidak ada conversation yang perlu dimigrasi');
            return 0;
        }
        
        $this->info("📋 Ditemukan {$orphanConversations->count()} conversation tanpa instagram_account_id");
        
        // Group by user_id
        $grouped = $orphanConversations->groupBy('user_id');
        
        $migratedCount = 0;
        $skippedCount = 0;
        
        foreach ($grouped as $userId => $conversations) {
            // Cari akun IG aktif untuk user ini
            $igAccount = InstagramAccount::where('user_id', $userId)
                ->where('is_active', true)
                ->first();
            
            if (!$igAccount) {
                $this->warn("⚠️  User ID {$userId}: Tidak ada akun IG aktif, {$conversations->count()} conversation dilewati");
                $skippedCount += $conversations->count();
                continue;
            }
            
            $this->info("👤 User ID {$userId}: Migrasi {$conversations->count()} conversation ke IG Account ID {$igAccount->id} ({$igAccount->username})");
            
            if (!$dryRun) {
                // Update semua conversation untuk user ini
                Conversation::whereIn('id', $conversations->pluck('id'))
                    ->update(['instagram_account_id' => $igAccount->id]);
            }
            
            $migratedCount += $conversations->count();
            
            // Tampilkan detail per conversation
            foreach ($conversations as $conv) {
                $this->line("   - Conv #{$conv->id}: {$conv->display_name} (@{$conv->ig_username})");
            }
        }
        
        $this->newLine();
        $this->info("📊 Ringkasan:");
        $this->info("   ✅ Dimigrasi: {$migratedCount}");
        $this->info("   ⏭️  Dilewati: {$skippedCount}");
        
        if ($dryRun && $migratedCount > 0) {
            $this->newLine();
            $this->warn("Jalankan tanpa --dry-run untuk menerapkan perubahan:");
            $this->line("   php artisan conversations:migrate-to-ig-account");
        }
        
        return 0;
    }
}
