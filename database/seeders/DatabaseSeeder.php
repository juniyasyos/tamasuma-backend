<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(
            [
                LearningPlatformSeeder::class,
                PartnerSeeder::class,
                ShieldSeeder::class,
                UsersWithRolesSeeder::class,
                EnrollmentSeeder::class,
            ]
        );
    }
}
