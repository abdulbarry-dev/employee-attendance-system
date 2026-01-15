<div>
    <!-- Header -->
    <flux:header>
        <div class="flex items-center gap-4">
            <flux:button
                href="{{ route('employees.index') }}"
                wire:navigate
                variant="ghost"
                icon="arrow-left"
                size="sm"
                class="!p-2"
            />
            <div>
                <flux:heading size="xl">{{ $employee->first_name }} {{ $employee->last_name }}</flux:heading>
                <flux:subheading>{{ __('Monthly shift schedule') }}</flux:subheading>
            </div>
        </div>

        <flux:spacer />

        <flux:avatar
            size="lg"
            :name="$employee->first_name . ' ' . $employee->last_name"
            :initials="$employee->initials()"
        />
    </flux:header>

    <!-- Main Content -->
    <div class="mt-6 max-w-6xl space-y-6">
        <!-- Month Navigation -->
        <div class="flex items-center justify-between rounded-lg border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <flux:button
                wire:click="previousMonth"
                variant="ghost"
                icon="chevron-left"
            />

            <div class="text-center">
                <p class="text-2xl font-bold text-zinc-900 dark:text-white">{{ $monthName }}</p>
            </div>

            <div class="flex gap-2">
                <flux:button
                    wire:click="goToToday"
                    variant="ghost"
                    size="sm"
                >
                    {{ __('Today') }}
                </flux:button>

                <flux:button
                    wire:click="nextMonth"
                    variant="ghost"
                    icon="chevron-right"
                />
            </div>
        </div>s

        <!-- Calendar Grid - Desktop View (hidden on mobile) -->
        <div class="hidden md:block rounded-lg border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <!-- Day Headers -->
            <div class="grid grid-cols-7 border-b border-zinc-200 dark:border-zinc-700">
                @foreach (['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $day)
                    <div class="px-4 py-3 text-center font-semibold text-zinc-700 dark:text-zinc-300">
                        {{ __($day) }}
                    </div>
                @endforeach
            </div>

            <!-- Calendar Days -->
            @foreach ($calendar as $week)
                <div class="grid grid-cols-7 border-b border-zinc-200 dark:border-zinc-700">
                    @for ($i = 0; $i < 7; $i++)
                        @php $dayData = $week[$i] ?? null; @endphp
                        @if ($dayData === null)
                            <!-- Empty cell for days outside current month -->
                            <div wire:key="admin-empty-{{ $i }}-{{ $refreshKey }}" class="aspect-square bg-zinc-50 dark:bg-zinc-800/50 border-r border-zinc-200 dark:border-zinc-700"></div>
                        @else
                            @php
                                $date = $dayData['date'];
                                $shiftInfo = $this->getShiftForDate($date);
                                $isToday = $date->isToday();
                            @endphp
                            <!-- Calendar Day Cell -->
                            <button
                                type="button"
                                wire:key="admin-day-{{ $date->toDateString() }}-{{ $refreshKey }}"
                                wire:click="selectDate({{ (int) $dayData['day'] }})"
                                @class([
                                    'aspect-square p-3 text-left transition-all hover:shadow-md cursor-pointer',
                                    'border-r border-b border-zinc-200 dark:border-zinc-700',
                                    // Background colors based on shift status
                                    'bg-blue-50 dark:bg-blue-900/20' => $shiftInfo,
                                    'bg-zinc-50 dark:bg-zinc-800/50' => !$shiftInfo,
                                    // Today highlight
                                    'ring-2 ring-blue-400 dark:ring-blue-600' => $isToday,
                                ])
                            >
                                <div class="h-full overflow-hidden">
                                    <div class="flex flex-col justify-between h-full">
                                        <!-- Day number -->
                                        <div class="flex items-start justify-between gap-1">
                                            <span class="text-sm font-semibold text-zinc-900 dark:text-white">
                                                {{ $dayData['day'] }}
                                            </span>

                                            @if ($shiftInfo?->monthlyShift)
                                                <span class="inline-flex rounded bg-green-600 px-1.5 py-0.5 text-xs font-medium text-white">
                                                    {{ __('Override') }}
                                                </span>
                                            @elseif ($shiftInfo && !$shiftInfo->monthlyShift)
                                                <span class="inline-flex rounded bg-blue-600 px-1.5 py-0.5 text-xs font-medium text-white">
                                                    {{ __('Weekly') }}
                                                </span>
                                            @endif
                                        </div>

                                        <!-- Shift time -->
                                        @if ($shiftInfo)
                                            <div class="mt-1">
                                                <p class="text-xs font-medium text-zinc-700 dark:text-zinc-300">
                                                    {{ $shiftInfo->shift->start_time }}-{{ $shiftInfo->shift->end_time }}
                                                </p>

                                                @if ($shiftInfo->shift->name)
                                                    <p class="text-xs text-zinc-600 dark:text-zinc-400">
                                                        {{ $shiftInfo->shift->name }}
                                                    </p>
                                                @endif
                                            </div>
                                        @else
                                            <p class="text-xs text-zinc-500 dark:text-zinc-500">{{ __('No shift') }}</p>
                                        @endif
                                    </div>
                                </div>
                            </button>
                        @endif
                    @endfor
                </div>
            @endforeach
        </div>

        <!-- Mobile List View (visible only on mobile) -->
        <div class="md:hidden space-y-3">
            @foreach ($calendar as $week)
                @for ($i = 0; $i < 7; $i++)
                    @php $dayData = $week[$i] ?? null; @endphp
                    @if ($dayData !== null)
                        @php
                            $date = $dayData['date'];
                            $shiftInfo = $this->getShiftForDate($date);
                            $isToday = $date->isToday();
                        @endphp
                        <div
                            wire:key="mobile-admin-day-{{ $date->toDateString() }}-{{ $refreshKey }}"
                            wire:click="selectDate({{ (int) $dayData['day'] }})"
                            @class([
                                'rounded-lg border p-4 cursor-pointer transition',
                                'border-blue-200 bg-blue-50 dark:border-blue-800 dark:bg-blue-900/20' => $shiftInfo,
                                'border-zinc-200 bg-white hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900 dark:hover:bg-zinc-800/50' => !$shiftInfo,
                                'ring-2 ring-blue-400 dark:ring-blue-600' => $isToday,
                            ])
                        >
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                                        <span class="text-lg font-bold text-zinc-900 dark:text-white">{{ $dayData['day'] }}</span>
                                    </div>
                                    <div>
                                        <div class="font-medium text-zinc-900 dark:text-white">
                                            {{ $date->format('l') }}
                                        </div>
                                        <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                            {{ $date->format('F d, Y') }}
                                        </div>
                                    </div>
                                </div>

                                @if ($shiftInfo?->monthlyShift)
                                    <flux:badge color="green" size="sm">
                                        {{ __('Override') }}
                                    </flux:badge>
                                @elseif ($shiftInfo && !$shiftInfo->monthlyShift)
                                    <flux:badge color="blue" size="sm">
                                        {{ __('Weekly') }}
                                    </flux:badge>
                                @endif
                            </div>

                            @if ($shiftInfo)
                                <div class="mt-3 pt-3 border-t border-zinc-200 dark:border-zinc-700">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            @if ($shiftInfo->shift->name)
                                                <div class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                                    {{ $shiftInfo->shift->name }}
                                                </div>
                                            @endif
                                            <div class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">
                                                {{ $shiftInfo->shift->start_time }} - {{ $shiftInfo->shift->end_time }}
                                            </div>
                                        </div>
                                        <flux:button size="xs" variant="ghost" icon="pencil" />
                                    </div>
                                </div>
                            @else
                                <div class="mt-3 pt-3 border-t border-zinc-200 dark:border-zinc-700">
                                    <div class="flex items-center justify-between text-sm text-zinc-500 dark:text-zinc-400">
                                        <span>{{ __('No shift assigned') }}</span>
                                        <flux:button size="xs" variant="ghost" icon="plus" />
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif
                @endfor
            @endforeach
        </div>

        <!-- Legend -->
        <div class="grid grid-cols-1 gap-3 rounded-lg border border-zinc-200 bg-white p-6 shadow-sm sm:grid-cols-3 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex items-center gap-3">
                <div class="size-4 rounded bg-blue-50 ring-1 ring-blue-200 dark:bg-blue-900/20 dark:ring-blue-700"></div>
                <span class="text-sm text-zinc-700 dark:text-zinc-300">{{ __('Scheduled Shift') }}</span>
            </div>
            <div class="flex items-center gap-3">
                <div class="size-4 rounded bg-zinc-50 ring-1 ring-zinc-200 dark:bg-zinc-800/50 dark:ring-zinc-700"></div>
                <span class="text-sm text-zinc-700 dark:text-zinc-300">{{ __('No Shift') }}</span>
            </div>
        </div>
    </div>

    <!-- Shift Assignment Modal -->
    @if ($showModal && $selectedDate)
        <div>
            <!-- Modal Backdrop -->
            <div
                class="fixed inset-0 z-40 bg-black/50 transition-opacity"
                wire:click="closeModal()"
            ></div>

            <!-- Modal -->
            <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="w-full max-w-md max-h-[90vh] rounded-lg border border-zinc-200 bg-white shadow-lg dark:border-zinc-700 dark:bg-zinc-900 flex flex-col">
                    <!-- Header -->
                    <div class="border-b border-zinc-200 px-6 py-4 dark:border-zinc-700 flex-shrink-0">
                        <flux:heading size="lg" level="2">
                            {{ __('Assign Shift') }}
                        </flux:heading>
                        <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                            {{ $selectedDate->format('l, F j, Y') }}
                        </p>
                    </div>

                    <!-- Body - Scrollable -->
                    <div class="flex-1 overflow-y-auto space-y-3 px-6 py-4">
                        <!-- Shift Time Form -->
                        <div class="space-y-3">
                            <!-- Time Fields -->
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label for="custom-start-time" class="block text-xs font-medium text-zinc-700 dark:text-zinc-300">
                                        {{ __('Start Time') }} <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="time"
                                        id="custom-start-time"
                                        wire:model="customStartTime"
                                        class="mt-0.5 block w-full rounded-lg border border-zinc-300 px-2 py-1.5 text-xs text-zinc-900 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white dark:focus:border-blue-400 dark:focus:ring-blue-400"
                                    />
                                    @error('customStartTime')
                                        <span class="text-red-600 text-xs">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div>
                                    <label for="custom-end-time" class="block text-xs font-medium text-zinc-700 dark:text-zinc-300">
                                        {{ __('End Time') }} <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="time"
                                        id="custom-end-time"
                                        wire:model="customEndTime"
                                        class="mt-0.5 block w-full rounded-lg border border-zinc-300 px-2 py-1.5 text-xs text-zinc-900 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white dark:focus:border-blue-400 dark:focus:ring-blue-400"
                                    />
                                    @error('customEndTime')
                                        <span class="text-red-600 text-xs">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="text-sm text-zinc-600 dark:text-zinc-400">
                                {{ __('Leave times empty to remove shift for this day.') }}
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="flex gap-3 border-t border-zinc-200 px-6 py-3 dark:border-zinc-700 flex-shrink-0 justify-between">
                        @php
                            $currentShift = $employee->monthlyShifts->first(fn ($m) => $m->date->isSameDay($selectedDate ?? now()));
                        @endphp
                        @if ($currentShift)
                            <flux:button
                                wire:click="deleteShiftForDate()"
                                variant="danger"
                                size="sm"
                            >
                                {{ __('Delete') }}
                            </flux:button>
                        @else
                            <div></div>
                        @endif

                        <div class="flex gap-2">
                            <flux:button
                                wire:click="closeModal()"
                                variant="ghost"
                                size="sm"
                                class="flex-1"
                            >
                                {{ __('Cancel') }}
                            </flux:button>

                            <flux:button
                                wire:click="assignShiftToDate()"
                                variant="primary"
                                size="sm"
                                class="flex-1"
                            >
                                {{ __('Save') }}
                            </flux:button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
