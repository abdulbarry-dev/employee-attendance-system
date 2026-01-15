<div>

    <!-- 1. Main Stats Boxes - Responsive Grid -->
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 mb-8">
        <!-- Present -->
        <div class="relative overflow-hidden rounded-xl bg-white p-6 shadow-[0_2px_8px_rgba(0,0,0,0.04)] ring-1 ring-zinc-900/5 dark:bg-zinc-800 dark:ring-zinc-100/10 transition-all hover:scale-[1.01]">
            <div class="absolute top-6 right-6">
                <div class="rounded-full bg-green-100 p-2 dark:bg-green-900/30">
                    <svg class="h-5 w-5 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <div class="flex flex-col justify-center pr-12">
                <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Present Now') }}</dt>
                <dd class="mt-2 flex items-baseline gap-2">
                    <span class="text-3xl lg:text-4xl font-bold tracking-tight text-zinc-900 dark:text-white">{{ $stats['present'] }}</span>
                    <span class="text-sm text-zinc-500">{{ __('employee') }}</span>
                </dd>
            </div>
        </div>

        <!-- On Break -->
        <div class="relative overflow-hidden rounded-xl bg-white p-6 shadow-[0_2px_8px_rgba(0,0,0,0.04)] ring-1 ring-zinc-900/5 dark:bg-zinc-800 dark:ring-zinc-100/10 transition-all hover:scale-[1.01]">
            <div class="absolute top-6 right-6">
                <div class="rounded-full bg-amber-100 p-2 dark:bg-amber-900/30">
                    <svg class="h-5 w-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <div class="flex flex-col justify-center pr-12">
                <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('On Break') }}</dt>
                <dd class="mt-2 flex items-baseline gap-2">
                    <span class="text-3xl lg:text-4xl font-bold tracking-tight text-amber-600 dark:text-amber-400">{{ $stats['on_break'] }}</span>
                    <span class="text-sm text-zinc-500">{{ __('employee') }}</span>
                </dd>
            </div>
        </div>

        <!-- Finished -->
        <div class="relative overflow-hidden rounded-xl bg-white p-6 shadow-[0_2px_8px_rgba(0,0,0,0.04)] ring-1 ring-zinc-900/5 dark:bg-zinc-800 dark:ring-zinc-100/10 transition-all hover:scale-[1.01]">
            <div class="absolute top-6 right-6">
                <div class="rounded-full bg-blue-100 p-2 dark:bg-blue-900/30">
                    <svg class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                    </svg>
                </div>
            </div>
            <div class="flex flex-col justify-center pr-12">
                <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Finished Shift') }}</dt>
                <dd class="mt-2 flex items-baseline gap-2">
                    <span class="text-3xl lg:text-4xl font-bold tracking-tight text-blue-600 dark:text-blue-400">{{ $stats['completed'] }}</span>
                    <span class="text-sm text-zinc-500">{{ __('employee') }}</span>
                </dd>
            </div>
        </div>

        <!-- Absent -->
        <div class="relative overflow-hidden rounded-xl bg-white p-6 shadow-[0_2px_8px_rgba(0,0,0,0.04)] ring-1 ring-zinc-900/5 dark:bg-zinc-800 dark:ring-zinc-100/10 transition-all hover:scale-[1.01]">
            <div class="absolute top-6 right-6">
                <div class="rounded-full bg-zinc-100 p-2 dark:bg-zinc-700">
                    <svg class="h-5 w-5 text-zinc-600 dark:text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                    </svg>
                </div>
            </div>
            <div class="flex flex-col justify-center pr-12">
                <dt class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Absent / Late') }}</dt>
                <dd class="mt-2 flex items-baseline gap-2">
                    <span class="text-3xl lg:text-4xl font-bold tracking-tight text-zinc-400">{{ $stats['absent'] }}</span>
                    <span class="text-sm text-zinc-500">{{ __('employee') }}</span>
                </dd>
            </div>
        </div>
    </div>

    <!-- 2. Content Area (Feed Only) -->
    <div class="min-h-[400px]">
        <div class="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-zinc-900/5 dark:bg-zinc-800 dark:ring-zinc-100/10">
                <div class="flex items-center justify-between border-b border-zinc-200 px-6 py-4 dark:border-zinc-700">
                    <h3 class="text-base font-semibold leading-6 text-zinc-900 dark:text-white">{{ __('Real-time Feed') }}</h3>
                    <a href="{{ route('attendance.history') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-500 dark:text-indigo-400 transition-colors">
                        {{ __('View Full History') }} &rarr;
                    </a>
                </div>
                <ul role="list" class="divide-y divide-zinc-100 dark:divide-zinc-700">
                    @forelse($latestActivities as $activity)
                        <li class="relative flex items-center justify-between gap-x-6 px-6 py-4 hover:bg-zinc-50 dark:hover:bg-zinc-700/50 transition-colors">
                            <div class="flex min-w-0 gap-x-4">
                            <div class="h-10 w-10 flex-none rounded-full bg-zinc-100 border border-zinc-200 flex items-center justify-center font-bold text-zinc-500 dark:bg-zinc-700 dark:border-zinc-600 dark:text-zinc-300">
                                {{ $activity->user->initials() }}
                            </div>
                            <div class="min-w-0 flex-auto">
                                <p class="text-sm font-semibold leading-6 text-zinc-900 dark:text-white">
                                    {{ $activity->user->name }}
                                </p>
                                <p class="truncate text-xs leading-5 text-zinc-500 dark:text-zinc-400">
                                    {{ $activity->user->email }}
                                </p>
                            </div>
                        </div>
                        <div class="flex flex-col items-end">
                            <p class="text-sm leading-6 text-zinc-500 dark:text-zinc-400">
                                {{ $activity->updated_at->diffForHumans() }}
                            </p>
                        </div>
                    </li>
                @empty
                    <li class="px-6 py-14 text-center">
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-zinc-100 dark:bg-zinc-800">
                             <svg class="h-6 w-6 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="mt-2 text-sm font-semibold text-zinc-900 dark:text-white">{{ __('No activities yet') }}</h3>
                        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ __('Attendance records will appear here as they happen.') }}</p>
                    </li>
                @endforelse
            </ul>
        </div>
    </div>
</div>
