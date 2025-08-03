<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class LearningArea extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'is_active',
    ];

    protected $guarded = ['id'];
    protected $hidden = ['created_at', 'updated_at'];

    public function programs()
    {
        return $this->hasMany(Program::class);
    }

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            $model->slug = $model->slug ?? Str::slug($model->name);
        });

        static::updating(function (self $model) {
            if ($model->isDirty('name') && empty($model->slug)) {
                $model->slug = Str::slug($model->name);
            }
        });
    }
}
