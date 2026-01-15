<div>
    <!-- Modal Backdrop -->
    <div
        class="fixed inset-0 z-40 bg-black/50 transition-opacity"
        wire:click="$parent.closeModal()"
    ></div>

    <!-- Modal -->
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
        <div class="w-full max-w-md rounded-lg border border-zinc-200 bg-white shadow-lg dark:border-zinc-700 dark:bg-zinc-900">
            <!-- Header -->
            <div class="border-b border-zinc-200 px-6 py-4 dark:border-zinc-700">
                <flux:heading size="lg" level="2">
                    {{ __('Assign Shift') }}
                </flux:heading>
                <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                    {{ $selectedDate->format('l, F j, Y') }}
                </p>
            </div>

            <!-- Body -->
            <div class="space-y-4 px-6 py-4">
                <!-- Current Weekly Shift Info -->
                @php
                    $dayOfWeek = strtolower($selectedDate->format('l'));
                    $weeklyShift = $employee->shifts->first(fn ($s) => $s->day_of_week === $dayOfWeek);
                @endphp

                @if ($weeklyShift)
                    <div class="rounded-lg bg-blue-50 p-4 dark:bg-blue-900/20">
                        <p class="text-xs font-medium uppercase tracking-wide text-blue-700 dark:text-blue-300">
                            {{ __('Weekly Pattern') }}
                        </p>
                        <p class="mt-2 font-medium text-blue-900 dark:text-blue-100">
                            {{ $weeklyShift->start_time->format('H:i') }}-{{ $weeklyShift->end_time->format('H:i') }}
                        </p>
                        @if ($weeklyShift->name)
                            <p class="text-sm text-blue-700 dark:text-blue-300">
                                {{ $weeklyShift->name }}
                            </p>
                        @endif
                    </div>
                @else
                    <div class="rounded-lg bg-zinc-50 p-4 dark:bg-zinc-800/50">
                        <p class="text-xs font-medium uppercase tracking-wide text-zinc-600 dark:text-zinc-400">
                            {{ __('Weekly Pattern') }}
                        </p>
                        <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                            {{ __('No weekly shift configured') }}
                        </p>
                    </div>
                @endif

                <!-- Shift Selection -->
                <div>
                    <label for="shift-select" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                        {{ __('Select Shift Override') }}
                    </label>
                    <select
                        id="shift-select"
                        wire:model="$parent.selectedShiftId"
                        class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-zinc-900 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white dark:focus:border-blue-400 dark:focus:ring-blue-400"
                    >
                        <option value="">{{ __('-- Use Weekly Pattern --') }}</option>
                        <option value="">{{ __('-- No Shift --') }}</option>

                        @foreach ($employee->shifts as $shift)
                            <option value="{{ $shift->id }}">
                                {{ $shift->start_time->format('H:i') }}-{{ $shift->end_time->format('H:i') }}
                                @if ($shift->name)
                                    ({{ $shift->name }})
                                @endif
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Notes -->
                <div>
                    <label for="notes" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                        {{ __('Notes (Optional)') }}
                    </label>
                    <textarea
                        id="notes"
                        wire:model="$parent.selectedNotes"
                        rows="2"
                        placeholder="{{ __('Add notes for this shift override...') }}"
                        class="mt-1 block w-full rounded-lg border border-zinc-300 px-3 py-2 text-zinc-900 shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white dark:focus:border-blue-400 dark:focus:ring-blue-400"
                    ></textarea>
                </div>
            </div>

            <!-- Footer -->
            <div class="flex gap-3 border-t border-zinc-200 px-6 py-4 dark:border-zinc-700">
                <flux:button
                    wire:click="$parent.closeModal()"
                    variant="ghost"
                    class="flex-1"
                >
                    {{ __('Cancel') }}
                </flux:button>

                <flux:button
                    wire:click="$parent.assignShiftToDate()"
                    variant="primary"
                    class="flex-1"
                >
                    {{ __('Save') }}
                </flux:button>
            </div>
        </div>
    </div>
</div>
