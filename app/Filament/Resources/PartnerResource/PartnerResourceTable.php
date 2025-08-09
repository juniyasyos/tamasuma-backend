<?php

namespace App\Filament\Resources\PartnerResource;

use App\Models\Partner;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\{TextColumn, IconColumn, ImageColumn, Layout\Split, Layout\Stack};
use Filament\Tables\Actions\{Action, ActionGroup, ViewAction, EditAction, DeleteAction};
use Filament\Tables\Filters\{TernaryFilter, Filter};
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PartnerResourceTable
{
    public static function make(Table $table): Table
    {
        return $table
            // klik baris → halaman detail (infolist), bukan modal
            ->recordUrl(fn (Model $record) => \App\Filament\Resources\PartnerResource::getUrl('view', ['record' => $record]))
            ->columns([
                Split::make([
                    ImageColumn::make('logo_path')
                        ->label(' ')
                        ->disk('public')
                        ->circular()
                        ->height(40)
                        ->width(40)
                        ->toggleable(false)
                        ->placeholder(fn (Partner $r) => null),

                    TextColumn::make('name')
                        ->label('Nama')
                        ->searchable()
                        ->sortable()
                        ->weight(FontWeight::Bold)
                        ->description(fn (Partner $r) => $r->slug, position: 'below'),

                    Stack::make([
                        TextColumn::make('website_url')
                            ->label('Website')
                            ->url(fn ($state) => $state ?: null, true)
                            ->copyable()
                            ->copyMessage('URL disalin')
                            ->copyMessageDuration(1200)
                            ->limit(40),

                        IconColumn::make('is_visible')
                            ->label('Tampil')
                            ->boolean()
                            ->trueIcon('heroicon-m-check-circle')
                            ->falseIcon('heroicon-m-eye-slash')
                            ->trueColor('success')
                            ->falseColor('gray'),
                    ])->space(1)->visibleFrom('md'),
                ])->from('md'),

                // tampilan ringkas untuk mobile
                Stack::make([
                    TextColumn::make('website_url')
                        ->label('Website')
                        ->url(fn ($state) => $state ?: null, true)
                        ->limit(28),
                    IconColumn::make('is_visible')->label('Tampil')->boolean(),
                ])->visibleFrom('sm')->hiddenFrom('md'),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                TernaryFilter::make('is_visible')
                    ->label('Status Tampil')
                    ->placeholder('Semua')
                    ->trueLabel('Tampil')
                    ->falseLabel('Disembunyikan'),

                Filter::make('has_logo')
                    ->label('Hanya yang punya logo')
                    ->query(fn (Builder $q) => $q->whereNotNull('logo_path')->where('logo_path', '!=', ''))
                    // ->indicateUsing([Tables\Filters\Indicator::make('Dengan logo')]),
            ])
            ->actions([
                // aksi cepat (ikon) → hemat ruang
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
                    ->tooltip('Edit mitra'),

                ActionGroup::make([
                    Action::make('openWebsite')
                        ->label('Buka Website')
                        ->icon('heroicon-o-arrow-top-right-on-square')
                        ->url(fn (Partner $r) => $r->website_url ?: '#', true)
                        ->visible(fn (Partner $r) => filled($r->website_url)),

                    Action::make('toggleVisibility')
                        ->label(fn (Partner $r) => $r->is_visible ? 'Sembunyikan' : 'Tampilkan')
                        ->icon(fn (Partner $r) => $r->is_visible ? 'heroicon-o-eye-slash' : 'heroicon-o-eye')
                        ->color(fn (Partner $r) => $r->is_visible ? 'gray' : 'success')
                        ->requiresConfirmation()
                        ->action(function (Partner $r) {
                            $r->update(['is_visible' => !$r->is_visible]);
                        })
                        ->successNotificationTitle('Status tampilan diperbarui.'),

                    Action::make('duplicate')
                        ->label('Duplikat')
                        ->icon('heroicon-o-document-duplicate')
                        ->action(function (Partner $r) {
                            $new = $r->replicate(['slug']);
                            $new->name = $r->name.' (Copy)';
                            $new->slug = Str::slug($r->slug.'-copy-'.Str::random(4));
                            $new->is_visible = false;
                            $new->save();
                        })
                        ->successNotificationTitle('Mitra berhasil diduplikasi (disembunyikan).'),

                    DeleteAction::make(),
                ])
                ->label('Lainnya')
                ->icon('heroicon-m-ellipsis-horizontal')
                ->button()
                ->size('sm'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('bulkShow')
                        ->label('Tampilkan Terpilih')
                        ->icon('heroicon-m-eye')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->update(['is_visible' => true]))
                        ->successNotificationTitle('Mitra terpilih ditampilkan.'),

                    Tables\Actions\BulkAction::make('bulkHide')
                        ->label('Sembunyikan Terpilih')
                        ->icon('heroicon-m-eye-slash')
                        ->color('gray')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->update(['is_visible' => false]))
                        ->successNotificationTitle('Mitra terpilih disembunyikan.'),

                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Belum ada mitra')
            ->emptyStateDescription('Tambahkan mitra untuk mulai menampilkan kolaborasi.')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()->label('Tambah Mitra')->slideOver(),
            ])
            ->paginated([10, 25, 50])
            ->deferLoading();
    }
}

