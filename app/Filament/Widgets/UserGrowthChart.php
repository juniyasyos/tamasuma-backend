<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class UserGrowthChart extends ApexChartWidget
{
    protected static ?string $chartId = 'userGrowthChart';
    protected static ?string $heading = '📈 Pertumbuhan User (14 Hari)';

    protected function getOptions(): array
    {
        $labels = [];
        $data   = [];

        foreach (range(13, 0) as $i) {
            $day = Carbon::today()->subDays($i);
            $labels[] = $day->format('d M');
            $data[] = DB::table('users')
                ->whereDate('created_at', $day)
                ->when(
                    DB::getSchemaBuilder()->hasColumn('users', 'deleted_at'),
                    fn($q) => $q->whereNull('deleted_at')
                )
                ->count();
        }

        return [
            'chart' => [
                'type' => 'area',
                'height' => 300,
                'toolbar' => ['show' => false],
            ],
            'series' => [[
                'name' => 'User Baru',
                'data' => $data,
            ]],
            'xaxis' => [
                'categories' => $labels,
            ],
            'colors' => ['#10b981'], // hijau emerald
            'stroke' => ['curve' => 'smooth'],
            'dataLabels' => ['enabled' => false],
            'fill' => [
                'type' => 'gradient',
                'gradient' => [
                    'shadeIntensity' => 0.4,
                    'opacityFrom' => 0.7,
                    'opacityTo' => 0.3,
                ],
            ],
        ];
    }
}
