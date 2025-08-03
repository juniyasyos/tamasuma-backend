<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Material extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $guarded = ['id'];
    protected $hidden = ['created_at', 'updated_at'];

    protected $fillable = [
        'unit_id',
        'title',
        'slug',
        'type',
        'content',
        'duration_minutes',
        'order',
        'is_mandatory',
        'is_visible',
    ];

    public function unit()
    {
        return $this->belongsTo(Unit::class);
    }

    public function materials()
    {
        return $this->hasMany(Material::class)->orderBy('order');
    }

    protected static function booted(): void
    {
        static::creating(function (self $material) {
            if (empty($material->slug)) {
                $material->slug = static::generateUniqueSlug($material->title);
            }
        });

        static::updating(function (self $material) {
            if (empty($material->slug)) {
                $material->slug = static::generateUniqueSlug($material->title);
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
