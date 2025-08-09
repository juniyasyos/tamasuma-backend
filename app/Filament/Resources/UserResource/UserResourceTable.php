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
use Filament\Tables\Table;
use Filament\Tables\Columns\{ImageColumn, TextColumn, IconColumn, Layout\Split, Layout\Stack};
use Filament\Tables\Actions\{Action, ActionGroup, ViewAction, EditAction, DeleteAction, ExportAction, ImportAction, ExportBulkAction, BulkActionGroup, BulkAction};
use Filament\Tables\Filters\{SelectFilter, TernaryFilter, Filter};
use Illuminate\Database\Eloquent\Builder;

class UserResourceTable
{
    public static function make(Table $table): Table
    {
        return $table
            ->recordUrl(fn($record) => UserResource::getUrl('view', ['record' => $record]))
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

                    Action::make('resendVerification')
                        ->label('Kirim Ulang Verifikasi')
                        ->icon('heroicon-m-paper-airplane')
                        ->visible(fn(User $r) => method_exists($r, 'sendEmailVerificationNotification') && is_null($r->email_verified_at))
                        ->action(fn(User $r) => $r->sendEmailVerificationNotification())
                        ->successNotificationTitle('Email verifikasi dikirim.'),

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
}
