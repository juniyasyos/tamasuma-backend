<?php

namespace Database\Seeders;

use App\Models\Partner;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PartnerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Partner::create([
            'name' => 'PKBM Nurul Huda Jember',
            'description' => 'Mitra pendidikan yang bekerja sama dengan platform.',
            'website_url' => null,
            'logo_path' => null,
            'is_visible' => true,
        ]);
    }
}
