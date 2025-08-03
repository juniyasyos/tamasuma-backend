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
        'estimated_minutes',
        'is_published',
    ];

    public function learningArea()
    {
        return $this->belongsTo(LearningArea::class);
    }

    public function units()
    {
        return $this->hasMany(Unit::class);
    }

    protected static function booted(): void
    {
        static::creating(function (self $program) {
            if (empty($program->slug)) {
                $program->slug = static::generateUniqueSlug($program->title);
            }
        });

        static::updating(function (self $program) {
            if (empty($program->slug)) {
                $program->slug = static::generateUniqueSlug($program->title);
            }
        });
    }

    protected static function generateUniqueSlug(string $title): string
    {
        $baseSlug = Str::slug($title);
        $slug = $baseSlug;
        $counter = 1;

        while (static::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter++;
        }

        return $slug;
    }
}
