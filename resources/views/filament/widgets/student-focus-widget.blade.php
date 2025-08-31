<x-filament-widgets::widget>
    <x-filament::section>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
            <div class="space-y-3">
                <div class="text-sm font-semibold text-gray-700 dark:text-gray-200">Lanjutkan Belajar</div>
                @forelse ($continueEnrollments as $en)
                    @php($p = $en->program)
                    <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-3 flex items-start gap-3">
                        <x-filament::icon icon="heroicon-o-play-circle" class="h-5 w-5 text-primary-500" />
                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-medium truncate">{{ $p?->title ?? '-' }}</div>
                            <div class="text-xs text-gray-500 flex items-center gap-2 flex-wrap">
                                @if($p?->learningArea?->name)
                                    <span class="fi-badge fi-color-gray">{{ $p->learningArea->name }}</span>
                                @endif
                                @if($p?->ends_at)
                                    <span>Sisa {{ now()->diffInDays($p->ends_at, false) }} hari</span>
                                @endif
                            </div>
                            <div class="mt-2">
                                <x-filament::button tag="a" size="sm" color="primary" icon="heroicon-o-arrow-right-circle"
                                    href="{{ \App\Filament\Resources\ProgramResource::getUrl('view', ['record' => $p?->id]) }}">
                                    Lanjutkan
                                </x-filament::button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-sm text-gray-500">Belum ada program aktif. Yuk mulai belajar!</div>
                @endforelse
            </div>

            <div class="space-y-3">
                <div class="text-sm font-semibold text-gray-700 dark:text-gray-200">Tenggat Mendekat</div>
                @forelse ($deadlineEnrollments as $en)
                    @php($p = $en->program)
                    <div class="rounded-lg border border-amber-200 dark:border-amber-700 p-3 flex items-start gap-3 bg-amber-50/50 dark:bg-amber-500/5">
                        <x-filament::icon icon="heroicon-o-exclamation-triangle" class="h-5 w-5 text-amber-600" />
                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-medium truncate">{{ $p?->title ?? '-' }}</div>
                            <div class="text-xs text-amber-700 dark:text-amber-300">Batas: {{ optional($p?->ends_at)->format('d M Y') }} ·
                                {{ now()->diffForHumans($p?->ends_at, ['parts' => 2, 'short' => true, 'syntax' => 1]) }}</div>
                            <div class="mt-2">
                                <x-filament::button tag="a" size="sm" color="warning" icon="heroicon-o-bolt"
                                    href="{{ \App\Filament\Resources\ProgramResource::getUrl('view', ['record' => $p?->id]) }}">
                                    Kerjakan Sekarang
                                </x-filament::button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-sm text-gray-500">Tidak ada tenggat dalam 14 hari.</div>
                @endforelse
            </div>

            <div class="space-y-3">
                <div class="text-sm font-semibold text-gray-700 dark:text-gray-200">Rekomendasi Untukmu</div>
                @forelse ($recommendations as $p)
                    <div class="rounded-lg border border-gray-200 dark:border-gray-700 p-3 flex items-start gap-3">
                        <x-filament::icon icon="heroicon-o-sparkles" class="h-5 w-5 text-success-500" />
                        <div class="flex-1 min-w-0">
                            <div class="text-sm font-medium truncate">{{ $p->title }}</div>
                            <div class="text-xs text-gray-500 flex items-center gap-2 flex-wrap">
                                @if($p->learningArea?->name)
                                    <span class="fi-badge fi-color-gray">{{ $p->learningArea->name }}</span>
                                @endif
                                @if($p->is_certified)
                                    <span class="fi-badge fi-color-primary">Bersertifikat</span>
                                @endif
                            </div>
                            <div class="mt-2">
                                <x-filament::button tag="a" size="sm" color="success" icon="heroicon-o-plus-circle"
                                    href="{{ \App\Filament\Resources\ProgramResource::getUrl('view', ['record' => $p->id]) }}">
                                    Lihat Program
                                </x-filament::button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-sm text-gray-500">Belum ada rekomendasi. Coba jelajahi program.</div>
                @endforelse
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>

