<x-filament-widgets::widget>
    <x-filament::section icon="heroicon-o-academic-cap" heading="Belajar & Progres Kamu"
        description="Lanjutkan yang tertunda, kejar tenggat, dan jelajahi rekomendasi yang relevan."
        class="overflow-hidden">
        {{-- Header actions --}}
        <div class="flex items-center justify-between gap-3 mb-4">
            <div class="text-sm text-gray-500 dark:text-gray-400">
                Terakhir diperbarui {{ now()->translatedFormat('d M Y, H:i') }}
            </div>
            <div class="flex items-center gap-2">
                <x-filament::button tag="a" color="gray" size="sm" icon="heroicon-o-magnifying-glass"
                    href="{{ \App\Filament\Resources\ProgramResource::getUrl() }}">
                    Jelajahi Program
                </x-filament::button>
                <x-filament::button tag="a" color="primary" size="sm" icon="heroicon-o-plus-circle"
                    href="{{ \App\Filament\Resources\ProgramResource::getUrl() }}">
                    Program Baru
                </x-filament::button>
            </div>
        </div>

        {{-- Loading skeleton (stack, no grid) --}}
        <div wire:loading.flex class="flex flex-col gap-3">
            @for ($i = 0; $i < 4; $i++)
                <div
                    class="rounded-xl border border-gray-200/60 dark:border-gray-700/60 bg-gray-50 dark:bg-gray-800/50 p-4 animate-pulse">
                    <div class="h-4 w-32 bg-gray-200 dark:bg-gray-700 rounded mb-2"></div>
                    <div class="h-3 w-48 bg-gray-200 dark:bg-gray-700 rounded mb-3"></div>
                    <div class="h-8 w-24 bg-gray-200 dark:bg-gray-700 rounded"></div>
                </div>
            @endfor
        </div>

        <div wire:loading.remove class="space-y-8">
            {{-- SECTION: Lanjutkan Belajar (nested section) --}}
            <x-filament::section heading="Lanjutkan Belajar" description="Program yang sedang kamu ikuti."
                style="margin-top:2rem; padding:1rem; border:1px solid border-radius:12px;">
                @if (count($continueEnrollments))
                    <ul role="list" class="flex flex-col gap-3">
                        @foreach ($continueEnrollments as $en)
                            @php($p = $en->program)
                            <li>
                                <x-filament::card
                                    class="group hover:shadow-md transition hover:-translate-y-0.5 duration-200">
                                    <div class="flex items-start gap-3">
                                        <x-filament::icon icon="heroicon-o-play-circle"
                                            class="h-5 w-5 text-primary-500 shrink-0" />
                                        <div class="flex-1 min-w-0">
                                            <div class="text-sm font-semibold truncate" title="{{ $p?->title ?? '-' }}">
                                                {{ $p?->title ?? '-' }}
                                            </div>

                                            <div
                                                class="mt-1 flex items-center gap-2 flex-wrap text-xs text-gray-500 dark:text-gray-400">
                                                @if ($p?->learningArea?->name)
                                                    <span
                                                        class="fi-badge fi-color-gray">{{ $p->learningArea->name }}</span>
                                                @endif
                                                @if ($p?->ends_at)
                                                    @php($sisa = now()->diffInDays($p->ends_at, false))
                                                    <span
                                                        class="fi-badge {{ $sisa <= 3 ? 'fi-color-danger' : 'fi-color-warning' }}">
                                                        Sisa {{ $sisa }} hari
                                                    </span>
                                                @endif
                                            </div>

                                            {{-- (Opsional) Progress bar kalau ada $en->progress 0..100 --}}
                                            @if (isset($en->progress))
                                                <div class="mt-2">
                                                    <div
                                                        class="h-1.5 bg-gray-200 dark:bg-gray-700 rounded-full overflow-hidden">
                                                        <div class="h-1.5 bg-primary-500"
                                                            style="width: {{ (int) $en->progress }}%"></div>
                                                    </div>
                                                    <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                                        {{ (int) $en->progress }}%
                                                        selesai</div>
                                                </div>
                                            @endif

                                            <div class="mt-3">
                                                <x-filament::button tag="a" size="sm" color="primary"
                                                    icon="heroicon-o-arrow-right-circle"
                                                    href="{{ \App\Filament\Resources\ProgramResource::getUrl('view', ['record' => $p?->id]) }}"
                                                    class="group-hover:translate-x-0.5 transition"
                                                    aria-label="Lanjutkan {{ $p?->title }}">
                                                    Lanjutkan
                                                </x-filament::button>
                                            </div>
                                        </div>
                                    </div>
                                </x-filament::card>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <x-filament::card>
                        <div class="text-sm text-gray-500">
                            Belum ada program aktif. Yuk mulai belajar dari katalog program.
                        </div>
                    </x-filament::card>
                @endif
            </x-filament::section>

            {{-- SECTION: Tenggat Mendekat (nested section) --}}
            <x-filament::section heading="Tenggat Mendekat"
                description="Prioritaskan tugas dengan batas waktu terdekat."
                style="margin-top:2rem; padding:1rem; border:1px solid #e5e7eb; border-radius:12px;">
                @if (count($deadlineEnrollments))
                    <ul role="list" class="flex flex-col gap-3">
                        @foreach ($deadlineEnrollments as $en)
                            @php($p = $en->program)
                            <li>
                                <x-filament::card
                                    class="group hover:shadow-md transition hover:-translate-y-0.5 duration-200">
                                    <div class="flex items-start gap-3">
                                        <x-filament::icon icon="heroicon-o-exclamation-triangle"
                                            class="h-5 w-5 text-amber-600 shrink-0" />
                                        <div class="flex-1 min-w-0">
                                            <div class="text-sm font-semibold truncate"
                                                title="{{ $p?->title ?? '-' }}">
                                                {{ $p?->title ?? '-' }}
                                            </div>

                                            <div class="mt-1 text-xs text-amber-700 dark:text-amber-300">
                                                Batas: {{ optional($p?->ends_at)->format('d M Y') }} ·
                                                {{ now()->diffForHumans($p?->ends_at, ['parts' => 2, 'short' => true, 'syntax' => 1]) }}
                                            </div>

                                            <div class="mt-3">
                                                <x-filament::button tag="a" size="sm" color="warning"
                                                    icon="heroicon-o-bolt"
                                                    href="{{ \App\Filament\Resources\ProgramResource::getUrl('view', ['record' => $p?->id]) }}"
                                                    class="group-hover:translate-x-0.5 transition"
                                                    aria-label="Kerjakan sekarang {{ $p?->title }}">
                                                    Kerjakan Sekarang
                                                </x-filament::button>
                                            </div>
                                        </div>
                                    </div>
                                </x-filament::card>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <x-filament::card>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Tidak ada tenggat dalam 14 hari.</div>
                    </x-filament::card>
                @endif
            </x-filament::section>

            {{-- SECTION: Rekomendasi Untukmu (nested section) --}}
            <x-filament::section heading="Rekomendasi Untukmu"
                description="Program pilihan yang cocok dengan minat & riwayat belajarmu."
                style="margin-top:2rem; padding:1rem; border:1px solid #e5e7eb; border-radius:12px;">
                @if (count($recommendations))
                    <ul role="list" class="flex flex-col gap-3">
                        @foreach ($recommendations as $p)
                            <li>
                                <x-filament::card
                                    class="group hover:shadow-md transition hover:-translate-y-0.5 duration-200">
                                    <div class="flex items-start gap-3">
                                        <x-filament::icon icon="heroicon-o-sparkles"
                                            class="h-5 w-5 text-success-500 shrink-0" />
                                        <div class="flex-1 min-w-0">
                                            <div class="text-sm font-semibold truncate" title="{{ $p->title }}">
                                                {{ $p->title }}
                                            </div>

                                            <div
                                                class="mt-1 flex items-center gap-2 flex-wrap text-xs text-gray-500 dark:text-gray-400">
                                                @if ($p->learningArea?->name)
                                                    <span
                                                        class="fi-badge fi-color-gray">{{ $p->learningArea->name }}</span>
                                                @endif
                                                @if ($p->is_certified)
                                                    <span class="fi-badge fi-color-primary">Bersertifikat</span>
                                                @endif
                                            </div>

                                            <div class="mt-3">
                                                <x-filament::button tag="a" size="sm" color="success"
                                                    icon="heroicon-o-plus-circle"
                                                    href="{{ \App\Filament\Resources\ProgramResource::getUrl('view', ['record' => $p->id]) }}"
                                                    class="group-hover:translate-x-0.5 transition"
                                                    aria-label="Lihat Program {{ $p->title }}">
                                                    Lihat Program
                                                </x-filament::button>
                                            </div>
                                        </div>
                                    </div>
                                </x-filament::card>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <x-filament::card>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Belum ada rekomendasi. Coba jelajahi
                            program.</div>
                    </x-filament::card>
                @endif
            </x-filament::section>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
