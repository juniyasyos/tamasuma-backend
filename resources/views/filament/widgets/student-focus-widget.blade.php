<x-filament-widgets::widget>
    <x-filament::section>
        <div class="space-y-6">
            <div>
                <div class="text-sm font-semibold text-gray-700 dark:text-gray-200 mb-2">Lanjutkan Belajar</div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    @forelse ($continueEnrollments as $en)
                        @php($p = $en->program)
                        <x-filament::card>
                            <div class="flex items-start gap-3">
                                <x-filament::icon icon="heroicon-o-play-circle" class="h-5 w-5 text-primary-500" />
                                <div class="flex-1 min-w-0">
                                    <div class="text-sm font-semibold truncate">{{ $p?->title ?? '-' }}</div>
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
                        </x-filament::card>
                    @empty
                        <x-filament::card>
                            <div class="text-sm text-gray-500">Belum ada program aktif. Yuk mulai belajar!</div>
                        </x-filament::card>
                    @endforelse
                </div>
            </div>

            <div>
                <div class="text-sm font-semibold text-gray-700 dark:text-gray-200 mb-2">Tenggat Mendekat</div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    @forelse ($deadlineEnrollments as $en)
                        @php($p = $en->program)
                        <x-filament::card>
                            <div class="flex items-start gap-3">
                                <x-filament::icon icon="heroicon-o-exclamation-triangle" class="h-5 w-5 text-amber-600" />
                                <div class="flex-1 min-w-0">
                                    <div class="text-sm font-semibold truncate">{{ $p?->title ?? '-' }}</div>
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
                        </x-filament::card>
                    @empty
                        <x-filament::card>
                            <div class="text-sm text-gray-500">Tidak ada tenggat dalam 14 hari.</div>
                        </x-filament::card>
                    @endforelse
                </div>
            </div>

            <div>
                <div class="text-sm font-semibold text-gray-700 dark:text-gray-200 mb-2">Rekomendasi Untukmu</div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    @forelse ($recommendations as $p)
                        <x-filament::card>
                            <div class="flex items-start gap-3">
                                <x-filament::icon icon="heroicon-o-sparkles" class="h-5 w-5 text-success-500" />
                                <div class="flex-1 min-w-0">
                                    <div class="text-sm font-semibold truncate">{{ $p->title }}</div>
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
                        </x-filament::card>
                    @empty
                        <x-filament::card>
                            <div class="text-sm text-gray-500">Belum ada rekomendasi. Coba jelajahi program.</div>
                        </x-filament::card>
                    @endforelse
                </div>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
