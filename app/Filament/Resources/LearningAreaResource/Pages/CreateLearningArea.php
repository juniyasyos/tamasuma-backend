<?php

namespace App\Filament\Resources\LearningAreaResource\Pages;

use App\Filament\Resources\LearningAreaResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateLearningArea extends CreateRecord
{
    protected static string $resource = LearningAreaResource::class;
    protected static bool $canCreateAnother = false;

    //customize redirect after create
    public function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    public function getBreadcrumbs(): array
    {
        return [
            __('Dashboard') => \App\Filament\Support\Breadcrumbs::panelDashboardUrl(),
            LearningAreaResource::getPluralModelLabel() => LearningAreaResource::getUrl('index'),
            __('Tambah') => null,
        ];
    }
}
