<?php

namespace Database\Seeders;

use App\Models\Partner;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PartnerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $partnerNames = ['ASF', 'ASDFG', 'PKBM'];

        foreach ($partnerNames as $name) {
            Partner::updateOrCreate(
                ['slug' => Str::slug($name)],
                [
                    'name' => $name,
                    'description' => null,
                    'website_url' => null,
                    'logo_path' => null,
                    'is_visible' => true,
                ]
            );
        }
    }
}
