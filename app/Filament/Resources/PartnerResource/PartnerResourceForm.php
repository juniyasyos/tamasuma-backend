<?php

namespace App\Filament\Resources\PartnerResource;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\{Section, Grid, TextInput, Textarea, FileUpload, Toggle};
use Illuminate\Support\Str;

class PartnerResourceForm
{
    public static function make(Form $form): Form
    {
        return $form->schema([
            Section::make('Informasi Mitra')
                ->description('Isi data mitra. Slug akan dibuat otomatis dari nama.')
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('name')
                            ->label('Nama Mitra')
                            ->required()
                            ->maxLength(120)
                            ->live(debounce: 400)
                            ->afterStateUpdated(function (?string $state, Forms\Set $set, Forms\Get $get) {
                                // Auto-slug jika belum diganti manual
                                if (!filled($get('slug')) || $get('slug') === Str::slug((string) $get('name'))) {
                                    $set('slug', Str::slug((string) $state));
                                }
                            })
                            ->helperText('Gunakan nama singkat & jelas.'),

                        TextInput::make('slug')
                            ->label('Slug')
                            ->readOnly()
                            ->unique(ignoreRecord: true)
                            ->maxLength(150)
                            ->helperText('Terbentuk otomatis dari nama.'),
                    ]),

                    Textarea::make('description')
                        ->label('Deskripsi')
                        ->rows(4)
                        ->maxLength(1000)
                        ->placeholder('Deskripsi singkat tentang mitra ini.'),

                    Grid::make(2)->schema([
                        TextInput::make('website_url')
                            ->label('Website')
                            ->url()
                            ->suffixIcon('heroicon-o-arrow-top-right-on-square')
                            ->helperText('Opsional. Gunakan URL lengkap, misalnya https://example.com'),

                        Toggle::make('is_visible')
                            ->label('Tampilkan di publik')
                            ->default(true)
                            ->helperText('Nonaktifkan untuk menyembunyikan mitra dari pengguna.'),
                    ]),

                    FileUpload::make('logo_path')
                        ->label('Logo')
                        ->image()
                        ->imageEditor() // crop/rotate sederhana
                        ->directory('partners')
                        ->disk('public')
                        ->imagePreviewHeight('120')
                        ->maxSize(1024) // KB
                        ->helperText('Format: JPG/PNG/WebP. Maks 1MB.'),
                ])
                ->columns(1),
        ]);
    }
}

