@props([
    'wireModel' => null,
    'placeholder' => 'Select an option',
    'icon' => null,
    'options' => [],
])

<div class="relative">
    @if($icon)
        <div class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2">
            <svg class="size-5 text-neutral-400 dark:text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                @if($icon === 'funnel')
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                @elseif($icon === 'adjustments-horizontal')
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                @endif
            </svg>
        </div>
    @endif

    <select
        @if($wireModel)
            wire:model.live="{{ $wireModel }}"
        @endif
        {{ $attributes->merge([
            'class' => 'w-full appearance-none rounded-lg border border-neutral-200 bg-white text-sm font-medium text-neutral-900 shadow-sm transition-all duration-150 hover:border-neutral-300 focus:border-neutral-400 focus:outline-none focus:ring-4 focus:ring-neutral-200/50 disabled:cursor-not-allowed disabled:opacity-50 dark:border-neutral-700 dark:bg-neutral-800 dark:text-white dark:hover:border-neutral-600 dark:focus:border-neutral-500 dark:focus:ring-neutral-700/50 dark:[color-scheme:dark] ' . ($icon ? 'pl-10 pr-10 py-2' : 'px-3.5 pr-10 py-2')
        ]) }}
    >
        {{ $slot }}
    </select>

    <!-- Chevron Icon -->
    <div class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2">
        <svg class="size-5 text-neutral-400 transition-transform dark:text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </div>
</div>
