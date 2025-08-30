<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class ProgramEnrolmentChart extends ApexChartWidget
{
    protected static ?string $chartId = 'programEnrolmentChart';
    protected static ?string $heading = '🎓 Program Baru per Minggu';
    protected static ?int $sort = 10;

    protected function getOptions(): array
    {
        $labels = [];
        $data   = [];

        foreach (range(5, 0) as $i) {
            $week = Carbon::now()->subWeeks($i);
            $labels[] = 'Minggu ' . $week->format('W');
            $data[] = DB::table('programs')
                ->whereBetween('created_at', [$week->startOfWeek(), $week->endOfWeek()])
                ->when(
                    DB::getSchemaBuilder()->hasColumn('programs', 'deleted_at'),
                    fn($q) => $q->whereNull('deleted_at')
                )
                ->count();
        }

        return [
            'chart' => [
                'type' => 'bar',
                'height' => 300,
            ],
            'series' => [[
                'name' => 'Program Baru',
                'data' => $data,
            ]],
            'xaxis' => [
                'categories' => $labels,
            ],
            'colors' => ['#3b82f6'], // biru
            'plotOptions' => [
                'bar' => [
                    'borderRadius' => 6,
                    'columnWidth' => '45%',
                ],
            ],
            'dataLabels' => ['enabled' => true],
        ];
    }
}
