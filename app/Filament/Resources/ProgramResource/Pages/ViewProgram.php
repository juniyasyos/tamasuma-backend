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

    protected function getHeaderActions(): array
    {
        return [
            Action::make('apply')
                ->label('Ajukan Ikut')
                ->icon('heroicon-o-user-plus')
                ->color('primary')
                ->authorize(fn() => (bool) (Auth::user()?->can('request_enrollment')))
                ->visible(fn($record) => $record->is_published && (! $record->ends_at || $record->ends_at->isFuture()))
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

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Ringkasan')
                    ->description('Detail singkat program pembelajaran.')
                    ->schema([
                        Grid::make()
                            ->schema([
                                TextEntry::make('title')
                                    ->label('Judul')
                                    ->weight('semibold')
                                    ->size('lg')
                                    ->columnSpanFull(),

                                TextEntry::make('learningArea.name')
                                    ->label('Bidang')
                                    ->badge()
                                    ->color('gray'),

                                TextEntry::make('level')
                                    ->label('Tingkat')
                                    ->badge()
                                    ->color(fn($state) => match ($state) {
                                        'pemula' => 'success',
                                        'menengah' => 'warning',
                                        'lanjutan' => 'danger',
                                        default => 'gray',
                                    })
                                    ->formatStateUsing(fn($state) => ucfirst($state ?? '-')),

                        TextEntry::make('slug')
                            ->label('Slug')
                            ->copyable()
                            ->icon('heroicon-m-link'),

                        TextEntry::make('starts_at')
                            ->label('Mulai')
                            ->date('d M Y')
                            ->placeholder('-'),

                        TextEntry::make('ends_at')
                            ->label('Selesai')
                            ->date('d M Y')
                            ->placeholder('-'),

                        IconEntry::make('is_published')
                            ->label('Status')
                            ->boolean()
                                    ->trueIcon('heroicon-m-check-circle')
                                    ->falseIcon('heroicon-m-x-circle')
                                    ->trueColor('success')
                                    ->falseColor('gray'),

                                IconEntry::make('is_certified')
                                    ->label('Sertifikat')
                                    ->boolean()
                                    ->trueIcon('heroicon-m-check-badge')
                                    ->falseIcon('heroicon-m-x-mark')
                                    ->trueColor('success')
                                    ->falseColor('gray'),

                                TextEntry::make('created_at')
                                    ->label('Dibuat')
                                    ->since()
                                    ->icon('heroicon-m-calendar'),

                                TextEntry::make('updated_at')
                                    ->label('Diubah')
                                    ->since()
                                    ->icon('heroicon-m-arrow-path'),
                            ])
                            ->columns([
                                'default' => 1,
                                'md' => 2,
                                'xl' => 3,
                            ]),
                    ])
                    ->collapsible(),

                Section::make('Deskripsi')
                    ->schema([
                        TextEntry::make('description')
                            ->placeholder('Belum ada deskripsi.')
                            ->prose() 
                            ->columnSpanFull(),
                    ])
                    ->collapsed(false),

                Section::make('Sumber Program')
                    ->schema([
                        TextEntry::make('source')
                            ->label('Sumber')
                            ->badge()
                            ->color(fn($state) => $state === 'external' ? 'info' : 'gray')
                            ->formatStateUsing(fn($state) => ucfirst($state ?? '-')),

                        TextEntry::make('platform')
                            ->label('Platform')
                            ->placeholder('-'),

                        TextEntry::make('external_url')
                            ->label('Link Program')
                            ->url(fn($state) => $state ?: null, shouldOpenInNewTab: true)
                            ->icon('heroicon-m-arrow-top-right-on-square')
                            ->placeholder('-')
                            ->visible(fn($record) => $record->source === 'external'),
                    ])
                    ->columns([
                        'default' => 1,
                        'md' => 2,
                    ])
                    ->collapsed(true),
            ])
            ->columns(1);
    }
}
