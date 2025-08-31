<?php

namespace App\Filament\Widgets\Stats\Contracts;

use Filament\Widgets\StatsOverviewWidget\Stat;

interface RoleStatsStrategy
{
    /**
     * Build the stats cards for a specific audience/role.
     *
     * @return array<int, Stat>
     */
    public function build(): array;
}

