<?php

namespace App\Filament\Resources\LearningAreaResource;

use App\Filament\Resources\LearningAreaResource;
use Filament\Forms\Components\{Section, TextInput, Textarea, Toggle};

class Schema extends LearningAreaResource
{
    public static function make(): array
    {
        return [
            Section::make('Informasi Bidang')
                ->description('Kelola nama, slug, dan deskripsi bidang.')
                ->schema([
                    TextInput::make('name')
                        ->label('Nama')
                        ->required()
                        ->maxLength(100),

                    TextInput::make('slug')
                        ->helperText('URL unik, contoh: pemrograman-web')
                        ->readOnly()
                        ->unique(ignoreRecord: true)
                        ->maxLength(100),

                    Textarea::make('description')
                        ->label('Deskripsi')
                        ->columnSpanFull()
                        ->rows(3)
                        ->maxLength(500)
                        ->placeholder('Deskripsi singkat bidang ini'),
                ])
                ->columns(2),

            Section::make('Pengaturan')
                ->collapsible()
                ->schema([
                    Toggle::make('is_active')
                        ->label('Aktifkan bidang ini')
                        ->helperText('Jika tidak aktif, bidang tidak tampil ke pengguna.')
                        ->default(true),
                ]),
        ];
    }
}

