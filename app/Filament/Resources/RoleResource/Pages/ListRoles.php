<?php

namespace App\Filament\Resources\RoleResource\Pages;

use App\Filament\Resources\RoleResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRoles extends ListRecords
{
    protected static string $resource = RoleResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    protected function getBreadcrumbs(): array
    {
        return [
            __('Dashboard') => \App\Filament\Support\Breadcrumbs::panelDashboardUrl(),
            RoleResource::getPluralModelLabel() => RoleResource::getUrl('index'),
        ];
    }
}
