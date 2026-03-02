<?php

namespace App\Http\Controllers;

use App\Models\ChatAutomation;
use App\Models\Conversation;
use App\Models\WaConversation;
use App\Services\ActivityLogService;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ChatAutomationController extends Controller
{
    /**
     * Display a listing of automations.
     */
    public function index()
    {
        $automations = ChatAutomation::orderBy('type')
            ->orderByDesc('is_active')
            ->orderByDesc('created_at')
            ->get();

        $stats = [
            'total' => $automations->count(),
            'active' => $automations->where('is_active', true)->count(),
            'triggered' => $automations->sum('trigger_count'),
            'by_type' => [
                'welcome' => $automations->where('type', ChatAutomation::TYPE_WELCOME)->count(),
                'away' => $automations->where('type', ChatAutomation::TYPE_AWAY_MESSAGE)->count(),
                'keyword' => $automations->where('type', ChatAutomation::TYPE_KEYWORD)->count(),
                'follow_up' => $automations->where('type', ChatAutomation::TYPE_FOLLOW_UP)->count(),
            ]
        ];

        return view('pages.automations.index', [
            'automations' => $automations,
            'stats' => $stats,
        ]);
    }

    /**
     * Show the form for creating a new automation.
     */
    public function create()
    {
        return view('pages.automations.create');
    }

    /**
     * Store a newly created automation.
     */
    public function store(Request $request)
    {
        $validated = $this->validateAutomation($request);

        $automation = ChatAutomation::create($validated);

        ActivityLogService::logCreated(
            $automation, 
            "Membuat otomasi chat: {$automation->name} ({$automation->type})"
        );

        return response()->json([
            'ok' => true,
            'message' => 'Automation created successfully',
            'automation' => $automation,
            'redirect' => route('automations.index'),
        ]);
    }

    /**
     * Show the form for editing an automation.
     */
    public function edit(ChatAutomation $automation)
    {
        return view('pages.automations.edit', [
            'automation' => $automation,
        ]);
    }

    /**
     * Update the specified automation.
     */
    public function update(Request $request, ChatAutomation $automation)
    {
        $validated = $this->validateAutomation($request, $automation);

        $automation->update($validated);

        ActivityLogService::logUpdated(
            $automation,
            "Memperbarui otomasi chat: {$automation->name}"
        );

        return response()->json([
            'ok' => true,
            'message' => 'Automation updated successfully',
            'automation' => $automation,
        ]);
    }

    /**
     * Remove the specified automation.
     */
    public function destroy(ChatAutomation $automation)
    {
        $name = $automation->name;
        $automation->delete();

        ActivityLogService::logDeleted(
            $automation,
            "Menghapus otomasi chat: {$name}"
        );

        return response()->json([
            'ok' => true,
            'message' => 'Automation deleted successfully',
        ]);
    }

    /**
     * Toggle automation status.
     */
    public function toggleStatus(ChatAutomation $automation)
    {
        $automation->is_active = !$automation->is_active;
        $automation->save();

        $status = $automation->is_active ? 'diaktifkan' : 'dinonaktifkan';
        
        ActivityLogService::logUpdated(
            $automation,
            "Otomasi chat {$status}: {$automation->name}"
        );

        return response()->json([
            'ok' => true,
            'message' => "Automation {$status}",
            'automation' => $automation,
        ]);
    }

    /**
     * Handle welcome message for new conversations.
     */
    public function handleWelcomeMessage($conversation): ?string
    {
        $welcome = ChatAutomation::welcome()
            ->active()
            ->first();

        if (!$welcome) {
            return null;
        }

        // Check if welcome already sent
        if ($conversation instanceof Conversation && $conversation->has_sent_welcome) {
            return null;
        }

        if ($conversation instanceof WaConversation && $conversation->followup_count > 0) {
            return null;
        }

        // Send welcome message
        $this->sendAutomationMessage($conversation, $welcome->message);
        
        $welcome->incrementTriggerCount();

        // Mark welcome as sent
        if ($conversation instanceof Conversation) {
            $conversation->update(['has_sent_welcome' => true]);
        }

        return $welcome->message;
    }

    /**
     * Handle away message outside business hours.
     */
    public function handleAwayMessage($conversation): ?string
    {
        $away = ChatAutomation::awayMessage()
            ->active()
            ->first();

        if (!$away || !$away->isInAwayHours()) {
            return null;
        }

        // Check cooldown (don't send away message too frequently)
        $cacheKey = "away_sent_{$conversation->id}";
        if (cache()->has($cacheKey)) {
            return null;
        }

        $this->sendAutomationMessage($conversation, $away->message);
        $away->incrementTriggerCount();

        // Set cooldown for 1 hour
        cache()->put($cacheKey, true, now()->addHour());

        return $away->message;
    }

    /**
     * Handle keyword-based reply.
     */
    public function handleKeywordReply($conversation, string $message): ?string
    {
        $keywords = ChatAutomation::keyword()
            ->active()
            ->orderByDesc('created_at')
            ->get();

        foreach ($keywords as $automation) {
            if ($automation->matches($message)) {
                $this->sendAutomationMessage($conversation, $automation->message);
                $automation->incrementTriggerCount();
                return $automation->message;
            }
        }

        return null;
    }

    /**
     * Handle follow-up messages.
     */
    public function handleFollowUp($conversation): ?string
    {
        $followUps = ChatAutomation::followUp()
            ->active()
            ->get();

        foreach ($followUps as $automation) {
            if ($this->shouldSendFollowUp($conversation, $automation)) {
                $this->sendAutomationMessage($conversation, $automation->message);
                $automation->incrementTriggerCount();
                
                // Mark follow-up as sent
                if ($conversation instanceof WaConversation) {
                    $conversation->update([
                        'followup_sent_at' => now(),
                        'followup_count' => ($conversation->followup_count ?? 0) + 1,
                    ]);
                }

                return $automation->message;
            }
        }

        return null;
    }

    /**
     * Check if follow-up should be sent.
     */
    protected function shouldSendFollowUp($conversation, ChatAutomation $automation): bool
    {
        if (!$automation->delay_hours) {
            return false;
        }

        // Get last message time
        $lastMessageAt = null;
        
        if ($conversation instanceof WaConversation) {
            // Check if autofollowup is stopped
            if ($conversation->stop_autofollowup) {
                return false;
            }

            $lastMessageAt = $conversation->last_user_reply_at 
                ?? $conversation->created_at;
            
            // Check if already sent follow-up
            if ($conversation->followup_sent_at && 
                $conversation->followup_sent_at->gt($lastMessageAt)) {
                return false;
            }
        } elseif ($conversation instanceof Conversation) {
            $lastMessage = $conversation->messages()
                ->where('sender_type', 'contact')
                ->latest()
                ->first();
            $lastMessageAt = $lastMessage?->created_at ?? $conversation->created_at;
        }

        if (!$lastMessageAt) {
            return false;
        }

        // Check if delay hours have passed
        return now()->diffInHours($lastMessageAt) >= $automation->delay_hours;
    }

    /**
     * Send automation message to conversation.
     */
    protected function sendAutomationMessage($conversation, string $message): void
    {
        if ($conversation instanceof WaConversation) {
            $this->sendWhatsAppMessage($conversation, $message);
        } elseif ($conversation instanceof Conversation) {
            $this->sendInstagramMessage($conversation, $message);
        }
    }

    /**
     * Send WhatsApp message.
     */
    protected function sendWhatsAppMessage(WaConversation $conversation, string $message): void
    {
        try {
            $waService = app(WhatsAppService::class);
            $waService->sendMessage(
                $conversation->phone_number,
                $message,
                $conversation->user_id
            );
        } catch (\Exception $e) {
            \Log::error('Failed to send WhatsApp automation message', [
                'conversation_id' => $conversation->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send Instagram message.
     */
    protected function sendInstagramMessage(Conversation $conversation, string $message): void
    {
        // Implement Instagram message sending
        // This would use your existing Instagram messaging service
        \Log::info('Instagram automation message', [
            'conversation_id' => $conversation->id,
            'message' => $message,
        ]);
    }

    /**
     * Validate automation data.
     */
    protected function validateAutomation(Request $request, ?ChatAutomation $automation = null): array
    {
        $rules = [
            'type' => ['required', 'string', 'in:welcome,away,keyword,follow_up'],
            'name' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:2000'],
            'is_active' => ['boolean'],
        ];

        // Type-specific validation
        if ($request->input('type') === 'away') {
            $rules['away_start_time'] = ['required', 'date_format:H:i'];
            $rules['away_end_time'] = ['required', 'date_format:H:i', 'after:away_start_time'];
            $rules['away_days'] = ['required', 'array', 'min:1'];
            $rules['away_days.*'] = ['string', 'in:monday,tuesday,wednesday,thursday,friday,saturday,sunday'];
        }

        if ($request->input('type') === 'keyword') {
            $rules['keywords'] = ['required', 'array', 'min:1'];
            $rules['keywords.*'] = ['string', 'max:100'];
            $rules['match_type'] = ['required', 'string', 'in:exact,contains,starts_with,regex'];
        }

        if ($request->input('type') === 'follow_up') {
            $rules['delay_hours'] = ['required', 'integer', 'min:1', 'max:168'];
        }

        $validated = $request->validate($rules);

        // Process arrays
        if (isset($validated['away_days'])) {
            $validated['away_days'] = array_map('strtolower', $validated['away_days']);
        }

        if (isset($validated['keywords'])) {
            $validated['keywords'] = array_map('strtolower', $validated['keywords']);
        }

        $validated['is_active'] = $validated['is_active'] ?? true;

        return $validated;
    }

    /**
     * Get automation types for UI.
     */
    public function getTypes(): array
    {
        return [
            [
                'value' => ChatAutomation::TYPE_WELCOME,
                'label' => 'Welcome Message',
                'icon' => 'waving_hand',
                'description' => 'Kirim pesan otomatis saat kontak baru pertama kali chat',
            ],
            [
                'value' => ChatAutomation::TYPE_AWAY_MESSAGE,
                'label' => 'Away Message',
                'icon' => 'schedule',
                'description' => 'Balasan otomatis di luar jam kerja',
            ],
            [
                'value' => ChatAutomation::TYPE_KEYWORD,
                'label' => 'Keyword Reply',
                'icon' => 'key',
                'description' => 'Balasan otomatis berdasarkan kata kunci',
            ],
            [
                'value' => ChatAutomation::TYPE_FOLLOW_UP,
                'label' => 'Follow-up',
                'icon' => 'follow_the_signs',
                'description' => 'Kirim pesan tindak lanjut setelah X jam tanpa balasan',
            ],
        ];
    }
}
