<?php

namespace App\Filament\Resources;

use App\Models\User;
use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\UserResourceForm;
use App\Filament\Resources\UserResource\UserResourceTable;
use Filament\Forms\Form;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\{Section as InfolistSection, Grid as InfoGrid, TextEntry, IconEntry, ImageEntry, RepeatableEntry, BadgeEntry};
use Filament\Resources\Resource;
use Filament\Tables\Table;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon  = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'User Management';
    protected static ?string $navigationLabel = 'Users';
    protected static ?string $modelLabel      = 'User';
    protected static ?string $pluralModelLabel = 'Users';

    public static function form(Form $form): Form
    {
        return UserResourceForm::make($form);
    }

    public static function table(Table $table): Table
    {
        return UserResourceTable::make($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view'   => Pages\ViewUser::route('/{record}'),
            'edit'   => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            InfolistSection::make('Profil')
                ->schema([
                    InfoGrid::make(['default' => 1, 'md' => 2, 'xl' => 3])->schema([
                        ImageEntry::make('avatar_url')
                            ->label('Avatar')
                            ->circular()
                            ->getStateUsing(fn(User $r) => $r->avatar_url ?: "https://ui-avatars.com/api/?name=" . urlencode($r->name))
                            ->columnSpan(1),

                        TextEntry::make('name')->label('Nama')->weight('semibold')->size('lg'),
                        TextEntry::make('email')->icon('heroicon-m-envelope')->copyable(),
                        IconEntry::make('email_verified_at')
                            ->label('Verifikasi Email')
                            ->boolean()
                            ->trueIcon('heroicon-m-check-circle')
                            ->falseIcon('heroicon-m-x-circle')
                            ->trueColor('success')
                            ->falseColor('gray'),
                        TextEntry::make('created_at')->label('Dibuat')->since()->icon('heroicon-m-calendar'),
                        TextEntry::make('updated_at')->label('Diubah')->since()->icon('heroicon-m-arrow-path'),
                    ]),

                    TextEntry::make('roles.name')
                        ->label('Roles')
                        ->badge()
                        ->color('info')
                        ->separator(' '),
                ])
                ->columns(1)
                ->collapsed(false),
        ]);
    }
}
