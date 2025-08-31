<?php

namespace App\Filament\Resources\RoleResource;

use App\Filament\Resources\RoleResource;
use BezhanSalleh\FilamentShield\Forms\ShieldSelectAllToggle;
use BezhanSalleh\FilamentShield\Support\Utils;
use BezhanSalleh\FilamentShield\Traits\HasShieldFormComponents;
use Filament\Facades\Filament;
use Filament\Forms;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\HtmlString;

class Schema extends RoleResource
{
    use HasShieldFormComponents;

    public static function make(): array
    {
        return [
            Forms\Components\Section::make(__('filament-shield::filament-shield.resource.label.role'))
                ->description('Nama peran dan pengaturan dasarnya.')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label(__('filament-shield::filament-shield.field.name'))
                        ->prefixIcon('heroicon-o-identification')
                        ->placeholder('Contoh: Admin, Pengajar, Pelajar')
                        ->helperText('Gunakan nama yang mudah dimengerti pengguna.')
                        ->unique(ignoreRecord: true)
                        ->required()
                        ->maxLength(255),

                    Forms\Components\TextInput::make('guard_name')
                        ->label(__('filament-shield::filament-shield.field.guard_name'))
                        ->prefixIcon('heroicon-o-shield-check')
                        ->default(Utils::getFilamentAuthGuard())
                        ->helperText('Biarkan default kecuali Anda paham multi-guard.')
                        ->nullable()
                        ->maxLength(255),

                    Forms\Components\Select::make(config('permission.column_names.team_foreign_key'))
                        ->label(__('filament-shield::filament-shield.field.team'))
                        ->placeholder(__('filament-shield::filament-shield.field.team.placeholder'))
                        /** @phpstan-ignore-next-line */
                        ->default([Filament::getTenant()?->id])
                        ->options(fn (): Arrayable => Utils::getTenantModel() ? Utils::getTenantModel()::pluck('name', 'id') : collect())
                        ->hidden(fn (): bool => ! (static::shield()->isCentralApp() && Utils::isTenancyEnabled()))
                        ->dehydrated(fn (): bool => ! (static::shield()->isCentralApp() && Utils::isTenancyEnabled())),

                    ShieldSelectAllToggle::make('select_all')
                        ->onIcon('heroicon-s-shield-check')
                        ->offIcon('heroicon-s-shield-exclamation')
                        ->label(__('filament-shield::filament-shield.field.select_all.name'))
                        ->helperText(fn (): HtmlString => new HtmlString(__('filament-shield::filament-shield.field.select_all.message')))
                        ->dehydrated(fn (bool $state): bool => $state),
                ])
                ->columns(['sm' => 2, 'lg' => 3])
                ->collapsible(),

            Forms\Components\Section::make('Permissions')
                ->description('Centang izin yang diperlukan. Gunakan tombol Select All untuk mempercepat.')
                ->schema([
                    static::getShieldFormComponents(),
                ])
                ->collapsible()
                ->collapsed(),
        ];
    }
}
