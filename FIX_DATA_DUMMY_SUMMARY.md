# Perbaikan Data Dummy di Menu Laporan

## Masalah
Beberapa halaman laporan menampilkan **data dummy hardcoded** sehingga user baru yang belum memiliki aktivitas tetap melihat data palsu.

## File yang Diperbaiki

### 1. ✅ Conversation Quality Controller
**File:** `app/Http/Controllers/ConversationQualityController.php`

**Perubahan:**
- Method `index()` sekarang mengirim data real ke view
- Menambahkan helper methods:
  - `getSentimentAnalysisData()` - Ambil analisis sentimen dari database
  - `calculateQualityScore()` - Hitung skor kualitas dari data real
  - `getBotHandledPercentage()` - Hitung persentase penanganan bot

**Data yang dikirim ke view:**
- `$qualityScore` - Skor kualitas (0-100)
- `$sentimentPositive` - Persentase sentimen positif
- `$sentimentNeutral` - Persentase sentimen netral
- `$sentimentNegative` - Persentase sentimen negatif
- `$botHandled` - Persentase penanganan bot

### 2. ✅ Quality Report View
**File:** `resources/views/pages/reports/quality/index.blade.php`

**Perubahan:**
- Hapus default value dummy:
  - Sebelum: `$qualityScore ?? 87` → Sesudah: `$qualityScore ?? 0`
  - Sebelum: `$sentimentPositive ?? 68` → Sesudah: `$sentimentPositive ?? 0`
  - Sebelum: `$sentimentNegative ?? 12` → Sesudah: `$sentimentNegative ?? 0`
  - Sebelum: `$sentimentNeutral ?? 20` → Sesudah: `$sentimentNeutral ?? 0`
  - Sebelum: `$botHandled ?? 74` → Sesudah: `$botHandled ?? 0`

### 3. ✅ Realtime Dashboard View
**File:** `resources/views/pages/reports/realtime/index.blade.php`

**Perubahan:**
- Hapus data dummy agents (Ahmad Rizki, Budi Santoso, dll)
- Hapus data dummy activities dengan detail palsu
- Set semua statistik ke 0 (data kosong)
- Disable fungsi `simulateRandomUpdate()` yang generate data palsu

## Status Data per Fitur

| Fitur | Status Sebelum | Status Sesudah |
|-------|---------------|----------------|
| Laporan Terjadwal | ✅ Real dari DB | ✅ Real dari DB |
| Template Laporan | ✅ Real dari DB | ✅ Real dari DB |
| Dashboard Real-time | ❌ Data dummy | ✅ Data kosong (0) |
| Kualitas Percakapan | ❌ Data dummy (87, 68%, dll) | ✅ Data real dari DB |
| Analisis Perbandingan | ✅ Real dari API | ✅ Real dari API |
| AI Performance | ✅ Real dari DB | ✅ Real dari DB |

## Langkah Selanjutnya

1. Clear cache:
```bash
php artisan view:clear
php artisan cache:clear
```

2. Refresh halaman dan cek:
- User baru seharusnya melihat angka 0 atau data kosong
- User dengan aktivitas akan melihat data real mereka

## Catatan

Untuk Dashboard Real-time, saat ini data ditampilkan sebagai kosong (0). Jika ingin menampilkan data real, perlu menambahkan endpoint API yang mengambil data dari:
- `WaConversation` untuk WhatsApp
- `Conversation` untuk Instagram
- `WebConversation` untuk Web Widget
