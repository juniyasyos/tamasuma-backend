<?php

namespace App\Filament\Resources;

use App\Models\User;
use App\Filament\Resources\UserResource\Pages;
use App\Filament\Exports\UserExporter;
use App\Filament\Imports\UserImporter;
use Filament\Resources\Resource;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\{Section, TextInput, Select, Grid, Toggle};
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns;
use Filament\Tables\Columns\{ImageColumn, TextColumn, IconColumn, Layout\Split, Layout\Stack};
use Filament\Tables\Actions\{Action, ActionGroup, ViewAction, EditAction, DeleteAction, ExportAction, ImportAction, ExportBulkAction, BulkActionGroup, BulkAction};
use Filament\Tables\Filters\{SelectFilter, TernaryFilter, Filter};
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\{Section as InfolistSection, Grid as InfoGrid, TextEntry, IconEntry, ImageEntry, RepeatableEntry, BadgeEntry};
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon  = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'User Management';
    protected static ?string $navigationLabel = 'Users';
    protected static ?string $modelLabel      = 'User';
    protected static ?string $pluralModelLabel = 'Users';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Section::make('User Information')
                ->description('Lengkapi data pengguna. Password opsional saat edit.')
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('name')
                            ->label('Nama')
                            ->required()
                            ->maxLength(100),

                        TextInput::make('email')
                            ->email()
                            ->required()
                            ->unique(modifyRuleUsing: fn(Rule $rule, ?User $record) => $rule->ignore($record))
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
                        // Kalau kamu punya kolom avatar_url di users, field ini aktif:
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
                ])->columns(1),

            Section::make('Keamanan')
                ->collapsible()
                ->schema([
                    Toggle::make('two_factor_enabled')
                        ->label('Aktifkan 2FA')
                        ->helperText('Centang jika ingin memaksa 2FA (opsional, sesuaikan dengan model Anda).')
                        ->dehydrated(fn() => false), // contoh placeholder jika belum ada kolomnya
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordUrl(fn($record) => static::getUrl('view', ['record' => $record]))
            ->columns([
                Split::make([
                    ImageColumn::make('avatar_url')
                        ->circular()
                        ->grow(false)
                        ->getStateUsing(
                            fn(User $record) =>
                            $record->avatar_url ?: "https://ui-avatars.com/api/?name=" . urlencode($record->name)
                        )
                        ->extraImgAttributes(['loading' => 'lazy'])
                        ->toggleable(false),

                    TextColumn::make('name')
                        ->weight(FontWeight::Bold)
                        ->searchable()
                        ->sortable(),

                    Stack::make([
                        TextColumn::make('email')
                            ->icon('heroicon-m-envelope')
                            ->searchable()
                            ->copyable()
                            ->copyMessage('Email disalin')
                            ->copyMessageDuration(1500),

                        TextColumn::make('roles.name')
                            ->label('Roles')
                            ->badge()
                            ->color('info')
                            ->separator(',')
                            ->toggleable(isToggledHiddenByDefault: true),
                    ])->alignStart()->space(1)->visibleFrom('md'),
                ])->from('md'),

                // Kolom ringkas untuk mobile
                Stack::make([
                    TextColumn::make('email')->icon('heroicon-m-envelope'),
                    TextColumn::make('roles.name')->label('Roles')->badge()->color('info')->separator(','),
                ])->visibleFrom('sm')->hiddenFrom('md'),
            ])
            ->defaultSort('name')
            ->filters([
                SelectFilter::make('roles')
                    ->label('Filter Role')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload(),

                TernaryFilter::make('email_verified_at')
                    ->label('Verifikasi Email')
                    ->placeholder('Semua')
                    ->trueLabel('Terverifikasi')
                    ->falseLabel('Belum Verifikasi')
                    ->queries(
                        true: fn(Builder $q) => $q->whereNotNull('email_verified_at'),
                        false: fn(Builder $q) => $q->whereNull('email_verified_at'),
                        blank: fn(Builder $q) => $q,
                    ),

                Filter::make('created_range')
                    ->label('Rentang Pendaftaran')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('Dari'),
                        Forms\Components\DatePicker::make('until')->label('Sampai'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when($data['from'] ?? null, fn($q, $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['until'] ?? null, fn($q, $date) => $q->whereDate('created_at', '<=', $date));
                    })
                    ->indicateUsing(function (array $data): array {
                        $out = [];
                        if ($data['from'] ?? null) $out[] = Tables\Filters\Indicator::make('Dari ' . $data['from']);
                        if ($data['until'] ?? null) $out[] = Tables\Filters\Indicator::make('Sampai ' . $data['until']);
                        return $out;
                    }),
            ])
            ->actions([
                // Aksi utama (ikon saja biar hemat ruang)
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
                    ->tooltip('Edit pengguna'),

                ActionGroup::make([
                    // Set Role (sync)
                    Action::make('setRoles')
                        ->label('Atur Roles')
                        ->icon('heroicon-m-adjustments-vertical')
                        ->form([
                            Select::make('roles')
                                ->label('Roles')
                                ->relationship('roles', 'name')
                                ->multiple()
                                ->preload()
                                ->searchable()
                                ->required(),
                        ])
                        ->action(function (User $record, array $data) {
                            $record->roles()->sync($data['roles'] ?? []);
                        })
                        ->successNotificationTitle('Roles berhasil diperbarui.'),

                    // Verifikasi / Batalkan verifikasi
                    Action::make('verify')
                        ->label('Tandai Terverifikasi')
                        ->icon('heroicon-m-check-badge')
                        ->visible(fn(User $r) => is_null($r->email_verified_at))
                        ->requiresConfirmation()
                        ->action(fn(User $r) => $r->forceFill(['email_verified_at' => now()])->save())
                        ->successNotificationTitle('Email ditandai terverifikasi.'),

                    Action::make('unverify')
                        ->label('Batalkan Verifikasi')
                        ->color('gray')
                        ->icon('heroicon-m-x-mark')
                        ->visible(fn(User $r) => !is_null($r->email_verified_at))
                        ->requiresConfirmation()
                        ->action(fn(User $r) => $r->forceFill(['email_verified_at' => null])->save())
                        ->successNotificationTitle('Status verifikasi dihapus.'),

                    // Kirim ulang email verifikasi (jika model mendukung)
                    Action::make('resendVerification')
                        ->label('Kirim Ulang Verifikasi')
                        ->icon('heroicon-m-paper-airplane')
                        ->visible(fn(User $r) => method_exists($r, 'sendEmailVerificationNotification') && is_null($r->email_verified_at))
                        ->action(fn(User $r) => $r->sendEmailVerificationNotification())
                        ->successNotificationTitle('Email verifikasi dikirim.'),

                    // Impersonate (opsional, hanya jika paket tersedia)
                    Action::make('impersonate')
                        ->label('Impersonate')
                        ->icon('heroicon-m-user')
                        ->visible(fn() => class_exists(\STS\FilamentImpersonate\Tables\Actions\Impersonate::class))
                        ->action(function () {
                            // Biarkan Action ini tersembunyi bila paket belum terpasang;
                            // jika terpasang, sebaiknya ganti menjadi STS\FilamentImpersonate\Tables\Actions\Impersonate::make()
                        }),

                    DeleteAction::make(),
                ])
                    ->label('Lainnya')
                    ->icon('heroicon-m-ellipsis-horizontal')
                    ->button()
                    ->size('sm'),
            ])
            ->headerActions([
                ExportAction::make()->exporter(UserExporter::class),
                ImportAction::make()->importer(UserImporter::class),
                Tables\Actions\CreateAction::make()->label('Tambah User')->slideOver(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    // Bulk set roles
                    BulkAction::make('bulkSetRoles')
                        ->label('Atur Roles (Terpilih)')
                        ->icon('heroicon-m-adjustments-vertical')
                        ->form([
                            Select::make('roles')
                                ->label('Roles')
                                ->relationship('roles', 'name')
                                ->multiple()
                                ->preload()
                                ->searchable()
                                ->required(),
                        ])
                        ->action(function ($records, array $data) {
                            foreach ($records as $user) {
                                $user->roles()->sync($data['roles'] ?? []);
                            }
                        })
                        ->successNotificationTitle('Roles pengguna terpilih diperbarui.'),

                    // Bulk verify/unverify
                    BulkAction::make('bulkVerify')
                        ->label('Tandai Terverifikasi')
                        ->icon('heroicon-m-check-badge')
                        ->requiresConfirmation()
                        ->action(fn($records) => $records->each->forceFill(['email_verified_at' => now()])->save())
                        ->successNotificationTitle('Pengguna terpilih ditandai terverifikasi.'),

                    BulkAction::make('bulkUnverify')
                        ->label('Batalkan Verifikasi')
                        ->icon('heroicon-m-x-mark')
                        ->color('gray')
                        ->requiresConfirmation()
                        ->action(fn($records) => $records->each->forceFill(['email_verified_at' => null])->save())
                        ->successNotificationTitle('Status verifikasi pengguna terpilih dihapus.'),

                    ExportBulkAction::make()->exporter(UserExporter::class),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Belum ada pengguna')
            ->emptyStateDescription('Tambahkan pengguna baru untuk mulai mengelola akses.')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()->label('Tambah User')->slideOver(),
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
            'index'  => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view'   => Pages\ViewUser::route('/{record}'),
            'edit'   => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            InfolistSection::make('Profil')
                ->schema([
                    InfoGrid::make(['default' => 1, 'md' => 2, 'xl' => 3])->schema([
                        ImageEntry::make('avatar_url')
                            ->label('Avatar')
                            ->circular()
                            ->getStateUsing(fn(User $r) => $r->avatar_url ?: "https://ui-avatars.com/api/?name=" . urlencode($r->name))
                            ->columnSpan(1),

                        TextEntry::make('name')->label('Nama')->weight('semibold')->size('lg'),
                        TextEntry::make('email')->icon('heroicon-m-envelope')->copyable(),
                        IconEntry::make('email_verified_at')
                            ->label('Verifikasi Email')
                            ->boolean()
                            ->trueIcon('heroicon-m-check-circle')
                            ->falseIcon('heroicon-m-x-circle')
                            ->trueColor('success')
                            ->falseColor('gray'),
                        TextEntry::make('created_at')->label('Dibuat')->since()->icon('heroicon-m-calendar'),
                        TextEntry::make('updated_at')->label('Diubah')->since()->icon('heroicon-m-arrow-path'),
                    ]),

                    TextEntry::make('roles.name')
                        ->label('Roles')
                        ->badge()
                        ->color('info')
                        ->separator(' '),
                ])
                ->columns(1)
                ->collapsed(false),
        ]);
    }
}
