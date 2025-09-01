<?php

namespace App\Filament\Pages;

use App\Models\Achievement;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Forms;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class MyAchievements extends Page implements Tables\Contracts\HasTable
{
    use Tables\Concerns\InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-trophy';
    protected static ?string $navigationLabel = 'Pencapaian Saya';
    protected static ?string $navigationGroup = 'Content Management';
    protected static ?int $navigationSort = 20;

    protected static string $view = 'filament.pages.my-achievements';

    public static function shouldRegisterNavigation(): bool
    {
        $u = Auth::user();
        if (! $u) return false;
        // Hide from Admin/Super Admin (mereka pakai AchievementResource)
        $isAdmin = method_exists($u, 'hasRole') && ($u->hasRole('super_admin') || $u->hasRole('Admin'));
        return ! $isAdmin;
    }

    public static function canAccess(): bool
    {
        return Auth::check();
    }

    public function getBreadcrumbs(): array
    {
        return [
            __('Dashboard') => \App\Filament\Support\Breadcrumbs::panelDashboardUrl(),
            __('Pencapaian Saya') => null,
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                ImageColumn::make('proof_image')->disk('public')->circular()->grow(false),
                TextColumn::make('title')->label('Judul')->wrap()->searchable()->sortable(),
                TextColumn::make('category')->badge()->color('info')->sortable(),
                TextColumn::make('issuer')->label('Penyelenggara')->toggleable(),
                TextColumn::make('achieved_at')->label('Tanggal')->date('d M Y')->sortable(),
                TextColumn::make('visibility')->badge()->color(fn($state) => match($state){
                    'public' => 'success', 'private' => 'gray', 'unlisted' => 'warning', default => 'gray'
                }),
            ])
            ->headerActions([
                Tables\Actions\Action::make('go_to_create')
                    ->label('Tambah Pencapaian')
                    ->icon('heroicon-o-plus-circle')
                    ->url(fn () => \App\Filament\Pages\CreateMyAchievement::getUrl())
                    ->openUrlInNewTab(false),
            ])
            ->actions([
                EditAction::make()
                    ->modalHeading('Ubah Pencapaian')
                    ->form($this->formSchema())
                    ->visible(fn(Achievement $record) => $record->created_via === 'self'),
                DeleteAction::make()
                    ->visible(fn(Achievement $record) => $record->created_via === 'self'),
            ])
            ->emptyStateHeading('Belum ada pencapaian')
            ->emptyStateDescription('Tambahkan pencapaian Anda menggunakan tombol di atas.')
            ->defaultSort('achieved_at', 'desc');
    }

    protected function getTableQuery(): Builder
    {
        $userId = Auth::id();
        return Achievement::query()->where('user_id', $userId);
    }

    protected function formSchema(): array
    {
        return [
            Forms\Components\Grid::make(2)->schema([
                Forms\Components\TextInput::make('title')->label('Judul')->required()->maxLength(150),
                Forms\Components\Select::make('category')->label('Kategori')->options([
                    'certificate' => 'Sertifikat',
                    'award' => 'Penghargaan',
                    'competition' => 'Kompetisi',
                    'training' => 'Pelatihan',
                    'other' => 'Lainnya',
                ])->native(false),

                Forms\Components\TextInput::make('issuer')->label('Penyelenggara')->maxLength(150),
                Forms\Components\DatePicker::make('achieved_at')->label('Tanggal')->native(false),

                Forms\Components\TextInput::make('url')->label('URL Terkait')->url()->columnSpanFull(),
                Forms\Components\Textarea::make('description')->label('Deskripsi')->rows(3)->columnSpanFull(),

                Forms\Components\FileUpload::make('proof_image')
                    ->label('Bukti (Gambar/PDF)')
                    ->imagePreviewHeight('150')
                    ->downloadable()
                    ->openable()
                    ->directory('achievements')
                    ->disk('public')
                    ->imageEditor()
                    ->maxSize(8192)
                    ->acceptedFileTypes(['image/*', 'application/pdf'])
                    ->columnSpanFull(),

                Forms\Components\Select::make('tags')->label('Tag')
                    ->multiple()->placeholder('Tambahkan tag')->columnSpanFull(),

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
        ];
    }
}
