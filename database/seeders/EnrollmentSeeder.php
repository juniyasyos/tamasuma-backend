<?php

namespace Database\Seeders;

use App\Models\Enrollment;
use App\Models\Program;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EnrollmentSeeder extends Seeder
{
    /**
     * Setiap user akan memiliki minimal 5 dan maksimal 10 program yang diikuti.
     * Status disesuaikan dengan jadwal program:
     * - Selesai (completed) bila program telah berakhir
     * - Aktif (active) bila sedang berjalan atau akan datang
     */
    public function run(): void
    {
        $today = Carbon::today();

        $users = User::query()->orderBy('id')->get();
        if ($users->isEmpty()) {
            return;
        }

        $programs = Program::query()->orderBy('id')->get();
        if ($programs->isEmpty()) {
            return;
        }

        DB::transaction(function () use ($users, $programs, $today) {
            foreach ($users as $user) {
                $existingCount = Enrollment::where('user_id', $user->id)->count();

                $minToAdd = max(5 - $existingCount, 0);
                $maxToAdd = max(10 - $existingCount, 0);

                if ($maxToAdd === 0) {
                    continue; // sudah 10 atau lebih
                }

                $toAdd = $minToAdd;
                if ($maxToAdd > $minToAdd) {
                    $toAdd += random_int(0, $maxToAdd - $minToAdd);
                }

                if ($toAdd <= 0) {
                    continue;
                }

                $alreadyProgramIds = Enrollment::where('user_id', $user->id)->pluck('program_id')->all();
                $candidatePrograms = $programs->whereNotIn('id', $alreadyProgramIds)->values();

                if ($candidatePrograms->isEmpty()) {
                    continue;
                }

                $selected = $candidatePrograms->shuffle()->take($toAdd);

                foreach ($selected as $program) {
                    // Tentukan status & tanggal realistis berdasarkan jadwal program
                    $status = 'active';
                    $enrolledAt = $today->copy();
                    $completedAt = null;

                    $startsAt = $program->starts_at ? Carbon::parse($program->starts_at) : null;
                    $endsAt   = $program->ends_at ? Carbon::parse($program->ends_at) : null;

                    if ($endsAt && $endsAt->lt($today)) {
                        // Program selesai
                        $status = 'completed';
                        $completedAt = $endsAt->copy();
                        $enrolledAt = $startsAt ? $startsAt->copy() : $endsAt->copy()->subDays(10);
                    } elseif ($startsAt && $startsAt->gt($today)) {
                        // Program belum dimulai (user sudah terdaftar lebih awal)
                        $status = 'active';
                        $enrolledAt = $today->copy();
                    } else {
                        // Program berjalan
                        $status = 'active';
                        $enrolledAt = $startsAt ? $startsAt->copy() : $today->copy()->subDays(3);
                    }

                    Enrollment::firstOrCreate(
                        [
                            'user_id' => $user->id,
                            'program_id' => $program->id,
                        ],
                        [
                            'status' => $status,
                            'enrolled_at' => $enrolledAt->toDateString(),
                            'completed_at' => $completedAt?->toDateString(),
                            'notes' => 'Dummy enrollment (seeder)'.($status === 'completed' ? ' - completed' : ''),
                        ]
                    );
                }
            }
        });
    }
}

