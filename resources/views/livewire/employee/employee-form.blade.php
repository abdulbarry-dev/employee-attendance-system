<div>
    <flux:header>
        <div>
            <flux:heading size="xl">
                @if($employee)
                    {{ __('Edit Employee') }}
                @else
                    {{ __('Add New Employee') }}
                @endif
            </flux:heading>
            <flux:subheading>
                @if($employee)
                    {{ __('Update employee information and manage their account') }}
                @else
                    {{ __('Create a new employee account with basic information') }}
                @endif
            </flux:subheading>
        </div>
    </flux:header>

    <div class="mt-6 max-w-3xl">
        <form wire:submit="submit" class="space-y-6">
            <div class="grid gap-6 md:grid-cols-2">
                <!-- First Name -->
                <flux:input
                    wire:model="first_name"
                    label="{{ __('First Name') }}"
                    placeholder="{{ __('John') }}"
                    icon="user"
                    required
                />

                <!-- Last Name -->
                <flux:input
                    wire:model="last_name"
                    label="{{ __('Last Name') }}"
                    placeholder="{{ __('Doe') }}"
                    icon="user"
                    required
                />
            </div>

            <flux:separator />

            <div class="grid gap-6 md:grid-cols-2">
                <!-- Email -->
                <flux:input
                    wire:model="email"
                    type="email"
                    label="{{ __('Email Address') }}"
                    placeholder="{{ __('john@example.com') }}"
                    icon="envelope"
                    required
                />

                <!-- Phone Number -->
                <flux:input
                    wire:model="phone_number"
                    type="tel"
                    label="{{ __('Phone Number') }}"
                    placeholder="{{ __('+1 (555) 123-4567') }}"
                    icon="phone"
                />
            </div>

            @if(!$employee)
                <flux:separator />

                <div class="flex gap-3 rounded-lg border border-blue-300 bg-blue-50 p-4 dark:border-blue-500 dark:bg-blue-950/40">
                    <svg class="size-5 flex-shrink-0 text-blue-700 dark:text-blue-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div class="text-sm leading-relaxed text-blue-900 dark:text-zinc-200">
                        <strong class="font-semibold text-blue-950 dark:text-white">{{ __('Note:') }}</strong>
                        {{ __('A temporary password will be generated and sent to the employee\'s email address. They will be able to set their own password upon first login.') }}
                    </div>
                </div>
            @endif

            <flux:separator />

            <!-- Actions -->
            <div class="flex justify-between">
                <flux:button :href="route('employees.index')" wire:navigate variant="ghost" icon="arrow-left">
                    {{ __('Back to List') }}
                </flux:button>

                <div class="flex gap-3">
                    <flux:button type="submit" variant="primary" icon="check">
                        @if($employee)
                            {{ __('Update Employee') }}
                        @else
                            {{ __('Create Employee') }}
                        @endif
                    </flux:button>
                </div>
            </div>
        </form>
    </div>
