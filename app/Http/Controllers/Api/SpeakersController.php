<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;

class SpeakersController extends Controller
{
    /**
     * GET /api/v1/speakers
     * Returns speakers (teachers) for the frontend. Falls back to sample data when none.
     */
    public function index(Request $request)
    {
        $teachers = User::role('Pengajar')
            ->orderBy('name')
            ->get(['id', 'name', 'email', 'avatar_url']);

        if ($teachers->isEmpty()) {
            return response()->json($this->dummy());
        }

        $items = $teachers->map(function (User $u) {
            $id = Str::slug($u->name ?: ('user-' . $u->id));
            $name = (string) $u->name;
            $avatar = $u->getFilamentAvatarUrl() ?: ($u->avatar_url ?: null);
            if (! $avatar) {
                $avatar = 'https://placehold.co/200x200?text=' . urlencode(Str::of($name)->words(1, ''));
            }

            return [
                'id' => $id,
                'visible' => true,
                'image' => $avatar,
                'name' => $name,
                'designation' => 'Pengajar',
                'email' => (string) ($u->email ?: ''),
                'company' => [
                    'name' => 'Tamasuma',
                    'url' => 'https://tamasuma.local',
                ],
                'city' => '',
                'country' => 'Indonesia',
                'bio' => '',
                'socialLinks' => [
                    'twitter' => '',
                    'linkedin' => '',
                    'github' => '',
                    'web' => '',
                    'facebook' => '',
                    'medium' => '',
                ],
            ];
        })->values();

        return response()->json($items);
    }

    /**
     * Default sample speakers when none exist.
     */
    private function dummy(): array
    {
        return [
            [
                'id' => 'dea_putri',
                'visible' => true,
                'image' => 'https://placehold.co/200x200?text=Dea',
                'name' => 'Dea Putri',
                'designation' => 'Instruktur AI',
                'email' => 'dea@tamasuma.local',
                'company' => [
                    'name' => 'Tamasuma Academy',
                    'url' => 'https://tamasuma.local',
                ],
                'city' => 'Bandung',
                'country' => 'Indonesia',
                'bio' => 'Instruktur AI yang berfokus pada penerapan AI dalam proses belajar-mengajar untuk pendidik.',
                'socialLinks' => [
                    'twitter' => 'https://twitter.com/tamasuma',
                    'linkedin' => 'https://linkedin.com/company/tamasuma',
                    'github' => '',
                    'web' => 'https://tamasuma.local/mentor/dea',
                    'facebook' => '',
                    'medium' => '',
                ],
            ],
            [
                'id' => 'budi_santoso',
                'visible' => true,
                'image' => 'https://placehold.co/200x200?text=Budi',
                'name' => 'Budi Santoso',
                'designation' => 'Konsultan Kurikulum',
                'email' => 'budi@tamasuma.local',
                'company' => [
                    'name' => 'Tamasuma Studio',
                    'url' => 'https://tamasuma.local/studio',
                ],
                'city' => 'Jakarta',
                'country' => 'Indonesia',
                'bio' => 'Membantu sekolah merancang kurikulum berbasis proyek dan kompetensi.',
                'socialLinks' => [
                    'twitter' => '',
                    'linkedin' => 'https://linkedin.com/in/budi-santoso',
                    'github' => '',
                    'web' => 'https://tamasuma.local/mentor/budi',
                    'facebook' => '',
                    'medium' => '',
                ],
            ],
            [
                'id' => 'siti_rahma',
                'visible' => true,
                'image' => 'https://placehold.co/200x200?text=Siti',
                'name' => 'Siti Rahma',
                'designation' => 'Fasilitator EdTech',
                'email' => 'siti@tamasuma.local',
                'company' => [
                    'name' => 'Tamasuma',
                    'url' => 'https://tamasuma.local',
                ],
                'city' => 'Yogyakarta',
                'country' => 'Indonesia',
                'bio' => 'Pakar adopsi teknologi pendidikan dan pembelajaran berbasis data.',
                'socialLinks' => [
                    'twitter' => '',
                    'linkedin' => 'https://linkedin.com/in/siti-rahma',
                    'github' => '',
                    'web' => 'https://tamasuma.local/mentor/siti',
                    'facebook' => '',
                    'medium' => '',
                ],
            ],
        ];
    }
}

