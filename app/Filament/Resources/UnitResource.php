<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UnitResource\Pages;
use App\Models\Unit;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\{Grid, Section, Select, TextInput, Textarea, Toggle};
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\{TextColumn, ToggleColumn, BadgeColumn, IconColumn};

class UnitResource extends Resource
{
    protected static ?string $model = Unit::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Content Management';
    protected static ?string $modelLabel = 'Unit';
    protected static ?string $pluralModelLabel = 'Units';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Informasi Unit')
                ->description('Masukkan informasi utama dari unit pembelajaran.')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            Select::make('program_id')
                                ->relationship('program', 'title')
                                ->required()
                                ->label('Program')
                                ->searchable()
                                ->placeholder('Pilih program'),

                            TextInput::make('title')
                                ->label('Judul Unit')
                                ->required()
                                ->placeholder('Contoh: Dasar-dasar HTML')
                                ->autofocus(),
                        ]),

                    TextInput::make('slug')
                        ->label('Slug (URL)')
                        ->placeholder('Otomatis dihasilkan dari judul')
                        ->helperText('Biarkan kosong untuk menghasilkan otomatis')
                        ->readOnly()
                        ->disabledOn('edit'),

                    Textarea::make('summary')
                        ->label('Ringkasan')
                        ->placeholder('Deskripsikan unit ini secara singkat...')
                        ->rows(3),

                    Grid::make(2)->schema([
                        TextInput::make('order')
                            ->label('Urutan Tampil')
                            ->numeric()
                            ->default(1)
                            ->helperText('Semakin kecil, semakin awal tampil di daftar'),

                        Toggle::make('is_visible')
                            ->label('Tampilkan di Halaman Pengguna')
                            ->default(true),
                    ]),
                ])->columns(1)->collapsible(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->sortable()
                    ->limit(30),

                TextColumn::make('program.title')
                    ->label('Program')
                    ->sortable()
                    ->badge()
                    ->color('info'),

                TextColumn::make('order')
                    ->label('Urutan')
                    ->sortable()
                    ->alignCenter(),

                IconColumn::make('is_visible')
                    ->label('Publish')
                    ->boolean()
                    ->trueIcon('heroicon-m-check-circle')
                    ->falseIcon('heroicon-m-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->tooltip('Lihat detail unit'),
                Tables\Actions\EditAction::make()->tooltip('Edit unit'),
                Tables\Actions\DeleteAction::make()->tooltip('Hapus unit'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->striped()
            ->emptyStateHeading('Belum ada unit')
            ->emptyStateDescription('Tambahkan unit baru untuk program pembelajaran Anda.')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUnits::route('/'),
            'create' => Pages\CreateUnit::route('/create'),
            'edit' => Pages\EditUnit::route('/{record}/edit'),
        ];
    }
}
