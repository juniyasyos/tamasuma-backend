<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProgramResource\Pages;
use App\Models\Program;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Illuminate\Database\Eloquent\Builder;

class ProgramResource extends Resource
{
    protected static ?string $model = Program::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Content Management';
    protected static ?string $navigationLabel = 'Program Pembelajaran';    // Label di sidebar
    protected static ?string $pluralModelLabel = 'Program';                // Untuk judul halaman list
    protected static ?string $modelLabel = 'Program';                      // Untuk halaman detail/singular
    
    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('Informasi Program')
                ->description('Masukkan detail program pelatihan secara lengkap.')
                ->schema([
                    Grid::make(2)->schema([
                        Select::make('learning_area_id')
                            ->label('Bidang Pembelajaran')
                            ->relationship('learningArea', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Select::make('level')
                            ->label('Tingkat')
                            ->options([
                                'pemula' => 'Pemula',
                                'menengah' => 'Menengah',
                                'lanjutan' => 'Lanjutan',
                            ])
                            ->default('pemula')
                            ->required(),
                    ]),

                    Grid::make(2)->schema([
                        TextInput::make('title')
                            ->label('Judul Program')
                            ->required()
                            ->maxLength(100),

                        TextInput::make('slug')
                            ->label('Slug URL')
                            ->readOnly()
                            ->helperText('Otomatis dari judul jika dikosongkan.')
                            ->unique(ignoreRecord: true)
                            ->maxLength(100),
                    ]),

                    Textarea::make('description')
                        ->label('Deskripsi')
                        ->rows(4)
                        ->placeholder('Deskripsikan secara ringkas dan jelas...'),

                    Grid::make(2)->schema([
                        TextInput::make('estimated_minutes')
                            ->label('Durasi (menit)')
                            ->numeric()
                            ->suffix('menit')
                            ->helperText('Estimasi waktu penyelesaian program'),

                        Toggle::make('is_published')
                            ->label('Publikasikan Program')
                            ->inline(false)
                            ->default(true),
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

                TextColumn::make('estimated_minutes')
                    ->label('Durasi')
                    ->suffix(' menit')
                    ->alignEnd()
                    ->sortable(),

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
