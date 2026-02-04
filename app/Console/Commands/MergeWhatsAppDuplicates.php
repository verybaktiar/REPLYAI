<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\WaConversation;
use App\Models\WaMessage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MergeWhatsAppDuplicates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wa:merge-duplicates {--dry-run : Only show what would be merged}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Merge duplicate WhatsApp conversations (LID vs Phone Number)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting duplicate check...');
        
        $isDryRun = $this->option('dry-run');
        if ($isDryRun) {
            $this->warn('DRY RUN MODE: No changes will be made.');
        }

        // 1. Find potential LIDs (phone numbers >= 15 digits)
        $lidConversations = WaConversation::withoutGlobalScopes()
            ->whereRaw('LENGTH(phone_number) >= 15')
            ->get();

        $this->info("Found " . $lidConversations->count() . " potential LID conversations.");

        $mergedCount = 0;

        foreach ($lidConversations as $lidConv) {
            $lidPhone = $lidConv->phone_number;
            $name = $lidConv->display_name;
            $userId = $lidConv->user_id;

            if (empty($name)) {
                $this->line("Skipping {$lidPhone}: No display name to match.");
                continue;
            }

            // 2. Find target conversation (normal phone number < 15 digits)
            // matching same user_id and display_name
            $targetConv = WaConversation::withoutGlobalScopes()
                ->where('user_id', $userId)
                ->where('display_name', $name)
                ->whereRaw('LENGTH(phone_number) < 15')
                ->where('id', '!=', $lidConv->id)
                ->first();

            if ($targetConv) {
                $targetPhone = $targetConv->phone_number;
                $this->info("Match found: LID {$lidPhone} -> Target {$targetPhone} ({$name})");

                if (!$isDryRun) {
                    DB::transaction(function () use ($lidConv, $targetConv, $lidPhone, $targetPhone) {
                        // Update messages
                        $updated = WaMessage::withoutGlobalScopes()
                            ->where('phone_number', $lidPhone)
                            ->update(['phone_number' => $targetPhone]);

                        // Delete LID conversation
                        $lidConv->delete();

                        Log::info("Merged WA Duplicate: LID {$lidPhone} merged into {$targetPhone}. {$updated} messages updated.");
                    });
                    $this->info("  -> Merged successfully.");
                }
                
                $mergedCount++;
            } else {
                // $this->line("  No match found for {$lidPhone} ({$name})");
            }
        }

        $this->info("Done. Total merged: {$mergedCount}");

        return Command::SUCCESS;
    }
}
