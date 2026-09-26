<x-filament-panels::page>
    {{-- Header Summary Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
        <div class="fi-section rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Report Payout</span>
            <div class="text-2xl font-bold text-primary-600 dark:text-primary-400 mt-1">
                Rs. {{ number_format($record->items()->sum('final_distribution_amount'), 0) }}
            </div>
            <div class="text-xs text-gray-400 mt-1">
                Across all departments
            </div>
        </div>

        <div class="fi-section rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Staff Processed</span>
            <div class="text-2xl font-bold text-gray-900 dark:text-white mt-1">
                {{ $record->items()->count() }} Staff
            </div>
            <div class="text-xs text-gray-400 mt-1">
                Cutoff: {{ \Carbon\Carbon::parse($record->cutoff_date)->format('d M, Y') }}
            </div>
        </div>

        <div class="fi-section rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Active Tab ({{ $activeDepartment }}) Staff</span>
            <div class="text-2xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">
                {{ $this->getDepartmentCount($activeDepartment) }} Staff
            </div>
            <div class="text-xs text-gray-400 mt-1">
                Allocated: Rs. {{ number_format($this->getDepartmentTotal($activeDepartment), 0) }}
            </div>
        </div>

        <div class="fi-section rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Print Actions</span>
            <div class="mt-2 flex flex-col gap-1.5">
                <div class="flex gap-1.5">
                    <a href="{{ route('tips.reports.print-summary', ['report' => $record->id]) }}" 
                       target="_blank" 
                       class="inline-flex items-center justify-center gap-1 flex-1 px-2 py-1.5 text-xs font-semibold text-white bg-sky-600 hover:bg-sky-500 rounded-lg shadow-sm transition">
                        <x-heroicon-m-table-cells class="w-3.5 h-3.5" />
                        Summary
                    </a>
                    <a href="{{ route('tips.reports.print-totals', ['report' => $record->id]) }}" 
                       target="_blank" 
                       class="inline-flex items-center justify-center gap-1 flex-1 px-2 py-1.5 text-xs font-semibold text-white bg-amber-600 hover:bg-amber-500 rounded-lg shadow-sm transition">
                        <x-heroicon-m-calculator class="w-3.5 h-3.5" />
                        Totals
                    </a>
                </div>
                <a href="{{ route('tips.reports.print', ['report' => $record->id, 'department' => $activeDepartment]) }}" 
                   target="_blank" 
                   class="inline-flex items-center justify-center gap-1.5 w-full px-2 py-1.5 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-500 rounded-lg shadow-sm transition">
                    <x-heroicon-m-printer class="w-3.5 h-3.5" />
                    Print {{ $activeDepartment }} Sheet
                </a>
            </div>
        </div>
    </div>

    {{-- Department Navigation Pills / Tabs --}}
    <div class="flex flex-wrap gap-2 p-1.5 bg-gray-100 dark:bg-gray-800 rounded-xl mb-4 border border-gray-200 dark:border-gray-700">
        @foreach($this->departments as $dept)
            @php
                $count = $this->getDepartmentCount($dept);
                $isActive = $activeDepartment === $dept;
            @endphp
            <button type="button"
                    wire:click="setActiveDepartment('{{ $dept }}')"
                    class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-medium transition cursor-pointer {{ $isActive ? 'bg-primary-600 text-white shadow-sm' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700' }}">
                <span>{{ $dept }}</span>
                <span class="px-1.5 py-0.5 rounded-full text-[10px] {{ $isActive ? 'bg-white/20 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300' }}">
                    {{ $count }}
                </span>
            </button>
        @endforeach
    </div>

    {{-- Filament Table for Selected Department --}}
    <div class="mt-2">
        {{ $this->table }}
    </div>
</x-filament-panels::page>
