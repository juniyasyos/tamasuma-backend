<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LearningArea extends Model
{
    /** @use HasFactory<\Database\Factories\LearningAreaFactory> */
    use HasFactory;

    // add fillable
    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active',
    ];

    // add guaded
    protected $guarded = ['id'];
    // add hidden
    protected $hidden = ['created_at', 'updated_at'];

    public function programs()
    {
        return $this->hasMany(Program::class);
    }
}
