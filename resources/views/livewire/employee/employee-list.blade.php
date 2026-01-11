<div>
    <flux:header>
        <div>
            <flux:heading size="xl">{{ __('Employees') }}</flux:heading>
            <flux:subheading>{{ __('Manage your team members and their access') }}</flux:subheading>
        </div>

        <flux:spacer />

        <flux:button :href="route('employees.create')" wire:navigate icon="user-plus" variant="primary">
            {{ __('Add Employee') }}
        </flux:button>
    </flux:header>

    <div class="mt-6 space-y-6">
        <!-- Search and Filters -->
        <div class="mb-6 flex flex-col gap-4 md:flex-row md:items-end">
            <div class="flex-1">
                <flux:input
                    wire:model.live.debounce.300ms="search"
                    placeholder="{{ __('Search employees by name, email, or phone...') }}"
                    icon="magnifying-glass"
                />
            </div>

            <div class="md:w-56">
                <x-select-filter wire-model="statusFilter" icon="funnel">
                    <option value="all">{{ __('All Employees') }}</option>
                    <option value="active">{{ __('Active Only') }}</option>
                    <option value="banned">{{ __('Banned Only') }}</option>
                </x-select-filter>
            </div>
        </div>

        <!-- Table or Empty State -->
        @if($employees->count() > 0)
            <div class="overflow-hidden rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                        <thead class="bg-zinc-50 dark:bg-zinc-800">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-700 dark:text-zinc-300">
                                    {{ __('Employee') }}
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-700 dark:text-zinc-300">
                                    {{ __('Contact') }}
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-700 dark:text-zinc-300">
                                    {{ __('Status') }}
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-700 dark:text-zinc-300">
                                    {{ __('Actions') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                            @foreach($employees as $employee)
                                <tr class="transition-colors hover:bg-zinc-50 dark:hover:bg-zinc-800/70">
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <flux:avatar
                                                size="lg"
                                                :name="$employee->first_name . ' ' . $employee->last_name"
                                                :initials="$employee->initials()"
                                            />
                                            <div>
                                                <div class="font-medium text-zinc-900 dark:text-white">
                                                    {{ $employee->first_name }} {{ $employee->last_name }}
                                                </div>
                                                <div class="text-sm text-zinc-500 dark:text-zinc-400">
                                                    {{ $employee->email }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <div class="text-sm">
                                            @if($employee->phone_number)
                                                <div class="flex items-center gap-2 text-zinc-600 dark:text-zinc-300">
                                                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                                    </svg>
                                                    {{ $employee->phone_number }}
                                                </div>
                                            @else
                                                <span class="text-zinc-400 dark:text-zinc-500">—</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        @if($employee->is_banned)
                                            <flux:badge color="red" size="sm" icon="no-symbol">
                                                {{ __('Banned') }}
                                            </flux:badge>
                                        @else
                                            <flux:badge color="green" size="sm" icon="check-circle">
                                                {{ __('Active') }}
                                            </flux:badge>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <div class="flex items-center gap-2">
                                            <flux:button :href="route('employees.edit', $employee)" wire:navigate size="sm" variant="ghost" icon="pencil" tooltip="{{ __('Edit employee') }}" />

                                            @if($employee->is_banned)
                                                <flux:button wire:click="unban({{ $employee->id }})" size="sm" variant="ghost" icon="check-circle" tooltip="{{ __('Unban employee') }}" />
                                            @else
                                                <flux:button wire:click="ban({{ $employee->id }})" size="sm" variant="ghost" icon="no-symbol" tooltip="{{ __('Ban employee') }}" />
                                            @endif

                                            <flux:button
                                                wire:click="confirmDelete({{ $employee->id }})"
                                                size="sm"
                                                variant="danger"
                                                icon="trash"
                                                tooltip="{{ __('Delete employee') }}"
                                            />
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                <!-- Pagination -->
                @if($employees->hasPages())
                    <div class="border-t border-zinc-200 bg-white px-4 py-3 dark:border-zinc-700 dark:bg-zinc-900">
                        {{ $employees->links() }}
                    </div>
                @endif
            </div>
        @else
            <!-- Clean Empty State -->
            <div class="flex min-h-[400px] items-center justify-center rounded-xl border-2 border-dashed border-zinc-300 bg-zinc-50/50 dark:border-zinc-700 dark:bg-zinc-900/50">
                <div class="mx-auto max-w-md px-6 text-center">
                    <div class="mb-4 inline-flex rounded-full bg-zinc-100 p-4 dark:bg-zinc-800">
                        <svg class="size-10 text-zinc-400 dark:text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>

                    <h3 class="mb-2 text-xl font-semibold text-zinc-900 dark:text-white">
                        @if($search || $statusFilter !== 'all')
                            {{ __('No employees found') }}
                        @else
                            {{ __('No employees yet') }}
                        @endif
                    </h3>

                    <p class="mb-6 text-sm leading-relaxed text-zinc-600 dark:text-zinc-400">
                        @if($search || $statusFilter !== 'all')
                            {{ __('No employees match your current search or filter criteria. Try adjusting your filters or search terms.') }}
                        @else
                            {{ __('Get started by adding your first employee to the system. You can manage their access and track their activity.') }}
                        @endif
                    </p>

                    @if(!$search && $statusFilter === 'all')
                        <flux:button :href="route('employees.create')" wire:navigate icon="user-plus" variant="primary">
                            {{ __('Add Your First Employee') }}
                        </flux:button>
                    @endif
                </div>
            </div>

        @endif
    </div>

    <!-- Delete Confirmation Modal -->
    <x-modal wire:model="showDeleteModal" maxWidth="md">
        <div class="p-6">
            <div class="mb-5 flex items-center justify-center">
                <div class="rounded-full bg-red-100 p-3 dark:bg-red-900/30">
                    <svg class="size-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
            </div>

            <div class="text-center">
                <h3 class="text-lg font-medium text-zinc-900 dark:text-white">
                    {{ __('Delete Employee') }}
                </h3>
                <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
                    {{ __('Are you sure you want to delete this employee? This action cannot be undone.') }}
                </p>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <flux:button wire:click="$set('showDeleteModal', false)" variant="ghost">
                    {{ __('Cancel') }}
                </flux:button>
                <flux:button wire:click="delete" variant="danger">
                    {{ __('Delete Employee') }}
                </flux:button>
            </div>
        </div>
    </x-modal>
</div>
