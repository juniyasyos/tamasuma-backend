# 001 - Tugas Optimisasi Resource Filament

## Langkah-langkah yang akan dilakukan

1. **Identifikasi Resource yang kompleks**
   - Telusuri `app/Filament/Resources` untuk menemukan resource yang memiliki kombinasi `form()` dan `table()` panjang.
2. **Pemisahan Table**
   - Buat kelas baru misalnya `app/Filament/Resources/<Resource>/Table.php` atau direktori serupa.
   - Pindahkan konfigurasi kolom, filter, action, dan bulk action dari metode `table()` ke kelas baru tersebut.
3. **Pemisahan Form Schema**
   - Buat kelas schema misalnya `app/Filament/Resources/<Resource>/Schema.php`.
   - Pindahkan elemen-elemen form (field, section) dari metode `form()` ke kelas baru.
4. **Integrasikan kembali pada Resource**
   - Ubah resource utama agar menggunakan kelas Table dan Schema baru dengan memanggilnya di dalam `table()` dan `form()`.
5. **Penyesuaian Namespace dan Autoload**
   - Sesuaikan namespace serta autoload Composer jika dibutuhkan agar kelas baru dikenali.
6. **Pengujian**
   - Jalankan test otomatis `vendor/bin/pest` untuk memastikan tidak ada error akibat refaktor.
   - Uji manual fungsi Resource pada panel Filament jika diperlukan.
7. **Dokumentasi**
   - Tambahkan catatan pada README atau dokumentasi internal mengenai struktur baru.

