<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProgramResource\Pages;
use App\Models\Program;
use DutchCodingCompany\FilamentSocialite\View\Components\Buttons;
use Filament\Forms;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class ProgramResource extends Resource
{
    protected static ?string $model = Program::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Content Management';
    protected static ?string $navigationLabel = 'Program Pembelajaran';
    protected static ?string $pluralModelLabel = 'Program';
    protected static ?string $modelLabel = 'Program Pembelajaran';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Tabs::make('Program Tabs')
                ->columnSpanFull()
                ->tabs([

                    Tab::make('Sumber Program')
                        ->schema([
                            Section::make('Asal Program')
                                ->description('Tentukan apakah program ini berasal dari internal atau eksternal.')
                                ->schema([
                                    ToggleButtons::make('source')
                                        ->label('Sumber Program')
                                        ->options([
                                            'internal' => 'Internal',
                                            'external' => 'Eksternal',
                                        ])
                                        ->colors([
                                            'internal' => 'gray',
                                            'external' => 'info',
                                        ])
                                        ->icons([
                                            'internal' => 'heroicon-o-building-library',
                                            'external' => 'heroicon-o-globe-alt',
                                        ])
                                        ->inline()
                                        ->default('internal')
                                        ->required(),

                                    TextInput::make('platform')
                                        ->label('Platform')
                                        ->placeholder('Contoh: Coursera, Dicoding, Udemy')
                                        ->visible(fn($get) => $get('source') === 'external'),

                                    TextInput::make('external_url')
                                        ->label('Link Program')
                                        ->placeholder('https://')
                                        ->url()
                                        ->suffixIcon('heroicon-o-arrow-top-right-on-square')
                                        ->visible(fn($get) => $get('source') === 'external'),

                                    ToggleButtons::make('is_certified')
                                        ->label('Sertifikat')
                                        ->options([
                                            true => 'Ada Sertifikat',
                                            false => 'Tanpa Sertifikat',
                                        ])
                                        ->icons([
                                            true => 'heroicon-o-check-badge',
                                            false => 'heroicon-o-x-mark',
                                        ])
                                        ->colors([
                                            true => 'success',
                                            false => 'gray',
                                        ])
                                        ->inline()
                                        ->default(false),
                                ]),
                        ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('learningArea.name')
                    ->label('Bidang')
                    ->sortable()
                    ->badge()
                    ->color('gray'),

                TextColumn::make('level')
                    ->label('Tingkat')
                    ->badge()
                    ->color(fn(string $state) => match ($state) {
                        'pemula' => 'success',
                        'menengah' => 'warning',
                        'lanjutan' => 'danger',
                        default => 'gray',
                    }),

                TextColumn::make('source')
                    ->label('Sumber')
                    ->badge()
                    ->color(fn(string $state) => match ($state) {
                        'internal' => 'gray',
                        'external' => 'info',
                        default => 'gray',
                    }),

                TextColumn::make('platform')
                    ->label('Platform'),

                ToggleColumn::make('is_certified')
                    ->label('Sertifikat'),

                ToggleColumn::make('is_published')
                    ->label('Terbitkan'),
            ])
            ->defaultSort('title')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->label('Lihat'),
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
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPrograms::route('/'),
            'create' => Pages\CreateProgram::route('/create'),
            'edit' => Pages\EditProgram::route('/{record}/edit'),
        ];
    }
}
