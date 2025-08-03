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
        parent::boot();

        static::saving(function ($model) {
            $model->slug = Str::slug($model->title);
        });
    }
}
