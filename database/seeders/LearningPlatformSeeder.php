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
    /**
     * Katalog statis (tanpa random).
     * - Setiap area punya daftar program dengan atribut lengkap.
     * - source: internal|external (external butuh platform & external_url).
     */
    protected array $catalog = [
        'Koding Dasar' => [
            ['title' => 'Fundamental Algoritma',              'level' => 'pemula',   'is_published' => true,  'is_certified' => false, 'source' => 'internal'],
            ['title' => 'Pemrograman Dasar dengan JavaScript', 'level' => 'pemula',   'is_published' => true,  'is_certified' => true,  'source' => 'internal'],
            ['title' => 'Struktur Data Praktis',              'level' => 'menengah', 'is_published' => true,  'is_certified' => false, 'source' => 'internal'],
            ['title' => 'Clean Code untuk Pemula',            'level' => 'menengah', 'is_published' => true,  'is_certified' => false, 'source' => 'internal'],
            ['title' => 'Dasar Pemrograman (Dicoding)',       'level' => 'pemula',   'is_published' => true,  'is_certified' => true,  'source' => 'external', 'platform' => 'Dicoding'],
            ['title' => 'Intro to CS (Coursera)',             'level' => 'pemula',   'is_published' => true,  'is_certified' => true,  'source' => 'external', 'platform' => 'Coursera'],
        ],
        'Komunikasi Efektif' => [
            ['title' => 'Dasar Komunikasi Profesional',       'level' => 'pemula',   'is_published' => true,  'is_certified' => false, 'source' => 'internal'],
            ['title' => 'Public Speaking Praktis',            'level' => 'menengah', 'is_published' => true,  'is_certified' => true,  'source' => 'internal'],
            ['title' => 'Negosiasi & Diplomasi',              'level' => 'lanjutan', 'is_published' => true,  'is_certified' => false, 'source' => 'internal'],
            ['title' => 'Menulis Teknis Ringkas',             'level' => 'menengah', 'is_published' => true,  'is_certified' => false, 'source' => 'internal'],
            ['title' => 'Effective Communication (edX)',      'level' => 'menengah', 'is_published' => true,  'is_certified' => true,  'source' => 'external', 'platform' => 'edX'],
            ['title' => 'Business Writing (Udemy)',           'level' => 'pemula',   'is_published' => true,  'is_certified' => false, 'source' => 'external', 'platform' => 'Udemy'],
        ],
        'Desain UI/UX' => [
            ['title' => 'Dasar Desain Antarmuka',             'level' => 'pemula',   'is_published' => true,  'is_certified' => false, 'source' => 'internal'],
            ['title' => 'Wireframing ke Prototyping',         'level' => 'menengah', 'is_published' => true,  'is_certified' => true,  'source' => 'internal'],
            ['title' => 'Evaluasi Usability',                 'level' => 'menengah', 'is_published' => true,  'is_certified' => false, 'source' => 'internal'],
            ['title' => 'Design System Dasar',                'level' => 'lanjutan', 'is_published' => true,  'is_certified' => false, 'source' => 'internal'],
            ['title' => 'UI Fundamentals (BWA)',              'level' => 'pemula',   'is_published' => true,  'is_certified' => true,  'source' => 'external', 'platform' => 'BuildWithAngga'],
            ['title' => 'Human-Centered Design (Coursera)',   'level' => 'menengah', 'is_published' => true,  'is_certified' => true,  'source' => 'external', 'platform' => 'Coursera'],
        ],
        'Data & Analitik' => [
            ['title' => 'Pengantar Analisis Data',            'level' => 'pemula',   'is_published' => true,  'is_certified' => false, 'source' => 'internal'],
            ['title' => 'SQL untuk Analis',                   'level' => 'menengah', 'is_published' => true,  'is_certified' => true,  'source' => 'internal'],
            ['title' => 'Dasar Visualisasi Data',             'level' => 'pemula',   'is_published' => true,  'is_certified' => false, 'source' => 'internal'],
            ['title' => 'Statistik Terapan',                  'level' => 'menengah', 'is_published' => true,  'is_certified' => false, 'source' => 'internal'],
            ['title' => 'Data Analysis (edX)',                'level' => 'menengah', 'is_published' => true,  'is_certified' => true,  'source' => 'external', 'platform' => 'edX'],
            ['title' => 'Intro to Data (Udemy)',              'level' => 'pemula',   'is_published' => true,  'is_certified' => false, 'source' => 'external', 'platform' => 'Udemy'],
        ],
    ];

    /**
     * Template unit & materi (deterministik).
     */
    // protected array $unitTemplates = [
    //     [
    //         'title' => 'Unit 1 - Pengenalan',
    //         'summary' => 'Tujuan, ruang lingkup, dan ekspektasi pembelajaran.',
    //         'materials' => [
    //             // ['title' => 'Tujuan Pembelajaran', 'type' => 'text',  'content' => 'Ringkasan capaian dan indikator keberhasilan.'],
    //             ['title' => 'Slide Pengantar',     'type' => 'file',  'content' => '/files/pengantar.pdf'],
    //         ],
    //     ],
    //     [
    //         'title' => 'Unit 2 - Konsep Dasar',
    //         'summary' => 'Konsep inti dan istilah penting.',
    //         'materials' => [
    //             ['title' => 'Materi Konsep',       'type' => 'text',  'content' => 'Penjelasan konsep inti beserta contoh.'],
    //             ['title' => 'Video Demo',          'type' => 'video', 'content' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ'],
    //         ],
    //     ],
    //     [
    //         'title' => 'Unit 3 - Praktik',
    //         'summary' => 'Latihan terarah dan studi kasus.',
    //         'materials' => [
    //             ['title' => 'Latihan Terarah',     'type' => 'text',  'content' => 'Langkah-langkah praktik untuk peserta.'],
    //             ['title' => 'Kuis Singkat',        'type' => 'quiz',  'content' => 'Kuis 5 soal pilihan ganda.'],
    //         ],
    //     ],
    // ];

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
                foreach ($programs as $pIndex => $p) {
                    $title = $p['title'];
                    $slug  = $this->uniqueSlug(Program::class, $title);

                    $platform    = $p['source'] === 'external' ? ($p['platform'] ?? null) : null;
                    $externalUrl = $p['source'] === 'external'
                        ? ('https://example.com/course/' . Str::slug($title))
                        : null;

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
                        ]
                    );

                    // === INTERNAL CONTENT ===
                    // if ($p['source'] === 'internal') {
                    //     $this->seedUnitsAndMaterials($program);
                    // }
                }
            });

            if (isset($this->command)) {
                $this->command->info("✓ Seeded area: {$areaName}");
            }
        }
    }

    /**
     * Seed Unit & Material berdasarkan template statis.
     */
    // protected function seedUnitsAndMaterials(Program $program): void
    // {
    //     foreach ($this->unitTemplates as $uIndex => $u) {
    //         $unitTitle = "{$u['title']} - {$program->title}";
    //         $unitSlug  = $this->uniqueSlug(Unit::class, $unitTitle);

    //         $unit = Unit::updateOrCreate(
    //             ['slug' => $unitSlug],
    //             [
    //                 'program_id' => $program->id,
    //                 'title'      => $unitTitle,
    //                 'summary'    => $u['summary'],
    //                 'order'      => $uIndex + 1,
    //                 'is_visible' => true,
    //             ]
    //         );

    //         foreach ($u['materials'] as $mIndex => $m) {
    //             Material::updateOrCreate(
    //                 [
    //                     'unit_id' => $unit->id,
    //                     'order'   => $mIndex + 1,
    //                 ],
    //                 [
    //                     'title'            => $m['title'],
    //                     'type'             => $m['type'],
    //                     'content'          => $m['content'],
    //                     'duration_minutes' => 10 + ($mIndex * 5), // deterministik
    //                     'is_mandatory'     => $mIndex === 0,      // materi pertama wajib
    //                     'is_visible'       => true,
    //                 ]
    //             );
    //         }
    //     }
    // }

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
}
