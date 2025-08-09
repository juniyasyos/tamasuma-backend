<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProgramResource\Pages;
use App\Models\Program;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Filament\Tables\Actions\{Action, ActionGroup, ViewAction, EditAction, ReplicateAction};

class ProgramResource extends Resource
{
    protected static ?string $model = Program::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Content Management';
    protected static ?string $navigationLabel = 'Program Pembelajaran';
    protected static ?string $pluralModelLabel = 'Program';
    protected static ?string $modelLabel = 'Program Pembelajaran';

    public static function getNavigationBadge(): ?string
    {
        return (string) Program::query()->where('is_published', false)->count();
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Jumlah program berstatus Draft';
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['title', 'slug', 'platform', 'learningArea.name'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Bidang'   => optional($record->learningArea)->name,
            'Level'    => Str::title($record->level ?? '-'),
            'Sumber'   => Str::title($record->source ?? '-'),
            'Platform' => $record->platform ?: '-',
        ];
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
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
                                                if (!filled($get('slug')) || $get('slug') === Str::slug((string) $get('title', ''))) {
                                                    $set('slug', Str::slug((string) $state));
                                                }
                                            })
                                            ->helperText('Gunakan judul singkat & jelas (maks. 100 karakter).'),

                                        TextInput::make('slug')
                                            ->label('Slug URL')
                                            ->readOnly()
                                            ->unique(ignoreRecord: true)
                                            ->maxLength(100)
                                            ->helperText('Terbentuk otomatis dari judul.'),
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
                                            'internal' => 'gray',
                                            'external' => 'info',
                                        ])
                                        ->icons([
                                            'internal' => 'heroicon-o-building-library',
                                            'external' => 'heroicon-o-globe-alt',
                                        ])
                                        ->inline()
                                        ->required()
                                        ->live(),

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
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->heading('Daftar Program')
            ->description('Kelola program pembelajaran internal maupun eksternal.')
            ->recordUrl(fn(Model $record) => static::getUrl('view', ['record' => $record]))
            ->columns([
                TextColumn::make('title')
                    ->label('Judul')
                    ->searchable(isIndividual: true)
                    ->sortable()
                    ->wrap()
                    ->weight(FontWeight::Bold)
                    ->limit(60),

                TextColumn::make('learningArea.name')
                    ->label('Bidang')
                    ->sortable()
                    ->badge()
                    ->color('gray')
                    ->searchable(isIndividual: true),

                TextColumn::make('level')
                    ->label('Tingkat')
                    ->badge()
                    ->sortable()
                    ->color(fn(string $state) => match ($state) {
                        'pemula' => 'success',
                        'menengah' => 'warning',
                        'lanjutan' => 'danger',
                        default   => 'gray',
                    })
                    ->formatStateUsing(fn(?string $state) => $state ? Str::title($state) : '-'),

                TextColumn::make('source')
                    ->label('Sumber')
                    ->badge()
                    ->sortable()
                    ->color(fn(string $state) => match ($state) {
                        'internal' => 'gray',
                        'external' => 'info',
                        default     => 'gray',
                    }),

                TextColumn::make('platform')
                    ->label('Platform')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(isIndividual: true),

                IconColumn::make('is_certified')
                    ->label('Sertifikat')
                    ->boolean()
                    ->trueIcon('heroicon-m-check-circle')
                    ->falseIcon('heroicon-m-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->sortable(),

                IconColumn::make('is_published')
                    ->label('Terbitkan')
                    ->boolean()
                    ->trueIcon('heroicon-m-check-circle')
                    ->falseIcon('heroicon-m-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->groups([
                Tables\Grouping\Group::make('learningArea.name')->label('Kelompok Bidang')->collapsible(),
                Tables\Grouping\Group::make('level')->label('Kelompok Level')->collapsible(),
            ])
            ->filters([
                SelectFilter::make('learning_area_id')
                    ->label('Bidang')
                    ->relationship('learningArea', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('level')
                    ->label('Tingkat')
                    ->options([
                        'pemula'   => 'Pemula',
                        'menengah' => 'Menengah',
                        'lanjutan' => 'Lanjutan',
                    ]),

                SelectFilter::make('source')
                    ->label('Sumber')
                    ->options([
                        'internal' => 'Internal',
                        'external' => 'Eksternal',
                    ]),

                TernaryFilter::make('is_published')
                    ->label('Status Publikasi')
                    ->placeholder('Semua')
                    ->trueLabel('Hanya Publik')
                    ->falseLabel('Hanya Draft')
                    ->indicateUsing(fn($state) => match ($state) {
                        true  => 'Publik',
                        false => 'Draft',
                        default => null,
                    }),

                TernaryFilter::make('is_certified')
                    ->label('Sertifikat')
                    ->placeholder('Semua') // ⬅️ perbaikan: ganti nullableLabel()
                    ->trueLabel('Dengan Sertifikat')
                    ->falseLabel('Tanpa Sertifikat'),

                Filter::make('created_at')
                    ->label('Rentang Tanggal')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('Dari'),
                        Forms\Components\DatePicker::make('until')->label('Sampai'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'] ?? null, fn($q, $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['until'] ?? null, fn($q, $date) => $q->whereDate('created_at', '<=', $date));
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['from'] ?? null) $indicators[] = Tables\Filters\Indicator::make('Dari ' . $data['from']);
                        if ($data['until'] ?? null) $indicators[] = Tables\Filters\Indicator::make('Sampai ' . $data['until']);
                        return $indicators;
                    }),
            ])
            ->actions([
                // Quick action: Detail (link ke page, bukan modal)
                ViewAction::make('detail')
                    ->label('Detail')
                    ->icon('heroicon-o-eye')
                    ->url(fn(Program $record) => static::getUrl('view', ['record' => $record]))
                    ->openUrlInNewTab(false)
                    ->iconButton()                // ikon saja → hemat ruang
                    ->tooltip('Lihat detail'),

                // Quick action: Edit (pakai slide-over biar tetap terasa ringan)
                EditAction::make('edit')
                    ->label('Edit')
                    ->icon('heroicon-o-pencil-square')
                    ->slideOver()                 // edit di slide-over (lebih enak di desktop & tetap oke di mobile)
                    ->iconButton()
                    ->tooltip('Edit'),

                // Lainnya: dikelompokkan agar tetap clean di layar kecil
                ActionGroup::make([
                    // Publish / Unpublish kontekstual
                    Action::make('publish')
                        ->label('Publikasikan')
                        ->icon('heroicon-o-eye')
                        ->color('success')
                        ->visible(fn(Program $record) => !$record->is_published)
                        ->requiresConfirmation()
                        ->action(fn(Program $record) => $record->update(['is_published' => true]))
                        ->successNotificationTitle('Program dipublikasikan.'),

                    Action::make('unpublish')
                        ->label('Jadikan Draft')
                        ->icon('heroicon-o-eye-slash')
                        ->color('gray')
                        ->visible(fn(Program $record) => $record->is_published)
                        ->requiresConfirmation()
                        ->action(fn(Program $record) => $record->update(['is_published' => false]))
                        ->successNotificationTitle('Program diubah menjadi draft.'),

                    // Buka link eksternal (hanya jika sumber eksternal & ada URL)
                    Action::make('openExternal')
                        ->label('Buka Link Program')
                        ->icon('heroicon-o-arrow-top-right-on-square')
                        ->url(fn(Program $record) => $record->external_url ?: '#', true)
                        ->visible(fn(Program $record) => $record->source === 'external' && filled($record->external_url)),

                    // Duplikasi aman: set judul & slug baru, default jadi draft
                    ReplicateAction::make('duplicate')
                        ->label('Duplikat')
                        ->icon('heroicon-o-document-duplicate')
                        ->mutateRecordDataUsing(function (array $data, Program $record): array {
                            $newTitle = $record->title . ' (Copy)';
                            $data['title'] = $newTitle;
                            $data['slug'] = Str::slug($record->slug . '-copy-' . Str::random(4));
                            $data['is_published'] = false;
                            return $data;
                        })
                        ->successNotificationTitle('Program diduplikasi (status: draft).'),
                ])
                    ->label('Lainnya')
                    ->icon('heroicon-m-ellipsis-horizontal')
                    ->button()
                    ->size('sm'),
            ])

            ->bulkActions([])
            ->emptyStateHeading('Belum ada program')
            ->emptyStateDescription('Buat program pertama kamu untuk mulai mengelola konten pembelajaran.')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()->label('Buat Program'),
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
            'index' => Pages\ListPrograms::route('/'),
            'create' => Pages\CreateProgram::route('/create'),
            'view' => Pages\ViewProgram::route('/{record}'),
            'edit' => Pages\EditProgram::route('/{record}/edit'),
        ];
    }
}
