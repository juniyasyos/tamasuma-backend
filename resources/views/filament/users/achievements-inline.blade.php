@php($items = $getState())
<div class="space-y-3">
    @if(blank($items))
        <div class="text-sm text-gray-500">Belum ada pencapaian untuk pengguna ini.</div>
    @else
        <div class="grid grid-cols-1 gap-3">
            @foreach($items as $a)
                <div class="flex items-start gap-3 rounded-md border border-gray-200 dark:border-gray-700 p-3">
                    @if($a->proof_image)
                        <img src="{{ Storage::disk('public')->url($a->proof_image) }}" alt="proof" class="h-12 w-12 rounded object-cover" />
                    @else
                        <x-filament::icon icon="heroicon-o-star" class="h-6 w-6 text-amber-500" />
                    @endif
                    <div class="flex-1">
                        <div class="text-sm font-medium">{{ $a->title }}</div>
                        <div class="text-xs text-gray-500">
                            {{ ucfirst($a->category) }}
                            @if($a->issuer)
                                · {{ $a->issuer }}
                            @endif
                            @if($a->achieved_at)
                                · {{ optional($a->achieved_at)->format('d M Y') }}
                            @endif
                        </div>
                        @if($a->description)
                            <div class="text-xs text-gray-600 dark:text-gray-300 mt-1">{{ $a->description }}</div>
                        @endif
                        @if($a->url)
                            <div class="mt-1">
                                <a class="text-xs text-primary-600 hover:underline" href="{{ $a->url }}" target="_blank" rel="noopener">Lihat tautan</a>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
