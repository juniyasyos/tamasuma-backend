<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MaterialResource\Pages;
use App\Filament\Resources\MaterialResource\RelationManagers;
use App\Models\Material;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MaterialResource extends Resource
{
    protected static ?string $model = Material::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Content Management';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Informasi Materi')
                ->description('Lengkapi detail materi yang akan ditampilkan kepada peserta.')
                ->schema([
                    Grid::make(2)->schema([
                        Select::make('unit_id')
                            ->label('Unit')
                            ->relationship('unit', 'title')
                            ->required()
                            ->searchable()
                            ->placeholder('Pilih unit'),

                        Select::make('type')
                            ->label('Jenis Materi')
                            ->options([
                                'text' => 'Teks',
                                'video' => 'Video',
                                'file' => 'File',
                                'quiz' => 'Quiz',
                            ])
                            ->required()
                            ->default('text')
                            ->native(false)
                            ->helperText('Pilih tipe konten utama materi ini.'),
                    ]),

                    TextInput::make('title')
                        ->label('Judul Materi')
                        ->required()
                        ->placeholder('Contoh: Pengantar Laravel'),

                    RichEditor::make('content')
                        ->label('Konten Materi')
                        ->toolbarButtons(['bold', 'italic', 'link', 'bulletList', 'orderedList'])
                        ->helperText('Kosongkan jika hanya menggunakan lampiran file.')
                        ->columnSpan('full'),

                    SpatieMediaLibraryFileUpload::make('attachments')
                        ->label('Lampiran')
                        ->collection('attachments')
                        ->multiple()
                        ->disk('public')
                        ->directory('materials')
                        ->preserveFilenames()
                        ->helperText('Tambahkan file pendukung seperti PDF atau dokumen lainnya.'),

                    Grid::make(3)->schema([
                        TextInput::make('duration_minutes')
                            ->label('Durasi (menit)')
                            ->numeric()
                            ->minValue(1)
                            ->placeholder('10')
                            ->helperText('Durasi estimasi penyelesaian materi ini.'),

                        TextInput::make('order')
                            ->label('Urutan')
                            ->numeric()
                            ->default(1)
                            ->helperText('Menentukan urutan tampil di daftar materi.'),

                        Toggle::make('is_mandatory')
                            ->label('Wajib Diselesaikan')
                            ->default(true),
                    ]),

                    Toggle::make('is_visible')
                        ->label('Tampilkan ke Peserta')
                        ->default(true),
                ])
                ->columns(1),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->searchable(),
                TextColumn::make('unit.title')->label('Unit'),
                TextColumn::make('type')
                    ->colors([
                        'text' => 'info',
                        'video' => 'success',
                        'file' => 'warning',
                        'quiz' => 'danger',
                    ])
                    ->badge()
                    ->label('Tipe'),

                ToggleColumn::make('is_visible')->label('Tampil'),
                ToggleColumn::make('is_mandatory')->label('Wajib'),
                TextColumn::make('duration_minutes')->label('Durasi'),
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
            'index' => Pages\ListMaterials::route('/'),
            'create' => Pages\CreateMaterial::route('/create'),
            'edit' => Pages\EditMaterial::route('/{record}/edit'),
        ];
    }
}
