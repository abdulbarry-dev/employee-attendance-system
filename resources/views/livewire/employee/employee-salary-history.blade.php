<div>
    <flux:header>
        <div>
            <flux:heading size="xl">{{ __('Salary & Deductions History') }}</flux:heading>
            <flux:subheading>{{ __('Review your monthly salary, deductions, and penalty details over time') }}</flux:subheading>
        </div>
    </flux:header>

    <div class="mt-6 space-y-6">
        <!-- Registration Date Indicator -->
        @if($registrationDate)
            <div class="flex items-center gap-3 rounded-lg border border-blue-200 bg-blue-50 px-4 py-3 dark:border-blue-900/30 dark:bg-blue-900/20">
                <svg class="h-5 w-5 flex-shrink-0 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path>
                    <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 1 1 0 100-2 4 4 0 00-4 4v10a4 4 0 004 4h12a4 4 0 004-4V5a4 4 0 00-4-4 1 1 0 100 2 2 2 0 012 2v10a2 2 0 01-2 2H6a2 2 0 01-2-2V5z" clip-rule="evenodd"></path>
                </svg>
                <p class="text-sm font-medium text-blue-900 dark:text-blue-200">
                    {{ __('Employee since') }}: <span class="font-semibold">{{ $registrationDate->format('F d, Y') }}</span>
                </p>
            </div>
        @endif

        <!-- Current Month Summary -->
        <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <div class="border-b border-zinc-200 bg-zinc-50 px-6 py-4 dark:border-zinc-700 dark:bg-zinc-800">
                <div class="flex items-center justify-between">
                    <h3 class="font-semibold text-zinc-900 dark:text-white">{{ __('Current Period Summary') }}</h3>
                    <div class="flex items-center gap-2">
                        <button
                            wire:click="changeMonth('prev')"
                            {{ !$canNavigatePrev ? 'disabled' : '' }}
                            class="rounded p-1 transition-colors {{ $canNavigatePrev ? 'hover:bg-zinc-200 dark:hover:bg-zinc-700 cursor-pointer' : 'cursor-not-allowed opacity-50' }}"
                        >
                            <svg class="size-5 text-zinc-600 dark:text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                        </button>
                        <span class="min-w-32 text-center text-sm font-medium text-zinc-700 dark:text-zinc-300">
                            {{ \Carbon\Carbon::create($selectedYear, $selectedMonth, 1)->format('F Y') }}
                        </span>
                        <button
                            wire:click="changeMonth('next')"
                            {{ !$canNavigateNext ? 'disabled' : '' }}
                            class="rounded p-1 transition-colors {{ $canNavigateNext ? 'hover:bg-zinc-200 dark:hover:bg-zinc-700 cursor-pointer' : 'cursor-not-allowed opacity-50' }}"
                        >
                            <svg class="size-5 text-zinc-600 dark:text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
            <div class="p-6">
                @if($monthlySalary > 0)
                    <div class="grid gap-4 md:grid-cols-4">
                        <!-- Fixed Monthly Salary -->
                        <div class="flex items-center justify-between rounded-lg bg-blue-50 p-4 dark:bg-blue-900/20">
                            <div class="flex-1">
                                <p class="text-xs font-medium uppercase tracking-wider text-blue-600 dark:text-blue-400">{{ __('Fixed Monthly Salary') }}</p>
                                <p class="mt-1 text-2xl font-bold text-blue-600 dark:text-blue-400">{{ number_format($monthlySalary, 2) }}</p>
                                <p class="mt-1 text-xs text-blue-500 dark:text-blue-300">{{ __('Base salary') }}</p>
                            </div>
                            <svg class="h-10 w-10 text-blue-200 dark:text-blue-900/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>

                        <!-- Your Salary for Working Days -->
                        <div class="flex items-center justify-between rounded-lg bg-zinc-50 p-4 dark:bg-zinc-800">
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <p class="text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Your Salary') }}</p>
                                    <div class="group relative">
                                        <svg class="h-4 w-4 cursor-help text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-300" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                        </svg>
                                        <div class="invisible absolute bottom-full left-1/2 mb-2 w-56 -translate-x-1/2 transform rounded-lg bg-zinc-900 p-3 text-xs text-white shadow-lg group-hover:visible dark:bg-zinc-800">
                                            <p class="font-semibold mb-1">{{ __('Calculated based on working days') }}</p>
                                            <p>{{ __('You worked') }} <span class="font-semibold">{{ $currentAttendanceDays }}</span> {{ __('out of') }} <span class="font-semibold">{{ $currentWorkingDays }}</span> {{ __('working days') }}</p>
                                            <p class="mt-1">{{ __('Daily rate') }}: {{ number_format($currentDailySalary, 2) }} × {{ $currentAttendanceDays }} = {{ number_format($currentProratedSalary, 2) }}</p>
                                        </div>
                                    </div>
                                </div>
                                <p class="mt-1 text-2xl font-bold text-zinc-900 dark:text-white">{{ number_format($currentProratedSalary, 2) }}</p>
                                <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">{{ __('prorated for working days') }}</p>
                            </div>
                            <svg class="h-10 w-10 text-zinc-300 dark:text-zinc-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>

                        <!-- Penalties to be Deducted -->
                        <div class="flex items-center justify-between rounded-lg bg-red-50 p-4 dark:bg-red-900/20">
                            <div>
                                <p class="text-xs font-medium uppercase tracking-wider text-red-600 dark:text-red-400">{{ __('Penalties Deducted') }}</p>
                                <p class="mt-1 text-2xl font-bold text-red-600 dark:text-red-400">-{{ number_format($currentMonthTotal, 2) }}</p>
                                <p class="mt-1 text-xs text-red-500 dark:text-red-300">{{ __('will be subtracted') }}</p>
                            </div>
                            <svg class="h-10 w-10 text-red-200 dark:text-red-900/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>

                        <!-- Final Net Salary -->
                        <div class="flex items-center justify-between rounded-lg border-2 border-emerald-200 bg-emerald-50 p-4 dark:border-emerald-900/30 dark:bg-emerald-900/20">
                            <div>
                                <p class="text-xs font-medium uppercase tracking-wider text-emerald-700 dark:text-emerald-300">{{ __('Net Salary') }}</p>
                                <p class="mt-1 text-2xl font-bold text-emerald-600 dark:text-emerald-400">{{ number_format($currentNetSalary, 2) }}</p>
                                <p class="mt-1 text-xs text-emerald-600 dark:text-emerald-300">{{ __('final amount') }}</p>
                            </div>
                            <svg class="h-10 w-10 text-emerald-200 dark:text-emerald-900/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                @else
                    <p class="text-center text-zinc-500 dark:text-zinc-400">{{ __('No salary information configured') }}</p>
                @endif
            </div>
        </div>

        <!-- Penalty Details for Current Month -->
        @if($currentMonthPenalties->count() > 0)
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
                            @foreach($currentMonthPenalties as $penalty)
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

        <!-- Monthly History Table -->
        @if($months->count())
            <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                <div class="border-b border-zinc-200 bg-zinc-50 px-6 py-4 dark:border-zinc-700 dark:bg-zinc-800">
                    <h3 class="font-semibold text-zinc-900 dark:text-white">{{ __('Historical Overview') }}</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                        <thead class="bg-zinc-50 dark:bg-zinc-800">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-700 dark:text-zinc-300">{{ __('Month') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-zinc-700 dark:text-zinc-300">{{ __('Days Worked') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-zinc-700 dark:text-zinc-300">{{ __('Total Working Days') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-zinc-700 dark:text-zinc-300">{{ __('Fixed Salary') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-zinc-700 dark:text-zinc-300">{{ __('Your Salary') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-zinc-700 dark:text-zinc-300">{{ __('Penalties') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-zinc-700 dark:text-zinc-300">{{ __('Net') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-zinc-700 dark:text-zinc-300">{{ __('Late (min)') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-zinc-700 dark:text-zinc-300">{{ __('Break Over (min)') }}</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-zinc-700 dark:text-zinc-300">{{ __('Entries') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                            @foreach($months as $month)
                                <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                                    <td class="whitespace-nowrap px-6 py-4 text-sm font-semibold text-zinc-900 dark:text-white">{{ $month['label'] }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right text-sm text-zinc-700 dark:text-zinc-300">{{ $month['attendance_days'] }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right text-sm text-zinc-700 dark:text-zinc-300">{{ $month['working_days'] }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right font-mono text-sm text-blue-600 dark:text-blue-400">{{ number_format($month['gross'], 2) }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right font-mono text-sm font-semibold text-zinc-700 dark:text-zinc-200">{{ number_format($month['prorated_gross'], 2) }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right font-mono text-sm text-red-600 dark:text-red-400">-{{ number_format($month['penalties'], 2) }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right font-mono text-sm font-bold text-emerald-600 dark:text-emerald-400">{{ number_format($month['net'], 2) }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right text-sm text-zinc-700 dark:text-zinc-300">{{ $month['late_minutes'] }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right text-sm text-zinc-700 dark:text-zinc-300">{{ $month['break_overage_minutes'] }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-right text-sm text-zinc-700 dark:text-zinc-300">{{ $month['entries'] }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white p-6 text-center dark:border-zinc-700 dark:bg-zinc-900">
                <svg class="mx-auto h-12 w-12 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <p class="mt-4 text-sm font-medium text-zinc-600 dark:text-zinc-300">{{ __('No salary or penalty history available yet') }}</p>
                <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">{{ __('Your penalty records will appear here once you start clocking in') }}</p>
            </div>
        @endif
    </div>
</div>
