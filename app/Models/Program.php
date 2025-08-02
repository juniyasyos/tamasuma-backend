<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Program extends Model
{
    /** @use HasFactory<\Database\Factories\ProgramFactory> */
    use HasFactory;

    // add guaded
    protected $guarded = ['id'];
    // add hidden
    protected $hidden = ['created_at', 'updated_at'];

    // add fillable
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
}
