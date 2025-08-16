<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Partner extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $hidden = ['created_at', 'updated_at'];

    protected $fillable = [
        'name',
        'slug',
        'description',
        'website_url',
        'logo_path',
        'address',
        'contact_email',
        'contact_phone',
        'is_visible',
    ];

    protected static function booted(): void
    {
        static::creating(function ($partner) {
            $partner->slug = Str::slug($partner->name);
        });
    }
}
