<x-filament::section>
    <x-slot name="heading">{{ __('filament-breezy::default.profile.personal_info.heading') }}</x-slot>
    <x-slot name="description">{{ __('filament-breezy::default.profile.personal_info.subheading') }}</x-slot>

    <form wire:submit.prevent="submit" class="space-y-6">
        {{ $this->form }}

        <div class="text-right">
            <x-filament::button type="submit">
                {{ __('filament-breezy::default.profile.personal_info.submit.label') }}
            </x-filament::button>
        </div>
    </form>
</x-filament::section>

