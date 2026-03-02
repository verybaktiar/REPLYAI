<?php

namespace App\Mail;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Email: Pending Payment Reminder
 * 
 * Reminder email saat pembayaran akan segera expired
 */
class PendingPaymentReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public Payment $payment;
    public string $timeLeft;

    /**
     * Create a new message instance.
     */
    public function __construct(Payment $payment)
    {
        $this->payment = $payment;
        $this->timeLeft = $payment->expires_at->diffForHumans();
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '⏰ Jangan Lupa! Pembayaran Anda Segera Berakhir - ' . $this->payment->invoice_number,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.payment.reminder',
            with: [
                'payment' => $this->payment,
                'plan' => $this->payment->plan,
                'user' => $this->payment->user,
                'timeLeft' => $this->timeLeft,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
