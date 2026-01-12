<?php

namespace App\Services;

use App\Models\Sequence;
use App\Models\SequenceStep;
use App\Models\SequenceEnrollment;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

/**
 * SequenceService
 * 
 * Service untuk mengelola logika bisnis Sequence/Drip Campaign.
 * Menangani enrollment, pengiriman pesan, dan proses otomatis.
 */
class SequenceService
{
    protected WhatsAppService $whatsappService;

    public function __construct(WhatsAppService $whatsappService)
    {
        $this->whatsappService = $whatsappService;
    }

    /**
     * Daftarkan kontak ke dalam sequence
     * 
     * @param Sequence $sequence - Sequence yang akan di-enroll
     * @param string $contactIdentifier - ID kontak (phone/IG ID/web session)
     * @param string $platform - Platform asal (whatsapp/instagram/web)
     * @param string|null $contactName - Nama kontak (opsional)
     * @return SequenceEnrollment|null
     */
    public function enrollContact(
        Sequence $sequence, 
        string $contactIdentifier, 
        string $platform,
        ?string $contactName = null
    ): ?SequenceEnrollment {
        // Cek apakah sequence aktif
        if (!$sequence->is_active) {
            Log::info("Sequence {$sequence->id} tidak aktif, skip enrollment");
            return null;
        }

        // Cek apakah sudah pernah di-enroll dan masih aktif
        $existingEnrollment = SequenceEnrollment::where('sequence_id', $sequence->id)
            ->where('contact_identifier', $contactIdentifier)
            ->where('platform', $platform)
            ->whereIn('status', ['active', 'paused'])
            ->first();

        if ($existingEnrollment) {
            Log::info("Kontak {$contactIdentifier} sudah terdaftar di sequence {$sequence->id}");
            return $existingEnrollment;
        }

        // Ambil step pertama
        $firstStep = $sequence->getFirstStep();
        if (!$firstStep) {
            Log::warning("Sequence {$sequence->id} tidak memiliki step aktif");
            return null;
        }

        // Buat enrollment baru
        $enrollment = SequenceEnrollment::create([
            'sequence_id' => $sequence->id,
            'contact_identifier' => $contactIdentifier,
            'contact_name' => $contactName,
            'platform' => $platform,
            'current_step_id' => $firstStep->id,
            'status' => 'active',
            'enrolled_at' => now(),
            'next_run_at' => now()->addSeconds($firstStep->getDelayInSeconds()),
        ]);

        // Update statistik
        $sequence->incrementEnrolled();

        Log::info("Kontak {$contactIdentifier} berhasil di-enroll ke sequence {$sequence->name}");

        return $enrollment;
    }

    /**
     * Proses satu enrollment (kirim pesan dan jadwalkan selanjutnya)
     * Dipanggil oleh scheduler setiap menit
     */
    public function processEnrollment(SequenceEnrollment $enrollment): bool
    {
        $step = $enrollment->currentStep;
        
        if (!$step) {
            Log::warning("Enrollment {$enrollment->id} tidak memiliki current step");
            $enrollment->markAsCompleted();
            return false;
        }

        // Kirim pesan
        $sent = $this->sendSequenceMessage($step, $enrollment);

        if (!$sent) {
            Log::error("Gagal mengirim pesan sequence untuk enrollment {$enrollment->id}");
            // Tidak advance ke step berikutnya jika gagal
            // Coba lagi di run berikutnya dengan delay 5 menit
            $enrollment->update(['next_run_at' => now()->addMinutes(5)]);
            return false;
        }

        // Pindah ke step berikutnya
        $hasNext = $enrollment->advanceToNextStep();

        if (!$hasNext) {
            Log::info("Enrollment {$enrollment->id} telah menyelesaikan semua step");
        }

        return true;
    }

