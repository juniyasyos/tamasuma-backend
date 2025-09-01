<?php

namespace App\Services\Achievements;

use App\Models\Achievement;
use App\Models\Enrollment;
use Illuminate\Support\Arr;

class AutoAwardService
{
    /**
     * Award an achievement when a program enrollment is completed.
     * Idempotent: upserts by (user_id, source_type, source_id).
     */
    public function awardProgramCompleted(Enrollment $enrollment): Achievement
    {
        // Safety checks
        if ($enrollment->status !== 'completed') {
            // Force treat as completed scenario only
            throw new \InvalidArgumentException('Enrollment is not in completed status.');
        }

        $program = $enrollment->program;
        $userId  = $enrollment->user_id;

        $title = 'Selesai Program: ' . ($program?->title ?? 'Tanpa Judul');
        $achievedAt = $enrollment->completed_at ?: now()->toDateString();

        $attributes = [
            'user_id'     => $userId,
            'source_type' => 'Enrollment',
            'source_id'   => $enrollment->id,
        ];

        $values = [
            'title'       => $title,
            'category'    => 'certificate',
            'issuer'      => null, // Tidak ada field issuer di Program; bisa diisi kemudian bila diperlukan
            'achieved_at' => $achievedAt,
            'description' => 'Diberikan otomatis karena menyelesaikan program.',
            'visibility'  => 'private',
            'created_via' => 'auto',
            'created_by_id' => null,
        ];

        // Preserve existing tags; ensure 'program' and 'auto' present
        $existing = Achievement::query()->where($attributes)->first();
        if ($existing) {
            $tags = (array) $existing->tags ?: [];
            $tags = array_values(array_unique(array_merge($tags, ['program', 'auto'])));
            $values['tags'] = $tags;

            $existing->fill($values)->save();
            return $existing->refresh();
        }

        $values['tags'] = ['program', 'auto'];
        return Achievement::create(array_merge($attributes, $values));
    }
}

