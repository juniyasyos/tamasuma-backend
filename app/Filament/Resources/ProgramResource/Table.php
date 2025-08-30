<?php

namespace App\Filament\Resources\ProgramResource;

use App\Filament\Resources\ProgramResource;
use App\Models\Program;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Actions\{Action, ActionGroup, ViewAction, EditAction, ReplicateAction};
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table as FilamentTable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Filament\Notifications\Notification;
use App\Models\Enrollment;

    class Table extends ProgramResource
    {
        public static function make(FilamentTable $table): FilamentTable
        {
        // Common authorization closures for cleaner reuse
        $canUpdate = fn(Program $record): bool => (bool) (Auth::user()?->can('update', $record));
        $canReplicate = fn(Program $record): bool => (bool) (Auth::user()?->can('replicate', $record));
        $canPublish = fn(Program $record): bool => (bool) (Auth::user()?->can('publish_program'));
        $canUnpublish = fn(Program $record): bool => (bool) (Auth::user()?->can('unpublish_program'));

        return $table
            ->modifyQueryUsing(function (Builder $query): Builder {
                $user = Auth::user();
                if (! $user || ! $user->can('view_unpublished_program')) {
                    $query->where('is_published', true);
                }
                return $query;
            })
            ->heading('Daftar Program')
            ->description('Kelola program pembelajaran internal maupun eksternal.')
            ->recordUrl(fn(Model $record) => ProgramResource::getUrl('view', ['record' => $record]))
            ->columns([
                TextColumn::make('title')
                    ->label('Judul')
                    ->searchable(isIndividual: true)
                    ->sortable()
                    ->wrap()
                    ->weight(FontWeight::Bold)
                    ->limit(60),

                TextColumn::make('learningArea.name')
                    ->label('Bidang')
                    ->sortable()
                    ->badge()
                    ->color('gray')
                    ->searchable(isIndividual: true),

                TextColumn::make('level')
                    ->label('Tingkat')
                    ->badge()
                    ->sortable()
                    ->color(fn(string $state) => match ($state) {
                        'pemula' => 'success',
                        'menengah' => 'warning',
                        'lanjutan' => 'danger',
                        default   => 'gray',
                    })
                    ->formatStateUsing(fn(?string $state) => $state ? Str::title($state) : '-'),

                TextColumn::make('source')
                    ->label('Sumber')
                    ->badge()
                    ->sortable()
                    ->color(fn(string $state) => match ($state) {
                        'internal' => 'gray',
                        'external' => 'info',
                        default     => 'gray',
                    }),

                TextColumn::make('platform')
                    ->label('Platform')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable(isIndividual: true),

                IconColumn::make('is_certified')
                    ->label('Sertifikat')
                    ->boolean()
                    ->trueIcon('heroicon-m-check-circle')
                    ->falseIcon('heroicon-m-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->sortable(),

                IconColumn::make('is_published')
                    ->label('Terbitkan')
                    ->boolean()
                    ->trueIcon('heroicon-m-check-circle')
                    ->falseIcon('heroicon-m-x-circle')
                    ->trueColor('success')
                    ->falseColor('gray')
                    ->sortable(),

                TextColumn::make('enrollments_count')
                    ->label('Enrolmen')
                    ->counts('enrollments')
                    ->badge()
                    ->color(fn($state) => $state > 0 ? 'success' : 'gray')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->alignRight(),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('starts_at')
                    ->label('Mulai')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('ends_at')
                    ->label('Selesai')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->defaultSort('created_at', 'desc')
            ->groups([
                Tables\Grouping\Group::make('learningArea.name')->label('Kelompok Bidang')->collapsible(),
                Tables\Grouping\Group::make('level')->label('Kelompok Level')->collapsible(),
            ])
            ->filters([
                SelectFilter::make('learning_area_id')
                    ->label('Bidang')
                    ->relationship('learningArea', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('level')
                    ->label('Tingkat')
                    ->options([
                        'pemula'   => 'Pemula',
                        'menengah' => 'Menengah',
                        'lanjutan' => 'Lanjutan',
                    ]),

                SelectFilter::make('source')
                    ->label('Sumber')
                    ->options([
                        'internal' => 'Internal',
                        'external' => 'Eksternal',
                    ]),

                TernaryFilter::make('is_published')
                    ->label('Status Publikasi')
                    ->placeholder('Semua')
                    ->trueLabel('Hanya Publik')
                    ->falseLabel('Hanya Draft')
                    ->indicateUsing(fn($state) => match ($state) {
                        true  => 'Publik',
                        false => 'Draft',
                        default => null,
                    }),

                TernaryFilter::make('is_certified')
                    ->label('Sertifikat')
                    ->placeholder('Semua')
                    ->trueLabel('Dengan Sertifikat')
                    ->falseLabel('Tanpa Sertifikat'),

                // Filter::make('created_at')
                //     ->label('Rentang Tanggal')
                //     ->form([
                //         Forms\Components\DatePicker::make('from')->label('Dari'),
                //         Forms\Components\DatePicker::make('until')->label('Sampai'),
                //     ])
                //     ->query(function (Builder $query, array $data): Builder {
                //         return $query
                //             ->when($data['from'] ?? null, fn($q, $date) => $q->whereDate('created_at', '>=', $date))
                //             ->when($data['until'] ?? null, fn($q, $date) => $q->whereDate('created_at', '<=', $date));
                //     })
                //     ->indicateUsing(function (array $data): array {
                //         $indicators = [];
                //         if ($data['from'] ?? null) $indicators[] = Tables\Filters\Indicator::make('Dari ' . $data['from']);
                //         if ($data['until'] ?? null) $indicators[] = Tables\Filters\Indicator::make('Sampai ' . $data['until']);
                //         return $indicators;
                //     }),
            ])
            ->actions([
                ViewAction::make('detail')
                    ->label('Detail')
                    ->icon('heroicon-o-eye')
                    ->url(fn(Program $record) => ProgramResource::getUrl('view', ['record' => $record]))
                    ->openUrlInNewTab(false)
                    ->iconButton()
                    ->tooltip('Lihat detail'),

                Action::make('apply')
                    ->label('Ajukan Ikut')
                    ->icon('heroicon-o-user-plus')
                    ->color('primary')
                    ->authorize(fn() => (bool) (Auth::user()?->can('request_enrollment')))
                    ->visible(fn(Program $record) => $record->is_published && (! $record->ends_at || $record->ends_at->isFuture()))
                    ->requiresConfirmation()
                    ->action(function (Program $record) {
                        $user = Auth::user();
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

                EditAction::make('edit')
                    ->label('Edit')
                    ->icon('heroicon-o-pencil-square')
                    ->slideOver()
                    ->authorize($canUpdate)
                    ->iconButton()
                    ->tooltip('Edit'),

                ActionGroup::make([
                    Action::make('publish')
                        ->label('Publikasikan')
                        ->icon('heroicon-o-eye')
                        ->color('success')
                        ->visible(fn(Program $record) => !$record->is_published)
                        ->authorize($canPublish)
                        ->requiresConfirmation()
                        ->action(fn(Program $record) => $record->update(['is_published' => true]))
                        ->successNotificationTitle('Program dipublikasikan.'),

                    Action::make('unpublish')
                        ->label('Jadikan Draft')
                        ->icon('heroicon-o-eye-slash')
                        ->color('gray')
                        ->visible(fn(Program $record) => $record->is_published)
                        ->authorize($canUnpublish)
                        ->requiresConfirmation()
                        ->action(fn(Program $record) => $record->update(['is_published' => false]))
                        ->successNotificationTitle('Program diubah menjadi draft.'),

                    Action::make('openExternal')
                        ->label('Buka Link Program')
                        ->icon('heroicon-o-arrow-top-right-on-square')
                        ->url(fn(Program $record) => $record->external_url ?: '#', true)
                        ->visible(fn(Program $record) => $record->source === 'external' && filled($record->external_url)),

                    ReplicateAction::make('duplicate')
                        ->label('Duplikat')
                        ->icon('heroicon-o-document-duplicate')
                        ->authorize($canReplicate)
                        ->mutateRecordDataUsing(function (array $data, Program $record): array {
                            $newTitle = $record->title . ' (Copy)';
                            $data['title'] = $newTitle;
                            $data['slug'] = Str::slug($record->slug . '-copy-' . Str::random(4));
                            $data['is_published'] = false;
                            return $data;
                        })
                        ->successNotificationTitle('Program diduplikasi (status: draft).'),
                ])
                    ->visible(function (Program $record) use ($canUpdate, $canReplicate, $canPublish, $canUnpublish): bool {
                        // Show group only if any action inside is visible/authorized
                        return $canUpdate($record)
                            || $canPublish($record)
                            || $canUnpublish($record)
                            || ($record->source === 'external' && filled($record->external_url))
                            || $canReplicate($record);
                    })
                    ->label('Lainnya')
                    ->icon('heroicon-m-ellipsis-horizontal')
                    ->button()
                    ->size('sm'),
            ])
            ->bulkActions([])
            ->emptyStateHeading('Belum ada program')
            ->emptyStateDescription('Buat program pertama kamu untuk mulai mengelola konten pembelajaran.')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()->label('Buat Program'),
            ])
            ->paginated([10, 25, 50])
            ->deferLoading();
    }
}
