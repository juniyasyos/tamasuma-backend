<?php

namespace App\Filament\Resources\ProgramResource;

use App\Models\Program;
use Filament\Forms;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ReplicateAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ProgramResourceTable
{
    public static function make(Table $table): Table
    {
        return $table
            ->heading('Daftar Program')
            ->description('Kelola program pembelajaran internal maupun eksternal.')
            ->recordUrl(fn (Model $record) => \App\Filament\Resources\ProgramResource::getUrl('view', ['record' => $record]))
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
                    ->color(fn (string $state) => match ($state) {
                        'pemula' => 'success',
                        'menengah' => 'warning',
                        'lanjutan' => 'danger',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn (?string $state) => $state ? Str::title($state) : '-'),

                TextColumn::make('source')
                    ->label('Sumber')
                    ->badge()
                    ->sortable()
                    ->color(fn (string $state) => match ($state) {
                        'internal' => 'gray',
                        'external' => 'info',
                        default => 'gray',
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

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
                        'pemula' => 'Pemula',
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
                    ->indicateUsing(fn ($state) => match ($state) {
                        true => 'Publik',
                        false => 'Draft',
                        default => null,
                    }),

                TernaryFilter::make('is_certified')
                    ->label('Sertifikat')
                    ->placeholder('Semua')
                    ->trueLabel('Dengan Sertifikat')
                    ->falseLabel('Tanpa Sertifikat'),

                Filter::make('created_at')
                    ->label('Rentang Tanggal')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('Dari'),
                        Forms\Components\DatePicker::make('until')->label('Sampai'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'] ?? null, fn ($q, $date) => $q->whereDate('created_at', '>=', $date))
                            ->when($data['until'] ?? null, fn ($q, $date) => $q->whereDate('created_at', '<=', $date));
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['from'] ?? null) {
                            $indicators[] = Tables\Filters\Indicator::make('Dari '.$data['from']);
                        }
                        if ($data['until'] ?? null) {
                            $indicators[] = Tables\Filters\Indicator::make('Sampai '.$data['until']);
                        }

                        return $indicators;
                    }),
            ])
            ->actions([
                // Quick action: Detail (link ke page, bukan modal)
                ViewAction::make('detail')
                    ->label('Detail')
                    ->icon('heroicon-o-eye')
                    ->url(fn (Program $record) => static::getUrl('view', ['record' => $record]))
                    ->openUrlInNewTab(false)
                    ->iconButton()
                    ->tooltip('Lihat detail'),

                // Quick action: Edit (pakai slide-over biar tetap terasa ringan)
                EditAction::make('edit')
                    ->label('Edit')
                    ->icon('heroicon-o-pencil-square')
                    ->slideOver()
                    ->iconButton()
                    ->tooltip('Edit'),

                // Lainnya: dikelompokkan agar tetap clean di layar kecil
                ActionGroup::make([
                    // Publish / Unpublish kontekstual
                    Action::make('publish')
                        ->label('Publikasikan')
                        ->icon('heroicon-o-eye')
                        ->color('success')
                        ->visible(fn (Program $record) => ! $record->is_published)
                        ->requiresConfirmation()
                        ->action(fn (Program $record) => $record->update(['is_published' => true]))
                        ->successNotificationTitle('Program dipublikasikan.'),

                    Action::make('unpublish')
                        ->label('Jadikan Draft')
                        ->icon('heroicon-o-eye-slash')
                        ->color('gray')
                        ->visible(fn (Program $record) => $record->is_published)
                        ->requiresConfirmation()
                        ->action(fn (Program $record) => $record->update(['is_published' => false]))
                        ->successNotificationTitle('Program diubah menjadi draft.'),

                    // Buka link eksternal (hanya jika sumber eksternal & ada URL)
                    Action::make('openExternal')
                        ->label('Buka Link Program')
                        ->icon('heroicon-o-arrow-top-right-on-square')
                        ->url(fn (Program $record) => $record->external_url ?: '#', true)
                        ->visible(fn (Program $record) => $record->source === 'external' && filled($record->external_url)),

                    // Duplikasi aman: set judul & slug baru, default jadi draft
                    ReplicateAction::make('duplicate')
                        ->label('Duplikat')
                        ->icon('heroicon-o-document-duplicate')
                        ->mutateRecordDataUsing(function (array $data, Program $record): array {
                            $newTitle = $record->title.' (Copy)';
                            $data['title'] = $newTitle;
                            $data['slug'] = Str::slug($record->slug.'-copy-'.Str::random(4));
                            $data['is_published'] = false;

                            return $data;
                        })
                        ->successNotificationTitle('Program diduplikasi (status: draft).'),
                ])
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
