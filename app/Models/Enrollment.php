<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'program_id', 'status', 'requested_at', 'approved_at', 'rejected_at', 'enrolled_at', 'completed_at', 'notes',
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'enrolled_at' => 'date',
        'completed_at' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    // Notification logic moved to Observer + Service for better testability and reuse.

    /**
     * Award achievement when program enrollment is completed.
     * Idempotent: upserts by (user_id, source_type, source_id).
     */
    public function awardCompletionAchievement(): Achievement
    {
        if ($this->status !== 'completed') {
            throw new \InvalidArgumentException('Enrollment is not in completed status.');
        }

        $title = 'Selesai Program: ' . ($this->program?->title ?? 'Tanpa Judul');
        $achievedAt = $this->completed_at ?: now()->toDateString();

        $attributes = [
            'user_id'     => $this->user_id,
            'source_type' => 'Enrollment',
            'source_id'   => $this->id,
        ];

        $values = [
            'title'       => $title,
            'category'    => 'certificate',
            'issuer'      => null,
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
