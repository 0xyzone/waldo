<x-filament-panels::page>
    @php
        $daysInMonth = $this->getDaysInMonth();
        $specialDays = $this->getSpecialDays();
        $empData = $this->getEmployeesData();
        $employees = $empData['items'];
        $totalEmployees = $empData['total'];
        $totalPages = $empData['totalPages'];
        $empCodes = $employees->pluck('employee_code')->toArray();
        $entriesMap = $this->getEntriesMap($empCodes);
        $adjustmentsMap = $this->getAdjustmentsMap($empCodes);
        $blocks = $this->getBlocks();
        $departments = $this->getDepartments();
    @endphp

    <div class="space-y-4">
        <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-gray-200 bg-white p-3.5 dark:border-white/10 dark:bg-gray-900">
            <div class="flex flex-wrap items-center gap-2.5">
                <input type="text" wire:model.live.debounce.300ms="search"
                    placeholder="Search code (e.g. 834), name..."
                    class="h-9 w-56 rounded-lg border border-gray-300 bg-white px-3 text-sm shadow-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-white/15 dark:bg-gray-800 dark:text-white" />

                <select wire:model.live="departmentId"
                    class="h-9 rounded-lg border border-gray-300 bg-white px-2.5 text-sm shadow-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-white/15 dark:bg-gray-800 dark:text-white">
                    <option value="">All Departments</option>
                    @foreach ($departments as $dept)
                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                    @endforeach
                </select>

                <select wire:model.live="blockId"
                    class="h-9 rounded-lg border border-gray-300 bg-white px-2.5 text-sm shadow-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-white/15 dark:bg-gray-800 dark:text-white">
                    <option value="">All Blocks</option>
                    @foreach ($blocks as $block)
                        <option value="{{ $block->id }}">Block {{ $block->name }}</option>
                    @endforeach
                </select>

                <select wire:model.live="otFilter"
                    class="h-9 rounded-lg border border-gray-300 bg-white px-2.5 text-sm shadow-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-white/15 dark:bg-gray-800 dark:text-white">
                    <option value="all">All Employees</option>
                    <option value="with_ot">With OT Only</option>
                    <option value="without_ot">Without OT Only</option>
                </select>

                @if ($this->search !== '' || $this->departmentId !== '' || $this->blockId !== '' || $this->otFilter !== 'all')
                    <button type="button" wire:click="resetFilters"
                        class="h-9 rounded-lg border border-gray-300 px-3 text-xs font-medium text-gray-600 hover:bg-gray-50 dark:border-white/15 dark:text-gray-300 dark:hover:bg-white/5">
                        Reset
                    </button>
                @endif
            </div>

            <div class="flex flex-wrap items-center gap-4 text-xs text-gray-500 dark:text-gray-400">
                <span class="inline-flex items-center gap-1.5">
                    <span class="inline-block h-3 w-3 rounded bg-amber-100 ring-1 ring-amber-400 dark:bg-amber-900/40"></span>
                    Special OT Day (×2)
                </span>
                <span>Type hours in bottom box to add &bull; Click top total for details</span>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3 rounded-xl border border-gray-200 bg-white px-3.5 py-2.5 dark:border-white/10 dark:bg-gray-900">
            <span class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Active Block:</span>
            <div class="flex flex-wrap items-center gap-2">
                @foreach ($blocks as $block)
                    <label class="inline-flex cursor-pointer items-center gap-1.5 rounded-lg border px-2.5 py-1 text-xs font-medium transition {{ (int) $this->activeBlockId === $block->id ? 'border-primary-500 bg-primary-50 text-primary-700 dark:border-primary-500 dark:bg-primary-900/30 dark:text-primary-300' : 'border-gray-200 bg-gray-50 text-gray-700 hover:bg-gray-100 dark:border-white/10 dark:bg-white/5 dark:text-gray-300 dark:hover:bg-white/10' }}">
                        <input type="radio" name="active_block" value="{{ $block->id }}" wire:model.live="activeBlockId"
                            class="h-3.5 w-3.5 border-gray-300 text-primary-600 focus:ring-primary-500 dark:border-white/20 dark:bg-gray-800" />
                        <span>{{ $block->name }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        <div x-data="{
            moveCell(e, row, col) {
                const dirs = { ArrowRight: [0, 1], ArrowLeft: [0, -1], ArrowDown: [1, 0], ArrowUp: [-1, 0], Enter: [1, 0] };
                const d = dirs[e.key];
                if (!d) return;
                e.preventDefault();
                const target = document.querySelector(`input[data-row='${row + d[0]}'][data-col='${col + d[1]}']`);
                if (target) { target.focus(); target.select(); }
            }
        }" class="max-h-[calc(100vh-16rem)] overflow-auto rounded-xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900">
            <table class="w-full border-collapse text-xs">
                <thead class="sticky top-0 z-30">
                    <tr class="border-b border-gray-200 bg-gray-50 dark:border-white/10 dark:bg-gray-800">
                        <th class="sticky left-0 z-40 min-w-[90px] border-r border-gray-200 bg-slate-100 px-2.5 py-2 text-left font-semibold text-slate-800 dark:border-white/10 dark:bg-slate-800 dark:text-slate-100">Code</th>
                        <th class="sticky left-[90px] z-40 min-w-[160px] border-r border-gray-200 bg-slate-100 px-2.5 py-2 text-left font-semibold text-slate-800 dark:border-white/10 dark:bg-slate-800 dark:text-slate-100">Name</th>
                        <th class="min-w-[120px] border-r border-gray-200 bg-slate-100 px-2.5 py-2 text-left font-semibold text-slate-800 dark:border-white/10 dark:bg-slate-800 dark:text-slate-100">Department</th>
                        @for ($d = 1; $d <= $daysInMonth; $d++)
                            @php
                                $dateObj = \Carbon\Carbon::create($this->record->year, $this->record->month, $d);
                                $isSpecial = in_array($d, $specialDays, true);
                                $isSaturday = $dateObj->isSaturday();
                                $thClass = $isSpecial
                                    ? 'bg-amber-100 text-amber-900 dark:bg-amber-900/50 dark:text-amber-200'
                                    : ($isSaturday
                                        ? 'bg-purple-100/80 text-purple-900 dark:bg-purple-900/40 dark:text-purple-200'
                                        : 'bg-gray-50 dark:bg-gray-800');
                            @endphp
                            <th class="min-w-[52px] border-r border-gray-200 px-1 py-1.5 text-center font-semibold dark:border-white/10 {{ $thClass }}">
                                <span class="block leading-tight">{{ str_pad((string) $d, 2, '0', STR_PAD_LEFT) }}</span>
                                <span class="block text-[9.5px] font-normal opacity-75">{{ $dateObj->format('D') }}</span>
                                @if ($isSpecial)
                                    <span class="block text-[9px] font-bold">×2</span>
                                @endif
                            </th>
                        @endfor
                        <th class="min-w-[76px] border-r border-gray-200 bg-blue-50/90 px-2 py-2 text-right font-semibold text-blue-900 dark:border-white/10 dark:bg-blue-950 dark:text-blue-200">Normal Adj.</th>
                        <th class="min-w-[80px] border-r border-gray-200 bg-blue-100/80 px-2 py-2 text-right font-semibold text-blue-900 dark:border-white/10 dark:bg-blue-900/60 dark:text-blue-100">Normal (×1.5)</th>
                        <th class="min-w-[76px] border-r border-gray-200 bg-amber-50/90 px-2 py-2 text-right font-semibold text-amber-900 dark:border-white/10 dark:bg-amber-950 dark:text-amber-200">Special Adj.</th>
                        <th class="min-w-[80px] border-r border-gray-200 bg-amber-100/80 px-2 py-2 text-right font-semibold text-amber-900 dark:border-white/10 dark:bg-amber-900/60 dark:text-amber-100">Special (×2)</th>
                        <th class="min-w-[70px] bg-gray-100 px-2 py-2 text-right font-bold dark:bg-gray-800">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    @foreach ($employees as $rowIndex => $emp)
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
                            $adj = $adjustmentsMap[$emp->employee_code] ?? ['normal' => 0.0, 'special' => 0.0, 'remarks' => ''];
                            $grandTotal = $normalTotal + $adj['normal'] + $specialTotal + $adj['special'];
                        @endphp
                        <tr wire:key="emp-{{ $emp->employee_code }}" class="hover:bg-gray-50/70 dark:hover:bg-white/5">
                            <td class="sticky left-0 z-10 border-r border-gray-200 bg-slate-50 px-2.5 py-1.5 font-mono text-slate-800 dark:border-white/10 dark:bg-slate-800/80 dark:text-slate-200">{{ $emp->employee_code }}</td>
                            <td class="sticky left-[90px] z-10 border-r border-gray-200 bg-slate-50 px-2.5 py-1.5 font-medium text-slate-900 dark:border-white/10 dark:bg-slate-800/80 dark:text-white">{{ $emp->name }}</td>
                            <td class="border-r border-gray-200 bg-slate-50/70 px-2.5 py-1.5 text-slate-700 dark:border-white/10 dark:bg-slate-800/50 dark:text-slate-300">{{ $emp->department?->name ?? '—' }}</td>
                            @for ($d = 1; $d <= $daysInMonth; $d++)
                                @php
                                    $dateObj = \Carbon\Carbon::create($this->record->year, $this->record->month, $d);
                                    $isSpecial = in_array($d, $specialDays, true);
                                    $isSaturday = $dateObj->isSaturday();
                                    $cellItems = $empEntries[$d] ?? [];
                                    $cellDisplay = collect($cellItems)
                                        ->groupBy('block')
                                        ->map(function ($items, $blockName) {
                                            $sum = (float) $items->sum('hours');
                                            return $blockName.rtrim(rtrim(number_format($sum, 2), '0'), '.');
                                        })
                                        ->join(', ');
                                    $tdBg = $isSpecial
                                        ? 'bg-amber-50/50 dark:bg-amber-900/10'
                                        : ($isSaturday ? 'bg-purple-50/50 dark:bg-purple-900/10' : '');
                                @endphp
                                <td class="border-r border-gray-200 p-0 text-center align-top dark:border-white/10 {{ $tdBg }}">
                                    <div x-on:click="document.querySelector('input[data-row=\'{{ $rowIndex }}\'][data-col=\'{{ $d }}\']')?.focus()"
                                        wire:dblclick="openCell('{{ $emp->employee_code }}', '{{ addslashes($emp->name) }}', {{ $d }})"
                                        title="{{ $cellDisplay ? $cellDisplay.' (Double-click for details/author)' : 'Click to enter hours • Double-click for details' }}"
                                        class="cursor-pointer border-b border-gray-100 px-1 py-0.5 text-[11px] leading-tight hover:bg-gray-100 dark:border-white/5 dark:hover:bg-white/10">
                                        @if ($cellDisplay !== '')
                                            <span class="font-semibold text-primary-700 dark:text-primary-300">{{ $cellDisplay }}</span>
                                        @else
                                            <span class="text-gray-300 dark:text-gray-700">&mdash;</span>
                                        @endif
                                    </div>
                                    <input type="text"
                                        inputmode="decimal"
                                        data-row="{{ $rowIndex }}"
                                        data-col="{{ $d }}"
                                        x-on:input="$el.value = $el.value.replace(/[^0-9.]/g, '').replace(/(\..*)\./g, '$1')"
                                        x-on:keydown="moveCell($event, {{ $rowIndex }}, {{ $d }})"
                                        x-on:change="if ($el.value.trim() !== '') { $wire.saveInlineCell('{{ $emp->employee_code }}', {{ $d }}, $el.value); $el.value = ''; }"
                                        placeholder="+"
                                        class="h-6 w-full border-0 bg-transparent px-1 text-center text-[11px] font-medium text-gray-900 placeholder-gray-300 focus:bg-primary-50 focus:outline-none focus:ring-1 focus:ring-inset focus:ring-primary-500 dark:text-white dark:placeholder-gray-700 dark:focus:bg-primary-900/20" />
                                </td>
                            @endfor
                            <td wire:click="openAdjustment('{{ $emp->employee_code }}', '{{ addslashes($emp->name) }}')"
                                title="{{ $adj['remarks'] ?: 'Click to add/edit Normal OT adjustment' }}"
                                class="cursor-pointer border-r border-gray-200 bg-blue-50/20 px-2 py-1.5 text-right font-medium hover:bg-blue-100/60 dark:border-white/10 dark:bg-blue-900/5 dark:hover:bg-blue-900/30 {{ $adj['normal'] != 0 ? 'text-blue-700 dark:text-blue-300' : 'text-gray-400' }}">
                                {{ $adj['normal'] != 0 ? ($adj['normal'] > 0 ? '+' : '').number_format($adj['normal'], 2) : '0.00' }}
                            </td>
                            <td class="border-r border-gray-200 bg-blue-50/40 px-2 py-1.5 text-right font-medium dark:border-white/10 dark:bg-blue-900/10">{{ number_format($normalTotal, 2) }}</td>
                            <td wire:click="openAdjustment('{{ $emp->employee_code }}', '{{ addslashes($emp->name) }}')"
                                title="{{ $adj['remarks'] ?: 'Click to add/edit Special OT adjustment' }}"
                                class="cursor-pointer border-r border-gray-200 bg-amber-50/20 px-2 py-1.5 text-right font-medium hover:bg-amber-100/60 dark:border-white/10 dark:bg-amber-900/5 dark:hover:bg-amber-900/30 {{ $adj['special'] != 0 ? 'text-amber-700 dark:text-amber-300' : 'text-gray-400' }}">
                                {{ $adj['special'] != 0 ? ($adj['special'] > 0 ? '+' : '').number_format($adj['special'], 2) : '0.00' }}
                            </td>
                            <td class="border-r border-gray-200 bg-amber-50/40 px-2 py-1.5 text-right font-medium text-amber-700 dark:border-white/10 dark:bg-amber-900/10 dark:text-amber-300">{{ number_format($specialTotal, 2) }}</td>
                            <td class="bg-gray-50 px-2 py-1.5 text-right font-bold dark:bg-gray-800/50">{{ number_format($grandTotal, 2) }}</td>
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
                                    <div>
                                        <div class="flex items-center gap-2.5">
                                            <span class="rounded-md bg-primary-100 px-2 py-0.5 text-xs font-semibold text-primary-800 dark:bg-primary-900/40 dark:text-primary-300">Block {{ $item['block'] }}</span>
                                            <span class="font-semibold text-gray-900 dark:text-white">{{ number_format($item['hours'], 2) }} hrs</span>
                                        </div>
                                        <div class="mt-1 text-[11px] text-gray-500 dark:text-gray-400">
                                            by {{ $item['created_by_name'] }} &bull; {{ $item['created_at'] }}
                                        </div>
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

    @if ($adjEmployeeCode)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4 backdrop-blur-xs">
            <div class="w-full max-w-md overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xl dark:border-white/10 dark:bg-gray-900">
                <div class="flex items-start justify-between border-b border-gray-200 bg-gray-50 px-5 py-4 dark:border-white/10 dark:bg-gray-800/60">
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="rounded bg-gray-200 px-2 py-0.5 font-mono text-xs font-semibold text-gray-800 dark:bg-white/10 dark:text-gray-200">{{ $adjEmployeeCode }}</span>
                            <h3 class="text-base font-semibold text-gray-900 dark:text-white">{{ $adjEmployeeName }}</h3>
                        </div>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Previous Month OT Adjustments (+ or - hours)</p>
                    </div>
                    <button type="button" wire:click="closeAdjustment" class="rounded-lg p-1 text-gray-400 hover:bg-gray-200 hover:text-gray-700 dark:hover:bg-white/10 dark:hover:text-white">✕</button>
                </div>

                <div class="space-y-4 p-5">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="mb-1.5 block text-xs font-medium text-gray-600 dark:text-gray-300">Normal OT Adj. (×1.5)</label>
                            <input type="number" step="0.5" wire:model="adjNormal" placeholder="0"
                                class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-white/15 dark:bg-gray-800 dark:text-white" />
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs font-medium text-gray-600 dark:text-gray-300">Special OT Adj. (×2)</label>
                            <input type="number" step="0.5" wire:model="adjSpecial" placeholder="0"
                                class="h-10 w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-white/15 dark:bg-gray-800 dark:text-white" />
                        </div>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium text-gray-600 dark:text-gray-300">Remarks</label>
                        <textarea rows="2" wire:model="adjRemarks" placeholder="Reason for adjustment..."
                            class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500 dark:border-white/15 dark:bg-gray-800 dark:text-white"></textarea>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 border-t border-gray-200 bg-gray-50 px-5 py-3 dark:border-white/10 dark:bg-gray-800/60">
                    <button type="button" wire:click="closeAdjustment"
                        class="rounded-lg border border-gray-300 bg-white px-4 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-white/10 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-white/10">
                        Cancel
                    </button>
                    <button type="button" wire:click="saveAdjustment"
                        class="rounded-lg bg-primary-600 px-4 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-primary-500">
                        Save Adjustment
                    </button>
                </div>
            </div>
        </div>
    @endif
</x-filament-panels::page>
