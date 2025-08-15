<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use BezhanSalleh\FilamentShield\Support\Utils;
use Spatie\Permission\PermissionRegistrar;

class ShieldSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // HANYA entity yang sesuai dengan policy yang ada
        $entities = [
            'role',
            'token',
            'user',
            'program',
            'unit',
            'material',
            'partner',
            'learning_area',
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

        // Shortcut kelompok entity konten (akses pelajar/moderator/admin)
        $contentEntities = ['program', 'unit', 'material', 'partner', 'learning_area'];

        // Role: super_admin (semua)
        $superAdminPerms = array_values(array_unique(array_merge(...array_values($permsByEntity))));

        // Role: admin (full pada konten + user + token) — tidak mengelola role
        $adminEntities = array_merge($contentEntities, ['user', 'token']);
        $adminPerms = [];
        foreach ($adminEntities as $e) {
            $adminPerms = array_merge($adminPerms, $permsByEntity[$e]);
        }
        $adminPerms = array_values(array_unique($adminPerms));

        // Role: Moderator (read + update saja pada konten)
        $moderatorPerms = [];
        $moderatorAllowed = ['view', 'view_any', 'update', 'replicate', 'reorder', 'restore', 'restore_any'];
        foreach ($contentEntities as $e) {
            $moderatorPerms = array_merge($moderatorPerms, $makePerms($e, $moderatorAllowed));
        }
        $moderatorPerms = array_values(array_unique($moderatorPerms));

        // Role: pelajar (read-only pada konten)
        $pelajarPerms = [];
        $pelajarAllowed = ['view', 'view_any'];
        foreach ($contentEntities as $e) {
            $pelajarPerms = array_merge($pelajarPerms, $makePerms($e, $pelajarAllowed));
        }
        $pelajarPerms = array_values(array_unique($pelajarPerms));

        $rolesWithPermissions = json_encode([
            [
                'name' => 'super_admin',
                'guard_name' => 'web',
                'permissions' => $superAdminPerms,
            ],
            [
                'name' => 'Pengajar',
                'guard_name' => 'web',
                'permissions' => $moderatorPerms,
            ],
            [
                'name' => 'Admin',
                'guard_name' => 'web',
                'permissions' => $adminPerms,
            ],
            [
                'name' => 'Pelajar',
                'guard_name' => 'web',
                'permissions' => $pelajarPerms,
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
