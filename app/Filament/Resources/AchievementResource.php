<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AchievementResource\Pages;
use App\Models\Achievement;
use App\Filament\Resources\AchievementResource\Schema as AchievementSchema;
use App\Filament\Resources\AchievementResource\Table as AchievementTable;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class AchievementResource extends Resource
{
    protected static ?string $model = Achievement::class;

    protected static ?string $navigationIcon = 'heroicon-o-trophy';
    protected static ?string $navigationGroup = 'Content Management';
    protected static ?string $navigationLabel = 'Pencapaian';
    protected static ?int $navigationSort = 10;

    public static function shouldRegisterNavigation(): bool
    {
        $u = Auth::user();
        if (! $u) return false;
        // Only Admin & Super Admin (or explicit permission) see this in navigation
        $isAdmin = method_exists($u, 'hasRole') && ($u->hasRole('super_admin') || $u->hasRole('Admin'));
        return $isAdmin || $u->can('view_any_achievement');
    }

    public static function form(Form $form): Form
    {
        return $form->schema(AchievementSchema::make());
    }

    public static function table(Table $table): Table
    {
        return AchievementTable::make($table);
    }

    public static function getRelations(): array { return []; }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAchievements::route('/'),
            'create' => Pages\CreateAchievement::route('/create'),
            'edit' => Pages\EditAchievement::route('/{record}/edit'),
        ];
    }
}
