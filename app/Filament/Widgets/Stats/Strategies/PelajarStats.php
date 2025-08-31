<?php

namespace App\Filament\Widgets\Stats\Strategies;

use App\Filament\Widgets\Stats\Concerns\BuildsStats;
use App\Filament\Widgets\Stats\Contracts\RoleStatsStrategy;

class PelajarStats implements RoleStatsStrategy
{
    use BuildsStats;

    public function build(): array
    {
        return [
            $this->simpleStat('Program Tersedia', 'programs', 'heroicon-o-rectangle-stack', 'App\\Filament\\Resources\\ProgramResource', 'info'),
            $this->simpleStat('Area Pembelajaran', 'learning_areas', 'heroicon-o-academic-cap', 'App\\Filament\\Resources\\LearningAreaResource', 'success'),
            $this->simpleStat('Mitra Kolaborasi', 'partners', 'heroicon-o-briefcase', 'App\\Filament\\Resources\\PartnerResource', 'gray'),
        ];
    }
}

