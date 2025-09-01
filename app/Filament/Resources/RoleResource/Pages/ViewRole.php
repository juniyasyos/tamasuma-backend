<?php

namespace App\Filament\Resources\RoleResource\Pages;

use App\Filament\Resources\RoleResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewRole extends ViewRecord
{
    protected static string $resource = RoleResource::class;

    protected function getActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    protected function getBreadcrumbs(): array
    {
        $title = \App\Filament\Support\Breadcrumbs::recordTitle($this->record);
        return [
            __('Dashboard') => \App\Filament\Support\Breadcrumbs::panelDashboardUrl(),
            RoleResource::getPluralModelLabel() => RoleResource::getUrl('index'),
            $title => null,
        ];
    }
}
