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
        $allPrograms = [];

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

                    // Kumpulkan id program untuk penjadwalan terpusat setelah semua area selesai diproses
                    $this->programBuffer[] = $program->id;
                }
            });

            if (isset($this->command)) {
                $this->command->info("✓ Seeded area: {$areaName}");
            }
        }

        // Ambil kembali instance program dari buffer untuk dijadwalkan
        if (! empty($this->programBuffer)) {
            $allPrograms = Program::whereIn('id', $this->programBuffer)
                ->orderBy('id')
                ->get()
                ->all();
        }

        // Tetapkan jadwal realistis: mulai bulan lalu, max 3 program per minggu, masa depan maks 1 bulan
        if (! empty($allPrograms)) {
            $this->assignSchedules($allPrograms);
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

    // Buffer id program untuk penjadwalan global (menghindari referensi di dalam transaksi)
    protected array $programBuffer = [];

    /**
     * Menetapkan jadwal untuk deretan program dengan batasan:
     * - Mulai dari awal bulan lalu (diselaraskan ke Senin)
     * - Maksimal 3 program per minggu (Senin, Rabu, Jumat)
     * - Tanggal masa depan dibatasi sampai 1 bulan dari hari ini
     * - Durasi deterministik 3-8 hari agar beragam (bukan acak)
     */
    protected function assignSchedules(array $programs): void
    {
        if (empty($programs)) {
            return;
        }

        $now = Carbon::now();
        $futureCap = $now->copy()->addMonth()->endOfDay();

        // Rentang minggu: dari awal bulan lalu hingga akhir minggu pada bulan depan
        $periodStart = $now->copy()->subMonth()->startOfMonth()->startOfWeek(Carbon::MONDAY);
        $periodEnd   = $now->copy()->addMonth()->endOfWeek(Carbon::SUNDAY);

        // Buat slot per minggu: Senin, Rabu, Jumat
        $weekStarts = [];
        for ($cursor = $periodStart->copy(); $cursor->lte($periodEnd); $cursor->addWeek()) {
            $weekStarts[] = $cursor->copy();
        }

        $slots = [];
        foreach ($weekStarts as $ws) {
            $slots[] = $ws->copy();              // Senin
            $slots[] = $ws->copy()->addDays(2);  // Rabu
            $slots[] = $ws->copy()->addDays(4);  // Jumat
        }

        // Batasi jumlah program sesuai jumlah slot yang tersedia
        $count = min(count($programs), count($slots));

        // Pola durasi deterministik agar stabil antar seed
        $durations = [3, 5, 4, 6, 7, 8];

        for ($i = 0; $i < $count; $i++) {
            /** @var \App\Models\Program $program */
            $program = $programs[$i];
            $start   = $slots[$i]->copy()->startOfDay();

            // Pastikan tanggal mulai tidak melewati batas masa depan
            if ($start->gt($futureCap)) {
                $start = $futureCap->copy()->startOfDay();
            }

            $durationDays = $durations[$i % count($durations)];
            $end = $start->copy()->addDays($durationDays)->endOfDay();

            // Klip tanggal selesai agar tidak lebih dari batas masa depan
            if ($end->gt($futureCap)) {
                $end = $futureCap->copy();
            }

            // Pastikan ends_at selalu >= starts_at
            if ($end->lt($start)) {
                $end = $start->copy()->endOfDay();
            }

            $program->starts_at = $start->toDateString();
            $program->ends_at   = $end->toDateString();
            $program->save();
        }
    }
}
