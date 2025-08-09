<x-filament-panels::page>
    @php
        $settings = \Juniyasyos\FilamentSettingsHub\Facades\FilamentSettingsHub::load()
            ->sortBy('order')
            ->groupBy('group');
        $tenant = \Filament\Facades\Filament::getTenant();
    @endphp

    @foreach ($settings as $settingGroup => $setting)
        <div class="fi-page">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                {{ str($settingGroup)->contains(['.', '::']) ? trans($settingGroup) : $settingGroup }}
            </h2>

            <section class="grid gap-6 pt-6 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($setting as $item)
                    @php
                        $canAccess = true;
                        $routeUrl = $item->route
                            ? ($tenant
                                ? route($item->route, $tenant)
                                : route($item->route))
                            : null;
                        $pageUrl = $item->page ? app($item->page)::getUrl() : null;

                        if ($item->route && filament('filament-settings-hub')->isShieldAllowed()) {
                            $page = optional(\Illuminate\Support\Facades\Route::getRoutes()->getRoutesByName()[$item->route] ?? null)->action['controller'] ?? null;
                            $page = $page ? str($page)->afterLast('\\') : null;
                            $canAccess = $page
                                ? \Filament\Facades\Filament::auth()
                                    ->user()
                                    ->can('page_' . $page)
                                : false;
                        } elseif ($item->page && filament('filament-settings-hub')->isShieldAllowed()) {
                            $canAccess = \Filament\Facades\Filament::auth()
                                ->user()
                                ->can('page_' . str($item->page)->afterLast('\\'));
                        }
                    @endphp

                    @if ($canAccess && ($routeUrl || $pageUrl))
                        <a href="{{ $routeUrl ?? $pageUrl }}" class="fi-section rounded-xl p-6 bg-white dark:fi-section-content ring-1 ring-gray-200 dark:ring-gray-700 transition hover:bg-gray-50 dark:hover:bg-gray-900">
                            <div class="flex items-start gap-4">
                                <div class="p-2">
                                    @if (isset($item->icon))
                                        <x-icon name="{{ $item->icon }}" class="w-10 h-10 text-gray-800 dark:text-gray-200" style="color: {{ $item->color ?? 'inherit' }}" />
                                    @else
                                        <x-heroicon-s-cog class="w-10 h-10 text-gray-800 dark:text-gray-200" />
                                    @endif
                                </div>
                                <div class="text-left">
                                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                                        {{ str($item->label)->contains(['.', '::']) ? trans($item->label) : $item->label }}
                                    </h3>
                                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                        {{ str($item->description)->contains(['.', '::']) ? trans($item->description) : $item->description }}
                                    </p>
                                </div>
                            </div>
                        </a>
                    @endif
                @endforeach
            </section>
        </div>
    @endforeach
</x-filament-panels::page>
