<?php

namespace App\Http\Controllers\Api;

use App\Models\Program;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ModulesController extends Controller
{
    /**
     * GET /api/v1/modules
     * Returns modules shaped for frontend. Falls back to dummy data when none.
     */
    public function index(Request $request)
    {
        $programs = Program::query()
            ->where('is_published', true)
            ->with('learningArea')
            ->latest('created_at')
            ->get();

        if ($programs->isEmpty()) {
            return response()->json($this->dummy());
        }

        $now = Carbon::now();

        $mapLevel = function (?string $level): string {
            return match ($level) {
                'pemula' => 'Beginner',
                'menengah' => 'Intermediate',
                'lanjutan' => 'Advanced',
                default => 'Beginner',
            };
        };

        $modules = $programs->map(function (Program $p) use ($now, $mapLevel) {
            $startDate = optional($p->starts_at)?->toDateString();
            $endDate   = optional($p->ends_at)?->toDateString();

            $isActive = false;
            if ($p->starts_at && $p->ends_at) {
                $isActive = $now->between($p->starts_at->startOfDay(), $p->ends_at->endOfDay());
            } elseif ($p->starts_at && ! $p->ends_at) {
                $isActive = $now->greaterThanOrEqualTo($p->starts_at->startOfDay());
            }

            $title = (string) $p->title;
            $imageTitle = urlencode($title ?: 'Program');

            return [
                'id' => $p->slug ?: ('program-' . $p->getKey()),
                'type' => 'custom',
                'name' => $title,
                'visible' => (bool) $p->is_published,
                'date' => $startDate,
                'endDate' => $endDate,
                'time' => [
                    'starttime' => '09:00',
                    'endtime' => '12:00',
                ],
                'active' => $isActive,
                'venue' => [
                    'googlemapsurl' => '',
                    'name' => $p->isExternal() ? 'Online' : 'Onsite',
                ],
                'links' => [
                    'registration' => url('/programs/' . ($p->slug ?: $p->getKey())),
                    'youtube' => 'https://youtube.com/@tamasuma',
                    'facebook' => '',
                    'meetup' => '',
                    'callforspeaker' => '',
                    'feedback' => '',
                ],
                'partners' => [],
                'team' => [],
                'speakers' => [],
                'image' => "https://placehold.co/800x40k0?text={$imageTitle}",
                'thumbnail' => "https://placehold.co/600x400?text=" . ($p->learningArea?->name ? urlencode($p->learningArea->name) : 'Tamasuma'),
                'hashtags' => ['Tamasuma'],
                'category' => array_values(array_filter([(string) optional($p->learningArea)->name])),
                'difficulty' => $mapLevel($p->level),
                'language' => 'id',
                'durationHours' => 10,
                'authors' => ['Tamasuma Academy'],
                'prerequisites' => [],
                'outcomes' => [],
                'resources' => [],
                'des' => $p->description ? '<p>' . $p->description . '</p>' : '<p>-</p>',
                'agenda' => [],
            ];
        })->values();

        return response()->json($modules);
    }

    /**
     * GET /api/v1/modules/featured
     * Return a list of featured module IDs. Uses newest published programs, or dummy fallback.
     */
    public function featured(Request $request)
    {
        $limit = (int) $request->query('limit', 3);

        $programs = Program::query()
            ->where('is_published', true)
            ->latest('created_at')
            ->limit($limit)
            ->get(['id', 'slug']);

        $ids = $programs->map(fn (Program $p) => $p->slug ?: ('program-' . $p->getKey()))->values();

        if ($ids->isEmpty()) {
            $ids = collect([
                'tamasuma-ai-dasar',
                'tamasuma-kurikulum-proyek',
                'tamasuma-edtech-tools',
            ]);
        }

        return response()->json([
            [
                'id' => 'data',
                'eventid' => $ids->all(),
            ],
        ]);
    }

    /**
     * Default dummy payload when no programs exist.
     */
    private function dummy(): array
    {
        return [
            [
                'id' => 'tamasuma-ai-dasar',
                'type' => 'custom',
                'name' => 'Dasar AI untuk Pendidik',
                'visible' => true,
                'date' => '2025-01-15',
                'endDate' => '2025-02-15',
                'time' => ['starttime' => '09:00', 'endtime' => '12:00'],
                'active' => true,
                'venue' => ['googlemapsurl' => '', 'name' => 'Online'],
                'links' => [
                    'registration' => 'https://tamasuma.local/registrasi/ai-dasar',
                    'youtube' => 'https://youtube.com/@tamasuma',
                    'facebook' => '',
                    'meetup' => '',
                    'callforspeaker' => '',
                    'feedback' => ''
                ],
                'partners' => ['edutech_asia'],
                'team' => ['rena_tamasuma', 'dimas_ops'],
                'speakers' => ['dea_putri'],
                'image' => 'https://placehold.co/800x400?text=Dasar+AI',
                'thumbnail' => 'https://placehold.co/600x400?text=AI',
                'hashtags' => ['Tamasuma', 'AI', 'Pendidik'],
                'category' => ['Artificial Intelligence', 'EdTech'],
                'difficulty' => 'Beginner',
                'language' => 'id',
                'durationHours' => 12,
                'authors' => ['Tamasuma Academy'],
                'prerequisites' => [
                    'Memahami dasar komputer',
                    'Terbiasa menggunakan aplikasi presentasi'
                ],
                'outcomes' => [
                    'Memahami konsep dasar AI',
                    'Menentukan use case AI dalam pengajaran',
                    'Membuat rencana integrasi AI sederhana di kelas'
                ],
                'resources' => [
                    ['title' => 'Slide Materi', 'url' => 'https://tamasuma.local/materi/ai-dasar'],
                    ['title' => 'Template RPP AI', 'url' => 'https://tamasuma.local/template/rpp-ai']
                ],
                'des' => '<p>Pelatihan pengenalan AI untuk pendidik dengan fokus pada penerapan praktis di ruang kelas.</p>',
                'agenda' => [
                    ['title' => 'Pengenalan AI', 'des' => 'Konsep dasar AI dalam pendidikan', 'starttime' => '09:00', 'endtime' => '10:30'],
                    ['title' => 'Studi Kasus', 'des' => 'Contoh penggunaan AI di kelas', 'starttime' => '10:45', 'endtime' => '12:00']
                ],
            ],
            [
                'id' => 'tamasuma-kurikulum-proyek',
                'type' => 'custom',
                'name' => 'Kurikulum Berbasis Proyek',
                'visible' => true,
                'date' => '2025-02-20',
                'endDate' => '2025-03-05',
                'time' => ['starttime' => '09:00', 'endtime' => '12:00'],
                'active' => true,
                'venue' => ['googlemapsurl' => '', 'name' => 'Online'],
                'links' => [
                    'registration' => 'https://tamasuma.local/registrasi/kurikulum-proyek',
                    'youtube' => 'https://youtube.com/@tamasuma',
                    'facebook' => '',
                    'meetup' => '',
                    'callforspeaker' => '',
                    'feedback' => ''
                ],
                'partners' => ['kampus_nusantara'],
                'team' => ['dimas_ops', 'ayu_community'],
                'speakers' => ['budi_santoso'],
                'image' => 'https://placehold.co/800x400?text=Kurikulum+Proyek',
                'thumbnail' => 'https://placehold.co/600x400?text=PBL',
                'hashtags' => ['Tamasuma', 'ProjectBasedLearning'],
                'category' => ['Curriculum', 'Project-Based Learning'],
                'difficulty' => 'Intermediate',
                'language' => 'id',
                'durationHours' => 10,
                'authors' => ['Tamasuma Studio'],
                'prerequisites' => ['Dasar penyusunan rencana pembelajaran'],
                'outcomes' => [
                    'Merancang kurikulum berbasis proyek',
                    'Menyusun rubrik asesmen berbasis kompetensi'
                ],
                'resources' => [
                    ['title' => 'Contoh Rubrik', 'url' => 'https://tamasuma.local/rubrik/pbl']
                ],
                'des' => '<p>Belajar konsep dan praktik terbaik untuk menyusun kurikulum berbasis proyek yang terukur dan relevan.</p>',
                'agenda' => [
                    ['title' => 'Dasar PBL', 'des' => 'Prinsip dan perencanaan PBL', 'starttime' => '09:00', 'endtime' => '10:30'],
                    ['title' => 'Perancangan Rubrik', 'des' => 'Asesmen berbasis kompetensi', 'starttime' => '10:45', 'endtime' => '12:00']
                ],
            ],
        ];
    }
}
