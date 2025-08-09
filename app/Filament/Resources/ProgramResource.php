<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProgramResource\Pages;
use App\Filament\Resources\ProgramResource\ProgramResourceForm;
use App\Filament\Resources\ProgramResource\ProgramResourceTable;
use App\Models\Program;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Filament\Resources\ProgramResource\RelationManagers\ParticipantsRelationManager;

class ProgramResource extends Resource
{
    protected static ?string $model = Program::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Content Management';
    protected static ?string $navigationLabel = 'Program Pembelajaran';
    protected static ?string $pluralModelLabel = 'Program';
    protected static ?string $modelLabel = 'Program Pembelajaran';

    public static function getNavigationBadge(): ?string
    {
        return (string) Program::query()->where('is_published', false)->count();
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Jumlah program berstatus Draft';
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['title', 'slug', 'platform', 'learningArea.name'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Bidang'   => optional($record->learningArea)->name,
            'Level'    => Str::title($record->level ?? '-'),
            'Sumber'   => Str::title($record->source ?? '-'),
            'Platform' => $record->platform ?: '-',
        ];
    }

    public static function form(Form $form): Form
    {
        return ProgramResourceForm::make($form);
    }

    public static function table(Table $table): Table
    {
        return ProgramResourceTable::make($table);
    }

    public static function getRelations(): array
    {
        return [
            ParticipantsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPrograms::route('/'),
            'create' => Pages\CreateProgram::route('/create'),
            'view' => Pages\ViewProgram::route('/{record}'),
            'edit' => Pages\EditProgram::route('/{record}/edit'),
        ];
    }
}
