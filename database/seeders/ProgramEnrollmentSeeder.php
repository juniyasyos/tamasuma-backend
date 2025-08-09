<?php

namespace Database\Seeders;

use App\Models\Program;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ProgramEnrollmentSeeder extends Seeder
{
    public function run(): void
    {
        $permission = Permission::firstOrCreate(['name' => 'enroll_program']);

        Role::whereIn('name', ['super_admin', 'Moderator'])
            ->each(fn (Role $role) => $role->givePermissionTo($permission));

        $programs = Program::all();

        User::all()->each(function (User $user) use ($programs) {
            $programs->random(rand(0, $programs->count()))
                ->each(fn (Program $program) => $user->programs()->syncWithoutDetaching([
                    $program->id => ['status' => 'enrolled'],
                ]));
        });
    }
}
