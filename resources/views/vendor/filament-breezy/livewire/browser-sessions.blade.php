<x-filament::section>
    <x-slot name="heading">{{ __('filament-breezy::default.profile.browser_sessions.heading') }}</x-slot>
    <x-slot name="description">{{ __('filament-breezy::default.profile.browser_sessions.subheading') }}</x-slot>

    <x-filament-panels::form>
        {{ $this->form }}
    </x-filament-panels::form>

    <x-filament-actions::modals />
</x-filament::section>

