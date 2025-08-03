<?php

namespace App\Filament\Resources\LearningAreaResource\Pages;

use App\Filament\Resources\LearningAreaResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLearningAreas extends ListRecords
{
    protected static string $resource = LearningAreaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
