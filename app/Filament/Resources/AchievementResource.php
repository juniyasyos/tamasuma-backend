<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AchievementResource\Pages;
use App\Models\Achievement;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ImageColumn;
use Illuminate\Support\Facades\Auth;

class AchievementResource extends Resource
{
    protected static ?string $model = Achievement::class;

    protected static ?string $navigationIcon = 'heroicon-o-trophy';
    protected static ?string $navigationGroup = 'Content Management';
    protected static ?string $navigationLabel = 'Pencapaian';

    public static function shouldRegisterNavigation(): bool
    {
        $u = Auth::user();
        if (! $u) return false;
        // Only Admin & Super Admin (or explicit permission) see this in navigation
        $isAdmin = method_exists($u, 'hasRole') && ($u->hasRole('super_admin') || $u->hasRole('Admin'));
        return $isAdmin || $u->can('view_any_achievement');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(2)->schema([
                    Forms\Components\Select::make('user_id')
                        ->label('Pengguna')
                        ->relationship('user', 'name')
                        ->searchable()
                        ->preload()
                        ->required()
                        ->native(false)
                        ->visible(fn() => ($u = Auth::user()) && method_exists($u, 'hasRole') && ($u->hasRole('super_admin') || $u->hasRole('Admin'))),

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
                        ->label('Bukti (Gambar)')
                        ->image()
                        ->directory('achievements')
                        ->disk('public')
                        ->imageEditor()
                        ->downloadable()
                        ->openable()
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
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->contentGrid([
                'default' => 1,
                'sm' => 1,
                'md' => 2,
                'lg' => 3,
                'xl' => 4,
            ])
            ->columns([
                ImageColumn::make('proof_image')->disk('public')->circular()->grow(false),
                TextColumn::make('user.name')->label('Pengguna')
                    ->visible(fn() => ($u = Auth::user()) && method_exists($u, 'hasRole') && ($u->hasRole('super_admin') || $u->hasRole('Admin'))),
                TextColumn::make('title')->label('Judul')->wrap()->searchable()->sortable(),
                TextColumn::make('category')->badge()->color('info')->sortable(),
                TextColumn::make('issuer')->label('Penyelenggara')->toggleable(),
                TextColumn::make('achieved_at')->label('Tanggal')->date('d M Y')->sortable(),
                TextColumn::make('visibility')->badge()->color(fn($state) => match($state){
                    'public' => 'success', 'private' => 'gray', 'unlisted' => 'warning', default => 'gray'
                }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')->label('Kategori')->options([
                    'certificate' => 'Sertifikat',
                    'award' => 'Penghargaan',
                    'competition' => 'Kompetisi',
                    'training' => 'Pelatihan',
                    'other' => 'Lainnya',
                ]),
                Tables\Filters\TernaryFilter::make('is_featured')->label('Unggulan'),
                Tables\Filters\Filter::make('date_range')->form([
                    Forms\Components\DatePicker::make('from')->label('Dari')->native(false),
                    Forms\Components\DatePicker::make('until')->label('Sampai')->native(false),
                ])->query(function($q, $data){
                    return $q
                        ->when(($data['from'] ?? null), fn($qq,$d)=>$qq->whereDate('achieved_at','>=',$d))
                        ->when(($data['until'] ?? null), fn($qq,$d)=>$qq->whereDate('achieved_at','<=',$d));
                }),
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

    public static function getRelations(): array { return []; }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAchievements::route('/'),
            'create' => Pages\CreateAchievement::route('/create'),
            'edit' => Pages\EditAchievement::route('/{record}/edit'),
        ];
    }
}
