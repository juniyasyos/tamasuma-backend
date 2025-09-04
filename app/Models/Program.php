<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Program extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $hidden = ['created_at', 'updated_at'];

    protected $fillable = [
        'learning_area_id',
        'title',
        'slug',
        'description',
        'level',
        'is_published',
        'source',
        'platform',
        'external_url',
        'is_certified',
        'starts_at',
        'ends_at',
    ];

    protected $casts = [
        'starts_at' => 'date',
        'ends_at' => 'date',
    ];

    public function learningArea()
    {
        return $this->belongsTo(LearningArea::class);
    }

    public function units()
    {
        return $this->hasMany(Unit::class);
    }

    public function enrollments()
    {
        return $this->hasMany(\App\Models\Enrollment::class)
            ->whereHas('user', fn ($q) => $q->role('Pelajar'));
    }

    public function students()
    {
        return $this->belongsToMany(\App\Models\User::class, 'enrollments')
            ->withPivot(['status', 'enrolled_at', 'completed_at', 'notes'])
            ->withTimestamps()
            ->role('Pelajar');
    }

    public function teachers()
    {
        return $this->belongsToMany(\App\Models\User::class, 'program_teacher')
            ->withTimestamps()
            ->role('Pengajar');
    }

    protected static function booted(): void
    {
        static::saving(function ($model) {
            if (empty($model->slug)) {
                $model->slug = Str::slug($model->title);
            }
        });
    }

    public function isExternal(): bool
    {
        return $this->source === 'external';
    }

    public function isInternal(): bool
    {
        return $this->source === 'internal';
    }

    public function isFinished(): bool
    {
        return ! is_null($this->ends_at) && $this->ends_at->isPast();
    }
}
