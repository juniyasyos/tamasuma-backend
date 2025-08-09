<?php

namespace App\Filament\Resources\LearningAreaResource;

use Filament\Forms\Components\Section;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Illuminate\Support\Str;

class LearningAreaResourceForm
{
    public static function make(Form $form): Form
    {
        return $form->schema([
            Section::make('Informasi Bidang')
                ->description('Kelola nama, slug, dan deskripsi bidang.')
                ->schema([
                    TextInput::make('name')
                        ->label('Nama')
                        ->required()
                        ->maxLength(100)
                        ->live(onBlur: true)
                        ->afterStateUpdated(fn (Set $set, ?string $state) => $set('slug', Str::slug($state))),

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
}
