<div class="space-y-6">
    <!-- Stats Grid - Responsive -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Present -->
        <div class="overflow-hidden rounded-xl bg-white p-6 shadow-sm ring-1 ring-zinc-900/5 dark:bg-zinc-800 dark:ring-zinc-100/10">
            <dt class="truncate text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Present Now') }}</dt>
            <dd class="mt-2 text-3xl lg:text-4xl font-semibold text-zinc-900 dark:text-white">{{ $stats['present'] }}</dd>
        </div>

        <!-- On Break -->
        <div class="overflow-hidden rounded-xl bg-white p-6 shadow-sm ring-1 ring-zinc-900/5 dark:bg-zinc-800 dark:ring-zinc-100/10">
            <dt class="truncate text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('On Break') }}</dt>
            <dd class="mt-2 text-3xl lg:text-4xl font-semibold text-amber-600 dark:text-amber-400">{{ $stats['on_break'] }}</dd>
        </div>

        <!-- Finished -->
        <div class="overflow-hidden rounded-xl bg-white p-6 shadow-sm ring-1 ring-zinc-900/5 dark:bg-zinc-800 dark:ring-zinc-100/10">
            <dt class="truncate text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Finished Shift') }}</dt>
            <dd class="mt-2 text-3xl lg:text-4xl font-semibold text-blue-600 dark:text-blue-400">{{ $stats['completed'] }}</dd>
        </div>

        <!-- Absent -->
        <div class="overflow-hidden rounded-xl bg-white p-6 shadow-sm ring-1 ring-zinc-900/5 dark:bg-zinc-800 dark:ring-zinc-100/10">
            <dt class="truncate text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Absent / Not In') }}</dt>
            <dd class="mt-2 text-3xl lg:text-4xl font-semibold text-zinc-400">{{ $stats['absent'] }}</dd>
        </div>
    </div>

    <!-- Live Feed -->
    <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-zinc-900/5 dark:bg-zinc-800 dark:ring-zinc-100/10">
        <div class="border-b border-zinc-200 px-6 py-4 dark:border-zinc-700">
            <h3 class="text-base font-semibold leading-6 text-zinc-900 dark:text-white">{{ __('Latest Activity') }}</h3>
        </div>
        <ul role="list" class="divide-y divide-zinc-100 dark:divide-zinc-700">
            @forelse($latestAttendances as $attendance)
                <li class="relative flex gap-x-4 px-6 py-4 hover:bg-zinc-50 dark:hover:bg-zinc-700/50">
                    <div class="flex-auto min-w-0">
                        <div class="flex items-baseline justify-between gap-x-4">
                            <p class="text-sm font-semibold leading-6 text-zinc-900 dark:text-white">
                                {{ $attendance->user->first_name }} {{ $attendance->user->last_name }}
                            </p>
                            <p class="flex-none text-xs text-zinc-500 dark:text-zinc-400">
                                {{ $attendance->updated_at->diffForHumans() }}
                            </p>
                        </div>
                        <p class="mt-1 truncate text-xs leading-5 text-zinc-500 dark:text-zinc-400">
                            Status: <span class="capitalize font-medium {{ $attendance->status === 'on_break' ? 'text-amber-500' : 'text-emerald-500' }}">{{ str_replace('_', ' ', $attendance->status) }}</span>
                        </p>
                    </div>
                </li>
            @empty
                <li class="px-6 py-4 text-center text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('No activity recorded today.') }}
                </li>
            @endforelse
        </ul>
    </div>
</div>
