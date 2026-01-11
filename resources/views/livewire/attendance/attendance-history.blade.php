<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-white">{{ __('Attendance Log') }}</h1>
        <!-- Add Filters here if needed -->
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
                                    <span class="text-zinc-400 dark:text-zinc-600">-</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-zinc-500 dark:text-zinc-400 font-mono">
                                @if($attendance->actual_work_duration > 0)
                                    {{ intdiv($attendance->actual_work_duration, 60) }}h {{ $attendance->actual_work_duration % 60 }}m
                                @else
                                    -
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
