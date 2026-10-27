<x-filament-widgets::widget class="fi-wi-clean h-full">
    @php
        $m = $this->metrics;
    @endphp

    <div 
        data-widget-card
        class="relative w-full h-full rounded-3xl bg-white dark:bg-[#121826] border border-slate-200/80 dark:border-white/[0.08] p-5 sm:p-7 shadow-[0_4px_24px_rgba(15,23,42,0.04)] dark:shadow-[0_12px_36px_rgba(0,0,0,0.38)] transition-all duration-300 flex flex-col justify-between overflow-hidden"
    >
        <!-- Ambient Background Glow -->
        <div class="pointer-events-none absolute -right-12 -top-12 h-44 w-44 rounded-full bg-gradient-to-br from-emerald-500/10 via-amber-500/5 to-transparent blur-3xl"></div>

        <div class="space-y-4 relative">
            <!-- Header Section -->
            <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-white/[0.06]">
                <div class="flex items-center gap-3.5">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-tr from-emerald-500/20 via-amber-500/15 to-emerald-500/10 border border-emerald-500/25 text-emerald-600 dark:text-emerald-400 shadow-sm shadow-emerald-500/10">
                        <x-filament::icon icon="heroicon-s-banknotes" class="h-5 w-5 text-emerald-600 dark:text-emerald-400" />
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-base sm:text-lg font-extrabold tracking-tight text-slate-900 dark:text-white">
                                Finance Operations
                            </h2>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold tracking-wide uppercase bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                                Finance Category
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-0.5">
                            Payroll adjustments, leaver holds, terminations & transfers
                        </p>
                    </div>
                </div>

                <a 
                    href="{{ url('/kamkaj/adjustments') }}"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 bg-slate-100/90 dark:bg-slate-800/80 hover:bg-emerald-50 dark:hover:bg-emerald-950/30 hover:text-emerald-600 dark:hover:text-emerald-400 border border-slate-200/80 dark:border-white/[0.08] transition-all cursor-pointer shadow-sm active:scale-95"
                >
                    <x-filament::icon icon="heroicon-m-arrow-top-right-on-square" class="h-3.5 w-3.5 text-emerald-500" />
                    <span>Adjustments</span>
                </a>
            </div>

            <!-- Key Financial Metric Cards -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                <!-- Pending Adjustments -->
                <div class="p-3.5 rounded-2xl bg-amber-500/5 border border-amber-500/15 flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400">Pending Adj</span>
                        <span class="h-2 w-2 rounded-full bg-amber-500 {{ $m['adjustments_pending'] > 0 ? 'animate-pulse' : '' }}"></span>
                    </div>
                    <div class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white mt-1.5">
                        {{ $m['adjustments_pending'] }}
                    </div>
                    <span class="text-[10px] font-medium text-slate-400 mt-0.5">{{ $m['adjustments_approved'] }} Approved</span>
                </div>

                <!-- Salary Holds -->
                <div class="p-3.5 rounded-2xl bg-rose-500/5 border border-rose-500/15 flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-rose-600 dark:text-rose-400">Salary Holds</span>
                        <x-filament::icon icon="heroicon-m-pause" class="h-3.5 w-3.5 text-rose-500" />
                    </div>
                    <div class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white mt-1.5">
                        {{ $m['salary_holds'] }}
                    </div>
                    <span class="text-[10px] font-medium text-slate-400 mt-0.5">{{ $m['tips_holds'] }} Tips Holds</span>
                </div>

                <!-- Terminations -->
                <div class="p-3.5 rounded-2xl bg-slate-50/80 dark:bg-slate-900/60 border border-slate-200/70 dark:border-white/[0.06] flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Leavers</span>
                        <x-filament::icon icon="heroicon-m-user-minus" class="h-3.5 w-3.5 text-slate-400" />
                    </div>
                    <div class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white mt-1.5">
                        {{ $m['leavers_this_month'] }}
                    </div>
                    <span class="text-[10px] font-medium text-slate-400 mt-0.5">{{ $m['total_terminations'] }} Terminated</span>
                </div>

                <!-- Pending HRMS Sync -->
                <div class="p-3.5 rounded-2xl bg-indigo-500/5 border border-indigo-500/15 flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-indigo-600 dark:text-indigo-400">HRMS Sync</span>
                        <x-filament::icon icon="heroicon-m-arrow-path" class="h-3.5 w-3.5 text-indigo-500" />
                    </div>
                    <div class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white mt-1.5">
                        {{ $m['pending_hrms_sync'] }}
                    </div>
                    <span class="text-[10px] font-medium text-slate-400 mt-0.5">Pending Sync</span>
                </div>
            </div>

            <!-- Resource Quick Jump Links -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 pt-1">
                <a href="{{ url('/kamkaj/adjustments') }}" class="flex items-center gap-2 p-2 rounded-xl bg-slate-50/70 dark:bg-slate-900/40 hover:bg-emerald-50 dark:hover:bg-emerald-950/20 border border-slate-200/50 dark:border-white/[0.04] transition-colors text-xs font-semibold text-slate-700 dark:text-slate-300">
                    <x-filament::icon icon="heroicon-m-scale" class="h-4 w-4 text-emerald-500" />
                    <span>Adjustments</span>
                </a>
                <a href="{{ url('/kamkaj/leavers') }}" class="flex items-center gap-2 p-2 rounded-xl bg-slate-50/70 dark:bg-slate-900/40 hover:bg-rose-50 dark:hover:bg-rose-950/20 border border-slate-200/50 dark:border-white/[0.04] transition-colors text-xs font-semibold text-slate-700 dark:text-slate-300">
                    <x-filament::icon icon="heroicon-m-user-minus" class="h-4 w-4 text-rose-500" />
                    <span>Leavers</span>
                </a>
                <a href="{{ url('/kamkaj/terminated-employees') }}" class="flex items-center gap-2 p-2 rounded-xl bg-slate-50/70 dark:bg-slate-900/40 hover:bg-slate-100 dark:hover:bg-slate-800/40 border border-slate-200/50 dark:border-white/[0.04] transition-colors text-xs font-semibold text-slate-700 dark:text-slate-300">
                    <x-filament::icon icon="heroicon-m-no-symbol" class="h-4 w-4 text-slate-400" />
                    <span>Terminated</span>
                </a>
                <a href="{{ url('/kamkaj/employee-transfers') }}" class="flex items-center gap-2 p-2 rounded-xl bg-slate-50/70 dark:bg-slate-900/40 hover:bg-indigo-50 dark:hover:bg-indigo-950/20 border border-slate-200/50 dark:border-white/[0.04] transition-colors text-xs font-semibold text-slate-700 dark:text-slate-300">
                    <x-filament::icon icon="heroicon-m-arrows-right-left" class="h-4 w-4 text-indigo-500" />
                    <span>Transfers</span>
                </a>
            </div>
        </div>
    </div>
</x-filament-widgets::widget>
