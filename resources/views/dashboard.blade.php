<x-layouts.app :title="__('Dashboard')">
    <div class="space-y-6">
        <!-- Welcome Header -->
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-white">
                {{ __('Welcome back') }}, {{ auth()->user()->first_name }}
            </h1>
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
                {{ __('Here\'s what\'s happening with your attendance system today.') }}
            </p>
        </div>

        <!-- Stats Grid -->
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
            <!-- Total Employees -->
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 p-6">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Total Employees') }}</p>
                        <p class="mt-2 text-3xl font-bold text-zinc-900 dark:text-white">
                            {{ \App\Models\User::role('employee')->whereDoesntHave('roles', fn($q) => $q->where('name', 'admin'))->count() }}
                        </p>
                    </div>
                    <div class="flex-shrink-0 rounded-full bg-blue-100 p-3 dark:bg-blue-900/30">
                        <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Present Today -->
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 p-6">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Present Today') }}</p>
                        <p class="mt-2 text-3xl font-bold text-zinc-900 dark:text-white">
                            {{ \App\Models\Attendance::where('date', today())->where('status', 'present')->count() }}
                        </p>
                    </div>
                    <div class="flex-shrink-0 rounded-full bg-green-100 p-3 dark:bg-green-900/30">
                        <svg class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- On Break -->
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 p-6">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('On Break') }}</p>
                        <p class="mt-2 text-3xl font-bold text-zinc-900 dark:text-white">
                            {{ \App\Models\Attendance::where('date', today())->where('status', 'on_break')->count() }}
                        </p>
                    </div>
                    <div class="flex-shrink-0 rounded-full bg-amber-100 p-3 dark:bg-amber-900/30">
                        <svg class="h-6 w-6 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Completed Shifts -->
            <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 p-6">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ __('Completed') }}</p>
                        <p class="mt-2 text-3xl font-bold text-zinc-900 dark:text-white">
                            {{ \App\Models\Attendance::where('date', today())->whereNotNull('check_out')->count() }}
                        </p>
                    </div>
                    <div class="flex-shrink-0 rounded-full bg-purple-100 p-3 dark:bg-purple-900/30">
                        <svg class="h-6 w-6 text-purple-600 dark:text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800 p-6">
            <h2 class="text-lg font-semibold text-zinc-900 dark:text-white mb-4">{{ __('Quick Actions') }}</h2>
            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <a href="{{ route('employees.index') }}" class="flex items-center gap-3 rounded-lg border border-zinc-200 dark:border-zinc-700 p-4 transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-700/50">
                    <div class="rounded-lg bg-blue-100 p-2 dark:bg-blue-900/30">
                        <svg class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="font-medium text-zinc-900 dark:text-white">{{ __('Employees') }}</p>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Manage team') }}</p>
                    </div>
                </a>

                <a href="{{ route('office.kiosk') }}" class="flex items-center gap-3 rounded-lg border border-zinc-200 dark:border-zinc-700 p-4 transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-700/50">
                    <div class="rounded-lg bg-green-100 p-2 dark:bg-green-900/30">
                        <svg class="h-5 w-5 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                        </svg>
                    </div>
                    <div>
                        <p class="font-medium text-zinc-900 dark:text-white">{{ __('Office Kiosk') }}</p>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('View QR code') }}</p>
                    </div>
                </a>

                <a href="{{ route('attendance.monitor') }}" class="flex items-center gap-3 rounded-lg border border-zinc-200 dark:border-zinc-700 p-4 transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-700/50">
                    <div class="rounded-lg bg-purple-100 p-2 dark:bg-purple-900/30">
                        <svg class="h-5 w-5 text-purple-600 dark:text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                        </svg>
                    </div>
                    <div>
                        <p class="font-medium text-zinc-900 dark:text-white">{{ __('Monitor') }}</p>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Live activity') }}</p>
                    </div>
                </a>

                <a href="{{ route('attendance.history') }}" class="flex items-center gap-3 rounded-lg border border-zinc-200 dark:border-zinc-700 p-4 transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-700/50">
                    <div class="rounded-lg bg-amber-100 p-2 dark:bg-amber-900/30">
                        <svg class="h-5 w-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div>
                        <p class="font-medium text-zinc-900 dark:text-white">{{ __('History') }}</p>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('View logs') }}</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
</x-layouts.app>
