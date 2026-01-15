<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading class="sr-only">{{ __('Language Settings') }}</flux:heading>

    <x-settings.layout :heading="__('Language')" :subheading=" __('Select your preferred language for the interface')">
        <flux:radio.group wire:model.live="selectedLanguage" variant="segmented">
            <flux:radio value="en" icon="language">{{ __('English') }}</flux:radio>
            <flux:radio value="fr" icon="language">{{ __('Français') }}</flux:radio>
            <flux:radio value="de" icon="language">{{ __('Deutsch') }}</flux:radio>
            <flux:radio value="es" icon="language">{{ __('Español') }}</flux:radio>
        </flux:radio.group>
    </x-settings.layout>
</section>
