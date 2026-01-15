<div>
    <flux:header>
        <div>
            <flux:heading size="xl">{{ __('My Schedule') }}</flux:heading>
            <flux:subheading>{{ __('View your weekly shift schedule and working hours') }}</flux:subheading>
        </div>
    </flux:header>

    <div class="mt-6">
        @if($shiftsByDay->isEmpty())
            <!-- No Shifts Configured -->
            <div class="flex flex-col items-center justify-center rounded-xl border-2 border-dashed border-zinc-300 bg-zinc-50 p-12 dark:border-zinc-700 dark:bg-zinc-900/40">
                <svg class="size-16 text-zinc-400 dark:text-zinc-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <h3 class="mt-4 text-lg font-semibold text-zinc-900 dark:text-white">{{ __('No Schedule Configured') }}</h3>
                <p class="mt-2 text-center text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('Your administrator has not configured your shifts yet.') }}<br>
                    {{ __('Please contact your manager to set up your work schedule.') }}
                </p>
            </div>
        @else
            <!-- Weekly Schedule Grid -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @foreach($shiftsByDay as $dayGroup)
                    <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                        <!-- Day Header -->
                        <div class="border-b border-zinc-200 bg-gradient-to-br from-zinc-50 to-zinc-100 px-5 py-4 dark:border-zinc-700 dark:from-zinc-800 dark:to-zinc-900">
                            <h3 class="text-lg font-bold text-zinc-900 dark:text-white">{{ $dayGroup['name'] }}</h3>
                            <p class="mt-0.5 text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                                {{ $dayGroup['shifts']->count() }} {{ $dayGroup['shifts']->count() === 1 ? __('Shift') : __('Shifts') }}
                            </p>
                        </div>

                        <!-- Shifts for This Day -->
                        <div class="divide-y divide-zinc-100 dark:divide-zinc-800">
                            @foreach($dayGroup['shifts'] as $shift)
                                <div class="p-5 space-y-3">
                                    <!-- Time Range -->
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <svg class="size-5 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            <div>
                                                <div class="flex items-baseline gap-1.5">
                                                    <span class="font-mono text-base font-bold text-zinc-900 dark:text-white">
                                                        {{ \Carbon\Carbon::parse($shift->start_time)->format('h:i A') }}
                                                    </span>
                                                    <span class="text-zinc-400">→</span>
                                                    <span class="font-mono text-base font-bold text-zinc-900 dark:text-white">
                                                        {{ \Carbon\Carbon::parse($shift->end_time)->format('h:i A') }}
                                                    </span>
                                                </div>
                                                @php
                                                    $start = \Carbon\Carbon::parse($shift->start_time);
                                                    $end = \Carbon\Carbon::parse($shift->end_time);
                                                    if ($end->lte($start)) {
                                                        $end->addDay();
                                                    }
                                                    $duration = $start->diffInMinutes($end);
                                                    $hours = intdiv($duration, 60);
                                                    $minutes = $duration % 60;
                                                @endphp
                                                <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                                    {{ __(':hours h :minutes min', ['hours' => $hours, 'minutes' => $minutes]) }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Shift Details -->
                                    <div class="grid grid-cols-2 gap-3 rounded-lg bg-zinc-50 p-3 dark:bg-zinc-800/50">
                                        <!-- Grace Period -->
                                        <div class="flex items-start gap-2">
                                            <svg class="size-4 mt-0.5 flex-shrink-0 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            <div>
                                                <p class="text-xs font-semibold text-zinc-600 dark:text-zinc-300">{{ __('Grace Period') }}</p>
                                                <p class="mt-0.5 font-mono text-sm font-bold text-zinc-900 dark:text-white">
                                                    {{ $shift->grace_period_minutes }} {{ __('min') }}
                                                </p>
                                            </div>
                                        </div>

                                        <!-- Break Allowance -->
                                        <div class="flex items-start gap-2">
                                            <svg class="size-4 mt-0.5 flex-shrink-0 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                            <div>
                                                <p class="text-xs font-semibold text-zinc-600 dark:text-zinc-300">{{ __('Break') }}</p>
                                                <p class="mt-0.5 font-mono text-sm font-bold text-zinc-900 dark:text-white">
                                                    {{ $shift->break_allowance_minutes }} {{ __('min') }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    @if(\Carbon\Carbon::parse($shift->end_time)->lte(\Carbon\Carbon::parse($shift->start_time)))
                                        <!-- Night Shift Indicator -->
                                        <div class="flex items-center gap-2 rounded-md bg-indigo-50 px-2 py-1.5 dark:bg-indigo-900/20">
                                            <svg class="size-4 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                                            </svg>
                                            <span class="text-xs font-semibold text-indigo-700 dark:text-indigo-300">{{ __('Night Shift') }}</span>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Summary Card -->
            <div class="mt-6 overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
                <div class="border-b border-zinc-200 bg-zinc-50 px-6 py-4 dark:border-zinc-700 dark:bg-zinc-800">
                    <h3 class="font-semibold text-zinc-900 dark:text-white">{{ __('Schedule Summary') }}</h3>
                </div>
                <div class="p-6">
                    <div class="grid gap-6 md:grid-cols-3">
                        <div class="flex items-center gap-3">
                            <div class="rounded-full bg-blue-100 p-3 dark:bg-blue-900/30">
                                <svg class="size-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">{{ __('Working Days') }}</p>
                                <p class="mt-1 text-2xl font-bold text-zinc-900 dark:text-white">{{ $shiftsByDay->count() }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <div class="rounded-full bg-emerald-100 p-3 dark:bg-emerald-900/30">
                                <svg class="size-6 text-emerald-600 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">{{ __('Total Shifts') }}</p>
                                <p class="mt-1 text-2xl font-bold text-zinc-900 dark:text-white">{{ $employee->shifts->where('is_active', true)->count() }}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <div class="rounded-full bg-purple-100 p-3 dark:bg-purple-900/30">
                                <svg class="size-6 text-purple-600 dark:text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">{{ __('Weekly Hours') }}</p>
                                @php
                                    $totalMinutes = $employee->shifts->where('is_active', true)->sum(function($shift) {
                                        $start = \Carbon\Carbon::parse($shift->start_time);
                                        $end = \Carbon\Carbon::parse($shift->end_time);
                                        if ($end->lte($start)) {
                                            $end->addDay();
                                        }
                                        return $start->diffInMinutes($end);
                                    });
                                    $weeklyHours = intdiv($totalMinutes, 60);
                                    $weeklyMinutes = $totalMinutes % 60;
                                @endphp
                                <p class="mt-1 text-2xl font-bold text-zinc-900 dark:text-white">{{ $weeklyHours }}h {{ $weeklyMinutes }}m</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
