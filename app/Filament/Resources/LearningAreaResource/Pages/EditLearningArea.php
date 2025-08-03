<?php

namespace App\Filament\Resources\LearningAreaResource\Pages;

use App\Filament\Resources\LearningAreaResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLearningArea extends EditRecord
{
    protected static string $resource = LearningAreaResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }

    //customize redirect after create
    public function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
