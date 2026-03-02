# 💳 Pending Payment Notification System

**Status:** ✅ COMPLETED  
**Date:** 2026-02-16  
**Scope:** User Notification for Unpaid Invoices

---

## 🎯 Fitur Overview

Sistem notifikasi untuk mengingatkan user tentang pembayaran yang belum diselesaikan:

1. **Login Redirect** - Auto-redirect ke halaman pembayaran saat login
2. **Dashboard Banner** - Banner kuning menarik perhatian di setiap halaman
3. **Header Badge** - Badge "X Pending" di navbar
4. **Email Reminder** - Email otomatis 4 jam sebelum expired

---

## ✅ Implementasi

### 1. Login Check & Redirect

**File:** `app/Http/Controllers/Auth/AuthenticatedSessionController.php`

```php
// Saat user login, otomatis cek pending payment
$pendingPaymentCheck = $this->checkPendingPayment($user);

if ($pendingPaymentCheck['has_pending']) {
    return redirect()->route('checkout.payment', $payment->invoice_number)
        ->with('warning', "Anda memiliki pembayaran pending...");
}
```

**Behavior:**
- User login → Cek pending payment
- Jika ada → Redirect ke halaman pembayaran dengan flash message
- Jika tidak → Lanjutkan flow normal

---

### 2. Dashboard Banner

**File:** `resources/views/layouts/dark.blade.php`

Banner muncul di **setiap halaman** yang menggunakan layout `dark.blade.php`:

```
┌─────────────────────────────────────────────────────┐
│  [💰]  Pembayaran Menunggu                          │
│       Invoice: INV-2026-00001                       │
│       Paket: Pro Plan                               │
│       ⏱️ Expires in 2 hours    💵 Rp 100.000       │
│                                                     │
│  [=====================>    ] 75% time remaining   │
│                                                     │
│                    [Bayar Sekarang]                 │
└─────────────────────────────────────────────────────┘
```

**Features:**
- Progress bar showing time remaining (24h total)
- Warna progress: Kuning → Oranye → Merah (mendekati expired)
- Auto-link ke halaman pembayaran

---

### 3. Header Badge

Badge di header menunjukkan jumlah pembayaran pending:

```
[2 Pending 💰] [🔔] [System Online] [👤]
```

Klik langsung menuju halaman pembayaran.

---

### 4. Email Reminder

**Command:** `php artisan payments:send-reminders`

**Schedule:** Setiap 2 jam

**Template:** `resources/views/emails/payment/reminder.blade.php`

Email dikirim ketika:
- Pembayaran akan expired dalam 4 jam
- Belum pernah dikirim reminder ATAU reminder terakhir > 12 jam yang lalu

**Subject:** `⏰ Jangan Lupa! Pembayaran Anda Segera Berakhir - INV-2026-00001`

---

## 🗄️ Database Migration

**File:** `database/migrations/2026_02_17_002614_add_reminder_sent_at_to_payments_table.php`

Menambahkan kolom:
```sql
reminder_sent_at TIMESTAMP NULL  -- Track kapan reminder terakhir dikirim
```

**Run:**
```bash
php artisan migrate
```

---

## ⚡ Scheduler Commands

### Cleanup Expired Payments
```bash
# Manual run
php artisan payments:cleanup-expired

# Force (no confirmation)
php artisan payments:cleanup-expired --force

# Schedule: Hourly
```

### Send Reminders
```bash
# Default: 4 hours before expiration
php artisan payments:send-reminders

# Custom hours
php artisan payments:send-reminders --hours=2

# Dry run (test without sending)
php artisan payments:send-reminders --dry-run

# Schedule: Every 2 hours
```

---

## 📁 Files Created/Modified

### New Files:
```
app/Http/View/Composers/PendingPaymentComposer.php
app/Console/Commands/SendPendingPaymentReminders.php
app/Mail/PendingPaymentReminderMail.php
resources/views/emails/payment/reminder.blade.php
database/migrations/2026_02_17_002614_add_reminder_sent_at_to_payments_table.php
PENDING_PAYMENT_NOTIFICATION.md
```

### Modified Files:
```
app/Http/Controllers/Auth/AuthenticatedSessionController.php
app/Providers/AppServiceProvider.php
app/Models/Payment.php
resources/views/layouts/dark.blade.php
routes/console.php
```

---

## 🧪 Testing

### Test Login Redirect
```bash
# 1. Buat invoice pending (via checkout)
# 2. Logout
# 3. Login lagi
# 4. Expected: Redirect ke halaman pembayaran dengan flash message
```

### Test Dashboard Banner
```bash
# 1. Login dengan user yang punya pending payment
# 2. Buka dashboard atau halaman apapun
# 3. Expected: Banner kuning muncul di atas konten
```

### Test Email Reminder
```bash
# 1. Create payment dengan expires_at dalam 3 jam
# 2. Run: php artisan payments:send-reminders --dry-run
# 3. Check output: Should show 1 payment
# 4. Run without --dry-run: Email queued
# 5. Check queue: php artisan queue:work
```

### Test Cleanup
```bash
# 1. Create payment dengan expires_at yesterday
# 2. Run: php artisan payments:cleanup-expired
# 3. Check: Payment status changed to 'failed'
```

---

## 🔧 Configuration

### Environment Variables
Tidak perlu env tambahan. Menggunakan konfigurasi yang sudah ada.

### Scheduler Setup
Pastikan cron job running:

```bash
# Edit crontab
crontab -e

# Add line:
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

Atau untuk Windows (Task Scheduler):
```powershell
# Run setiap menit
php artisan schedule:run
```

---

## 📊 Monitoring

**Logs to watch:**
- `storage/logs/payments-cleanup.log`
- `storage/logs/payment-reminders.log`
- `storage/logs/laravel.log` (search for "pending payment")

**Queue monitoring:**
```bash
php artisan queue:monitor
table php artisan queue:work --queue=default
```

---

## 🔄 User Flow

```
User Checkout → Create Invoice → Pending Payment Created
                                    ↓
User Logout ----------------------→ User Login
                                    ↓
                              Auto Redirect to Payment Page
                                    ↓
User Bayar/Batal → Payment Success/Failed
```

**Dalam Dashboard:**
```
User Buka Dashboard → Banner Muncul (jika ada pending)
       ↓
Klik "Bayar Sekarang" → Payment Page
       ↓
Selesaikan Pembayaran → Banner Hilang
```

**Email Flow:**
```
Payment Created (24h expiry)
       ↓
T+20 hours (4h remaining) → Email Reminder Sent
       ↓
T+24 hours → Expired (auto cleanup)
```

---

## 🚀 Deployment Checklist

- [ ] Run migration: `php artisan migrate`
- [ ] Clear cache: `php artisan cache:clear`
- [ ] Clear view: `php artisan view:clear`
- [ ] Test login dengan pending payment
- [ ] Test email reminder (dry-run)
- [ ] Verify scheduler running
- [ ] Monitor logs 24h pertama

---

**Last Updated:** 2026-02-16
