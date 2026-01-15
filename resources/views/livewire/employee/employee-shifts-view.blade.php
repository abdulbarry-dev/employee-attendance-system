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
                <flux:subheading>{{ __('Manage monthly shift schedule') }}</flux:subheading>
            </div>
        </div>

        <flux:spacer />

        <div class="flex items-center gap-4">
            <flux:button
                href="{{ route('employees.monthly-schedule', $employee) }}"
                wire:navigate
                variant="primary"
                icon="calendar-days"
            >
                {{ __('Monthly Schedule') }}
            </flux:button>

            <flux:avatar
                size="lg"
                :name="$employee->first_name . ' ' . $employee->last_name"
                :initials="$employee->initials()"
            />
        </div>
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
        </div>

        <!-- Calendar Grid - Desktop View (hidden on mobile) -->
        <div class="hidden md:block rounded-lg border border-zinc-200 bg-white shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
            <!-- Day Headers -->
            <div class="grid grid-cols-7 border-b border-zinc-200 dark:border-zinc-700">
                @foreach (['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $day)
                    <div class="p-4 text-center font-semibold text-zinc-700 dark:text-zinc-300 text-sm bg-zinc-50 dark:bg-zinc-800">{{ __($day) }}</div>
                @endforeach
            </div>

            <!-- Calendar Days -->
            @foreach ($calendar as $weekIndex => $week)
                <div class="grid grid-cols-7">
                    @foreach ($week as $dayIndex => $cell)
                        @php
                            $shiftInfo = $cell && $cell['isCurrentMonth'] ? $this->getShiftForDate($cell['date']) : null;
                            $cellKey = $cell ? $cell['date']->toDateString() : "empty-{$weekIndex}-{$dayIndex}";
                        @endphp
                        <div class="min-h-24 p-3 border-r border-b border-zinc-200 dark:border-zinc-700 last:border-r-0 transition"
                             wire:key="desktop-{{ $cellKey }}-{{ $refreshKey }}"
                             @if ($cell && $cell['isCurrentMonth']) wire:click="selectDate({{ (int) $cell['day'] }})" @endif
                             @class([
                                 'cursor-pointer hover:bg-zinc-50 dark:hover:bg-zinc-800/50' => $cell && $cell['isCurrentMonth'],
                                 'bg-blue-50 dark:bg-blue-900/20' => $shiftInfo,
                                 'bg-white dark:bg-zinc-900' => !$shiftInfo && $cell && $cell['isCurrentMonth'],
                                 'bg-zinc-50 dark:bg-zinc-800 cursor-default' => !$cell || !$cell['isCurrentMonth'],
                             ])>
                            @if ($cell && $cell['isCurrentMonth'])
                                <div class="font-semibold text-zinc-900 dark:text-white mb-2 text-sm">{{ $cell['day'] }}</div>

                                @if ($shiftInfo)
                                    <div class="text-xs p-1.5 rounded font-medium truncate mb-1 bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300">
                                        {{ $shiftInfo->shift->name }}
                                    </div>
                                    <div class="text-xs text-zinc-600 dark:text-zinc-400">
                                        {{ $shiftInfo->shift->start_time }} - {{ $shiftInfo->shift->end_time }}
                                    </div>
                                @endif
                            @endif
                        </div>
                    @endforeach
                </div>
            @endforeach
        </div>

        <!-- Mobile List View (visible only on mobile) -->
        <div class="md:hidden space-y-3">
            @foreach ($calendar as $weekIndex => $week)
                @foreach ($week as $dayIndex => $cell)
                    @if ($cell && $cell['isCurrentMonth'])
                        @php
                            $shiftInfo = $this->getShiftForDate($cell['date']);
                        @endphp
                        <div
                            wire:key="mobile-{{ $cell['date']->toDateString() }}-{{ $refreshKey }}"
                            wire:click="selectDate({{ (int) $cell['day'] }})"
                            @class([
                                'rounded-lg border p-4 cursor-pointer transition',
                                'border-blue-200 bg-blue-50 dark:border-blue-800 dark:bg-blue-900/20' => $shiftInfo,
                                'border-zinc-200 bg-white hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900 dark:hover:bg-zinc-800/50' => !$shiftInfo,
                            ])
                        >
                            <div class="flex items-center justify-between mb-2">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                                        <span class="text-lg font-bold text-zinc-900 dark:text-white">{{ $cell['day'] }}</span>
                                    </div>
                                    <div>
                                        <div class="font-medium text-zinc-900 dark:text-white">
                                            {{ $cell['date']->format('l') }}
                                        </div>
                                        <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                            {{ $cell['date']->format('F d, Y') }}
                                        </div>
                                    </div>
                                </div>

                                @if ($shiftInfo)
                                    <flux:badge color="blue" size="sm">
                                        {{ __('Shift Set') }}
                                    </flux:badge>
                                @endif
                            </div>

                            @if ($shiftInfo)
                                <div class="mt-3 pt-3 border-t border-zinc-200 dark:border-zinc-700">
                                    <div>
                                        <div class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
                                            {{ $shiftInfo->shift->name }}
                                        </div>
                                        <div class="text-xs text-zinc-500 dark:text-zinc-400 mt-1">
                                            {{ $shiftInfo->shift->start_time }} - {{ $shiftInfo->shift->end_time }}
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="mt-3 pt-3 border-t border-zinc-200 dark:border-zinc-700">
                                    <div class="text-sm text-zinc-500 dark:text-zinc-400">
                                        {{ __('No shift assigned') }}
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif
                @endforeach
            @endforeach
        </div>

    </div>

    <!-- Modal -->
    @if ($showModal && $selectedDate)
        <flux:modal open="{{ $showModal }}" on-close="closeModal">
            <div class="flex flex-col max-h-[90vh]">
                <!-- Header -->
                <div class="flex-shrink-0 px-6 py-4 border-b border-zinc-200 dark:border-zinc-700">
                    <flux:heading size="lg">
                        {{ __('Shift for') }} {{ $selectedDate->format('d M Y') }}
                    </flux:heading>
                </div>

                <!-- Body (Scrollable) -->
                <div class="flex-1 overflow-y-auto px-6 py-4 space-y-4">
                    <!-- Shift Time Form -->
                    <div class="grid grid-cols-2 gap-3">
                        <flux:field>
                            <flux:label>{{ __('Start Time') }}</flux:label>
                            <flux:input wire:model="customStartTime" type="time" size="sm" />
                            @error('customStartTime')
                                <span class="text-red-600 text-xs">{{ $message }}</span>
                            @enderror
                        </flux:field>
                        <flux:field>
                            <flux:label>{{ __('End Time') }}</flux:label>
                            <flux:input wire:model="customEndTime" type="time" size="sm" />
                            @error('customEndTime')
                                <span class="text-red-600 text-xs">{{ $message }}</span>
                            @enderror
                        </flux:field>
                    </div>
                    <div class="text-sm text-zinc-600 dark:text-zinc-400">
                        {{ __('Leave times empty to remove shift for this day.') }}
                    </div>
                </div>

                <!-- Footer -->
                <div class="flex-shrink-0 px-6 py-4 border-t border-zinc-200 dark:border-zinc-700 flex gap-2 justify-between">
                    <flux:button wire:click="deleteShiftForDate" variant="danger" size="sm">{{ __('Delete') }}</flux:button>
                    <div class="flex gap-2">
                        <flux:button wire:click="closeModal" variant="subtle" size="sm">{{ __('Cancel') }}</flux:button>
                        <flux:button wire:click="assignShiftToDate" variant="primary" size="sm">{{ __('Save Shift') }}</flux:button>
                    </div>
                </div>
            </div>
        </flux:modal>
    @endif
</div>
