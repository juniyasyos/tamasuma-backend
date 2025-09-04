@php($items = $getState())
<div class="space-y-3">
    @if(blank($items))
        <div class="text-sm text-gray-500">Belum ada pengajar pada program ini.</div>
    @else
        <div class="grid grid-cols-1 gap-3">
            @foreach($items as $t)
                <div class="flex items-start gap-3 rounded-md border border-gray-200 dark:border-gray-700 p-3">
                    <div class="h-10 w-10 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center overflow-hidden">
                        @php($avatar = $t->avatar_url ?? null)
                        @if($avatar)
                            <img src="{{ $avatar }}" alt="avatar" class="h-10 w-10 object-cover" />
                        @else
                            <x-filament::icon icon="heroicon-o-academic-cap" class="h-5 w-5 text-gray-500" />
                        @endif
                    </div>
                    <div class="flex-1">
                        @php($profileUrl = class_exists(\App\Filament\Resources\UserResource::class) ? \App\Filament\Resources\UserResource::getUrl('view', ['record' => $t]) : null)
                        <div class="text-sm font-medium">
                            @if($profileUrl)
                                <a href="{{ $profileUrl }}" class="hover:underline" target="_blank" rel="noopener">{{ $t->name }}</a>
                            @else
                                {{ $t->name }}
                            @endif
                        </div>
                        <div class="text-xs text-gray-500 flex items-center gap-2">
                            <x-filament::icon icon="heroicon-o-envelope" class="h-4 w-4" />
                            <span>{{ $t->email }}</span>
                            <span class="fi-badge fi-color-primary">Pengajar</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

