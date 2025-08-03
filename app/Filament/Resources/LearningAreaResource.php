<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LearningAreaResource\Pages;
use App\Models\LearningArea;
use Filament\Forms\Form;
use Filament\Forms\Components\{Section, TextInput, Textarea, Toggle};
use Filament\Tables\Table;
use Filament\Tables;
use Filament\Tables\Columns\{TextColumn, IconColumn, ToggleColumn};
use Filament\Resources\Resource;

class LearningAreaResource extends Resource
{
    protected static ?string $model = LearningArea::class;
    protected static ?string $navigationIcon = 'heroicon-o-book-open';
    protected static ?string $navigationLabel = 'Bidang Pembelajaran';
    protected static ?string $pluralModelLabel = 'Bidang Pembelajaran';
    protected static ?string $modelLabel = 'Bidang Pembelajaran';
    protected static ?string $navigationGroup = 'Content Management';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Informasi Bidang')
                ->description('Kelola nama, slug, dan deskripsi bidang.')
                ->schema([
                    TextInput::make('name')
                        ->label('Nama')
                        ->required()
                        ->maxLength(100),

                    TextInput::make('slug')
                        ->helperText('URL unik bidang, seperti "pemrograman-web"')
                        ->readOnly()
                        ->unique(ignoreRecord: true)
                        ->maxLength(100),

                    Textarea::make('description')
                        ->label('Deskripsi')
                        ->columnSpanFull()
                        ->rows(3)
                        ->maxLength(500)
                        ->placeholder('Deskripsi singkat bidang pembelajaran ini'),
                ])->columns(2),

            Section::make('Pengaturan')
                ->collapsible()
                ->schema([
                    Toggle::make('is_active')
                        ->label('Aktifkan bidang ini')
                        ->helperText('Jika tidak aktif, bidang tidak akan muncul ke pengguna.')
                        ->default(true),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable()
                    ->description(fn($record) => $record->slug)
                    ->weight('medium'),

                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->trueIcon('heroicon-m-check-circle')
                    ->falseIcon('heroicon-m-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray'),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Aktif'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // e.g. RelationManagers\ProgramsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLearningAreas::route('/'),
            'create' => Pages\CreateLearningArea::route('/create'),
            'edit' => Pages\EditLearningArea::route('/{record}/edit'),
        ];
    }
}
