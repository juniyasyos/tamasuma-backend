<?php

namespace App\Filament\Resources\LearningAreaResource\Widgets;

use App\Models\LearningArea;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class LearningAreaStats extends BaseWidget
{
    protected static ?string $pollingInterval = '30s';
    protected function getCards(): array
    {
        $total     = LearningArea::count();
        $active    = LearningArea::where('is_active', true)->count();
        $inactive  = $total - $active;

        return [
            Stat::make('Total Bidang', number_format($total))
                ->description('Total semua bidang')
                ->icon('heroicon-m-rectangle-stack'),

            Stat::make('Aktif', number_format($active))
                ->description('Bidang yang tampil')
                ->color('success')
                ->icon('heroicon-m-check-circle'),

            Stat::make('Nonaktif', number_format($inactive))
                ->description('Perlu ditinjau')
                ->color('gray')
                ->icon('heroicon-m-no-symbol'),
        ];
    }
}
