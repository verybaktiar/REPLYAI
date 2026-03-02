# Panduan Menambahkan Logo Partner

## Lokasi Folder
```
public/images/partners/
```

## Logo yang Diperlukan

### 1. Inovas Marketing Management
- **Nama file:** `inovas.png`
- **Ukuran ideal:** 200x200 pixel
- **Format:** PNG dengan background transparan
- **Letakkan di:** `public/images/partners/inovas.png`

### 2. iNDS.id
- **Nama file:** `inds.png`
- **Ukuran ideal:** 200x200 pixel
- **Format:** PNG dengan background transparan
- **Letakkan di:** `public/images/partners/inds.png`

## Cara Menambahkan

### Opsi 1: Copy File Langsung
1. Siapkan gambar logo dengan format PNG
2. Rename sesuai nama file di atas
3. Copy ke folder `public/images/partners/`

### Opsi 2: Upload via FTP/File Manager
1. Akses folder `public/images/partners/`
2. Upload file logo ke folder tersebut

### Opsi 3: Command Line (jika ada akses SSH)
```bash
cd /path/to/replyai/public/images/partners
# Upload via scp atau wget
wget https://domain.com/logo-inovas.png -O inovas.png
wget https://domain.com/logo-inds.png -O inds.png
```

## Menambah Partner Baru

### Step 1: Tambahkan Logo
Copy logo ke folder `public/images/partners/nama-partner.png`

### Step 2: Edit File
Buka `resources/views/landingpage.blade.php`

### Step 3: Tambahkan Code
Tambahkan card partner baru sebelum `<!-- Slot untuk partner tambahan -->`:

```html
<!-- Partner 3: Nama Partner -->
<div class="partner-card">
    <div class="partner-logo">
        @if(file_exists(public_path('images/partners/nama-partner.png')))
            <img src="{{ asset('images/partners/nama-partner.png') }}" alt="Nama Partner" loading="lazy">
        @else
            <div class="partner-initial">NP</div>
        @endif
    </div>
    <div class="partner-info">
        <h3>Nama Partner</h3>
        <p>Kategori/Deskripsi</p>
    </div>
</div>
```

### Step 4: Clear Cache
```bash
php artisan view:clear
```

## Fallback Otomatis
Jika logo belum di-upload, sistem akan menampilkan:
- **Inisial** (2 huruf) sebagai pengganti logo
- Contoh: "IN" untuk Inovas, "ID" untuk iNDS.id

## Tips
- Gunakan logo dengan background transparan untuk tampilan terbaik
- Ukuran file tidak lebih dari 100KB untuk loading cepat
- Pastikan logo terlihat jelas di ukuran 80x80 pixel
