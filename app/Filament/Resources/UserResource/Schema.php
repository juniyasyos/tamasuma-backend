<?php

namespace App\Filament\Resources\UserResource;

use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class Schema
{
    /**
     * Get the form schema for the User resource.
     */
    public static function make(): array
    {
        return [
            Section::make('User Information')
                ->description('Lengkapi data pengguna. Password opsional saat edit.')
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('name')
                            ->label('Nama')
                            ->required()
                            ->maxLength(100),

                        TextInput::make('email')
                            ->email()
                            ->required()
                            ->unique(modifyRuleUsing: fn (Rule $rule, ?User $record) => $rule->ignore($record))
                            ->helperText('Gunakan email aktif.'),
                    ]),

                    Grid::make(2)->schema([
                        TextInput::make('password')
                            ->label('Password')
                            ->password()
                            ->revealable()
                            ->confirmed()
                            ->dehydrateStateUsing(fn ($state) => filled($state) ? Hash::make($state) : null)
                            ->dehydrated(fn ($state) => filled($state))
                            ->required(fn (?User $record) => $record === null),

                        TextInput::make('password_confirmation')
                            ->label('Konfirmasi Password')
                            ->password()
                            ->revealable()
                            ->dehydrated(false)
                            ->required(fn (?User $record, Forms\Get $get) => $record === null || filled($get('password'))),
                    ]),

                    Grid::make(2)->schema([
                        // Kalau kamu punya kolom avatar_url di users, field ini aktif:
                        TextInput::make('avatar_url')
                            ->label('Avatar URL')
                            ->url()
                            ->suffixAction(
                                Forms\Components\Actions\Action::make('clear')->icon('heroicon-m-x-mark')->action(fn ($set) => $set('avatar_url', null))
                            )
                            ->helperText('Boleh kosong. Jika kosong, avatar akan memakai inisial nama.'),

                        Select::make('roles')
                            ->label('Peran (Roles)')
                            ->relationship('roles', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->helperText('Pilih satu atau lebih peran.'),
                    ]),
                ])->columns(1),

            Section::make('Keamanan')
                ->collapsible()
                ->schema([
                    Toggle::make('two_factor_enabled')
                        ->label('Aktifkan 2FA')
                        ->helperText('Centang jika ingin memaksa 2FA (opsional, sesuaikan dengan model Anda).')
                        ->dehydrated(fn () => false), // contoh placeholder jika belum ada kolomnya
                ]),
        ];
    }
}
