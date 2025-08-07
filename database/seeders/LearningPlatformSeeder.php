<?php

namespace Database\Seeders;

use App\Models\LearningArea;
use App\Models\Program;
use App\Models\Unit;
use App\Models\Material;
use Illuminate\Database\Seeder;

class LearningPlatformSeeder extends Seeder
{
    public function run(): void
    {
        $areas = [
            ['name' => 'Koding Dasar'],
            ['name' => 'Komunikasi Efektif'],
        ];

        foreach ($areas as $areaData) {
            $area = LearningArea::create([
                ...$areaData,
                'description' => 'Deskripsi untuk ' . $areaData['name'],
                'is_active' => true,
            ]);

            // Programs
            for ($i = 1; $i <= 2; $i++) {
                $isExternal = rand(0, 1) === 1;

                $program = Program::create([
                    'learning_area_id' => $area->id,
                    'title' => "Program {$i} - {$area->name}",
                    'slug' => "program-{$i}-" . $area->slug,
                    'description' => 'Deskripsi program ' . $i,
                    'level' => ['pemula', 'menengah', 'lanjutan'][rand(0, 2)],
                    'is_published' => true,
                    'source' => $isExternal ? 'external' : 'internal',
                    'platform' => $isExternal ? 'Dicoding' : null,
                    'external_url' => $isExternal ? 'https://example.com/program-' . $i : null,
                    'is_certified' => (bool)rand(0, 1),
                ]);

                if (!$isExternal) {
                    for ($j = 1; $j <= 3; $j++) {
                        $unit = Unit::create([
                            'program_id' => $program->id,
                            'title' => "Unit {$j} - {$program->title}",
                            'slug' => "unit-{$j}-" . $program->slug,
                            'summary' => 'Ringkasan untuk unit ' . $j,
                            'order' => $j,
                            'is_visible' => true,
                        ]);

                        for ($k = 1; $k <= 2; $k++) {
                            Material::create([
                                'unit_id' => $unit->id,
                                'title' => "Materi {$k} - {$unit->title}",
                                'type' => ['text', 'video', 'file', 'quiz'][rand(0, 3)],
                                'content' => 'Konten materi ' . $k,
                                'duration_minutes' => rand(5, 30),
                                'order' => $k,
                                'is_mandatory' => (bool)rand(0, 1),
                                'is_visible' => true,
                            ]);
                        }
                    }
                }
            }
        }
    }
}
