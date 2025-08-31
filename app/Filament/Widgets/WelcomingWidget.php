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

class WelcomingWidget extends Widget
{
    protected static string $view = 'filament.widgets.welcoming-widget';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = -100; // keep it at the top

    public static function canView(): bool
    {
        // All authenticated users can see the welcome widget
        return Auth::check();
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
        ];
    }
}
