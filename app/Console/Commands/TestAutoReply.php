<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AutoReplyEngine;
use App\Models\Conversation;

class TestAutoReply extends Command
{
    protected $signature = 'autoreply:test {conversation_id?}';
    protected $description = 'Testing Auto Reply Engine untuk 1 conversation atau semua conversation';

    public function handle(AutoReplyEngine $engine)
    {
        $convId = $this->argument('conversation_id');

        // test 1 conversation (pakai ID LOCAL conversations.id)
        if ($convId) {
            $conv = Conversation::find((int)$convId);
            if (!$conv) {
                $this->error("Conversation local id {$convId} tidak ditemukan.");
                return Command::FAILURE;
            }

            $report = $engine->runForConversation($conv);
            $this->info("Report:");
            foreach ($report as $k => $v) {
                $this->line("- {$k}: {$v}");
            }

            return Command::SUCCESS;
        }

        // test semua conversation
        $report = $engine->runForAllConversations();

        $this->info("Report All Conversations:");
        foreach ($report as $k => $v) {
            $this->line("- {$k}: {$v}");
        }

        return Command::SUCCESS;
    }
}
