<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ChatwootClient;
use App\Models\Conversation;
use App\Models\Message;

class SyncChatwoot extends Command
{
    protected $signature = 'sync:chatwoot {--messages : Sync messages juga}';
    protected $description = 'Sync conversations (dan optional messages) dari Chatwoot ke DB lokal';

    public function handle(ChatwootClient $chatwoot)
    {
        $this->info("Fetching conversations from Chatwoot...");

        $convs = $chatwoot->getConversationsNormalized();

        foreach ($convs as $c) {
            $conv = Conversation::updateOrCreate(
                ['chatwoot_id' => $c['id']],
                [
                    'ig_username'   => ltrim($c['display_name'] ?? '', '@'),
                    'display_name'  => $c['display_name'] ?? 'User',
                    'avatar'        => $c['avatar'] ?? null,
                    'last_message'  => $c['last_message'] ?? null,
                ]
            );

            $this->line("Synced conversation #{$c['id']}");

            // ============================================
            // ✅ SYNC MESSAGES
            // ============================================
            if ($this->option('messages')) {
                $this->line("  Fetching messages for conversation #{$c['id']}");

                $messagesData = $chatwoot->getMessagesNormalized((int)$c['id']);
                $msgs = $messagesData['messages'] ?? [];
                
                $this->line("  Got ".count($msgs)." messages");
                
                foreach ($msgs as $m) {
                    $messageType = (int) data_get($m, 'message_type', -1);

                    /**
                     * ✅ FIX UTAMA:
                     * 0 = incoming dari user IG (contact)
                     * 1 = outgoing dari agent/bot (agent)
                     */
                    $senderType = match ($messageType) {
                        0 => 'contact',
                        1 => 'agent',
                        default => 'unknown',
                    };

                    $chatwootMsgId = data_get($m, 'id');
                    if (!$chatwootMsgId) continue;

                    Message::updateOrCreate(
                        ['chatwoot_id' => $chatwootMsgId],
                        [
                            'conversation_id'     => $conv->id, // local id
                            'sender_type'         => $senderType,
                            'content'             => data_get($m, 'content') ?? '',
                            'message_created_at'  => (int) data_get($m, 'created_at'),
                        ]
                    );
                }
            }
        }

        $this->info("Done.");
        return Command::SUCCESS;
    }
}
