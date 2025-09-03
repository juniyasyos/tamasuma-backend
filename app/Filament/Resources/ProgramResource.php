<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProgramResource\Pages;
use App\Filament\Resources\ProgramResource\Schema as ProgramSchema;
use App\Filament\Resources\ProgramResource\Table as ProgramTable;
use App\Filament\Resources\ProgramResource\Infolist as ProgramInfolist;
use App\Models\Program;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Filament\Infolists\Infolist;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;

class ProgramResource extends Resource implements HasShieldPermissions
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

    /**
     * Extend Filament Shield resource permissions with domain-specific actions
     * so they appear in the Role permission selector under this resource.
     */
    public static function getPermissionPrefixes(): array
    {
        $defaults = (array) config('filament-shield.permission_prefixes.resource', [
            'view', 'view_any', 'create', 'update', 'restore', 'restore_any', 'replicate', 'reorder', 'delete', 'delete_any', 'force_delete', 'force_delete_any',
        ]);

        $extras = [
            'update_any',
            'view_unpublished',
            'publish',
            'unpublish',
        ];

        return array_values(array_unique(array_merge($defaults, $extras)));
    }
}
