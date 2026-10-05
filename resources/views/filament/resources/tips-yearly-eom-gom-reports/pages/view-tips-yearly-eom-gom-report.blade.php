<x-filament-panels::page>
    @php
        $months = [
            1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April',
            5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August',
            9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'
        ];
        $entries = $this->monthEntries;
        $e1 = $entries->firstWhere('entry_number', 1);
        $e2 = $entries->firstWhere('entry_number', 2);
        $e3 = $entries->firstWhere('entry_number', 3);
    @endphp

    {{-- Month Navigation Pills --}}
    <div class="flex flex-wrap items-center gap-1.5 p-2 bg-white dark:bg-gray-900 rounded-2xl border border-gray-200 dark:border-gray-800 shadow-sm mb-4">
        @for($num = 1; $num <= 12; $num++)
            @php
                $period = $record->getPeriodForMonth($num);
                $isActive = $this->activeMonth === $num;
                $monthEntries = $record->entries->where('month_number', $num);
                $assignedCount = 0;
                foreach($monthEntries as $mentry) {
                    if ($mentry->eom_employee_code_1) $assignedCount++;
                    if ($mentry->eom_employee_code_2) $assignedCount++;
                    if ($mentry->gom_employee_code_1) $assignedCount++;
                }
            @endphp
            <button type="button"
                    wire:click="setActiveMonth({{ $num }})"
                    class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold transition cursor-pointer {{ $isActive ? 'bg-primary-600 text-white shadow-md shadow-primary-500/20' : 'text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800' }}">
                <span>{{ $period['release_month_short'] }}</span>
                <span class="text-[10px] {{ $isActive ? 'text-amber-200' : 'text-gray-400' }} font-medium">({{ $period['evaluated_month_short'] }} '{{ $period['evaluated_year_short'] }})</span>
                @if($assignedCount > 0)
                    <span class="w-1.5 h-1.5 rounded-full {{ $isActive ? 'bg-amber-300' : 'bg-emerald-500' }}"></span>
                @endif
            </button>
        @endfor
    </div>

    {{-- Active Month Banner --}}
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-gray-900 via-slate-900 to-indigo-950 p-6 text-white shadow-lg border border-slate-800 mb-6">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wider text-amber-400">
                    <span>{{ $this->activePeriod['release_label'] }} • Year {{ $record->year }}</span>
                    <span>•</span>
                    <span>{{ $record->title ?: 'Annual Recognition Cycle' }}</span>
                </div>
                <h2 class="text-2xl font-black tracking-tight text-white mt-1">
                    {{ $this->activePeriod['evaluated_label'] }}
                </h2>
                <p class="text-xs text-slate-300 mt-1">
                    Evaluated Period: <strong class="text-white">{{ $this->activePeriod['evaluated_label'] }}</strong> • Released in <span class="text-amber-300 font-semibold">{{ $this->activePeriod['release_month'] }} {{ $record->year }}</span> (3 Department Entries • 5 Total Honorees)
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2.5">
                <a href="{{ route('tips.eom-gom.print-monthly', ['report' => $record->id, 'month' => $this->activeMonth]) }}"
                   target="_blank"
                   class="inline-flex items-center gap-2 px-3.5 py-2 text-xs font-bold rounded-xl bg-white/10 hover:bg-white/20 text-white border border-white/20 backdrop-blur-md transition">
                    <x-heroicon-m-printer class="w-4 h-4 text-sky-400" />
                    <span>Print Monthly Sheet</span>
                </a>

                <a href="{{ route('tips.eom-gom.hrms-card', ['report' => $record->id, 'month' => $this->activeMonth]) }}"
                   target="_blank"
                   class="inline-flex items-center gap-2 px-3.5 py-2 text-xs font-bold rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-slate-950 shadow-md shadow-amber-500/20 font-black transition">
                    <x-heroicon-m-sparkles class="w-4 h-4" />
                    <span>HRMS Wish Card</span>
                </a>
            </div>
        </div>

        {{-- Background decorative elements --}}
        <div class="absolute -right-10 -bottom-10 w-44 h-44 bg-amber-500/10 rounded-full blur-3xl pointer-events-none"></div>
    </div>

    {{-- 3 Department Entries Showcase --}}
    <div class="space-y-6">

        {{-- ENTRY 1: Gaming / Slot Department --}}
        <div class="rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-sm overflow-hidden">
            <div class="px-5 py-3.5 bg-gray-50/80 dark:bg-gray-800/50 border-b border-gray-200 dark:border-gray-800 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="flex items-center justify-center w-7 h-7 rounded-lg bg-amber-500/10 text-amber-600 dark:text-amber-400 font-bold text-xs">
                        1
                    </span>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-sm font-bold text-gray-900 dark:text-white">Gaming / Slot Department</h3>
                            <span class="text-[10px] px-2 py-0.5 rounded-full bg-amber-100 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 font-bold uppercase tracking-wider">
                                Mandatory Entry (2 EOM + 1 GOM)
                            </span>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Floor operations department with high staff count</p>
                    </div>
                </div>
            </div>

            <div class="p-5 grid grid-cols-1 md:grid-cols-3 gap-4">
                {{-- Slot 1: EOM 1 --}}
                <div class="p-4 rounded-xl border {{ $e1?->eomEmployee1 ? 'border-amber-200 dark:border-amber-900/50 bg-amber-50/20 dark:bg-amber-950/10' : 'border-dashed border-gray-200 dark:border-gray-800 bg-gray-50/40 dark:bg-gray-800/20' }}">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-[11px] font-black uppercase tracking-wider text-amber-600 dark:text-amber-400 flex items-center gap-1.5">
                            🏆 EOM 1
                        </span>
                        <span class="text-[10px] text-gray-400 font-medium">Employee of the Month</span>
                    </div>

                    @if($e1?->eomEmployee1)
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 rounded-xl bg-amber-500 text-white font-bold flex items-center justify-center text-sm shadow-sm">
                                {{ strtoupper(substr($e1->eomEmployee1->name, 0, 2)) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="text-sm font-bold text-gray-900 dark:text-white truncate">{{ $e1->eomEmployee1->name }}</h4>
                                <div class="text-xs text-gray-500 dark:text-gray-400 flex items-center gap-1.5 mt-0.5">
                                    <span class="font-mono font-semibold text-gray-700 dark:text-gray-300">{{ $e1->eomEmployee1->employee_code }}</span>
                                    <span>•</span>
                                    <span class="truncate">{{ $e1->eomEmployee1->designation?->name ?: 'Staff' }}</span>
                                </div>
                            </div>
                        </div>
                        @if($e1->eom_remarks_1)
                            <div class="mt-3 p-2 rounded-lg bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 text-xs text-gray-600 dark:text-gray-300 italic">
                                "{{ $e1->eom_remarks_1 }}"
                            </div>
                        @endif
                    @else
                        <div class="text-center py-4 text-xs text-gray-400">
                            <x-heroicon-o-user-plus class="w-6 h-6 mx-auto mb-1 text-gray-300 dark:text-gray-600" />
                            <span>Unassigned Slot</span>
                        </div>
                    @endif
                </div>

                {{-- Slot 2: EOM 2 --}}
                <div class="p-4 rounded-xl border {{ $e1?->eomEmployee2 ? 'border-amber-200 dark:border-amber-900/50 bg-amber-50/20 dark:bg-amber-950/10' : 'border-dashed border-gray-200 dark:border-gray-800 bg-gray-50/40 dark:bg-gray-800/20' }}">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-[11px] font-black uppercase tracking-wider text-amber-600 dark:text-amber-400 flex items-center gap-1.5">
                            🏆 EOM 2
                        </span>
                        <span class="text-[10px] text-gray-400 font-medium">Employee of the Month</span>
                    </div>

                    @if($e1?->eomEmployee2)
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 rounded-xl bg-amber-500 text-white font-bold flex items-center justify-center text-sm shadow-sm">
                                {{ strtoupper(substr($e1->eomEmployee2->name, 0, 2)) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="text-sm font-bold text-gray-900 dark:text-white truncate">{{ $e1->eomEmployee2->name }}</h4>
                                <div class="text-xs text-gray-500 dark:text-gray-400 flex items-center gap-1.5 mt-0.5">
                                    <span class="font-mono font-semibold text-gray-700 dark:text-gray-300">{{ $e1->eomEmployee2->employee_code }}</span>
                                    <span>•</span>
                                    <span class="truncate">{{ $e1->eomEmployee2->designation?->name ?: 'Staff' }}</span>
                                </div>
                            </div>
                        </div>
                        @if($e1->eom_remarks_2)
                            <div class="mt-3 p-2 rounded-lg bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 text-xs text-gray-600 dark:text-gray-300 italic">
                                "{{ $e1->eom_remarks_2 }}"
                            </div>
                        @endif
                    @else
                        <div class="text-center py-4 text-xs text-gray-400">
                            <x-heroicon-o-user-plus class="w-6 h-6 mx-auto mb-1 text-gray-300 dark:text-gray-600" />
                            <span>Unassigned Slot</span>
                        </div>
                    @endif
                </div>

                {{-- Slot 3: GOM 1 --}}
                <div class="p-4 rounded-xl border {{ $e1?->gomEmployee1 ? 'border-sky-200 dark:border-sky-900/50 bg-sky-50/20 dark:bg-sky-950/10' : 'border-dashed border-gray-200 dark:border-gray-800 bg-gray-50/40 dark:bg-gray-800/20' }}">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-[11px] font-black uppercase tracking-wider text-sky-600 dark:text-sky-400 flex items-center gap-1.5">
                            ✨ GOM 1
                        </span>
                        <span class="text-[10px] text-gray-400 font-medium">Grooming of the Month</span>
                    </div>

                    @if($e1?->gomEmployee1)
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 rounded-xl bg-sky-500 text-white font-bold flex items-center justify-center text-sm shadow-sm">
                                {{ strtoupper(substr($e1->gomEmployee1->name, 0, 2)) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="text-sm font-bold text-gray-900 dark:text-white truncate">{{ $e1->gomEmployee1->name }}</h4>
                                <div class="text-xs text-gray-500 dark:text-gray-400 flex items-center gap-1.5 mt-0.5">
                                    <span class="font-mono font-semibold text-gray-700 dark:text-gray-300">{{ $e1->gomEmployee1->employee_code }}</span>
                                    <span>•</span>
                                    <span class="truncate">{{ $e1->gomEmployee1->designation?->name ?: 'Staff' }}</span>
                                </div>
                            </div>
                        </div>
                        @if($e1->gom_remarks_1)
                            <div class="mt-3 p-2 rounded-lg bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 text-xs text-gray-600 dark:text-gray-300 italic">
                                "{{ $e1->gom_remarks_1 }}"
                            </div>
                        @endif
                    @else
                        <div class="text-center py-4 text-xs text-gray-400">
                            <x-heroicon-o-user-plus class="w-6 h-6 mx-auto mb-1 text-gray-300 dark:text-gray-600" />
                            <span>Unassigned Slot</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ENTRY 2: Random Allowed Department (1 EOM) --}}
        <div class="rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-sm overflow-hidden">
            <div class="px-5 py-3.5 bg-gray-50/80 dark:bg-gray-800/50 border-b border-gray-200 dark:border-gray-800 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="flex items-center justify-center w-7 h-7 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-bold text-xs">
                        2
                    </span>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-sm font-bold text-gray-900 dark:text-white">
                                {{ $e2?->department_name ?: ($e2?->department?->name ?: 'Allowed Department') }}
                            </h3>
                            <span class="text-[10px] px-2 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 font-bold uppercase tracking-wider">
                                Random Selection (1 EOM)
                            </span>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Selected automatically from non-excluded departments</p>
                    </div>
                </div>
            </div>

            <div class="p-5">
                <div class="max-w-md p-4 rounded-xl border {{ $e2?->eomEmployee1 ? 'border-amber-200 dark:border-amber-900/50 bg-amber-50/20 dark:bg-amber-950/10' : 'border-dashed border-gray-200 dark:border-gray-800 bg-gray-50/40 dark:bg-gray-800/20' }}">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-[11px] font-black uppercase tracking-wider text-amber-600 dark:text-amber-400 flex items-center gap-1.5">
                            🏆 EOM (Employee of the Month)
                        </span>
                        <span class="text-[10px] text-gray-400 font-medium">{{ $e2?->department_name }}</span>
                    </div>

                    @if($e2?->eomEmployee1)
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 rounded-xl bg-emerald-600 text-white font-bold flex items-center justify-center text-sm shadow-sm">
                                {{ strtoupper(substr($e2->eomEmployee1->name, 0, 2)) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="text-sm font-bold text-gray-900 dark:text-white truncate">{{ $e2->eomEmployee1->name }}</h4>
                                <div class="text-xs text-gray-500 dark:text-gray-400 flex items-center gap-1.5 mt-0.5">
                                    <span class="font-mono font-semibold text-gray-700 dark:text-gray-300">{{ $e2->eomEmployee1->employee_code }}</span>
                                    <span>•</span>
                                    <span class="truncate">{{ $e2->eomEmployee1->designation?->name ?: 'Staff' }}</span>
                                </div>
                            </div>
                        </div>
                        @if($e2->eom_remarks_1)
                            <div class="mt-3 p-2 rounded-lg bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 text-xs text-gray-600 dark:text-gray-300 italic">
                                "{{ $e2->eom_remarks_1 }}"
                            </div>
                        @endif
                    @else
                        <div class="text-center py-4 text-xs text-gray-400">
                            <x-heroicon-o-user-plus class="w-6 h-6 mx-auto mb-1 text-gray-300 dark:text-gray-600" />
                            <span>Unassigned Slot</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ENTRY 3: Random Allowed Department (1 GOM) --}}
        <div class="rounded-2xl bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 shadow-sm overflow-hidden">
            <div class="px-5 py-3.5 bg-gray-50/80 dark:bg-gray-800/50 border-b border-gray-200 dark:border-gray-800 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="flex items-center justify-center w-7 h-7 rounded-lg bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 font-bold text-xs">
                        3
                    </span>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="text-sm font-bold text-gray-900 dark:text-white">
                                {{ $e3?->department_name ?: ($e3?->department?->name ?: 'Allowed Department') }}
                            </h3>
                            <span class="text-[10px] px-2 py-0.5 rounded-full bg-indigo-100 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-400 font-bold uppercase tracking-wider">
                                Random Selection (1 GOM)
                            </span>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Selected automatically from non-excluded departments (distinct from Entry 2)</p>
                    </div>
                </div>
            </div>

            <div class="p-5">
                <div class="max-w-md p-4 rounded-xl border {{ $e3?->gomEmployee1 ? 'border-sky-200 dark:border-sky-900/50 bg-sky-50/20 dark:bg-sky-950/10' : 'border-dashed border-gray-200 dark:border-gray-800 bg-gray-50/40 dark:bg-gray-800/20' }}">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-[11px] font-black uppercase tracking-wider text-sky-600 dark:text-sky-400 flex items-center gap-1.5">
                            ✨ GOM (Grooming of the Month)
                        </span>
                        <span class="text-[10px] text-gray-400 font-medium">{{ $e3?->department_name }}</span>
                    </div>

                    @if($e3?->gomEmployee1)
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white font-bold flex items-center justify-center text-sm shadow-sm">
                                {{ strtoupper(substr($e3->gomEmployee1->name, 0, 2)) }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="text-sm font-bold text-gray-900 dark:text-white truncate">{{ $e3->gomEmployee1->name }}</h4>
                                <div class="text-xs text-gray-500 dark:text-gray-400 flex items-center gap-1.5 mt-0.5">
                                    <span class="font-mono font-semibold text-gray-700 dark:text-gray-300">{{ $e3->gomEmployee1->employee_code }}</span>
                                    <span>•</span>
                                    <span class="truncate">{{ $e3->gomEmployee1->designation?->name ?: 'Staff' }}</span>
                                </div>
                            </div>
                        </div>
                        @if($e3->gom_remarks_1)
                            <div class="mt-3 p-2 rounded-lg bg-white dark:bg-gray-800 border border-gray-100 dark:border-gray-700 text-xs text-gray-600 dark:text-gray-300 italic">
                                "{{ $e3->gom_remarks_1 }}"
                            </div>
                        @endif
                    @else
                        <div class="text-center py-4 text-xs text-gray-400">
                            <x-heroicon-o-user-plus class="w-6 h-6 mx-auto mb-1 text-gray-300 dark:text-gray-600" />
                            <span>Unassigned Slot</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

    </div>
</x-filament-panels::page>