    /**
     * Kirim pesan sequence ke kontak
     */
    public function sendSequenceMessage(SequenceStep $step, SequenceEnrollment $enrollment): bool
    {
        $message = $step->message_content;
        $platform = $enrollment->platform;
        $contactId = $enrollment->contact_identifier;

        try {
            switch ($platform) {
                case 'whatsapp':
                    return $this->sendWhatsAppMessage($contactId, $message);
                
                case 'instagram':
                    return $this->sendInstagramMessage($contactId, $message);
                
                case 'web':
                    return $this->sendWebMessage($contactId, $message, $enrollment);
                
                default:
                    Log::error("Platform tidak dikenal: {$platform}");
                    return false;
            }
        } catch (\Exception $e) {
            Log::error("Error mengirim sequence message: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Kirim pesan via WhatsApp
     */
    protected function sendWhatsAppMessage(string $phone, string $message): bool
    {
        try {
            $result = $this->whatsappService->sendMessage($phone, $message);
            return $result['success'] ?? false;
        } catch (\Exception $e) {
            Log::error("WhatsApp send error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Kirim pesan via Instagram
     * TODO: Implementasi sesuai dengan Instagram API yang digunakan
     */
    protected function sendInstagramMessage(string $igUserId, string $message): bool
    {
        try {
            // Gunakan Instagram Graph API untuk mengirim pesan
            $accessToken = config('services.instagram.access_token');
            $pageId = config('services.instagram.page_id');
            
            if (!$accessToken || !$pageId) {
                Log::warning("Instagram credentials belum dikonfigurasi");
                return false;
            }

            $response = Http::post("https://graph.facebook.com/v18.0/{$pageId}/messages", [
                'recipient' => ['id' => $igUserId],
                'message' => ['text' => $message],
                'access_token' => $accessToken,
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error("Instagram send error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Kirim pesan via Web Widget
     * Simpan pesan ke database web_messages untuk di-poll oleh widget
     */
    protected function sendWebMessage(string $sessionId, string $message, SequenceEnrollment $enrollment): bool
    {
        try {
            // Cari conversation web berdasarkan session
            $conversation = \App\Models\WebConversation::where('session_id', $sessionId)->first();
            
            if (!$conversation) {
                Log::warning("Web conversation tidak ditemukan untuk session: {$sessionId}");
                return false;
            }

            // Simpan pesan bot
            \App\Models\WebMessage::create([
                'web_conversation_id' => $conversation->id,
                'sender_type' => 'bot',
                'content' => $message,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error("Web message send error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Cek dan auto-enroll berdasarkan trigger
     * Dipanggil dari webhook saat ada pesan masuk
     * 
     * @param string $triggerType - Tipe trigger (first_message, keyword)
     * @param array $data - Data kontak dan pesan
     */
    public function checkAndEnrollByTrigger(string $triggerType, array $data): void
    {
        $platform = $data['platform'] ?? 'whatsapp';
        $contactId = $data['contact_id'] ?? '';
        $contactName = $data['contact_name'] ?? null;
        $messageText = $data['message'] ?? '';

        // Cari sequence yang match dengan trigger
        $query = Sequence::active()
            ->where('trigger_type', $triggerType)
            ->forPlatform($platform);

        // Jika trigger keyword, filter berdasarkan keyword
        if ($triggerType === 'keyword') {
            $sequences = $query->get()->filter(function ($sequence) use ($messageText) {
                // Cek apakah pesan mengandung keyword
                $keywords = array_map('trim', explode(',', $sequence->trigger_value ?? ''));
                foreach ($keywords as $keyword) {
                    if (stripos($messageText, $keyword) !== false) {
                        return true;
                    }
                }
                return false;
            });
        } else {
            $sequences = $query->get();
        }

        // Enroll ke semua sequence yang match
        foreach ($sequences as $sequence) {
            $this->enrollContact($sequence, $contactId, $platform, $contactName);
        }
    }

    /**
     * Proses semua enrollment yang siap dijalankan
     * Dipanggil oleh scheduler command setiap menit
     */
    public function processReadyEnrollments(): int
    {
        $enrollments = SequenceEnrollment::readyToProcess()
            ->with(['sequence', 'currentStep'])
            ->get();

        $processed = 0;

        foreach ($enrollments as $enrollment) {
            if ($this->processEnrollment($enrollment)) {
                $processed++;
            }
        }

        return $processed;
    }

    /**
     * Enroll kontak secara manual (dari admin dashboard)
     */
    public function manualEnroll(int $sequenceId, string $contactIdentifier, string $platform, ?string $contactName = null): ?SequenceEnrollment
    {
        $sequence = Sequence::find($sequenceId);
        
        if (!$sequence) {
            return null;
        }

        return $this->enrollContact($sequence, $contactIdentifier, $platform, $contactName);
    }

    /**
     * Batalkan semua enrollment aktif untuk kontak tertentu
     */
    public function cancelAllForContact(string $contactIdentifier, string $platform): int
    {
        return SequenceEnrollment::where('contact_identifier', $contactIdentifier)
            ->where('platform', $platform)
            ->where('status', 'active')
            ->update([
                'status' => 'cancelled',
                'next_run_at' => null,
            ]);
    }
}
