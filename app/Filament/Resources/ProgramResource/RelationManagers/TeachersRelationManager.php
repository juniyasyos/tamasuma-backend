<?php

namespace App\Filament\Resources\ProgramResource\RelationManagers;

use App\Models\User;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class TeachersRelationManager extends RelationManager
{
    protected static string $relationship = 'teachers';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->label('Tambah Pengajar')
                    ->preloadRecordSelect()
                    ->recordSelectOptions(fn () => User::role('Pengajar')
                        ->orderBy('name')
                        ->pluck('name', 'id')),
            ])
            ->actions([
                Tables\Actions\DetachAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DetachBulkAction::make(),
            ]);
    }
}
