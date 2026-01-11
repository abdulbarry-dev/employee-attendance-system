<div>
    <flux:header>
        <div>
            <flux:heading size="xl">{{ __('My Profile') }}</flux:heading>
            <flux:subheading>{{ __('View your personal information, shift details, and salary') }}</flux:subheading>
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

        <!-- Salary & Penalties Card -->
        <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <div class="border-b border-zinc-200 bg-zinc-50 px-6 py-4 dark:border-zinc-700 dark:bg-zinc-800">
                <div class="flex items-center justify-between">
                    <h3 class="font-semibold text-zinc-900 dark:text-white">{{ __('Salary & Deductions') }}</h3>
                    <div class="flex items-center gap-2">
                        <button wire:click="changeMonth('prev')" class="rounded p-1 hover:bg-zinc-200 dark:hover:bg-zinc-700">
                            <svg class="size-5 text-zinc-600 dark:text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                        </button>
                        <span class="min-w-32 text-center text-sm font-medium text-zinc-700 dark:text-zinc-300">
                            {{ \Carbon\Carbon::create($selectedYear, $selectedMonth, 1)->format('F Y') }}
                        </span>
                        <button wire:click="changeMonth('next')" class="rounded p-1 hover:bg-zinc-200 dark:hover:bg-zinc-700">
                            <svg class="size-5 text-zinc-600 dark:text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
            <div class="p-6 space-y-4">
                @if($employee->monthly_salary)
                    <div class="grid gap-4">
                        <div class="flex items-center justify-between rounded-lg bg-zinc-50 p-4 dark:bg-zinc-800">
                            <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ __('Monthly Salary') }}</span>
                            <span class="text-lg font-bold text-zinc-900 dark:text-white">{{ number_format($employee->monthly_salary, 2) }}</span>
                        </div>
                        <div class="flex items-center justify-between rounded-lg bg-red-50 p-4 dark:bg-red-900/10">
                            <span class="text-sm font-medium text-red-700 dark:text-red-300">{{ __('Total Penalties') }}</span>
                            <span class="text-lg font-bold text-red-600 dark:text-red-400">-{{ number_format($totalPenaltyAmount, 2) }}</span>
                        </div>
                        <div class="flex items-center justify-between rounded-lg border-2 border-emerald-200 bg-emerald-50 p-4 dark:border-emerald-900/30 dark:bg-emerald-900/10">
                            <span class="font-semibold text-emerald-800 dark:text-emerald-300">{{ __('Net Salary') }}</span>
                            <span class="text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ number_format($netSalary, 2) }}</span>
                        </div>
                    </div>
                @else
                    <p class="text-zinc-500 dark:text-zinc-400">{{ __('No salary information configured') }}</p>
                @endif
            </div>
        </div>

        <!-- Penalty History Table -->
        @if($penalties->count() > 0)
            <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                <div class="border-b border-zinc-200 bg-zinc-50 px-6 py-4 dark:border-zinc-700 dark:bg-zinc-800">
                    <h3 class="font-semibold text-zinc-900 dark:text-white">{{ __('Penalty Details') }}</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                        <thead class="bg-zinc-50 dark:bg-zinc-800">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-700 dark:text-zinc-300">{{ __('Date') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-700 dark:text-zinc-300">{{ __('Type') }}</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-700 dark:text-zinc-300">{{ __('Details') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-zinc-700 dark:text-zinc-300">{{ __('Amount') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                            @foreach($penalties as $penalty)
                                <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-zinc-900 dark:text-white">
                                        {{ $penalty->occurred_on->format('M d, Y') }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        @if($penalty->type === 'late')
                                            <flux:badge color="red" size="sm" icon="clock">{{ __('Late Check-in') }}</flux:badge>
                                        @else
                                            <flux:badge color="amber" size="sm" icon="pause-circle">{{ __('Break Overage') }}</flux:badge>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm text-zinc-600 dark:text-zinc-400">
                                        @if($penalty->minutes_late > 0)
                                            {{ $penalty->minutes_late }} {{ __('minutes late') }}
                                        @endif
                                        @if($penalty->break_overage_minutes > 0)
                                            {{ $penalty->break_overage_minutes }} {{ __('minutes over break') }}
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-semibold text-red-600 dark:text-red-400">
                                        -{{ number_format($penalty->penalty_amount, 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </div>
</div>
