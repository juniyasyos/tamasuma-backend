<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    /** @use HasFactory<\Database\Factories\UnitFactory> */
    use HasFactory;

    // add guaded
    protected $guarded = ['id'];
    // add hidden
    protected $hidden = ['created_at', 'updated_at'];
    // add fillable
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
        return $this->hasMany(Material::class);
    }
}
