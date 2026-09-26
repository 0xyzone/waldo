<x-filament-panels::page>
    {{-- Validated Notice Banner --}}
    @if($record->isValidated())
        <div class="flex items-center justify-between p-3.5 mb-4 rounded-xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-300 dark:border-emerald-800 text-emerald-900 dark:text-emerald-100 shadow-sm">
            <div class="flex items-center gap-2.5">
                <div class="p-1.5 rounded-lg bg-emerald-600 text-white">
                    <x-heroicon-m-lock-closed class="w-4 h-4" />
                </div>
                <div>
                    <div class="text-sm font-bold flex items-center gap-2">
                        <span>Report Validated & Locked</span>
                        <span class="text-[10px] px-2 py-0.5 rounded-full bg-emerald-200 dark:bg-emerald-800 text-emerald-800 dark:text-emerald-200 uppercase tracking-wider font-semibold">Audited</span>
                    </div>
                    <div class="text-xs text-emerald-700 dark:text-emerald-300 mt-0.5">
                        Locked on {{ $record->validated_at?->format('d M, Y h:i A') }} {{ $record->validator ? 'by ' . $record->validator->name : '' }}. Editing and recalculations are disabled.
                    </div>
                </div>
            </div>
            <div class="text-xs font-semibold text-emerald-800 dark:text-emerald-300 bg-white/60 dark:bg-gray-900/60 px-3 py-1.5 rounded-lg border border-emerald-200 dark:border-emerald-700">
                Period: {{ ucfirst($record->month) }} {{ $record->year }}
            </div>
        </div>
    @endif

    {{-- Header Summary Cards (from Totals & Collections) --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
        {{-- Card 1: Total to Distribute --}}
        <div class="fi-section rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Total to Distribute</span>
            <div class="text-2xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">
                Rs. {{ number_format($this->totalToDistribute, 0) }}
            </div>
            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1 flex items-center justify-between">
                <span>{{ $record->items()->count() }} Staff processed</span>
                <span class="text-gray-400">Cutoff: {{ \Carbon\Carbon::parse($record->cutoff_date)->format('d M, Y') }}</span>
            </div>
        </div>

        {{-- Card 2: Actual Total Collection --}}
        <div class="fi-section rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Actual Total Collection</span>
            <div class="text-2xl font-bold text-sky-600 dark:text-sky-400 mt-1">
                Rs. {{ number_format($this->actualCollection, 0) }}
            </div>
            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1 flex items-center justify-between">
                <span>Chips: {{ number_format($this->totalChips, 0) }} | Cash: {{ number_format($this->totalCash, 0) }}</span>
                <span class="text-gray-400">Floor Collections</span>
            </div>
        </div>

        {{-- Card 3: Company Should Add --}}
        <div class="fi-section rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Company Should Add</span>
            <div class="text-2xl font-bold text-amber-600 dark:text-amber-400 mt-1">
                Rs. {{ number_format($this->companyShouldAdd, 0) }}
            </div>
            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1 flex items-center justify-between">
                <span>Subsidy to cover payout</span>
                <span class="text-gray-400">Collection + Adj - Distribute</span>
            </div>
        </div>

        {{-- Card 4: Adjustments / Left Outs --}}
        <div class="fi-section rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Adjustments / Left Outs</span>
            <div class="text-2xl font-bold text-purple-600 dark:text-purple-400 mt-1">
                Rs. {{ number_format($this->adjustmentsAndLeftOuts, 0) }}
            </div>
            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1 flex items-center justify-between">
                <span>{{ $this->leftOutsCount }} Left Out Staff</span>
                <span class="text-gray-400">Manual Inclusions</span>
            </div>
        </div>
    </div>

    {{-- Active Department Bar --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-2 px-3 py-2 bg-gray-50 dark:bg-gray-800/60 rounded-xl mb-3 border border-gray-200 dark:border-gray-700 text-xs">
        <div class="flex items-center gap-2">
            <span class="font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Active Tab:</span>
            <span class="font-bold text-sm text-gray-900 dark:text-white">{{ $activeDepartment }}</span>
            <span class="px-2 py-0.5 rounded-full bg-primary-100 text-primary-700 dark:bg-primary-950 dark:text-primary-300 font-semibold text-[11px]">
                {{ $this->getDepartmentCount($activeDepartment) }} Staff
            </span>
            <span class="text-gray-500 dark:text-gray-400">
                • Allocated: <strong class="text-gray-800 dark:text-gray-200">Rs. {{ number_format($this->getDepartmentTotal($activeDepartment), 0) }}</strong>
            </span>
        </div>
        <div class="text-gray-400 text-[11px]">
            Tip: Use header actions above to print Summary or Totals reports.
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
