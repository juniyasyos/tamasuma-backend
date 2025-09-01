# Spesifikasi Achievement: Akses, Otomatisasi, dan Visibilitas

Dokumen ini merinci rancangan fitur Achievement (Pencapaian) mencakup kontrol akses per peran, pembuatan otomatis (auto-award), pengelolaan manual, serta pengaturan visibilitas untuk profil publik.

## Ringkasan Tujuan
- Admin & Super Admin: melihat dan mengelola seluruh Achievement.
- Pelajar: dapat membuat Achievement miliknya sendiri, tapi tidak bisa mengubah yang dibuat otomatis atau oleh admin.
- Otomatisasi: sistem menganugerahkan Achievement pada peristiwa tertentu (trigger).
- Visibilitas: setiap Achievement memiliki status `public | private | unlisted` untuk profil publik.
- Filament Resource: `AchievementResource` dipakai untuk mengatur Achievement milik pengguna lain, dan hanya muncul untuk role tertentu (admin/super_admin).

> Catatan Proses: Untuk pekerjaan berskala besar, kita akan selalu menyiapkan outline implementasi dan menunggu persetujuan sebelum eksekusi. Perbaikan kecil (bugfix ringan) dapat langsung dilakukan tanpa outline terpisah.

## Terminologi
- Achievement: entitas pencapaian pengguna (sertifikat, penghargaan, pelatihan, dll.).
- Self: Achievement yang dibuat sendiri oleh pemilik (pelajar).
- Admin-managed: Achievement yang dibuat/diubah oleh admin/super_admin.
- Auto: Achievement yang dibuat otomatis oleh sistem berdasarkan trigger.

## Keputusan Sertifikat (Certificate)
- Satu model: Sertifikat ditangani sebagai kategori `certificate` di dalam `Achievement` (bukan model terpisah).
- Alasan: Menjaga alur RBAC, policy, UI Filament, widget, dan auto-award tetap sederhana serta konsisten.
- Kapan dipisah di masa depan: Jika dibutuhkan alur khusus (penomoran unik/serial, pencabutan/revocation, verifikasi publik via QR, multi-penandatangan, re-issue/riwayat versi, template desain kompleks). Jika kebutuhan ini muncul, kita dapat membuat model `Certificate` berdampingan atau 1:1 dengan `Achievement`.

## Peran & Hak Akses (RBAC)
- Super Admin, Admin:
  - Lihat semua, buat, ubah, hapus, pulihkan, reorder.
  - Menggunakan `AchievementResource` untuk kelola lintas pengguna.
- Pelajar:
  - Lihat hanya miliknya sendiri di panel (list dan detail).
  - Boleh membuat Achievement “Self”.
  - Boleh mengubah/menghapus hanya Achievement “Self”.
  - Tidak boleh mengubah Achievement “Auto” atau “Admin-managed”.
  - Tidak melihat menu navigasi `AchievementResource`.

## Skema Data & Perubahan Model
Field yang sudah ada: `user_id, title, category, issuer, achieved_at, proof_image, url, description, is_featured, visibility, tags`.

Tambahan yang disarankan untuk mendukung aturan di atas:
- created_by_id (nullable, FK ke users): siapa yang membuat pertama kali (admin/pemilik/sistem).
- created_via (enum string): `self | admin | auto` (default `self`).
- provenance kolom referensi (opsional, untuk auto-award):
  - source_type (nullable string), contoh: `Enrollment`.
  - source_id (nullable bigint), ID sumber.

Opsional khusus kategori `certificate` (tanpa wajib migrasi awal, bisa ditambahkan bertahap bila diperlukan):
- credential_id (string nullable): nomor/ID sertifikat.
- expires_at (date nullable): masa berlaku.
- verification_code (string nullable, unique): kode verifikasi publik.
- metadata (json nullable): payload fleksibel untuk informasi tambahan.

Catatan implementasi:
- `visibility`: dipakai untuk filter di profil publik; `public` muncul, `private` tidak, `unlisted` diakses via tautan khusus (bisa diimplement tahap berikutnya).
- `tags`: cast ke array.
- Index: `user_id`, `visibility`, `category`, kombinasi (`source_type`, `source_id`, `user_id`) untuk mencegah duplikasi auto-award.

## Permission & Policy
Gunakan Spatie Permission. Selain permission CRUD standar dari Filament/Shield, tambahkan permission ber-scope “self”:

Permission standar (untuk admin/super_admin):
- `view_any_achievement`, `view_achievement`, `create_achievement`, `update_achievement`, `delete_achievement`, `restore_achievement`, `force_delete_achievement`, `reorder_achievement`.

Permission self-scope (untuk pelajar):
- `view_achievement_self`, `create_achievement_self`, `update_achievement_self`, `delete_achievement_self`.

Aturan Policy (garis besar):
- viewAny:
  - Admin/Super Admin: true (via `view_any_achievement`).
  - Pelajar: false untuk `AchievementResource` (mereka pakai halaman “My Achievements”, bukan resource global).
