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
        parent::boot();

        static::saving(function ($model) {
            $model->slug = Str::slug($model->title);
        });
    }
}
