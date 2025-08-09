<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LearningAreaResource\Pages;
use App\Filament\Resources\LearningAreaResource\LearningAreaResourceForm;
use App\Filament\Resources\LearningAreaResource\LearningAreaResourceTable;
use App\Models\LearningArea;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;

class LearningAreaResource extends Resource
{
    protected static ?string $model = LearningArea::class;
    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static ?string $navigationLabel = 'Bidang Pembelajaran';
    protected static ?string $pluralModelLabel = 'Bidang Pembelajaran';
    protected static ?string $modelLabel = 'Bidang Pembelajaran';
    protected static ?string $navigationGroup = 'Content Management';

    public static function form(Form $form): Form
    {
        return LearningAreaResourceForm::make($form);
    }

    public static function table(Table $table): Table
    {
        return LearningAreaResourceTable::make($table);
    }

    public static function getRelations(): array
    {
        return [
            // e.g. RelationManagers\ProgramsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLearningAreas::route('/'),
            'create' => Pages\CreateLearningArea::route('/create'),
            'edit' => Pages\EditLearningArea::route('/{record}/edit'),
        ];
    }
}
