<x-filament-panels::page>
    @php
        $daysInMonth = $this->getDaysInMonth();
        $specialDays = $this->getSpecialDays();
        $empData = $this->getEmployeesData();
        $employees = $empData['items'];
        $totalEmployees = $empData['total'];
        $totalPages = $empData['totalPages'];
        $entriesMap = $this->getEntriesMap($employees->pluck('employee_code')->toArray());
        $blocks = $this->getBlocks();
    @endphp

    <div class="space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-gray-200 bg-white p-3 dark:border-white/10 dark:bg-gray-900">
            <input type="text" wire:model.live.debounce.300ms="search"
                placeholder="Search employee code, name, or department..."
                class="w-full max-w-sm rounded-lg border-gray-300 bg-white px-3 py-1.5 text-sm shadow-sm focus:border-primary-500 focus:ring-primary-500 dark:border-white/10 dark:bg-gray-800 dark:text-white" />

            <div class="flex flex-wrap items-center gap-4 text-xs text-gray-500 dark:text-gray-400">
                <span class="inline-flex items-center gap-1.5">
                    <span class="inline-block h-3 w-3 rounded bg-amber-100 ring-1 ring-amber-400 dark:bg-amber-900/40"></span>
                    Special OT Day (×2)
                </span>
                <span>Click any day cell to add/edit Block &amp; Hours</span>
            </div>
        </div>

        <div class="overflow-x-auto rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900">
            <table class="w-full border-collapse text-xs">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50 dark:border-white/10 dark:bg-gray-800/60">
                        <th class="sticky left-0 z-20 min-w-[90px] border-r border-gray-200 bg-gray-50 px-2.5 py-2 text-left font-semibold dark:border-white/10 dark:bg-gray-800">Code</th>
                        <th class="sticky left-[90px] z-20 min-w-[160px] border-r border-gray-200 bg-gray-50 px-2.5 py-2 text-left font-semibold dark:border-white/10 dark:bg-gray-800">Name</th>
                        <th class="min-w-[120px] border-r border-gray-200 px-2.5 py-2 text-left font-semibold dark:border-white/10">Department</th>
                        @for ($d = 1; $d <= $daysInMonth; $d++)
                            @php $isSpecial = in_array($d, $specialDays, true); @endphp
                            <th class="min-w-[50px] border-r border-gray-200 px-1 py-2 text-center font-semibold dark:border-white/10 {{ $isSpecial ? 'bg-amber-100 text-amber-900 dark:bg-amber-900/40 dark:text-amber-200' : '' }}">
                                {{ str_pad((string) $d, 2, '0', STR_PAD_LEFT) }}
                                @if ($isSpecial)
                                    <span class="block text-[9px] font-normal">×2</span>
                                @endif
                            </th>
                        @endfor
                        <th class="min-w-[80px] border-r border-gray-200 bg-blue-50 px-2 py-2 text-right font-semibold text-blue-900 dark:border-white/10 dark:bg-blue-900/30 dark:text-blue-200">Normal (×1.5)</th>
                        <th class="min-w-[80px] border-r border-gray-200 bg-amber-50 px-2 py-2 text-right font-semibold text-amber-900 dark:border-white/10 dark:bg-amber-900/30 dark:text-amber-200">Special (×2)</th>
                        <th class="min-w-[70px] bg-gray-100 px-2 py-2 text-right font-bold dark:bg-gray-800">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    @foreach ($employees as $emp)
                        @php
                            $empEntries = $entriesMap[$emp->employee_code] ?? [];
                            $normalTotal = 0.0;
                            $specialTotal = 0.0;
                            foreach ($empEntries as $dayItems) {
                                foreach ($dayItems as $item) {
                                    if ($item['is_special_ot']) {
                                        $specialTotal += $item['hours'];
                                    } else {
                                        $normalTotal += $item['hours'];
                                    }
                                }
                            }
                        @endphp
                        <tr wire:key="emp-{{ $emp->employee_code }}" class="hover:bg-gray-50/70 dark:hover:bg-white/5">
                            <td class="sticky left-0 z-10 border-r border-gray-200 bg-white px-2.5 py-1.5 font-mono dark:border-white/10 dark:bg-gray-900">{{ $emp->employee_code }}</td>
                            <td class="sticky left-[90px] z-10 border-r border-gray-200 bg-white px-2.5 py-1.5 font-medium text-gray-900 dark:border-white/10 dark:bg-gray-900 dark:text-white">{{ $emp->name }}</td>
                            <td class="border-r border-gray-200 px-2.5 py-1.5 text-gray-600 dark:border-white/10 dark:text-gray-300">{{ $emp->department?->name ?? '—' }}</td>
                            @for ($d = 1; $d <= $daysInMonth; $d++)
                                @php
                                    $isSpecial = in_array($d, $specialDays, true);
                                    $cellItems = $empEntries[$d] ?? [];
                                    $cellSum = collect($cellItems)->sum('hours');
                                @endphp
                                <td wire:click="openCell('{{ $emp->employee_code }}', '{{ addslashes($emp->name) }}', {{ $d }})"
                                    class="cursor-pointer border-r border-gray-200 px-1 py-1 text-center transition hover:bg-primary-50 dark:border-white/10 dark:hover:bg-primary-900/20 {{ $isSpecial ? 'bg-amber-50/50 dark:bg-amber-900/10' : '' }}">
                                    @if ($cellSum > 0)
                                        <span class="font-semibold text-gray-900 dark:text-white">{{ rtrim(rtrim(number_format($cellSum, 2), '0'), '.') }}</span>
                                        <span class="block text-[9px] text-primary-600 dark:text-primary-400">
                                            {{ collect($cellItems)->pluck('block')->unique()->join(',') }}
                                        </span>
                                    @else
                                        <span class="text-gray-300 dark:text-gray-700">·</span>
                                    @endif
                                </td>
                            @endfor
                            <td class="border-r border-gray-200 bg-blue-50/40 px-2 py-1.5 text-right font-medium dark:border-white/10 dark:bg-blue-900/10">{{ number_format($normalTotal, 2) }}</td>
                            <td class="border-r border-gray-200 bg-amber-50/40 px-2 py-1.5 text-right font-medium text-amber-700 dark:border-white/10 dark:bg-amber-900/10 dark:text-amber-300">{{ number_format($specialTotal, 2) }}</td>
                            <td class="bg-gray-50 px-2 py-1.5 text-right font-bold dark:bg-gray-800/50">{{ number_format($normalTotal + $specialTotal, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="flex items-center justify-between rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-xs text-gray-600 dark:border-white/10 dark:bg-gray-900 dark:text-gray-300">
            <span>Showing page <strong>{{ $this->page }}</strong> of <strong>{{ $totalPages }}</strong> ({{ number_format($totalEmployees) }} employees)</span>
            <div class="flex items-center gap-2">
                <button type="button" wire:click="previousPage" @disabled($this->page <= 1)
                    class="rounded-lg border border-gray-300 px-3 py-1 font-medium hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-40 dark:border-white/10 dark:hover:bg-white/5">
                    Previous
                </button>
                <button type="button" wire:click="nextPage" @disabled($this->page >= $totalPages)
                    class="rounded-lg border border-gray-300 px-3 py-1 font-medium hover:bg-gray-50 disabled:cursor-not-allowed disabled:opacity-40 dark:border-white/10 dark:hover:bg-white/5">
                    Next
                </button>
            </div>
        </div>
    </div>

    @if ($editingEmployeeCode && $editingDay)
        @php
            $cellEntries = $entriesMap[$editingEmployeeCode][$editingDay] ?? [];
            $isSpecialDay = in_array($editingDay, $specialDays, true);
            $dateLabel = \Carbon\Carbon::create($this->record->year, $this->record->month, $editingDay)->format('D, d M Y');
        @endphp
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-xs">
            <div class="w-full max-w-md overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xl dark:border-white/10 dark:bg-gray-900">
                <div class="flex items-start justify-between border-b border-gray-200 bg-gray-50 px-5 py-4 dark:border-white/10 dark:bg-gray-800/60">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="rounded bg-gray-200 px-2 py-0.5 font-mono text-xs font-semibold text-gray-800 dark:bg-white/10 dark:text-gray-200">{{ $editingEmployeeCode }}</span>
                            <h3 class="text-base font-semibold text-gray-900 dark:text-white">{{ $editingEmployeeName }}</h3>
                        </div>
                        <div class="mt-1.5 flex items-center gap-2 text-xs">
                            <span class="text-gray-500 dark:text-gray-400">{{ $dateLabel }}</span>
                            @if ($isSpecialDay)
                                <span class="rounded-full bg-amber-100 px-2 py-0.5 font-medium text-amber-800 dark:bg-amber-900/40 dark:text-amber-300">Special OT (×2)</span>
                            @else
                                <span class="rounded-full bg-blue-100 px-2 py-0.5 font-medium text-blue-800 dark:bg-blue-900/40 dark:text-blue-300">Normal OT (×1.5)</span>
                            @endif
                        </div>
                    </div>
                    <button type="button" wire:click="closeCell" class="rounded-lg p-1 text-gray-400 hover:bg-gray-200 hover:text-gray-700 dark:hover:bg-white/10 dark:hover:text-white">✕</button>
                </div>

                <div class="space-y-4 p-5">
                    @if (count($cellEntries) > 0)
                        <div class="space-y-2">
                            <div class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Logged Entries</div>
                            @foreach ($cellEntries as $item)
                                <div class="flex items-center justify-between rounded-xl border border-gray-200 bg-gray-50/70 px-3.5 py-2.5 text-sm dark:border-white/10 dark:bg-white/5">
                                    <div class="flex items-center gap-2.5">
                                        <span class="rounded-md bg-primary-100 px-2 py-0.5 text-xs font-semibold text-primary-800 dark:bg-primary-900/40 dark:text-primary-300">Block {{ $item['block'] }}</span>
                                        <span class="font-semibold text-gray-900 dark:text-white">{{ number_format($item['hours'], 2) }} hrs</span>
                                    </div>
                                    <button type="button" wire:click="deleteCellEntry({{ $item['id'] }})"
                                        class="rounded-md px-2 py-1 text-xs font-medium text-danger-600 hover:bg-danger-50 dark:text-danger-400 dark:hover:bg-danger-900/20">
                                        Remove
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <div class="rounded-xl border border-gray-200 bg-gray-50/40 p-4 dark:border-white/10 dark:bg-white/[0.02]">
                        <div class="mb-3 text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Add OT Entry</div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="mb-1.5 block text-xs font-medium text-gray-600 dark:text-gray-300">Block</label>
                                <select wire:model="newBlockId"
                                    class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-white/15 dark:bg-gray-800 dark:text-white">
                                    @foreach ($blocks as $block)
                                        <option value="{{ $block->id }}">{{ $block->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="mb-1.5 block text-xs font-medium text-gray-600 dark:text-gray-300">Hours</label>
                                <input type="number" step="0.5" min="0.5" max="24" wire:model="newHours" wire:keydown.enter="addCellEntry" placeholder="e.g. 4"
                                    class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-white/15 dark:bg-gray-800 dark:text-white" />
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 border-t border-gray-200 bg-gray-50 px-5 py-3 dark:border-white/10 dark:bg-gray-800/60">
                    <button type="button" wire:click="closeCell"
                        class="rounded-lg border border-gray-300 bg-white px-4 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-white/10 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-white/10">
                        Done
                    </button>
                    <button type="button" wire:click="addCellEntry"
                        class="rounded-lg bg-primary-600 px-4 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-primary-500">
                        Add Entry
                    </button>
                </div>
            </div>
        </div>
    @endif
</x-filament-panels::page>
