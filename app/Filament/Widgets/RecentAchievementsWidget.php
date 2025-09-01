<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class RecentAchievementsWidget extends Widget
{
    protected static string $view = 'filament.widgets.recent-achievements-widget';

    public static function canView(): bool
    {
        return Auth::check();
    }

    protected function getViewData(): array
    {
        $user = Auth::user();
        $items = $user?->achievements()->latest('achieved_at')->take(5)->get() ?? collect();
        return [
            'items' => $items,
        ];
    }
}

