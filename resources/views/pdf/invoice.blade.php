<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $invoice_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 12px; color: #333; }
        .invoice-container { max-width: 800px; margin: 0 auto; padding: 40px; }
        
        /* Header */
        .header { display: flex; justify-content: space-between; margin-bottom: 40px; border-bottom: 2px solid #135bec; padding-bottom: 20px; }
        .logo { font-size: 28px; font-weight: 800; }
        .logo-reply { color: #135bec; }
        .logo-ai { color: #8b5cf6; }
        .invoice-title { text-align: right; }
        .invoice-title h1 { font-size: 24px; color: #135bec; margin-bottom: 5px; }
        .invoice-number { font-size: 14px; color: #666; }
        
        /* Info Section */
        .info-section { display: flex; justify-content: space-between; margin-bottom: 40px; }
        .info-block { width: 48%; }
        .info-label { font-size: 10px; text-transform: uppercase; color: #999; letter-spacing: 1px; margin-bottom: 8px; }
        .info-value { font-size: 14px; color: #333; line-height: 1.6; }
        
        /* Table */
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .items-table th { background: #135bec; color: white; padding: 12px 15px; text-align: left; font-weight: 600; }
        .items-table td { padding: 15px; border-bottom: 1px solid #eee; }
        .items-table .amount { text-align: right; }
        
        /* Totals */
        .totals { width: 300px; margin-left: auto; }
        .total-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #eee; }
        .total-row.grand-total { border-bottom: none; border-top: 2px solid #135bec; margin-top: 10px; padding-top: 15px; }
        .total-row.grand-total .label, .total-row.grand-total .value { font-size: 18px; font-weight: 700; color: #135bec; }
        
        /* Footer */
        .footer { margin-top: 60px; text-align: center; color: #999; font-size: 11px; border-top: 1px solid #eee; padding-top: 20px; }
        
        /* Status Badge */
        .status-badge { display: inline-block; padding: 5px 12px; border-radius: 20px; font-size: 10px; font-weight: 600; text-transform: uppercase; }
        .status-paid { background: #dcfce7; color: #166534; }
        .status-pending { background: #fef9c3; color: #854d0e; }
    </style>
</head>
<body>
    <div class="invoice-container">
        <!-- Header -->
        <table style="width: 100%; margin-bottom: 40px; border-bottom: 2px solid #135bec; padding-bottom: 20px;">
            <tr>
                <td style="width: 50%;">
                    <div class="logo">
                        <span class="logo-reply">REPLY</span><span class="logo-ai">AI</span>
                    </div>
                    <p style="color: #666; margin-top: 5px;">AI Customer Service Platform</p>
                </td>
                <td style="width: 50%; text-align: right;">
                    <h1 style="font-size: 28px; color: #135bec; margin: 0;">INVOICE</h1>
                    <p style="color: #666; margin-top: 5px;">{{ $invoice_number }}</p>
                    <span class="status-badge status-{{ $status }}">{{ ucfirst($status) }}</span>
                </td>
            </tr>
        </table>

        <!-- Info Section -->
        <table style="width: 100%; margin-bottom: 40px;">
            <tr>
                <td style="width: 50%; vertical-align: top;">
                    <p class="info-label">Tagihan Kepada</p>
                    <p class="info-value">
                        <strong>{{ $user['name'] }}</strong><br>
                        {{ $user['email'] }}
                    </p>
                </td>
                <td style="width: 50%; vertical-align: top; text-align: right;">
                    <p class="info-label">Detail Invoice</p>
                    <p class="info-value">
                        <strong>Tanggal:</strong> {{ $date }}<br>
                        <strong>Metode Pembayaran:</strong> {{ $payment_method }}
                    </p>
                </td>
            </tr>
        </table>

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 50%;">Deskripsi</th>
                    <th>Periode</th>
                    <th class="amount">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                @foreach($items as $item)
                <tr>
                    <td>{{ $item['description'] }}</td>
                    <td>{{ $item['period'] }}</td>
                    <td class="amount">Rp {{ number_format($item['amount'], 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals -->
        <div class="totals">
            <div class="total-row">
                <span class="label">Subtotal</span>
                <span class="value">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
            </div>
            @if($discount > 0)
            <div class="total-row">
                <span class="label">Diskon</span>
                <span class="value" style="color: #22c55e;">- Rp {{ number_format($discount, 0, ',', '.') }}</span>
            </div>
            @endif
            <div class="total-row grand-total">
                <span class="label">Total</span>
                <span class="value">Rp {{ number_format($total, 0, ',', '.') }}</span>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p>Terima kasih telah menggunakan ReplyAI!</p>
            <p style="margin-top: 10px;">Website: replyai.id | Email: support@replyai.id</p>
        </div>
    </div>
</body>
</html>
