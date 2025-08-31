<?php

namespace App\Filament\Widgets;

use App\Models\Enrollment;
use App\Models\Program;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

class StudentFocusWidget extends Widget
{
    protected static string $view = 'filament.widgets.student-focus-widget';

    protected int|string|array $columnSpan = ['sm' => 2, 'md' => 2, 'lg' => 3, 'xl' => 4];

    protected static ?int $sort = -90; // just after WelcomingWidget

    public static function canView(): bool
    {
        $u = Auth::user();
        if (!$u) return false;
        // Must have widget permission and be in pelajar audience
        $audience = $u->can('dashboard.view.pelajar')
            || (method_exists($u, 'hasRole') && ($u->hasRole('pelajar') || $u->hasRole('student') || $u->hasRole('mahasiswa')));
        return $u->can('view_widget_student_focus_widget') && $audience;
    }

    protected function getViewData(): array
    {
        $userId = Auth::id();
        $ttl = (int) Config::get('dashboard.stats_cache_seconds', 60);

        $key = "student:focus:{$userId}";

        return Cache::remember($key, $ttl, function () use ($userId) {
            $continue = Enrollment::query()
                ->with(['program.learningArea'])
                ->where('user_id', $userId)
                ->where('status', 'active')
                ->latest('enrolled_at')
                ->limit(3)
                ->get();

            $deadlines = Enrollment::query()
                ->with(['program'])
                ->where('user_id', $userId)
                ->where('status', 'active')
                ->whereHas('program', function ($q) {
                    $q->whereNotNull('ends_at');
                })
                ->get()
                ->filter(function ($en) {
                    $ends = optional($en->program)->ends_at;
                    return $ends && now()->diffInDays($ends, false) <= 14; // due within 14 days
                })
                ->sortBy(fn($en) => $en->program->ends_at)
                ->values()
                ->take(3);

            // recommendations: programs in same learning areas not enrolled yet
            $areaIds = Enrollment::query()
                ->where('user_id', $userId)
                ->pluck('program_id')
                ->when(true, function ($ids) {
                    return DB::table('programs')->whereIn('id', $ids)->pluck('learning_area_id');
                });

            $enrolledIds = Enrollment::query()->where('user_id', $userId)->pluck('program_id');

            $recs = Program::query()
                ->with('learningArea')
                ->when($areaIds->isNotEmpty(), fn($q) => $q->whereIn('learning_area_id', $areaIds))
                ->whereNotIn('id', $enrolledIds)
                ->latest('id')
                ->limit(3)
                ->get();

            return [
                'continueEnrollments' => $continue,
                'deadlineEnrollments' => $deadlines,
                'recommendations' => $recs,
            ];
        });
    }
}
