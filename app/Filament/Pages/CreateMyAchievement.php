<?php

namespace App\Filament\Pages;

use App\Models\Achievement;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class CreateMyAchievement extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-plus-circle';
    protected static ?string $navigationLabel = 'Tambah Pencapaian';
    protected static ?string $navigationGroup = null;
    protected static ?int $navigationSort = null;

    protected static string $view = 'filament.pages.create-my-achievement';

    public ?array $data = [];

    public static function shouldRegisterNavigation(): bool
    {
        // Akses hanya untuk user login; tidak tampil di menu utama agar fokus via tombol dari My Achievements
        return false;
    }

    public static function canAccess(): bool
    {
        return Auth::check();
    }

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(2)->schema([
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

                    Forms\Components\TextInput::make('issuer')->label('Penyelenggara')->maxLength(150),
                    Forms\Components\DatePicker::make('achieved_at')->label('Tanggal')->native(false),

                    Forms\Components\TextInput::make('url')->label('URL Terkait')->url()->columnSpanFull(),
                    Forms\Components\Textarea::make('description')->label('Deskripsi')->rows(3)->columnSpanFull(),

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

                    Forms\Components\Select::make('tags')->label('Tag')
                        ->multiple()->tags()->placeholder('Tambahkan tag')
                        ->columnSpanFull(),

                    Forms\Components\ToggleButtons::make('visibility')
                        ->label('Keterlihatan')
                        ->options([
                            'public' => 'Publik',
                            'private' => 'Privat',
                            'unlisted' => 'Unlisted',
                        ])->inline()->icons([
                            'public' => 'heroicon-m-eye',
                            'private' => 'heroicon-m-lock-closed',
                            'unlisted' => 'heroicon-m-link',
                        ])->default('private')->columnSpanFull(),

                    Forms\Components\Toggle::make('is_featured')->label('Unggulkan')->inline(false),
                ])
            ])
            ->statePath('data');
    }

    public function create(): void
    {
        $data = $this->form->getState();
        $data['user_id'] = Auth::id();
        Achievement::create($data);

        Notification::make()->title('Pencapaian ditambahkan')->success()->send();

        // Redirect kembali ke halaman daftar
        $this->redirect(\App\Filament\Pages\MyAchievements::getUrl());
    }
}

