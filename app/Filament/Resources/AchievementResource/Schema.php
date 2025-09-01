<?php

namespace App\Filament\Resources\AchievementResource;

use Filament\Forms;
use Illuminate\Support\Facades\Auth;

class Schema
{
    /**
     * Get the form schema array for AchievementResource.
     */
    public static function make(): array
    {
        $isAdmin = function () {
            $u = Auth::user();
            return $u && method_exists($u, 'hasRole') && ($u->hasRole('super_admin') || $u->hasRole('Admin'));
        };

        return [
            Forms\Components\Tabs::make('AchievementTabs')
                ->columnSpanFull()
                ->tabs([
                    Forms\Components\Tabs\Tab::make('Detail')
                        ->icon('heroicon-o-document-text')
                        ->schema([
                            Forms\Components\Grid::make(2)->schema([
                                Forms\Components\Select::make('user_id')
                                    ->label('Pengguna')
                                    ->relationship('user', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->native(false)
                                    ->visible($isAdmin),

                                Forms\Components\TextInput::make('title')
                                    ->label('Judul')
                                    ->required()
                                    ->maxLength(150),

                                Forms\Components\Select::make('category')
                                    ->label('Kategori')
                                    ->options([
                                        'certificate' => 'Sertifikat',
                                        'award' => 'Penghargaan',
                                        'competition' => 'Kompetisi',
                                        'training' => 'Pelatihan',
                                        'other' => 'Lainnya',
                                    ])
                                    ->default('certificate')
                                    ->native(false),

                                Forms\Components\TextInput::make('issuer')
                                    ->label('Penyelenggara')
                                    ->maxLength(150),

                                Forms\Components\DatePicker::make('achieved_at')
                                    ->label('Tanggal')
                                    ->native(false),

                                Forms\Components\TextInput::make('url')
                                    ->label('URL Terkait')
                                    ->url()
                                    ->columnSpanFull(),
                            ]),
                        ]),

                    Forms\Components\Tabs\Tab::make('Media')
                        ->icon('heroicon-o-photo')
                        ->schema([
                            Forms\Components\FileUpload::make('proof_image')
                                ->label('Bukti (Gambar/PDF)')
                                ->imageEditor()
                                ->downloadable()
                                ->openable()
                                ->imagePreviewHeight('150')
                                ->directory('achievements')
                                ->disk('public')
                                ->acceptedFileTypes(['image/*','application/pdf'])
                                ->columnSpanFull(),
                        ]),

                    Forms\Components\Tabs\Tab::make('Metadata')
                        ->icon('heroicon-o-tag')
                        ->schema([
                            Forms\Components\Textarea::make('description')
                                ->label('Deskripsi')
                                ->rows(3)
                                ->columnSpanFull(),

                            Forms\Components\Select::make('tags')
                                ->label('Tag')
                                ->multiple()
                                ->tags()
                                ->placeholder('Tambahkan tag')
                                ->columnSpanFull(),

                            Forms\Components\Toggle::make('is_featured')
                                ->label('Unggulkan')
                                ->inline(false),
                        ]),

                    Forms\Components\Tabs\Tab::make('Visibilitas')
                        ->icon('heroicon-o-eye')
                        ->schema([
                            Forms\Components\ToggleButtons::make('visibility')
                                ->label('Keterlihatan')
                                ->options([
                                    'public' => 'Publik',
                                    'private' => 'Privat',
                                    'unlisted' => 'Unlisted',
                                ])
                                ->inline()
                                ->icons([
                                    'public' => 'heroicon-m-eye',
                                    'private' => 'heroicon-m-lock-closed',
                                    'unlisted' => 'heroicon-m-link',
                                ])
                                ->default('private')
                                ->helperText('Atur ke Publik untuk tampil di profil portofolio.'),
                        ]),
                ]),
        ];
    }
}

