<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;

class Material extends Model
{
    /** @use HasFactory<\Database\Factories\MaterialFactory> */
    use HasFactory, InteractsWithMedia;

    // add guaded
    protected $guarded = ['id'];
    // add hidden
    protected $hidden = ['created_at', 'updated_at'];
    // add fillable

    protected $fillable = [
        'unit_id',
        'title',
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
}
