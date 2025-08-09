<?php

namespace Database\Seeders;

use App\Models\LearningArea;
use App\Models\Program;
use App\Models\Unit;
use App\Models\Material;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LearningPlatformSeeder extends Seeder
{
    protected int $programsPerArea = 6;       // jumlah program per area
    protected float $externalRatio  = 0.4;    // rasio eksternal (tetap dipakai sbg kalkulasi, tapi deterministik)
    protected array $areas = [
        'Koding Dasar',
        'Komunikasi Efektif',
        'Desain UI/UX',
        'Data & Analitik',
    ];

    // daftar “tetap” (urutan dipakai melingkar/cyclic)
    protected array $prefixes = ['Fundamental', 'Dasar-Dasar', 'Praktik', 'Intensif', 'Kelas', 'Workshop'];
    protected array $suffixes = ['Untuk Pemula', 'Terapan', 'Lanjutan', 'Cepat', 'Project-Based', 'Mendalam'];
    protected array $levels   = ['pemula', 'menengah', 'lanjutan'];
    protected array $platforms = ['Dicoding', 'Coursera', 'Udemy', 'edX', 'BuildWithAngga'];
    protected array $materialTypes = ['text', 'video', 'file', 'quiz'];

    public function run(): void
    {
        foreach ($this->areas as $areaName) {
            DB::transaction(function () use ($areaName) {
                // ==== LEARNING AREA ====
                $areaSlug = $this->uniqueSlug(LearningArea::class, $areaName);
                $area = LearningArea::updateOrCreate(
                    ['slug' => $areaSlug],
                    [
                        'name'        => $areaName,
                        'description' => "Koleksi program terkurasi untuk topik {$areaName}.",
                        'is_active'   => true,
                    ]
                );

                // hitung berapa program eksternal (deterministik)
                $externalCount = (int) round($this->programsPerArea * $this->externalRatio);

                // ==== PROGRAMS ====
                for ($i = 1; $i <= $this->programsPerArea; $i++) {
                    $title = $this->programTitle($areaName, $i);
                    $slug  = $this->uniqueSlug(Program::class, $title);

                    $isExternal  = $i <= $externalCount; // program 1..externalCount = eksternal, sisanya internal
                    $level       = $this->levels[($i - 1) % count($this->levels)];
                    $isPublished = $i % 6 !== 0;     // contoh: semua published kecuali ke-6 (pola tetap)
                    $isCertified = $i % 2 === 0;     // contoh: genap = tersertifikasi (pola tetap)

                    $platform    = $isExternal ? $this->platforms[($i - 1) % count($this->platforms)] : null;
                    $externalUrl = $isExternal ? "https://example.com/{$areaSlug}/program-{$i}" : null;
                    $source      = $isExternal ? 'external' : 'internal';

                    $program = Program::updateOrCreate(
                        ['slug' => $slug],
                        [
                            'learning_area_id' => $area->id,
                            'title'            => $title,
                            'description'      => "Program {$title} berfokus pada praktik terstruktur dan studi kasus.",
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
                        $this->seedUnitsAndMaterials($program);
                    }
                }
            });

            if (isset($this->command)) {
                $this->command->info("✓ Seeded area: {$areaName}");
            }
        }
    }

    /**
     * Buat Unit & Material berurutan (deterministik).
     */
    protected function seedUnitsAndMaterials(Program $program): void
    {
        // Tetapkan jumlah unit & materi per unit (tanpa random)
        $unitCount = 4;

        for ($j = 1; $j <= $unitCount; $j++) {
            $unitTitle = "Unit {$j} - {$program->title}";
            $unitSlug  = $this->uniqueSlug(Unit::class, $unitTitle);

            $unit = Unit::updateOrCreate(
                ['slug' => $unitSlug],
                [
                    'program_id' => $program->id,
                    'title'      => $unitTitle,
                    'summary'    => "Ringkasan materi untuk {$unitTitle}.",
                    'order'      => $j,
                    'is_visible' => true,
                ]
            );

            // 3 materi per unit (tetap)
            $materialCount = 3;
            for ($k = 1; $k <= $materialCount; $k++) {
                $type = $this->materialTypes[($k - 1) % count($this->materialTypes)];
                $materialTitle = "Materi {$k} - {$unit->title}";

                $content = match ($type) {
                    'text'  => "Konten teks terstruktur untuk {$materialTitle}.",
                    'video' => "https://videos.example.com/{$program->slug}/unit-{$j}-materi-{$k}",
                    'file'  => "/files/{$program->slug}/unit-{$j}/materi-{$k}.pdf",
                    'quiz'  => "Kuis 5 soal pilihan ganda untuk {$materialTitle}.",
                    default => "Konten {$type} untuk {$materialTitle}.",
                };

                Material::updateOrCreate(
                    [
                        'unit_id' => $unit->id,
                        'order'   => $k,
                    ],
                    [
                        'title'            => $materialTitle,
                        'type'             => $type,
                        'content'          => $content,
                        'duration_minutes' => 10 + ($k * 5), // pola 15/20/25
                        'is_mandatory'     => $k !== 3,       // contoh: materi 1-2 wajib, 3 opsional
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
     * Generator judul program deterministik & unik per area.
     */
    protected function programTitle(string $areaName, int $idx): string
    {
        $prefix = $this->prefixes[($idx - 1) % count($this->prefixes)];
        $suffix = $this->suffixes[($idx - 1) % count($this->suffixes)];
        return "{$prefix} {$areaName}: {$suffix}";
    }
}
