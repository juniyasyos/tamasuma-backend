<?php

namespace App\Filament\Resources\LearningAreaResource;

use App\Filament\Resources\LearningAreaResource;
use Filament\Tables;
use Filament\Tables\Columns\{TextColumn, IconColumn};
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
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable()
                    ->weight('medium')
                    ->limit(40)
                    ->description(fn($record) => $record->slug),

                TextColumn::make('slug')
                    ->label('Slug')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(isIndividual: true),

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
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                Tables\Actions\EditAction::make()->iconButton(),
                Tables\Actions\DeleteAction::make()->iconButton(),
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

