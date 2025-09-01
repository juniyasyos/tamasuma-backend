<?php

namespace App\Filament\Resources\UserResource;

use App\Filament\Exports\UserExporter;
use App\Filament\Imports\UserImporter;
use App\Filament\Resources\UserResource;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ExportAction;
use Filament\Tables\Actions\ExportBulkAction;
use Filament\Tables\Actions\ImportAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\Layout\Panel;
use Filament\Tables\Columns\Layout\Grid as LayoutGrid;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table as FilamentTable;
use Illuminate\Database\Eloquent\Builder;

class Table extends UserResource
{
    /**
     * Configure the table for the User resource.
     */
    public static function make(FilamentTable $table): FilamentTable
    {
        return $table
            ->recordUrl(fn ($record) => static::getUrl('view', ['record' => $record]))
            ->persistFiltersInSession()
            ->persistSearchInSession()
            // Arrange records into a responsive grid (acts like cards on small screens)
            ->contentGrid([
                'default' => 1,
                'sm' => 1,
                'md' => 2,
                'lg' => 2,
                'xl' => 3,
            ])
            ->columns([
                Split::make([
                    ImageColumn::make('avatar_url')
                        ->circular()
                        ->grow(false)
                        ->getStateUsing(
                            fn (User $record) => $record->avatar_url ?: 'https://ui-avatars.com/api/?name='.urlencode($record->name)
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

                // Layout kartu grid untuk mobile: panel berisi grid internal
                Panel::make([
                    LayoutGrid::make(2)
                        ->schema([
                            Stack::make([
                                TextColumn::make('email')
                                    ->icon('heroicon-m-envelope')
                                    ->limit(32)
                                    ->listWithLineBreaks(),
                                TextColumn::make('roles.name')
                                    ->label('Roles')
                                    ->badge()
                                    ->color('info')
                                    ->separator(',')
                                    ->limitList(2)
                                    ->tooltip(fn ($state) => is_array($state) ? implode(', ', $state) : $state),
                            ])->columnSpan(2),

                            TextColumn::make('created_at')
                                ->label('Dibuat')
                                ->since()
                                ->icon('heroicon-m-clock'),

                            TextColumn::make('email_verified_at')
                                ->label('Verifikasi Email')
                                ->formatStateUsing(fn($state) => $state ? 'Terverifikasi' : 'Belum')
                                ->badge()
                                ->colors([
                                    'success' => fn($state) => (bool) $state,
                                    'warning' => fn($state) => ! (bool) $state,
                                ])
                                ->icon(fn($state) => $state ? 'heroicon-m-check-badge' : 'heroicon-m-exclamation-triangle'),
                        ]),
                ])
                    ->collapsed()
                    ->hiddenFrom('md'),
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
                        true: fn (Builder $q) => $q->whereNotNull('email_verified_at'),
                        false: fn (Builder $q) => $q->whereNull('email_verified_at'),
                        blank: fn (Builder $q) => $q,
                    ),

                Filter::make('created_range')
                    ->label('Rentang Pendaftaran')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('Dari'),
                        Forms\Components\DatePicker::make('until')->label('Sampai'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when($data['from'] ?? null, fn ($q, $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['until'] ?? null, fn ($q, $date) => $q->whereDate('created_at', '<=', $date));
                    })
                    ->indicateUsing(function (array $data): array {
                        $out = [];
                        if ($data['from'] ?? null) {
                            $out[] = Tables\Filters\Indicator::make('Dari '.$data['from']);
                        }
                        if ($data['until'] ?? null) {
                            $out[] = Tables\Filters\Indicator::make('Sampai '.$data['until']);
                        }

                        return $out;
                    }),
            ])
            ->actions([
                // Aksi utama (ikon saja biar hemat ruang)
                ViewAction::make('detail')
                    ->label('Detail')
                    ->icon('heroicon-o-eye')->color('gray')
                    ->iconButton()
                    ->tooltip('Lihat detail'),

                EditAction::make('edit')
                    ->label('Edit')
                    ->icon('heroicon-o-pencil-square')->color('warning')
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
                                ->label('Role')
                                ->relationship('roles', 'name')
                                ->multiple()
                                ->maxItems(1)
                                ->preload()
                                ->searchable()
                                ->native(false)
                                ->required(),
                        ])
                        ->action(function (User $record, array $data) {
                            $roles = $data['roles'] ?? [];
                            $first = is_array($roles) ? (array_slice($roles, 0, 1) ?: []) : (isset($roles) ? [$roles] : []);
                            $record->roles()->sync($first);
                        })
                        ->successNotificationTitle('Roles berhasil diperbarui.'),

                    // Verifikasi / Batalkan verifikasi
                    Action::make('verify')
                        ->label('Tandai Terverifikasi')
                        ->icon('heroicon-m-check-badge')
                        ->visible(fn (User $r) => is_null($r->email_verified_at))
                        ->requiresConfirmation()
                        ->action(fn (User $r) => $r->forceFill(['email_verified_at' => now()])->save())
                        ->successNotificationTitle('Email ditandai terverifikasi.'),

                    Action::make('unverify')
                        ->label('Batalkan Verifikasi')
                        ->color('gray')
                        ->icon('heroicon-m-x-mark')
                        ->visible(fn (User $r) => ! is_null($r->email_verified_at))
                        ->requiresConfirmation()
                        ->action(fn (User $r) => $r->forceFill(['email_verified_at' => null])->save())
                        ->successNotificationTitle('Status verifikasi dihapus.'),

                    // Kirim ulang email verifikasi (jika model mendukung)
                    Action::make('resendVerification')
                        ->label('Kirim Ulang Verifikasi')
                        ->icon('heroicon-m-paper-airplane')
                        ->visible(fn (User $r) => method_exists($r, 'sendEmailVerificationNotification') && is_null($r->email_verified_at))
                        ->action(fn (User $r) => $r->sendEmailVerificationNotification())
                        ->successNotificationTitle('Email verifikasi dikirim.'),

                    // Impersonate (opsional, hanya jika paket tersedia)
                    Action::make('impersonate')
                        ->label('Impersonate')
                        ->icon('heroicon-m-user')
                        ->visible(fn () => class_exists(\STS\FilamentImpersonate\Tables\Actions\Impersonate::class))
                        ->action(function () {
                            // Biarkan Action ini tersembunyi bila paket belum terpasang;
                            // jika terpasang, sebaiknya ganti menjadi STS\FilamentImpersonate\Tables\Actions\Impersonate::make()
                        }),

                    DeleteAction::make(),
                ])
                    ->label('Lainnya')
                    ->icon('heroicon-m-ellipsis-horizontal')
                    ->button()->color('gray')
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
                                ->label('Role')
                                ->relationship('roles', 'name')
                                ->multiple()
                                ->maxItems(1)
                                ->preload()
                                ->searchable()
                                ->native(false)
                                ->required(),
                        ])
                        ->action(function ($records, array $data) {
                            $roles = $data['roles'] ?? [];
                            $first = is_array($roles) ? (array_slice($roles, 0, 1) ?: []) : (isset($roles) ? [$roles] : []);
                            foreach ($records as $user) {
                                $user->roles()->sync($first);
                            }
                        })
                        ->successNotificationTitle('Roles pengguna terpilih diperbarui.'),

                    // Bulk verify/unverify
                    BulkAction::make('bulkVerify')
                        ->label('Tandai Terverifikasi')
                        ->icon('heroicon-m-check-badge')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->forceFill(['email_verified_at' => now()])->save())
                        ->successNotificationTitle('Pengguna terpilih ditandai terverifikasi.'),

                    BulkAction::make('bulkUnverify')
                        ->label('Batalkan Verifikasi')
                        ->icon('heroicon-m-x-mark')
                        ->color('gray')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->forceFill(['email_verified_at' => null])->save())
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
}
