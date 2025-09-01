<x-filament-widgets::widget>
    <x-filament::section heading="Pencapaian Terbaru" description="Ringkasan 5 pencapaian terakhir Anda.">
        @if ($items->count())
            <ul class="space-y-2">
                @foreach ($items as $a)
                    <li class="rounded-lg border border-gray-200 dark:border-gray-700 p-3 flex items-center gap-3">
                        @if ($a->proof_image)
                            <img src="{{ Storage::disk('public')->url($a->proof_image) }}" alt="Bukti" class="h-10 w-10 rounded-md object-cover" loading="lazy">
                        @else
                            <x-filament::icon icon="heroicon-o-trophy" class="h-6 w-6 text-warning-500" />
                        @endif
                        <div class="min-w-0">
                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">{{ $a->title }}</div>
                            <div class="text-xs text-gray-600 dark:text-gray-400 truncate">{{ $a->issuer ?? '—' }} • {{ optional($a->achieved_at)->format('d M Y') }}</div>
                        </div>
                    </li>
                @endforeach
            </ul>
        @else
            <div class="text-sm text-gray-500 dark:text-gray-400">Belum ada pencapaian. Tambahkan dari menu Pencapaian.</div>
        @endif
    </x-filament::section>
</x-filament-widgets::widget>

