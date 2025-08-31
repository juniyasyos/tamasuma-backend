<?php

namespace App\Filament\Resources\ProgramResource;

use App\Filament\Resources\ProgramResource;
use Filament\Infolists\Components\Grid as InfoGrid;
use Filament\Infolists\Components\IconEntry as InfoIconEntry;
use Filament\Infolists\Components\Section as InfoSection;
use Filament\Infolists\Components\Tabs as InfoTabs;
use Filament\Infolists\Components\Tabs\Tab as InfoTab;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry as InfoViewEntry;
use Filament\Infolists\Infolist as FilamentInfolist;
use Filament\Infolists\Components\Actions\Action as InfoAction;

class Infolist extends ProgramResource
{
    public static function make(FilamentInfolist $infolist): FilamentInfolist
    {
        return $infolist->schema([
            InfoTabs::make('ProgramViewTabs')
                ->columnSpanFull()
                ->tabs([
                    /** ============ TAB: INFORMASI ============ */
                    InfoTab::make('Informasi')
                        ->icon('heroicon-o-information-circle')
                        ->schema([
                            /** HERO: Judul + Badge status + meta ringkas */
                            InfoSection::make()
                                ->schema([
                                    InfoGrid::make(['default' => 1, 'md' => 3])->schema([
                                        TextEntry::make('title')
                                            ->label('Judul Program')
                                            ->weight('bold')
                                            ->size('lg')
                                            ->columnSpan(2)
                                            ->hintAction(
                                                InfoAction::make('status')
                                                    ->label(fn($record) => $record->is_published ? 'Terpublikasi' : 'Draft')
                                                    ->icon(fn($record) => $record->is_published ? 'heroicon-o-check-circle' : 'heroicon-o-pencil')
                                                    ->color(fn($record) => $record->is_published ? 'success' : 'gray')
                                                    ->disabled() // hanya sebagai badge, tidak bisa diklik
                                            ),

                                        // Status + level ringkas dalam 1 kolom
                                        InfoGrid::make(1)->schema([
                                            TextEntry::make('is_published')
                                                ->label('Status')
                                                ->badge()
                                                ->formatStateUsing(fn(bool $state) => $state ? 'Published' : 'Draft')
                                                ->color(fn(bool $state) => $state ? 'success' : 'gray'),
                                            TextEntry::make('level')
                                                ->label('Level')
                                                ->badge()
                                                ->color(fn(?string $state) => match ($state) {
                                                    'pemula'   => 'success',
                                                    'menengah' => 'warning',
                                                    'lanjutan' => 'danger',
                                                    default    => 'gray',
                                                }),
                                        ]),
                                    ]),

                                    /** META: bidang, sertifikat, dibuat/diupdate */
                                    InfoGrid::make(['default' => 1, 'md' => 4])->schema([
                                        TextEntry::make('learningArea.name')
                                            ->label('Bidang')
                                            ->badge()
                                            ->icon('heroicon-o-academic-cap')
                                            ->color('gray'),

                                        InfoIconEntry::make('is_certified')
                                            ->label('Sertifikat')
                                            ->boolean()
                                            ->trueColor('success')
                                            ->falseColor('gray')
                                            ->trueIcon('heroicon-o-check-badge')
                                            ->falseIcon('heroicon-o-no-symbol'),

                                        TextEntry::make('created_at')
                                            ->label('Dibuat')
                                            ->since() // “2 jam lalu”
                                            ->icon('heroicon-o-clock'),

                                        TextEntry::make('updated_at')
                                            ->label('Diperbarui')
                                            ->since()
                                            ->icon('heroicon-o-arrow-path'),
                                    ])->columns(4),

                                    /** RINGKASAN ANGKA: enrol, materi, durasi (jika ada) */
                                    InfoGrid::make(['default' => 1, 'md' => 3])->schema([
                                        TextEntry::make('enrollments_count')
                                            ->label('Total Pendaftar')
                                            ->getStateUsing(fn($record) => $record->enrollments()->count())
                                            ->numeric()
                                            ->badge()
                                            ->color('info')
                                            ->icon('heroicon-o-user-group'),

                                        TextEntry::make('lessons_count')
                                            ->label('Jumlah Materi')
                                            ->getStateUsing(fn($record) => method_exists($record, 'lessons') ? $record->lessons()->count() : 0)
                                            ->numeric()
                                            ->badge()
                                            ->color('warning')
                                            ->icon('heroicon-o-rectangle-stack'),

                                        TextEntry::make('duration_display')
                                            ->label('Perkiraan Durasi')
                                            ->getStateUsing(fn($record) => $record->duration_minutes
                                                ? floor($record->duration_minutes / 60) . ' jam ' . ($record->duration_minutes % 60) . ' mnt'
                                                : '—')
                                            ->badge()
                                            ->color('success')
                                            ->icon('heroicon-o-clock'),
                                    ])->columns(3),

                                    /** DESKRIPSI: prosa rapi */
                                    TextEntry::make('description')
                                        ->label('Deskripsi')
                                        ->prose()        // typography cakep
                                        ->placeholder('Belum ada deskripsi.')
                                        ->columnSpanFull(),
                                ])
                                ->columns(1),


                            /** SUMBER & TAUTAN: tampilkan lebih enak dipindai */
                            InfoSection::make('Sumber & Platform')
                                ->schema([
                                    InfoGrid::make(['default' => 1, 'md' => 2])->schema([
                                        TextEntry::make('source')
                                            ->label('Sumber')
                                            ->badge()
                                            ->color(fn(?string $state) => $state === 'external' ? 'info' : 'gray')
                                            ->icon(fn(?string $state) => $state === 'external'
                                                ? 'heroicon-o-arrow-top-right-on-square'
                                                : 'heroicon-o-home'),

                                        TextEntry::make('platform')
                                            ->label('Platform')
                                            ->placeholder('-')
                                            ->badge()
                                            ->color('gray'),
                                    ]),

                                    // Link eksternal ditampilkan sebagai tautan jelas + copyable
                                    TextEntry::make('external_url')
                                        ->label('Tautan Eksternal')
                                        ->url(fn(?string $state) => $state ?: null, true)
                                        ->copyable()
                                        ->copyMessage('Link disalin')
                                        ->placeholder('—')
                                        ->icon('heroicon-o-link')
                                        ->columnSpanFull(),
                                ])
                                ->collapsible(), // bisa collapse jika ingin ringkas
                        ]),

                    /** ============ TAB: PENDAFTARAN ============ */
                    InfoTab::make('Pendaftaran')
                        ->icon('heroicon-o-user-group')
                        ->schema([
                            InfoViewEntry::make('enrollments')
                                ->label(false)
                                ->view('filament/programs/enrollments-inline')
                                ->getStateUsing(
                                    fn(\App\Models\Program $r) =>
                                    $r->enrollments()
                                        ->with('user')
                                        ->latest('enrolled_at')
                                        ->get()
                                ),
                        ]),
                ]),
        ]);
    }
}