- view (detail):
  - Admin/Super Admin: true.
  - Pelajar: hanya jika `record.user_id == user.id`. Untuk profil publik, akses diatur oleh `visibility` pada endpoint publik (di luar panel admin).
- create:
  - Admin/Super Admin: true.
  - Pelajar: true hanya untuk `created_via = self` (di UI dipaksa demikian).
- update/delete:
  - Admin/Super Admin: true.
  - Pelajar: hanya jika `record.user_id == user.id` DAN `record.created_via == 'self'` (Achievement otomatis/admin tidak bisa diubah).

Mapping Role → Permission (disarankan):
- super_admin, admin: semua permission standar Achievement.
- pelajar: hanya permission self-scope.

## Strategi UI / UX (Filament)
1) AchievementResource (khusus admin/super_admin)
- Tampil di navigasi hanya untuk role admin/super_admin.
- Query menampilkan semua data.
- Form mencakup `user_id`, `title`, `category`, `issuer`, `achieved_at`, `proof_image`, `url`, `description`, `visibility`, `tags`, `is_featured`.
- Kolom sistem (`created_via`, `created_by_id`, `source_type`, `source_id`) hanya terlihat/diatur untuk admin; untuk auto-award, bersifat read-only atau hidden.

2) Self-service “My Achievements” (khusus pelajar)
- Tampilan list + form sederhana untuk membuat dan mengelola Achievement milik sendiri.
- Create selalu menset `created_via = self`, `created_by_id = auth()->id()`.
- Update/Delete hanya mengizinkan record dengan `created_via = self`.
- Tidak ada akses ke Achievement milik user lain.

3) Widget “Recent Achievements”
- Sudah ada ringkasan (top-N) milik user. Tetap dipakai untuk konteks dashboard pelajar.

4) Penanganan Berkas Sertifikat
- Rekomendasi: manfaatkan Spatie Media Library pada model `Achievement` (sudah tersedia di project melalui plugin Filament) untuk menyimpan PDF/gambar sertifikat, termasuk dukungan thumbnail/preview.
- Alternatif minimal: gunakan `FileUpload` bawaan Filament dengan `acceptedFileTypes` mencakup `application/pdf` dan gambar umum, bila belum ingin mengaktifkan Media Library di `Achievement`.

## Trigger Otomatis (Auto-award)
Tujuan: sistem membuat Achievement secara otomatis saat event tertentu terjadi. Tahap awal fokus pada alur Program/Enrollment.

Trigger awal yang disarankan:
1) Enrollment Completed → Award “Program Selesai”
- Saat `enrollments.status` berubah menjadi `completed`, sistem membuat Achievement:
  - title: `Selesai Program: {Nama Program}`
  - category (default): `certificate`
  - issuer: nama penyelenggara program (jika ada)
  - achieved_at: `completed_at`
  - created_via: `auto`
  - created_by_id: null (atau system user jika dipakai)
  - source_type: `Enrollment`, source_id: ID terkait (untuk mencegah duplikasi).

