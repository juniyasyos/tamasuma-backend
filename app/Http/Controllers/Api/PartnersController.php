<?php

namespace App\Http\Controllers\Api;

use App\Models\Partner;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;

class PartnersController extends Controller
{
    /**
     * GET /api/v1/partners
     * Returns partners for frontend. Falls back to dummy data when none.
     */
    public function index(Request $request)
    {
        $onlyVisible = filter_var($request->query('visible', 'true'), FILTER_VALIDATE_BOOLEAN);

        $partners = Partner::query()
            ->when($onlyVisible, fn ($q) => $q->where('is_visible', true))
            ->orderBy('name')
            ->get();

        if ($partners->isEmpty()) {
            return response()->json($this->dummy());
        }

        $items = $partners->map(function (Partner $p) {
            $image = $p->logo_path;
            if (! $image) {
                $image = 'https://placehold.co/300x120?text=' . urlencode($p->name ?: 'Partner');
            } elseif (! Str::startsWith($image, ['http://', 'https://'])) {
                $image = url('/storage/' . ltrim($image, '/'));
            }

            return [
                'id' => $p->slug,
                'name' => $p->name,
                'des' => (string) ($p->description ?: ''),
                'image' => $image,
                'visible' => (bool) $p->is_visible,
                'active' => (bool) $p->is_visible,
                'socialLinks' => [
                    'linkedin' => '',
                    'web' => (string) ($p->website_url ?: ''),
                    'github' => '',
                    'twitter' => '',
                    'facebook' => '',
                ],
            ];
        })->values();

        return response()->json($items);
    }

    /**
     * Default dummy payload when no partners exist.
     */
    private function dummy(): array
    {
        return [
            [
                'id' => 'kampus_nusantara',
                'name' => 'Kampus Nusantara',
                'des' => 'Mitra pendidikan untuk pengembangan modul pelatihan pendidik.',
                'image' => 'https://placehold.co/300x120?text=Kampus+Nusantara',
                'visible' => true,
                'active' => true,
                'socialLinks' => [
                    'linkedin' => 'https://linkedin.com/school/kampus-nusantara',
                    'web' => 'https://kampus-nusantara.example',
                    'github' => '',
                    'twitter' => '',
                    'facebook' => '',
                ],
            ],
            [
                'id' => 'edutech_asia',
                'name' => 'EduTech Asia',
                'des' => 'Mendukung riset dan implementasi teknologi pendidikan.',
                'image' => 'https://placehold.co/300x120?text=EduTech+Asia',
                'visible' => true,
                'active' => true,
                'socialLinks' => [
                    'linkedin' => 'https://linkedin.com/company/edutech-asia',
                    'web' => 'https://edutech-asia.example',
                    'github' => '',
                    'twitter' => '',
                    'facebook' => '',
                ],
            ],
        ];
    }
}

