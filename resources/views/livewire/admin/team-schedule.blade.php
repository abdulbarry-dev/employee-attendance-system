<div class="min-h-screen bg-gradient-to-br from-slate-50 to-slate-100 dark:from-slate-900 dark:to-slate-800 p-6">
    <div class="max-w-7xl mx-auto">
        <div class="mb-8">
            <flux:heading size="xl" level="1">Team Schedule</flux:heading>
            <flux:subheading>View all employees and their shift schedules</flux:subheading>
        </div>

        @forelse ($employees as $employee)
            <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-slate-200 dark:border-slate-700 overflow-hidden mb-6">
                <!-- Employee Header -->
                <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-slate-700 dark:to-slate-600 px-6 py-4 border-b border-slate-200 dark:border-slate-600">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-full bg-blue-500 text-white flex items-center justify-center font-bold text-lg">
                            {{ strtoupper(substr($employee->name, 0, 1)) }}
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">{{ $employee->name }}</h3>
                            <p class="text-sm text-slate-600 dark:text-slate-400">{{ $employee->email }}</p>
                        </div>
                    </div>
                </div>

                <!-- Shifts Grid -->
                <div class="p-6">
                    @if ($employee->shifts->isNotEmpty())
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
                            @foreach ($employee->shifts->sortBy(fn ($shift) => [$shift->day_of_week, $shift->start_time]) as $shift)
                                <div class="border border-slate-200 dark:border-slate-600 rounded-lg p-4 hover:shadow-md transition-shadow">
                                    <div class="flex items-start justify-between mb-3">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 dark:bg-slate-700 text-slate-800 dark:text-slate-200">
                                            {{ ucfirst(substr($shift->day_of_week, 0, 3)) }}
                                        </span>
                                        @php
                                            $shiftStart = $shift->start_time;
                                            $shiftEnd = $shift->end_time;
                                            if ($shiftEnd <= $shiftStart) {
                                                $isNightShift = true;
                                            } else {
                                                $isNightShift = false;
                                            }
                                        @endphp
                                        @if ($isNightShift)
                                            <span class="inline-flex items-center px-2 py-1 rounded text-xs font-medium bg-indigo-100 dark:bg-indigo-900 text-indigo-800 dark:text-indigo-200">
                                                Night
                                            </span>
                                        @endif
                                    </div>

                                    <div class="space-y-2 text-sm">
                                        <div>
                                            <p class="text-slate-600 dark:text-slate-400">Time</p>
                                            <p class="font-semibold text-slate-900 dark:text-white">
                                                {{ $shift->start_time->format('H:i') }} - {{ $shift->end_time->format('H:i') }}
                                            </p>
                                        </div>

                                        <div class="flex gap-4 text-xs">
                                            <div>
                                                <p class="text-slate-600 dark:text-slate-400">Grace Period</p>
                                                <p class="font-semibold text-slate-900 dark:text-white">{{ $shift->grace_period_minutes }}m</p>
                                            </div>
                                            <div>
                                                <p class="text-slate-600 dark:text-slate-400">Break Allowance</p>
                                                <p class="font-semibold text-slate-900 dark:text-white">{{ $shift->break_allowance_minutes }}m</p>
                                            </div>
                                        </div>

                                        @php
                                            $duration = $shift->end_time->diffInMinutes($shift->start_time);
                                            if ($duration < 0) $duration += 24 * 60;
                                            $hours = intval($duration / 60);
                                            $minutes = $duration % 60;
                                        @endphp
                                        <div>
                                            <p class="text-slate-600 dark:text-slate-400">Duration</p>
                                            <p class="font-semibold text-slate-900 dark:text-white">{{ $hours }}h {{ $minutes }}m</p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Weekly Summary -->
                        @php
                            $totalMinutes = $employee->shifts->reduce(function ($carry, $shift) {
                                $duration = $shift->end_time->diffInMinutes($shift->start_time);
                                if ($duration < 0) $duration += 24 * 60;
                                return $carry + $duration;
                            }, 0);
                            $totalHours = intval($totalMinutes / 60);
                            $totalMins = $totalMinutes % 60;
                        @endphp
                        <div class="bg-slate-50 dark:bg-slate-700 rounded-lg p-4 flex items-center justify-between">
                            <div>
                                <p class="text-sm text-slate-600 dark:text-slate-400">Working Days</p>
                                <p class="text-2xl font-bold text-slate-900 dark:text-white">{{ $employee->shifts->unique('day_of_week')->count() }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-slate-600 dark:text-slate-400">Total Shifts</p>
                                <p class="text-2xl font-bold text-slate-900 dark:text-white">{{ $employee->shifts->count() }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-slate-600 dark:text-slate-400">Weekly Hours</p>
                                <p class="text-2xl font-bold text-slate-900 dark:text-white">{{ $totalHours }}h {{ $totalMins }}m</p>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-8">
                            <p class="text-slate-600 dark:text-slate-400">No shifts scheduled</p>
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-slate-200 dark:border-slate-700 p-12 text-center">
                <p class="text-slate-600 dark:text-slate-400">No employees found</p>
            </div>
        @endforelse
    </div>
</div>