2) (Opsional) Enrollment Approved/Started → Award “Memulai Program”
- Saat `status` menjadi `active` (atau `approved/enrolled_at` di-set), buat Achievement “Memulai Program: {Nama Program}`”.

Arsitektur layanan:
- Service: `App\Services\Achievements\AutoAwardService`
  - `awardProgramCompleted(Enrollment $enrollment)`
  - `awardProgramStarted(Enrollment $enrollment)` (opsional)
  - Bertanggung jawab melakukan upsert idempoten (menghindari duplikasi via kombinasi kunci `user_id + source_type + source_id + type`).
- Integrasi: dipanggil dari `EnrollmentObserver@updated` ketika mendeteksi perubahan status.

## Visibilitas di Profil Publik
- `public`: Achievement terlihat di profil publik pengguna.
- `private`: tidak terlihat di profil publik; tetap terlihat di panel pemilik dan admin.
- `unlisted`: tidak ditampilkan di daftar publik, tetapi dapat dibagikan via URL khusus (tahap berikutnya jika diperlukan).

Endpoint publik (tersedia):
- Route: `GET /u/{user}/portfolio` → `PortfolioController@show`
- View: `resources/views/portfolio/show.blade.php`
- Query: hanya `visibility = public`, urut terbaru (`achieved_at desc`), paginasi 12.

## Migrasi & Kebutuhan Data
Tambahan migrasi untuk provenance & enum asal pembuatan:
- Tambah kolom ke tabel `achievements`:
  - `created_by_id` (nullable foreignId ke users, index)
  - `created_via` (string, default `self`, index) — nilai: `self|admin|auto`
  - `source_type` (nullable string, index)
  - `source_id` (nullable bigInteger, index)

Model `Achievement`:
- Tambahkan ke `$fillable`: `visibility`, `tags`, `created_by_id`, `created_via`, `source_type`, `source_id`.
- Tambahkan ke `$casts`: `tags` => `array`.

Seeder/Permission setup:
- Tambahkan permission self-scope dan mapping ke role pelajar.
- Pastikan admin/super_admin memiliki permission penuh Achievement.

## Acceptance Criteria
- Admin/super_admin:
  - Melihat menu `Pencapaian` (AchievementResource) dan bisa CRUD seluruh data.
- Pelajar:
  - Tidak melihat menu `Pencapaian` global.
  - Memiliki halaman “My Achievements” untuk list, create, update, delete miliknya sendiri.
  - Tidak bisa mengubah/menghapus Achievement `created_via != self`.
- Otomatisasi:
  - Saat Enrollment menjadi `completed`, Achievement “Program Selesai” dibuat otomatis sekali saja per sumber.
- Visibilitas:
  - Hanya Achievement `public` yang tampil di profil publik.

## Rencana Implementasi (Tahap Bertahap)
1) Data & Model
   - Tambah kolom provenance (migrasi) + update `$fillable` & `$casts` model `Achievement`.
2) Permission & Policy
   - Tambah permission self-scope, mapping role, dan logika policy update/delete berbasis `created_via`.
3) Filament Resource (admin)
   - Batasi navigasi hanya admin/super_admin; form lengkap termasuk assignment `user_id`.
4) Self-service “My Achievements” (pelajar)
   - Tambah page/resource khusus user saat ini dengan pembatasan update/delete ke `created_via = self`.
5) Auto-award
   - Tambah `AutoAwardService` dan panggil dari `EnrollmentObserver` pada status `completed` (dan opsional `active`).
6) Publikasi Profil
   - Pastikan endpoint/halaman publik hanya menampilkan `visibility = public`.
   - Status: SUDAH ADA (`/u/{user}/portfolio`).
7) QA & Dokumentasi
   - Uji peran/akses, idempoten auto-award, dan visibilitas publik.

## Outline Implementasi (Butuh Persetujuan Sebelum Eksekusi)
Batch A — Fondasi Data & Akses
- A1: Migrasi kolom provenance (`created_by_id`, `created_via`, `source_type`, `source_id`).
- A2: Update model `Achievement` (`$fillable`, `$casts`).
- A3: Revisi `AchievementPolicy` untuk aturan self vs admin dan blokir update/delete untuk `created_via != self` bagi pelajar.

Batch B — Admin Panel & Self-Service
- B1: Batasi `AchievementResource` hanya untuk admin/super_admin (navigasi + gate/Panel). 
- B2: Tambah/rapikan form `AchievementResource` (termasuk `user_id`, `visibility`, `tags`, media upload).
- B3: Buat halaman “My Achievements” untuk pelajar (list + create/update/delete yang hanya untuk `self`).

Batch C — Auto-award & Media
- C1: Buat `AutoAwardService` dan integrasikan di `EnrollmentObserver` untuk status `completed` (opsional `active`).
- C2: Pilih mekanisme berkas: aktifkan Media Library pada `Achievement` atau pakai `FileUpload` minimal (dengan dukungan PDF).

Batch D — Publik & QA
- D1: Endpoint/halaman profil publik menampilkan hanya `visibility = public`.
- D2: QA keseluruhan alur dan dokumentasi akhir.

Status Batch D:
- D1: Done — halaman portofolio publik tersedia dan memfilter `visibility = public`.
- D2: Pending — lakukan QA singkat (lihat checklist di bawah) dan finalisasi dokumentasi jika ada temuan.

QA Checklist Singkat:
- Buat 2 Achievement milik user A: satu `public`, satu `private`.
- Buka `/u/{idA}/portfolio` → hanya item `public` yang tampil.
- Selesaikan sebuah Enrollment → pastikan auto-award tercipta (`created_via=auto`, `visibility=private`) dan tidak tampil di portofolio hingga diubah ke `public` secara manual oleh admin/pemilik.
- Pastikan pelajar tidak melihat `AchievementResource` di navigasi, namun bisa mengelola “Pencapaian Saya”.

Mohon konfirmasi batch mana yang ingin dieksekusi terlebih dahulu. Setelah disetujui, saya lanjut implementasi sesuai batch tersebut.

## Risiko & Mitigasi
- Duplikasi Achievement auto-award → gunakan kunci unik logis (`user_id + source_type + source_id + type`).
- Eskalasi hak pelajar via UI → enforce di Policy dan validasi server-side (bukan hanya UI).
- Kompleksitas permission → sederhanakan dengan dua kelompok permission: penuh (admin/super_admin) dan self-scope (pelajar).

## Pertanyaan Terbuka
- Nama pasti role: apakah `admin` dan `super_admin` sesuai seeder saat ini? (Atau `administrator`?).
- Judul/Kategori default untuk auto-award program selesai (mis. `certificate` vs `training`).
- Apakah perlu “unlisted share link” sekarang atau ditunda?

---

Dokumen ini menjadi acuan implementasi. Setelah disetujui, langkah pertama adalah menambah migrasi provenance + update Policy, lalu menambahkan halaman “My Achievements” untuk pelajar, dan terakhir mengaktifkan auto-award pada status Enrollment `completed`.
