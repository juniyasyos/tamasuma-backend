<x-filament::page>
    <div class="space-y-4">
        {{ $this->form }}

        <div class="flex items-center gap-2">
            <x-filament::button color="primary" icon="heroicon-o-check" wire:click="create">
                Simpan
            </x-filament::button>
            <x-filament::button tag="a" :href="\App\Filament\Pages\MyAchievements::getUrl()" color="gray" icon="heroicon-o-arrow-left">
                Kembali
            </x-filament::button>
        </div>
    </div>
</x-filament::page>

