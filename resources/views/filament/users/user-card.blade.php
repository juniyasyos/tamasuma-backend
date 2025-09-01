@php
    $user = isset($getRecord) ? $getRecord() : ($record ?? null);
    $avatar = $user?->avatar_url ?: 'https://ui-avatars.com/api/?name=' . urlencode($user?->name ?? 'U');
    $roles = method_exists($user, 'getRoleNames') ? $user->getRoleNames()->all() : [];
@endphp

<div class="rounded-xl border border-gray-200 dark:border-gray-700 overflow-hidden bg-white dark:bg-gray-900">
    <div class="p-4 flex items-center gap-3">
        <img src="{{ $avatar }}" alt="Avatar" class="h-12 w-12 rounded-full ring-2 ring-primary-200 dark:ring-primary-900" loading="lazy" />
        <div class="min-w-0">
            <div class="text-base font-semibold text-gray-900 dark:text-gray-100 truncate">{{ $user?->name }}</div>
            <div class="text-sm text-gray-600 dark:text-gray-300 truncate flex items-center gap-1">
                <x-filament::icon icon="heroicon-m-envelope" class="h-4 w-4" />
                <span class="truncate">{{ $user?->email }}</span>
            </div>
        </div>
    </div>

    @if (!empty($roles))
        <div class="px-4 pb-2 flex flex-wrap gap-1">
            @foreach ($roles as $r)
                <x-filament::badge size="sm" color="info">{{ str($r)->headline() }}</x-filament::badge>
            @endforeach
        </div>
    @endif

    <div class="px-4 py-3 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between text-xs text-gray-600 dark:text-gray-300">
        <div class="flex items-center gap-2">
            <x-filament::icon icon="heroicon-m-clock" class="h-4 w-4" />
            <span>Dibuat {{ optional($user?->created_at)->diffForHumans() }}</span>
        </div>
        <div class="flex items-center gap-2">
            @if ($user?->email_verified_at)
                <x-filament::badge color="success" icon="heroicon-m-check-badge">Terverifikasi</x-filament::badge>
            @else
                <x-filament::badge color="warning" icon="heroicon-m-exclamation-triangle">Belum</x-filament::badge>
            @endif
        </div>
    </div>

    <div class="px-4 py-3 bg-gray-50 dark:bg-gray-900/40 flex flex-wrap gap-2">
        <x-filament::button tag="a" href="{{ \App\Filament\Resources\UserResource::getUrl('view', ['record' => $user]) }}" size="sm" color="gray" icon="heroicon-o-eye">
            Detail
        </x-filament::button>

        @can('update', $user)
            <x-filament::button tag="a" href="{{ \App\Filament\Resources\UserResource::getUrl('edit', ['record' => $user]) }}" size="sm" color="warning" icon="heroicon-o-pencil-square">
                Edit
            </x-filament::button>
        @endcan

        @can('delete', $user)
            <x-filament::button tag="a" href="{{ \App\Filament\Resources\UserResource::getUrl('edit', ['record' => $user]) }}" size="sm" color="danger" icon="heroicon-o-trash">
                Hapus
            </x-filament::button>
        @endcan
    </div>
</div>

