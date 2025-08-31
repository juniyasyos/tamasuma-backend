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
use App\Filament\Widgets\Stats\RoleStatsFactory;

/**
 * StatOverview widget
 *
 * - Builds role/audience-specific stats for the dashboard.
 * - Uses permission slugs (from config/dashboard.php) to determine which
 *   variant of the widget the user should see, avoiding hard-coded role names.
 * - Caches computed stats for a short time to reduce DB hits.
 * - Trend and sparkline window sizes are configurable.
 */
class StatOverview extends BaseWidget
{
    /**
     * Controls visibility of the widget itself.
     * Requires the user to have the 'view_widget_stat_overview' permission.
     * Additionally, it is explicitly hidden for Pelajar (student) role.
     */
    public static function canView(): bool
    {
        $u = Auth::user();
        if (!$u) return false;

        // Hide for Pelajar explicitly (by permission or role alias)
        $rolePermissions = (array) Config::get('dashboard.permissions', [
            'super_admin' => 'dashboard.view.super_admin',
            'admin' => 'dashboard.view.admin',
            'pengajar' => 'dashboard.view.pengajar',
            'pelajar' => 'dashboard.view.pelajar',
        ]);
        $pelajarPerm = $rolePermissions['pelajar'] ?? 'dashboard.view.pelajar';
        if (method_exists($u, 'can') && $u->can($pelajarPerm)) {
            return false;
        }

        $aliasesMap = (array) Config::get('dashboard.role_fallback.aliases', [
            'pelajar' => ['pelajar', 'student', 'mahasiswa', 'learner'],
        ]);
        foreach ((array) ($aliasesMap['pelajar'] ?? []) as $alias) {
            if (method_exists($u, 'hasRole') && $u->hasRole($alias)) {
                return false;
            }
        }

        return $u?->can('view_widget_stat_overview') ?? false;
    }

    protected int|string|array $columnSpan = ['sm' => 2, 'md' => 2, 'lg' => 3, 'xl' => 4];
    protected static ?string $pollingInterval = '60s';

    /**
     * Return the stats to render for the current user audience.
     * Uses cache to avoid heavy DB calls on every refresh.
     *
     * @return array<int, Stat>
     */
    protected function getStats(): array
    {
        $role = $this->detectRole();
        $key  = sprintf('dash:stats:%s:%s', Auth::id() ?? 'guest', $role);

        $ttl = (int) Config::get('dashboard.stats_cache_seconds', 60);
        return Cache::remember($key, $ttl, fn() => $this->roleStats($role));
    }

    /** ================= Role Map ================= */
    /**
     * Build stats for a specific audience key.
     *
     * @param string $role One of: super_admin, admin, pengajar, pelajar, guest
     * @return array<int, Stat>
     */
    protected function roleStats(string $role): array
    {
        return RoleStatsFactory::make($role)->build();
    }

    /** ================== Builders ================== */
    /**
     * Create a trend stat card with sparkline and delta description.
     */
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

    /**
     * Create a simple count stat card.
     */
    protected function simpleStat(string $label, string $table, string $icon, string $resource, string $color = 'info'): Stat
    {
        return Stat::make($label, $this->fmt($this->safeCount($table)))
            ->icon($icon)->color($color)->url($this->resourceUrl($resource));
    }

    /**
     * Create a stat card counting resources owned by the current user.
     */
    protected function ownedStat(string $label, string $table, string $icon, string $resource): Stat
    {
        return Stat::make($label, $this->fmt($this->countOwned($table)))
            ->icon($icon)->color('info')->url($this->resourceUrl($resource));
    }

    /**
     * Create a trend stat card for the last configured window (default 7 days).
     */
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
    /**
     * Safe counter for a DB table with optional scope and soft-delete awareness.
     */
    protected function safeCount(string $table, ?Closure $scope = null): int
    {
        if (!$this->hasTable($table)) return 0;
        $q = DB::table($table);
        if ($this->hasColumn($table, 'deleted_at')) $q->whereNull('deleted_at');
        if ($scope) $scope($q);
        return (int)$q->count();
    }

    /**
     * Count rows owned by the authenticated user in a given table.
     */
    protected function countOwned(string $table, string $col = 'user_id'): int
    {
        if (!$this->hasTable($table) || !$this->hasColumn($table, $col)) return 0;
        $q = DB::table($table)->where($col, Auth::id());
        if ($this->hasColumn($table, 'deleted_at')) $q->whereNull('deleted_at');
        return (int)$q->count();
    }

