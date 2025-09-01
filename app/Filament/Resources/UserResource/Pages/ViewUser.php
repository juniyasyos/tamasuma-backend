<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use STS\FilamentImpersonate\Pages\Actions\Impersonate;

class ViewUser extends ViewRecord
{
    protected static string $resource = UserResource::class;
    protected static bool $canCreateAnother = false;

    //customize redirect after create
    public function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('edit')
                ->label('Edit User')
                ->icon('heroicon-o-pencil-square')
                ->url(fn() => UserResource::getUrl('edit', ['record' => $this->record]))
                ->color('primary'),

            Impersonate::make()->record($this->getRecord()),
        ];
    }

    protected function getBreadcrumbs(): array
    {
        $title = \App\Filament\Support\Breadcrumbs::recordTitle($this->record);
        return [
            __('Dashboard') => \App\Filament\Support\Breadcrumbs::panelDashboardUrl(),
            UserResource::getPluralModelLabel() => UserResource::getUrl('index'),
            $title => null,
        ];
    }
}
