<?php

namespace Database\Seeders;

use App\Models\User;
use BezhanSalleh\FilamentShield\Support\Utils as ShieldUtils;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UsersWithRolesSeeder extends Seeder
{
    /**
     * Seed demo users for every role found.
     */
    public function run(): void
    {
        DB::transaction(function () {
            $roleModel = ShieldUtils::getRoleModel();
            $allRoles = $roleModel::query()->orderBy('name')->get(['id', 'name', 'guard_name']);

            foreach ($allRoles as $role) {
                $slug = Str::slug($role->name, '.');
                $email = $slug . '@example.com';

                /** @var User $user */
                $user = User::query()->firstOrCreate(
                    ['email' => $email],
                    [
                        'name' => Str::headline($role->name),
                        'password' => 'password',
                        'email_verified_at' => now(),
                    ]
                );

                if (! $user->hasRole($role->name, $role->guard_name)) {
                    $user->assignRole($role->name);
                }
            }

            // Ensure a dedicated super admin demo account exists
            $superName = ShieldUtils::getSuperAdminName() ?: 'super_admin';
            $super = User::query()->firstOrCreate(
                ['email' => 'super.admin@example.com'],
                [
                    'name' => 'Super Admin',
                    'password' => 'password',
                    'email_verified_at' => now(),
                ]
            );
            if (! $super->hasRole($superName)) {
                $super->assignRole($superName);
            }
        });
    }
}

