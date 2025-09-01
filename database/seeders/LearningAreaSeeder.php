<?php

namespace Database\Seeders;

use App\Models\LearningArea;
use App\Models\Program;
use App\Models\Unit;
use App\Models\Material;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class LearningAreaSeeder extends Seeder
{
    /**
     * Katalog terstruktur untuk area belajar dan program.
     * Menyertakan kombinasi sumber internal & eksternal.
     */
    protected array $catalog = [
        'Koding Dasar' => [
            ['title' => 'Fundamental Algoritma',               'level' => 'pemula',   'is_published' => true,  'is_certified' => false, 'source' => 'internal'],
            ['title' => 'Pemrograman Dasar dengan JavaScript', 'level' => 'pemula',   'is_published' => true,  'is_certified' => true,  'source' => 'internal'],
            ['title' => 'Struktur Data Praktis',               'level' => 'menengah', 'is_published' => true,  'is_certified' => false, 'source' => 'internal'],
            ['title' => 'Clean Code untuk Pemula',             'level' => 'menengah', 'is_published' => true,  'is_certified' => false, 'source' => 'internal'],
            ['title' => 'Dasar Pemrograman (Dicoding)',        'level' => 'pemula',   'is_published' => true,  'is_certified' => true,  'source' => 'external', 'platform' => 'Dicoding'],
            ['title' => 'Intro to CS (Coursera)',              'level' => 'pemula',   'is_published' => true,  'is_certified' => true,  'source' => 'external', 'platform' => 'Coursera'],
        ],
        'Komunikasi Efektif' => [
            ['title' => 'Dasar Komunikasi Profesional',        'level' => 'pemula',   'is_published' => true,  'is_certified' => false, 'source' => 'internal'],
            ['title' => 'Public Speaking Praktis',             'level' => 'menengah', 'is_published' => true,  'is_certified' => true,  'source' => 'internal'],
            ['title' => 'Negosiasi & Diplomasi',               'level' => 'lanjutan', 'is_published' => true,  'is_certified' => false, 'source' => 'internal'],
            ['title' => 'Menulis Teknis Ringkas',              'level' => 'menengah', 'is_published' => true,  'is_certified' => false, 'source' => 'internal'],
            ['title' => 'Effective Communication (edX)',       'level' => 'menengah', 'is_published' => true,  'is_certified' => true,  'source' => 'external', 'platform' => 'edX'],
            ['title' => 'Business Writing (Udemy)',            'level' => 'pemula',   'is_published' => true,  'is_certified' => false, 'source' => 'external', 'platform' => 'Udemy'],
        ],
        'Desain UI/UX' => [
            ['title' => 'Dasar Desain Antarmuka',              'level' => 'pemula',   'is_published' => true,  'is_certified' => false, 'source' => 'internal'],
            ['title' => 'Wireframing ke Prototyping',          'level' => 'menengah', 'is_published' => true,  'is_certified' => true,  'source' => 'internal'],
            ['title' => 'Evaluasi Usability',                  'level' => 'menengah', 'is_published' => true,  'is_certified' => false, 'source' => 'internal'],
            ['title' => 'Design System Dasar',                 'level' => 'lanjutan', 'is_published' => true,  'is_certified' => false, 'source' => 'internal'],
            ['title' => 'UI Fundamentals (BWA)',               'level' => 'pemula',   'is_published' => true,  'is_certified' => true,  'source' => 'external', 'platform' => 'BuildWithAngga'],
            ['title' => 'Human-Centered Design (Coursera)',    'level' => 'menengah', 'is_published' => true,  'is_certified' => true,  'source' => 'external', 'platform' => 'Coursera'],
        ],
        'Data & Analitik' => [
            ['title' => 'Pengantar Analisis Data',             'level' => 'pemula',   'is_published' => true,  'is_certified' => false, 'source' => 'internal'],
            ['title' => 'SQL untuk Analis',                    'level' => 'menengah', 'is_published' => true,  'is_certified' => true,  'source' => 'internal'],
            ['title' => 'Dasar Visualisasi Data',              'level' => 'pemula',   'is_published' => true,  'is_certified' => false, 'source' => 'internal'],
            ['title' => 'Statistik Terapan',                   'level' => 'menengah', 'is_published' => true,  'is_certified' => false, 'source' => 'internal'],
            ['title' => 'Data Analysis (edX)',                 'level' => 'menengah', 'is_published' => true,  'is_certified' => true,  'source' => 'external', 'platform' => 'edX'],
            ['title' => 'Intro to Data (Udemy)',               'level' => 'pemula',   'is_published' => true,  'is_certified' => false, 'source' => 'external', 'platform' => 'Udemy'],
        ],
    ];

    /**
     * Template unit & materi (deterministik, kaya tipe konten)
     */
    protected array $unitTemplates = [
        [
            'title' => 'Unit 1 - Pengenalan',
            'summary' => 'Tujuan, ruang lingkup, dan ekspektasi pembelajaran.',
            'materials' => [
                ['title' => 'Slide Pengantar',   'type' => 'file',  'content' => '/files/pengantar.pdf',                      'duration' => 15],
                ['title' => 'Video Pengenalan',  'type' => 'video', 'content' => 'https://www.youtube.com/watch?v=ysz5S6PUM-U','duration' => 8],
            ],
        ],
        [
            'title' => 'Unit 2 - Konsep Dasar',
            'summary' => 'Konsep inti dan istilah penting.',
            'materials' => [
                ['title' => 'Materi Konsep',     'type' => 'text',  'content' => 'Penjelasan konsep inti beserta contoh.',     'duration' => 20],
                ['title' => 'Video Demo',        'type' => 'video', 'content' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ','duration' => 10],
            ],
        ],
        [
            'title' => 'Unit 3 - Praktik',
            'summary' => 'Latihan terarah dan studi kasus.',
            'materials' => [
                ['title' => 'Latihan Terarah',   'type' => 'text',  'content' => 'Langkah-langkah praktik untuk peserta.',      'duration' => 25],
                ['title' => 'Kuis Singkat',      'type' => 'quiz',  'content' => 'Kuis 5 soal pilihan ganda.',                  'duration' => 12],
            ],
        ],
    ];

    public function run(): void
    {
        foreach ($this->catalog as $areaName => $programs) {
            DB::transaction(function () use ($areaName, $programs) {
                // === LEARNING AREA ===
                $areaSlug = $this->uniqueSlug(LearningArea::class, $areaName);
                $area = LearningArea::updateOrCreate(
                    ['slug' => $areaSlug],
                    [
                        'name'        => $areaName,
                        'description' => "Area {$areaName} berisi kurikulum terstruktur.",
                        'is_active'   => true,
                    ]
                );

                // === PROGRAMS ===
                foreach ($programs as $idx => $p) {
                    $title = $p['title'];
                    $slug  = $this->uniqueSlug(Program::class, $title);

                    $isExternal  = $p['source'] === 'external';
                    $platform    = $isExternal ? ($p['platform'] ?? null) : null;
                    $externalUrl = $isExternal ? ('https://example.com/course/' . Str::slug($title)) : null;

                    // Jadwal deterministik (lebih realistis)
                    $startsAt = Carbon::now()->subDays(7 + $idx)->startOfDay();
                    $endsAt   = $isExternal ? null : Carbon::now()->addWeeks(8 + $idx)->endOfDay();

                    $program = Program::updateOrCreate(
                        ['slug' => $slug],
                        [
                            'learning_area_id' => $area->id,
                            'title'            => $title,
                            'description'      => "Silabus {$title} dengan jalur belajar terstruktur.",
                            'level'            => $p['level'],
                            'is_published'     => (bool) $p['is_published'],
                            'source'           => $p['source'],
                            'platform'         => $platform,
                            'external_url'     => $externalUrl,
                            'is_certified'     => (bool) $p['is_certified'],
                            'starts_at'        => $startsAt,
                            'ends_at'          => $endsAt,
                        ]
                    );

                    // Seed konten internal
                    if (! $isExternal) {
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
     * Buat slug unik untuk model tertentu (idempotent).
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
     * Seed Unit & Material berdasarkan template statis kaya tipe.
     */
    protected function seedUnitsAndMaterials(Program $program): void
    {
        foreach ($this->unitTemplates as $uIndex => $u) {
            $unitTitle = $u['title'];
            $unitSlug  = $this->uniqueSlug(Unit::class, $unitTitle);

            $unit = Unit::updateOrCreate(
                ['slug' => $unitSlug],
                [
                    'program_id' => $program->id,
                    'title'      => $unitTitle,
                    'summary'    => $u['summary'],
                    'order'      => $uIndex + 1,
                    'is_visible' => true,
                ]
            );

            foreach ($u['materials'] as $mIndex => $m) {
                $materialTitle = $m['title'];
                $materialSlug  = $this->uniqueSlug(Material::class, $materialTitle);

                Material::updateOrCreate(
                    ['slug' => $materialSlug],
                    [
                        'unit_id'          => $unit->id,
                        'title'            => $materialTitle,
                        'type'             => $m['type'],
                        'content'          => $m['content'],
                        'duration_minutes' => $m['duration'] ?? (10 + $mIndex * 5),
                        'order'            => $mIndex + 1,
                        'is_mandatory'     => $mIndex === 0,
                        'is_visible'       => true,
                    ]
                );
            }
        }
    }
}
