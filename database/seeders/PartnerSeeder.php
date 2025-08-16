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
        $partners = [
            [
                'name' => 'Alpha Learning Center',
                'description' => 'Pusat pelatihan teknologi yang menyediakan kursus pemrograman untuk remaja.',
                'website_url' => 'https://alpha-learning.example',
                'logo_path' => 'partners/alpha.png',
                'address' => 'Jl. Merdeka No. 123, Jakarta',
                'contact_email' => 'contact@alpha-learning.example',
                'contact_phone' => '+62 21 1234 5678',
                'is_visible' => true,
            ],
            [
                'name' => 'Beta Education Group',
                'description' => 'Berfokus pada pengembangan kurikulum sekolah menengah dan pelatihan guru.',
                'website_url' => 'https://beta-edu.example',
                'logo_path' => 'partners/beta.png',
                'address' => 'Jl. Pendidikan No. 45, Bandung',
                'contact_email' => 'info@beta-edu.example',
                'contact_phone' => '+62 22 8765 4321',
                'is_visible' => true,
            ],
            [
                'name' => 'Gamma Community Center',
                'description' => 'Lembaga nirlaba yang menyediakan fasilitas belajar bagi masyarakat kurang mampu.',
                'website_url' => null,
                'logo_path' => null,
                'address' => 'Jl. Raya No. 7, Surabaya',
                'contact_email' => 'admin@gamma-cc.example',
                'contact_phone' => '+62 31 555 7890',
                'is_visible' => false,
            ],
        ];

        foreach ($partners as $partner) {
            Partner::updateOrCreate(
                ['slug' => Str::slug($partner['name'])],
                $partner
            );
        }
    }
}
