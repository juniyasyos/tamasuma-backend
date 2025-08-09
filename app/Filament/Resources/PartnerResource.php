<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PartnerResource\Pages;
use App\Models\Partner;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\{Section, Grid, TextInput, Textarea, FileUpload, Toggle};
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\{TextColumn, IconColumn, ImageColumn, Layout\Split, Layout\Stack};
use Filament\Tables\Actions\{Action, ActionGroup, ViewAction, EditAction, DeleteAction};
use Filament\Tables\Filters\{TernaryFilter, Filter};
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class PartnerResource extends Resource
{
    protected static ?string $model = Partner::class;

    protected static ?string $navigationIcon  = 'heroicon-c-user-group';
    protected static ?string $navigationGroup = 'Content Management';
    protected static ?string $navigationLabel = 'Mitra';
    protected static ?string $modelLabel      = 'Mitra';
    protected static ?string $pluralModelLabel = 'Mitra';

    /** Urutan navigasi: posisi ke-3 */
    protected static ?int $navigationSort = 3;

    /** Badge di menu: jumlah mitra yang disembunyikan */
    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::query()->where('is_visible', false)->count();
        return $count ? (string) $count : null;
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Jumlah mitra yang disembunyikan';
    }

    /** Global search */
    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'slug', 'website_url'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Slug'    => $record->slug,
            'Website' => $record->website_url ?: '-',
            'Status'  => $record->is_visible ? 'Tampil' : 'Disembunyikan',
        ];
    }

    public static function form(Form $form): Form
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

    public static function table(Table $table): Table
    {
        return $table
            // klik baris → halaman detail (infolist), bukan modal
            ->recordUrl(fn (Model $record) => static::getUrl('view', ['record' => $record]))
            ->columns([
                Split::make([
                    ImageColumn::make('logo_path')
                        ->label(' ')
                        ->disk('public')
                        ->circular()
                        ->height(40)
                        ->width(40)
                        ->toggleable(false)
                        ->placeholder(fn (Partner $r) => null),

                    TextColumn::make('name')
                        ->label('Nama')
                        ->searchable()
                        ->sortable()
                        ->weight(FontWeight::Bold)
                        ->description(fn (Partner $r) => $r->slug, position: 'below'),

                    Stack::make([
                        TextColumn::make('website_url')
                            ->label('Website')
                            ->url(fn ($state) => $state ?: null, true)
                            ->copyable()
                            ->copyMessage('URL disalin')
                            ->copyMessageDuration(1200)
                            ->limit(40),

                        IconColumn::make('is_visible')
                            ->label('Tampil')
                            ->boolean()
                            ->trueIcon('heroicon-m-check-circle')
                            ->falseIcon('heroicon-m-eye-slash')
                            ->trueColor('success')
                            ->falseColor('gray'),
                    ])->space(1)->visibleFrom('md'),
                ])->from('md'),

                // tampilan ringkas untuk mobile
                Stack::make([
                    TextColumn::make('website_url')
                        ->label('Website')
                        ->url(fn ($state) => $state ?: null, true)
                        ->limit(28),
                    IconColumn::make('is_visible')->label('Tampil')->boolean(),
                ])->visibleFrom('sm')->hiddenFrom('md'),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                TernaryFilter::make('is_visible')
                    ->label('Status Tampil')
                    ->placeholder('Semua')
                    ->trueLabel('Tampil')
                    ->falseLabel('Disembunyikan'),

                Filter::make('has_logo')
                    ->label('Hanya yang punya logo')
                    ->query(fn (Builder $q) => $q->whereNotNull('logo_path')->where('logo_path', '!=', ''))
                    // ->indicateUsing([Tables\Filters\Indicator::make('Dengan logo')]),
            ])
            ->actions([
                // aksi cepat (ikon) → hemat ruang
                ViewAction::make('detail')
                    ->label('Detail')
                    ->icon('heroicon-o-eye')
                    ->iconButton()
                    ->tooltip('Lihat detail'),

                EditAction::make('edit')
                    ->label('Edit')
                    ->icon('heroicon-o-pencil-square')
                    ->slideOver()
                    ->iconButton()
                    ->tooltip('Edit mitra'),

                ActionGroup::make([
                    Action::make('openWebsite')
                        ->label('Buka Website')
                        ->icon('heroicon-o-arrow-top-right-on-square')
                        ->url(fn (Partner $r) => $r->website_url ?: '#', true)
                        ->visible(fn (Partner $r) => filled($r->website_url)),

                    Action::make('toggleVisibility')
                        ->label(fn (Partner $r) => $r->is_visible ? 'Sembunyikan' : 'Tampilkan')
                        ->icon(fn (Partner $r) => $r->is_visible ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                        ->color(fn (Partner $r) => $r->is_visible ? 'gray' : 'success')
                        ->requiresConfirmation()
                        ->action(function (Partner $r) {
                            $r->update(['is_visible' => !$r->is_visible]);
                        })
                        ->successNotificationTitle('Status tampilan diperbarui.'),

                    Action::make('duplicate')
                        ->label('Duplikat')
                        ->icon('heroicon-o-document-duplicate')
                        ->action(function (Partner $r) {
                            $new = $r->replicate(['slug']);
                            $new->name = $r->name.' (Copy)';
                            $new->slug = Str::slug($r->slug.'-copy-'.Str::random(4));
                            $new->is_visible = false;
                            $new->save();
                        })
                        ->successNotificationTitle('Mitra berhasil diduplikasi (disembunyikan).'),

                    DeleteAction::make(),
                ])
                ->label('Lainnya')
                ->icon('heroicon-m-ellipsis-horizontal')
                ->button()
                ->size('sm'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('bulkShow')
                        ->label('Tampilkan Terpilih')
                        ->icon('heroicon-m-eye')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->update(['is_visible' => true]))
                        ->successNotificationTitle('Mitra terpilih ditampilkan.'),

                    Tables\Actions\BulkAction::make('bulkHide')
                        ->label('Sembunyikan Terpilih')
                        ->icon('heroicon-m-eye-slash')
                        ->color('gray')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->update(['is_visible' => false]))
                        ->successNotificationTitle('Mitra terpilih disembunyikan.'),

                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Belum ada mitra')
            ->emptyStateDescription('Tambahkan mitra untuk mulai menampilkan kolaborasi.')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()->label('Tambah Mitra')->slideOver(),
            ])
            ->paginated([10, 25, 50])
            ->deferLoading();
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPartners::route('/'),
            'create' => Pages\CreatePartner::route('/create'),
            'view'   => Pages\ViewPartner::route('/{record}'),
            'edit'   => Pages\EditPartner::route('/{record}/edit'),
        ];
    }
}
