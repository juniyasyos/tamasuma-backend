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
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Entity yang saat ini tersedia sebagai Resource/fitur utama
        // (sinkron dengan App\Filament\Resources dan fitur terkait)
        $entities = [
            'role',
            'user',
            'program',
            'partner',
            'learning_area',
            // Opsional: token (Sanctum) untuk fitur API token Breezy
            'token',
        ];

        // Generator daftar permission standar untuk satu entity
        $allActions = [
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

        $makePerms = function (string $entity, array $actions) {
            return array_map(
                fn($a) => ($a === 'view_any' || $a === 'restore_any' || $a === 'delete_any' || $a === 'force_delete_any')
                    ? "{$a}_{$entity}"
                    : "{$a}_{$entity}",
                $actions
            );
        };

        // Kumpulan permission lengkap per entity
        $permsByEntity = [];
        foreach ($entities as $e) {
            $permsByEntity[$e] = $makePerms($e, $allActions);
        }

        // Kelompok entity konten yang dikelola harian
        $contentEntities = ['program', 'partner', 'learning_area'];

        // Role: super_admin (semua)
        $superAdminPerms = array_values(array_unique(array_merge(...array_values($permsByEntity))));

        // Role: admin (full pada konten + user + token) — tidak mengelola role
        $adminEntities = array_merge($contentEntities, ['user', 'token']);
        $adminPerms = [];
        foreach ($adminEntities as $e) {
            $adminPerms = array_merge($adminPerms, $permsByEntity[$e]);
        }
        // Optional hardening: hilangkan force_delete untuk admin
        $adminPerms = array_values(array_unique(array_filter($adminPerms, fn($p) => ! str_contains($p, 'force_delete'))));

        // Role: Pengajar (read + update konten pembelajaran; tanpa restore/delete)
        $pengajarPerms = [];
        $pengajarAllowedEntities = ['program', 'unit', 'material'];
        $pengajarAllowed = ['view', 'view_any', 'create', 'update', 'replicate', 'reorder'];
        foreach ($pengajarAllowedEntities as $e) {
            $pengajarPerms = array_merge($pengajarPerms, $makePerms($e, $pengajarAllowed));
        }
        $pengajarPerms = array_values(array_unique($pengajarPerms));

        // Role: pelajar (read-only pada konten)
        $pelajarPerms = [];
        $pelajarAllowed = ['view', 'view_any'];
        foreach ($contentEntities as $e) {
            $pelajarPerms = array_merge($pelajarPerms, $makePerms($e, $pelajarAllowed));
        }
        $pelajarPerms = array_values(array_unique($pelajarPerms));

        // Widget permissions (selaraskan dengan widget yang ada di app/Filament/Widgets)
        $widgetPerms = [
            'view_widget_stat_overview',
            'view_widget_user_growth_chart',
            'view_widget_program_enrolment_chart',
            'view_widget_media_storage_chart',
            // Widget baru yang berfokus pada user/pelajar
            'view_widget_student_focus_widget',
            'view_widget_welcoming_widget',
        ];

        // Custom permissions
        $customPerms = [
            'update_any_program',
            'view_unpublished_program',
            'publish_program',
            'unpublish_program',
        ];

        // Dashboard audience permissions (used by StatOverview detection)
        $dashboardAudiencePerms = (array) Config::get('dashboard.permissions', [
            'super_admin' => 'dashboard.view.super_admin',
            'admin' => 'dashboard.view.admin',
            'pengajar' => 'dashboard.view.pengajar',
            'pelajar' => 'dashboard.view.pelajar',
        ]);

        $rolesWithPermissions = json_encode([
            [
                'name' => 'super_admin',
                'guard_name' => 'web',
                'permissions' => array_values(array_unique(array_merge($superAdminPerms, $widgetPerms, $customPerms, [
                    $dashboardAudiencePerms['super_admin'],
                ]))),
            ],
            [
                'name' => 'Pengajar',
                'guard_name' => 'web',
                'permissions' => array_values(array_unique(array_merge($pengajarPerms, [
                    'view_widget_stat_overview',
                    'view_widget_welcoming_widget',
                    $dashboardAudiencePerms['pengajar'],
                    // Pengajar dapat melihat draft (untuk editing), tapi bukan publish
                    'view_unpublished_program',
                ]))),
            ],
            [
                'name' => 'Admin',
                'guard_name' => 'web',
                'permissions' => array_values(array_unique(array_merge($adminPerms, $widgetPerms, [
                    $dashboardAudiencePerms['admin'],
                    'update_any_program',
                    'view_unpublished_program',
                    'publish_program',
                    'unpublish_program',
                ]))),
            ],
            [
                'name' => 'Pelajar',
                'guard_name' => 'web',
                'permissions' => array_values(array_unique(array_merge($pelajarPerms, [
                    'view_widget_stat_overview',
                    'view_widget_student_focus_widget',
                    'view_widget_welcoming_widget',
                    $dashboardAudiencePerms['pelajar'],
                    'request_enrollment',
                ]))),
            ],
        ]);

        // Tidak ada directPermissions lain di luar policy — kosongkan
        $directPermissions = '[]';

        static::makeRolesWithPermissions($rolesWithPermissions);
        static::makeDirectPermissions($directPermissions);

        $this->command->info('Shield Seeding Completed (policy-only).');
    }

    protected static function makeRolesWithPermissions(string $rolesWithPermissions): void
    {
        if (! blank($rolePlusPermissions = json_decode($rolesWithPermissions, true))) {
            $roleModel = Utils::getRoleModel();
            $permissionModel = Utils::getPermissionModel();

            foreach ($rolePlusPermissions as $rolePlusPermission) {
                $role = $roleModel::firstOrCreate([
                    'name' => $rolePlusPermission['name'],
                    'guard_name' => $rolePlusPermission['guard_name'],
                ]);

                if (! blank($rolePlusPermission['permissions'])) {
                    $permissionModels = collect($rolePlusPermission['permissions'])
                        ->map(fn($permission) => $permissionModel::firstOrCreate([
                            'name' => $permission,
                            'guard_name' => $rolePlusPermission['guard_name'],
                        ]))
                        ->all();

                    $role->syncPermissions($permissionModels);
                }
            }
        }
    }

    public static function makeDirectPermissions(string $directPermissions): void
    {
        if (! blank($permissions = json_decode($directPermissions, true))) {
            $permissionModel = Utils::getPermissionModel();

            foreach ($permissions as $permission) {
                if ($permissionModel::whereName($permission)->doesntExist()) {
                    $permissionModel::create([
                        'name' => $permission['name'],
                        'guard_name' => $permission['guard_name'],
                    ]);
                }
            }
        }
    }
}
