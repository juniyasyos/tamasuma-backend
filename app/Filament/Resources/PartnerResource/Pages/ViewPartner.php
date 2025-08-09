<?php

namespace App\Filament\Resources\PartnerResource\Pages;

use App\Filament\Resources\PartnerResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\{Section, Grid, TextEntry, IconEntry, ImageEntry};

class ViewPartner extends ViewRecord
{
    protected static string $resource = PartnerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\EditAction::make()->label('Edit'),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Section::make('Profil Mitra')
                ->schema([
                    Grid::make([
                        'default' => 1,
                        'md' => 2,
                        'xl' => 3,
                    ])->schema([
                        ImageEntry::make('logo_path')
                            ->label('Logo')
                            ->disk('public')
                            ->circular()
                            ->columnSpan(1),

                        TextEntry::make('name')
                            ->label('Nama')
                            ->weight('semibold')
                            ->size('lg'),

                        TextEntry::make('slug')
                            ->label('Slug')
                            ->copyable()
                            ->icon('heroicon-m-link'),

                        IconEntry::make('is_visible')
                            ->label('Status')
                            ->boolean()
                            ->trueIcon('heroicon-m-check-circle')
                            ->falseIcon('heroicon-m-eye-slash')
                            ->trueColor('success')
                            ->falseColor('gray'),

                        TextEntry::make('website_url')
                            ->label('Website')
                            ->url(fn($state) => $state ?: null, shouldOpenInNewTab: true)
                            ->icon('heroicon-m-arrow-top-right-on-square')
                            ->placeholder('-'),

                        TextEntry::make('created_at')->label('Dibuat')->since()->icon('heroicon-m-calendar'),
                        TextEntry::make('updated_at')->label('Diubah')->since()->icon('heroicon-m-arrow-path'),
                    ]),
                ])
                ->collapsed(false),

            Section::make('Deskripsi')
                ->schema([
                    TextEntry::make('description')
                        ->placeholder('Belum ada deskripsi.')
                        ->prose()
                        ->columnSpanFull(),
                ])
                ->collapsed(false),
        ]);
    }
}
