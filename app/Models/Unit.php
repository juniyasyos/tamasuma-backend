<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Unit extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    protected $hidden = ['created_at', 'updated_at'];

    protected $fillable = [
        'program_id',
        'title',
        'slug',
        'summary',
        'order',
        'is_visible',
    ];

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function materials()
    {
        return $this->hasMany(Material::class)->orderBy('order');
    }

    protected static function booted(): void
    {
        static::creating(function (self $unit) {
            if (empty($unit->slug)) {
                $unit->slug = static::generateUniqueSlug($unit->title);
            }
        });

        static::updating(function (self $unit) {
            if (empty($unit->slug)) {
                $unit->slug = static::generateUniqueSlug($unit->title);
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
