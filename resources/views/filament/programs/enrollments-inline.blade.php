@php($items = $getState())
<div class="space-y-3">
    @if(blank($items))
        <div class="text-sm text-gray-500">Belum ada pengguna yang terdaftar pada program ini.</div>
    @else
        <div class="grid grid-cols-1 gap-3">
            @foreach($items as $en)
                <div class="flex items-start gap-3 rounded-md border border-gray-200 dark:border-gray-700 p-3">
                    <div class="h-10 w-10 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center overflow-hidden">
                        @php($avatar = $en->user?->avatar_url)
                        @if($avatar)
                            <img src="{{ $avatar }}" alt="avatar" class="h-10 w-10 object-cover" />
                        @else
                            <x-filament::icon icon="heroicon-o-user" class="h-5 w-5 text-gray-500" />
                        @endif
                    </div>
                    <div class="flex-1">
                        <div class="text-sm font-medium">{{ $en->user?->name ?? '-' }}</div>
                        <div class="text-xs text-gray-500">
                            <span class="fi-badge {{ match($en->status){'active'=>'fi-color-success','completed'=>'fi-color-primary','dropped'=>'fi-color-gray', default=>'fi-color-gray'} }}">{{ ucfirst($en->status) }}</span>
                            @if($en->enrolled_at)
                                · Daftar: {{ optional($en->enrolled_at)->format('d M Y') }}
                            @endif
                            @if($en->completed_at)
                                · Selesai: {{ optional($en->completed_at)->format('d M Y') }}
                            @endif
                        </div>
                        @if($en->notes)
                            <div class="text-xs text-gray-600 dark:text-gray-300 mt-1">{{ $en->notes }}</div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

