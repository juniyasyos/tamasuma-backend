<?php

namespace App\Filament\Widgets;

use Illuminate\Support\Facades\DB;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;
use Illuminate\Support\Facades\Auth;

class MediaStorageChart extends ApexChartWidget
{
    protected static ?string $chartId = 'mediaStorageChart';
    protected static ?string $heading = '☁️ Penggunaan Media Storage';
    protected static ?int $sort = 20;

    public static function canView(): bool
    {
        return Auth::user()?->can('view_widget_media_storage_chart') ?? false;
    }

    /** full width di dashboard */
    public function getColumnSpan(): int|string|array
    {
        return 'full';
    }

    protected function getOptions(): array
    {
        $used = 0;
        if (
            DB::getSchemaBuilder()->hasTable('media')
            && DB::getSchemaBuilder()->hasColumn('media', 'size')
        ) {
            $used = round(DB::table('media')->sum('size') / 1024 / 1024, 1); // MB
        }

        $quota = 1000; 
        $free  = max($quota - $used, 0);

        return [
            'chart' => [
                'type' => 'donut',
                'height' => 400,
            ],
            'series' => [$used, $free],
            'labels' => [
                "Dipakai: {$used} MB",
                "Tersedia: {$free} MB",
            ],
            'colors' => ['#ef4444', '#10b981'],
            'legend' => [
                'position' => 'bottom',
                'fontSize' => '14px',
            ],
            // tidak ada closure, hanya data statis dari PHP
            'title' => [
                'text' => "Total Kuota: {$quota} MB",
                'align' => 'center',
                'style' => [
                    'fontSize' => '16px',
                    'fontWeight' => 'bold',
                ],
            ],
        ];
    }
}
