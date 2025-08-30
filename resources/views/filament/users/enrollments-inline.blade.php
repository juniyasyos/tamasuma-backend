@php($items = $getState())
<div class="space-y-3">
    @if(blank($items))
        <div class="text-sm text-gray-500">Belum ada program yang diikuti atau dimiliki.</div>
    @else
        <div class="grid grid-cols-1 gap-3">
            @foreach($items as $en)
                <div class="flex items-start gap-3 rounded-md border border-gray-200 dark:border-gray-700 p-3">
                    <x-filament::icon icon="heroicon-o-rectangle-stack" class="h-6 w-6 text-primary-500" />
                    <div class="flex-1">
                        <div class="text-sm font-medium">{{ $en->program?->title ?? '-' }}</div>
                        <div class="text-xs text-gray-500">
                            @if($en->program?->learningArea?->name)
                                <span class="fi-badge fi-color-gray">{{ $en->program->learningArea->name }}</span>
                            @endif
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

