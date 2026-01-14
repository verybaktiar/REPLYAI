# WhatsApp Multi-Channel Settings

## Deskripsi
Halaman pengaturan WhatsApp yang mendukung multiple channel/device WhatsApp. Setiap device dapat dikelola secara independen dengan sesi terpisah.

## Fitur Utama

### 1. **Device Management**
- Menampilkan semua device WhatsApp yang terhubung
- Informasi lengkap setiap device:
  - Nama device
  - Status koneksi (Connected, Scanning, Disconnected, Unknown)
  - Nomor telepon
  - Nama profil
  - Session ID
  - Waktu update terakhir

### 2. **Tambah Device Baru**
- Modal untuk menambahkan device baru
- Input nama device yang mudah dikenali
- Auto-generate session ID unik
- Integrasi dengan Node.js WhatsApp service

### 3. **QR Code Scanning**
- Auto-refresh QR code untuk device dengan status "scanning"
- Polling status setiap 3 detik
- Instruksi scan QR code yang jelas
- Auto-reload setelah berhasil connect

### 4. **Real-time Status Updates**
- Auto-check status untuk device yang sedang scanning
- Update badge status secara real-time
- Auto-stop polling setelah device connected

### 5. **Device Actions**
- **View Details**: Melihat detail device (redirect ke inbox)
- **Disconnect/Hapus**: Menghapus device dan session

## Struktur Database

### Table: `whatsapp_devices`
```sql
- id (bigint, primary key)
- session_id (string, unique) - ID unik untuk sesi Baileys
- device_name (string) - Nama label perangkat
- phone_number (string, nullable) - Nomor WA yang terhubung
- profile_name (string, nullable) - Nama profil WA
- status (enum: connected, disconnected, scanning, unknown)
- last_disconnect_reason (text, nullable)
- last_connected_at (timestamp, nullable)
- is_active (boolean, default: true)
- created_at, updated_at
```

## Routes

```php
// Settings Page
Route::get('/whatsapp/settings', [WhatsAppController::class, 'settings'])
    ->name('whatsapp.settings');

// Device Management
Route::post('/whatsapp/store', [WhatsAppController::class, 'store'])
    ->name('whatsapp.store');
    
Route::delete('/whatsapp/device/{sessionId}', [WhatsAppController::class, 'destroy'])
    ->name('whatsapp.destroy');
    
Route::get('/whatsapp/status/{sessionId}', [WhatsAppController::class, 'status'])
    ->name('whatsapp.status');
    
Route::get('/whatsapp/qr/{sessionId}', [WhatsAppController::class, 'qr'])
    ->name('whatsapp.qr');
```

## API Endpoints

### 1. Tambah Device
**POST** `/whatsapp/store`
```json
Request:
{
  "device_name": "HP Admin Utama"
}

Response:
{
  "success": true,
  "device": {
    "id": 1,
    "session_id": "hp-admin-utama-abc123",
    "device_name": "HP Admin Utama",
    "status": "scanning"
  }
}
```

### 2. Get Status
**GET** `/whatsapp/status/{sessionId}`
```json
Response:
{
  "status": "connected",
  "phoneNumber": "628123456789",
  "profileName": "Admin"
}
```

### 3. Get QR Code
**GET** `/whatsapp/qr/{sessionId}`
```json
Response:
{
  "success": true,
  "qr": "data:image/png;base64,..."
}
```

### 4. Hapus Device
**DELETE** `/whatsapp/device/{sessionId}`
```json
Response:
{
  "success": true,
  "message": "Device removed"
}
```

## JavaScript Functions

### Modal Management
- `openAddDeviceModal()` - Membuka modal tambah device
- `closeAddDeviceModal()` - Menutup modal tambah device
- `closeQrModal()` - Menutup modal QR code

### Device Operations
- `addDevice(event)` - Menambahkan device baru
- `disconnectDevice(sessionId)` - Menghapus device
- `checkDeviceStatus(sessionId)` - Mengecek status device
- `loadQrCode(sessionId, container)` - Load QR code untuk scanning
- `viewDeviceDetails(sessionId)` - Melihat detail device

### Utilities
- `showNotification(message, type)` - Menampilkan notifikasi

## Auto-Polling Mechanism

Device dengan status "scanning" akan otomatis:
1. Polling status setiap 3 detik
2. Load QR code jika tersedia
3. Update badge status
4. Stop polling setelah connected
5. Reload page setelah berhasil connect

## UI Components

### Status Badges
- **Connected**: Hijau (bg-success)
- **Scanning**: Kuning (bg-warning)
- **Disconnected**: Merah (bg-danger)
- **Unknown**: Abu-abu (bg-meta-1)

### Device Card
- Header dengan nama dan status badge
- Info section dengan icon:
  - Phone number
  - Profile name
  - Last update time
  - Session ID
- QR code container (untuk scanning status)
- Action buttons (Detail, Hapus)

### Empty State
- Icon placeholder
- Pesan "Belum ada device"
- Button "Tambah Device"

## Integrasi dengan Node.js Service

Service URL dikonfigurasi di `config/services.php`:
```php
'whatsapp' => [
    'url' => env('WHATSAPP_SERVICE_URL', 'http://127.0.0.1:3001'),
],
```

### Node.js Endpoints yang Digunakan
- `POST /connect` - Inisialisasi sesi baru
- `GET /status` - Get status koneksi
- `GET /qr` - Get QR code
- `POST /disconnect` - Disconnect sesi

## Cara Penggunaan

### Menambah Device Baru
1. Klik tombol "Tambah Device Baru"
2. Masukkan nama device (contoh: "HP Admin Utama")
3. Klik "Tambah"
4. Scan QR code yang muncul dengan WhatsApp
5. Tunggu hingga status berubah menjadi "Connected"

### Menghapus Device
1. Klik tombol "Hapus" pada device card
2. Konfirmasi penghapusan
3. Device akan dihapus dari database dan Node.js service

### Melihat Detail Device
1. Klik tombol "Detail" pada device yang connected
2. Akan redirect ke halaman WhatsApp Inbox

## Troubleshooting

### QR Code Tidak Muncul
- Pastikan Node.js WhatsApp service berjalan
- Check log di `storage/logs/laravel.log`
- Pastikan port 3001 tidak terblokir

### Device Tidak Connect
- Pastikan QR code di-scan dengan benar
- Check koneksi internet
- Restart Node.js service jika perlu

### Status Tidak Update
- Check browser console untuk error
- Pastikan CSRF token valid
- Clear browser cache

## Security Notes

- Semua request menggunakan CSRF token
- Session ID di-generate secara unik
- Device dapat dihapus kapan saja
- Tidak ada data sensitif yang disimpan di frontend

## Future Improvements

1. **Bulk Actions**: Hapus multiple devices sekaligus
2. **Device Groups**: Grouping devices berdasarkan kategori
3. **Auto-reconnect**: Otomatis reconnect jika disconnect
4. **Notification**: Browser notification untuk status changes
5. **Device Analytics**: Statistik penggunaan per device
6. **Device Settings**: Konfigurasi per device (auto-reply, dll)
