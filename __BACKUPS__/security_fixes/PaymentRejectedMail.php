<?php

namespace App\Mail;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Email: Pembayaran Ditolak
 * 
 * Email notification saat pembayaran ditolak oleh admin
 */
class PaymentRejectedMail extends Mailable
{
    use Queueable, SerializesModels;

    public Payment $payment;
    public string $reason;

    /**
     * Create a new message instance.
     */
    public function __construct(Payment $payment, string $reason)
    {
        $this->payment = $payment;
        $this->reason = $reason;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '❌ Pembayaran Tidak Valid - ' . $this->payment->invoice_number,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.payment.rejected',
            with: [
                'payment' => $this->payment,
                'plan' => $this->payment->plan,
                'reason' => $this->reason,
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
