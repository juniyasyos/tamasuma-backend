<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Carbon\Carbon;

use App\Filament\Resources\UserResource;
use App\Filament\Resources\ProgramResource;
use App\Filament\Resources\RoleResource;
use App\Filament\Resources\PartnerResource;
use Filament\Notifications\Notification;
use App\Models\User as AppUser;
use Illuminate\Support\Str;

class WelcomingWidget extends Widget
{
    protected static string $view = 'filament.widgets.welcoming-widget';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = -100; // keep it at the top

    public static function canView(): bool
    {
        $u = Auth::user();
        if (!$u) return false;
        // Use explicit permission when available; fallback to authenticated
        return $u->can('view_widget_welcoming_widget') || $u !== null;
    }

    protected function getViewData(): array
    {
        $user = Auth::user();

        $now = Carbon::now(config('app.timezone'));
        $hour = (int) $now->format('H');
        $greeting = $hour < 11 ? 'Selamat pagi' : ($hour < 15 ? 'Selamat siang' : ($hour < 18 ? 'Selamat sore' : 'Selamat malam'));

        // Role badges (Spatie Permission)
        $roleNames = method_exists($user, 'getRoleNames') ? $user->getRoleNames()->values()->all() : [];

        // Quick links based on permissions/roles
        $quickLinks = [];

        $quickLinks[] = [
            'label' => 'Profil Saya',
            'icon'  => 'heroicon-o-user',
            'color' => 'info',
            'url'   => url('my-profile'), // Breezy slug as configured in PanelProvider
        ];

        if ($user->can('view_any_user')) {
            $quickLinks[] = [
                'label' => 'Kelola Pengguna',
                'icon'  => 'heroicon-o-users',
                'color' => 'primary',
                'url'   => class_exists(UserResource::class) ? UserResource::getUrl('index') : URL::to('/'),
            ];
        }

        if ($user->can('create_program')) {
            $quickLinks[] = [
                'label' => 'Buat Program',
                'icon'  => 'heroicon-o-plus-circle',
                'color' => 'success',
                'url'   => class_exists(ProgramResource::class) ? ProgramResource::getUrl('create') : URL::to('/'),
            ];
        }

        if ($user->can('view_any_program')) {
            $quickLinks[] = [
                'label' => 'Lihat Program',
                'icon'  => 'heroicon-o-rectangle-stack',
                'color' => 'warning',
                'url'   => class_exists(ProgramResource::class) ? ProgramResource::getUrl('index') : URL::to('/'),
            ];
        }

        if ($user->can('view_any_role')) {
            $quickLinks[] = [
                'label' => 'Roles & Permission',
                'icon'  => 'heroicon-o-shield-check',
                'color' => 'gray',
                'url'   => class_exists(RoleResource::class) ? RoleResource::getUrl('index') : URL::to('/'),
            ];
        }

        if ($user->can('view_any_partner')) {
            $quickLinks[] = [
                'label' => 'Mitra Kolaborasi',
                'icon'  => 'heroicon-o-briefcase',
                'color' => 'gray',
                'url'   => class_exists(PartnerResource::class) ? PartnerResource::getUrl('index') : URL::to('/'),
            ];
        }

        $avatar = method_exists($user, 'getFilamentAvatarUrl') ? ($user->getFilamentAvatarUrl() ?: null) : null;
        $unread = method_exists($user, 'unreadNotifications') ? (int) $user->unreadNotifications()->count() : 0;
        $has2fa = method_exists($user, 'hasEnabledTwoFactor') ? (bool) $user->hasEnabledTwoFactor() : false;
        $emailVerified = method_exists($user, 'hasVerifiedEmail') ? (bool) $user->hasVerifiedEmail() : true;

        // Tester utilities for admins only
        $canTestNotifications = $user->can('view_any_user');
        $roleOptions = [];
        if ($canTestNotifications && class_exists(\Spatie\Permission\Models\Role::class)) {
            $roleOptions = \Spatie\Permission\Models\Role::query()->pluck('name')->map(fn($n) => (string) $n)->values()->all();
        }

        return [
            'user' => $user,
            'greeting' => $greeting,
            'dateText' => $now->translatedFormat('l, d F Y'),
            'roleNames' => $roleNames,
            'quickLinks' => $quickLinks,
            'avatar' => $avatar,
            'unreadNotificationsCount' => $unread,
            'hasTwoFactor' => $has2fa,
            'emailVerified' => $emailVerified,
            'canTestNotifications' => $canTestNotifications,
            'roleOptions' => $roleOptions,
        ];
    }

    // ========== Tester actions (Admin only) ==========
    public function notifyAll(): void
    {
        $u = Auth::user();
        if (! $u || ! $u->can('view_any_user')) return;

        $users = AppUser::query()->get();
        if ($users->isEmpty()) return;

        $title = 'Pengumuman Sistem';
        $body  = 'Ini adalah notifikasi uji ke semua pengguna.';

        Notification::make()
            ->title($title)
            ->body($body)
            ->icon('heroicon-o-bell')
            ->success()
            ->sendToDatabase($users);

        // feedback to the initiator
        Notification::make()->title('Terkirim ke semua pengguna')->success()->send();
    }

    public function notifyRole(string $role): void
    {
        $u = Auth::user();
        if (! $u || ! $u->can('view_any_user')) return;
        $role = trim($role);
        if ($role === '') return;

        $users = AppUser::role($role)->get();
        if ($users->isEmpty()) {
            Notification::make()->title('Role tidak memiliki pengguna')->warning()->send();
            return;
        }

        $title = 'Pengumuman Role';
        $body  = 'Ini adalah notifikasi uji untuk role: '.Str::headline($role);

        Notification::make()
            ->title($title)
            ->body($body)
            ->icon('heroicon-o-bell-alert')
            ->info()
            ->sendToDatabase($users);

        Notification::make()->title('Terkirim ke role: '.Str::headline($role))->success()->send();
    }
}
