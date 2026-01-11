<div class="max-w-md mx-auto py-12 px-4 sm:px-0">
    <!-- Header / Clock -->
    <div class="text-center mb-8" wire:poll.1s>
        <h2 class="text-xs font-semibold tracking-widest text-zinc-500 uppercase dark:text-zinc-400">
            {{ now()->format('l, F j') }}
        </h2>
        <div class="mt-2 text-6xl font-bold tracking-tight text-zinc-900 dark:text-white font-mono">
            {{ now()->format('H:i:s') }}
        </div>
        <div class="mt-4 flex items-center justify-center gap-2">
            <div @class([
                'h-2.5 w-2.5 rounded-full',
                'bg-emerald-500' => $attendance && !$attendance->check_out && $attendance->status !== 'on_break',
                'bg-amber-500' => $attendance && $attendance->status === 'on_break',
                'bg-zinc-300 dark:bg-zinc-600' => !$attendance || $attendance->check_out,
            ])></div>
            <span class="text-sm font-medium text-zinc-600 dark:text-zinc-300">
                @if(!$attendance)
                    {{ __('Not Checked In') }}
                @elseif($attendance->check_out)
                    {{ __('Shift Completed') }}
                @elseif($attendance->status === 'on_break')
                    {{ __('On Break') }}
                @else
                    {{ __('Working') }}
                @endif
            </span>
        </div>
    </div>

    <!-- Actions -->
    <div class="space-y-4">
        @if(!$attendance)
            <!-- CHECK IN -->
            <button
                wire:click="checkIn"
                class="w-full group relative flex items-center justify-center gap-3 rounded-2xl bg-zinc-900 px-6 py-6 transition-all hover:bg-zinc-800 active:scale-95 dark:bg-white dark:hover:bg-zinc-200"
            >
                <div class="rounded-full bg-white/20 p-2 dark:bg-zinc-900/10">
                    <svg class="h-6 w-6 text-white dark:text-zinc-900" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                    </svg>
                </div>
                <span class="text-xl font-bold text-white dark:text-zinc-900">{{ __('Check In') }}</span>
            </button>

        @elseif(!$attendance->check_out)

            @if($currentBreak)
                <!-- END BREAK -->
                <button
                    wire:click="endBreak"
                    class="w-full group relative flex items-center justify-center gap-3 rounded-2xl bg-emerald-600 px-6 py-5 transition-all hover:bg-emerald-500 active:scale-95"
                >
                    <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="text-xl font-bold text-white">{{ __('Resume Work') }}</span>
                </button>
            @else
                <!-- START BREAK & CHECK OUT -->
                <button
                    wire:click="startBreak('lunch')"
                    class="w-full flex items-center justify-center gap-3 rounded-2xl bg-amber-100 p-6 transition-all hover:bg-amber-200 active:scale-95 dark:bg-amber-900/30 dark:hover:bg-amber-900/50"
                >
                    <svg class="h-8 w-8 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="text-xl font-bold text-amber-900 dark:text-amber-100">{{ __('Start Break') }}</span>
                </button>

                <div class="mt-4 pt-4 border-t border-zinc-200 dark:border-zinc-800">
                    <button
                        wire:click="checkOut"
                        wire:confirm="{{ __('Are you sure you want to end your shift?') }}"
                        class="w-full flex items-center justify-center gap-2 rounded-xl border-2 border-red-100 bg-red-50 p-4 text-red-600 transition-all hover:bg-red-100 hover:border-red-200 dark:bg-red-900/10 dark:border-red-900/20 dark:text-red-400 dark:hover:bg-red-900/30"
                    >
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                        <span class="font-semibold">{{ __('Check Out') }}</span>
                    </button>
                </div>
            @endif

        @else
            <!-- SUMMARY / FINISHED -->
            <div class="rounded-2xl bg-zinc-100 p-6 text-center dark:bg-zinc-800">
                <svg class="mx-auto h-12 w-12 text-zinc-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <h3 class="mt-4 text-lg font-medium text-zinc-900 dark:text-white">{{ __('You are all set!') }}</h3>
                <p class="mt-2 text-zinc-500 dark:text-zinc-400">
                    {{ __('Shift Duration:') }} <span class="font-mono font-bold text-zinc-700 dark:text-zinc-300">{{ intdiv($attendance->work_duration, 60) }}h {{ $attendance->work_duration % 60 }}m</span>
                </p>
                <p class="mt-1 text-sm text-zinc-400">
                    {{ __('Have a good rest.') }}
                </p>
            </div>
        @endif
    </div>

    <!-- Geolocation -->
    <script>
        document.addEventListener('livewire:initialized', () => {
            if ("geolocation" in navigator) {
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        @this.set('latitude', position.coords.latitude);
                        @this.set('longitude', position.coords.longitude);
                    },
                    (error) => {
                        console.error("Error Code = " + error.code + " - " + error.message);
                        @this.set('error', error.message);
                    }
                );
            }
        });
    </script>
</div>
