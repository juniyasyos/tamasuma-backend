<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PartnerResource\Pages;
use App\Models\Partner;
use Filament\Forms;
use Filament\Forms\Components\{Section, Grid, TextInput, Textarea, FileUpload, Toggle};
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\{TextColumn, IconColumn, ImageColumn};
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PartnerResource extends Resource
{
    protected static ?string $model = Partner::class;

    protected static ?string $navigationIcon = 'heroicon-o-handshake';
    protected static ?string $navigationGroup = 'Content Management';
    protected static ?string $navigationLabel = 'Mitra';
    protected static ?string $modelLabel = 'Mitra';
    protected static ?string $pluralModelLabel = 'Mitra';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Informasi Mitra')
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('name')
                            ->label('Nama Mitra')
                            ->required()
                            ->autofocus(),

                        TextInput::make('slug')
                            ->label('Slug')
                            ->disabledOn('edit')
                            ->readOnly(),
                    ]),

                    Textarea::make('description')
                        ->label('Deskripsi')
                        ->rows(3),

                    TextInput::make('website_url')
                        ->label('Website')
                        ->url()
                        ->suffixIcon('heroicon-o-arrow-top-right-on-square'),

                    FileUpload::make('logo_path')
                        ->label('Logo')
                        ->image()
                        ->directory('partners')
                        ->disk('public'),

                    Toggle::make('is_visible')
                        ->label('Tampilkan')
                        ->default(true),
                ])->columns(1),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('logo_path')
                    ->label('Logo')
                    ->disk('public')
                    ->square(),

                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('website_url')
                    ->label('Website')
                    ->url()
                    ->limit(30),

                IconColumn::make('is_visible')
                    ->label('Tampil')
                    ->boolean(),
            ])
            ->filters([
                //
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPartners::route('/'),
            'create' => Pages\CreatePartner::route('/create'),
            'edit' => Pages\EditPartner::route('/{record}/edit'),
        ];
    }
}
