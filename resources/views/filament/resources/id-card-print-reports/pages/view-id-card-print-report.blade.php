<x-filament-panels::page>
    {{-- Header Info / Meta Bar --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between p-4 mb-4 rounded-xl bg-gradient-to-r from-sky-50 to-indigo-50 dark:from-sky-950/40 dark:to-indigo-950/40 border border-sky-200 dark:border-sky-800 text-sky-950 dark:text-sky-100 shadow-sm gap-3">
        <div class="flex items-center gap-3">
            <div class="p-2 rounded-xl bg-sky-600 text-white shadow-sm">
                <x-heroicon-m-identification class="w-6 h-6" />
            </div>
            <div>
                <div class="text-base font-bold flex items-center gap-2">
                    <span>{{ $record->title }}</span>
                    <span class="text-[10px] px-2 py-0.5 rounded-full bg-sky-200 dark:bg-sky-800 text-sky-800 dark:text-sky-200 uppercase tracking-wider font-semibold">
                        {{ ucfirst($record->status) }}
                    </span>
                </div>
                <div class="text-xs text-sky-700 dark:text-sky-300 mt-0.5 flex items-center gap-3">
                    <span>Printed Date: <strong>{{ $record->batch_date?->format('d M, Y') }}</strong></span>
                    @if($record->creator)
                        <span>• Uploaded by: <strong>{{ $record->creator->name }}</strong></span>
                    @endif
                    @if($record->notes)
                        <span>• Notes: <em>{{ $record->notes }}</em></span>
                    @endif
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('id-card-print-reports.print', ['report' => $record->id, 'department' => $activeDepartment !== 'ALL' ? $activeDepartment : null, 'status' => $activeStatus !== 'ALL' ? $activeStatus : null]) }}"
               target="_blank"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-sm transition">
                <x-heroicon-m-printer class="w-4 h-4" />
                <span>Print Distribution Sheet</span>
            </a>
        </div>
    </div>

    {{-- Summary Widgets / Metric Cards (like Tips Report) --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-6 gap-3 mb-4">
        {{-- Total Cards --}}
        <div class="fi-section rounded-xl bg-white p-3 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 border-l-4 border-l-sky-500">
            <div class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Cards</div>
            <div class="text-2xl font-bold text-sky-600 dark:text-sky-400 mt-0.5">
                {{ $this->totalCount }}
            </div>
            <div class="text-[11px] text-gray-400 mt-1">Batch total cards</div>
        </div>

        {{-- Sent for Print --}}
        <div class="fi-section rounded-xl bg-white p-3 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 border-l-4 border-l-purple-500">
            <div class="text-xs font-medium text-gray-500 dark:text-gray-400">Sent for Print</div>
            <div class="text-2xl font-bold text-purple-600 dark:text-purple-400 mt-0.5">
                {{ $this->sentForPrintCount }}
            </div>
            <div class="text-[11px] text-purple-500/80 mt-1">In printing queue</div>
        </div>

        {{-- Printed --}}
        <div class="fi-section rounded-xl bg-white p-3 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 border-l-4 border-l-blue-500">
            <div class="text-xs font-medium text-gray-500 dark:text-gray-400">Printed</div>
            <div class="text-2xl font-bold text-blue-600 dark:text-blue-400 mt-0.5">
                {{ $this->printedCount }}
            </div>
            <div class="text-[11px] text-blue-500/80 mt-1">Received in office</div>
        </div>

        {{-- In Office --}}
        <div class="fi-section rounded-xl bg-white p-3 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 border-l-4 border-l-amber-500">
            <div class="text-xs font-medium text-gray-500 dark:text-gray-400">In Office</div>
            <div class="text-2xl font-bold text-amber-600 dark:text-amber-400 mt-0.5">
                {{ $this->inOfficeCount }}
            </div>
            <div class="text-[11px] text-amber-500/80 mt-1">Awaiting pickup</div>
        </div>

        {{-- Released --}}
        <div class="fi-section rounded-xl bg-white p-3 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 border-l-4 border-l-emerald-500">
            <div class="text-xs font-medium text-gray-500 dark:text-gray-400">Released</div>
            <div class="text-2xl font-bold text-emerald-600 dark:text-emerald-400 mt-0.5">
                {{ $this->releasedCount }}
            </div>
            <div class="text-[11px] text-emerald-500/80 mt-1">Handed over to staff</div>
        </div>

        {{-- On Hold --}}
        <div class="fi-section rounded-xl bg-white p-3 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 border-l-4 border-l-rose-500">
            <div class="text-xs font-medium text-gray-500 dark:text-gray-400">On Hold</div>
            <div class="text-2xl font-bold text-rose-600 dark:text-rose-400 mt-0.5">
                {{ $this->onHoldCount }}
            </div>
            <div class="text-[11px] text-rose-500/80 mt-1">Held / Issues</div>
        </div>
    </div>

    {{-- Filter Toolbar: Status & Active Department --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 px-3.5 py-2.5 bg-gray-50 dark:bg-gray-800/60 rounded-xl mb-3 border border-gray-200 dark:border-gray-700 text-xs">
        {{-- Status Filter Badges --}}
        <div class="flex flex-wrap items-center gap-1.5">
            <span class="font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider text-[11px] mr-1">Status:</span>
            @foreach(['ALL' => 'All Status', 'sent for print' => 'Sent for Print', 'printed' => 'Printed', 'in office' => 'In Office', 'released' => 'Released', 'on hold' => 'On Hold'] as $stKey => $stLabel)
                @php $isStActive = $activeStatus === $stKey; @endphp
                <button type="button"
                        wire:click="setActiveStatus('{{ $stKey }}')"
                        class="px-2.5 py-1 rounded-lg text-xs font-medium transition cursor-pointer
                        {{ $isStActive
                            ? 'bg-sky-600 text-white shadow-xs'
                            : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600 border border-gray-200 dark:border-gray-600' }}">
                    {{ $stLabel }}
                </button>
            @endforeach
        </div>

        {{-- Active Department & Tip --}}
        <div class="text-xs text-gray-500 dark:text-gray-400 flex items-center gap-2">
            <span>Filtering by Department: <strong class="text-gray-900 dark:text-white">{{ $activeDepartment }}</strong> ({{ $this->getDepartmentCount($activeDepartment) }})</span>
        </div>
    </div>

    {{-- Department Navigation Pills / Tabs --}}
    <div class="flex flex-wrap gap-1.5 p-1.5 bg-gray-100 dark:bg-gray-800 rounded-xl mb-4 border border-gray-200 dark:border-gray-700">
        @foreach($this->departments as $dept)
            @php
                $count = $this->getDepartmentCount($dept);
                $isActive = $activeDepartment === $dept;
            @endphp
            <button type="button"
                    wire:click="setActiveDepartment('{{ $dept }}')"
                    class="px-3 py-1.5 rounded-lg text-xs font-medium transition flex items-center gap-1.5 cursor-pointer
                    {{ $isActive
                        ? 'bg-white dark:bg-gray-900 text-primary-600 dark:text-primary-400 shadow-xs border border-gray-200 dark:border-gray-700 font-semibold'
                        : 'text-gray-600 dark:text-gray-400 hover:bg-white/60 dark:hover:bg-gray-700/60' }}">
                <span>{{ $dept }}</span>
                <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold {{ $isActive ? 'bg-primary-100 text-primary-700 dark:bg-primary-950 dark:text-primary-300' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300' }}">
                    {{ $count }}
                </span>
            </button>
        @endforeach
    </div>

    {{-- Items Table --}}
    {{ $this->table }}
</x-filament-panels::page>
