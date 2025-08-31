<?php

namespace App\Filament\Widgets\Stats\Strategies;

use App\Filament\Widgets\Stats\Concerns\BuildsStats;
use App\Filament\Widgets\Stats\Contracts\RoleStatsStrategy;

class SuperAdminStats implements RoleStatsStrategy
{
    use BuildsStats;

    public function build(): array
    {
        return [
            $this->trendStat('Total Pengguna', 'users', 'heroicon-o-user-group', 'App\\Filament\\Resources\\UserResource'),
            $this->simpleStat('Hak Akses', 'roles', 'heroicon-o-shield-check', 'App\\Filament\\Resources\\RoleResource', 'gray'),
            $this->trendStat('Program Terdaftar', 'programs', 'heroicon-o-rectangle-stack', 'App\\Filament\\Resources\\ProgramResource'),
            $this->trendStat('Area Pembelajaran', 'learning_areas', 'heroicon-o-academic-cap', 'App\\Filament\\Resources\\LearningAreaResource'),
            $this->trendStat('Mitra Kolaborasi', 'partners', 'heroicon-o-briefcase', 'App\\Filament\\Resources\\PartnerResource'),
            $this->trendWeekStat('Aktivitas Mingguan', 'breezy_sessions', 'heroicon-o-chart-bar'),
        ];
    }
}

