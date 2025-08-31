<?php

namespace App\Filament\Resources\ProgramResource\Pages;

use App\Filament\Resources\ProgramResource;
use App\Models\Enrollment;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\IconEntry;
use Illuminate\Support\Facades\Auth;

class ViewProgram extends ViewRecord
{
    protected static string $resource = ProgramResource::class;

    // Hide only Enrollments relation manager on the view page
    protected function getAllRelationManagers(): array
    {
        return array_filter(
            static::getResource()::getRelations(),
            fn ($manager) => $this->normalizeRelationManagerClass($manager) !== \App\Filament\Resources\ProgramResource\RelationManagers\EnrollmentsRelationManager::class,
        );
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('apply')
                ->label('Ajukan Ikut')
                ->icon('heroicon-o-user-plus')
                ->color('primary')
                ->authorize(fn() => (bool) (Auth::user()?->can('request_enrollment')))
                ->visible(function ($record) {
                    $userId = Auth::id();
                    if (! $userId) return false;
                    $notEnded = (! $record->ends_at || $record->ends_at->isFuture());
                    if (! $record->is_published || ! $notEnded) return false;
                    return ! \App\Models\Enrollment::where('user_id', $userId)
                        ->where('program_id', $record->id)
                        ->exists();
                })
                ->action(function () {
                    $user = Auth::user();
                    $record = $this->getRecord();
                    if (! $user) {
                        Notification::make()->title('Silakan masuk dulu.')->danger()->send();
                        return;
                    }
                    if ($record->ends_at && $record->ends_at->isPast()) {
                        Notification::make()->title('Program sudah selesai.')->warning()->send();
                        return;
                    }
                    $existing = Enrollment::where('user_id', $user->id)->where('program_id', $record->id)->first();
                    if ($existing) {
                        $msg = match ($existing->status) {
                            'requested' => 'Permohonan sudah diajukan. Menunggu persetujuan.',
                            'active' => 'Anda sudah terdaftar pada program ini.',
                            'completed' => 'Anda sudah menyelesaikan program ini.',
                            default => 'Enrolmen sudah ada.',
                        };
                        Notification::make()->title($msg)->info()->send();
                        return;
                    }

                    Enrollment::create([
                        'user_id' => $user->id,
                        'program_id' => $record->id,
                        'status' => 'requested',
                        'requested_at' => now(),
                    ]);

                    Notification::make()
                        ->title('Permohonan dikirim')
                        ->body('Permohonan mengikuti program telah dikirim. Menunggu persetujuan admin.')
                        ->success()
                        ->send();
                }),

            \Filament\Actions\EditAction::make()->label('Edit'),
        ];
    }

    // Remove custom infolist to use ProgramResource::infolist (which uses tabs)
}
