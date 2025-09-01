<?php

namespace App\Filament\Resources\AchievementResource;

use App\Filament\Resources\AchievementResource as Resource;
use App\Models\Achievement;
use Filament\Forms;
use Filament\Tables;
use Filament\Tables\Table as FilamentTable;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\ReplicateAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;

class Table extends Resource
{
    /**
     * Configure the table for the Achievement resource.
     */
    public static function make(FilamentTable $table): FilamentTable
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                $query->orderByDesc('is_featured')->orderByDesc('achieved_at');
            })
            ->contentGrid([
                'default' => 1,
                'sm' => 1,
                'md' => 2,
                'lg' => 3,
                'xl' => 4,
            ])
            ->columns([
                ImageColumn::make('proof_image')
                    ->disk('public')
                    ->grow(false)
                    ->toggleable(),
                TextColumn::make('user.name')->label('Pengguna')
                    ->visible(fn() => ($u = Auth::user()) && method_exists($u, 'hasRole') && ($u->hasRole('super_admin') || $u->hasRole('Admin'))),
                TextColumn::make('title')->label('Judul')->wrap()->searchable()->sortable(),
                TextColumn::make('category')->badge()->color('info')->sortable(),
                TextColumn::make('issuer')->label('Penyelenggara')->toggleable(),
                TextColumn::make('achieved_at')->label('Tanggal')->date('d M Y')->sortable(),
                TextColumn::make('visibility')->badge()->color(fn($state) => match($state){
                    'public' => 'success', 'private' => 'gray', 'unlisted' => 'warning', default => 'gray'
                }),
                TextColumn::make('created_via')
                    ->label('Dibuat')
                    ->badge()
                    ->color(fn($state) => match($state) {
                        'self' => 'gray',
                        'admin' => 'info',
                        'auto' => 'success',
                        default => 'gray',
                    })
                    ->toggleable(),
                ToggleColumn::make('is_featured')
                    ->label('Unggulan')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')->label('Kategori')->options([
                    'certificate' => 'Sertifikat',
                    'award' => 'Penghargaan',
                    'competition' => 'Kompetisi',
                    'training' => 'Pelatihan',
                    'other' => 'Lainnya',
                ]),
                Tables\Filters\TernaryFilter::make('is_featured')->label('Unggulan'),
                Tables\Filters\SelectFilter::make('visibility')
                    ->label('Keterlihatan')
                    ->options([
                        'public' => 'Publik',
                        'private' => 'Privat',
                        'unlisted' => 'Unlisted',
                    ]),
                Tables\Filters\SelectFilter::make('created_via')
                    ->label('Dibuat via')
                    ->options([
                        'self' => 'Self',
                        'admin' => 'Admin',
                        'auto' => 'Auto',
                    ]),
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('Pemilik')
                    ->relationship('user', 'name')
                    ->visible(fn() => ($u = Auth::user()) && method_exists($u, 'hasRole') && ($u->hasRole('super_admin') || $u->hasRole('Admin'))),
                Tables\Filters\Filter::make('date_range')->form([
                    Forms\Components\DatePicker::make('from')->label('Dari')->native(false),
                    Forms\Components\DatePicker::make('until')->label('Sampai')->native(false),
                ])->query(function($q, $data){
                    return $q
                        ->when(($data['from'] ?? null), fn($qq,$d)=>$qq->whereDate('achieved_at','>=',$d))
                        ->when(($data['until'] ?? null), fn($qq,$d)=>$qq->whereDate('achieved_at','<=',$d));
                }),
                Tables\Filters\Filter::make('tag')->form([
                    Forms\Components\TextInput::make('tag')->label('Tag'),
                ])->query(function ($q, $data) {
                    $tag = trim((string) ($data['tag'] ?? ''));
                    if ($tag !== '') {
                        $q->whereJsonContains('tags', $tag);
                    }
                    return $q;
                }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                ReplicateAction::make(),
                Action::make('set_visibility')
                    ->label('Atur Visibilitas')
                    ->icon('heroicon-m-eye')
                    ->form([
                        Forms\Components\Select::make('visibility')
                            ->label('Keterlihatan')
                            ->options([
                                'public' => 'Publik',
                                'private' => 'Privat',
                                'unlisted' => 'Unlisted',
                            ])
                            ->required(),
                    ])
                    ->action(function (Achievement $record, array $data) {
                        $record->update(['visibility' => $data['visibility']]);
                    }),
                Action::make('view_portfolio')
                    ->label('Portofolio Publik')
                    ->icon('heroicon-m-arrow-top-right-on-square')
                    ->url(fn (Achievement $record) => route('portfolio.show', ['user' => $record->user_id]))
                    ->openUrlInNewTab(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    ExportBulkAction::make(),
                    BulkAction::make('bulk_set_visibility')
                        ->label('Atur Visibilitas')
                        ->icon('heroicon-m-eye')
                        ->form([
                            Forms\Components\Select::make('visibility')
                                ->label('Keterlihatan')
                                ->options([
                                    'public' => 'Publik',
                                    'private' => 'Privat',
                                    'unlisted' => 'Unlisted',
                                ])
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data) {
                            foreach ($records as $rec) {
                                $rec->update(['visibility' => $data['visibility']]);
                            }
                        }),
                    BulkAction::make('bulk_set_category')
                        ->label('Atur Kategori')
                        ->icon('heroicon-m-squares-2x2')
                        ->form([
                            Forms\Components\Select::make('category')
                                ->label('Kategori')
                                ->options([
                                    'certificate' => 'Sertifikat',
                                    'award' => 'Penghargaan',
                                    'competition' => 'Kompetisi',
                                    'training' => 'Pelatihan',
                                    'other' => 'Lainnya',
                                ])
                                ->required(),
                        ])
                        ->action(function (Collection $records, array $data) {
                            foreach ($records as $rec) {
                                $rec->update(['category' => $data['category']]);
                            }
                        }),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

