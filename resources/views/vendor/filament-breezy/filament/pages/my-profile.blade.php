@php
    $user = auth()->user();
    $components = $this->getRegisteredMyProfileComponents();

    $anchorMap = [
        'PersonalInfo' => 'info',
        'UpdatePassword' => 'password',
        'TwoFactorAuthentication' => '2fa',
        'BrowserSessions' => 'sessions',
        'SanctumTokens' => 'tokens',
    ];

    $left = [];
    $right = [];
    foreach ($components as $component) {
        $key = collect($anchorMap)->first(fn($id, $needle) => str_contains($component, $needle));
        $bucket =
            str_contains($component, 'PersonalInfo') || str_contains($component, 'UpdatePassword') ? 'left' : 'right';
        ${$bucket}[] = [
            'class' => $component,
            'anchor' => $key ?? \Illuminate\Support\Str::kebab(class_basename($component)),
        ];
    }

    $quick = collect([$left, $right])
        ->flatten(1)
        ->map(function ($c) {
            $label = match (true) {
                str_contains($c['class'], 'PersonalInfo') => 'Informasi Pribadi',
                str_contains($c['class'], 'UpdatePassword') => 'Ubah Password',
                str_contains($c['class'], 'TwoFactorAuthentication') => 'Autentikasi 2FA',
                str_contains($c['class'], 'BrowserSessions') => 'Sesi Peramban',
                str_contains($c['class'], 'SanctumTokens') => 'API Tokens',
                default => class_basename($c['class']),
            };
            $icon = match (true) {
                str_contains($c['class'], 'PersonalInfo') => 'heroicon-o-user',
                str_contains($c['class'], 'UpdatePassword') => 'heroicon-o-key',
                str_contains($c['class'], 'TwoFactorAuthentication') => 'heroicon-o-shield-check',
                str_contains($c['class'], 'BrowserSessions') => 'heroicon-o-computer-desktop',
                str_contains($c['class'], 'SanctumTokens') => 'heroicon-o-cog-6-tooth',
                default => 'heroicon-o-chevron-right',
            };
            return [
                'label' => $label,
                'icon' => $icon,
                'anchor' => $c['anchor'],
            ];
        })
        ->all();

    $avatar = method_exists($user, 'getFilamentAvatarUrl') ? ($user->getFilamentAvatarUrl() ?: null) : null;
    $roles = method_exists($user, 'getRoleNames') ? $user->getRoleNames()->all() : [];
    $emailVerified = method_exists($user, 'hasVerifiedEmail') ? $user->hasVerifiedEmail() : true;
    $has2fa = method_exists($user, 'hasEnabledTwoFactor') ? $user->hasEnabledTwoFactor() : false;
@endphp

<x-filament::page>
    <div class="space-y-6">
        <div class="rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="bg-gradient-to-r from-amber-100 to-amber-50 dark:from-amber-500/10 dark:to-amber-400/5 p-6">
                <div class="flex items-center gap-4">
                    @if ($avatar)
                        <img src="{{ $avatar }}" alt="Avatar"
                            class="h-16 w-16 rounded-full ring-2 ring-amber-300 dark:ring-amber-700" />
                    @else
                        <div
                            class="h-16 w-16 rounded-full bg-amber-600/10 text-amber-600 grid place-items-center text-2xl font-semibold">
                            {{ str($user->name)->substr(0, 1)->upper() }}
                        </div>
                    @endif

                    <div class="min-w-0">
                        <div class="text-xl font-semibold text-gray-900 dark:text-gray-100">Profil Saya</div>
                        <div class="text-sm text-gray-600 dark:text-gray-300 truncate">{{ $user->name }} ·
                            {{ $user->email }}</div>
                        @if (!empty($roles))
                            <div class="mt-2 flex flex-wrap gap-2">
                                @foreach ($roles as $r)
                                    <x-filament::badge icon="heroicon-o-identification"
                                        color="info">{{ str($r)->headline() }}</x-filament::badge>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div class="ml-auto hidden md:flex items-center gap-2">
                        @foreach ($quick as $q)
                            <x-filament::button tag="a" href="#{{ $q['anchor'] }}" size="sm" color="gray"
                                icon="{{ $q['icon'] }}">{{ $q['label'] }}</x-filament::button>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-12 gap-6 p-6">
                <div class="md:col-span-7 space-y-6">
                    @foreach ($left as $c)
                        <div id="{{ $c['anchor'] }}" class="scroll-mt-24">
                            @livewire($c['class'])
                        </div>
                    @endforeach
                </div>
                <div class="md:col-span-5 space-y-6">
                    <x-filament::section>
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2 text-gray-700 dark:text-gray-200">
                                    <x-filament::icon icon="heroicon-o-envelope-open" class="h-4 w-4" />
                                    <span>Verifikasi Email</span>
                                </div>
                                @if ($emailVerified)
                                    <x-filament::badge color="success"
                                        icon="heroicon-o-check-circle">Terverifikasi</x-filament::badge>
                                @else
                                    <x-filament::badge color="warning"
                                        icon="heroicon-o-exclamation-triangle">Belum</x-filament::badge>
                                @endif
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2 text-gray-700 dark:text-gray-200">
                                    <x-filament::icon icon="heroicon-o-shield-check" class="h-4 w-4" />
                                    <span>Autentikasi Dua Langkah</span>
                                </div>
                                @if ($has2fa)
                                    <x-filament::badge color="success"
                                        icon="heroicon-o-lock-closed">Aktif</x-filament::badge>
                                @else
                                    <x-filament::badge color="gray"
                                        icon="heroicon-o-lock-open">Nonaktif</x-filament::badge>
                                @endif
                            </div>
                            <div class="pt-2">
                                <x-filament::button tag="a" href="#2fa" size="sm" color="primary"
                                    icon="heroicon-o-shield-exclamation">Kelola 2FA</x-filament::button>
                                <x-filament::button tag="a" href="#password" size="sm" color="gray"
                                    icon="heroicon-o-key">Ubah Password</x-filament::button>
                            </div>
                        </div>
                    </x-filament::section>

                    @foreach ($right as $c)
                        <div id="{{ $c['anchor'] }}" class="scroll-mt-24">
                            @livewire($c['class'])
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-filament::page>
