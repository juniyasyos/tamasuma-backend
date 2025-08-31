<?php

namespace App\Filament\Widgets\Stats\Strategies;

use App\Filament\Widgets\Stats\Concerns\BuildsStats;
use App\Filament\Widgets\Stats\Contracts\RoleStatsStrategy;
use Filament\Widgets\StatsOverviewWidget\Stat;

class GuestStats implements RoleStatsStrategy
{
    use BuildsStats;

    public function build(): array
    {
        return [
            Stat::make('Welcome', 'Silakan login')->icon('heroicon-o-information-circle')->color('gray'),
        ];
    }
}

