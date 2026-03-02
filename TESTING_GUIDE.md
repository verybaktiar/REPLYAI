# 🧪 Testing Guide - REPLYAI Payment System

Dokumen ini berisi panduan lengkap untuk menguji sistem pembayaran dan security fixes.

---

## 📋 Quick Start

```bash
# 1. Run all tests
php artisan test

# 2. Run specific test
php artisan test --filter=CheckoutPriceTest

# 3. Run security tests only
php artisan test --filter=SecurityPaymentTest

# 4. Run with coverage
php artisan test --coverage

# 5. Interactive test runner (Windows)
run-tests.bat
```

---

## 🧪 Test Categories

### 1. **Unit Tests** - `tests/Unit/`

Test untuk individual classes/methods:
- Model relationships
- Service methods
- Helper functions

```bash
php artisan test --testsuite=Unit
```

### 2. **Feature Tests** - `tests/Feature/`

Test untuk HTTP requests dan controllers:

| Test File | Coverage |
|-----------|----------|
| `CheckoutPriceTest.php` | Harga bulanan vs tahunan |
| `SecurityPaymentTest.php` | All security vulnerabilities |
| `PaymentFlowTest.php` | End-to-end checkout flow |

```bash
php artisan test --testsuite=Feature
```

### 3. **Browser Tests** - `tests/Browser/`

Test menggunakan browser Chrome (Laravel Dusk):

```bash
# Install Dusk (satu kali)
php artisan dusk:install

# Download ChromeDriver
php artisan dusk:chrome-driver

# Run browser tests
php artisan dusk
```

**Requirements:**
- Google Chrome terinstall
- ChromeDriver compatible dengan versi Chrome

### 4. **API Tests** - `tests/ApiTesting/`

File: `PaymentApiTest.http`

Gunakan dengan:
- **VS Code**: Install "REST Client" extension
- **Postman**: Import file → Convert to collection
- **Insomnia**: Import → From File

---

## 🔒 Security Testing Checklist

### CRITICAL-001: Price Manipulation

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Checkout plan Pro bulanan | Total: Rp 500.000 |
| 2 | Checkout plan Pro tahunan | Total: Rp 5.000.000 |
| 3 | Inspect element, coba ubah value | Tidak bisa diubah |
| 4 | Submit form dengan curl/postman | Harga tetap dari DB |

**Test Command:**
```bash
php artisan test --filter=test_price_cannot_be_manipulated
```

### CRITICAL-002: Multiple Pending Payments

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | User checkout plan Pro | Invoice INV-001 created |
| 2 | User checkout plan Pro lagi | Redirect ke INV-001 |
| 3 | Cek database | Hanya 1 invoice pending |
| 4 | Checkout plan Business | Invoice INV-002 created (beda plan OK) |

**Test Command:**
```bash
php artisan test --filter=test_cannot_create_multiple_pending_payments
```

### CRITICAL-003: Midtrans Callback Bypass

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Akses `/checkout/midtrans/finish?transaction_status=settlement` | Cek ke API Midtrans |
| 2 | Tidak ada parameter | Tidak update status |
| 3 | Parameter palsu | Tidak update status |
| 4 | Bayar beneran via Midtrans | Status updated |

**Test Command:**
```bash
php artisan test --filter=test_midtrans_webhook
```

### CRITICAL-004: Secure File Upload

| Step | Action | Expected Result |
|------|--------|-----------------|
| 1 | Upload file > 5MB | Error: File too large |
| 2 | Upload file .exe | Error: Invalid file type |
| 3 | Upload valid .jpg | Success, filename random |
| 4 | Cek storage | Filename 40 chars random |

---

## 🎯 Manual Testing Scenarios

### Scenario 1: First-time User Checkout

```gherkin
Given user baru register
When user pilih plan Pro tahunan
And klik "Lanjutkan Pembayaran"
Then invoice created dengan:
  - amount: 5000000
  - duration_months: 12
  - status: pending
And redirect ke payment page
And tampil "Hemat Rp 1.000.000"
```

