<?php

namespace App\Console\Commands;

use App\Http\Controllers\ChatAutomationController;
use App\Models\ChatAutomation;
use App\Models\WaConversation;
use App\Models\Conversation;
use Illuminate\Console\Command;

class CheckFollowUps extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'automations:check-followups 
                            {--platform=all : Platform to check (whatsapp, instagram, all)}
                            {--dry-run : Show what would be sent without actually sending}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and send follow-up messages for inactive conversations';

    /**
     * Execute the console command.
     */
    public function handle(ChatAutomationController $controller)
    {
        $this->info('Checking follow-up automations...');

        $platform = $this->option('platform');
        $dryRun = $this->option('dry-run');

        // Get active follow-up automations
        $followUps = ChatAutomation::followUp()
            ->active()
            ->get();

        if ($followUps->isEmpty()) {
            $this->warn('No active follow-up automations found.');
            return 0;
        }

        $this->info("Found {$followUps->count()} follow-up automation(s)");

        $totalSent = 0;

        // Check WhatsApp conversations
        if (in_array($platform, ['whatsapp', 'all'])) {
            $totalSent += $this->processWhatsAppConversations($controller, $followUps, $dryRun);
        }

        // Check Instagram conversations
        if (in_array($platform, ['instagram', 'all'])) {
            $totalSent += $this->processInstagramConversations($controller, $followUps, $dryRun);
        }

        if ($dryRun) {
            $this->info("Dry run completed. Would have sent {$totalSent} follow-up(s).");
        } else {
            $this->info("Follow-up check completed. Sent {$totalSent} message(s).");
        }

        return 0;
    }

    /**
     * Process WhatsApp conversations for follow-ups.
     */
    protected function processWhatsAppConversations(
        ChatAutomationController $controller, 
        $followUps, 
        bool $dryRun
    ): int {
        $this->info('Checking WhatsApp conversations...');

        $sent = 0;

        foreach ($followUps as $automation) {
            if (!$automation->delay_hours) {
                continue;
            }

            // Find conversations that need follow-up
            $conversations = WaConversation::where('stop_autofollowup', false)
                ->where(function ($query) use ($automation) {
                    $query->whereNull('followup_sent_at')
                        ->orWhereColumn('followup_sent_at', '<', 'last_user_reply_at');
                })
                ->where(function ($query) use ($automation) {
                    $query->whereNotNull('last_user_reply_at')
                        ->where('last_user_reply_at', '<=', now()->subHours($automation->delay_hours));
                })
                ->orWhere(function ($query) use ($automation) {
                    $query->whereNull('last_user_reply_at')
                        ->where('created_at', '<=', now()->subHours($automation->delay_hours));
                })
                ->get();

            foreach ($conversations as $conversation) {
                if ($dryRun) {
                    $this->line("  [DRY RUN] Would send follow-up to {$conversation->phone_number}");
                } else {
                    $result = $controller->handleFollowUp($conversation);
                    if ($result) {
                        $this->info("  ✓ Sent follow-up to {$conversation->phone_number}");
                        $sent++;
                    }
                }
            }
        }

        return $sent;
    }

    /**
     * Process Instagram conversations for follow-ups.
     */
    protected function processInstagramConversations(
        ChatAutomationController $controller, 
        $followUps, 
        bool $dryRun
    ): int {
        $this->info('Checking Instagram conversations...');

        $sent = 0;

        foreach ($followUps as $automation) {
            if (!$automation->delay_hours) {
                continue;
            }

            // Find conversations that need follow-up
            $conversations = Conversation::whereHas('messages', function ($query) use ($automation) {
                $query->where('sender_type', 'contact')
                    ->where('created_at', '<=', now()->subHours($automation->delay_hours));
            })->get();

            foreach ($conversations as $conversation) {
                if ($dryRun) {
                    $this->line("  [DRY RUN] Would send follow-up to {$conversation->ig_username}");
                } else {
                    $result = $controller->handleFollowUp($conversation);
                    if ($result) {
                        $this->info("  ✓ Sent follow-up to {$conversation->ig_username}");
                        $sent++;
                    }
                }
            }
        }

        return $sent;
    }
}
