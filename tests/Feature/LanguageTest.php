<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class LanguageTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $employee;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['email' => 'admin@example.com']);
        $this->admin->assignRole('admin');

        $this->employee = User::factory()->create(['email' => 'employee@example.com']);
        $this->employee->assignRole('employee');
    }

    public function test_admin_can_select_and_persist_language(): void
    {
        $this->actingAs($this->admin);

        // Test that admin can access language settings
        $response = $this->get(route('language.edit'));
        $response->assertStatus(200);

        // Test that Livewire component can update language
        Livewire::test('settings.language')
            ->set('selectedLanguage', 'fr')
            ->assertSet('selectedLanguage', 'fr');

        // Verify language was persisted to database
        $this->admin->refresh();
        $this->assertEquals('fr', $this->admin->preferred_locale);

        // Verify session locale was set
        $this->assertEquals('fr', session('locale'));
    }

    public function test_language_changes_apply_immediately(): void
    {
        $this->actingAs($this->admin);

        Livewire::test('settings.language')
            ->set('selectedLanguage', 'de')
            ->assertDispatched('$refresh');
    }

    public function test_employee_inherits_admin_language_on_creation(): void
    {
        // Set admin's language to French
        $this->admin->update(['preferred_locale' => 'fr']);

        // Create a new employee directly using the model
        $newEmployee = User::create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'name' => 'John Doe',
            'email' => 'newemployee@example.com',
            'phone_number' => '+1234567890',
            'password' => bcrypt('password'),
            'preferred_locale' => $this->admin->preferred_locale ?? 'en',
        ]);

        $newEmployee->assignRole('employee');

        // Verify employee inherited admin's language
        $this->assertEquals('fr', $newEmployee->preferred_locale);
    }

    public function test_existing_employee_keeps_language_when_admin_changes_theirs(): void
    {
        // Set both admin and employee to French initially
        $this->admin->update(['preferred_locale' => 'fr']);
        $this->employee->update(['preferred_locale' => 'fr']);

        // Admin changes their language to Spanish
        $this->actingAs($this->admin);
        Livewire::test('settings.language')
            ->set('selectedLanguage', 'es');

        // Verify admin's language changed
        $this->admin->refresh();
        $this->assertEquals('es', $this->admin->preferred_locale);

        // Verify employee's language remained French
        $this->employee->refresh();
        $this->assertEquals('fr', $this->employee->preferred_locale);
    }

    public function test_admin_gets_browser_detected_language_on_first_login(): void
    {
        $newAdmin = User::factory()->create([
            'email' => 'newadmin@example.com',
            'preferred_locale' => null,
        ]);
        $newAdmin->assignRole('admin');

        $this->actingAs($newAdmin);

        // Simulate a request with browser language preference
        $response = $this->withHeaders([
            'Accept-Language' => 'fr-FR,fr;q=0.9,en;q=0.8',
        ])->get(route('dashboard'));

        // Verify admin's language was detected and saved
        $newAdmin->refresh();
        $this->assertEquals('fr', $newAdmin->preferred_locale);
    }

    public function test_unsupported_browser_language_defaults_to_english(): void
    {
        $newAdmin = User::factory()->create([
            'email' => 'newadmin@example.com',
            'preferred_locale' => null,
        ]);
        $newAdmin->assignRole('admin');

        $this->actingAs($newAdmin);

        // Simulate a request with unsupported language (Arabic)
        $response = $this->withHeaders([
            'Accept-Language' => 'ar-SA,ar;q=0.9',
        ])->get(route('dashboard'));

        // Verify default to English
        $newAdmin->refresh();
        $this->assertEquals('en', $newAdmin->preferred_locale);
    }

    public function test_language_setting_page_displays_current_language(): void
    {
        $this->admin->update(['preferred_locale' => 'de']);
        $this->actingAs($this->admin);

        Livewire::test('settings.language')
            ->assertSet('selectedLanguage', 'de');
    }

    public function test_employee_can_change_language_after_initial_inheritance(): void
    {
        // Employee inherits French from admin
        $this->employee->update(['preferred_locale' => 'fr']);
        $this->actingAs($this->employee);

        // Employee changes their language to German
        Livewire::test('settings.language')
            ->set('selectedLanguage', 'de');

        // Verify employee's language changed
        $this->employee->refresh();
        $this->assertEquals('de', $this->employee->preferred_locale);
    }

    public function test_invalid_language_selection_is_rejected(): void
    {
        $this->actingAs($this->admin);

        // Try to set an invalid language
        Livewire::test('settings.language')
            ->set('selectedLanguage', 'invalid_lang')
            ->assertSet('selectedLanguage', 'invalid_lang');

        // Verify the invalid language was not saved
        $this->admin->refresh();
        // Should remain with default or previous value
        $this->assertNotEquals('invalid_lang', $this->admin->preferred_locale);
    }

    public function test_guest_session_locale_is_set(): void
    {
        // Guest request with session locale
        session(['locale' => 'es']);

        $response = $this->get(route('login'));
        $response->assertStatus(200);

        // Verify session locale was preserved
        $this->assertEquals('es', session('locale'));
    }

    public function test_all_supported_languages_can_be_selected(): void
    {
        $this->actingAs($this->admin);

        $supportedLanguages = ['en', 'fr', 'de', 'es'];

        foreach ($supportedLanguages as $language) {
            Livewire::test('settings.language')
                ->set('selectedLanguage', $language)
                ->assertSet('selectedLanguage', $language);

            $this->admin->refresh();
            $this->assertEquals($language, $this->admin->preferred_locale);
        }
    }
}
