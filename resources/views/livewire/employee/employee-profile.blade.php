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
                @if($employee->shifts && $employee->shifts->count())
                    <div class="space-y-3">
                        @foreach($employee->shifts->sortBy(['day_of_week', 'start_time']) as $shift)
                            <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-800 dark:bg-zinc-800/60">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-semibold text-zinc-700 dark:text-zinc-200 uppercase">{{ strtoupper($shift->day_of_week) }}</span>
                                        @if($shift->name)
                                            <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ $shift->name }}</span>
                                        @endif
                                    </div>
                                    <span class="font-mono text-base font-semibold text-zinc-900 dark:text-white">
                                        {{ \Carbon\Carbon::parse($shift->start_time)->format('h:i A') }}
                                        <span class="text-zinc-500">→</span>
                                        {{ \Carbon\Carbon::parse($shift->end_time)->format('h:i A') }}
                                    </span>
                                </div>
                                <div class="mt-2 flex flex-wrap gap-4 text-xs text-zinc-600 dark:text-zinc-400">
                                    <span>{{ __('Grace: :minutes min', ['minutes' => $shift->grace_period_minutes]) }}</span>
                                    <span>{{ __('Break Allowance: :minutes min', ['minutes' => $shift->break_allowance_minutes]) }}</span>
                                    <span>{{ $shift->is_active ? __('Active') : __('Inactive') }}</span>
                                </div>
                            </div>
                        @endforeach
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
