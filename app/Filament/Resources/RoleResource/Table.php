<?php

namespace App\Filament\Resources\RoleResource;

use App\Filament\Resources\RoleResource;
use BezhanSalleh\FilamentShield\Support\Utils;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table as FilamentTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class Table extends RoleResource
{
    public static function make(FilamentTable $table): FilamentTable
    {
        return $table
            ->heading('Daftar Role')
            ->description('Kelola role & perizinannya.')
            ->emptyStateHeading('Belum ada role')
            ->emptyStateDescription('Buat role pertama Anda atau gunakan template.')
            ->headerActions([
                Action::make('quickCreateTemplate')
                    ->label('Buat dari Template')
                    ->icon('heroicon-o-sparkles')
                    ->color('primary')
                    ->form([
                        Forms\Components\Select::make('template')
                            ->label('Pilih Template')
                            ->options([
                                'super_admin' => 'Super Admin',
                                'admin'       => 'Admin',
                                'pengajar'    => 'Pengajar',
                                'pelajar'     => 'Pelajar',
                            ])
                            ->required(),
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Role')
                            ->placeholder('Contoh: Admin')
                            ->required(),
                        Forms\Components\TextInput::make('guard_name')
                            ->label('Guard')
                            ->default(Utils::getFilamentAuthGuard()),
                    ])
                    ->action(function (array $data) {
                        $roleModel = Utils::getRoleModel();
                        /** @var \Spatie\Permission\Models\Role $role */
                        $role = $roleModel::create([
                            'name'       => $data['name'],
                            'guard_name' => $data['guard_name'] ?? Utils::getFilamentAuthGuard(),
                        ]);

                        // Grant baseline permissions sederhana per template (bisa dikembangkan)
                        $permModel = Utils::getPermissionModel();
                        $prefixes  = ['view', 'view_any', 'create', 'update', 'delete', 'delete_any'];
                        foreach ($prefixes as $p) {
                            $perm = $permModel::firstOrCreate([
                                'name'       => "{$p} {$data['template']}",
                                'guard_name' => $role->guard_name,
                            ]);
                            $role->givePermissionTo($perm);
                        }

                        \Filament\Notifications\Notification::make()
                            ->title("Role '{$role->name}' dibuat dari template.")
                            ->success()
                            ->send();
                    }),

                Action::make('exportPermissions')
                    ->label('Export Permissions (JSON)')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('gray')
                    ->action(function () {
                        $roles = static::getModel()::with('permissions:id,name')->get(['id', 'name', 'guard_name']);
                        $payload = $roles->map(fn ($r) => [
                            'id'          => $r->id,
                            'name'        => $r->name,
                            'guard_name'  => $r->guard_name,
                            'permissions' => $r->permissions->pluck('name')->values(),
                        ])->values()->toJson(JSON_PRETTY_PRINT);

                        return response($payload, 200, [
                            'Content-Type'        => 'application/json',
                            'Content-Disposition' => 'attachment; filename="roles_permissions.json"',
                        ]);
                    }),
            ])
            ->columns([
                TextColumn::make('name')
                    ->weight('font-medium')
                    ->label(__('filament-shield::filament-shield.column.name'))
                    ->formatStateUsing(fn ($state): string => Str::headline($state))
                    ->description(fn ($record) => "Guard: {$record->guard_name}")
                    ->searchable(),

                TextColumn::make('guard_name')
                    ->badge()
                    ->color('warning')
                    ->label(__('filament-shield::filament-shield.column.guard_name')),

                TextColumn::make('team_name')
                    ->label(__('filament-shield::filament-shield.column.team'))
                    ->default('Global')
                    ->badge()
                    ->color(fn (mixed $state): string => str($state)->contains('Global') ? 'gray' : 'primary')
                    ->searchable()
                    ->visible(fn (): bool => static::shield()->isCentralApp() && Utils::isTenancyEnabled()),

                TextColumn::make('permissions_count')
                    ->badge()
                    ->label(__('filament-shield::filament-shield.column.permissions'))
                    ->counts('permissions')
                    ->colors(['success'])
                    ->tooltip('Jumlah izin yang terpasang pada role ini.'),

                TextColumn::make('updated_at')
                    ->label(__('filament-shield::filament-shield.column.updated_at'))
                    ->dateTime()
                    ->since(),
            ])
            ->filters([
                SelectFilter::make('guard_name')->label('Guard')
                    ->options(fn () => static::getModel()::query()->distinct()->pluck('guard_name', 'guard_name')->filter()),
                Filter::make('updated_between')->label('Diperbarui')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('Dari')->native(false),
                        Forms\Components\DatePicker::make('until')->label('Sampai')->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'] ?? null, fn ($q, $d) => $q->whereDate('updated_at', '>=', $d))
                            ->when($data['until'] ?? null, fn ($q, $d) => $q->whereDate('updated_at', '<=', $d));
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Action::make('duplicate')
                    ->label('Duplicate')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('info')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $roleModel = Utils::getRoleModel();
                        /** @var \Spatie\Permission\Models\Role $new */
                        $new = $roleModel::create([
                            'name'       => Str::of($record->name)->append(' Copy')->value(),
                            'guard_name' => $record->guard_name,
                        ]);
                        $new->permissions()->sync($record->permissions()->pluck('id'));
                        \Filament\Notifications\Notification::make()
                            ->title("Role '{$new->name}' dibuat (duplikasi).")
                            ->success()
                            ->send();
                    }),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
}
