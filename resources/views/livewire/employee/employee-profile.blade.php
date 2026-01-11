<div>
    <flux:header>
        <div>
            <flux:heading size="xl">{{ __('My Profile') }}</flux:heading>
            <flux:subheading>{{ __('View your personal information and shift details') }}</flux:subheading>
        </div>
    </flux:header>

    <div class="mt-6 space-y-6">
        <!-- Personal Information Card -->
        <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <div class="border-b border-zinc-200 bg-zinc-50 px-6 py-4 dark:border-zinc-700 dark:bg-zinc-800">
                <h3 class="font-semibold text-zinc-900 dark:text-white">{{ __('Personal Information') }}</h3>
            </div>
            <div class="p-6 space-y-4">
                <div class="grid gap-6 md:grid-cols-2">
                    <div>
                        <label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Full Name') }}</label>
                        <p class="mt-1 text-base text-zinc-900 dark:text-white">{{ $employee->full_name }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Email') }}</label>
                        <p class="mt-1 text-base text-zinc-900 dark:text-white">{{ $employee->email }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Phone Number') }}</label>
                        <p class="mt-1 text-base text-zinc-900 dark:text-white">{{ $employee->phone_number ?? '—' }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Employee ID') }}</label>
                        <p class="mt-1 font-mono text-base text-zinc-900 dark:text-white">#{{ str_pad($employee->id, 5, '0', STR_PAD_LEFT) }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Shift Schedule Card -->
        <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <div class="border-b border-zinc-200 bg-zinc-50 px-6 py-4 dark:border-zinc-700 dark:bg-zinc-800">
                <h3 class="font-semibold text-zinc-900 dark:text-white">{{ __('Shift Schedule') }}</h3>
            </div>
            <div class="p-6 space-y-4">
                @if($employee->shift_start && $employee->shift_end)
                    <div class="grid gap-6 md:grid-cols-2">
                        <div>
                            <label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Shift Start') }}</label>
                            <p class="mt-1 flex items-center gap-2">
                                <svg class="size-5 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span class="font-mono text-lg font-semibold text-zinc-900 dark:text-white">{{ \Carbon\Carbon::parse($employee->shift_start)->format('h:i A') }}</span>
                            </p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Shift End') }}</label>
                            <p class="mt-1 flex items-center gap-2">
                                <svg class="size-5 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                <span class="font-mono text-lg font-semibold text-zinc-900 dark:text-white">{{ \Carbon\Carbon::parse($employee->shift_end)->format('h:i A') }}</span>
                            </p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Grace Period') }}</label>
                            <p class="mt-1 text-base text-zinc-900 dark:text-white">{{ $employee->grace_period_minutes ?? 0 }} {{ __('minutes') }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Daily Break Allowance') }}</label>
                            <p class="mt-1 text-base text-zinc-900 dark:text-white">{{ $employee->break_allowance_minutes ?? 0 }} {{ __('minutes') }}</p>
                        </div>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Working Days') }}</label>
                        <p class="mt-1 text-base text-zinc-900 dark:text-white">{{ $workingDays ?: __('Not set') }}</p>
                    </div>
                @else
                    <p class="text-zinc-500 dark:text-zinc-400">{{ __('No shift schedule configured yet') }}</p>
                @endif
            </div>
        </div>
    </div>
</div>
