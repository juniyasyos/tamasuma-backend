<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LearningAreaResource\Pages;
use App\Filament\Resources\LearningAreaResource\LearningAreaResourceForm;
use App\Filament\Resources\LearningAreaResource\LearningAreaResourceTable;
use App\Filament\Resources\LearningAreaResource\Widgets\LearningAreaStats;
use App\Models\LearningArea;
use Filament\Resources\Resource;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class LearningAreaResource extends Resource
{
    protected static ?string $model = LearningArea::class;

    protected static ?string $navigationIcon  = 'heroicon-o-book-open';
    protected static ?string $navigationGroup = 'Content Management';
    protected static ?string $navigationLabel = 'Bidang Pembelajaran';
    protected static ?string $pluralModelLabel = 'Bidang Pembelajaran';
    protected static ?string $modelLabel = 'Bidang Pembelajaran';

    // Letakkan di urutan yang kamu mau (mis. ke-2 atau ke-3)
    protected static ?int $navigationSort = 1;

    /** Badge menu: jumlah area nonaktif */
    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::query()->where('is_active', false)->count();
        return $count ? (string) $count : null;
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Jumlah bidang yang nonaktif';
    }

    /** Global search */
    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'slug', 'description'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Slug'   => $record->slug,
            'Status' => $record->is_active ? 'Aktif' : 'Nonaktif',
        ];
    }

    /** Optional: kalau pakai SoftDeletes di model, hilangkan scope default di query resource */
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        // Uncomment jika LearningArea pakai SoftDeletes:
        // use Illuminate\Database\Eloquent\SoftDeletingScope; (di atas)
        // $query->withoutGlobalScopes([SoftDeletingScope::class]);

        return $query;
    }

    public static function form(Form $form): Form
    {
        return LearningAreaResourceForm::make($form);
    }

    public static function table(Table $table): Table
    {
        return LearningAreaResourceTable::make($table);
    }

    /** Widget header yg tampil di List & View page */
    public static function getWidgets(): array
    {
        return [
            LearningAreaStats::class,
        ];
    }

    public static function getRelations(): array
    {
        return [
            // Contoh kalau nanti ada relasi Program:
            // RelationManagers\ProgramsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListLearningAreas::route('/'),
            'create' => Pages\CreateLearningArea::route('/create'),
            'view'   => Pages\ViewLearningArea::route('/{record}'),
            'edit'   => Pages\EditLearningArea::route('/{record}/edit'),
        ];
    }
}
