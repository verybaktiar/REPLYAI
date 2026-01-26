<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceService
{
    /**
     * Generate invoice PDF for a payment
     */
    public function generateInvoice(Payment $payment): \Barryvdh\DomPDF\PDF
    {
        $user = $payment->user;
        $plan = $payment->subscription?->plan;

        $data = [
            'invoice_number' => $this->generateInvoiceNumber($payment),
            'date' => $payment->created_at->format('d F Y'),
            'due_date' => $payment->created_at->format('d F Y'),
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
            ],
            'items' => [
                [
                    'description' => 'Langganan ' . ($plan?->name ?? 'ReplyAI'),
                    'period' => $payment->billing_period ?? 'Bulanan',
                    'amount' => $payment->amount,
                ]
            ],
            'subtotal' => $payment->amount,
            'discount' => $payment->discount_amount ?? 0,
            'total' => $payment->amount - ($payment->discount_amount ?? 0),
            'payment_method' => $payment->payment_method ?? 'Transfer Bank',
            'status' => $payment->status,
        ];

        return Pdf::loadView('pdf.invoice', $data);
    }

    /**
     * Generate unique invoice number
     */
    protected function generateInvoiceNumber(Payment $payment): string
    {
        $prefix = 'INV';
        $year = $payment->created_at->format('Y');
        $month = $payment->created_at->format('m');
        $sequence = str_pad($payment->id, 5, '0', STR_PAD_LEFT);

        return "{$prefix}-{$year}{$month}-{$sequence}";
    }

    /**
     * Download invoice as PDF
     */
    public function download(Payment $payment): \Symfony\Component\HttpFoundation\Response
    {
        $pdf = $this->generateInvoice($payment);
        $filename = 'Invoice-' . $this->generateInvoiceNumber($payment) . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Stream invoice (view in browser)
     */
    public function stream(Payment $payment): \Symfony\Component\HttpFoundation\Response
    {
        $pdf = $this->generateInvoice($payment);
        return $pdf->stream();
    }
}
