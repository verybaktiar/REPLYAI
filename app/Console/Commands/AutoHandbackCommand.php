<?php

namespace App\Console\Commands;

use App\Models\Conversation;
use App\Models\WaConversation;
use App\Models\WaSession;
use App\Models\TakeoverLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class AutoHandbackCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'takeover:auto-handback';

    /**
     * The console command description.
     */
    protected $description = 'Automatically hand back conversations to bot after timeout';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $session = WaSession::getDefault();
        $timeout = $session->takeover_timeout_minutes ?? 60;

        $this->info("Checking for conversations idle for more than {$timeout} minutes...");

        $waCount = $this->processWhatsApp($timeout);
        $igCount = $this->processInstagram($timeout);

        $total = $waCount + $igCount;
        
        if ($total > 0) {
            $this->info("Handed back {$total} conversations ({$waCount} WhatsApp, {$igCount} Instagram)");
            Log::info("Auto-handback: {$total} conversations returned to bot", [
                'whatsapp' => $waCount,
                'instagram' => $igCount,
                'timeout_minutes' => $timeout,
            ]);
        } else {
            $this->info("No conversations needed handback.");
        }

        return Command::SUCCESS;
    }

    /**
     * Process WhatsApp conversations
     */
    protected function processWhatsApp(int $timeout): int
    {
        $count = 0;

        $conversations = WaConversation::where('status', WaConversation::STATUS_AGENT_HANDLING)
            ->where('last_cs_reply_at', '<', now()->subMinutes($timeout))
            ->get();

        foreach ($conversations as $conv) {
            $idleMinutes = now()->diffInMinutes($conv->last_cs_reply_at);
            
            // Log before updating
            TakeoverLog::logAutoHandback(
                TakeoverLog::PLATFORM_WHATSAPP,
                $conv->phone_number,
                $conv->display_name,
                $idleMinutes,
                $conv->user_id
            );

            $conv->handback();
            $count++;

            $this->line("  - WhatsApp: {$conv->phone_number} (idle {$idleMinutes}m)");
        }

        return $count;
    }

    /**
     * Process Instagram conversations
     */
    protected function processInstagram(int $timeout): int
    {
        $count = 0;

        $conversations = Conversation::where('status', 'agent_handling')
            ->where('agent_replied_at', '<', now()->subMinutes($timeout))
            ->get();

        foreach ($conversations as $conv) {
            $idleMinutes = now()->diffInMinutes($conv->agent_replied_at);
            
            // Log before updating
            TakeoverLog::logAutoHandback(
                TakeoverLog::PLATFORM_INSTAGRAM,
                (string) $conv->id,
                $conv->display_name,
                $idleMinutes,
                $conv->user_id
            );

            $conv->update([
                'status' => 'bot_handling',
                'assigned_cs' => null,
                'takeover_at' => null,
                'agent_replied_at' => null,
            ]);
            $count++;

            $this->line("  - Instagram: {$conv->display_name} (idle {$idleMinutes}m)");
        }

        return $count;
    }
}
