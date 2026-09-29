<x-filament-panels::page>
    {{-- Header Info / Meta Bar --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between p-4 mb-4 rounded-xl bg-gradient-to-r from-amber-50 to-orange-50 dark:from-amber-950/40 dark:to-orange-950/40 border border-amber-200 dark:border-amber-800 text-amber-950 dark:text-amber-100 shadow-sm gap-3">
        <div class="flex items-center gap-3">
            <div class="p-2.5 rounded-xl bg-amber-600 text-white shadow-sm">
                <x-heroicon-m-calendar-days class="w-6 h-6" />
            </div>
            <div>
                <div class="text-base font-bold flex items-center gap-2">
                    <span>{{ $record->period }} Manual Roster</span>
                    <span class="text-[10px] px-2 py-0.5 rounded-full uppercase tracking-wider font-semibold
                        @if($record->status === 'completed') bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800
                        @elseif($record->status === 'in_progress') bg-amber-100 dark:bg-amber-950 text-amber-800 dark:text-amber-200 border border-amber-300 dark:border-amber-800
                        @else bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-300 dark:border-gray-700
                        @endif">
                        {{ $record->status === 'in_progress' ? 'In Progress' : ucfirst($record->status) }}
                    </span>
                </div>
                <div class="text-xs text-amber-800 dark:text-amber-300 mt-0.5 flex flex-wrap items-center gap-3">
                    <span>Year: <strong>{{ $record->year }}</strong></span>
                    <span>• Month: <strong>{{ $record->month_name }}</strong></span>
                    @if($record->creator)
                        <span>• Created by: <strong>{{ $record->creator->name }}</strong></span>
                    @endif
                    @if($record->notes)
                        <span>• Notes: <em>{{ $record->notes }}</em></span>
                    @endif
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ \App\Filament\Resources\MonthlyManualRosters\MonthlyManualRosterResource::getUrl('index') }}"
               class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 transition">
                <x-heroicon-m-arrow-left class="w-4 h-4" />
                <span>All Monthly Rosters</span>
            </a>
        </div>
    </div>

    {{-- Metric KPI Cards --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-4">
        {{-- Total Employees --}}
        <div class="fi-section rounded-xl bg-white p-3.5 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 border-l-4 border-l-sky-500">
            <div class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Staff</div>
            <div class="text-2xl font-bold text-sky-600 dark:text-sky-400 mt-0.5">
                {{ $this->totalCount }}
            </div>
            <div class="text-[11px] text-gray-400 mt-1">Manual roster staff</div>
        </div>

        {{-- Updated in HRMS --}}
        <div class="fi-section rounded-xl bg-white p-3.5 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 border-l-4 border-l-emerald-500">
            <div class="text-xs font-medium text-gray-500 dark:text-gray-400">Updated in HRMS</div>
            <div class="text-2xl font-bold text-emerald-600 dark:text-emerald-400 mt-0.5">
                {{ $this->updatedCount }}
            </div>
            <div class="text-[11px] text-emerald-600/80 dark:text-emerald-400/80 mt-1">Roster entry completed</div>
        </div>

        {{-- Pending in HRMS --}}
        <div class="fi-section rounded-xl bg-white p-3.5 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 border-l-4 border-l-amber-500">
            <div class="text-xs font-medium text-gray-500 dark:text-gray-400">Pending Entry</div>
            <div class="text-2xl font-bold text-amber-600 dark:text-amber-400 mt-0.5">
                {{ $this->pendingCount }}
            </div>
            <div class="text-[11px] text-amber-600/80 dark:text-amber-400/80 mt-1">Awaiting roster in HRMS</div>
        </div>

        {{-- Completion Rate --}}
        <div class="fi-section rounded-xl bg-white p-3.5 shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 border-l-4 border-l-purple-500">
            <div class="text-xs font-medium text-gray-500 dark:text-gray-400">Progress Rate</div>
            <div class="text-2xl font-bold text-purple-600 dark:text-purple-400 mt-0.5">
                {{ $this->completionPercentage }}%
            </div>
            {{-- Progress bar --}}
            <div class="w-full bg-gray-100 dark:bg-gray-800 rounded-full h-1.5 mt-2 overflow-hidden">
                <div class="bg-purple-500 h-1.5 rounded-full transition-all duration-300" style="width: {{ $this->completionPercentage }}%"></div>
            </div>
        </div>
    </div>

    {{-- Filter Toolbar: Status & Active Department --}}
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 px-3.5 py-2.5 bg-gray-50 dark:bg-gray-800/60 rounded-xl mb-3 border border-gray-200 dark:border-gray-700 text-xs">
        {{-- Status Filter Badges --}}
        <div class="flex flex-wrap items-center gap-1.5">
            <span class="font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider text-[11px] mr-1">Roster Status:</span>
            @foreach(['ALL' => 'All Staff (' . $this->totalCount . ')', 'pending' => 'Pending (' . $this->pendingCount . ')', 'updated' => 'Updated (' . $this->updatedCount . ')'] as $stKey => $stLabel)
                @php $isStActive = $activeStatus === $stKey; @endphp
                <button type="button"
                        wire:click="setActiveStatus('{{ $stKey }}')"
                        class="px-2.5 py-1 rounded-lg text-xs font-medium transition cursor-pointer
                        {{ $isStActive
                            ? 'bg-amber-600 text-white shadow-xs'
                            : 'bg-white dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600 border border-gray-200 dark:border-gray-600' }}">
                    {{ $stLabel }}
                </button>
            @endforeach
        </div>

        {{-- Active Department summary --}}
        <div class="text-xs text-gray-500 dark:text-gray-400 flex items-center gap-2">
            <span>Department: <strong class="text-gray-900 dark:text-white">{{ $activeDepartment }}</strong> ({{ $this->getDepartmentCount($activeDepartment) }})</span>
        </div>
    </div>

    {{-- Department Navigation Pills / Tabs --}}
    @if(count($this->departments) > 1)
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
                            ? 'bg-white dark:bg-gray-900 text-amber-600 dark:text-amber-400 shadow-xs border border-gray-200 dark:border-gray-700 font-semibold'
                            : 'text-gray-600 dark:text-gray-400 hover:bg-white/60 dark:hover:bg-gray-700/60' }}">
                    <span>{{ $dept }}</span>
                    <span class="px-1.5 py-0.5 rounded-full text-[10px] font-bold {{ $isActive ? 'bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-300' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300' }}">
                        {{ $count }}
                    </span>
                </button>
            @endforeach
        </div>
    @endif

    {{-- Checklist Table --}}
    {{ $this->table }}
</x-filament-panels::page>
