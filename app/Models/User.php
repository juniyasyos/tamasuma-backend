<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasAvatar;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Jeffgreco13\FilamentBreezy\Traits\TwoFactorAuthenticatable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser, HasAvatar, MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, Notifiable, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar_url',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'mute_program_enrollment_notifications' => 'boolean',
        ];
    }

    // public function getFilamentAvatarUrl(): ?string
    // {
    //     return asset($this->avatar_url);
    // }

    public function getFilamentAvatarUrl(): ?string
    {
        return $this->avatar_url ? Storage::url($this->avatar_url) : null;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return true;
    }

    public function achievements()
    {
        return $this->hasMany(\App\Models\Achievement::class);
    }

    public function enrollments()
    {
        $relation = $this->hasMany(\App\Models\Enrollment::class);

        if (! $this->hasRole('Pelajar')) {
            $relation->whereRaw('1 = 0');
        }

        return $relation;
    }

    public function programs()
    {
        $relation = $this->belongsToMany(\App\Models\Program::class, 'enrollments')
            ->withPivot(['status', 'enrolled_at', 'completed_at', 'notes'])
            ->withTimestamps();

        if (! $this->hasRole('Pelajar')) {
            $relation->whereRaw('1 = 0');
        }

        return $relation;
    }

    public function teachingPrograms()
    {
        $relation = $this->belongsToMany(\App\Models\Program::class, 'program_teacher')
            ->withTimestamps();

        if (! $this->hasRole('Pengajar')) {
            $relation->whereRaw('1 = 0');
        }

        return $relation;
    }
}
