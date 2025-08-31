<?php

namespace App\Filament\Resources\ProgramResource;

use App\Filament\Resources\ProgramResource;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Components\DatePicker;

class Schema extends ProgramResource
{
    public static function make(): array
    {
        return [
            Tabs::make('Program Tabs')
                ->columnSpanFull()
                ->tabs([
                    Tab::make('Informasi Dasar')
                        ->icon('heroicon-o-information-circle')
                        ->schema([
                            Section::make('Informasi Program')
                                ->description('Masukkan detail program pelatihan secara lengkap.')
                                ->collapsible()
                                ->schema([
                                    Grid::make(2)->schema([
                                        Select::make('learning_area_id')
                                            ->label('Bidang Pembelajaran')
                                            ->relationship('learningArea', 'name')
                                            ->searchable()
                                            ->preload()
                                            ->native(false)
                                            ->required()
                                            ->helperText('Pilih bidang/topik utama program.'),

                                        ToggleButtons::make('level')
                                            ->label('Tingkat')
                                            ->options([
                                                'pemula'   => 'Pemula',
                                                'menengah' => 'Menengah',
                                                'lanjutan' => 'Lanjutan',
                                            ])
                                            ->colors([
                                                'pemula'   => 'success',
                                                'menengah' => 'warning',
                                                'lanjutan' => 'primary',
                                            ])
                                            ->icons([
                                                'pemula'   => 'heroicon-o-sparkles',
                                                'menengah' => 'heroicon-o-adjustments-horizontal',
                                                'lanjutan' => 'heroicon-o-fire',
                                            ])
                                            ->inline()
                                            ->required()
                                            ->live(),
                                    ]),

                                    Grid::make(2)->schema([
                                        TextInput::make('title')
                                            ->label('Judul Program')
                                            ->placeholder('Contoh: Dasar-Dasar Data Science')
                                            ->required()
                                            ->maxLength(100)
                                            ->rule('string')
                                            ->reactive()
                                            ->debounce(500)
                                            ->afterStateUpdated(function (?string $state, callable $set, callable $get) {
                                                if (!filled($get('slug')) || $get('slug') === \Illuminate\Support\Str::slug((string) $get('title', ''))) {
                                                    $set('slug', \Illuminate\Support\Str::slug((string) $state));
                                                }
                                            })
                                            ->helperText('Gunakan judul singkat & jelas (maks. 100 karakter).'),

                                        TextInput::make('slug')
                                            ->label('Slug URL')
                                            ->readOnly()
                                            ->unique(ignoreRecord: true),

                                        DatePicker::make('starts_at')
                                            ->label('Mulai')
                                            ->native(false)
                                            ->closeOnDateSelection()
                                            ->suffixIcon('heroicon-o-calendar')
                                            ->helperText('Tanggal mulai program (opsional).'),

                                        DatePicker::make('ends_at')
                                            ->label('Selesai')
                                            ->native(false)
                                            ->closeOnDateSelection()
                                            ->suffixIcon('heroicon-o-calendar')
                                            ->rule('after_or_equal:starts_at')
                                            ->helperText('Batas akhir pelaksanaan program (opsional).')
                                    ]),

                                    Textarea::make('description')
                                        ->label('Deskripsi')
                                        ->rows(5)
                                        ->minLength(20)
                                        ->maxLength(1000)
                                        ->placeholder('Deskripsikan secara ringkas dan jelas...')
                                        ->helperText('Idealnya 60–160 karakter untuk cuplikan, tapi boleh lebih detail.'),

                                    Toggle::make('is_published')
                                        ->label('Dipublikasikan?')
                                        ->inline(false)
                                        ->default(true)
                                        ->helperText('Nonaktifkan jika ingin menyimpan sebagai Draft.'),
                                ]),
                        ]),

                    Tab::make('Sumber Program')
                        ->icon('heroicon-o-globe-alt')
                        ->schema([
                            Section::make('Asal Program')
                                ->description('Tentukan apakah program ini berasal dari internal atau eksternal.')
                                ->collapsible()
                                ->schema([
                                    ToggleButtons::make('source')
                                        ->label('Sumber Program')
                                        ->options([
                                            'internal' => 'Internal',
                                            'external' => 'Eksternal',
                                        ])
                                        ->colors([
                                            'internal' => 'success',
                                            'external' => 'info',
                                        ])
                                        ->icons([
                                            'internal' => 'heroicon-o-building-library',
                                            'external' => 'heroicon-o-globe-alt',
                                        ])
                                        ->default('external')
                                        // ->disabled()
                                        ->inline()
                                        ->required(),
                                    // ->live(),

                                    TextInput::make('platform')
                                        ->label('Platform')
                                        ->placeholder('Contoh: Coursera, Dicoding, Udemy')
                                        ->visible(fn(Forms\Get $get) => $get('source') === 'external')
                                        ->maxLength(100)
                                        ->rule('nullable|string'),

                                    TextInput::make('external_url')
                                        ->label('Link Program')
                                        ->placeholder('https://contoh.com/kursus-keren')
                                        ->url()
                                        ->suffixIcon('heroicon-o-arrow-top-right-on-square')
                                        ->visible(fn(Forms\Get $get) => $get('source') === 'external')
                                        ->rule('nullable|url'),

                                    ToggleButtons::make('is_certified')
                                        ->label('Sertifikat')
                                        ->options([
                                            true  => 'Ada Sertifikat',
                                            false => 'Tanpa Sertifikat',
                                        ])
                                        ->icons([
                                            true  => 'heroicon-o-check-badge',
                                            false => 'heroicon-o-x-mark',
                                        ])
                                        ->colors([
                                            true  => 'success',
                                            false => 'gray',
                                        ])
                                        ->inline()
                                        ->default(false),
                                ]),
                        ]),

                ])
        ];
    }
}