    /**
     * Generate trend payload including percentage delta and sparkline.
     *
     * @return array{text: string, up: bool, spark: array<int,int>}
     */
    protected function trendPayload(string $table): array
    {
        if (!$this->hasTable($table) || !$this->hasColumn($table, 'created_at')) return ['text' => '—', 'up' => true, 'spark' => []];
        [$a, $b] = $this->trendWindowCounts($table);
        $pct = $b ? (($a - $b) / max($b, 1)) * 100 : null;
        $text = $pct !== null ? sprintf('%+d%% vs prev', (int) $pct) : ($a ? ' +∞% vs prev' : '—');
        return ['text' => $text, 'up' => $a >= $b, 'spark' => $this->spark($table)];
    }

    /**
     * Build sparkline values for the last N days (configurable).
     *
     * @return array<int,int>
     */
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

    /**
     * Count rows in the current vs previous comparison window.
     * Window size is configured via dashboard.trend.window_days.
     *
     * @return array{0:int,1:int} [$current, $previous]
     */
    protected function trendWindowCounts(string $table): array
    {
        $now = $this->now();
        $d = max(1, (int) Config::get('dashboard.trend.window_days', 7));
        $w0 = [$now->copy()->subDays($d - 1)->startOfDay(), $now->endOfDay()];
        $w1 = [$now->copy()->subDays(2 * $d - 1)->startOfDay(), $now->copy()->subDays($d)->endOfDay()];
        $q = fn($range) => DB::table($table)->whereBetween('created_at', $range)
            ->when($this->hasColumn($table, 'deleted_at'), fn($q) => $q->whereNull('deleted_at'))->count();
        return [(int)$q($w0), (int)$q($w1)];
    }

    /**
     * Count rows created within the current comparison window (default last 7 days).
     */
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

    /**
     * Resolve index URL for a Filament Resource FQCN if available.
     */
    protected function resourceUrl(string $fqcn): ?string
    {
        return class_exists($fqcn) && method_exists($fqcn, 'getUrl') ? $fqcn::getUrl('index') : null;
    }

    /**
     * Whether a DB table exists.
     */
    protected function hasTable(string $t): bool
    {
        return DB::getSchemaBuilder()->hasTable($t);
    }
    /**
     * Whether a column exists on a table.
     */
    protected function hasColumn(string $t, string $c): bool
    {
        return DB::getSchemaBuilder()->hasColumn($t, $c);
    }

    /** Formatting */
    /**
     * Format a number into human-friendly notation (K, M, B).
     */
    protected function fmt(int|float $n): string
    {
        $a = abs($n);
        return $a >= 1e9 ? $this->num($n / 1e9) . 'B' : ($a >= 1e6 ? $this->num($n / 1e6) . 'M' : ($a >= 1e3 ? $this->num($n / 1e3) . 'K' : number_format($n, 0, ',', '.')));
    }
    /**
     * Format a float with locale-friendly separator.
     */
    protected function num(float $n, int $d = 1): string
    {
        return number_format($n, $d, ',', '.');
    }

    /**
     * Get the current time using the app timezone.
     */
    protected function now(): Carbon
    {
        return Carbon::now(Config::get('app.timezone', 'UTC'));
    }
    /**
     * Get today using the app timezone.
     */
    protected function today(): Carbon
    {
        return Carbon::today(Config::get('app.timezone', 'UTC'));
    }

    /**
     * Determine the audience (dashboard variant) for the current user.
     *
     * Priority:
     * 1) Check configured permission slugs (dashboard.permissions) — first match wins.
     * 2) Optional fallback to role-name aliases (dashboard.role_fallback.aliases) if enabled.
     */
    protected function detectRole(): string
    {
        $u = Auth::user();
        if (!$u) return 'guest';

        // Prefer permission-based detection (configurable at the ACL layer)
        $rolePermissions = (array) Config::get('dashboard.permissions', [
            'super_admin' => 'dashboard.view.super_admin',
            'admin' => 'dashboard.view.admin',
            'pengajar' => 'dashboard.view.pengajar',
            'pelajar' => 'dashboard.view.pelajar',
        ]);
        foreach ($rolePermissions as $role => $perm) {
            if (method_exists($u, 'can') && $u->can($perm)) {
                return $role;
            }
        }

        // Fallback to role-name based detection (backwards compatibility)
        if ((bool) Config::get('dashboard.role_fallback.enabled', true)) {
            $aliasesMap = (array) Config::get('dashboard.role_fallback.aliases', [
                'super_admin' => ['super admin', 'super_admin', 'owner'],
                'admin' => ['admin', 'administrator'],
                'pengajar' => ['pengajar', 'teacher', 'dosen', 'instructor'],
                'pelajar' => ['pelajar', 'student', 'mahasiswa', 'learner'],
            ]);
            foreach ($aliasesMap as $k => $aliases) {
                foreach ((array) $aliases as $a) {
                    if (method_exists($u, 'hasRole') && $u->hasRole($a)) {
                        return (string) $k;
                    }
                }
            }
        }

        return 'guest';
    }
}

