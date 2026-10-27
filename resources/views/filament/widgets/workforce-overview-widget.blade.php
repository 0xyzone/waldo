<x-filament-widgets::widget class="fi-wi-clean h-full">
    @php
        $m = $this->metrics;
    @endphp

    <div 
        data-widget-card
        class="relative w-full h-full rounded-3xl bg-white dark:bg-[#121826] border border-slate-200/80 dark:border-white/[0.08] p-5 sm:p-7 shadow-[0_4px_24px_rgba(15,23,42,0.04)] dark:shadow-[0_12px_36px_rgba(0,0,0,0.38)] transition-all duration-300 flex flex-col justify-between overflow-hidden"
    >
        <!-- Ambient Background Glow -->
        <div class="pointer-events-none absolute -right-12 -top-12 h-44 w-44 rounded-full bg-gradient-to-br from-emerald-500/10 via-teal-500/5 to-transparent blur-3xl"></div>

        <div class="space-y-5 relative">
            <!-- Header Section -->
            <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-white/[0.06]">
                <div class="flex items-center gap-3.5">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-tr from-emerald-500/20 via-teal-500/15 to-emerald-500/10 border border-emerald-500/25 text-emerald-600 dark:text-emerald-400 shadow-sm shadow-emerald-500/10">
                        <x-filament::icon icon="heroicon-s-user-group" class="h-5 w-5 text-emerald-600 dark:text-emerald-400" />
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-base sm:text-lg font-extrabold tracking-tight text-slate-900 dark:text-white">
                                Workforce Overview
                            </h2>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold tracking-wide uppercase bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20">
                                {{ number_format($m['total']) }} Staff
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-0.5">
                            Real-time headcount status & staff distribution
                        </p>
                    </div>
                </div>

                <a 
                    href="{{ url('/kamkaj/employees') }}"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 bg-slate-100/90 dark:bg-slate-800/80 hover:bg-emerald-50 dark:hover:bg-emerald-950/30 hover:text-emerald-600 dark:hover:text-emerald-400 border border-slate-200/80 dark:border-white/[0.08] transition-all cursor-pointer shadow-sm active:scale-95"
                >
                    <x-filament::icon icon="heroicon-m-arrow-top-right-on-square" class="h-3.5 w-3.5 text-emerald-500" />
                    <span>View All</span>
                </a>
            </div>

            <!-- Key Status Grid -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <!-- Active Employees -->
                <div class="p-3.5 rounded-2xl bg-emerald-500/5 border border-emerald-500/15 flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Active</span>
                        <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    </div>
                    <div class="text-2xl font-black text-slate-900 dark:text-white mt-2">
                        {{ number_format($m['active']) }}
                    </div>
                    <span class="text-[10px] font-medium text-slate-400 mt-0.5">On Duty</span>
                </div>

                <!-- Suspended -->
                <div class="p-3.5 rounded-2xl bg-rose-500/5 border border-rose-500/15 flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-rose-600 dark:text-rose-400">Suspended</span>
                        <x-filament::icon icon="heroicon-m-no-symbol" class="h-3.5 w-3.5 text-rose-500" />
                    </div>
                    <div class="text-2xl font-black text-slate-900 dark:text-white mt-2">
                        {{ number_format($m['suspended']) }}
                    </div>
                    <span class="text-[10px] font-medium text-slate-400 mt-0.5">Disciplinary</span>
                </div>

                <!-- Resigning -->
                <div class="p-3.5 rounded-2xl bg-amber-500/5 border border-amber-500/15 flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400">Resigning</span>
                        <x-filament::icon icon="heroicon-m-arrow-right-start-on-rectangle" class="h-3.5 w-3.5 text-amber-500" />
                    </div>
                    <div class="text-2xl font-black text-slate-900 dark:text-white mt-2">
                        {{ number_format($m['resigning']) }}
                    </div>
                    <span class="text-[10px] font-medium text-slate-400 mt-0.5">This Month</span>
                </div>

                <!-- Tips on Hold -->
                <div class="p-3.5 rounded-2xl bg-violet-500/5 border border-violet-500/15 flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-violet-600 dark:text-violet-400">Tips Hold</span>
                        <x-filament::icon icon="heroicon-m-pause-circle" class="h-3.5 w-3.5 text-violet-500" />
                    </div>
                    <div class="text-2xl font-black text-slate-900 dark:text-white mt-2">
                        {{ number_format($m['tips_hold']) }}
                    </div>
                    <span class="text-[10px] font-medium text-slate-400 mt-0.5">Financial Hold</span>
                </div>
            </div>
        </div>

        <!-- Gender Diversity Breakdown -->
        <div class="mt-5 p-4 rounded-2xl bg-slate-50/80 dark:bg-slate-900/60 border border-slate-200/80 dark:border-white/[0.06] space-y-2.5">
            <div class="flex items-center justify-between text-xs font-bold text-slate-700 dark:text-slate-300">
                <div class="flex items-center gap-2">
                    <span class="h-2 w-2 rounded-full bg-blue-500"></span>
                    <span>Male: {{ number_format($m['male']) }} ({{ $m['male_percent'] }}%)</span>
                </div>
                <div class="flex items-center gap-2">
                    <span class="h-2 w-2 rounded-full bg-pink-500"></span>
                    <span>Female: {{ number_format($m['female']) }} ({{ $m['female_percent'] }}%)</span>
                </div>
            </div>

            <!-- Distribution Bar -->
            <div class="h-2.5 w-full rounded-full bg-slate-200 dark:bg-slate-800 overflow-hidden flex">
                <div class="h-full bg-blue-500 transition-all duration-500" style="width: {{ $m['male_percent'] }}%"></div>
                <div class="h-full bg-pink-500 transition-all duration-500" style="width: {{ $m['female_percent'] }}%"></div>
            </div>
        </div>
    </div>
</x-filament-widgets::widget>
