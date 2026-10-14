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
                <span class="text-gray-400">Collection + Left Outs - Distribute</span>
            </div>
        </div>

        {{-- Card 4: Left Outs --}}
        <div class="fi-section rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Left Outs</span>
            <div class="text-2xl font-bold text-purple-600 dark:text-purple-400 mt-1">
                Rs. {{ number_format($this->leftOutsTotal, 0) }}
            </div>
            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1 flex items-center justify-between">
                <span>{{ $this->leftOutsCount }} Left Out Staff</span>
                <span class="text-gray-400">Manual Inclusions</span>
            </div>
        </div>
    </div>

    {{-- Active Department / Section Bar --}}
    @if($activeDepartment === 'Adjustments & Errors')
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 px-4 py-3 bg-gradient-to-r from-amber-50 to-orange-50/50 dark:from-amber-950/40 dark:to-orange-950/20 rounded-xl mb-3 border border-amber-200/80 dark:border-amber-800/60 shadow-sm text-xs">
            <div class="flex flex-wrap items-center gap-2.5">
                <div class="p-1.5 rounded-lg bg-amber-600 text-white shadow-sm">
                    <x-heroicon-m-scale class="w-4 h-4" />
                </div>
                <div class="flex items-center gap-2">
                    <span class="font-bold text-sm text-amber-950 dark:text-amber-100">Adjustments & Error Penalties</span>
                    <span class="px-2 py-0.5 rounded-full bg-amber-200 dark:bg-amber-900 text-amber-900 dark:text-amber-200 font-bold text-[11px]">
                        {{ $this->totalAdjustmentsAndErrorsCount }} Records
                    </span>
                </div>
                <span class="text-amber-700 dark:text-amber-300 hidden md:inline">•</span>
                <span class="text-amber-800 dark:text-amber-300">
                    <strong>{{ $this->adjustmentsList->count() }}</strong> Adjustments ({{ $this->adjustmentsList->where('type', 'add')->count() }} Added, {{ $this->adjustmentsList->where('type', 'deduct')->count() }} Deducted),
                    <strong>{{ $this->errorsList->count() }}</strong> Error Penalties
                    & <strong>{{ $this->heldTipsList->count() }}</strong> Tips Held
                </span>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('tips.reports.print-adjustments-errors', ['report' => $record->id]) }}" target="_blank"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white dark:bg-gray-800 text-amber-900 dark:text-amber-200 border border-amber-300 dark:border-amber-700 hover:bg-amber-100 dark:hover:bg-amber-900/40 font-semibold text-xs shadow-sm transition">
                    <x-heroicon-m-printer class="w-3.5 h-3.5 text-amber-600 dark:text-amber-400" />
                    <span>Print Audit Sheet</span>
                </a>
            </div>
        </div>
    @else
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-2 px-3 py-2 bg-gray-50 dark:bg-gray-800/60 rounded-xl mb-3 border border-gray-200 dark:border-gray-700 text-xs">
            <div class="flex flex-wrap items-center gap-2">
                <span class="font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Active Tab:</span>
                <span class="font-bold text-sm text-gray-900 dark:text-white">{{ $activeDepartment }}</span>
                <span class="px-2 py-0.5 rounded-full bg-primary-100 text-primary-700 dark:bg-primary-950 dark:text-primary-300 font-semibold text-[11px]">
                    {{ $this->getDepartmentCount($activeDepartment) }} Staff
                </span>
                <span class="text-gray-500 dark:text-gray-400">
                    • Allocated: <strong class="text-gray-800 dark:text-gray-200">Rs. {{ number_format($this->getDepartmentTotal($activeDepartment), 0) }}</strong>
                </span>
                @if(!empty($this->activeDepartmentSelectedDepartments))
                    <span class="text-gray-400 hidden sm:inline">•</span>
                    <div class="flex flex-wrap items-center gap-1">
                        <span class="text-gray-400 text-[11px]">Depts:</span>
                        @foreach($this->activeDepartmentSelectedDepartments as $sDept)
                            <span class="px-1.5 py-0.5 rounded bg-gray-200/80 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-[10.5px] font-medium">
                                {{ $sDept }}
                            </span>
                        @endforeach
                    </div>
                @endif
            </div>
            <div class="flex items-center gap-2">
                @if($activeDepartment === 'Left Outs' && !$record->isValidated())
                    <button type="button"
                            wire:click="mountAction('addLeftOut')"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-primary-600 hover:bg-primary-500 text-white shadow-sm transition cursor-pointer">
                        <x-heroicon-m-user-plus class="w-4 h-4" />
                        <span>Add Left Out Staff</span>
                    </button>
                @endif
                <div class="text-gray-400 text-[11px] hidden sm:inline">
                    Tip: Use header actions above to print Summary, Totals, or Adjustments & Errors reports.
                </div>
            </div>
        </div>
    @endif

    {{-- Department Navigation Pills / Tabs --}}
    <div class="flex flex-wrap items-center gap-2 p-1.5 bg-gray-100 dark:bg-gray-800 rounded-xl mb-4 border border-gray-200 dark:border-gray-700">
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

        {{-- Divider --}}
        <div class="w-px h-6 bg-gray-300 dark:bg-gray-700 my-auto mx-1"></div>

        {{-- Dedicated Adjustments & Errors Tab --}}
        @php
            $isAdjActive = $activeDepartment === 'Adjustments & Errors';
            $adjTotalCount = $this->totalAdjustmentsAndErrorsCount;
        @endphp
        <button type="button"
                wire:click="setActiveDepartment('Adjustments & Errors')"
                class="flex items-center gap-2 px-3.5 py-1.5 rounded-lg text-xs font-semibold transition cursor-pointer {{ $isAdjActive ? 'bg-amber-600 text-white shadow-sm ring-2 ring-amber-500/30' : 'text-amber-800 dark:text-amber-300 bg-amber-50 dark:bg-amber-950/40 hover:bg-amber-100 dark:hover:bg-amber-900/50 border border-amber-200 dark:border-amber-800/60' }}">
            <x-heroicon-m-scale class="w-3.5 h-3.5 {{ $isAdjActive ? 'text-white' : 'text-amber-600 dark:text-amber-400' }}" />
            <span>Adjustments & Errors</span>
            <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold {{ $isAdjActive ? 'bg-white/25 text-white' : 'bg-amber-200 dark:bg-amber-800 text-amber-900 dark:text-amber-100' }}">
                {{ $adjTotalCount }}
            </span>
        </button>
    </div>

    {{-- Content Area: Adjustments & Errors OR Department Table --}}
    @if($activeDepartment === 'Adjustments & Errors')
        <div x-data="{ activeFilter: 'all', search: '' }" class="space-y-4">
            {{-- KPI Summary Cards for Adjustments & Errors --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3.5">
                {{-- Card 1: Add Adjustments (+) --}}
                <div class="fi-section rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Tips Adjustments (+)</span>
                        <span class="p-1.5 rounded-lg bg-emerald-50 dark:bg-emerald-950 text-emerald-600 dark:text-emerald-400">
                            <x-heroicon-m-plus-circle class="w-4 h-4" />
                        </span>
                    </div>
                    <div class="text-2xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">
                        + Rs. {{ number_format($this->totalAddAdjustmentsAmount, 0) }}
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1 flex items-center justify-between">
                        <span>{{ $this->adjustmentsList->where('type', 'add')->count() }} Additions</span>
                        <span class="text-emerald-600 dark:text-emerald-400 font-medium">Payout Bonus</span>
                    </div>
                </div>

                {{-- Card 2: Deduct Adjustments (-) --}}
                <div class="fi-section rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Tips Adjustments (-)</span>
                        <span class="p-1.5 rounded-lg bg-rose-50 dark:bg-rose-950 text-rose-600 dark:text-rose-400">
                            <x-heroicon-m-minus-circle class="w-4 h-4" />
                        </span>
                    </div>
                    <div class="text-2xl font-bold text-rose-600 dark:text-rose-400 mt-1">
                        - Rs. {{ number_format($this->totalDeductAdjustmentsAmount, 0) }}
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1 flex items-center justify-between">
                        <span>{{ $this->adjustmentsList->where('type', 'deduct')->count() }} Deductions</span>
                        <span class="text-rose-600 dark:text-rose-400 font-medium">Payout Reduction</span>
                    </div>
                </div>

                {{-- Card 3: Company Error Penalties --}}
                <div class="fi-section rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Error Penalties</span>
                        <span class="p-1.5 rounded-lg bg-amber-50 dark:bg-amber-950 text-amber-600 dark:text-amber-400">
                            <x-heroicon-m-exclamation-triangle class="w-4 h-4" />
                        </span>
                    </div>
                    <div class="text-2xl font-bold text-amber-600 dark:text-amber-400 mt-1">
                        - Rs. {{ number_format($this->totalEstimatedErrorDeduction, 0) }}
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1 flex items-center justify-between">
                        <span>{{ $this->errorsList->count() }} Staff Penalized</span>
                        <span class="text-amber-600 dark:text-amber-400 font-medium">Infraction Deduct</span>
                    </div>
                </div>

                {{-- Card 4: Tips On Hold --}}
                <div class="fi-section rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Tips On Hold</span>
                        <span class="p-1.5 rounded-lg bg-amber-50 dark:bg-amber-950 text-amber-600 dark:text-amber-400">
                            <x-heroicon-m-pause-circle class="w-4 h-4" />
                        </span>
                    </div>
                    <div class="text-2xl font-bold text-amber-600 dark:text-amber-400 mt-1">
                        Rs. {{ number_format($this->totalHeldTipsAmount, 0) }}
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1 flex items-center justify-between">
                        <span>{{ $this->heldTipsList->count() }} Staff on Hold</span>
                        <span class="text-amber-600 dark:text-amber-400 font-medium">Payout Held (0)</span>
                    </div>
                </div>

                {{-- Card 5: Net Adjustment Fund Impact --}}
                <div class="fi-section rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Net Adjustments</span>
                        <span class="p-1.5 rounded-lg bg-sky-50 dark:bg-sky-950 text-sky-600 dark:text-sky-400">
                            <x-heroicon-m-calculator class="w-4 h-4" />
                        </span>
                    </div>
                    <div class="text-2xl font-bold {{ $this->totalNetAdjustmentAmount >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }} mt-1">
                        {{ $this->totalNetAdjustmentAmount >= 0 ? '+' : '' }} Rs. {{ number_format($this->totalNetAdjustmentAmount, 0) }}
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1 flex items-center justify-between">
                        <span>{{ $this->totalAdjustmentsAndErrorsCount }} Total Items</span>
                        <span class="text-gray-400">Net Shift</span>
                    </div>
                </div>
            </div>

            {{-- Filter & Search Toolbar --}}
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-3 bg-white dark:bg-gray-900 rounded-xl shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10">
                <div class="flex items-center gap-2">
                    <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mr-1">Filter:</span>
                    <button type="button"
                            @click="activeFilter = 'all'"
                            :class="activeFilter === 'all' ? 'bg-primary-600 text-white shadow-sm' : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700'"
                            class="px-3 py-1.5 rounded-lg text-xs font-medium transition cursor-pointer">
                        All Records ({{ $this->totalAdjustmentsAndErrorsCount }})
                    </button>
                    <button type="button"
                            @click="activeFilter = 'adjustments'"
                            :class="activeFilter === 'adjustments' ? 'bg-primary-600 text-white shadow-sm' : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700'"
                            class="px-3 py-1.5 rounded-lg text-xs font-medium transition cursor-pointer">
                        Adjustments ({{ $this->adjustmentsList->count() }})
                    </button>
                    <button type="button"
                            @click="activeFilter = 'errors'"
                            :class="activeFilter === 'errors' ? 'bg-primary-600 text-white shadow-sm' : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700'"
                            class="px-3 py-1.5 rounded-lg text-xs font-medium transition cursor-pointer">
                        Company Errors ({{ $this->errorsList->count() }})
                    </button>
                    <button type="button"
                            @click="activeFilter = 'held'"
                            :class="activeFilter === 'held' ? 'bg-primary-600 text-white shadow-sm' : 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700'"
                            class="px-3 py-1.5 rounded-lg text-xs font-medium transition cursor-pointer">
                        Tips Held ({{ $this->heldTipsList->count() }})
                    </button>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    @if(!$record->isValidated())
                        <button type="button"
                                wire:click="mountAction('addAdjustment')"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-emerald-600 hover:bg-emerald-500 text-white shadow-sm transition cursor-pointer">
                            <x-heroicon-m-plus-circle class="w-4 h-4" />
                            <span>Add Adjustment</span>
                        </button>
                        <button type="button"
                                wire:click="mountAction('addError')"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-rose-600 hover:bg-rose-500 text-white shadow-sm transition cursor-pointer">
                            <x-heroicon-m-plus-circle class="w-4 h-4" />
                            <span>Add Company Error</span>
                        </button>
                        <button type="button"
                                wire:click="mountAction('addHeldTip')"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-amber-600 hover:bg-amber-500 text-white shadow-sm transition cursor-pointer">
                            <x-heroicon-m-pause-circle class="w-4 h-4" />
                            <span>Hold Tips</span>
                        </button>
                    @endif
                    <div class="w-full sm:w-64">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                                <x-heroicon-m-magnifying-glass class="w-4 h-4" />
                            </div>
                            <input type="text"
                                   x-model="search"
                                   placeholder="Search employee, remarks, code..."
                                   class="w-full pl-9 pr-3 py-1.5 rounded-lg text-xs bg-gray-50 dark:bg-gray-800/80 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-primary-500 transition" />
                        </div>
                    </div>
                </div>
            </div>

            {{-- SECTION 1: TIPS ADJUSTMENTS LIST --}}
            <div x-show="activeFilter === 'all' || activeFilter === 'adjustments'"
                 class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 overflow-hidden">
                <div class="flex items-center justify-between px-4 py-3 bg-gray-50/70 dark:bg-gray-800/40 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center gap-2">
                        <span class="p-1 rounded bg-emerald-100 dark:bg-emerald-950 text-emerald-600 dark:text-emerald-400">
                            <x-heroicon-m-banknotes class="w-4 h-4" />
                        </span>
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white">Tips Adjustments (Additions & Deductions)</h3>
                        <span class="px-2 py-0.5 rounded-full bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-[11px] font-semibold">
                            {{ $this->adjustmentsList->count() }} records
                        </span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-gray-400 hidden sm:inline">
                            Period: {{ ucfirst($record->month) }} {{ $record->year }}
                        </span>
                        @if(!$record->isValidated())
                            <button type="button"
                                    wire:click="mountAction('addAdjustment')"
                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold bg-emerald-600 hover:bg-emerald-500 text-white shadow-sm transition cursor-pointer">
                                <x-heroicon-m-plus class="w-3.5 h-3.5" />
                                <span>Add Adjustment</span>
                            </button>
                        @endif
                    </div>
                </div>

                @if($this->adjustmentsList->isEmpty())
                    <div class="p-8 text-center text-gray-400 text-xs">
                        <x-heroicon-o-document-magnifying-glass class="w-8 h-8 mx-auto mb-2 opacity-50" />
                        <p class="font-medium text-gray-600 dark:text-gray-300">No individual tips adjustments recorded</p>
                        <p class="text-gray-400 mt-0.5">No tips adjustment entries found for {{ ucfirst($record->month) }} {{ $record->year }}.</p>
                        @if(!$record->isValidated())
                            <div class="mt-3">
                                <button type="button"
                                        wire:click="mountAction('addAdjustment')"
                                        class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold bg-emerald-600 hover:bg-emerald-500 text-white shadow-sm transition cursor-pointer">
                                    <x-heroicon-m-plus class="w-3.5 h-3.5" />
                                    <span>Add Adjustment</span>
                                </button>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs divide-y divide-gray-200 dark:divide-gray-800">
                            <thead class="bg-gray-50/50 dark:bg-gray-800/30 text-[11px] uppercase tracking-wider text-gray-500 dark:text-gray-400 font-semibold">
                                <tr>
                                    <th class="py-2.5 px-3 w-10 text-center">#</th>
                                    <th class="py-2.5 px-3 w-28">Status</th>
                                    <th class="py-2.5 px-3 w-28">Code</th>
                                    <th class="py-2.5 px-3">Employee Name</th>
                                    <th class="py-2.5 px-3">Department</th>
                                    <th class="py-2.5 px-3">Designation</th>
                                    <th class="py-2.5 px-3 w-24 text-center">Type</th>
                                    <th class="py-2.5 px-3 w-32 text-right">Amount</th>
                                    <th class="py-2.5 px-3 min-w-[240px]">Remarks / Note</th>
                                    <th class="py-2.5 px-3 w-28 text-center">In Report</th>
                                    <th class="py-2.5 px-3 w-20 text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800 font-normal text-gray-700 dark:text-gray-300">
                                @foreach($this->adjustmentsList as $idx => $adj)
                                    @php
                                        $searchHaystack = strtolower($adj['employee_code'].' '.$adj['employee_name'].' '.$adj['department'].' '.$adj['designation'].' '.$adj['remarks']);
                                        $isEditingAdj = ($this->editingAdjustmentId === $adj['id']);
                                    @endphp
                                    <tr x-show="!search || '{{ addslashes($searchHaystack) }}'.includes(search.toLowerCase())"
                                        class="{{ $isEditingAdj ? 'bg-primary-50/60 dark:bg-primary-950/40 ring-1 ring-primary-500/30' : 'hover:bg-gray-50/80 dark:hover:bg-gray-800/50' }} transition">
                                        <td class="py-3 px-3 text-center text-gray-400 font-mono">{{ $idx + 1 }}</td>
                                        <td class="py-3 px-3">
                                            @if($isEditingAdj)
                                                <select wire:model.defer="editingAdjustmentData.status"
                                                        class="w-full text-[11px] font-semibold rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 py-1 px-1.5 focus:border-primary-500 focus:ring-primary-500">
                                                    <option value="pending">Pending</option>
                                                    <option value="updated">Updated</option>
                                                    <option value="cancelled">Cancelled</option>
                                                </select>
                                            @else
                                                @if($adj['status'] === 'updated')
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                                        <x-heroicon-m-check class="w-3 h-3 text-emerald-600" />
                                                        <span>Updated</span>
                                                    </span>
                                                @elseif($adj['status'] === 'cancelled')
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 border border-gray-200 dark:border-gray-700">
                                                        <x-heroicon-m-x-mark class="w-3 h-3 text-gray-500" />
                                                        <span>Cancelled</span>
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300 border border-amber-200 dark:border-amber-800">
                                                        <x-heroicon-m-clock class="w-3 h-3 text-amber-600" />
                                                        <span>Pending</span>
                                                    </span>
                                                @endif
                                            @endif
                                        </td>
                                        <td class="py-3 px-3 font-bold font-mono text-gray-900 dark:text-white">
                                            <span class="px-1.5 py-0.5 rounded bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
                                                {{ $adj['employee_code'] }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-3 font-medium text-gray-900 dark:text-gray-100">
                                            {{ $adj['employee_name'] }}
                                        </td>
                                        <td class="py-3 px-3">
                                            <span class="px-2 py-0.5 rounded text-[11px] bg-gray-100 dark:bg-gray-800 font-medium">
                                                {{ $adj['department'] }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-3 text-gray-600 dark:text-gray-400">
                                            {{ $adj['designation'] }}
                                        </td>
                                        <td class="py-3 px-3 text-center">
                                            @if($isEditingAdj)
                                                <select wire:model.defer="editingAdjustmentData.type"
                                                        class="w-full text-[11px] font-bold rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 py-1 px-1 focus:border-primary-500 focus:ring-primary-500">
                                                    <option value="add">+ ADD</option>
                                                    <option value="deduct">- DEDUCT</option>
                                                </select>
                                            @else
                                                @if($adj['type'] === 'add')
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">
                                                        + ADD
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300">
                                                        - DEDUCT
                                                    </span>
                                                @endif
                                            @endif
                                        </td>
                                        <td class="py-3 px-3 text-right font-bold {{ $adj['type'] === 'add' ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                                            @if($isEditingAdj)
                                                <input type="number"
                                                       step="any"
                                                       min="0"
                                                       wire:model.defer="editingAdjustmentData.amount"
                                                       wire:keydown.enter="saveEditingAdjustment"
                                                       wire:keydown.escape="cancelEditingAdjustment"
                                                       class="w-28 text-right font-bold text-xs rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 py-1 px-2 focus:border-primary-500 focus:ring-primary-500" />
                                            @else
                                                {{ $adj['type'] === 'add' ? '+' : '-' }} Rs. {{ number_format($adj['amount'], 0) }}
                                            @endif
                                        </td>
                                        <td class="py-3 px-3">
                                            @if($isEditingAdj)
                                                <input type="text"
                                                       wire:model.defer="editingAdjustmentData.remarks"
                                                       wire:keydown.enter="saveEditingAdjustment"
                                                       wire:keydown.escape="cancelEditingAdjustment"
                                                       placeholder="Enter remarks..."
                                                       class="w-full text-xs rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 py-1 px-2 focus:border-primary-500 focus:ring-primary-500" />
                                            @else
                                                @if(!empty($adj['remarks']))
                                                    <div class="flex items-start gap-1.5 p-2 rounded-lg bg-gray-50 dark:bg-gray-800/80 border border-gray-200/80 dark:border-gray-700/80 text-gray-800 dark:text-gray-200 text-xs">
                                                        <x-heroicon-m-chat-bubble-bottom-center-text class="w-3.5 h-3.5 text-primary-500 shrink-0 mt-0.5" />
                                                        <span class="leading-relaxed">{{ $adj['remarks'] }}</span>
                                                    </div>
                                                @else
                                                    <span class="text-gray-400 italic text-[11px]">No remarks provided</span>
                                                @endif
                                            @endif
                                        </td>
                                        <td class="py-3 px-3 text-center">
                                            @if($adj['is_applied'])
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                                    <x-heroicon-m-check-circle class="w-3 h-3 text-emerald-500" />
                                                    <span>Applied</span>
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400"
                                                      title="Staff code was not matched in this month's attendance sheet">
                                                    <span>Not in Sheet</span>
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-3 text-center">
                                            @if(!$record->isValidated())
                                                @if($isEditingAdj)
                                                    <div class="flex items-center justify-center gap-1">
                                                        <button type="button"
                                                                wire:click="saveEditingAdjustment"
                                                                title="Save Adjustment (Enter)"
                                                                class="p-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white shadow-sm transition cursor-pointer">
                                                            <x-heroicon-m-check class="w-3.5 h-3.5" />
                                                        </button>
                                                        <button type="button"
                                                                wire:click="cancelEditingAdjustment"
                                                                title="Cancel (Esc)"
                                                                class="p-1.5 rounded-lg bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 transition cursor-pointer">
                                                            <x-heroicon-m-x-mark class="w-3.5 h-3.5" />
                                                        </button>
                                                    </div>
                                                @else
                                                    <button type="button"
                                                            wire:click="startEditingAdjustment({{ $adj['id'] }})"
                                                            title="Edit Adjustment"
                                                            class="p-1.5 rounded-lg text-gray-500 hover:text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-950/50 transition cursor-pointer">
                                                        <x-heroicon-m-pencil-square class="w-4 h-4" />
                                                    </button>
                                                @endif
                                            @else
                                                <span class="text-gray-300 dark:text-gray-700">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            {{-- SECTION 2: COMPANY ERROR PENALTIES LIST --}}
            <div x-show="activeFilter === 'all' || activeFilter === 'errors'"
                 class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 overflow-hidden">
                <div class="flex items-center justify-between px-4 py-3 bg-gray-50/70 dark:bg-gray-800/40 border-b border-gray-200 dark:border-gray-700">
                    <div class="flex items-center gap-2">
                        <span class="p-1 rounded bg-rose-100 dark:bg-rose-950 text-rose-600 dark:text-rose-400">
                            <x-heroicon-m-exclamation-triangle class="w-4 h-4" />
                        </span>
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white">Company Error Penalty Deductions</h3>
                        <span class="px-2 py-0.5 rounded-full bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-[11px] font-semibold">
                            {{ $this->errorsList->count() }} penalties
                        </span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-gray-400 hidden sm:inline">
                            Total Est. Deduction: <strong class="text-rose-600 dark:text-rose-400">- Rs. {{ number_format($this->totalEstimatedErrorDeduction, 0) }}</strong>
                        </span>
                        @if(!$record->isValidated())
                            <button type="button"
                                    wire:click="mountAction('addError')"
                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold bg-rose-600 hover:bg-rose-500 text-white shadow-sm transition cursor-pointer">
                                <x-heroicon-m-plus class="w-3.5 h-3.5" />
                                <span>Add Company Error</span>
                            </button>
                        @endif
                    </div>
                </div>

                @if($this->errorsList->isEmpty())
                    <div class="p-8 text-center text-gray-400 text-xs">
                        <x-heroicon-o-shield-check class="w-8 h-8 mx-auto mb-2 opacity-50 text-emerald-500" />
                        <p class="font-medium text-gray-600 dark:text-gray-300">No company error penalties registered</p>
                        <p class="text-gray-400 mt-0.5">No employee penalty percentage deductions recorded for this tips report.</p>
                        @if(!$record->isValidated())
                            <div class="mt-3">
                                <button type="button"
                                        wire:click="mountAction('addError')"
                                        class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold bg-rose-600 hover:bg-rose-500 text-white shadow-sm transition cursor-pointer">
                                    <x-heroicon-m-plus class="w-3.5 h-3.5" />
                                    <span>Add Company Error</span>
                                </button>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs divide-y divide-gray-200 dark:divide-gray-800">
                            <thead class="bg-gray-50/50 dark:bg-gray-800/30 text-[11px] uppercase tracking-wider text-gray-500 dark:text-gray-400 font-semibold">
                                <tr>
                                    <th class="py-2.5 px-3 w-10 text-center">#</th>
                                    <th class="py-2.5 px-3 w-28">Code</th>
                                    <th class="py-2.5 px-3">Employee Name</th>
                                    <th class="py-2.5 px-3">Department</th>
                                    <th class="py-2.5 px-3">Designation</th>
                                    <th class="py-2.5 px-3 w-32 text-center">Deduct Penalty</th>
                                    <th class="py-2.5 px-3 w-28 text-right">Base Tips</th>
                                    <th class="py-2.5 px-3 w-32 text-right">Est. Deduction</th>
                                    <th class="py-2.5 px-3 min-w-[240px]">Error Remarks / Reason</th>
                                    <th class="py-2.5 px-3 w-24 text-center">Status</th>
                                    <th class="py-2.5 px-3 w-20 text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800 font-normal text-gray-700 dark:text-gray-300">
                                @foreach($this->errorsList as $idx => $err)
                                    @php
                                        $errSearch = strtolower($err['employee_code'].' '.$err['employee_name'].' '.$err['department'].' '.$err['designation'].' '.$err['reason']);
                                        $errIndex = $err['index'] ?? $idx;
                                        $isEditingErr = ($this->editingErrorIndex !== null && $this->editingErrorIndex === $errIndex);
                                    @endphp
                                    <tr x-show="!search || '{{ addslashes($errSearch) }}'.includes(search.toLowerCase())"
                                        class="{{ $isEditingErr ? 'bg-rose-50/50 dark:bg-rose-950/30 ring-1 ring-rose-500/30' : 'hover:bg-gray-50/80 dark:hover:bg-gray-800/50' }} transition">
                                        <td class="py-3 px-3 text-center text-gray-400 font-mono">{{ $idx + 1 }}</td>
                                        <td class="py-3 px-3 font-bold font-mono text-gray-900 dark:text-white">
                                            <span class="px-1.5 py-0.5 rounded bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
                                                {{ $err['employee_code'] }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-3 font-medium text-gray-900 dark:text-gray-100">
                                            {{ $err['employee_name'] }}
                                        </td>
                                        <td class="py-3 px-3">
                                            <span class="px-2 py-0.5 rounded text-[11px] bg-gray-100 dark:bg-gray-800 font-medium">
                                                {{ $err['department'] }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-3 text-gray-600 dark:text-gray-400">
                                            {{ $err['designation'] }}
                                        </td>
                                        <td class="py-3 px-3 text-center">
                                            @if($isEditingErr)
                                                <div class="inline-flex items-center justify-center gap-1">
                                                    <input type="number"
                                                           step="1"
                                                           min="0"
                                                           max="100"
                                                           wire:model.defer="editingErrorData.percentage_to_deduct"
                                                           wire:keydown.enter="saveEditingError"
                                                           wire:keydown.escape="cancelEditingError"
                                                           class="w-16 text-center font-bold text-xs rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 py-1 px-1 focus:border-rose-500 focus:ring-rose-500" />
                                                    <span class="text-xs font-bold text-rose-600">%</span>
                                                </div>
                                            @else
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300 border border-rose-200 dark:border-rose-900">
                                                    <x-heroicon-m-arrow-down class="w-3 h-3 text-rose-600" />
                                                    <span>{{ $err['percentage_to_deduct'] }}%</span>
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-3 text-right text-gray-600 dark:text-gray-400">
                                            Rs. {{ number_format($err['base_tips'] ?? 0, 0) }}
                                        </td>
                                        <td class="py-3 px-3 text-right font-bold text-rose-600 dark:text-rose-400">
                                            - Rs. {{ number_format($err['estimated_deduction'] ?? 0, 0) }}
                                        </td>
                                        <td class="py-3 px-3">
                                            @if($isEditingErr)
                                                <input type="text"
                                                       wire:model.defer="editingErrorData.reason"
                                                       wire:keydown.enter="saveEditingError"
                                                       wire:keydown.escape="cancelEditingError"
                                                       placeholder="Enter error reason..."
                                                       class="w-full text-xs rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 py-1 px-2 focus:border-rose-500 focus:ring-rose-500" />
                                            @else
                                                @if(!empty($err['reason']))
                                                    <div class="flex items-start gap-1.5 p-2 rounded-lg bg-rose-50/70 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900/60 text-rose-950 dark:text-rose-200 text-xs">
                                                        <x-heroicon-m-exclamation-circle class="w-3.5 h-3.5 text-rose-500 shrink-0 mt-0.5" />
                                                        <span class="font-medium leading-relaxed">{{ $err['reason'] }}</span>
                                                    </div>
                                                @else
                                                    <span class="text-gray-400 italic text-[11px]">No specific reason recorded</span>
                                                @endif
                                            @endif
                                        </td>
                                        <td class="py-3 px-3 text-center">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-rose-50 text-rose-700 dark:bg-rose-950 dark:text-rose-300 border border-rose-200 dark:border-rose-900">
                                                Deducted
                                            </span>
                                        </td>
                                        <td class="py-3 px-3 text-center">
                                            @if(!$record->isValidated())
                                                @if($isEditingErr)
                                                    <div class="flex items-center justify-center gap-1">
                                                        <button type="button"
                                                                wire:click="saveEditingError"
                                                                title="Save Company Error (Enter)"
                                                                class="p-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white shadow-sm transition cursor-pointer">
                                                            <x-heroicon-m-check class="w-3.5 h-3.5" />
                                                        </button>
                                                        <button type="button"
                                                                wire:click="cancelEditingError"
                                                                title="Cancel (Esc)"
                                                                class="p-1.5 rounded-lg bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 transition cursor-pointer">
                                                            <x-heroicon-m-x-mark class="w-3.5 h-3.5" />
                                                        </button>
                                                    </div>
                                                @else
                                                    <button type="button"
                                                            wire:click="startEditingError({{ $errIndex }})"
                                                            title="Edit Company Error"
                                                            class="p-1.5 rounded-lg text-gray-500 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/50 transition cursor-pointer">
                                                        <x-heroicon-m-pencil-square class="w-4 h-4" />
                                                    </button>
                                                @endif
                                            @else
                                                <span class="text-gray-300 dark:text-gray-700">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            {{-- SECTION 3: EMPLOYEES WITH TIPS HELD --}}
            <div x-show="activeFilter === 'all' || activeFilter === 'held'"
                 class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 overflow-hidden">
                <div class="flex items-center justify-between px-4 py-3 bg-amber-50/70 dark:bg-amber-950/40 border-b border-amber-200/80 dark:border-amber-800/60">
                    <div class="flex items-center gap-2">
                        <span class="p-1 rounded bg-amber-100 dark:bg-amber-900 text-amber-700 dark:text-amber-300">
                            <x-heroicon-m-pause-circle class="w-4 h-4" />
                        </span>
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white">Employees With Tips Held</h3>
                        <span class="px-2 py-0.5 rounded-full bg-amber-200 dark:bg-amber-800 text-amber-900 dark:text-amber-100 text-[11px] font-bold">
                            {{ $this->heldTipsList->count() }} staff on hold
                        </span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-amber-800 dark:text-amber-300 hidden sm:inline">
                            Total Held Amount: <strong class="text-amber-700 dark:text-amber-400">Rs. {{ number_format($this->totalHeldTipsAmount, 0) }}</strong>
                        </span>
                        @if(!$record->isValidated())
                            <button type="button"
                                    wire:click="mountAction('addHeldTip')"
                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-semibold bg-amber-600 hover:bg-amber-500 text-white shadow-sm transition cursor-pointer">
                                <x-heroicon-m-plus class="w-3.5 h-3.5" />
                                <span>Hold Tips</span>
                            </button>
                        @endif
                    </div>
                </div>

                @if($this->heldTipsList->isEmpty())
                    <div class="p-8 text-center text-gray-400 text-xs">
                        <x-heroicon-o-check-badge class="w-8 h-8 mx-auto mb-2 opacity-50 text-emerald-500" />
                        <p class="font-medium text-gray-600 dark:text-gray-300">No staff tips currently placed on hold</p>
                        <p class="text-gray-400 mt-0.5">All staff tips for this report period are marked as Release.</p>
                        @if(!$record->isValidated())
                            <div class="mt-3">
                                <button type="button"
                                        wire:click="mountAction('addHeldTip')"
                                        class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold bg-amber-600 hover:bg-amber-500 text-white shadow-sm transition cursor-pointer">
                                    <x-heroicon-m-plus class="w-3.5 h-3.5" />
                                    <span>Hold Tips</span>
                                </button>
                            </div>
                        @endif
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs divide-y divide-gray-200 dark:divide-gray-800">
                            <thead class="bg-gray-50/50 dark:bg-gray-800/30 text-[11px] uppercase tracking-wider text-gray-500 dark:text-gray-400 font-semibold">
                                <tr>
                                    <th class="py-2.5 px-3 w-10 text-center">#</th>
                                    <th class="py-2.5 px-3 w-24 text-center">Status</th>
                                    <th class="py-2.5 px-3 w-28">Code</th>
                                    <th class="py-2.5 px-3">Employee Name</th>
                                    <th class="py-2.5 px-3">Department</th>
                                    <th class="py-2.5 px-3">Designation</th>
                                    <th class="py-2.5 px-3 w-28 text-right">Base Tips</th>
                                    <th class="py-2.5 px-3 w-28 text-right">Held Amount</th>
                                    <th class="py-2.5 px-3 min-w-[240px]">Hold Reason / Remarks</th>
                                    <th class="py-2.5 px-3 w-24 text-center">In Report</th>
                                    <th class="py-2.5 px-3 w-24 text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800 font-normal text-gray-700 dark:text-gray-300">
                                @foreach($this->heldTipsList as $idx => $held)
                                    @php
                                        $heldSearch = strtolower($held['employee_code'].' '.$held['employee_name'].' '.$held['department'].' '.$held['designation'].' '.$held['reason']);
                                        $isEditingHeld = ($this->editingHeldTipCode === $held['employee_code']);
                                    @endphp
                                    <tr x-show="!search || '{{ addslashes($heldSearch) }}'.includes(search.toLowerCase())"
                                        class="{{ $isEditingHeld ? 'bg-amber-50/60 dark:bg-amber-950/40 ring-1 ring-amber-500/30' : 'hover:bg-gray-50/80 dark:hover:bg-gray-800/50' }} transition">
                                        <td class="py-3 px-3 text-center text-gray-400 font-mono">{{ $idx + 1 }}</td>
                                        <td class="py-3 px-3 text-center">
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300 border border-rose-200 dark:border-rose-900">
                                                <x-heroicon-m-pause class="w-3 h-3 text-rose-600" />
                                                <span>HOLD</span>
                                            </span>
                                        </td>
                                        <td class="py-3 px-3 font-bold font-mono text-gray-900 dark:text-white">
                                            <span class="px-1.5 py-0.5 rounded bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
                                                {{ $held['employee_code'] }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-3 font-medium text-gray-900 dark:text-gray-100">
                                            {{ $held['employee_name'] }}
                                        </td>
                                        <td class="py-3 px-3">
                                            <span class="px-2 py-0.5 rounded text-[11px] bg-gray-100 dark:bg-gray-800 font-medium">
                                                {{ $held['department'] }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-3 text-gray-600 dark:text-gray-400">
                                            {{ $held['designation'] }}
                                        </td>
                                        <td class="py-3 px-3 text-right text-gray-600 dark:text-gray-400">
                                            Rs. {{ number_format($held['base_tips'], 0) }}
                                        </td>
                                        <td class="py-3 px-3 text-right font-bold text-amber-700 dark:text-amber-400">
                                            Rs. {{ number_format($held['held_amount'], 0) }}
                                        </td>
                                        <td class="py-3 px-3">
                                            @if($isEditingHeld)
                                                <input type="text"
                                                       wire:model.defer="editingHeldTipData.reason"
                                                       wire:keydown.enter="saveEditingHeldTip"
                                                       wire:keydown.escape="cancelEditingHeldTip"
                                                       placeholder="Enter reason for holding tips..."
                                                       class="w-full text-xs rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 py-1 px-2 focus:border-amber-500 focus:ring-amber-500" />
                                            @else
                                                @if(!empty($held['reason']))
                                                    <div class="flex items-start gap-1.5 p-2 rounded-lg bg-amber-50/70 dark:bg-amber-950/40 border border-amber-200/80 dark:border-amber-800/60 text-amber-950 dark:text-amber-200 text-xs">
                                                        <x-heroicon-m-information-circle class="w-3.5 h-3.5 text-amber-600 shrink-0 mt-0.5" />
                                                        <span class="leading-relaxed">{{ $held['reason'] }}</span>
                                                    </div>
                                                @else
                                                    <span class="text-gray-400 italic text-[11px]">No specific reason specified</span>
                                                @endif
                                            @endif
                                        </td>
                                        <td class="py-3 px-3 text-center">
                                            @if($held['is_in_report'])
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                                    <x-heroicon-m-check-circle class="w-3 h-3 text-emerald-500" />
                                                    <span>In Sheet</span>
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400"
                                                      title="Staff code was not matched in this month's attendance sheet">
                                                    <span>Not in Sheet</span>
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-3 text-center">
                                            @if(!$record->isValidated())
                                                @if($isEditingHeld)
                                                    <div class="flex items-center justify-center gap-1">
                                                        <button type="button"
                                                                wire:click="saveEditingHeldTip"
                                                                title="Save Hold Reason (Enter)"
                                                                class="p-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-500 text-white shadow-sm transition cursor-pointer">
                                                            <x-heroicon-m-check class="w-3.5 h-3.5" />
                                                        </button>
                                                        <button type="button"
                                                                wire:click="cancelEditingHeldTip"
                                                                title="Cancel (Esc)"
                                                                class="p-1.5 rounded-lg bg-gray-200 hover:bg-gray-300 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 transition cursor-pointer">
                                                            <x-heroicon-m-x-mark class="w-3.5 h-3.5" />
                                                        </button>
                                                    </div>
                                                @else
                                                    <div class="flex items-center justify-center gap-1">
                                                        <button type="button"
                                                                wire:click="startEditingHeldTip('{{ $held['employee_code'] }}')"
                                                                title="Edit Hold Reason"
                                                                class="p-1.5 rounded-lg text-gray-500 hover:text-amber-600 hover:bg-amber-50 dark:hover:bg-amber-950/50 transition cursor-pointer">
                                                            <x-heroicon-m-pencil-square class="w-4 h-4" />
                                                        </button>
                                                        <button type="button"
                                                                wire:click="deleteHeldTip('{{ $held['employee_code'] }}')"
                                                                wire:confirm="Are you sure you want to release the hold on employee {{ $held['employee_code'] }} ({{ $held['employee_name'] }})? Their tips status will be reverted to 'Release' and their payout will be recalculated and restored."
                                                                title="Release Hold (Revert to Release)"
                                                                class="p-1.5 rounded-lg text-gray-500 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/50 transition cursor-pointer">
                                                            <x-heroicon-m-trash class="w-4 h-4" />
                                                        </button>
                                                    </div>
                                                @endif
                                            @else
                                                <span class="text-gray-300 dark:text-gray-700">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    @else
        {{-- Filament Table for Selected Department --}}
        <div class="mt-2">
            {{ $this->table }}
        </div>
    @endif

    <x-filament-actions::modals />
</x-filament-panels::page>
