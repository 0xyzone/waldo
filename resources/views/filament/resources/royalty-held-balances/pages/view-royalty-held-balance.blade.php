<x-filament-panels::page>
    {{-- Employee Profile Header Card --}}
    <div class="rounded-xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 p-5 shadow-sm mb-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3.5">
                <div class="w-12 h-12 rounded-xl bg-primary-100 dark:bg-primary-950/60 border border-primary-200 dark:border-primary-800 flex items-center justify-center text-primary-700 dark:text-primary-300 font-bold text-lg shadow-sm">
                    {{ strtoupper(substr($record->name ?? $record->employee_code, 0, 2)) }}
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white">{{ $record->name }}</h2>
                        <span class="px-2 py-0.5 text-xs font-bold rounded-md bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700">
                            {{ $record->employee_code }}
                        </span>
                        <span class="px-2 py-0.5 text-xs font-semibold rounded-full {{ $record->employee_status === 'Active' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300' }}">
                            {{ $record->employee_status }}
                        </span>
                    </div>
                    <div class="flex flex-wrap items-center gap-3 text-xs text-gray-500 dark:text-gray-400 mt-1">
                        <span><strong>Department:</strong> {{ $record->department?->name ?? '-' }}</span>
                        <span>&bull;</span>
                        <span><strong>Designation:</strong> {{ $record->designation?->name ?? '-' }}</span>
                        <span>&bull;</span>
                        <span><strong>Annual Royalty Budget:</strong> Rs. {{ number_format($record->designation?->royalty_annual_budget ?? 0, 2) }}</span>
                    </div>
                </div>
            </div>

            @if($this->remainingBalance > 0)
                <div class="flex items-center">
                    <x-filament::button
                        wire:click="mountAction('releaseAmount')"
                        color="success"
                        icon="heroicon-m-banknotes"
                        size="md"
                        class="shadow-sm"
                    >
                        Release Held Amount
                    </x-filament::button>
                </div>
            @endif
        </div>
    </div>

    {{-- 4 Stat Summary Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        {{-- Card 1: Remaining Balance --}}
        <div class="fi-section rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 border-l-4 {{ $this->remainingBalance > 0 ? 'border-amber-500' : 'border-emerald-500' }}">
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Current Held Balance</span>
            <div class="text-2xl font-bold mt-1 {{ $this->remainingBalance > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400' }}">
                Rs. {{ number_format($this->remainingBalance, 2) }}
            </div>
            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                <span>Available for annual merit release</span>
            </div>
        </div>

        {{-- Card 2: Total Cumulative Held --}}
        <div class="fi-section rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 border-l-4 border-rose-500">
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Cumulative Held</span>
            <div class="text-2xl font-bold text-rose-600 dark:text-rose-400 mt-1">
                Rs. {{ number_format($this->totalHeld, 2) }}
            </div>
            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                <span>Across {{ count($this->heldReportItems) }} monthly report periods</span>
            </div>
        </div>

        {{-- Card 3: Total Released --}}
        <div class="fi-section rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 border-l-4 border-sky-500">
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Released to Date</span>
            <div class="text-2xl font-bold text-sky-600 dark:text-sky-400 mt-1">
                Rs. {{ number_format($this->totalReleased, 2) }}
            </div>
            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                <span>{{ count($this->releases) }} merit disbursement transaction(s)</span>
            </div>
        </div>

        {{-- Card 4: Held Months Count --}}
        <div class="fi-section rounded-xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 border-l-4 border-purple-500">
            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Held Months Count</span>
            <div class="text-2xl font-bold text-purple-600 dark:text-purple-400 mt-1">
                {{ count($this->heldReportItems) }} Month(s)
            </div>
            <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                <span>Accumulated in reports</span>
            </div>
        </div>
    </div>

    {{-- Section 1: Monthly Held Breakdown --}}
    <div class="fi-section rounded-xl bg-white dark:bg-gray-900 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10 overflow-hidden mb-6">
        <div class="p-4 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <x-heroicon-m-calendar-days class="w-5 h-5 text-gray-500" />
                <h3 class="text-sm font-bold text-gray-900 dark:text-white">Monthly Held Royalty Bonus Breakdown</h3>
            </div>
            <span class="text-xs font-semibold px-2 py-0.5 rounded bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300">
                {{ count($this->heldReportItems) }} Record(s)
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-gray-50 dark:bg-gray-800/60 uppercase font-semibold text-gray-600 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <th class="px-4 py-3">Period</th>
                        <th class="px-4 py-3">Report Title</th>
                        <th class="px-4 py-3">Designation</th>
                        <th class="px-4 py-3 text-right">Max Payout</th>
                        <th class="px-4 py-3 text-center">Absent</th>
                        <th class="px-4 py-3 text-center">Late/Early</th>
                        <th class="px-4 py-3 text-center">Thumb</th>
                        <th class="px-4 py-3 text-right">Deductions</th>
                        <th class="px-4 py-3 text-right font-bold">Amount Held</th>
                        <th class="px-4 py-3">Reason / Remarks</th>
                        <th class="px-4 py-3 text-center">Report Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($this->heldReportItems as $item)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/40 transition">
                            <td class="px-4 py-3 font-bold text-gray-900 dark:text-white whitespace-nowrap">
                                {{ ucfirst($item->report?->month ?? '-') }} {{ $item->report?->year ?? '' }}
                            </td>
                            <td class="px-4 py-3 text-gray-700 dark:text-gray-300">
                                <a href="{{ route('filament.kamkaj.resources.royalty-reports.view', ['record' => $item->royalty_report_id]) }}" class="hover:underline text-primary-600 dark:text-primary-400 font-medium">
                                    {{ $item->report?->title ?? 'Report #' . $item->royalty_report_id }}
                                </a>
                            </td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-300 whitespace-nowrap">
                                {{ $item->designation ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-right font-mono">
                                Rs. {{ number_format($item->monthly_max_payout, 2) }}
                            </td>
                            <td class="px-4 py-3 text-center font-mono {{ $item->absent_days > 0 ? 'text-rose-600 font-bold' : 'text-gray-400' }}">
                                {{ $item->absent_days + 0 }}
                            </td>
                            <td class="px-4 py-3 text-center font-mono {{ $item->late_early_count > 0 ? 'text-amber-600' : 'text-gray-400' }}">
                                {{ $item->late_early_count }}
                            </td>
                            <td class="px-4 py-3 text-center font-mono {{ $item->missing_thumb_count > 0 ? 'text-amber-600' : 'text-gray-400' }}">
                                {{ $item->missing_thumb_count }}
                            </td>
                            <td class="px-4 py-3 text-right font-mono text-rose-600">
                                Rs. {{ number_format($item->total_deduction_amount, 2) }}
                            </td>
                            <td class="px-4 py-3 text-right font-mono font-bold text-amber-600 dark:text-amber-400 text-sm">
                                Rs. {{ number_format($item->final_payout, 2) }}
                            </td>
                            <td class="px-4 py-3 text-gray-500 max-w-xs truncate">
                                {{ $item->held_reason ?: ($item->remarks ?: 'Bonus placed on Hold') }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                @if($item->report?->isValidated())
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">
                                        Validated
                                    </span>
                                @else
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300">
                                        Draft
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="px-4 py-8 text-center text-gray-400">
                                No held bonus records found for this employee.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if(count($this->heldReportItems) > 0)
                    <tfoot class="bg-gray-50 dark:bg-gray-800/80 font-bold border-t border-gray-200 dark:border-gray-700">
                        <tr>
                            <td colspan="8" class="px-4 py-3 text-right">TOTAL HELD BONUSES:</td>
                            <td class="px-4 py-3 text-right font-mono text-sm text-amber-600 dark:text-amber-400">
                                Rs. {{ number_format($this->totalHeld, 2) }}
                            </td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>

    {{-- Section 2: Release & Disbursement History --}}
    <div class="fi-section rounded-xl bg-white dark:bg-gray-900 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10 overflow-hidden">
        <div class="p-4 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <x-heroicon-m-banknotes class="w-5 h-5 text-gray-500" />
                <h3 class="text-sm font-bold text-gray-900 dark:text-white">Annual Merit Release & Disbursement History</h3>
            </div>
            <span class="text-xs font-semibold px-2 py-0.5 rounded bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300">
                {{ count($this->releases) }} Transaction(s)
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-gray-50 dark:bg-gray-800/60 uppercase font-semibold text-gray-600 dark:text-gray-400 border-b border-gray-200 dark:border-gray-700">
                    <tr>
                        <th class="px-4 py-3">#</th>
                        <th class="px-4 py-3">Release Date</th>
                        <th class="px-4 py-3 text-right font-bold">Released Amount</th>
                        <th class="px-4 py-3">Merit Score / Points</th>
                        <th class="px-4 py-3">Approved / Released By</th>
                        <th class="px-4 py-3">Justification / Notes</th>
                        <th class="px-4 py-3 text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($this->releases as $idx => $rel)
                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/40 transition">
                            <td class="px-4 py-3 font-mono text-gray-400">{{ $idx + 1 }}</td>
                            <td class="px-4 py-3 font-bold text-gray-900 dark:text-white whitespace-nowrap">
                                {{ \Carbon\Carbon::parse($rel->release_date)->format('d M, Y') }}
                            </td>
                            <td class="px-4 py-3 text-right font-mono font-bold text-emerald-600 dark:text-emerald-400 text-sm">
                                Rs. {{ number_format($rel->amount, 2) }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="px-2 py-0.5 rounded-md bg-purple-100 dark:bg-purple-950 text-purple-800 dark:text-purple-300 font-semibold text-xs">
                                    {{ $rel->merit_points ?: 'N/A' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-700 dark:text-gray-300">
                                {{ $rel->releaser?->name ?? 'System Admin' }}
                            </td>
                            <td class="px-4 py-3 text-gray-600 dark:text-gray-400 max-w-sm">
                                {{ $rel->notes ?: '-' }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <button type="button"
                                        wire:click="deleteRelease({{ $rel->id }})"
                                        wire:confirm="Are you sure you want to revert this release? The released amount of Rs. {{ number_format($rel->amount, 2) }} will be restored to the held balance."
                                        class="text-rose-600 hover:text-rose-800 dark:text-rose-400 dark:hover:text-rose-300 font-semibold transition cursor-pointer">
                                    Revert
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-gray-400">
                                No disbursement transactions have been recorded yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if(count($this->releases) > 0)
                    <tfoot class="bg-gray-50 dark:bg-gray-800/80 font-bold border-t border-gray-200 dark:border-gray-700">
                        <tr>
                            <td colspan="2" class="px-4 py-3 text-right">TOTAL DISBURSED:</td>
                            <td class="px-4 py-3 text-right font-mono text-sm text-emerald-600 dark:text-emerald-400">
                                Rs. {{ number_format($this->totalReleased, 2) }}
                            </td>
                            <td colspan="4"></td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>
</x-filament-panels::page>
