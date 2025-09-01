<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex flex-col gap-4">
            <div class="flex items-center gap-4">
                @if ($avatar)
                    <img src="{{ $avatar }}" alt="Avatar" class="h-12 w-12 rounded-full ring-2 ring-primary-200 dark:ring-primary-900" />
                @else
                    <div class="h-12 w-12 rounded-full bg-primary-600/10 text-primary-600 grid place-items-center font-semibold">
                        {{ str($user->name)->substr(0,1)->upper() }}
                    </div>
                @endif

                <div class="min-w-0">
                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ $dateText }}</div>
                    <div class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                        {{ $greeting }}, {{ $user->name }} 👋
                    </div>

                    @if (!empty($roleNames))
                        <div class="mt-1 flex flex-wrap gap-2">
                            @foreach ($roleNames as $r)
                                <x-filament::badge color="info" icon="heroicon-o-identification">{{ str($r)->headline() }}</x-filament::badge>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <div class="col-span-1 md:col-span-2">
                    <div class="rounded-xl bg-gray-50 dark:bg-gray-900/40 p-4">
                        <div class="text-sm font-medium text-gray-700 dark:text-gray-200 mb-3">Aksi Cepat</div>
                        <div class="flex flex-wrap gap-2">
                            @foreach ($quickLinks as $link)
                                <x-filament::button tag="a" href="{{ $link['url'] }}" icon="{{ $link['icon'] }}" color="{{ $link['color'] }}">
                                    {{ $link['label'] }}
                                </x-filament::button>
                            @endforeach
                        </div>
                        @if ($canTestNotifications)
                            <div class="mt-4 border-t border-gray-200 dark:border-gray-700 pt-3">
                                <div class="text-xs uppercase tracking-wide text-gray-500 dark:text-gray-400 mb-2">Testing Notifikasi</div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <x-filament::button color="gray" icon="heroicon-o-bell" wire:click="notifyAll">
                                        Kirim ke Semua Pengguna
                                    </x-filament::button>

                                    @if (!empty($roleOptions))
                                        <x-filament::button color="info" icon="heroicon-o-bell-alert"
                                            x-data="{ role: '{{ $roleOptions[0] ?? '' }}' }"
                                            x-on:click="$wire.notifyRole(role)">
                                            Kirim ke Role Terpilih
                                        </x-filament::button>

                                        <select x-data x-model="role" class="fi-input mt-0.5 rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-800 text-sm">
                                            @foreach ($roleOptions as $r)
                                                <option value="{{ $r }}">{{ Str::headline($r) }}</option>
                                            @endforeach
                                        </select>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="col-span-1">
                    <div class="rounded-xl border border-gray-200 dark:border-gray-700 p-4 h-full">
                        <div class="text-sm font-medium text-gray-700 dark:text-gray-200">Ringkasan</div>
                        <div class="mt-3 space-y-2">
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex items-center gap-2 text-gray-600 dark:text-gray-300">
                                    <x-filament::icon icon="heroicon-o-bell" class="h-4 w-4" />
                                    <span>Notifikasi belum dibaca</span>
                                </div>
                                <span class="inline-flex items-center justify-center rounded-full px-2 py-0.5 text-xs font-semibold bg-warning-100 text-warning-800 dark:bg-warning-400/10 dark:text-warning-400">{{ $unreadNotificationsCount }}</span>
                            </div>
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex items-center gap-2 text-gray-600 dark:text-gray-300">
                                    <x-filament::icon icon="heroicon-o-envelope-open" class="h-4 w-4" />
                                    <span>Verifikasi email</span>
                                </div>
                                @if ($emailVerified)
                                    <x-filament::badge color="success" icon="heroicon-o-check-circle">Terverifikasi</x-filament::badge>
                                @else
                                    <x-filament::badge color="warning" icon="heroicon-o-exclamation-triangle">Belum</x-filament::badge>
                                @endif
                            </div>
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex items-center gap-2 text-gray-600 dark:text-gray-300">
                                    <x-filament::icon icon="heroicon-o-lock-closed" class="h-4 w-4" />
                                    <span>Autentikasi 2 Langkah</span>
                                </div>
                                @if ($hasTwoFactor)
                                    <x-filament::badge color="success" icon="heroicon-o-shield-check">Aktif</x-filament::badge>
                                @else
                                    <x-filament::badge color="gray" icon="heroicon-o-shield-exclamation">Nonaktif</x-filament::badge>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if (!$emailVerified || !$hasTwoFactor)
                <div class="rounded-xl bg-amber-50 dark:bg-amber-500/10 p-4 flex items-start gap-3">
                    <x-filament::icon icon="heroicon-o-light-bulb" class="h-5 w-5 text-amber-600 dark:text-amber-400 mt-0.5" />
                    <div class="text-sm text-amber-900 dark:text-amber-200">
                        <div class="font-semibold mb-0.5">Tips Keamanan</div>
                        <div>
                            @if (!$emailVerified)
                                Verifikasi email Anda untuk meningkatkan keamanan akun.
                            @endif
                            @if (!$emailVerified && !$hasTwoFactor)
                                <span class="mx-1">•</span>
                            @endif
                            @if (!$hasTwoFactor)
                                Aktifkan autentikasi dua langkah (2FA) dari halaman Profil untuk keamanan ekstra.
                            @endif
                        </div>
                        <div class="mt-2">
                            <x-filament::button tag="a" href="{{ url('my-profile') }}" size="sm" color="warning" icon="heroicon-o-shield-check">
                                Buka Profil
                            </x-filament::button>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
