<?php

namespace App\Filament\Resources\ProgramResource\RelationManagers;

use App\Models\Enrollment;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;

class EnrollmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'enrollments';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Permohonan & Enrolmen')
            ->description('Kelola permohonan dan status keikutsertaan program.')
            ->columns([
                TextColumn::make('user.name')->label('Pengguna')->searchable()->wrap(),
                TextColumn::make('status')
                    ->badge()
                    ->label('Status')
                    ->colors([
                        'warning' => 'requested',
                        'success' => 'active',
                        'primary' => 'completed',
                        'gray' => 'dropped',
                    ]),
                TextColumn::make('requested_at')->label('Diminta')->since()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('approved_at')->label('Disetujui')->since()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('rejected_at')->label('Ditolak')->since()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('enrolled_at')->label('Mulai')->date('d M Y')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('completed_at')->label('Selesai')->date('d M Y')->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('notes')->label('Catatan')->wrap()->limit(60)->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Action::make('approve')
                    ->label('Setujui')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(function (Enrollment $record): bool {
                        $u = Auth::user();
                        return $record->status === 'requested' && $u && ($u->can('update_any_program') || $u->can('update', $record->program));
                    })
                    ->requiresConfirmation()
                    ->action(function (Enrollment $record) {
                        $record->update([
                            'status' => 'active',
                            'approved_at' => now(),
                            'rejected_at' => null,
                            'enrolled_at' => now()->toDateString(),
                        ]);
                        Notification::make()->title('Permohonan disetujui')->success()->send();
                    }),

                Action::make('reject')
                    ->label('Tolak')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->visible(function (Enrollment $record): bool {
                        $u = Auth::user();
                        return $record->status === 'requested' && $u && ($u->can('update_any_program') || $u->can('update', $record->program));
                    })
                    ->requiresConfirmation()
                    ->action(function (Enrollment $record) {
                        $record->update([
                            'status' => 'dropped',
                            'rejected_at' => now(),
                        ]);
                        Notification::make()->title('Permohonan ditolak')->warning()->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->visible(false),
                ]),
            ]);
    }
}
