<?php

namespace App\Filament\Resources\ProgramResource\RelationManagers;

use App\Models\Enrollment;
use App\Models\User;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EnrollmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'enrollments';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Permohonan & Enrolmen')
            ->description('Kelola permohonan dan status keikutsertaan program. Gunakan aksi di atas untuk operasi cepat.')
            ->recordTitleAttribute('user.name')
            ->defaultSort('requested_at', 'desc')

            /* ================= HEADER (GLOBAL) ACTIONS ================= */
            ->headerActions([
                // Tambahkan pendaftar baru
                Action::make('addEnrollment')
                    ->label('Tambah Pendaftar')
                    ->icon('heroicon-o-user-plus')
                    ->color('primary')
                    ->form([
                        Forms\Components\Select::make('user_id')
                            ->label('Pilih Pengguna')
                            ->options(function () {
                                $owner = $this->getOwnerRecord();
                                $already = Enrollment::query()
                                    ->where('program_id', $owner->getKey())
                                    ->pluck('user_id');

                                return User::query()
                                    ->role('Pelajar')
                                    ->whereNotIn('id', $already)
                                    ->orderBy('name')
                                    ->pluck('name', 'id');
                            })
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\Select::make('status')
                            ->label('Status Awal')
                            ->options([
                                'requested' => 'Requested',
                                'active' => 'Active',
                            ])
                            ->default('requested')
                            ->required(),

                        Forms\Components\DatePicker::make('enrolled_at')
                            ->label('Tanggal Mulai (jika Active)')
                            ->native(false)
                            ->visible(fn (Forms\Get $get) => $get('status') === 'active'),

                        Forms\Components\Textarea::make('notes')
                            ->label('Catatan (opsional)')
                            ->rows(2),
                    ])
                    ->action(function (array $data) {
                        $owner = $this->getOwnerRecord();

                        // Hindari duplikasi
                        $exists = Enrollment::query()
                            ->where('program_id', $owner->getKey())
                            ->where('user_id', $data['user_id'])
                            ->exists();

                        if ($exists) {
                            Notification::make()->title('Pengguna sudah terdaftar di program ini.')->warning()->send();

                            return;
                        }

                        Enrollment::create([
                            'program_id' => $owner->getKey(),
                            'user_id' => $data['user_id'],
                            'status' => $data['status'],
                            'requested_at' => now(),
                            'approved_at' => $data['status'] === 'active' ? now() : null,
                            'enrolled_at' => $data['status'] === 'active'
                                ? ($data['enrolled_at'] ?? now()->toDateString())
                                : null,
                            'notes' => $data['notes'] ?? null,
                        ]);

                        Notification::make()->title('Pendaftar ditambahkan')->success()->send();
                    }),

                // Setujui semua pending
                Action::make('approveAllPending')
                    ->label('Setujui Semua Pending')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Setujui semua permohonan pending?')
                    ->action(function () {
                        $owner = $this->getOwnerRecord();
                        $updated = Enrollment::query()
                            ->where('program_id', $owner->getKey())
                            ->where('status', 'requested')
                            ->update([
                                'status' => 'active',
                                'approved_at' => now(),
                                'enrolled_at' => now()->toDateString(),
                                'rejected_at' => null,
                            ]);

                        Notification::make()
                            ->title("{$updated} permohonan disetujui")
                            ->success()
                            ->send();
                    }),

                // Batalkan semua penyetujuan (active -> requested)
                Action::make('cancelAllActive')
                    ->label('Batalkan Semua Penyetujuan')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Batalkan semua yang sudah disetujui?')
                    ->modalDescription('Status akan dikembalikan ke Requested dan tanggal approved/enrolled dikosongkan.')
                    ->action(function () {
                        $owner = $this->getOwnerRecord();
                        $updated = Enrollment::query()
                            ->where('program_id', $owner->getKey())
                            ->where('status', 'active')
                            ->update([
                                'status' => 'requested',
                                'approved_at' => null,
                                'enrolled_at' => null,
                            ]);

                        Notification::make()
                            ->title("{$updated} penyetujuan dibatalkan")
                            ->warning()
                            ->send();
                    }),

                // Export CSV (berdasarkan query tabel saat ini)
                Action::make('exportCsv')
                    ->label('Export CSV')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->action(function () {
                        $owner = $this->getOwnerRecord();

                        $rows = Enrollment::query()
                            ->with('user:id,name,email')
                            ->where('program_id', $owner->getKey())
                            ->orderByDesc('requested_at')
                            ->get([
                                'id', 'user_id', 'status', 'requested_at', 'approved_at', 'rejected_at',
                                'enrolled_at', 'completed_at', 'notes',
                            ]);

                        $filename = 'enrollments_'.$owner->id.'_'.now()->format('Ymd_His').'.csv';

                        return new StreamedResponse(function () use ($rows) {
                            $handle = fopen('php://output', 'w');
                            fputcsv($handle, [
                                'ID', 'User', 'Email', 'Status', 'Requested At', 'Approved At',
                                'Rejected At', 'Enrolled At', 'Completed At', 'Notes',
                            ]);

                            foreach ($rows as $r) {
                                fputcsv($handle, [
                                    $r->id,
                                    optional($r->user)->name,
                                    optional($r->user)->email,
                                    $r->status,
                                    optional($r->requested_at)?->toDateTimeString(),
                                    optional($r->approved_at)?->toDateTimeString(),
                                    optional($r->rejected_at)?->toDateTimeString(),
                                    $r->enrolled_at,
                                    $r->completed_at,
                                    $r->notes,
                                ]);
                            }
                            fclose($handle);
                        }, 200, [
                            'Content-Type' => 'text/csv',
                            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
                        ]);
                    }),
            ])

            /* Empty state CTA */
            ->emptyStateHeading('Belum ada pendaftar')
            ->emptyStateDescription('Tambahkan pendaftar baru atau tunggu permohonan masuk.')
            ->emptyStateActions([
                Action::make('emptyAddEnrollment')
                    ->label('Tambah Pendaftar')
                    ->icon('heroicon-o-user-plus')
                    ->color('primary')
                    ->action(fn () => $this->dispatch('open-modal', id: 'table-action.addEnrollment')), // buka action di header
            ])

            /* ================= COLUMNS ================= */
            ->columns([
                TextColumn::make('user.name')->label('Pengguna')->searchable()->wrap()
                    ->tooltip(fn (Enrollment $record) => "Email: {$record->user->email}"),
                TextColumn::make('status')->label('Status')->badge()
                    ->icon(fn (string $state) => match ($state) {
                        'requested' => 'heroicon-o-question-mark-circle',
                        'active' => 'heroicon-o-check-circle',
                        'completed' => 'heroicon-o-trophy',
                        'dropped' => 'heroicon-o-x-circle',
                        default => 'heroicon-o-ellipsis-horizontal-circle',
                    })
                    ->colors([
                        'warning' => 'requested',
                        'success' => 'active',
                        'primary' => 'completed',
                        'gray' => 'dropped',
                    ])
                    ->sortable(),
                TextColumn::make('requested_at')->label('Diminta')->since()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('approved_at')->label('Disetujui')->since()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('rejected_at')->label('Ditolak')->since()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('enrolled_at')->label('Mulai')->date('d M Y')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('completed_at')->label('Selesai')->date('d M Y')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('notes')->label('Catatan')->wrap()->limit(60)->tooltip(fn (?string $state) => $state ?: null)->toggleable(isToggledHiddenByDefault: true),
            ])

            /* ================= FILTERS ================= */
            ->filters([
                SelectFilter::make('status')->label('Status')->options([
                    'requested' => 'Requested',
                    'active' => 'Active',
                    'completed' => 'Completed',
                    'dropped' => 'Dropped',
                ])->indicator('Status'),

                Filter::make('date_range')->label('Rentang Tanggal')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('Dari')->native(false),
                        Forms\Components\DatePicker::make('until')->label('Sampai')->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'] ?? null, fn ($q, $d) => $q->whereDate('requested_at', '>=', $d))
                            ->when($data['until'] ?? null, fn ($q, $d) => $q->whereDate('requested_at', '<=', $d));
                    })
                    ->indicateUsing(function (array $data): array {
                        $i = [];
                        if ($data['from'] ?? null) {
                            $i[] = Tables\Filters\Indicator::make('Dari '.$data['from']);
                        }
                        if ($data['until'] ?? null) {
                            $i[] = Tables\Filters\Indicator::make('Sampai '.$data['until']);
                        }

                        return $i;
                    }),
            ])

            /* ================= ROW ACTIONS (tetap) ================= */
            ->actions([
                Action::make('approve')->label('Setujui')->icon('heroicon-o-check')->color('success')
                    ->visible(fn (Enrollment $record) => $this->canManage($record) && $record->status === 'requested')
                    ->form([Forms\Components\Textarea::make('notes')->label('Catatan (opsional)')->rows(2)])
                    ->requiresConfirmation()->modalHeading('Setujui permohonan?')
                    ->action(function (Enrollment $record, array $data) {
                        $record->update([
                            'status' => 'active',
                            'approved_at' => now(),
                            'rejected_at' => null,
                            'enrolled_at' => now()->toDateString(),
                            'notes' => $data['notes'] ?? $record->notes,
                        ]);
                        Notification::make()->title('Permohonan disetujui')->success()->send();
                    }),

                Action::make('reject')->label('Tolak')->icon('heroicon-o-x-mark')->color('danger')
                    ->visible(fn (Enrollment $record) => $this->canManage($record) && $record->status === 'requested')
                    ->form([Forms\Components\Textarea::make('reason')->label('Alasan penolakan')->rows(2)->required()])
                    ->requiresConfirmation()->modalHeading('Tolak permohonan?')
                    ->action(function (Enrollment $record, array $data) {
                        $record->update([
                            'status' => 'dropped',
                            'rejected_at' => now(),
                            'notes' => trim(($record->notes ?? '').PHP_EOL.'Rejected: '.$data['reason']),
                        ]);
                        Notification::make()->title('Permohonan ditolak')->warning()->send();
                    }),

                Action::make('cancelApproval')->label('Batalkan Penyetujuan')->icon('heroicon-o-arrow-uturn-left')->color('warning')
                    ->visible(fn (Enrollment $record) => $this->canManage($record) && $record->status === 'active')
                    ->form([Forms\Components\Textarea::make('notes')->label('Catatan (opsional)')->rows(2)])
                    ->requiresConfirmation()->modalHeading('Batalkan penyetujuan?')
                    ->action(function (Enrollment $record, array $data) {
                        $record->update([
                            'status' => 'requested',
                            'approved_at' => null,
                            'enrolled_at' => null,
                            'notes' => $data['notes'] ?? $record->notes,
                        ]);
                        Notification::make()->title('Penyetujuan dibatalkan')->warning()->send();
                    }),

                Action::make('complete')->label('Tandai Selesai')->icon('heroicon-o-trophy')->color('primary')
                    ->visible(fn (Enrollment $record) => $this->canManage($record) && $record->status === 'active')
                    ->requiresConfirmation()
                    ->action(function (Enrollment $record) {
                        $record->update([
                            'status' => 'completed',
                            'completed_at' => now()->toDateString(),
                        ]);
                        Notification::make()->title('Enrolmen selesai')->success()->send();
                    }),
            ])

            /* ================= BULK ACTIONS ================= */
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('bulkApprove')
                        ->label('Setujui (Bulk)')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (array $records) {
                            $ids = collect($records)->pluck('id');
                            $updated = Enrollment::whereIn('id', $ids)
                                ->where('status', 'requested')
                                ->update([
                                    'status' => 'active',
                                    'approved_at' => now(),
                                    'enrolled_at' => now()->toDateString(),
                                    'rejected_at' => null,
                                ]);
                            Notification::make()->title("{$updated} permohonan disetujui")->success()->send();
                        }),

                    Tables\Actions\BulkAction::make('bulkReject')
                        ->label('Tolak (Bulk)')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->form([Forms\Components\Textarea::make('reason')->label('Alasan')->rows(2)->required()])
                        ->requiresConfirmation()
                        ->action(function (array $records, array $data) {
                            $ids = collect($records)->pluck('id');
                            $updated = Enrollment::whereIn('id', $ids)
                                ->where('status', 'requested')
                                ->update([
                                    'status' => 'dropped',
                                    'rejected_at' => now(),
                                ]);
                            // Tambah catatan
                            Enrollment::whereIn('id', $ids)->update([
                                'notes' => DB::raw("concat(coalesce(notes,''), '\nRejected: ".addslashes($data['reason'])."')"),
                            ]);

                            Notification::make()->title("{$updated} permohonan ditolak")->warning()->send();
                        }),

                    Tables\Actions\BulkAction::make('bulkCancelApproval')
                        ->label('Batalkan Penyetujuan (Bulk)')
                        ->icon('heroicon-o-arrow-uturn-left')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->action(function (array $records) {
                            $ids = collect($records)->pluck('id');
                            $updated = Enrollment::whereIn('id', $ids)
                                ->where('status', 'active')
                                ->update([
                                    'status' => 'requested',
                                    'approved_at' => null,
                                    'enrolled_at' => null,
                                ]);
                            Notification::make()->title("{$updated} penyetujuan dibatalkan")->warning()->send();
                        }),

                    Tables\Actions\DeleteBulkAction::make()->visible(false),
                ]),
            ]);
    }

    /* ================= HELPERS ================= */

    protected function canManage(Enrollment $record): bool
    {
        $u = Auth::user();

        return $u && ($u->can('update_any_program') || $u->can('update', $record->program));
    }
}
