<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PartnerResource\Pages;
use App\Filament\Resources\PartnerResource\PartnerResourceForm;
use App\Filament\Resources\PartnerResource\PartnerResourceTable;
use App\Models\Partner;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PartnerResource extends Resource
{
    protected static ?string $model = Partner::class;

    protected static ?string $navigationIcon  = 'heroicon-c-user-group';
    protected static ?string $navigationGroup = 'Content Management';
    protected static ?string $navigationLabel = 'Mitra';
    protected static ?string $modelLabel      = 'Mitra';
    protected static ?string $pluralModelLabel = 'Mitra';

    /** Urutan navigasi: posisi ke-3 */
    protected static ?int $navigationSort = 3;

    /** Badge di menu: jumlah mitra yang disembunyikan */
    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::query()->where('is_visible', false)->count();
        return $count ? (string) $count : null;
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Jumlah mitra yang disembunyikan';
    }

    /** Global search */
    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'slug', 'website_url'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Slug'    => $record->slug,
            'Website' => $record->website_url ?: '-',
            'Status'  => $record->is_visible ? 'Tampil' : 'Disembunyikan',
        ];
    }

    public static function form(Form $form): Form
    {
        return PartnerResourceForm::make($form);
    }

    public static function table(Table $table): Table
    {
        return PartnerResourceTable::make($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPartners::route('/'),
            'create' => Pages\CreatePartner::route('/create'),
            'view'   => Pages\ViewPartner::route('/{record}'),
            'edit'   => Pages\EditPartner::route('/{record}/edit'),
        ];
    }
}
