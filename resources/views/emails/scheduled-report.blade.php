@component('mail::message')
# Laporan Terjadwal

Berikut adalah laporan terjadwal Anda:

**Nama Laporan:** {{ $reportName }}
**Tipe:** {{ $reportType }}
**Frekuensi:** {{ ucfirst($frequency) }}
**Dibuat:** {{ $generatedAt }}

Laporan terlampir dalam format yang Anda pilih.

@component('mail::button', ['url' => url('/reports/scheduled')])
Kelola Laporan
@endcomponent

Terima kasih,
{{ config('app.name') }}
@endcomponent
