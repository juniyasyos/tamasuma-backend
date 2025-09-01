<?php

namespace App\Filament\Resources\AchievementResource\Pages;

use App\Filament\Resources\AchievementResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListAchievements extends ListRecords
{
    protected static string $resource = AchievementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getBreadcrumbs(): array
    {
        return [
            __('Dashboard') => \App\Filament\Support\Breadcrumbs::panelDashboardUrl(),
            AchievementResource::getPluralModelLabel() => AchievementResource::getUrl('index'),
        ];
    }
}
