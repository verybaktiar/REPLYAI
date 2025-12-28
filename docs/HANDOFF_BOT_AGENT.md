# Implementasi Sistem Handoff Bot-to-Agent

Sistem untuk menangani perpindahan kontrol dari Bot ke CS (dan sebaliknya) secara otomatis dan manual.

## Konsep Utama

| Status Percakapan | Artinya | Bot Aktif? |
|---|---|---|
| `bot_handling` | Bot sedang handle | ✅ Ya |
| `escalated` | Bot tidak bisa jawab, menunggu CS | ❌ Tidak |
| `agent_handling` | CS sudah ambil alih | ❌ Tidak |

**Timeout**: 4 jam setelah CS terakhir balas → otomatis kembali ke `bot_handling`
**New Session**: Pesan baru setelah 24 jam gap → anggap sesi baru → `bot_handling`

## Alur Kerja

```
Chat Masuk → Bot Coba Jawab
    ↓
Bisa jawab? → Ya → Bot balas, selesai
    ↓ No
Status = "escalated" (Bot diam untuk conv ini)
    ↓
CS Balas → Status = "agent_handling"
    ↓
[Setelah 4 jam tidak ada chat baru] → Status = "bot_handling"
    ATAU
[Pesan baru setelah 24 jam] → Anggap sesi baru, bot aktif
```

## Komponen

### Database
- Kolom `status` di `conversations`: `bot_handling`, `escalated`, `agent_handling`
- Kolom `agent_replied_at`: Timestamp terakhir CS membalas

### Backend
- `AutoReplyEngine.php`: Cek status sebelum bot balas
- `InboxController.php`: Update status saat CS balas + endpoint handback

### Frontend
- Badge status di header chat
- Tombol "Kembalikan ke Bot"
- Notifikasi countdown timeout

## Parameter
- `AGENT_TIMEOUT_HOURS`: 4 jam
- `NEW_SESSION_GAP_HOURS`: 24 jam

---

## 🔄 Cara Kembalikan ke Bot

### 1. Manual (Tombol)
CS klik tombol **"Kembalikan ke Bot"** di header chat Inbox.
- Langsung aktif
- Cocok untuk: CS sudah selesai membantu pasien

### 2. Otomatis (Timeout 4 Jam)
Jika CS tidak membalas selama **4 jam**, bot otomatis aktif kembali.
- Tidak perlu aksi apapun
- Cocok untuk: CS lupa klik tombol / tutup browser

### 3. Otomatis (Sesi Baru 24 Jam)
Jika pasien chat kembali setelah **24 jam** dari chat terakhir, dianggap sesi baru.
- Bot otomatis aktif
- Cocok untuk: Pasien baru chat lagi besok/lusa

## 🧪 Panduan Testing

### Test 1: Bot Balas Chat Baru
1. Kirim pesan dari Instagram ke akun RS
2. Pastikan bot membalas otomatis
3. Buka `/inbox` → lihat badge di header chat: **🤖 Bot** (hijau)

### Test 2: CS Ambil Alih
1. Buka halaman **Inbox** (`/inbox`)
2. Pilih conversation, ketik balasan manual, klik **Kirim**
3. Setelah terkirim:
   - Badge berubah jadi **👤 Agent** (kuning)
   - Tombol **"Kembalikan ke Bot"** muncul
4. Kirim pesan lagi dari Instagram → **Bot DIAM** (tidak balas)

### Test 3: Kembalikan ke Bot (Manual)
1. Di conversation yang statusnya `agent_handling`
2. Klik tombol **"Kembalikan ke Bot"**
3. Badge berubah jadi **🤖 Bot** (hijau)
4. Kirim pesan dari Instagram → **Bot balas lagi**

### Test 4: Timeout Otomatis (via Tinker)
```bash
php artisan tinker
```
```php
$conv = \App\Models\Conversation::find(1); // ganti ID
$conv->update(['agent_replied_at' => now()->subHours(5)]);
```
Kirim pesan dari Instagram → Bot balas (karena sudah timeout 4 jam)

### Test 5: Cek Log
Buka `storage/logs/laravel.log`, cari:
- `🤖 Handoff timeout` → Bot aktif karena timeout
- `🤫 Bot silent: agent still handling` → Bot diam, CS aktif
- `🆕 New session` → Bot aktif karena gap 24 jam
