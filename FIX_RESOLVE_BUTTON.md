# Fix: Resolve Button Blank Page & WhatsApp Auto-Reply

## Problem Summary
1. **Resolve button** di AI Analytics Dashboard menyebabkan halaman putih/blank
2. **WhatsApp auto-reply** tidak bekerja untuk kontak baru

## Root Cause

### Resolve Button Issue
- Form submit secara tradisional tanpa CSRF token yang benar
- Redirect response menyebabkan halaman blank di beberapa browser

### WhatsApp Auto-Reply Issue
- `WaConversation` dibuat dengan `status` default `null` atau `STATUS_HANDOVER`
- Seharusnya default ke `STATUS_BOT_ACTIVE` agar bot langsung merespon

## Changes Made

### 1. AI Analytics Dashboard (resources/views/admin/analytics/index.blade.php)

#### Tombol Resolve
```blade
<!-- Sebelumnya: Form submit tradisional -->
<form action="..." method="POST">...</form>

<!-- Sesudah: Button dengan onclick handler -->
<button onclick="resolveQuery({{ $query->id }})" ...>Resolve</button>
```

#### Tombol Ignore
```blade
<!-- Sebelumnya: Form submit tradisional -->
<form action="..." method="POST">...</form>

<!-- Sesudah: Button dengan onclick handler -->
<button onclick="ignoreQuery({{ $query->id }})" ...>Ignore</button>
```

#### JavaScript Functions
```javascript
function resolveQuery(queryId) {
    // Tampilkan modal
    const form = document.getElementById('resolveForm');
    form.action = `/admin/analytics/missed-query/${queryId}/resolve`;
    document.getElementById('resolveModal').classList.remove('hidden');
}

function submitResolveForm() {
    // Submit via AJAX dengan fetch API
    fetch(form.action, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (response.ok) {
            window.location.reload();
        }
    });
}

function ignoreQuery(queryId) {
    // Submit via AJAX dengan CSRF token
    const formData = new FormData();
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
    formData.append('_method', 'PATCH');
    
    fetch(`/admin/analytics/missed-query/${queryId}/ignore`, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (response.ok) {
            window.location.reload();
        }
    });
}
```

### 2. Controller Methods (app/Http/Controllers/Admin/AnalyticsController.php)

Sudah support AJAX response:
```php
public function resolveMissedQuery(Request $request, KbMissedQuery $query)
{
    // ... update query ...
    
    // Return JSON if AJAX request
    if ($request->wantsJson() || $request->ajax()) {
        return response()->json([
            'success' => true,
            'message' => 'Query marked as resolved.'
        ]);
    }
    
    return redirect()->back()->with('success', ...);
}

public function ignoreMissedQuery(Request $request, KbMissedQuery $query)
{
    // ... update query ...
    
    // Return JSON if AJAX request
    if ($request->wantsJson() || $request->ajax()) {
        return response()->json([
            'success' => true,
            'message' => 'Query ignored.'
        ]);
    }
    
    return redirect()->back()->with('success', ...);
}
```

### 3. WhatsApp Webhook Controller (app/Http/Controllers/WhatsAppWebhookController.php)

```php
$waConversation = WaConversation::firstOrCreate(
    ['phone_number' => $message->phone_number, 'user_id' => $message->user_id],
    [
        'display_name' => $message->push_name,
        'session_status' => WaConversation::SESSION_ACTIVE,
        'status' => WaConversation::STATUS_BOT_ACTIVE  // ← Critical fix!
    ]
);
```

## Testing Steps

### Test Resolve Button
1. Buka AI Analytics Dashboard
2. Pastikan ada missed query dengan status "pending"
3. Klik tombol "Resolve"
4. Pilih KB article dari dropdown
5. Klik "Resolve" di modal
6. ✅ Page harus reload tanpa blank screen
7. ✅ Query status berubah menjadi "resolved"

### Test Ignore Button
1. Buka AI Analytics Dashboard
2. Pastikan ada missed query dengan status "pending"
3. Klik tombol "Ignore"
4. Konfirmasi dialog
5. ✅ Page harus reload tanpa blank screen
6. ✅ Query status berubah menjadi "ignored"

### Test WhatsApp Auto-Reply
1. Kirim pesan WhatsApp ke nomor bot dari nomor yang belum pernah chat
2. ✅ Bot harus langsung merespon tanpa greeting "Maaf kak, maksudnya..."
3. ✅ Response harus relevan dengan konteks percakapan

## Files Modified
- `resources/views/admin/analytics/index.blade.php` - AJAX buttons & JS functions
- `app/Http/Controllers/WhatsAppWebhookController.php` - Default status bot_active
- `app/Services/AiAnswerService.php` - Greeting suppression for ongoing conversations

## Notes
- CSRF token tersedia di layout `dark.blade.php` via meta tag
- Route sudah tersedia di `routes/web.php`
- Controller methods sudah support AJAX response
