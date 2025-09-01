# Breadcrumbs UX Standardization – Outline (All Resources & Pages, excluding Settings)

Tujuan: Menambahkan breadcrumbs yang konsisten di seluruh Resource dan Page (kecuali menu/panel Settings) untuk meningkatkan orientasi pengguna dan mengurangi kebingungan navigasi.

## Ringkasan
- Berlaku untuk semua Filament Resource (List/Create/Edit/View) dan Page kustom.
- Dikecualikan: halaman/panel yang berada di kelompok/navigasi “Settings” atau terkait plugin pengaturan.
- Pola breadcrumbs konsisten: Dashboard → [Group] → Resource/Page → (Subaksi) → (Record Title)

## Ruang Lingkup
- Resource target (sementara):
  - AchievementResource (List/Create/Edit)
  - ProgramResource (List/Create/Edit/View)
  - LearningAreaResource (List/Create/Edit)
  - PartnerResource (List/Create/Edit)
  - UserResource (List/Create/Edit/View)
  - RoleResource (List/Create/Edit)
- Page kustom:
  - MyAchievements, CreateMyAchievement
  - Dashboard (sebagai root breadcrumb)
- Dikecualikan:
  - Semua halaman di navigationGroup “Settings”
  - Halaman My Profile (Breezy)
  - Halaman plugin Settings Hub

## Prinsip Breadcrumbs
- Dashboard selalu menjadi root (link ke halaman dashboard panel aktif).
- Resource:
  - List: Dashboard → [Group] → [Plural Label]
  - Create: Dashboard → [Group] → [Plural Label] → Create
  - Edit: Dashboard → [Group] → [Plural Label] → Edit: {Record Title}
  - View (jika ada): Dashboard → [Group] → [Plural Label] → {Record Title}
- Page Kustom:
  - MyAchievements: Dashboard → Pencapaian Saya
  - CreateMyAchievement: Dashboard → Pencapaian Saya → Tambah

## Implementasi Teknis (Rencana)
1) Utilitas & Trait
- Tambah helper `App\Filament\Support\Breadcrumbs` (static methods):
  - `panelDashboardUrl()` – resolve URL dashboard panel aktif.
  - `resourceIndexCrumb($resourceClass)` – label+url untuk index resource.
  - `recordTitle($record, $field = 'title')` – fallback aman untuk judul.
- Tambah trait `App\Filament\Support\Concerns\HasStandardBreadcrumbs` untuk dipakai di Page kustom.

2) Resource Pages (List/Create/Edit/View)
- Override breadcrumb minimalis sesuai Filament v3:
  - `public function getBreadcrumbs(): array` (Page turunan `ListRecords`, `CreateRecord`, `EditRecord`, `ViewRecord`).
  - Gunakan helper untuk mengisi label dan URL.
  - Untuk Edit/View: ambil judul record secara singkat (mis. 30 karakter, `Str::limit`).

3) Page Kustom
- Implementasi `getBreadcrumbs()` pada `MyAchievements` dan `CreateMyAchievement`.
- Gunakan helper `panelDashboardUrl()` sebagai root.

4) Pengecualian Settings
- Deteksi lewat `static::$navigationGroup === 'Settings'` pada Resource/Page.
- Pada Resource/Page yang berada pada grup Settings: skip/return breadcrumbs default Filament (atau array kosong) agar tidak tampil.

5) Konsistensi i18n
- Label crumb pakai label navigasi yang sudah ada: `static::$navigationLabel`, `static::$pluralModelLabel`/`static::getPluralModelLabel()`.
- Kata “Create”, “Edit”, “View” bisa diubah jadi bahasa Indonesia: “Tambah”, “Ubah”, “Detail”.

## Contoh Pola Kode (Ringkas)
- Pada Edit Achievement (turunan `EditRecord`):
```php
public function getBreadcrumbs(): array
{
    $dashboard = [__('Dashboard') => Breadcrumbs::panelDashboardUrl()];
    $index = [static::getResource()::getPluralLabel() => static::getResource()::getUrl('index')];
    $title = \Illuminate\Support\Str::limit($this->record->title ?? (string) $this->record->getKey(), 30);
    return $dashboard + $index + [__('Ubah') . ': ' . $title => null];
}
```
- Pada MyAchievements (turunan `Page`):
```php
public function getBreadcrumbs(): array
{
    return [__('Dashboard') => Breadcrumbs::panelDashboardUrl(), __('Pencapaian Saya') => null];
}
```

## Acceptence Criteria
- Semua resource/page (non-Settings) menampilkan breadcrumbs konsisten dengan pola di atas.
- Edit/View menampilkan judul record secara ringkas.
- Settings & My Profile tidak menampilkan breadcrumbs baru (tetap default/none).
- Tidak mengubah URL/slug yang sudah ada.

## Batch Implementasi
- Batch 1: Tambah helper + trait, pasang di MyAchievements & CreateMyAchievement.
- Batch 2: AchievementResource (List/Create/Edit) override breadcrumbs.
- Batch 3: Resource lain (Program, LearningArea, Partner, User, Role).
- Batch 4: Verifikasi eksklusi Settings & QA.

## QA Checklist
- Cek semua target halaman menampilkan trail: Dashboard → Group → Resource/Page …
- Cek Edit Achievement menampilkan “Ubah: {Judul}”.
- Cek Create Achievement menampilkan “Tambah”.
- Cek halaman di grup Settings tidak berubah.

