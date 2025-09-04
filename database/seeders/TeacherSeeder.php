<?php

namespace Database\Seeders;

use App\Models\Program;
use App\Models\Teacher;
use Illuminate\Database\Seeder;

class TeacherSeeder extends Seeder
{
    public function run(): void
    {
        $teachers = [
            ['name' => 'Budi Santoso', 'bio' => 'Ahli pemrograman dasar.'],
            ['name' => 'Siti Nurhaliza', 'bio' => 'Praktisi komunikasi efektif.'],
            ['name' => 'Rudi Hartono', 'bio' => 'Desainer UI/UX berpengalaman.'],
        ];

        foreach ($teachers as $data) {
            Teacher::firstOrCreate(['name' => $data['name']], $data);
        }

        $programs = Program::all();
        $teacherIds = Teacher::pluck('id');

        foreach ($programs as $program) {
            $program->teachers()->syncWithoutDetaching([$teacherIds->random()]);
        }
    }
}
