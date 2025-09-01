# Refactor AchievementResource: Tabel & Form UX

Tujuan: meningkatkan produktivitas admin/super_admin mengelola Achievement lintas pengguna, serta memperbaiki discoverability dan kecepatan aksi umum (ubah visibility, tandai featured, filter kaya, export). Tidak ada perubahan skema database pada tahap ini.

## Target & Batasan
- Target: Resource admin (bukan halaman "Pencapaian Saya").
- Batasan: tanpa migrasi baru; memanfaatkan kolom yang sudah ada.
- Peran: hanya terlihat untuk `super_admin`/`Admin` (atau yang berizin setara).

## Perbaikan Tabel (List)
- Query & Sort:
  - Urutan: unggulkan `is_featured desc`, lalu `achieved_at desc` (via `modifyQueryUsing`).
  - Pencarian: aktif di judul, issuer, category.
- Kolom:
  - Gambar bukti: preview (tidak circular), klik untuk membuka modal preview.
  - `user.name`: tampilkan pemilik (admin-only column), clickable untuk filter by owner.
  - `title`, `category` (badge), `issuer`, `achieved_at` (tanggal), `visibility` (badge warna), `created_via` (badge: self/admin/auto).
  - `is_featured`: `ToggleColumn` untuk toggle cepat.
- Filter:
  - Kategori (SelectFilter), Visibility (SelectFilter), Tanggal (date range), Featured (ternary),
  - Pemilik (SelectFilter dari users) — hanya admin.
  - Tag (MultiSelect → `whereJsonContains('tags', value)`), opsional: multi-select OR.
  - Created Via (self/admin/auto).
- Actions:
  - Row: Edit, Delete, Duplicate (Replicate),
  - Quick “Set Visibility” (public/private/unlisted) via small modal.
  - “Lihat Portofolio Publik” → buka `/u/{user_id}/portfolio` (di tab baru).
- Bulk Actions:
  - Export (pakai `pxlrbt/filament-excel`) → CSV/XLSX kolom terpilih.
  - Bulk Set Visibility, Bulk Set Category, Bulk Delete.
- Empty state: teks ringkas + tombol Create.

## Perbaikan Form (Schema)
- Tata letak Tabs: Detail | Media | Metadata | Visibilitas
  - Detail: `user_id` (admin-only), `title`, `category` (button group), `issuer`, `achieved_at`, `url`.
  - Media: `proof_image` (gambar/PDF), preview height, downloadable/openable.
  - Metadata: `description`, `tags` (multiple, tags input), `is_featured`.
  - Visibilitas: `visibility` (toggle buttons) + info bantuan tentang profil publik.
- Validasi & UX:
  - `url` tipe URL, `title` required, `category` default `certificate`.
  - `proof_image` menerima `image/*, application/pdf`.
  - Bantuan kontekstual (helperText) singkat untuk `visibility` dan `tags`.

## Implementasi Teknis (Rencana)
1) Table builder
   - `modifyQueryUsing()` untuk multi-order.
   - Tambah kolom `ToggleColumn::make('is_featured')`.
   - Tambah kolom `TextColumn::make('created_via')->badge()` dengan warna map: self=gray, admin=info, auto=success.
   - Tambah action `setVisibility` (row + bulk) dengan `ToggleButtons` sederhana.
   - Tambah action `viewPortfolio` (external url `/u/{user_id}/portfolio`).
   - Tambah filter `owner`, `visibility`, `created_via`, `tags` (JSON contains), `category`, `date_range`.
   - Tambah `ExportBulkAction` (filament-excel) dengan mapping kolom dasar.

2) Form builder
   - Refactor ke `Tabs` dengan 4 tab sesuai di atas.
   - `Select::make('user_id')` hanya terlihat untuk admin.
   - `Select::make('category')` gunakan button group (`->native(false)` + `->options()` + `->live()` untuk responsif).
   - `FileUpload::make('proof_image')` terima PDF + gambar, preview tinggi 150, downloadable/openable.
   - `ToggleButtons::make('visibility')` dengan ikon (eye/lock/link).

3) My Achievements (Pelajar) — Alur Create
- Alih‑alih modal Create di halaman tabel, tombol “Tambah Pencapaian” mengarah ke halaman form terpisah:
  - Page: `App\Filament\Pages\CreateMyAchievement` (hidden dari navigasi, akses melalui tombol dari `MyAchievements`).
  - Form: sama seperti form pelajar sebelumnya, tanpa field `user_id` (otomatis `Auth::id()`).
  - Submit: membuat record dan redirect kembali ke `MyAchievements` dengan notifikasi sukses.
- Hal ini meningkatkan UX untuk form panjang (upload + deskripsi) dan mengurangi rasa “sempit” di modal.

3) Kualitas & Akses
   - Pastikan kolom/aksi admin-only tetap dibungkus guard pengguna.
   - Pertahankan kebijakan policy (pelajar tidak dapat mengedit non-self).

## Catatan Integrasi
- Export membutuhkan paket `pxlrbt/filament-excel` (sudah ada di composer). Kita akan gunakan `ExportBulkAction` dari paket tersebut.
- Link portofolio publik mengarah ke daftar, bukan halaman detail per Achievement (belum ada). Cukup untuk konteks saat ini.

## Acceptance Criteria
- Admin dapat:
  - Filter cepat berdasarkan kategori, visibility, pemilik, tanggal, tags, created_via.
  - Toggle featured langsung dari tabel.
  - Ubah visibility per-row dan bulk.
  - Export data terpilih ke Excel/CSV.
- Form lebih terstruktur via tabs.
- Tanpa migrasi baru; kompatibel dengan data saat ini.
- Untuk Pelajar: tombol “Tambah Pencapaian” membuka halaman khusus Create (bukan modal), lalu kembali ke daftar setelah berhasil.

## Permintaan Persetujuan
- Jika disetujui, saya akan refactor `app/Filament/Resources/AchievementResource.php` sesuai rencana di atas dalam satu commit terfokus, tanpa mengubah resource lain.