### Scenario 2: User dengan Pending Payment Login

```gherkin
Given user punya payment pending
When user logout dan login lagi
Then redirect ke payment page
And tampil flash message "Anda memiliki pembayaran pending"
And banner kuning muncul di dashboard
```

### Scenario 3: Payment Expired

```gherkin
Given user punya payment pending dari 2 hari lalu
When scheduler jalan: php artisan payments:cleanup-expired
Then status payment berubah jadi 'failed'
And user bisa buat invoice baru
```

---

## 🛠️ Testing Tools

### 1. PHPUnit (Built-in)

```bash
# Run all tests
php artisan test

# Run with colors
php artisan test --colors

# Stop on first failure
php artisan test --stop-on-failure

# Filter by name
php artisan test --filter=test_yearly_price

# Parallel testing (fast)
php artisan test --parallel
```

### 2. Laravel Dusk (Browser)

```bash
# Setup (satu kali)
composer require --dev laravel/dusk
php artisan dusk:install

# Run
php artisan dusk

# Run specific file
php artisan dusk tests/Browser/CheckoutFlowTest.php

# Run with screenshot on failure
php artisan dusk --browse
```

### 3. HTTP Client (VS Code)

Install extension: **REST Client** (humao.rest-client)

Buka file: `tests/ApiTesting/PaymentApiTest.http`

Klik "Send Request" di atas setiap request.

### 4. Postman Collection

Import dari file HTTP:
1. Postman → File → Import
2. Pilih `PaymentApiTest.http`
3. Set environment variables

---

## 📊 Coverage Report

```bash
# Generate coverage
php artisan test --coverage --min=80

# HTML Report
php artisan test --coverage --coverage-html=storage/app/coverage-report

# Buka report
start storage/app/coverage-report/index.html
```

**Target Coverage:**
- Models: 90%+
- Services: 85%+
- Controllers: 80%+
- Overall: 80%+

---

## 🚨 Common Issues

### Issue: "ChromeDriver not found"

```bash
# Download ChromeDriver sesuai versi Chrome
php artisan dusk:chrome-driver --detect

# Atau download manual dari:
# https://chromedriver.chromium.org/downloads
```

### Issue: "Database not found"

```bash
# Setup test database
php artisan migrate:fresh --env=testing

# Atau
php artisan test --create-db
```

### Issue: "Queue not processing"

```bash
# Run queue worker
php artisan queue:work --queue=default

# Atau sync driver untuk testing
# .env.testing: QUEUE_CONNECTION=sync
```

---

## 📁 Test Files Structure

```
tests/
├── Feature/
│   ├── CheckoutPriceTest.php       # Harga checkout
│   ├── SecurityPaymentTest.php     # Security fixes
│   └── PaymentFlowTest.php         # End-to-end flow
├── Browser/
│   └── CheckoutFlowTest.php        # Browser automation
├── ApiTesting/
│   └── PaymentApiTest.http         # API tests
└── Unit/
    └── (existing tests)
```

---

## ✅ Pre-Deployment Checklist

- [ ] All unit tests pass
- [ ] All feature tests pass
- [ ] Security tests pass
- [ ] Browser tests pass (jika pakai Dusk)
- [ ] Coverage > 80%
- [ ] Manual testing: Checkout flow
- [ ] Manual testing: Midtrans callback
- [ ] Manual testing: File upload
- [ ] Test di production: Buat real payment (kecil)

---

## 🔗 Useful Commands

```bash
# Tinker untuk quick test
php artisan tinker
>>> App\Models\Plan::first()

# Route list
php artisan route:list --name=checkout

# Check pending payments
php artisan tinker --execute="print_r(App\Models\Payment::pending()->get()->toArray())"

# Send test reminder
php artisan payments:send-reminders --hours=24 --dry-run

# Cleanup expired
php artisan payments:cleanup-expired --force
```

---

**Last Updated:** 2026-02-17
