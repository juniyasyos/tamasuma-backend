<?php

namespace App\Filament\Widgets;

use Carbon\Carbon;
use Closure;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class StatOverview extends BaseWidget
{
    protected int|string|array $columnSpan = ['sm' => 2, 'md' => 2, 'lg' => 3, 'xl' => 4];
    protected static ?string $pollingInterval = '60s';

    protected function getStats(): array
    {
        $role = $this->detectRole();
        $key  = sprintf('dash:stats:%s:%s', Auth::id() ?? 'guest', $role);

        return Cache::remember($key, 60, fn() => $this->roleStats($role));
    }

    /** ================= Role Map ================= */
    protected function roleStats(string $role): array
    {
        return match ($role) {
            'super_admin' => [
                $this->trendStat('Total Pengguna', 'users', 'heroicon-o-user-group', 'App\\Filament\\Resources\\UserResource'),
                $this->simpleStat('Hak Akses', 'roles', 'heroicon-o-shield-check', 'App\\Filament\\Resources\\RoleResource', 'gray'),
                $this->trendStat('Program Terdaftar', 'programs', 'heroicon-o-rectangle-stack', 'App\\Filament\\Resources\\ProgramResource'),
                $this->trendStat('Area Pembelajaran', 'learning_areas', 'heroicon-o-academic-cap', 'App\\Filament\\Resources\\LearningAreaResource'),
                $this->trendStat('Mitra Kolaborasi', 'partners', 'heroicon-o-briefcase', 'App\\Filament\\Resources\\PartnerResource'),
                $this->trendWeekStat('Aktivitas Mingguan', 'breezy_sessions', 'heroicon-o-chart-bar'),
            ],
            'admin' => [
                $this->trendStat('Total Pengguna', 'users', 'heroicon-o-user-group', 'App\\Filament\\Resources\\UserResource'),
                $this->trendStat('Program Terdaftar', 'programs', 'heroicon-o-rectangle-stack', 'App\\Filament\\Resources\\ProgramResource'),
                $this->trendStat('Area Pembelajaran', 'learning_areas', 'heroicon-o-academic-cap', 'App\\Filament\\Resources\\LearningAreaResource'),
                $this->trendStat('Mitra Kolaborasi', 'partners', 'heroicon-o-briefcase', 'App\\Filament\\Resources\\PartnerResource'),
                $this->trendWeekStat('Aktivitas Mingguan', 'breezy_sessions', 'heroicon-o-chart-bar'),
            ],
            'pengajar' => [
                $this->ownedStat('Program Saya', 'programs', 'heroicon-o-rectangle-group', 'App\\Filament\\Resources\\ProgramResource'),
                $this->simpleStat('Area Pembelajaran', 'learning_areas', 'heroicon-o-academic-cap', 'App\\Filament\\Resources\\LearningAreaResource', 'success'),
                $this->simpleStat('Mitra Kolaborasi', 'partners', 'heroicon-o-briefcase', 'App\\Filament\\Resources\\PartnerResource', 'gray'),
            ],
            'pelajar' => [
                $this->simpleStat('Program Tersedia', 'programs', 'heroicon-o-rectangle-stack', 'App\\Filament\\Resources\\ProgramResource', 'info'),
                $this->simpleStat('Area Pembelajaran', 'learning_areas', 'heroicon-o-academic-cap', 'App\\Filament\\Resources\\LearningAreaResource', 'success'),
                $this->simpleStat('Mitra Kolaborasi', 'partners', 'heroicon-o-briefcase', 'App\\Filament\\Resources\\PartnerResource', 'gray'),
            ],
            default => [
                Stat::make('Welcome', 'Silakan login')->icon('heroicon-o-information-circle')->color('gray'),
            ]
        };
    }

    /** ================== Builders ================== */
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

    /** ================== Data Helpers ================== */
    protected function safeCount(string $table, ?Closure $scope = null): int
    {
        if (!$this->hasTable($table)) return 0;
        $q = DB::table($table);
        if ($this->hasColumn($table, 'deleted_at')) $q->whereNull('deleted_at');
        if ($scope) $scope($q);
        return (int)$q->count();
    }

    protected function countOwned(string $table, string $col = 'user_id'): int
    {
        if (!$this->hasTable($table) || !$this->hasColumn($table, $col)) return 0;
        $q = DB::table($table)->where($col, Auth::id());
        if ($this->hasColumn($table, 'deleted_at')) $q->whereNull('deleted_at');
        return (int)$q->count();
    }

    protected function trendPayload(string $table): array
    {
        if (!$this->hasTable($table) || !$this->hasColumn($table, 'created_at')) return ['text' => '—', 'up' => true, 'spark' => []];
        [$a, $b] = $this->trendWindowCounts($table);
        $text = $b ? sprintf('%+d%% vs w-1', (($a - $b) / max($b, 1)) * 100) : ($a ? ' +∞% vs w-1' : '—');
        return ['text' => $text, 'up' => $a >= $b, 'spark' => $this->spark($table)];
    }

    protected function spark(string $table): array
    {
        return collect(range(6, 0))->map(fn($i) => DB::table($table)
            ->whereBetween('created_at', [$this->today()->subDays($i)->startOfDay(), $this->today()->subDays($i)->endOfDay()])
            ->when($this->hasColumn($table, 'deleted_at'), fn($q) => $q->whereNull('deleted_at'))
            ->count())->all();
    }

    protected function trendWindowCounts(string $table): array
    {
        $now = $this->now();
        $w0 = [$now->copy()->subDays(6)->startOfDay(), $now->endOfDay()];
        $w1 = [$now->copy()->subDays(13)->startOfDay(), $now->copy()->subDays(7)->endOfDay()];
        $q = fn($range) => DB::table($table)->whereBetween('created_at', $range)
            ->when($this->hasColumn($table, 'deleted_at'), fn($q) => $q->whereNull('deleted_at'))->count();
        return [(int)$q($w0), (int)$q($w1)];
    }

    protected function countThisWeek(string $table): int
    {
        if (!$this->hasTable($table) || !$this->hasColumn($table, 'created_at')) return 0;
        $start = $this->today()->subDays(6)->startOfDay();
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

    /** Formatting */
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

    /** Role detection */
    protected function detectRole(): string
    {
        $u = Auth::user();
        if (!$u) return 'guest';
        $map = [
            'super_admin' => ['super admin', 'super_admin', 'owner'],
            'admin' => ['admin', 'administrator'],
            'pengajar' => ['pengajar', 'teacher', 'dosen', 'instructor'],
            'pelajar' => ['pelajar', 'student', 'mahasiswa', 'learner']
        ];
        foreach ($map as $k => $aliases) foreach ($aliases as $a) if (method_exists($u, 'hasRole') && $u->hasRole($a)) return $k;
        return 'guest';
    }
}
