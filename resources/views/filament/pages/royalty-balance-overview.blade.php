<x-filament-panels::page>
    <div class="space-y-6">
        {{-- KPI Stat Cards --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Total Budget --}}
            <div class="relative overflow-hidden rounded-xl bg-white dark:bg-gray-900 border border-gray-200/80 dark:border-gray-800 p-5 shadow-xs">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total 12-Mo Budget</p>
                        <h3 class="text-2xl font-extrabold text-gray-900 dark:text-white mt-1">
                            Rs. {{ number_format($this->totalBudget, 2) }}
                        </h3>
                    </div>
                    <div class="p-3 rounded-xl bg-primary-50 dark:bg-primary-950/50 text-primary-600 dark:text-primary-400">
                        <x-heroicon-o-banknotes class="w-6 h-6" />
                    </div>
                </div>
                <div class="mt-3 text-xs text-gray-500">
                    Allocated across <strong class="text-gray-700 dark:text-gray-300">{{ count($this->records) }}</strong> eligible staff
                </div>
            </div>

            {{-- Total Utilized --}}
            <div class="relative overflow-hidden rounded-xl bg-white dark:bg-gray-900 border border-gray-200/80 dark:border-gray-800 p-5 shadow-xs">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Utilized / Paid</p>
                        <h3 class="text-2xl font-extrabold text-blue-600 dark:text-blue-400 mt-1">
                            Rs. {{ number_format($this->totalConsumed, 2) }}
                        </h3>
                    </div>
                    <div class="p-3 rounded-xl bg-blue-50 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400">
                        <x-heroicon-o-arrow-trending-up class="w-6 h-6" />
                    </div>
                </div>
                <div class="mt-3 text-xs text-gray-500">
                    Cumulative payouts in current 12-month tenures
                </div>
            </div>

            {{-- Total Remaining Budget --}}
            <div class="relative overflow-hidden rounded-xl bg-white dark:bg-gray-900 border border-gray-200/80 dark:border-gray-800 p-5 shadow-xs">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Remaining Budget</p>
                        <h3 class="text-2xl font-extrabold text-emerald-600 dark:text-emerald-400 mt-1">
                            Rs. {{ number_format($this->totalRemaining, 2) }}
                        </h3>
                    </div>
                    <div class="p-3 rounded-xl bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400">
                        <x-heroicon-o-shield-check class="w-6 h-6" />
                    </div>
                </div>
                <div class="mt-3 text-xs text-gray-500">
                    Available balance for future monthly runs
                </div>
            </div>

            {{-- Utilization Rate --}}
            <div class="relative overflow-hidden rounded-xl bg-white dark:bg-gray-900 border border-gray-200/80 dark:border-gray-800 p-5 shadow-xs">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Overall Utilization</p>
                        <h3 class="text-2xl font-extrabold {{ $this->averageUtilization >= 90 ? 'text-rose-600' : ($this->averageUtilization >= 70 ? 'text-amber-600' : 'text-gray-900 dark:text-white') }} mt-1">
                            {{ $this->averageUtilization }}%
                        </h3>
                    </div>
                    <div class="p-3 rounded-xl bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400">
                        <x-heroicon-o-chart-pie class="w-6 h-6" />
                    </div>
                </div>
                <div class="mt-3">
                    <div class="w-full bg-gray-100 dark:bg-gray-800 h-1.5 rounded-full overflow-hidden">
                        <div
                            class="h-full rounded-full transition-all duration-500 {{ $this->averageUtilization >= 90 ? 'bg-rose-500' : ($this->averageUtilization >= 70 ? 'bg-amber-500' : 'bg-emerald-500') }}"
                            style="width: {{ min(100, $this->averageUtilization) }}%"
                        ></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Main Table Container --}}
        <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200/80 dark:border-gray-800 shadow-xs overflow-hidden">
            {{-- Toolbar: Filters, Scope Selector, Excel Export --}}
            <div class="p-4 bg-gray-50/70 dark:bg-gray-800/40 border-b border-gray-200/80 dark:border-gray-800 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
                <div class="flex flex-wrap items-center gap-2.5 flex-1">
                    {{-- Search Input with Properly Centered Icon --}}
                    <div class="relative w-full sm:w-64">
                        <div class="pointer-events-none absolute inset-y-0 left-0 z-10 flex items-center pl-3 text-gray-400 dark:text-gray-500">
                            <x-heroicon-m-magnifying-glass class="w-4 h-4 shrink-0" />
                        </div>
                        <input
                            type="text"
                            wire:model.live.debounce.250ms="search"
                            placeholder="Search employee, designation..."
                            style="padding-left: 2.25rem !important;"
                            class="w-full text-xs !pl-9 pr-3 py-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none shadow-2xs"
                        />
                    </div>

                    {{-- Report Scope Selector --}}
                    <select
                        wire:model.live="selectedReport"
                        class="text-xs py-2 px-3 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none shadow-2xs"
                    >
                        <option value="all">All Active Staff (Cumulative 12-Month Balances)</option>
                        @foreach($this->reports as $rep)
                            <option value="{{ $rep->id }}">Report #{{ $rep->id }}: {{ $rep->title }} ({{ ucfirst($rep->month) }} {{ $rep->year }})</option>
                        @endforeach
                    </select>

                    {{-- Department Dropdown --}}
                    <select
                        wire:model.live="selectedDepartment"
                        class="text-xs py-2 px-3 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none shadow-2xs"
                    >
                        <option value="all">All Departments ({{ $this->departments->count() }})</option>
                        @foreach($this->departments as $dept)
                            <option value="{{ $dept->name }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>

                    {{-- Status Filter Pills --}}
                    <div class="inline-flex rounded-lg border border-gray-200 dark:border-gray-700 p-0.5 bg-gray-100 dark:bg-gray-800 text-xs">
                        <button
                            type="button"
                            wire:click="setStatusFilter('all')"
                            class="px-2.5 py-1 rounded-md font-medium transition {{ $statusFilter === 'all' ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-xs font-bold' : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300' }}"
                        >
                            All
                        </button>
                        <button
                            type="button"
                            wire:click="setStatusFilter('healthy')"
                            class="px-2.5 py-1 rounded-md font-medium transition {{ $statusFilter === 'healthy' ? 'bg-white dark:bg-gray-700 text-emerald-700 dark:text-emerald-400 shadow-xs font-bold' : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300' }}"
                        >
                            Healthy
                        </button>
                        <button
                            type="button"
                            wire:click="setStatusFilter('low')"
                            class="px-2.5 py-1 rounded-md font-medium transition {{ $statusFilter === 'low' ? 'bg-white dark:bg-gray-700 text-amber-700 dark:text-amber-400 shadow-xs font-bold' : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300' }}"
                        >
                            Low (&lt;20%)
                        </button>
                        <button
                            type="button"
                            wire:click="setStatusFilter('exhausted')"
                            class="px-2.5 py-1 rounded-md font-medium transition {{ $statusFilter === 'exhausted' ? 'bg-white dark:bg-gray-700 text-rose-700 dark:text-rose-400 shadow-xs font-bold' : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300' }}"
                        >
                            Exhausted
                        </button>
                    </div>
                </div>

                {{-- Export Excel Button --}}
                <div class="flex items-center gap-2 shrink-0">
                    <a
                        href="{{ route('royalty.balance-overview.export', ['report' => $selectedReport !== 'all' ? $selectedReport : null, 'department' => $selectedDepartment !== 'all' ? $selectedDepartment : null]) }}"
                        target="_blank"
                        class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs transition shadow-xs"
                    >
                        <x-heroicon-m-arrow-down-tray class="w-4 h-4" />
                        <span>Export Excel</span>
                    </a>
                </div>
            </div>

            {{-- Table --}}
            <div class="overflow-x-auto max-h-[700px] overflow-y-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-gray-50 dark:bg-gray-800/60 uppercase font-semibold text-gray-600 dark:text-gray-400 sticky top-0 z-10 border-b border-gray-200 dark:border-gray-700">
                        <tr>
                            <th class="px-4 py-3">#</th>
                            <th class="px-4 py-3">Employee</th>
                            <th class="px-4 py-3">Department & Designation</th>
                            <th class="px-4 py-3">Join Date</th>
                            <th class="px-4 py-3">12-Mo Tenure Cycle</th>
                            <th class="px-4 py-3 text-right">12-Mo Budget</th>
                            <th class="px-4 py-3 text-right">Utilized</th>
                            <th class="px-4 py-3 text-right">Remaining Budget</th>
                            <th class="px-4 py-3 text-center" style="width: 130px;">Usage</th>
                            <th class="px-4 py-3 text-center">Status</th>
                            <th class="px-4 py-3 text-center">History</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @forelse($this->records as $index => $r)
                            <tr class="hover:bg-gray-50/70 dark:hover:bg-gray-800/40 transition {{ $r['remaining_budget'] <= 0 && $r['annual_budget'] > 0 ? 'bg-rose-50/20 dark:bg-rose-950/10' : '' }}">
                                <td class="px-4 py-3 text-gray-400 font-mono text-[11px]">{{ $index + 1 }}</td>
                                <td class="px-4 py-3">
                                    <div class="font-bold text-gray-900 dark:text-white">{{ $r['employee_name'] }}</div>
                                    <div class="font-mono text-[11px] text-gray-500 dark:text-gray-400">{{ $r['employee_code'] }}</div>
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-800 dark:text-gray-200">{{ $r['designation'] }}</div>
                                    <span class="inline-block mt-0.5 px-2 py-0.5 rounded text-[10px] font-semibold bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300">
                                        {{ $r['department'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 font-mono text-gray-700 dark:text-gray-300">
                                    {{ $r['join_date'] ?? '-' }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="font-medium text-gray-900 dark:text-gray-100">{{ $r['tenure_start'] }} - {{ $r['tenure_end'] }}</div>
                                    <div class="text-[11px] text-primary-600 dark:text-primary-400 font-semibold">
                                        Year {{ $r['tenure_year'] }} &bull; Month {{ $r['tenure_month'] }}/12
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-right font-mono font-semibold text-gray-900 dark:text-gray-100">
                                    Rs. {{ number_format($r['annual_budget'], 2) }}
                                </td>
                                <td class="px-4 py-3 text-right font-mono text-blue-600 dark:text-blue-400 font-semibold">
                                    Rs. {{ number_format($r['consumed_budget'], 2) }}
                                </td>
                                <td class="px-4 py-3 text-right font-mono font-bold {{ $r['remaining_budget'] <= 0 && $r['annual_budget'] > 0 ? 'text-rose-600 dark:text-rose-400' : ($r['utilization_rate'] >= 80 ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400') }}">
                                    Rs. {{ number_format($r['remaining_budget'], 2) }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <div class="flex-1 bg-gray-100 dark:bg-gray-700 h-2 rounded-full overflow-hidden">
                                            <div
                                                class="h-full rounded-full {{ $r['utilization_rate'] >= 90 ? 'bg-rose-500' : ($r['utilization_rate'] >= 70 ? 'bg-amber-500' : 'bg-emerald-500') }}"
                                                style="width: {{ min(100, $r['utilization_rate']) }}%"
                                            ></div>
                                        </div>
                                        <span class="text-[11px] font-mono font-semibold text-gray-600 dark:text-gray-400">{{ $r['utilization_rate'] }}%</span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if($r['status'] === 'Healthy')
                                        <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">
                                            Healthy
                                        </span>
                                    @elseif($r['status'] === 'Low Budget')
                                        <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300">
                                            Low Budget
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold bg-rose-100 text-rose-800 dark:bg-rose-950 dark:text-rose-300">
                                            Exhausted
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <button
                                        type="button"
                                        wire:click="openHistoryModal('{{ $r['employee_code'] }}')"
                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-[11px] font-semibold text-primary-700 bg-primary-50 dark:bg-primary-950 dark:text-primary-300 hover:bg-primary-100 transition shadow-2xs"
                                        title="View monthly calculation breakdown in current tenure"
                                    >
                                        <x-heroicon-m-eye class="w-3.5 h-3.5" />
                                        <span>History ({{ count($r['history']) }})</span>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="px-4 py-12 text-center">
                                    <div class="w-12 h-12 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center mx-auto mb-3 text-gray-400">
                                        <x-heroicon-o-scale class="w-6 h-6" />
                                    </div>
                                    <h4 class="font-bold text-sm text-gray-800 dark:text-gray-200">No Royalty Balance Data Found</h4>
                                    <p class="font-normal text-xs text-gray-500 max-w-md mx-auto mt-1">
                                        Once royalty reports are generated or designations are marked eligible in Royalty Settings, each employee's 12-month tenure cycle, allocated budget, payouts, and remaining balance will appear here in real-time.
                                    </p>
                                    <div class="mt-4 flex items-center justify-center gap-3">
                                        <a href="{{ url('/kamkaj/royalty-reports/create') }}" class="px-3 py-1.5 rounded-lg bg-primary-600 hover:bg-primary-700 text-white font-semibold text-xs shadow-xs transition">
                                            Generate Royalty Report
                                        </a>
                                        <a href="{{ url('/kamkaj/royalty-bonus/settings') }}" class="px-3 py-1.5 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300 font-semibold text-xs hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                                            Royalty Settings
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Table Footer Summary --}}
            <div class="p-3 bg-gray-50 dark:bg-gray-800/40 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between text-xs text-gray-500">
                <span>Showing {{ count($this->records) }} employees</span>
                <span>Remaining Tenure Balance Pool: <strong class="text-emerald-600 dark:text-emerald-400 font-bold font-mono">Rs. {{ number_format($this->totalRemaining, 2) }}</strong></span>
            </div>
        </div>

        {{-- Tenure History Modal --}}
        @if($this->selectedRecord)
            <div
                class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-xs animate-fade-in"
                wire:click.self="closeHistoryModal"
            >
                <div class="bg-white dark:bg-gray-900 rounded-2xl max-w-2xl w-full p-6 shadow-2xl border border-gray-200 dark:border-gray-800 max-h-[85vh] overflow-y-auto">
                    {{-- Modal Header --}}
                    <div class="flex items-start justify-between pb-4 border-b border-gray-100 dark:border-gray-800">
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                                    {{ $this->selectedRecord['employee_name'] }}
                                </h3>
                                <span class="px-2 py-0.5 rounded text-xs font-mono font-bold bg-primary-50 text-primary-700 dark:bg-primary-950 dark:text-primary-300">
                                    {{ $this->selectedRecord['employee_code'] }}
                                </span>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">
                                {{ $this->selectedRecord['designation'] }} &bull; {{ $this->selectedRecord['department'] }}
                            </p>
                        </div>
                        <button
                            type="button"
                            wire:click="closeHistoryModal"
                            class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 transition"
                        >
                            <x-heroicon-m-x-mark class="w-5 h-5" />
                        </button>
                    </div>

                    {{-- Tenure Details Cards --}}
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 my-4">
                        <div class="p-3 rounded-xl bg-gray-50 dark:bg-gray-800/60 border border-gray-100 dark:border-gray-800">
                            <span class="text-[10px] uppercase font-bold text-gray-400">Join Date</span>
                            <div class="text-xs font-semibold text-gray-800 dark:text-gray-200 mt-0.5">
                                {{ $this->selectedRecord['join_date'] ?? 'N/A' }}
                            </div>
                        </div>

                        <div class="p-3 rounded-xl bg-gray-50 dark:bg-gray-800/60 border border-gray-100 dark:border-gray-800">
                            <span class="text-[10px] uppercase font-bold text-gray-400">Tenure Cycle</span>
                            <div class="text-xs font-semibold text-primary-600 dark:text-primary-400 mt-0.5">
                                Year {{ $this->selectedRecord['tenure_year'] }} (M{{ $this->selectedRecord['tenure_month'] }}/12)
                            </div>
                        </div>

                        <div class="p-3 rounded-xl bg-gray-50 dark:bg-gray-800/60 border border-gray-100 dark:border-gray-800">
                            <span class="text-[10px] uppercase font-bold text-gray-400">Total Budget</span>
                            <div class="text-xs font-bold font-mono text-gray-900 dark:text-white mt-0.5">
                                Rs. {{ number_format($this->selectedRecord['annual_budget'], 2) }}
                            </div>
                        </div>

                        <div class="p-3 rounded-xl bg-gray-50 dark:bg-gray-800/60 border border-gray-100 dark:border-gray-800">
                            <span class="text-[10px] uppercase font-bold text-gray-400">Remaining</span>
                            <div class="text-xs font-bold font-mono text-emerald-600 dark:text-emerald-400 mt-0.5">
                                Rs. {{ number_format($this->selectedRecord['remaining_budget'], 2) }}
                            </div>
                        </div>
                    </div>

                    {{-- History Table --}}
                    <div class="mt-4">
                        <h4 class="text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider mb-2">
                            Monthly Calculations in Current 12-Month Tenure ({{ $this->selectedRecord['tenure_start'] }} to {{ $this->selectedRecord['tenure_end'] }})
                        </h4>

                        <div class="border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-gray-50 dark:bg-gray-800 text-gray-600 dark:text-gray-400 font-semibold border-b border-gray-200 dark:border-gray-700">
                                    <tr>
                                        <th class="px-3 py-2.5">#</th>
                                        <th class="px-3 py-2.5">Report / Period</th>
                                        <th class="px-3 py-2.5">Cutoff Date</th>
                                        <th class="px-3 py-2.5 text-right">Calculated Payout</th>
                                        <th class="px-3 py-2.5 text-center">Status</th>
                                        <th class="px-3 py-2.5 text-right">Remaining Balance</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                    @forelse($this->selectedRecord['history'] as $hIdx => $h)
                                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/40">
                                            <td class="px-3 py-2 text-gray-400 font-mono text-[11px]">{{ $hIdx + 1 }}</td>
                                            <td class="px-3 py-2 font-medium text-gray-800 dark:text-gray-200">{{ $h['title'] }}</td>
                                            <td class="px-3 py-2 font-mono text-gray-500">{{ $h['cutoff_date'] }}</td>
                                            <td class="px-3 py-2 text-right font-mono font-bold text-emerald-600 dark:text-emerald-400">
                                                Rs. {{ number_format($h['payout'], 2) }}
                                            </td>
                                            <td class="px-3 py-2 text-center">
                                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold {{ $h['status'] === 'Hold' ? 'bg-rose-100 text-rose-700 dark:bg-rose-950 dark:text-rose-300' : 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-300' }}">
                                                    {{ $h['status'] }}
                                                </span>
                                            </td>
                                            <td class="px-3 py-2 text-right font-mono font-semibold text-gray-700 dark:text-gray-300">
                                                Rs. {{ number_format($h['remaining'], 2) }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-3 py-6 text-center text-gray-400 italic">
                                                No monthly reports calculated yet for this employee in the current tenure cycle.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- Modal Footer --}}
                    <div class="mt-6 flex justify-end">
                        <button
                            type="button"
                            wire:click="closeHistoryModal"
                            class="px-4 py-2 rounded-lg bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 text-xs font-semibold transition"
                        >
                            Close
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
