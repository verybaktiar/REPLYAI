<?php

namespace App\Services;

use App\Models\CsatRating;
use App\Models\User;

class CsatService
{
    /**
     * CSAT Message Templates
     */
    protected array $templates = [
        'id' => [
            'question' => "Bagaimana pelayanan kami? 😊\n\nBalas dengan angka 1-5:\n⭐ 1 = Sangat Buruk\n⭐⭐ 2 = Buruk\n⭐⭐⭐ 3 = Cukup\n⭐⭐⭐⭐ 4 = Baik\n⭐⭐⭐⭐⭐ 5 = Sangat Baik",
            'thanks' => "Terima kasih atas penilaian Anda! 🙏\nMasukan Anda sangat berarti untuk kami.",
            'thanks_low' => "Terima kasih atas masukan Anda. Kami akan berusaha lebih baik lagi. 🙏",
        ],
        'en' => [
            'question' => "How was our service? 😊\n\nReply with 1-5:\n⭐ 1 = Very Poor\n⭐⭐ 2 = Poor\n⭐⭐⭐ 3 = Average\n⭐⭐⭐⭐ 4 = Good\n⭐⭐⭐⭐⭐ 5 = Excellent",
            'thanks' => "Thank you for your feedback! 🙏\nYour input means a lot to us.",
            'thanks_low' => "Thank you for your feedback. We will strive to do better. 🙏",
        ],
    ];

    /**
     * Get CSAT question message
     */
    public function getQuestionMessage(string $lang = 'id'): string
    {
        return $this->templates[$lang]['question'] ?? $this->templates['id']['question'];
    }

    /**
     * Get thanks message based on rating
     */
    public function getThanksMessage(int $rating, string $lang = 'id'): string
    {
        $templates = $this->templates[$lang] ?? $this->templates['id'];
        return $rating >= 4 ? $templates['thanks'] : $templates['thanks_low'];
    }

    /**
     * Check if a message is a CSAT response (1-5)
     */
    public function isRatingResponse(string $message): ?int
    {
        $message = trim($message);
        
        // Check for direct numbers
        if (preg_match('/^[1-5]$/', $message)) {
            return (int) $message;
        }

        // Check for emoji stars count
        if (preg_match('/^(⭐+)$/', $message, $matches)) {
            $count = mb_strlen($matches[1]) / mb_strlen('⭐');
            if ($count >= 1 && $count <= 5) {
                return (int) $count;
            }
        }

        return null;
    }

    /**
     * Create or update a CSAT rating request
     */
    public function createRequest(
        int $userId,
        string $platform,
        string $contactIdentifier,
        ?string $contactName = null,
        ?int $conversationId = null,
        ?int $waConversationId = null,
        string $handledBy = 'bot'
    ): CsatRating {
        return CsatRating::create([
            'user_id' => $userId,
            'platform' => $platform,
            'conversation_id' => $conversationId,
            'wa_conversation_id' => $waConversationId,
            'contact_identifier' => $contactIdentifier,
            'contact_name' => $contactName,
            'handled_by' => $handledBy,
            'requested_at' => now(),
        ]);
    }

    /**
     * Record a CSAT response
     */
    public function recordResponse(
        int $userId,
        string $platform,
        string $contactIdentifier,
        int $rating,
        ?string $feedback = null
    ): ?CsatRating {
        // Find the most recent pending CSAT request for this contact
        $csatRating = CsatRating::where('user_id', $userId)
            ->where('platform', $platform)
            ->where('contact_identifier', $contactIdentifier)
            ->whereNull('rating')
            ->whereNotNull('requested_at')
            ->where('requested_at', '>=', now()->subHours(24)) // Valid for 24 hours
            ->orderBy('requested_at', 'desc')
            ->first();

        if (!$csatRating) {
            return null;
        }

        $csatRating->update([
            'rating' => $rating,
            'feedback' => $feedback,
            'responded_at' => now(),
        ]);

        return $csatRating;
    }

    /**
     * Check if CSAT was recently requested for a contact
     */
    public function wasRecentlyRequested(
        int $userId,
        string $platform,
        string $contactIdentifier,
        int $hoursThreshold = 24
    ): bool {
        return CsatRating::where('user_id', $userId)
            ->where('platform', $platform)
            ->where('contact_identifier', $contactIdentifier)
            ->where('requested_at', '>=', now()->subHours($hoursThreshold))
            ->exists();
    }

    /**
     * Get analytics summary for a user
     */
    public function getAnalytics(int $userId, int $days = 30): array
    {
        return [
            'average_rating' => CsatRating::averageForUser($userId, null, $days),
            'average_instagram' => CsatRating::averageForUser($userId, 'instagram', $days),
            'average_whatsapp' => CsatRating::averageForUser($userId, 'whatsapp', $days),
            'distribution' => CsatRating::distributionForUser($userId, null, $days),
            'total_responses' => CsatRating::where('user_id', $userId)
                ->whereNotNull('rating')
                ->where('created_at', '>=', now()->subDays($days))
                ->count(),
            'response_rate' => $this->getResponseRate($userId, $days),
        ];
    }

    /**
     * Get response rate (% of requests that got a response)
     */
    protected function getResponseRate(int $userId, int $days = 30): float
    {
        $totalRequests = CsatRating::where('user_id', $userId)
            ->whereNotNull('requested_at')
            ->where('created_at', '>=', now()->subDays($days))
            ->count();

        if ($totalRequests === 0) {
            return 0;
        }

        $responses = CsatRating::where('user_id', $userId)
            ->whereNotNull('rating')
            ->where('created_at', '>=', now()->subDays($days))
            ->count();

        return round(($responses / $totalRequests) * 100, 1);
    }
}
