<?php

namespace App\Filament\Widgets\Stats\Concerns;

use Carbon\Carbon;
use Closure;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

trait BuildsStats
{
    /** Builders */
    protected function trendStat(string $label, string $table, string $icon, string $resource, ?string $fallbackColor = 'warning'): Stat
    {
        $trend = $this->trendPayload($table);
        return Stat::make($label, $this->fmt($this->safeCount($table)))
            ->icon($icon)
            ->color($trend['up'] ? 'success' : $fallbackColor)
            ->description($trend['text'])
            ->descriptionIcon($trend['up'] ? 'heroicon-o-arrow-trending-up' : 'heroicon-o-arrow-trending-down')
            ->chart($trend['spark'])
            ->url($this->resourceUrl($resource));
    }

    protected function simpleStat(string $label, string $table, string $icon, string $resource, string $color = 'info'): Stat
    {
        return Stat::make($label, $this->fmt($this->safeCount($table)))
            ->icon($icon)->color($color)->url($this->resourceUrl($resource));
    }

    protected function ownedStat(string $label, string $table, string $icon, string $resource): Stat
    {
        return Stat::make($label, $this->fmt($this->countOwned($table)))
            ->icon($icon)->color('info')->url($this->resourceUrl($resource));
    }

    protected function trendWeekStat(string $label, string $table, string $icon, ?string $resource = null, ?string $fallbackColor = 'warning'): Stat
    {
        $trend = $this->trendPayload($table);
        return Stat::make($label, $this->fmt($this->countThisWeek($table)))
            ->icon($icon)
            ->color($trend['up'] ? 'success' : $fallbackColor)
            ->description($trend['text'])
            ->descriptionIcon($trend['up'] ? 'heroicon-o-arrow-trending-up' : 'heroicon-o-arrow-trending-down')
            ->chart($trend['spark'])
            ->url($resource ? $this->resourceUrl($resource) : null);
    }

    /** Data helpers */
    protected function safeCount(string $table, ?Closure $scope = null): int
    {
        if (!$this->hasTable($table)) return 0;
        $q = DB::table($table);
        if ($this->hasColumn($table, 'deleted_at')) $q->whereNull('deleted_at');
        if ($scope) $scope($q);
        return (int) $q->count();
    }

    protected function countOwned(string $table, string $col = 'user_id'): int
    {
        if (!$this->hasTable($table) || !$this->hasColumn($table, $col)) return 0;
        $q = DB::table($table)->where($col, Auth::id());
        if ($this->hasColumn($table, 'deleted_at')) $q->whereNull('deleted_at');
        return (int) $q->count();
    }

    protected function trendPayload(string $table): array
    {
        if (!$this->hasTable($table) || !$this->hasColumn($table, 'created_at')) return ['text' => '—', 'up' => true, 'spark' => []];
        [$a, $b] = $this->trendWindowCounts($table);
        $pct = $b ? (($a - $b) / max($b, 1)) * 100 : null;
        $text = $pct !== null ? sprintf('%+d%% vs prev', (int) $pct) : ($a ? ' +∞% vs prev' : '—');
        return ['text' => $text, 'up' => $a >= $b, 'spark' => $this->spark($table)];
    }

    protected function spark(string $table): array
    {
        $days = max(1, (int) Config::get('dashboard.trend.spark_days', 7));
        $range = range($days - 1, 0);
        return collect($range)
            ->map(function ($i) use ($table) {
                return DB::table($table)
                    ->whereBetween('created_at', [
                        $this->today()->subDays($i)->startOfDay(),
                        $this->today()->subDays($i)->endOfDay(),
                    ])
                    ->when($this->hasColumn($table, 'deleted_at'), fn($q) => $q->whereNull('deleted_at'))
                    ->count();
            })
            ->all();
    }

    protected function trendWindowCounts(string $table): array
    {
        $now = $this->now();
        $d = max(1, (int) Config::get('dashboard.trend.window_days', 7));
        $w0 = [$now->copy()->subDays($d - 1)->startOfDay(), $now->endOfDay()];
        $w1 = [$now->copy()->subDays(2 * $d - 1)->startOfDay(), $now->copy()->subDays($d)->endOfDay()];
        $q = fn($range) => DB::table($table)->whereBetween('created_at', $range)
            ->when($this->hasColumn($table, 'deleted_at'), fn($q) => $q->whereNull('deleted_at'))->count();
        return [(int) $q($w0), (int) $q($w1)];
    }

    protected function countThisWeek(string $table): int
    {
        if (!$this->hasTable($table) || !$this->hasColumn($table, 'created_at')) return 0;
        $d = max(1, (int) Config::get('dashboard.trend.window_days', 7));
        $start = $this->today()->subDays($d - 1)->startOfDay();
        return (int) DB::table($table)
            ->whereBetween('created_at', [$start, $this->now()])
            ->when($this->hasColumn($table, 'deleted_at'), fn($q) => $q->whereNull('deleted_at'))
            ->count();
    }

    protected function resourceUrl(string $fqcn): ?string
    {
        return class_exists($fqcn) && method_exists($fqcn, 'getUrl') ? $fqcn::getUrl('index') : null;
    }

    protected function hasTable(string $t): bool
    {
        return DB::getSchemaBuilder()->hasTable($t);
    }

    protected function hasColumn(string $t, string $c): bool
    {
        return DB::getSchemaBuilder()->hasColumn($t, $c);
    }

    protected function fmt(int|float $n): string
    {
        $a = abs($n);
        return $a >= 1e9 ? $this->num($n / 1e9) . 'B' : ($a >= 1e6 ? $this->num($n / 1e6) . 'M' : ($a >= 1e3 ? $this->num($n / 1e3) . 'K' : number_format($n, 0, ',', '.')));
    }

    protected function num(float $n, int $d = 1): string
    {
        return number_format($n, $d, ',', '.');
    }

    protected function now(): Carbon
    {
        return Carbon::now(Config::get('app.timezone', 'UTC'));
    }

    protected function today(): Carbon
    {
        return Carbon::today(Config::get('app.timezone', 'UTC'));
    }
}

