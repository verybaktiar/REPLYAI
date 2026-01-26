<?php

namespace App\Services;

use App\Models\SupportTicket;
use App\Models\TicketReply;
use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Service: Support Ticket
 * 
 * Service untuk mengelola tiket support.
 * Fitur: buat tiket, balas, tutup, notifikasi.
 */
class SupportService
{
    /**
     * Buat tiket baru
     */
    public function createTicket(
        User $user,
        string $category,
        string $subject,
        string $message,
        string $priority = SupportTicket::PRIORITY_MEDIUM,
        ?array $attachments = null
    ): SupportTicket {
        $ticket = SupportTicket::create([
            'ticket_number' => SupportTicket::generateTicketNumber(),
            'user_id' => $user->id,
            'category' => $category,
            'subject' => $subject,
            'message' => $message,
            'priority' => $priority,
            'status' => SupportTicket::STATUS_OPEN,
            'attachments' => $attachments,
        ]);

        // Kirim notifikasi ke admin
        $this->notifyAdminNewTicket($ticket);

        return $ticket;
    }

    /**
     * Balas tiket (customer)
     */
    public function replyAsCustomer(SupportTicket $ticket, User $user, string $message, ?array $attachments = null): TicketReply
    {
        $reply = TicketReply::create([
            'ticket_id' => $ticket->id,
            'sender_type' => TicketReply::SENDER_CUSTOMER,
            'sender_id' => $user->id,
            'message' => $message,
            'attachments' => $attachments,
        ]);

        // Update status ke in_progress jika sedang waiting
        if ($ticket->status === SupportTicket::STATUS_WAITING) {
            $ticket->update(['status' => SupportTicket::STATUS_IN_PROGRESS]);
        }

        // Notifikasi ke admin
        $this->notifyAdminNewReply($ticket, $reply);

        return $reply;
    }

    /**
     * Balas tiket (admin)
     */
    public function replyAsAdmin(SupportTicket $ticket, int $adminId, string $message, ?array $attachments = null): TicketReply
    {
        $reply = TicketReply::create([
            'ticket_id' => $ticket->id,
            'sender_type' => TicketReply::SENDER_ADMIN,
            'sender_id' => $adminId,
            'message' => $message,
            'attachments' => $attachments,
        ]);

        // Update status ke waiting customer
        $ticket->update([
            'status' => SupportTicket::STATUS_WAITING,
            'assigned_to' => $adminId,
        ]);

        // Notifikasi ke customer
        $this->notifyCustomerNewReply($ticket, $reply);

        return $reply;
    }

    /**
     * Resolve tiket
     */
    public function resolveTicket(SupportTicket $ticket, int $adminId): SupportTicket
    {
        $ticket->update([
            'status' => SupportTicket::STATUS_RESOLVED,
            'resolved_at' => now(),
            'assigned_to' => $adminId,
        ]);

        // Tambahkan system message
        TicketReply::create([
            'ticket_id' => $ticket->id,
            'sender_type' => TicketReply::SENDER_SYSTEM,
            'message' => 'Tiket telah ditandai sebagai selesai. Jika masalah Anda belum terselesaikan, silakan balas tiket ini.',
        ]);

        // Notifikasi customer
        $this->notifyCustomerTicketResolved($ticket);

        return $ticket->fresh();
    }

    /**
     * Tutup tiket
     */
    public function closeTicket(SupportTicket $ticket): SupportTicket
    {
        $ticket->update([
            'status' => SupportTicket::STATUS_CLOSED,
            'closed_at' => now(),
        ]);

        return $ticket->fresh();
    }

    /**
     * Beri rating tiket
     */
    public function rateTicket(SupportTicket $ticket, int $rating, ?string $feedback = null): SupportTicket
    {
        $ticket->update([
            'rating' => $rating,
            'feedback' => $feedback,
        ]);

        return $ticket->fresh();
    }

    /**
     * Ambil statistik tiket untuk admin
     */
    public function getStats(): array
    {
        return [
            'open' => SupportTicket::open()->count(),
            'in_progress' => SupportTicket::inProgress()->count(),
            'resolved' => SupportTicket::resolved()->count(),
            'urgent_open' => SupportTicket::open()->urgent()->count(),
            'avg_rating' => SupportTicket::whereNotNull('rating')->avg('rating'),
        ];
    }

    // ==========================================
    // NOTIFIKASI
    // ==========================================

    /**
     * Notifikasi ke admin: tiket baru
     */
    private function notifyAdminNewTicket(SupportTicket $ticket): void
    {
        Log::info('New support ticket', [
            'ticket' => $ticket->ticket_number,
            'user' => $ticket->user->email ?? 'Unknown',
            'category' => $ticket->category,
            'priority' => $ticket->priority,
            'subject' => $ticket->subject,
        ]);

        // TODO: Kirim email ke admin
        // Mail::to('admin@replyai.com')->send(new NewTicketMail($ticket));

        // TODO: Kirim notifikasi Telegram
        // $this->sendTelegramNotification($ticket);
    }

    /**
     * Notifikasi ke admin: balasan baru dari customer
     */
    private function notifyAdminNewReply(SupportTicket $ticket, TicketReply $reply): void
    {
        Log::info('New ticket reply from customer', [
            'ticket' => $ticket->ticket_number,
            'reply_id' => $reply->id,
        ]);
    }

    /**
     * Notifikasi ke customer: balasan dari admin
     */
    private function notifyCustomerNewReply(SupportTicket $ticket, TicketReply $reply): void
    {
        Log::info('Admin replied to ticket', [
            'ticket' => $ticket->ticket_number,
            'user_email' => $ticket->user->email ?? 'Unknown',
        ]);

        // TODO: Kirim email ke customer
        // Mail::to($ticket->user->email)->send(new TicketReplyMail($ticket, $reply));
    }

    /**
     * Notifikasi ke customer: tiket resolved
     */
    private function notifyCustomerTicketResolved(SupportTicket $ticket): void
    {
        Log::info('Ticket resolved', [
            'ticket' => $ticket->ticket_number,
        ]);

        // TODO: Kirim email ke customer minta rating
    }
}
