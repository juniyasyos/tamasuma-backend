# 🚀 TAMASUMA Backend — Admin Data & API Penelitian Vokasi

Selamat datang di **TAMASUMA Backend**!  
Proyek ini adalah tulang punggung data untuk penelitian dosen di lingkungan **Sekolah Vokasi Universitas Jember**.  

Bayangkan sebuah pusat komando di mana semua domain pembelajaran, program, unit, mitra, hingga pengguna dapat dikelola dengan rapi. Dari situlah data penelitian diproses, disusun, hingga siap dipublikasikan ke aplikasi atau situs publik melalui **API yang konsisten, aman, dan mudah diintegrasikan**.

---

## 🎯 Visi Proyek

- Menyediakan **panel admin yang nyaman** untuk kurasi & pengelolaan konten pembelajaran.  
- Menjadi **sumber data terstandar** untuk riset, publikasi, dan kebutuhan audit.  
- Menghubungkan dosen, asisten peneliti, dan admin dalam satu alur kerja yang **efisien dan transparan**.  

---

## 📚 Apa yang Bisa Dilakukan Aplikasi Ini?

- 🗂 **Back-office lengkap**: Kelola Learning Area, Program, Unit, Material, Mitra, hingga pengguna dan perannya.  
- ✅ **Kurasi program**: Pilih mana yang dipublikasikan, tandai bersertifikat, atau hubungkan dengan sumber eksternal.  
- 🔐 **Sumber kebenaran**: Sediakan API *read-only* yang konsisten untuk pihak lain tanpa mengorbankan keamanan.  

---

## 🧩 Model Domain Inti

- **Learning Area** → payung dari beberapa Program.  
- **Program** → berisi Unit (materi), punya opsi sertifikat, publikasi, periode aktif, dan tautan eksternal.  
- **Enrollment** → catatan interaksi User–Program (status, progres, tanggal penting).  
- **Mitra** → organisasi yang bekerja sama dengan program/kegiatan.  
- ✨ *Tambahan teknis*: Slug otomatis + field audit → API lebih hemat payload & tetap rapi.  

---

## ⚙️ Stack Teknologi

Proyek ini dibangun dengan teknologi modern namun **sengaja dibuat sederhana dan terukur**:

- **Laravel 12 + PHP 8.2** → stabil, kaya fitur (queue, cache, scheduler, policy), minim dependensi.  
- **Filament v3** → panel admin CRUD cepat, konsisten, dan kaya plugin (RBAC, ekspor, profil).  
- **Filament Shield (RBAC)** → izin granular, generator otomatis untuk resource baru.  
- **Sanctum** → token & API aman, cocok untuk SPA.  
- **Socialite (Google SSO)** + 2FA → onboarding mudah, aman, minim reset password.  
- **Spatie Media Library** → manajemen file/media yang teruji.  
- **Dedoc Scramble** → dokumentasi API otomatis & selalu mutakhir.  
- **ApexCharts & Excel Export** → data & analitik instan untuk laporan riset.  
- **Docker (Sail)** → environment dev yang konsisten.  

---

## 🏛️ Prinsip Desain

- 🔒 API publik = **read-only** → menjaga integritas data riset.  
- 🛡️ Kebijakan akses = sama di **admin panel & API**.  
- 🧘 **Keep it simple**: monolit Laravel → menghindari kompleksitas mikroservis yang tidak perlu.  

---

## 🔐 Peran & Izin (RBAC)

Skema peran menerapkan prinsip least‑privilege. Nama permission mengikuti pola `aksi_entitas` (misal: `view_any_program`, `update_unit`). Sumber definisi awal ada di `database/seeders/ShieldSeeder.php:1`.

- Super Admin
  - Ruang lingkup: semua entitas (`role`, `token`, `user`, `program`, `unit`, `material`, `partner`, `learning_area`).
  - Aksi: seluruh aksi standar (view, view_any, create, update, restore, restore_any, replicate, reorder, delete, delete_any, force_delete, force_delete_any).
  - Tambahan: izin widget (statistik/grafik), akses dashboard super admin, dan izin khusus seperti `publish_program`, `unpublish_program`, `update_any_program`, `view_unpublished_program`.
  - Catatan: dapat mengelola role (lihat `app/Policies/RolePolicy.php:10`).

