<x-filament-panels::page>
    <div class="max-w-5xl mx-auto space-y-8">
        {{-- General Rules & Deductions Form --}}
        <form wire:submit="save" class="space-y-6">
            {{ $this->form }}

            <div class="flex items-center justify-end gap-3 pt-2">
                <x-filament::button
                    type="submit"
                    size="md"
                    icon="heroicon-m-check"
                    color="primary"
                    class="shadow-sm"
                >
                    Save Deduction Rules
                </x-filament::button>
            </div>
        </form>

        {{-- Designation Eligibility Management Section --}}
        <div class="fi-section rounded-xl bg-white dark:bg-gray-900 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10 overflow-hidden border border-gray-200/80 dark:border-gray-800">
            {{-- Section Header --}}
            <div class="p-5 border-b border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-800/40 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-lg bg-primary-100 dark:bg-primary-950/60 border border-primary-200 dark:border-primary-800 flex items-center justify-center text-primary-700 dark:text-primary-300 font-bold shrink-0">
                        <x-heroicon-m-briefcase class="w-5 h-5" />
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-gray-900 dark:text-white flex items-center gap-2">
                            <span>Designations Calculated in Royalty Bonus</span>
                            <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full bg-primary-100 text-primary-800 dark:bg-primary-950 dark:text-primary-300">
                                {{ $this->eligibleDesignationsCount }} / {{ $this->totalDesignationsCount }} Eligible
                            </span>
                        </h2>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                            Toggle which designations are included in monthly Royalty Bonus calculations. Unchecked designations are skipped during report generation.
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2 text-xs shrink-0">
                    <div class="px-3 py-1.5 rounded-lg bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 font-semibold">
                        Monthly Max Budget: <span class="font-bold">Rs. {{ number_format($this->totalMonthlyMaxBudget, 2) }}</span>
                    </div>
                </div>
            </div>

            {{-- Filters Bar --}}
            <div class="p-4 bg-white dark:bg-gray-900 border-b border-gray-100 dark:border-gray-800 flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
                <div class="flex flex-wrap items-center gap-2.5 flex-1">
                    {{-- Search Input --}}
                    <div class="relative w-full sm:w-64">
                        <div class="pointer-events-none absolute inset-y-0 left-0 z-10 flex items-center pl-3 text-gray-400 dark:text-gray-500">
                            <x-heroicon-m-magnifying-glass class="w-4 h-4 shrink-0" />
                        </div>
                        <input
                            type="text"
                            wire:model.live.debounce.250ms="searchDesignation"
                            placeholder="Search designation title..."
                            class="w-full text-xs pl-9 pr-3 py-1.5 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none shadow-sm"
                        />
                    </div>

                    {{-- Department Dropdown --}}
                    <select
                        wire:model.live="selectedDepartment"
                        class="text-xs py-1.5 px-3 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none shadow-sm"
                    >
                        <option value="all">All Departments ({{ $this->departments->count() }})</option>
                        @foreach($this->departments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>

                    {{-- Status Filter --}}
                    <div class="inline-flex rounded-lg border border-gray-200 dark:border-gray-700 p-0.5 bg-gray-50 dark:bg-gray-800/60 text-xs">
                        <button
                            type="button"
                            wire:click="setRoyaltyFilter('all')"
                            class="px-2.5 py-1 rounded-md font-medium transition {{ $royaltyFilter === 'all' ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm font-bold' : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300' }}"
                        >
                            All ({{ $this->filteredDesignations->count() }})
                        </button>
                        <button
                            type="button"
                            wire:click="setRoyaltyFilter('eligible')"
                            class="px-2.5 py-1 rounded-md font-medium transition {{ $royaltyFilter === 'eligible' ? 'bg-white dark:bg-gray-700 text-emerald-700 dark:text-emerald-400 shadow-sm font-bold' : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300' }}"
                        >
                            Eligible
                        </button>
                        <button
                            type="button"
                            wire:click="setRoyaltyFilter('ineligible')"
                            class="px-2.5 py-1 rounded-md font-medium transition {{ $royaltyFilter === 'ineligible' ? 'bg-white dark:bg-gray-700 text-rose-700 dark:text-rose-400 shadow-sm font-bold' : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300' }}"
                        >
                            Excluded
                        </button>
                    </div>
                </div>

                {{-- Bulk Action Buttons --}}
                <div class="flex items-center gap-2 shrink-0">
                    <button
                        type="button"
                        wire:click="bulkSetEligibility(true)"
                        class="text-xs px-2.5 py-1.5 rounded-lg border border-emerald-300 dark:border-emerald-800 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 hover:bg-emerald-100 font-semibold transition"
                        title="Include all currently filtered designations in Royalty Bonus"
                    >
                        ✓ Enable Filtered
                    </button>
                    <button
                        type="button"
                        wire:click="bulkSetEligibility(false)"
                        class="text-xs px-2.5 py-1.5 rounded-lg border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 font-semibold transition"
                        title="Exclude all currently filtered designations from Royalty Bonus"
                    >
                        ✕ Disable Filtered
                    </button>
                </div>
            </div>

            {{-- Designations List Table --}}
            <div class="overflow-x-auto max-h-[600px] overflow-y-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-gray-50 dark:bg-gray-800/60 uppercase font-semibold text-gray-600 dark:text-gray-400 sticky top-0 z-10 border-b border-gray-200 dark:border-gray-700">
                        <tr>
                            <th class="px-4 py-2.5">#</th>
                            <th class="px-4 py-2.5">Department</th>
                            <th class="px-4 py-2.5">Designation Title</th>
                            <th class="px-4 py-2.5 text-right">Annual Budget</th>
                            <th class="px-4 py-2.5 text-right">Monthly Max</th>
                            <th class="px-4 py-2.5 text-right">Monthly Min</th>
                            <th class="px-4 py-2.5 text-center">Calculate in Royalty?</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($this->filteredDesignations as $index => $desig)
                            <tr class="hover:bg-gray-50/60 dark:hover:bg-gray-800/40 transition {{ $desig->is_royalty_eligible ? 'bg-emerald-50/20 dark:bg-emerald-950/10' : '' }}">
                                <td class="px-4 py-2.5 text-gray-400 font-mono text-[11px]">{{ $index + 1 }}</td>
                                <td class="px-4 py-2.5">
                                    <span class="px-2 py-0.5 rounded text-[11px] font-semibold bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300">
                                        {{ $desig->department?->name ?? 'General' }}
                                    </span>
                                </td>
                                <td class="px-4 py-2.5 font-bold text-gray-900 dark:text-white">
                                    {{ $desig->name }}
                                </td>
                                <td class="px-4 py-2.5 text-right font-mono text-gray-600 dark:text-gray-300">
                                    {{ $desig->royalty_annual_budget > 0 ? 'Rs. ' . number_format($desig->royalty_annual_budget, 2) : '-' }}
                                </td>
                                <td class="px-4 py-2.5 text-right font-mono font-semibold {{ $desig->royalty_monthly_max > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-400' }}">
                                    {{ $desig->royalty_monthly_max > 0 ? 'Rs. ' . number_format($desig->royalty_monthly_max, 2) : '-' }}
                                </td>
                                <td class="px-4 py-2.5 text-right font-mono text-gray-500">
                                    {{ $desig->royalty_monthly_min > 0 ? 'Rs. ' . number_format($desig->royalty_monthly_min, 2) : '-' }}
                                </td>
                                <td class="px-4 py-2.5 text-center">
                                    <button
                                        type="button"
                                        wire:click="toggleDesignation({{ $desig->id }})"
                                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold transition shadow-xs {{ $desig->is_royalty_eligible ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-700 hover:bg-emerald-200' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400 border border-gray-300 dark:border-gray-700 hover:bg-gray-200' }}"
                                    >
                                        @if($desig->is_royalty_eligible)
                                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                            <span>Eligible</span>
                                        @else
                                            <span class="w-2 h-2 rounded-full bg-gray-400"></span>
                                            <span>Excluded</span>
                                        @endif
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-gray-400">
                                    <x-heroicon-o-magnifying-glass class="w-8 h-8 mx-auto mb-2 opacity-50" />
                                    <p class="font-medium text-xs">No designations found matching your filter criteria.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Footer Summary --}}
            <div class="p-3 bg-gray-50 dark:bg-gray-800/40 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between text-xs text-gray-500">
                <span>Showing {{ $this->filteredDesignations->count() }} of {{ $this->totalDesignationsCount }} designations</span>
                <span>Active Royalty Designations: <strong class="text-emerald-600 dark:text-emerald-400">{{ $this->eligibleDesignationsCount }}</strong></span>
            </div>
        </div>
    </div>
</x-filament-panels::page>
