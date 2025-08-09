<?php

namespace Database\Seeders;

use App\Models\LearningArea;
use App\Models\Program;
use App\Models\Unit;
use App\Models\Material;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Faker\Factory as Faker;

class LearningPlatformSeeder extends Seeder
{
    /**
     * Konfigurasi default (bisa disesuaikan).
     */
    protected int $programsPerArea = 6;       // jumlah program per area
    protected float $externalRatio = 0.4;     // peluang program eksternal (0..1)
    protected array $areas = [
        'Koding Dasar',
        'Komunikasi Efektif',
        'Desain UI/UX',
        'Data & Analitik',
    ];

    public function run(): void
    {
        $faker = Faker::create('id_ID');

        // daftar platform eksternal yang umum
        $platforms = ['Dicoding', 'Coursera', 'Udemy', 'edX', 'BuildWithAngga'];

        foreach ($this->areas as $areaName) {
            DB::transaction(function () use ($areaName, $faker, $platforms) {
                // ==== LEARNING AREA ====
                $areaSlug = $this->uniqueSlug(LearningArea::class, $areaName);
                $area = LearningArea::updateOrCreate(
                    ['slug' => $areaSlug],
                    [
                        'name'       => $areaName,
                        'description'=> $faker->sentence(12),
                        'is_active'  => true,
                    ]
                );

                // ==== PROGRAMS ====
                for ($i = 1; $i <= $this->programsPerArea; $i++) {
                    $title = $this->programTitle($areaName, $i, $faker);
                    $slug  = $this->uniqueSlug(Program::class, $title);

                    $isExternal   = $faker->boolean($this->externalRatio * 100);
                    $level        = $faker->randomElement(['pemula', 'menengah', 'lanjutan']);
                    $isPublished  = $faker->boolean(75); // mayoritas published
                    $isCertified  = $faker->boolean(55);

                    $platform     = $isExternal ? $faker->randomElement($platforms) : null;
                    $externalUrl  = $isExternal ? $faker->url() : null;
                    $source       = $isExternal ? 'external' : 'internal';

                    $program = Program::updateOrCreate(
                        ['slug' => $slug],
                        [
                            'learning_area_id' => $area->id,
                            'title'            => $title,
                            'description'      => $faker->paragraphs(2, true),
                            'level'            => $level,
                            'is_published'     => $isPublished,
                            'source'           => $source,
                            'platform'         => $platform,
                            'external_url'     => $externalUrl,
                            'is_certified'     => $isCertified,
                        ]
                    );

                    // ==== INTERNAL PROGRAM CONTENT ====
                    if ($source === 'internal') {
                        $this->seedUnitsAndMaterials($program, $faker);
                    }
                }
            });

            // Progress output di console (jika via artisan db:seed)
            if (isset($this->command)) {
                $this->command->info("✓ Seeded area: {$areaName}");
            }
        }
    }

    /**
     * Buat Unit & Material berurutan untuk program internal.
     */
    protected function seedUnitsAndMaterials(Program $program, \Faker\Generator $faker): void
    {
        // 3–5 unit per program internal
        $unitCount = $faker->numberBetween(3, 5);

        for ($j = 1; $j <= $unitCount; $j++) {
            $unitTitle = "Unit {$j} - {$program->title}";
            $unitSlug  = $this->uniqueSlug(Unit::class, $unitTitle);

            $unit = Unit::updateOrCreate(
                ['slug' => $unitSlug],
                [
                    'program_id' => $program->id,
                    'title'      => $unitTitle,
                    'summary'    => $faker->sentence(15),
                    'order'      => $j,
                    'is_visible' => true,
                ]
            );

            // 2–4 materi per unit
            $materialCount = $faker->numberBetween(2, 4);
            for ($k = 1; $k <= $materialCount; $k++) {
                $materialTitle = "Materi {$k} - {$unit->title}";
                $type = $faker->randomElement(['text', 'video', 'file', 'quiz']);

                $content = match ($type) {
                    'text'  => $faker->paragraphs(3, true),
                    'video' => 'https://www.youtube.com/watch?v=' . Str::random(11),
                    'file'  => '/files/contoh/' . Str::slug($materialTitle) . '.pdf',
                    'quiz'  => 'Kuis singkat dengan 5 pertanyaan pilihan ganda.',
                    default => $faker->sentence(10),
                };

                Material::updateOrCreate(
                    [
                        // gabungan kunci logis agar idempotent
                        'unit_id' => $unit->id,
                        'order'   => $k,
                    ],
                    [
                        'title'            => $materialTitle,
                        'type'             => $type,
                        'content'          => $content,
                        'duration_minutes' => $faker->numberBetween(5, 30),
                        'is_mandatory'     => $faker->boolean(70),
                        'is_visible'       => true,
                    ]
                );
            }
        }
    }

    /**
     * Membuat slug unik untuk model tertentu.
     */
    protected function uniqueSlug(string $modelClass, string $title, string $column = 'slug'): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $i = 2;

        while ($modelClass::where($column, $slug)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }

    /**
     * Generator judul program yang lebih “manusiawi”.
     */
    protected function programTitle(string $areaName, int $idx, \Faker\Generator $faker): string
    {
        $prefix = $faker->randomElement([
            'Fundamental', 'Dasar-Dasar', 'Praktik', 'Intensif', 'Kelas',
        ]);

        $suffix = $faker->randomElement([
            'Untuk Pemula', 'Terapan', 'Lanjutan', 'Cepat', 'Project-Based',
        ]);

        // Contoh: "Dasar-Dasar Koding Dasar: Project-Based"
        return "{$prefix} {$areaName}: {$suffix}";
    }
}
