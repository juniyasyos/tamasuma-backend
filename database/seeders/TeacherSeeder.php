<?php

namespace Database\Seeders;

use App\Models\Program;
use App\Models\User;
use Illuminate\Database\Seeder;

class TeacherSeeder extends Seeder
{
    public function run(): void
    {
        $programs = Program::all();
        $teacherIds = User::role('Pengajar')->pluck('id');

        foreach ($programs as $program) {
            if ($teacherIds->isNotEmpty()) {
                $program->teachers()->syncWithoutDetaching([$teacherIds->random()]);
            }
        }
    }
}
