<?php

namespace App\Filament\Resources\UserResource;

use App\Filament\Resources\UserResource;
use App\Models\Achievement;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Unique;

class Schema extends UserResource
{
    /**
     * Get the form schema for the User resource.
     */
    public static function make(): array
    {
        return [
            Tabs::make('UserTabs')
                ->columnSpanFull()
                ->tabs([
                    Tab::make('Profil')
                        ->icon('heroicon-o-user')
                        ->schema([
                            Grid::make(2)->schema([
                                TextInput::make('name')
                                    ->label('Nama')
                                    ->required()
                                    ->maxLength(100),

                                TextInput::make('email')
                                    ->email()
                                    ->required()
                                    ->unique(modifyRuleUsing: fn(Unique $rule, ?User $record) => $rule->ignore($record))
                                    ->helperText('Gunakan email aktif.'),
                            ]),

                            Grid::make(2)->schema([
                                TextInput::make('password')
                                    ->label('Password')
                                    ->password()
                                    ->revealable()
                                    ->confirmed()
                                    ->dehydrateStateUsing(fn($state) => filled($state) ? Hash::make($state) : null)
                                    ->dehydrated(fn($state) => filled($state))
                                    ->required(fn(?User $record) => $record === null),

                                TextInput::make('password_confirmation')
                                    ->label('Konfirmasi Password')
                                    ->password()
                                    ->revealable()
                                    ->dehydrated(false)
                                    ->required(fn(?User $record, Forms\Get $get) => $record === null || filled($get('password'))),
                            ]),

                            Grid::make(2)->schema([
                                TextInput::make('avatar_url')
                                    ->label('Avatar URL')
                                    ->url()
                                    ->suffixAction(
                                        Forms\Components\Actions\Action::make('clear')->icon('heroicon-m-x-mark')->action(fn($set) => $set('avatar_url', null))
                                    )
                                    ->helperText('Boleh kosong. Jika kosong, avatar akan memakai inisial nama.'),

                                Select::make('roles')
                                    ->label('Peran (Roles)')
                                    ->relationship('roles', 'name')
                                    ->multiple()
                                    ->preload()
                                    ->searchable()
                                    ->helperText('Pilih satu atau lebih peran.'),
                            ]),
                        ]),

                    Tab::make('Keamanan')
                        ->icon('heroicon-o-lock-closed')
                        ->schema([
                            Toggle::make('two_factor_enabled')
                                ->label('Aktifkan 2FA')
                                ->helperText('Centang jika ingin memaksa 2FA (opsional, sesuaikan dengan model Anda).')
                                ->dehydrated(fn() => false),
                        ]),

                    Tab::make('Pencapaian')
                        ->icon('heroicon-o-star')
                        ->schema([
                            Forms\Components\Repeater::make('achievements')
                                ->relationship('achievements')
                                ->label('Daftar Pencapaian')
                                ->addActionLabel('Tambah Pencapaian')
                                ->orderable('achieved_at')
                                ->defaultItems(0)
                                ->columns(2)
                                ->schema([
                                    Forms\Components\TextInput::make('title')
                                        ->label('Judul')
                                        ->placeholder('Contoh: Sertifikat Data Science')
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
                                        ->native(false)
                                        ->required(),

                                    Forms\Components\TextInput::make('issuer')
                                        ->label('Penyelenggara/Pemberi')
                                        ->maxLength(150),

                                    Forms\Components\DatePicker::make('achieved_at')
                                        ->label('Tanggal')
                                        ->displayFormat('d M Y')
                                        ->native(false),

                                    Forms\Components\TextInput::make('url')
                                        ->label('URL terkait')
                                        ->url()
                                        ->maxLength(255)
                                        ->columnSpanFull(),

                                    Forms\Components\FileUpload::make('proof_image')
                                        ->label('Bukti (Gambar)')
                                        ->image()
                                        ->directory('achievements')
                                        ->disk('public')
                                        ->imageEditor()
                                        ->downloadable()
                                        ->openable()
                                        ->columnSpanFull(),

                                    Forms\Components\Textarea::make('description')
                                        ->label('Deskripsi')
                                        ->rows(3)
                                        ->columnSpanFull(),

                                    Forms\Components\Toggle::make('is_featured')
                                        ->label('Tampilkan menonjol')
                                        ->inline(false),
                                ])
                                ->reorderable(true)
                                ->grid(1)
                                ->collapsed(false)
                                ->helperText('Tambahkan pencapaian secara manual, termasuk gambar bukti.'),
                        ]),

                    Tab::make('Program')
                        ->icon('heroicon-o-rectangle-stack')
                        ->schema([
                            Forms\Components\Repeater::make('enrollments')
                                ->relationship('enrollments')
                                ->label('Enrolmen Program')
                                ->addActionLabel('Tambah Enrolmen')
                                ->defaultItems(0)
                                ->columns(2)
                                ->schema([
                                    Forms\Components\Select::make('program_id')
                                        ->label('Program')
                                        ->relationship('program', 'title')
                                        ->searchable()
                                        ->preload()
                                        ->required()
                                        ->native(false)
                                        ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                        ->helperText('Setiap program hanya boleh satu kali per user.'),

                                    Forms\Components\Select::make('status')
                                        ->label('Status')
                                        ->options([
                                            'active' => 'Aktif',
                                            'completed' => 'Selesai',
                                            'dropped' => 'Berhenti',
                                        ])
                                        ->required()
                                        ->native(false),

                                    Forms\Components\DatePicker::make('enrolled_at')
                                        ->label('Tanggal Daftar')
                                        ->native(false),

                                    Forms\Components\DatePicker::make('completed_at')
                                        ->label('Tanggal Selesai')
                                        ->native(false),

                                    Forms\Components\Textarea::make('notes')
                                        ->label('Catatan')
                                        ->rows(2)
                                        ->columnSpanFull(),
                                ])
                                ->reorderable(false)
                                ->grid(1)
                                ->helperText('Catat pendaftaran user ke program, termasuk status dan tanggal.'),
                        ]),
                ]),
        ];
    }
}
