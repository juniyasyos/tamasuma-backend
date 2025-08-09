<?php

namespace App\Filament\Resources\ProgramResource\Pages;

use App\Filament\Resources\ProgramResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\IconEntry;

class ViewProgram extends ViewRecord
{
    protected static string $resource = ProgramResource::class;

    protected function getHeaderActions(): array
    {
        // opsional: hanya tombol edit agar tetap clean
        return [
            \Filament\Actions\EditAction::make()->label('Edit'),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Ringkasan')
                    ->description('Detail singkat program pembelajaran.')
                    ->schema([
                        Grid::make()
                            ->schema([
                                TextEntry::make('title')
                                    ->label('Judul')
                                    ->weight('semibold')
                                    ->size('lg')
                                    ->columnSpanFull(),

                                TextEntry::make('learningArea.name')
                                    ->label('Bidang')
                                    ->badge()
                                    ->color('gray'),

                                TextEntry::make('level')
                                    ->label('Tingkat')
                                    ->badge()
                                    ->color(fn($state) => match ($state) {
                                        'pemula' => 'success',
                                        'menengah' => 'warning',
                                        'lanjutan' => 'danger',
                                        default => 'gray',
                                    })
                                    ->formatStateUsing(fn($state) => ucfirst($state ?? '-')),

                                TextEntry::make('slug')
                                    ->label('Slug')
                                    ->copyable()
                                    ->icon('heroicon-m-link'),

                                IconEntry::make('is_published')
                                    ->label('Status')
                                    ->boolean()
                                    ->trueIcon('heroicon-m-check-circle')
                                    ->falseIcon('heroicon-m-x-circle')
                                    ->trueColor('success')
                                    ->falseColor('gray'),

                                IconEntry::make('is_certified')
                                    ->label('Sertifikat')
                                    ->boolean()
                                    ->trueIcon('heroicon-m-check-badge')
                                    ->falseIcon('heroicon-m-x-mark')
                                    ->trueColor('success')
                                    ->falseColor('gray'),

                                TextEntry::make('created_at')
                                    ->label('Dibuat')
                                    ->since()
                                    ->icon('heroicon-m-calendar'),

                                TextEntry::make('updated_at')
                                    ->label('Diubah')
                                    ->since()
                                    ->icon('heroicon-m-arrow-path'),
                            ])
                            ->columns([
                                'default' => 1,
                                'md' => 2,
                                'xl' => 3,
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Deskripsi')
                    ->schema([
                        TextEntry::make('description')
                            ->placeholder('Belum ada deskripsi.')
                            ->prose() 
                            ->columnSpanFull(),
                    ])
                    ->collapsed(false),

                Section::make('Sumber Program')
                    ->schema([
                        TextEntry::make('source')
                            ->label('Sumber')
                            ->badge()
                            ->color(fn($state) => $state === 'external' ? 'info' : 'gray')
                            ->formatStateUsing(fn($state) => ucfirst($state ?? '-')),

                        TextEntry::make('platform')
                            ->label('Platform')
                            ->placeholder('-'),

                        TextEntry::make('external_url')
                            ->label('Link Program')
                            ->url(fn($state) => $state ?: null, shouldOpenInNewTab: true)
                            ->icon('heroicon-m-arrow-top-right-on-square')
                            ->placeholder('-')
                            ->visible(fn($record) => $record->source === 'external'),
                    ])
                    ->columns([
                        'default' => 1,
                        'md' => 2,
                    ])
                    ->collapsed(true),
            ])
            ->columns(1);
    }
}
