<?php

namespace App\Livewire\Settings;

use Livewire\Component;

class Language extends Component
{
    public $selectedLanguage;

    public $languages = [
        'en' => 'English',
        'fr' => 'Français',
        'de' => 'Deutsch',
        'es' => 'Español',
    ];

    public function mount(): void
    {
        $this->selectedLanguage = auth()->user()->preferred_locale ?? config('app.locale', 'en');
    }

    public function updatedSelectedLanguage($locale): void
    {
        // Validate locale is supported
        if (! array_key_exists($locale, $this->languages)) {
            return;
        }

        // Update user's preferred locale
        auth()->user()->update(['preferred_locale' => $locale]);

        // Update session locale
        session(['locale' => $locale]);

        // Set application locale immediately
        app()->setLocale($locale);

        // Refresh the component to show translations in new language
        $this->dispatch('$refresh');
    }

    public function render()
    {
        return view('livewire.settings.language');
    }
}
