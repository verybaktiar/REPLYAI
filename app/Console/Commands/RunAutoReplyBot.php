<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Message;
use App\Services\AutoReplyEngine;


class RunAutoReplyBot extends Command
{
    protected $signature = 'bot:autoreply';
    protected $description = 'Check new incoming messages and auto reply based on rules';

    public function handle(AutoReplyEngine $engine,)
    {
        $incomingMessages = Message::query()
            ->where('sender_type', 'contact')
            ->where('is_replied_by_bot', false)
            ->orderBy('message_created_at', 'asc')
            ->limit(50)
            ->get();

        if ($incomingMessages->isEmpty()) {
            $this->info("No new incoming messages.");
            return Command::SUCCESS;
        }

        foreach ($incomingMessages as $msg) {
            $rule = $engine->matchRule($msg->content ?? '', null);

            if (!$rule) {
                $msg->is_replied_by_bot = true;
                $msg->save();
                continue;
            }

            $this->line("Matched rule #{$rule->id} for message #{$msg->id}");

            $chatwoot->sendMessage(
                (int)$msg->conversation->chatwoot_id,
                $rule->reply
            );

            $msg->is_replied_by_bot = true;
            $msg->save();
        }

        $this->info("Auto reply done.");
        return Command::SUCCESS;
    }
}
