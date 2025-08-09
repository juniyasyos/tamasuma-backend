<?php

namespace App\Filament\Resources\LearningAreaResource;

use App\Models\LearningArea;
use Filament\Tables;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class LearningAreaResourceTable
{
    public static function make(Table $table): Table
    {
        return $table
            ->defaultPaginationPageOption(10)
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable()
                    ->description(fn($record) => $record->slug)
                    ->weight('medium'),

                TextColumn::make('programs_count')
                    ->label('Program')
                    ->counts('programs')
                    ->badge()
                    ->sortable(),

                ToggleColumn::make('is_active')
                    ->label('Aktif')
                    ->onColor('success')
                    ->offColor('gray'),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Aktif'),
            ])
            ->headerActions([
                // ExportAction::make(),
            ])
            ->actions([
                // Quick action: Detail (link ke page, bukan modal)
                ViewAction::make('detail')
                    ->label('Detail')
                    ->icon('heroicon-o-eye')
                    ->url(fn(LearningArea $record) => \App\Filament\Resources\ProgramResource::getUrl('view', ['record' => $record]))
                    ->openUrlInNewTab(false)
                    ->iconButton()
                    ->tooltip('Lihat detail'),

                // Quick action: Edit (pakai slide-over biar tetap terasa ringan)
                EditAction::make('edit')
                    ->label('Edit')
                    ->icon('heroicon-o-pencil-square')
                    ->slideOver()
                    ->iconButton()
                    ->tooltip('Edit'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
