<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProgramResource\Pages;
use App\Filament\Resources\ProgramResource\Schema as ProgramSchema;
use App\Filament\Resources\ProgramResource\Table as ProgramTable;
use App\Filament\Resources\ProgramResource\Infolist as ProgramInfolist;
use App\Models\Program;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Filament\Tables\Actions\{Action, ActionGroup, ViewAction, EditAction, ReplicateAction};
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section as InfoSection;
use Filament\Infolists\Components\Grid as InfoGrid;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\IconEntry as InfoIconEntry;
use Filament\Infolists\Components\ViewEntry as InfoViewEntry;
use Filament\Infolists\Components\Tabs as InfoTabs;
use Filament\Infolists\Components\Tabs\Tab as InfoTab;
use Illuminate\Validation\Rule;

class ProgramResource extends Resource
{
    protected static ?string $model = Program::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Content Management';
    protected static ?string $navigationLabel = 'Program Pembelajaran';
    protected static ?string $pluralModelLabel = 'Program';
    protected static ?string $modelLabel = 'Program Pembelajaran';

    // public static function getNavigationBadge(): ?string
    // {
    //     return (string) Program::query()->where('is_published', false)->count();
    // }

    // public static function getNavigationBadgeTooltip(): ?string
    // {
    //     return 'Jumlah program berstatus Draft';
    // }

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
        return $form->schema(ProgramSchema::make());
    }

    public static function table(Table $table): Table
    {
        return ProgramTable::make($table);
    }

    public static function getRelations(): array
    {
        return [
            \App\Filament\Resources\ProgramResource\RelationManagers\EnrollmentsRelationManager::class,
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

    public static function infolist(Infolist $infolist): Infolist
    {
        return ProgramInfolist::make($infolist);
    }
}
