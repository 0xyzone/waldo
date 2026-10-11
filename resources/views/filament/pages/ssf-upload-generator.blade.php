<x-filament-panels::page>
    @php
        $activeRecords = $this->getActiveRecords();
        $filteredRecords = $this->getFilteredRecords();
        $totalActive = count($activeRecords);
        $missingCount = $this->getMissingRecordsCount();
        $readyCount = $totalActive - $missingCount;
        $excludedCount = count($this->excludedCodes);
        $canDownload = $totalActive > 0 && $missingCount === 0;
    @endphp

    <style>
        /* SSF Table Header Styling - Light Mode */
        .ssf-table-header,
        .ssf-table-header > tr,
        .ssf-table-header th {
            background-color: #f1f5f9 !important;
            color: #0f172a !important;
        }

        /* SSF Table Header Styling - Dark Mode (Crisp White Text on Dark Navy Background) */
        html.dark .ssf-table-header,
        html.dark .ssf-table-header > tr,
        html.dark .ssf-table-header th,
        .dark .ssf-table-header,
        .dark .ssf-table-header > tr,
        .dark .ssf-table-header th {
            background-color: #0f172a !important;
            color: #ffffff !important;
        }
    </style>

    <div class="space-y-6">
        {{-- Hero Header & Summary Banner --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 p-6 text-white shadow-xl border border-indigo-900/50">
            <div class="absolute -right-10 -bottom-10 h-48 w-48 rounded-full bg-indigo-500/10 blur-2xl pointer-events-none"></div>
            <div class="absolute -left-10 -top-10 h-48 w-48 rounded-full bg-emerald-500/10 blur-2xl pointer-events-none"></div>

            <div class="relative z-10 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <div class="inline-flex items-center gap-2 rounded-full bg-indigo-500/20 px-3 py-1 text-xs font-semibold text-indigo-300 ring-1 ring-inset ring-indigo-400/30 mb-2">
                        <span class="inline-block h-2 w-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        Social Security Fund (SSF) Portal Assistant
                    </div>
                    <h2 class="text-2xl font-black tracking-tight text-white">SSF Upload File Generator</h2>
                    <p class="mt-1 text-sm text-slate-300 max-w-2xl leading-relaxed">
                        Compiles active employees without an SSF ID into the exact SSF entry format. Review missing details, exclude records if needed, and export a ready-to-upload Excel file.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <button
                        type="button"
                        wire:click="regenerateList"
                        wire:loading.attr="disabled"
                        wire:target="regenerateList"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl font-semibold text-sm bg-slate-800 hover:bg-slate-700 active:bg-slate-900 text-slate-100 border border-slate-700 shadow-sm transition-all duration-200 cursor-pointer disabled:opacity-50"
                        title="Reloads all active employees from the database"
                    >
                        <x-heroicon-o-arrow-path wire:loading.remove wire:target="regenerateList" class="w-4 h-4 text-indigo-400" />
                        <x-heroicon-o-arrow-path wire:loading wire:target="regenerateList" class="w-4 h-4 text-indigo-400 animate-spin" />
                        <span>Regenerate List</span>
                    </button>

                    <button
                        type="button"
                        wire:click="downloadExcel"
                        wire:loading.attr="disabled"
                        wire:target="downloadExcel"
                        @disabled(! $canDownload)
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-semibold text-sm transition-all duration-200 shadow-lg cursor-pointer {{ $canDownload ? 'bg-emerald-600 hover:bg-emerald-500 active:bg-emerald-700 text-white shadow-emerald-900/30' : 'bg-slate-800 text-slate-500 border border-slate-700/60 cursor-not-allowed opacity-60' }}"
                        title="{{ $canDownload ? 'Download SSF Excel file' : ($totalActive === 0 ? 'No records to download' : 'Download disabled: Resolve missing records or remove them first') }}"
                    >
                        <x-heroicon-o-arrow-down-tray wire:loading.remove wire:target="downloadExcel" class="w-4 h-4 {{ $canDownload ? 'text-white' : 'text-slate-500' }}" />
                        <x-heroicon-o-arrow-path wire:loading wire:target="downloadExcel" class="w-4 h-4 animate-spin text-white" />
                        <span>Download Excel (.xlsx)</span>
                    </button>
                </div>
            </div>

            @if ($lastGeneratedAt)
                <div class="mt-4 pt-3 border-t border-slate-800/80 flex items-center gap-2 text-xs text-slate-400">
                    <x-heroicon-m-clock class="w-3.5 h-3.5 text-slate-500" />
                    <span>Last synchronized from employee database: <strong class="text-slate-300 font-medium">{{ $lastGeneratedAt }}</strong></span>
                </div>
            @endif
        </div>

        {{-- Top Metrics Overview --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Total Active Records --}}
            <div class="bg-white dark:bg-gray-900 p-5 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total in Batch</p>
                    <p class="text-3xl font-black text-gray-900 dark:text-white mt-1">{{ $totalActive }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Active without SSF ID</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 border border-indigo-100 dark:border-indigo-900/40 flex items-center justify-center text-indigo-600 dark:text-indigo-400">
                    <x-heroicon-o-users class="w-6 h-6" />
                </div>
            </div>

            {{-- Ready For Download --}}
            <div class="bg-white dark:bg-gray-900 p-5 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-emerald-600 dark:text-emerald-400 uppercase tracking-wider">Ready for Upload</p>
                    <p class="text-3xl font-black text-emerald-600 dark:text-emerald-400 mt-1">{{ $readyCount }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">100% complete records</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-100 dark:border-emerald-900/40 flex items-center justify-center text-emerald-600 dark:text-emerald-400">
                    <x-heroicon-o-check-badge class="w-6 h-6" />
                </div>
            </div>

            {{-- Incomplete / Missing Values --}}
            <div class="bg-white dark:bg-gray-900 p-5 rounded-2xl border {{ $missingCount > 0 ? 'border-rose-300 dark:border-rose-900/60 bg-rose-50/20' : 'border-gray-200 dark:border-gray-800' }} shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold {{ $missingCount > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-gray-500 dark:text-gray-400' }} uppercase tracking-wider">Missing Required Data</p>
                    <p class="text-3xl font-black {{ $missingCount > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-gray-900 dark:text-white' }} mt-1">{{ $missingCount }}</p>
                    <p class="text-xs {{ $missingCount > 0 ? 'text-rose-600/80 dark:text-rose-400/80' : 'text-gray-500 dark:text-gray-400' }} mt-1">Blocks Excel download</p>
                </div>
                <div class="w-12 h-12 rounded-xl {{ $missingCount > 0 ? 'bg-rose-100 dark:bg-rose-950/60 text-rose-600 dark:text-rose-400 border border-rose-200 dark:border-rose-900/50' : 'bg-gray-100 dark:bg-gray-800 text-gray-400' }} flex items-center justify-center">
                    <x-heroicon-o-exclamation-triangle class="w-6 h-6" />
                </div>
            </div>

            {{-- Skipped / Excluded --}}
            <div class="bg-white dark:bg-gray-900 p-5 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Skipped / Excluded</p>
                    <p class="text-3xl font-black text-gray-900 dark:text-white mt-1">{{ $excludedCount }}</p>
                    @if ($excludedCount > 0)
                        <button
                            type="button"
                            wire:click="restoreAllExcluded"
                            class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline mt-1 cursor-pointer"
                        >
                            Restore all skipped
                        </button>
                    @else
                        <p class="text-xs text-gray-400 mt-1">No skipped records</p>
                    @endif
                </div>
                <div class="w-12 h-12 rounded-xl bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700/60 flex items-center justify-center text-gray-500 dark:text-gray-400">
                    <x-heroicon-o-trash class="w-6 h-6" />
                </div>
            </div>
        </div>

        {{-- Download Guard Notification Banner --}}
        @if ($missingCount > 0)
            <div class="rounded-2xl bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-900/60 p-4 text-rose-900 dark:text-rose-200 shadow-sm flex items-start gap-3">
                <x-heroicon-s-exclamation-circle class="w-5 h-5 text-rose-500 shrink-0 mt-0.5" />
                <div class="text-sm">
                    <strong class="font-bold">Download Blocked:</strong>
                    {{ $missingCount }} record(s) currently have missing required values (marked in red below).
                    You can either:
                    <ul class="list-disc list-inside mt-1 space-y-0.5 text-xs text-rose-800 dark:text-rose-300">
                        <li>Click the <strong>Edit icon</strong> beside an employee to update their profile in a new tab, then click <strong>Regenerate List</strong>.</li>
                        <li>Click the <strong>Trash icon</strong> on incomplete rows to skip them from this export, which will immediately allow download once all remaining records are valid.</li>
                    </ul>
                </div>
            </div>
        @elseif ($totalActive > 0)
            <div class="rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-900/60 p-4 text-emerald-900 dark:text-emerald-200 shadow-sm flex items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <x-heroicon-s-check-circle class="w-5 h-5 text-emerald-500 shrink-0" />
                    <div class="text-sm">
                        <strong class="font-bold">All Records Complete!</strong> Every active record has all required fields validated. You can proceed with the Excel download.
                    </div>
                </div>
                <button
                    type="button"
                    wire:click="downloadExcel"
                    class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-lg text-xs font-bold bg-emerald-600 hover:bg-emerald-500 text-white shadow-sm transition-colors cursor-pointer shrink-0"
                >
                    <x-heroicon-m-arrow-down-tray class="w-4 h-4" />
                    Download Now
                </button>
            </div>
        @else
            <div class="rounded-2xl bg-slate-100 dark:bg-slate-800/60 border border-slate-200 dark:border-slate-700/60 p-4 text-slate-700 dark:text-slate-300 text-sm">
                No active employees without SSF ID found in the database.
            </div>
        @endif

        {{-- Filters & Search Controls --}}
        <div class="bg-white dark:bg-gray-900 p-4 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            {{-- Tabs --}}
            <div class="inline-flex p-1 bg-gray-100 dark:bg-gray-800/80 rounded-xl text-xs font-semibold">
                <button
                    type="button"
                    wire:click="$set('filterStatus', 'all')"
                    class="px-3.5 py-1.5 rounded-lg transition-all cursor-pointer {{ $filterStatus === 'all' ? 'bg-white dark:bg-gray-900 text-gray-900 dark:text-white shadow-xs font-bold' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white' }}"
                >
                    All Records ({{ $totalActive }})
                </button>
                <button
                    type="button"
                    wire:click="$set('filterStatus', 'missing')"
                    class="px-3.5 py-1.5 rounded-lg transition-all cursor-pointer flex items-center gap-1.5 {{ $filterStatus === 'missing' ? 'bg-white dark:bg-gray-900 text-rose-600 dark:text-rose-400 shadow-xs font-bold' : 'text-gray-600 dark:text-gray-400 hover:text-rose-600 dark:hover:text-rose-400' }}"
                >
                    <span>Missing Data</span>
                    @if ($missingCount > 0)
                        <span class="px-1.5 py-0.2 bg-rose-100 dark:bg-rose-900/50 text-rose-700 dark:text-rose-300 rounded-full text-[10px] font-bold">
                            {{ $missingCount }}
                        </span>
                    @endif
                </button>
                <button
                    type="button"
                    wire:click="$set('filterStatus', 'complete')"
                    class="px-3.5 py-1.5 rounded-lg transition-all cursor-pointer {{ $filterStatus === 'complete' ? 'bg-white dark:bg-gray-900 text-emerald-600 dark:text-emerald-400 shadow-xs font-bold' : 'text-gray-600 dark:text-gray-400 hover:text-emerald-600 dark:hover:text-emerald-400' }}"
                >
                    Complete ({{ $readyCount }})
                </button>
            </div>

            {{-- Live Search Input --}}
            <div class="relative w-full md:w-80">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                    <x-heroicon-m-magnifying-glass class="w-4 h-4" />
                </div>
                <input
                    type="text"
                    wire:model.live.debounce.250ms="search"
                    placeholder="Search name, code, citizenship, post..."
                    class="w-full pl-9 pr-8 py-2 text-xs rounded-xl bg-gray-50 dark:bg-gray-800/60 border border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 transition-all"
                />
                @if ($search !== '')
                    <button
                        type="button"
                        wire:click="$set('search', '')"
                        class="absolute inset-y-0 right-0 pr-2.5 flex items-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 cursor-pointer"
                    >
                        <x-heroicon-m-x-mark class="w-4 h-4" />
                    </button>
                @endif
            </div>
        </div>

        {{-- Table Container --}}
        <div class="bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm overflow-hidden">
            <div class="overflow-x-auto max-h-[640px] relative">
                <table class="w-full text-left text-xs whitespace-nowrap">
                    <thead class="ssf-table-header bg-slate-100 dark:bg-slate-900 text-slate-900 dark:text-white font-bold sticky top-0 z-20 shadow-xs border-b border-gray-200 dark:border-gray-700">
                        <tr>
                            <th class="py-3 px-3 w-12 text-center bg-slate-100 dark:bg-slate-900 text-slate-900 dark:text-white font-bold">Sno</th>
                            <th class="py-3 px-3 bg-slate-100 dark:bg-slate-900 text-slate-900 dark:text-white font-bold">Actions</th>
                            <th class="py-3 px-3 bg-slate-100 dark:bg-slate-900 text-slate-900 dark:text-white font-bold">Employee</th>
                            <th class="py-3 px-3 bg-slate-100 dark:bg-slate-900 text-slate-900 dark:text-white font-bold">Status</th>
                            <th class="py-3 px-3 bg-slate-100 dark:bg-slate-900 text-slate-900 dark:text-white font-bold">First name</th>
                            <th class="py-3 px-3 bg-slate-100 dark:bg-slate-900 text-slate-900 dark:text-white font-bold">Mid name</th>
                            <th class="py-3 px-3 bg-slate-100 dark:bg-slate-900 text-slate-900 dark:text-white font-bold">Last Name</th>
                            <th class="py-3 px-3 bg-slate-100 dark:bg-slate-900 text-slate-900 dark:text-white font-bold">Gender</th>
                            <th class="py-3 px-3 bg-slate-100 dark:bg-slate-900 text-slate-900 dark:text-white font-bold">DOB(YYYY.MM.DD)</th>
                            <th class="py-3 px-3 bg-slate-100 dark:bg-slate-900 text-slate-900 dark:text-white font-bold">Country</th>
                            <th class="py-3 px-3 bg-slate-100 dark:bg-slate-900 text-slate-900 dark:text-white font-bold">Email</th>
                            <th class="py-3 px-3 bg-slate-100 dark:bg-slate-900 text-slate-900 dark:text-white font-bold">Contact Type</th>
                            <th class="py-3 px-3 bg-slate-100 dark:bg-slate-900 text-slate-900 dark:text-white font-bold">Contact No</th>
                            <th class="py-3 px-3 bg-slate-100 dark:bg-slate-900 text-slate-900 dark:text-white font-bold">Document</th>
                            <th class="py-3 px-3 bg-slate-100 dark:bg-slate-900 text-slate-900 dark:text-white font-bold">Document No</th>
                            <th class="py-3 px-3 bg-slate-100 dark:bg-slate-900 text-slate-900 dark:text-white font-bold">Issuing Office</th>
                            <th class="py-3 px-3 bg-slate-100 dark:bg-slate-900 text-slate-900 dark:text-white font-bold">Issued Date(YYYY.MM.DD)</th>
                            <th class="py-3 px-3 bg-slate-100 dark:bg-slate-900 text-slate-900 dark:text-white font-bold">Employement Type</th>
                            <th class="py-3 px-3 bg-slate-100 dark:bg-slate-900 text-slate-900 dark:text-white font-bold">Post</th>
                            <th class="py-3 px-3 bg-slate-100 dark:bg-slate-900 text-slate-900 dark:text-white font-bold">Join Date(YYYY.MM.DD)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800 text-gray-700 dark:text-gray-300 font-normal">
                        @forelse ($filteredRecords as $record)
                            @php
                                $isMissing = ! $record['is_complete'];
                            @endphp
                            <tr class="transition-colors hover:bg-gray-50/80 dark:hover:bg-gray-800/40 {{ $isMissing ? 'bg-rose-50/30 dark:bg-rose-950/20' : '' }}">
                                {{-- Sno --}}
                                <td class="py-2.5 px-3 text-center font-bold text-gray-500 dark:text-gray-400">
                                    {{ $record['sno'] }}
                                </td>

                                {{-- Actions --}}
                                <td class="py-2.5 px-3">
                                    <div class="flex items-center gap-1.5">
                                        <a
                                            href="{{ route('filament.kamkaj.resources.employees.index', ['tableSearch' => $record['employee_code']]) }}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="inline-flex items-center justify-center w-7 h-7 rounded-lg text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-950/60 transition-colors"
                                            title="Open employee in a new tab to edit profile"
                                        >
                                            <x-heroicon-m-arrow-top-right-on-square class="w-4 h-4" />
                                        </a>

                                        <button
                                            type="button"
                                            wire:click="deleteRecord('{{ $record['employee_code'] }}')"
                                            class="inline-flex items-center justify-center w-7 h-7 rounded-lg text-rose-500 hover:text-rose-700 hover:bg-rose-100 dark:hover:bg-rose-950/60 transition-colors cursor-pointer"
                                            title="Skip this record from SSF download"
                                        >
                                            <x-heroicon-m-trash class="w-4 h-4" />
                                        </button>
                                    </div>
                                </td>

                                {{-- Employee Identifier --}}
                                <td class="py-2.5 px-3">
                                    <div class="font-semibold text-gray-900 dark:text-white">{{ $record['name'] }}</div>
                                    <div class="text-[10px] text-gray-400 font-mono">{{ $record['employee_code'] }}</div>
                                </td>

                                {{-- Status / Validation --}}
                                <td class="py-2.5 px-3">
                                    @if ($record['is_complete'])
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/80 dark:text-emerald-300">
                                            <x-heroicon-m-check class="w-3 h-3" />
                                            Ready
                                        </span>
                                    @else
                                        <div class="space-y-1">
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800 dark:bg-rose-950/80 dark:text-rose-300" title="{{ implode(', ', $record['missing_fields']) }}">
                                                <x-heroicon-m-exclamation-triangle class="w-3 h-3" />
                                                Missing ({{ count($record['missing_fields']) }})
                                            </span>
                                            <div class="text-[10px] text-rose-600 dark:text-rose-400 max-w-[140px] truncate" title="{{ implode(', ', $record['missing_fields']) }}">
                                                {{ implode(', ', $record['missing_fields']) }}
                                            </div>
                                        </div>
                                    @endif
                                </td>

                                {{-- First name --}}
                                <td class="py-2.5 px-3">
                                    @if (blank($record['first_name']))
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-700 dark:bg-rose-900/60 dark:text-rose-300">Missing</span>
                                    @else
                                        {{ $record['first_name'] }}
                                    @endif
                                </td>

                                {{-- Mid name (optional) --}}
                                <td class="py-2.5 px-3 text-gray-500 dark:text-gray-400">
                                    {{ $record['mid_name'] ?: '—' }}
                                </td>

                                {{-- Last Name --}}
                                <td class="py-2.5 px-3">
                                    @if (blank($record['last_name']))
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-700 dark:bg-rose-900/60 dark:text-rose-300">Missing</span>
                                    @else
                                        {{ $record['last_name'] }}
                                    @endif
                                </td>

                                {{-- Gender --}}
                                <td class="py-2.5 px-3">
                                    @if (blank($record['gender']))
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-700 dark:bg-rose-900/60 dark:text-rose-300">Missing</span>
                                    @else
                                        {{ $record['gender'] }}
                                    @endif
                                </td>

                                {{-- DOB(YYYY.MM.DD) --}}
                                <td class="py-2.5 px-3 font-mono">
                                    @if (blank($record['dob_bs']))
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-700 dark:bg-rose-900/60 dark:text-rose-300">Missing</span>
                                    @else
                                        {{ $record['dob_bs'] }}
                                    @endif
                                </td>

                                {{-- Country --}}
                                <td class="py-2.5 px-3 font-medium text-slate-600 dark:text-slate-400">
                                    {{ $record['country'] }}
                                </td>

                                {{-- Email --}}
                                <td class="py-2.5 px-3">
                                    @if (blank($record['email']))
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-700 dark:bg-rose-900/60 dark:text-rose-300">Missing</span>
                                    @else
                                        <span class="font-mono text-xs">{{ $record['email'] }}</span>
                                    @endif
                                </td>

                                {{-- Contact Type --}}
                                <td class="py-2.5 px-3 text-slate-600 dark:text-slate-400">
                                    {{ $record['contact_type'] }}
                                </td>

                                {{-- Contact No --}}
                                <td class="py-2.5 px-3 font-mono">
                                    @if (blank($record['contact_no']))
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-700 dark:bg-rose-900/60 dark:text-rose-300">Missing</span>
                                    @else
                                        {{ $record['contact_no'] }}
                                    @endif
                                </td>

                                {{-- Document --}}
                                <td class="py-2.5 px-3 text-slate-600 dark:text-slate-400">
                                    {{ $record['document'] }}
                                </td>

                                {{-- Document No --}}
                                <td class="py-2.5 px-3 font-mono">
                                    @if (blank($record['document_no']))
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-700 dark:bg-rose-900/60 dark:text-rose-300">Missing</span>
                                    @else
                                        {{ $record['document_no'] }}
                                    @endif
                                </td>

                                {{-- Issuing Office --}}
                                <td class="py-2.5 px-3">
                                    @if (blank($record['issuing_office']))
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-700 dark:bg-rose-900/60 dark:text-rose-300">Missing</span>
                                    @else
                                        {{ $record['issuing_office'] }}
                                    @endif
                                </td>

                                {{-- Issued Date(YYYY.MM.DD) --}}
                                <td class="py-2.5 px-3 font-mono">
                                    @if (blank($record['issued_date']))
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-700 dark:bg-rose-900/60 dark:text-rose-300">Missing</span>
                                    @else
                                        {{ $record['issued_date'] }}
                                    @endif
                                </td>

                                {{-- Employement Type --}}
                                <td class="py-2.5 px-3 text-slate-600 dark:text-slate-400">
                                    {{ $record['employement_type'] }}
                                </td>

                                {{-- Post --}}
                                <td class="py-2.5 px-3">
                                    @if (blank($record['post']))
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-700 dark:bg-rose-900/60 dark:text-rose-300">Missing</span>
                                    @else
                                        <span class="font-medium text-gray-900 dark:text-gray-100">{{ $record['post'] }}</span>
                                    @endif
                                </td>

                                {{-- Join Date(YYYY.MM.DD) --}}
                                <td class="py-2.5 px-3 font-mono">
                                    @if (blank($record['join_date']))
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-rose-100 text-rose-700 dark:bg-rose-900/60 dark:text-rose-300">Missing</span>
                                    @else
                                        {{ $record['join_date'] }}
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="20" class="py-12 text-center text-gray-400 dark:text-gray-500">
                                    <div class="flex flex-col items-center justify-center">
                                        <x-heroicon-o-document-magnifying-glass class="w-10 h-10 text-gray-300 dark:text-gray-600 mb-2" />
                                        <p class="font-medium text-sm">No matching employee records found.</p>
                                        <p class="text-xs text-gray-400 mt-1">Try clearing your search query or selecting a different status filter.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-gray-100 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-800/30 flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                <span>Showing <strong class="text-gray-700 dark:text-gray-300 font-semibold">{{ count($filteredRecords) }}</strong> of <strong class="text-gray-700 dark:text-gray-300 font-semibold">{{ $totalActive }}</strong> active records</span>
                <span>Headings match official SSF upload template • Sheet: <code class="px-1.5 py-0.5 bg-gray-200 dark:bg-gray-700 rounded text-gray-700 dark:text-gray-300 font-mono">Entry</code></span>
            </div>
        </div>

        {{-- Excluded Records List (if any records were skipped) --}}
        @if ($excludedCount > 0)
            <div class="bg-gray-50 dark:bg-gray-900/50 rounded-2xl border border-dashed border-gray-300 dark:border-gray-700 p-5">
                <div class="flex items-center justify-between mb-3">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-eye-slash class="w-4 h-4 text-gray-400" />
                        <h4 class="text-xs font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">
                            Skipped Records ({{ $excludedCount }})
                        </h4>
                    </div>
                    <button
                        type="button"
                        wire:click="restoreAllExcluded"
                        class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:underline cursor-pointer"
                    >
                        Restore All to List
                    </button>
                </div>
                <div class="flex flex-wrap gap-2">
                    @foreach ($this->records as $r)
                        @if (in_array($r['employee_code'], $this->excludedCodes, true))
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 shadow-2xs">
                                <span class="font-medium">{{ $r['name'] }}</span>
                                <span class="text-[10px] text-gray-400 font-mono">({{ $r['employee_code'] }})</span>
                                <button
                                    type="button"
                                    wire:click="restoreRecord('{{ $r['employee_code'] }}')"
                                    class="text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 ml-1 cursor-pointer"
                                    title="Restore back to list"
                                >
                                    <x-heroicon-m-arrow-uturn-left class="w-3.5 h-3.5" />
                                </button>
                            </span>
                        @endif
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
