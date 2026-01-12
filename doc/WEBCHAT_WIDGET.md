# Dokumentasi Web Chat Widget ReplyAI

## Pendahuluan

Web Chat Widget ReplyAI adalah solusi chat yang dapat di-embed ke website WordPress atau website lainnya. Widget ini memungkinkan visitor website berkomunikasi langsung dengan AI ReplyAI, dan semua percakapan dapat dikelola melalui dashboard ReplyAI.

---

## Cara Kerja

```
┌─────────────────┐      ┌─────────────────┐      ┌─────────────────┐
│                 │      │                 │      │                 │
│  Website        │ ──── │  ReplyAI API    │ ──── │   AI Engine     │
│  (WordPress)    │      │                 │      │                 │
│                 │      └─────────────────┘      └─────────────────┘
│  ┌───────────┐  │             │
│  │  Widget   │  │             │
│  │  Chat     │◄─┼─────────────┘
│  └───────────┘  │      Respons AI
│                 │
└─────────────────┘
```

1. Visitor membuka website dan melihat bubble chat
2. Visitor mengetik pesan dan mengirimnya
3. Widget mengirim pesan ke API ReplyAI
4. AI memproses dan memberikan respons
5. Respons ditampilkan di widget

---

## Instalasi

### Langkah 1: Buat Widget

1. Login ke dashboard ReplyAI
2. Buka menu **Web Widget** (`/web-widgets`)
3. Klik **"Buat Widget Baru"**
4. Isi formulir:
   - **Nama Widget**: Nama untuk identifikasi (contoh: "Website Utama")
   - **Domain**: Domain website Anda (opsional, untuk keamanan)
   - **Pesan Sambutan**: Pesan pertama yang dilihat visitor
   - **Nama Bot**: Nama yang ditampilkan untuk bot
   - **Warna Utama**: Warna tema widget
   - **Posisi**: Kanan bawah atau kiri bawah
5. Klik **"Buat Widget"**

### Langkah 2: Salin Embed Code

Setelah widget dibuat, salin kode embed yang ditampilkan:

```html
<script src="https://domain-replyai-anda.com/widget/replyai-widget.js" 
        data-api-key="rw_xxxxxxxxxxxxxxxxxxxxxxxxxx"></script>
```

### Langkah 3: Pasang di WordPress

**Metode A: Menggunakan Plugin**

1. Install plugin **"Insert Headers and Footers"** atau **"WPCode"**
2. Buka pengaturan plugin
3. Paste kode di bagian **Footer Scripts**
4. Simpan

**Metode B: Edit Theme**

1. Buka **Appearance → Theme File Editor**
2. Pilih file `footer.php`
3. Paste kode tepat sebelum tag `</body>`
4. Update File

**Metode C: Menggunakan Custom HTML Block**

1. Buka halaman dengan editor Gutenberg
2. Tambahkan block **Custom HTML**
3. Paste kode embed
4. Publish/Update halaman

---

## Konfigurasi Widget

### Pengaturan yang Tersedia

| Pengaturan | Deskripsi | Default |
|------------|-----------|---------|
| **Nama Widget** | Identifikasi widget di dashboard | (wajib diisi) |
| **Domain** | Restrict widget ke domain tertentu | Semua domain |
| **Pesan Sambutan** | Pesan sapaan untuk visitor | "Halo! Ada yang bisa kami bantu?" |
| **Nama Bot** | Nama yang ditampilkan di header | "Bot ReplyAI" |
| **Warna Utama** | Warna tema bubble dan button | #4F46E5 |
| **Posisi** | Lokasi bubble di layar | Kanan bawah |
| **Status Aktif** | Mengaktifkan/nonaktifkan widget | Aktif |

### Mengubah Pengaturan

1. Buka `/web-widgets`
2. Klik ikon **Edit** pada widget
3. Ubah pengaturan yang diinginkan
4. Klik **"Simpan Perubahan"**

---

## Fitur Widget

### Untuk Visitor

- **Bubble Chat**: Tombol melayang di pojok layar
- **Panel Chat**: Area percakapan yang responsif
- **Riwayat Chat**: Percakapan tersimpan saat kembali ke website
- **Mobile Friendly**: Tampilan fullscreen di ponsel
- **Keyboard Shortcuts**: Enter untuk kirim, Shift+Enter untuk baris baru

### Untuk Admin

- **Dashboard Management**: Kelola semua widget dari satu tempat
- **API Key**: Setiap widget punya API key unik
- **Statistik**: Lihat jumlah chat per widget
- **Toggle Aktif**: Nonaktifkan widget tanpa menghapus

---

## API Reference

### Get Widget Configuration

```
GET /api/web/widget/{api_key}
```

**Response:**
```json
{
  "success": true,
  "widget": {
    "name": "Website Utama",
    "welcome_message": "Halo! Ada yang bisa kami bantu?",
    "bot_name": "Bot ReplyAI",
    "primary_color": "#4F46E5",
    "position": "bottom-right"
  }
}
```

### Send Message

```
POST /api/web/chat
Content-Type: application/json

{
  "api_key": "rw_xxx...",
  "visitor_id": "v_abc123",
  "message": "Halo, saya mau bertanya",
  "page_url": "https://website.com/contact"
}
```

**Response:**
```json
{
  "success": true,
  "message_id": 123,
  "bot_response": "Halo! Tentu, silakan bertanya. Apa yang bisa saya bantu?",
  "conversation_status": "bot"
}
```

### Get Conversation History

```
GET /api/web/conversation/{visitor_id}?api_key=rw_xxx
```

**Response:**
```json
{
  "success": true,
  "messages": [
    {
      "id": 1,
      "sender_type": "visitor",
      "content": "Halo",
      "created_at": "2026-01-07T10:00:00Z"
    },
    {
      "id": 2,
      "sender_type": "bot",
      "content": "Halo! Ada yang bisa saya bantu?",
      "created_at": "2026-01-07T10:00:05Z"
    }
  ]
}
```

---

## Troubleshooting

### Widget Tidak Muncul

1. Pastikan domain di pengaturan widget sesuai atau dikosongkan
2. Periksa console browser untuk error JavaScript
3. Pastikan API key benar
4. Verifikasi widget dalam status "Aktif"

### Respons AI Tidak Muncul

1. Periksa koneksi internet
2. Pastikan AI service ReplyAI berjalan
3. Cek log di `/storage/logs/laravel.log`

### Session Hilang

Visitor ID disimpan di localStorage browser. Session bisa hilang jika:
- Browser dalam mode incognito/private
- Visitor menghapus data browser
- Mengakses dari browser berbeda

---

## Keamanan

- **API Key**: Jaga kerahasiaan API key Anda
- **Domain Restriction**: Atur domain untuk membatasi penggunaan
- **HTTPS**: Selalu gunakan HTTPS untuk website Anda
- **Rate Limiting**: API memiliki rate limiting untuk mencegah abuse

---

## Pemeliharaan

### Regenerate API Key

Jika API key bocor:
1. Buka halaman edit widget
2. Klik "Regenerate Key" (jika tersedia) atau hapus dan buat widget baru
3. Update embed code di website

### Hapus Widget

1. Buka `/web-widgets`
2. Klik ikon **Delete** pada widget
3. Konfirmasi penghapusan
4. Hapus embed code dari website

---

## Changelog

**v1.0.0** - Januari 2026
- Rilis awal
- Fitur: Bubble chat, panel percakapan, AI response
- Dashboard management
- API endpoints lengkap
