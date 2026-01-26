# ⚠️ PENTING: Aturan Template ReplyAI

## 🔒 JANGAN MENGUBAH TEMPLATE UI!

Template UI ReplyAI menggunakan **FULL DARK THEME**. 

### Aturan Wajib:

1. **JANGAN** install Laravel Breeze, Jetstream, atau starter kit lain yang mengubah views
2. **JANGAN** menggunakan kelas `bg-white` atau varian light lainnya
3. **SELALU** gunakan warna dark theme:
   - Background: `bg-background-dark` (#101622)
   - Surface/Cards: `bg-surface-dark` (#1a2230)
   - Border: `border-slate-700` atau `border-slate-800`
   - Text: `text-white` atau `text-slate-300/400`

### Warna Primary Palette:
```css
primary: #135bec
background-dark: #101622
surface-dark: #1a2230
```

### Jika Butuh Auth:
- Buat AuthController manual
- Buat views auth dengan dark theme yang sama
- JANGAN gunakan Breeze/Jetstream karena akan menimpa template

### File Penting:
- `resources/views/components/sidebar.blade.php` - Sidebar navigation
- `resources/views/pages/dashboard/replyai.blade.php` - Dashboard utama
- `resources/views/pages/*` - Semua halaman

---

## 📅 Catatan Perubahan

**17 Januari 2026:**
- Laravel Breeze sempat diinstall dan menimpa beberapa views
- Template di-restore dari backup
- Semua `bg-white dark:bg-*` diganti menjadi dark-only
- Template sekarang kembali FULL DARK

---

*Dokumen ini dibuat untuk mencegah perubahan template yang tidak diinginkan.*
