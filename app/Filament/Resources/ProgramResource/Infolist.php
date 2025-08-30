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

class Infolist extends ProgramResource
{
    public static function make(FilamentInfolist $infolist): FilamentInfolist
    {
        return $infolist->schema([
            InfoTabs::make('ProgramViewTabs')
                ->columnSpanFull()
                ->tabs([
                    InfoTab::make('Informasi')
                        ->icon('heroicon-o-information-circle')
                        ->schema([
                            InfoSection::make('Dasar')
                                ->schema([
                                    InfoGrid::make(['default' => 1, 'md' => 2])->schema([
                                        TextEntry::make('title')->label('Judul')->weight('semibold'),
                                        TextEntry::make('learningArea.name')->label('Bidang')->badge()->color('gray'),
                                        TextEntry::make('level')->label('Tingkat')->badge()->color(fn($s) => match ($s) {
                                            'pemula' => 'success',
                                            'menengah' => 'warning',
                                            'lanjutan' => 'danger',
                                            default => 'gray'
                                        }),
                                        InfoIconEntry::make('is_published')->label('Publikasi')->boolean()->trueColor('success')->falseColor('gray'),
                                        InfoIconEntry::make('is_certified')->label('Sertifikat')->boolean()->trueColor('success')->falseColor('gray'),
                                    ]),
                                    TextEntry::make('description')->label('Deskripsi')->prose()->columnSpanFull(),
                                ])->columns(1),

                            InfoSection::make('Sumber')
                                ->schema([
                                    InfoGrid::make(2)->schema([
                                        TextEntry::make('source')->label('Sumber')->badge()->color(fn($s) => $s === 'external' ? 'info' : 'gray'),
                                        TextEntry::make('platform')->label('Platform')->placeholder('-'),
                                        TextEntry::make('external_url')->label('Link')->url(true)->columnSpanFull(),
                                    ]),
                                ]),
                        ]),

                    InfoTab::make('Pendaftaran')
                        ->icon('heroicon-o-user-group')
                        ->schema([
                            InfoViewEntry::make('enrollments')
                                ->label(false)
                                ->view('filament/programs/enrollments-inline')
                                ->getStateUsing(fn(\App\Models\Program $r) => $r->enrollments()->with('user')->latest('enrolled_at')->get()),
                        ]),
                ]),
        ]);
    }
}