- Admin
  - Ruang lingkup: seluruh entitas konten (`program`, `unit`, `material`, `partner`, `learning_area`) + manajemen `user` dan `token`.
  - Aksi: hampir semua aksi standar, KECUALI varian `force_delete*` (dinonaktifkan sebagai hardening).
  - Tambahan: `update_any_program`, `view_unpublished_program`, `publish_program`, `unpublish_program`, izin widget, akses dashboard admin.
  - Batasan: tidak mengelola `role`.

- Pengajar
  - Ruang lingkup: `program`, `unit`, `material`.
  - Aksi: `view`, `view_any`, `create`, `update`, `replicate`, `reorder`.
  - Tambahan: dapat melihat konten draft (`view_unpublished_program`) untuk proses editing; akses widget dasar & dashboard pengajar.
  - Batasan: tidak dapat `delete`/`restore`/`force_delete`; tidak mengelola `user`, `token`, `partner`, `learning_area`, atau `role`.

- Pelajar
  - Ruang lingkup: konten (`program`, `unit`, `material`, `partner`, `learning_area`).
  - Aksi: hanya `view` dan `view_any` (read‑only).
  - Tambahan: `request_enrollment` (pengajuan enrolmen jika fitur dibuka), akses widget dasar & dashboard pelajar.
  - Batasan: tidak ada aksi tulis.

Konvensi Penting

- Publikasi Program: tindakan `publish_program`/`unpublish_program` mengubah `is_published`. Pengguna tanpa `view_unpublished_program` hanya melihat program terbit, enforced pada query tabel (lihat `app/Filament/Resources/ProgramResource/Table.php:24`).
- Policy: contoh `ProgramPolicy` mengizinkan melihat draft jika punya `view_unpublished_program` atau dapat `update` (lihat `app/Policies/ProgramPolicy.php:16`).

---

## 🚦 Cara Menjalankan Proyek

1. Clone repo ini:  
   ```bash
   git clone https://github.com/juniyasyos/tamasuma-backend.git
   cd tamasuma-backend

2. Install dependencies:

   ```bash
   ./vendor/bin/sail composer install
   ```
3. Generate role & permission:

   ```bash
   ./vendor/bin/sail artisan shield:generate --all
   ```
4. Buat super admin:

   ```bash
   ./vendor/bin/sail artisan shield:super-admin
   ```
5. Jalankan server:

   ```bash
   ./vendor/bin/sail composer run dev
   ```

> ⚠️ Pastikan `APP_DEBUG=false` di `.env` sebelum deploy ke production.

---

## 📖 Dokumentasi Terkait

* Endpoint API → [`docs/api/learning-areas.md`](docs/api/learning-areas.md)
* Admin panel → `app/Providers/Filament/AdminPanelProvider.php`
* Model inti → `app/Models/Program.php`, `LearningArea.php`, `Unit.php`

---

## 🤝 Kontribusi

Kami sangat terbuka untuk kontribusi!
Silakan fork repo ini, buat branch baru, lalu ajukan **Pull Request**.

---

## 🙏 Terima Kasih

* [Laravel](https://laravel.com/)
* [FilamentPHP](https://filamentphp.com/)
* [Semua kontributor](https://github.com/juniyasyos/tamasuma-backend/graphs/contributors)

---

## 💬 Support & Komunitas

* 🐛 [Laporkan bug](https://github.com/juniyasyos/tamasuma-backend/issues)
* 💡 [Ajukan fitur](https://github.com/juniyasyos/tamasuma-backend/issues)
* 📧 Email: [juniyasyos@gmail.com](mailto:juniyasyos@gmail.com)
* 💬 [WhatsApp](https://chat.whatsapp.com/+6285732431396)

---

## ⭐ Dukung Proyek Ini

Jika project ini membantu, jangan lupa kasih **⭐ di GitHub**.
Semakin banyak bintang, semakin semangat kami mengembangkan TAMASUMA! ✨

```
