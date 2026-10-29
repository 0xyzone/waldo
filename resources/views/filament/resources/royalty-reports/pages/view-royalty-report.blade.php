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
                        <span>Royalty Report Validated & Locked</span>
                        <span class="text-[10px] px-2 py-0.5 rounded-full bg-emerald-200 dark:bg-emerald-800 text-emerald-800 dark:text-emerald-200 uppercase tracking-wider font-semibold">Audited</span>
                    </div>
                    <div class="text-xs text-emerald-700 dark:text-emerald-300 mt-0.5">
                        Locked on {{ $record->validated_at?->format('d M, Y h:i A') }} {{ $record->validator ? 'by ' . $record->validator->name : '' }}. Editing, recalculations, and exemption toggles are disabled.
                    </div>
                </div>
            </div>
            <div class="text-xs font-semibold text-emerald-800 dark:text-emerald-300 bg-white/60 dark:bg-gray-900/60 px-3 py-1.5 rounded-lg border border-emerald-200 dark:border-emerald-700">
                Period: {{ ucfirst($record->month) }} {{ $record->year }}
            </div>
        </div>
    @endif

    {{-- Header Summary Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
        {{-- Card 1: Total Final Net Payout --}}
        <div class="fi-section rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Net Royalty Payout</span>
            <div class="text-2xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">
                Rs. {{ number_format($this->totalFinalPayout, 2) }}
            </div>
            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1 flex items-center justify-between">
                <span>{{ $this->totalStaff }} Eligible Staff</span>
                <span class="text-gray-400">Net after all deductions</span>
            </div>
        </div>

        {{-- Card 2: Total Baseline Budget --}}
        <div class="fi-section rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Monthly Max Budget</span>
            <div class="text-2xl font-bold text-sky-600 dark:text-sky-400 mt-1">
                Rs. {{ number_format($this->totalMaxPayout, 2) }}
            </div>
            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1 flex items-center justify-between">
                <span>Sum of designation baselines</span>
                <span class="text-gray-400">Pre-deductions</span>
            </div>
        </div>

        {{-- Card 3: Total Deductions --}}
        <div class="fi-section rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Deductions Applied</span>
            <div class="text-2xl font-bold text-rose-600 dark:text-rose-400 mt-1">
                Rs. {{ number_format($this->totalDeductions, 2) }}
            </div>
            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1 flex items-center justify-between">
                <span>Performance + Attendance</span>
                <span class="text-gray-400">Savings retained</span>
            </div>
        </div>

        {{-- Card 4: Staff & Exemption Status --}}
        <div class="fi-section rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Exempt Staff</span>
            <div class="text-2xl font-bold text-indigo-600 dark:text-indigo-400 mt-1">
                {{ $this->exemptStaffCount }} / {{ $this->totalStaff }}
            </div>
            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1 flex items-center justify-between">
                <span>Guaranteed 100% max payout</span>
                <span class="text-gray-400">Cutoff: {{ \Carbon\Carbon::parse($record->cutoff_date)->format('d M, Y') }}</span>
            </div>
        </div>
    </div>

    {{-- Department Navigation Pills / Tabs --}}
    <div class="flex flex-wrap items-center gap-2 p-1.5 bg-gray-100 dark:bg-gray-800 rounded-xl mb-4 border border-gray-200 dark:border-gray-700">
        <button type="button"
                wire:click="setActiveDepartment('All')"
                class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-medium transition cursor-pointer {{ $activeDepartment === 'All' ? 'bg-primary-600 text-white shadow-sm' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700' }}">
            <span>All Departments</span>
            <span class="px-1.5 py-0.5 rounded-full text-[10px] {{ $activeDepartment === 'All' ? 'bg-white/20 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300' }}">
                {{ $this->getDepartmentCount('All') }}
            </span>
        </button>

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

    {{-- Interactive Royalty Items Table --}}
    <div class="mt-2">
        {{ $this->table }}
    </div>
</x-filament-panels::page>
