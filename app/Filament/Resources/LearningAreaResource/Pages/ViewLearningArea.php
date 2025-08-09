<?php

namespace App\Filament\Resources\LearningAreaResource\Pages;

use App\Filament\Resources\LearningAreaResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\{Section, Grid, TextEntry, IconEntry};
use Filament\Actions;

class ViewLearningArea extends ViewRecord
{
    protected static string $resource = LearningAreaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()->label('Edit'),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Section::make('Ringkasan Bidang')
                ->schema([
                    Grid::make([
                        'default' => 1,
                        'md' => 2,
                        'xl' => 3,
                    ])->schema([
                        TextEntry::make('name')->label('Nama')->weight('semibold')->size('lg'),
                        TextEntry::make('slug')->label('Slug')->copyable()->icon('heroicon-m-link'),
                        IconEntry::make('is_active')
                            ->label('Status')
                            ->boolean()
                            ->trueIcon('heroicon-m-check-circle')
                            ->falseIcon('heroicon-m-x-circle')
                            ->trueColor('success')
                            ->falseColor('gray'),

                        TextEntry::make('created_at')->label('Dibuat')->since()->icon('heroicon-m-calendar'),
                        TextEntry::make('updated_at')->label('Diubah')->since()->icon('heroicon-m-arrow-path'),

                        // KPI kecil (kalau ada relasi programs())
                        TextEntry::make('programs_count')
                            ->label('Jumlah Program')
                            ->getStateUsing(fn($record) => $record->programs()->count())
                            ->badge()
                            ->color('info'),
                        TextEntry::make('programs_published')
                            ->label('Program Publik')
                            ->getStateUsing(fn($record) => $record->programs()->where('is_published', true)->count())
                            ->badge()
                            ->color('success'),
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
