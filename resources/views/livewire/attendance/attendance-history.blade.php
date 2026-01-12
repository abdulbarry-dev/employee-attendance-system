<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-white">{{ __('Attendance Log') }}</h1>
    </div>

    <div class="mb-6 space-y-4">
        <!-- Search Bar (Full Width) -->
        <flux:input
            wire:model.live.debounce.300ms="search"
            placeholder="{{ __('Name or email...') }}"
            icon="magnifying-glass"
            label="{{ __('Search Employee') }}"
        />

        <!-- Filters Row -->
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 items-end">
            <flux:dropdown position="bottom" align="start">
                <flux:button class="w-full" icon="funnel">{{ $status ?: __('All Statuses') }}</flux:button>

                <flux:menu>
                    <flux:menu.item wire:click="$set('status', '')">{{ __('All Statuses') }}</flux:menu.item>
                    <flux:menu.separator />
                    <flux:menu.item wire:click="$set('status', 'present')" icon="check-circle">{{ __('Present') }}</flux:menu.item>
                    <flux:menu.item wire:click="$set('status', 'late')" icon="clock">{{ __('Late') }}</flux:menu.item>
                    <flux:menu.item wire:click="$set('status', 'on_break')" icon="pause-circle">{{ __('On Break') }}</flux:menu.item>
                    <flux:menu.item wire:click="$set('status', 'absent')" icon="x-circle">{{ __('Absent') }}</flux:menu.item>
                    <flux:menu.item wire:click="$set('status', 'left_early')" icon="arrow-right-start-on-rectangle">{{ __('Left Early') }}</flux:menu.item>
                </flux:menu>
            </flux:dropdown>

            <flux:button wire:click="clearFilters" variant="ghost" icon="arrow-path" class="w-full">
                {{ __('Clear All') }}
            </flux:button>
        </div>

        <!-- Date Range Filters -->
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-zinc-50/50 dark:bg-zinc-800/30 p-4">
            <flux:input
                wire:model.live="startDate"
                type="date"
                label="{{ __('Start Date') }}"
                icon="calendar"
            />

            <flux:input
                wire:model.live="endDate"
                type="date"
                label="{{ __('End Date') }}"
                icon="calendar"
            />
        </div>
    </div>

    <div class="overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800">
        <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                <thead class="bg-zinc-50 dark:bg-zinc-800">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Employee') }}</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Date') }}</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Check In') }}</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Check Out') }}</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Break Time') }}</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">{{ __('Duration') }}</th>

                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700 bg-white dark:bg-zinc-800">
                    @forelse ($attendances as $attendance)
                        <tr class="transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-700/50">
                            <td class="whitespace-nowrap px-6 py-4">
                                <div class="flex items-center">
                                    <div class="h-8 w-8 rounded-full bg-zinc-200 dark:bg-zinc-700 flex items-center justify-center text-xs font-bold text-zinc-500 dark:text-zinc-300">
                                        {{ substr($attendance->user->first_name, 0, 1) }}{{ substr($attendance->user->last_name, 0, 1) }}
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-zinc-900 dark:text-white">{{ $attendance->user->first_name }} {{ $attendance->user->last_name }}</div>
                                        <div class="text-xs text-zinc-500 dark:text-zinc-400">{{ $attendance->user->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-zinc-500 dark:text-zinc-400">
                                {{ $attendance->date->format('M d, Y') }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-zinc-900 dark:text-white">
                                {{ $attendance->check_in->format('H:i') }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-zinc-500 dark:text-zinc-400">
                                {{ $attendance->check_out ? $attendance->check_out->format('H:i') : '--:--' }}
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm font-mono">
                                @if($attendance->total_break_duration > 0)
                                    <span class="text-amber-600 dark:text-amber-400">
                                        {{ intdiv($attendance->total_break_duration, 60) }}h {{ $attendance->total_break_duration % 60 }}m
                                    </span>
                                @else
                                    <span class="text-zinc-400 dark:text-zinc-600">--</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-zinc-500 dark:text-zinc-400 font-mono">
                                @if($attendance->check_out && $attendance->check_in)
                                    @php
                                        $workDuration = $attendance->actual_work_duration;
                                        $totalMinutes = $attendance->check_out->diffInMinutes($attendance->check_in);
                                    @endphp
                                    @if($workDuration > 0)
                                        {{ intdiv($workDuration, 60) }}h {{ $workDuration % 60 }}m
                                    @else
                                        {{ intdiv($totalMinutes, 60) }}h {{ $totalMinutes % 60 }}m
                                    @endif
                                @else
                                    --
                                @endif
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-zinc-500 dark:text-zinc-400">
                                {{ __('No attendance records found.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
    </div>

    <div class="mt-4">
        {{ $attendances->links() }}
    </div>
</div>
