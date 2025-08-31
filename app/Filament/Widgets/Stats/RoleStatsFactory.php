<?php

namespace App\Filament\Widgets\Stats;

use App\Filament\Widgets\Stats\Contracts\RoleStatsStrategy;
use App\Filament\Widgets\Stats\Strategies\{AdminStats, GuestStats, PelajarStats, PengajarStats, SuperAdminStats};

class RoleStatsFactory
{
    public static function make(string $role): RoleStatsStrategy
    {
        return match ($role) {
            'super_admin' => new SuperAdminStats(),
            'admin' => new AdminStats(),
            'pengajar' => new PengajarStats(),
            'pelajar' => new PelajarStats(),
            default => new GuestStats(),
        };
    }
}

