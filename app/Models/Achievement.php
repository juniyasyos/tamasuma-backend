<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Achievement extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'category',
        'issuer',
        'achieved_at',
        'proof_image',
        'url',
        'description',
        'is_featured',
        'visibility',
        'tags',
        'created_by_id',
        'created_via',
        'source_type',
        'source_id',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'achieved_at' => 'date',
        'tags' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            // Auto-assign provenance when not explicitly provided
            if (empty($model->created_via)) {
                $actor = Auth::user();
                if ($actor) {
                    $model->created_by_id = $model->created_by_id ?: $actor->id;

                    // If actor creates for different target user, mark as admin-managed
                    if ($model->user_id && (int) $model->user_id !== (int) $actor->id) {
                        $model->created_via = 'admin';
                    } else {
                        $model->created_via = 'self';
                    }
                } else {
                    $model->created_via = 'self';
                }
            }
        });
    }
}
