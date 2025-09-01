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
     * Seed exactly 5 demo users:
     * - Super Admin (role: super_admin)
     * - Admin (role: Admin)
     * - Pengajar (role: Pengajar)
     * - Pelajar (role: Pelajar)
     * - User tanpa role
     */
    public function run(): void
    {
        DB::transaction(function () {
            // Ensure base roles exist (seed Shield first in DatabaseSeeder order)
            $superRole = ShieldUtils::getSuperAdminName() ?: 'super_admin';

            $spec = [
                ['name' => 'Super Admin', 'email' => 'super.admin@example.com', 'role' => $superRole],
                ['name' => 'Admin',       'email' => 'admin@example.com',       'role' => 'Admin'],
                ['name' => 'Pengajar',    'email' => 'pengajar@example.com',    'role' => 'Pengajar'],
                ['name' => 'Pelajar',     'email' => 'pelajar@example.com',     'role' => 'Pelajar'],
                ['name' => 'Tanpa Role',  'email' => 'user@example.com',        'role' => null],
            ];

            foreach ($spec as $row) {
                /** @var User $user */
                $user = User::query()->firstOrCreate(
                    ['email' => $row['email']],
                    [
                        'name' => $row['name'],
                        'password' => 'password', // cast to hashed by model
                        'email_verified_at' => now(),
                    ]
                );

                // Sync to single role (or none)
                if ($row['role']) {
                    $user->syncRoles([$row['role']]);
                } else {
                    $user->syncRoles([]); // ensure no roles
                }
            }
        });
    }
}
