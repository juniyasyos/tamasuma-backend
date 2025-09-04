<?php

namespace App\Filament\Resources\ProgramResource\RelationManagers;

use App\Filament\Resources\UserResource;
use App\Models\User;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TeachersRelationManager extends RelationManager
{
    protected static string $relationship = 'teachers';

    // Better tab title and record display attribute
    protected static ?string $title = 'Pengajar';
    protected static ?string $recordTitleAttribute = 'name';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Pengajar Program')
            ->description('Kelola daftar pengajar untuk program ini. Tambah atau lepas pengajar sesuai kebutuhan.')
            ->emptyStateHeading('Belum ada pengajar')
            ->emptyStateDescription('Tambahkan pengajar menggunakan tombol “Tambah Pengajar”.')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->icon('heroicon-o-envelope')
                    ->searchable(),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->label('Tambah Pengajar')
                    ->modalHeading('Tambah Pengajar')
                    ->modalDescription('Pilih satu atau lebih pengguna berperan “Pengajar” untuk ditambahkan ke program ini.')
                    ->preloadRecordSelect()
                    ->multiple()
                    // Search by name and email, and ensure query returns a Builder (not a Collection)
                    ->recordSelectSearchColumns(['name', 'email'])
                    ->recordSelectOptionsQuery(fn (Builder $query) => $query
                        ->role('Pengajar')
                        ->orderBy('name')
                    )
                    ->successNotificationTitle('Pengajar ditambahkan'),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('Lihat')
                    ->icon('heroicon-o-eye')
                    ->url(fn (User $record) => class_exists(UserResource::class)
                        ? UserResource::getUrl('view', ['record' => $record])
                        : null)
                    ->visible(fn () => class_exists(UserResource::class))
                    ->openUrlInNewTab(),

                Tables\Actions\DetachAction::make()
                    ->label('Lepas')
                    ->icon('heroicon-o-link-slash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Lepas Pengajar')
                    ->modalDescription('Pengajar akan dilepas dari program ini. Tindakan ini tidak menghapus akun pengguna.')
                    ->successNotificationTitle('Pengajar dilepas'),
            ])
            ->bulkActions([
                Tables\Actions\DetachBulkAction::make()
                    ->label('Lepas Terpilih')
                    ->requiresConfirmation()
                    ->modalHeading('Lepas Pengajar Terpilih')
                    ->modalDescription('Semua pengajar terpilih akan dilepas dari program.')
                    ->successNotificationTitle('Pengajar terpilih dilepas')
                    ->color('danger'),
            ]);
    }
}
