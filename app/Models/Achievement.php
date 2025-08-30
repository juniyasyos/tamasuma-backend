<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Achievement extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'title', 'category', 'issuer', 'achieved_at', 'proof_image', 'url', 'description', 'is_featured',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'achieved_at' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
