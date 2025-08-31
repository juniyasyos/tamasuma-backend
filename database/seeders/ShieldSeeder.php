<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Config;
use BezhanSalleh\FilamentShield\Support\Utils;
use Spatie\Permission\PermissionRegistrar;

class ShieldSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        // =========================
        // 1) Definisi Entity & Aksi
        // =========================
        $entities = [
            'role',
            'user',
            'program',
            'partner',
            'learning_area',
            // opsional (API tokens / Breezy):
            'token',
        ];

        $actions = [
            'view',
            'view_any',
            'create',
            'update',
            'restore',
            'restore_any',
            'replicate',
            'reorder',
            'delete',
            'delete_any',
            'force_delete',
            'force_delete_any',
        ];

        $makePerms = static function (string $entity, array $actions): array {
            // semua format nama permission konsisten: "{aksi}_{entity}"
            return array_map(fn($a) => "{$a}_{$entity}", $actions);
        };

        // Precompute perms per-entity (hemat perulangan)
        $permsByEntity = [];
        foreach ($entities as $e) {
            $permsByEntity[$e] = $makePerms($e, $actions);
        }

        // =========================
        // 2) Kelompok Per-Role
        // =========================
        $contentEntities = ['program', 'partner', 'learning_area'];

        // Admin: full pada konten + user + token, tanpa force_delete*
        $adminEntities = array_merge($contentEntities, ['user', 'token']);
        $adminPerms = [];
        foreach ($adminEntities as $e) {
            $adminPerms = array_merge($adminPerms, $permsByEntity[$e]);
        }
        $adminPerms = array_values(array_filter($adminPerms, fn($p) => !str_contains($p, 'force_delete')));

        // Pengajar: read + create/update (tanpa restore/delete)
        $pengajarEntities = ['program', 'unit', 'material']; // 'unit' & 'material' bisa belum jadi Resource, ini tetap aman
        $pengajarActions = ['view', 'view_any', 'create', 'update', 'replicate', 'reorder'];
        $pengajarPerms = [];
        foreach ($pengajarEntities as $e) {
            $pengajarPerms = array_merge($pengajarPerms, $makePerms($e, $pengajarActions));
        }
        $pengajarPerms = array_values(array_unique($pengajarPerms));

        // Pelajar: read-only pada konten
        $pelajarActions = ['view', 'view_any'];
        $pelajarPerms = [];
        foreach ($contentEntities as $e) {
            $pelajarPerms = array_merge($pelajarPerms, $makePerms($e, $pelajarActions));
        }
        $pelajarPerms = array_values(array_unique($pelajarPerms));

        // =========================
        // 3) Widget & Custom Perms
        // =========================
        $widgetPerms = [
            'view_widget_stat_overview',
            'view_widget_user_growth_chart',
            'view_widget_program_enrolment_chart',
            'view_widget_media_storage_chart',
            'view_widget_student_focus_widget',
            'view_widget_welcoming_widget',
        ];

        $customPerms = [
            'update_any_program',
            'view_unpublished_program',
            'publish_program',
            'unpublish_program',
            'request_enrollment', // dipakai di Pelajar pada contoh sebelumnya
        ];

        // Dashboard audience (fallback bila config tidak ada)
        $dashboardAudiencePerms = (array) Config::get('dashboard.permissions', [
            'super_admin' => 'dashboard.view.super_admin',
            'admin'       => 'dashboard.view.admin',
            'pengajar'    => 'dashboard.view.pengajar',
            'pelajar'     => 'dashboard.view.pelajar',
        ]);

        // =========================
        // 4) Definisi Role & Izin
        // =========================

        // ⚠️ SUPER ADMIN:
        // Jangan sync permission apa pun.
        // Filament Shield akan memberi "semua izin" via Gate::before
        // asalkan role name = config super admin (biasanya "super_admin").
        $roles = [
            [
                'name'        => 'super_admin',
                'guard_name'  => 'web',
                'permissions' => [
                    // cukup permission kontekstual (mis. audience dashboard) bila memang dipakai UI,
                    // tidak wajib, tapi boleh:
                    $dashboardAudiencePerms['super_admin'] ?? null,
                ],
            ],
            [
                'name'        => 'Admin',
                'guard_name'  => 'web',
                'permissions' => array_values(array_filter(array_merge(
                    $adminPerms,
                    $widgetPerms,
                    [
                        $dashboardAudiencePerms['admin'] ?? null,
                        'update_any_program',
                        'view_unpublished_program',
                        'publish_program',
                        'unpublish_program',
                    ]
                ))),
            ],
            [
                'name'        => 'Pengajar',
                'guard_name'  => 'web',
                'permissions' => array_values(array_filter(array_merge(
                    $pengajarPerms,
                    [
                        'view_widget_stat_overview',
                        'view_widget_welcoming_widget',
                        $dashboardAudiencePerms['pengajar'] ?? null,
                        'view_unpublished_program', // lihat draft untuk editing
                    ]
                ))),
            ],
            [
                'name'        => 'Pelajar',
                'guard_name'  => 'web',
                'permissions' => array_values(array_filter(array_merge(
                    $pelajarPerms,
                    [
                        'view_widget_stat_overview',
                        'view_widget_student_focus_widget',
                        'view_widget_welcoming_widget',
                        $dashboardAudiencePerms['pelajar'] ?? null,
                        'request_enrollment',
                    ]
                ))),
            ],
        ];

        // =========================
        // 5) Persist Role & Permission
        // =========================
        static::syncRoles($roles);

        // Tidak ada directPermissions di luar role/policy
        $this->command->info('Shield Seeding Completed (lean, super_admin via Gate::before).');
    }

    /**
     * Membuat role & izin secara efisien:
     * - FirstOrCreate untuk permission unik
     * - syncPermissions per role (kecuali super_admin tidak wajib, tapi kita biarkan item non-null)
     */
    protected static function syncRoles(array $roles): void
    {
        $roleModel = Utils::getRoleModel();
        $permissionModel = Utils::getPermissionModel();

        // Kumpulkan semua permission unik selain null
        $allPerms = [];
        foreach ($roles as $r) {
            foreach (($r['permissions'] ?? []) as $p) {
                if ($p) $allPerms[$p] = true;
            }
        }

        // Buat seluruh permission sekali jalan
        $permissionInstances = [];
        foreach (array_keys($allPerms) as $pName) {
            $permissionInstances[$pName] = $permissionModel::firstOrCreate([
                'name'       => $pName,
                'guard_name' => 'web',
            ]);
        }

        // Buat role & sinkronkan izin (kecuali super_admin tidak butuh apa-apa sebenarnya)
        foreach ($roles as $r) {
            $role = $roleModel::firstOrCreate([
                'name'       => $r['name'],
                'guard_name' => $r['guard_name'] ?? 'web',
            ]);

            $perms = array_values(array_filter($r['permissions'] ?? []));
            if (!empty($perms)) {
                $role->syncPermissions(collect($perms)->map(fn($p) => $permissionInstances[$p])->all());
            }
        }
    }
}
