<?php

namespace App\Filament\Resources\LearningAreaResource;

use App\Filament\Resources\LearningAreaResource;
use Filament\Tables;
use Filament\Tables\Columns\{TextColumn, IconColumn};
use Filament\Tables\Columns\Layout\Panel;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\Layout\Grid as LayoutGrid;
use Filament\Tables\Table as FilamentTable;

class Table extends LearningAreaResource
{
    public static function make(FilamentTable $table): FilamentTable
    {
        return $table
            ->heading('Bidang')
            ->description('Kelola bidang belajar.')
            ->defaultSort('name')
            ->recordUrl(fn($record) => static::getUrl('edit', ['record' => $record]))
            ->searchPlaceholder('Cari nama/slug')
            ->persistSearchInSession()
            // Atur setiap record sebagai kartu grid yang responsif
            ->contentGrid([
                'default' => 1,
                'sm' => 1,
                'md' => 2,
                'lg' => 3,
                'xl' => 4,
            ])
            ->columns([
                Panel::make([
                    LayoutGrid::make(2)
                        ->schema([
                            Stack::make([
                                TextColumn::make('name')
                                    ->label('Nama')
                                    ->weight('medium')
                                    ->searchable()
                                    ->sortable()
                                    ->limit(40)
                                    ->description(fn($record) => $record->slug),
                            ])->columnSpan(2),

                            TextColumn::make('programs_count')
                                ->label('Program')
                                ->counts('programs')
                                ->badge()
                                ->sortable(),

                            IconColumn::make('is_active')
                                ->label('Aktif')
                                ->boolean()
                                ->trueIcon('heroicon-m-check-circle')
                                ->falseIcon('heroicon-m-x-circle')
                                ->trueColor('success')
                                ->falseColor('gray')
                                ->sortable(),

                            TextColumn::make('created_at')
                                ->label('Dibuat')
                                ->since()
                                ->icon('heroicon-m-clock')
                                ->sortable(),
                        ]),
                ])->collapsed(false),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Aktif'),
                Tables\Filters\TernaryFilter::make('has_programs')
                    ->label('Program')
                    ->queries(
                        true: fn($q) => $q->has('programs'),
                        false: fn($q) => $q->doesntHave('programs'),
                        blank: fn($q) => $q,
                    ),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->color('warning')->iconButton(),
                Tables\Actions\DeleteAction::make()->color('danger')->iconButton(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Belum ada')
            ->emptyStateDescription('Buat bidang baru untuk mulai.')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()->label('Buat'),
            ]);
    }
}
