<?php

namespace App\Filament\Resources\ProgramResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\AttachAction;
use Filament\Tables\Actions\DetachAction;
use Filament\Tables\Actions\DetachBulkAction;
use Filament\Tables\Actions\EditAction;

class ParticipantsRelationManager extends RelationManager
{
    protected static string $relationship = 'participants';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Select::make('status')
                ->label('Status')
                ->required(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Nama')->searchable(),
                TextColumn::make('pivot.status')->label('Status')->badge(),
                TextColumn::make('pivot.created_at')
                    ->label('Gabung')
                    ->dateTime()->since(),
            ])
            ->headerActions([
                AttachAction::make()->preloadRecordSelect()->form([
                    Forms\Components\Select::make('status')
                        ->label('Status')
                        ->required(),
                ]),
            ])
            ->actions([
                EditAction::make()->form([
                    Forms\Components\Select::make('status')
                        ->label('Status')
                        ->required(),
                ]),
                DetachAction::make(),
            ])
            ->bulkActions([
                DetachBulkAction::make(),
            ]);
    }
}
