<x-filament-widgets::widget class="fi-wi-clean h-full">
    @php
        $s = $this->stats;
        $leavers = $this->recentLeavers;
    @endphp

    <div 
        data-widget-card
        class="relative w-full h-full rounded-3xl bg-white dark:bg-[#121826] border border-slate-200/80 dark:border-white/[0.08] p-5 sm:p-7 shadow-[0_4px_24px_rgba(15,23,42,0.04)] dark:shadow-[0_12px_36px_rgba(0,0,0,0.38)] transition-all duration-300 flex flex-col justify-between overflow-hidden"
    >
        <!-- Ambient Background Glow -->
        <div class="pointer-events-none absolute -right-12 -top-12 h-44 w-44 rounded-full bg-gradient-to-br from-rose-500/10 via-amber-500/5 to-transparent blur-3xl"></div>

        <div class="space-y-4 relative">
            <!-- Header Section -->
            <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-white/[0.06]">
                <div class="flex items-center gap-3.5">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-tr from-rose-500/20 via-orange-500/15 to-rose-500/10 border border-rose-500/25 text-rose-600 dark:text-rose-400 shadow-sm shadow-rose-500/10">
                        <x-filament::icon icon="heroicon-s-user-minus" class="h-5 w-5 text-rose-600 dark:text-rose-400" />
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-base sm:text-lg font-extrabold tracking-tight text-slate-900 dark:text-white">
                                Offboarding & Leavers
                            </h2>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold tracking-wide uppercase bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20">
                                {{ $s['this_month'] }} This Month
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-0.5">
                            Track leaver dates, financial holds, and exit clearances
                        </p>
                    </div>
                </div>

                <a 
                    href="{{ url('/kamkaj/leavers') }}"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 bg-slate-100/90 dark:bg-slate-800/80 hover:bg-rose-50 dark:hover:bg-rose-950/30 hover:text-rose-600 dark:hover:text-rose-400 border border-slate-200/80 dark:border-white/[0.08] transition-all cursor-pointer shadow-sm active:scale-95"
                >
                    <x-filament::icon icon="heroicon-m-arrow-top-right-on-square" class="h-3.5 w-3.5 text-rose-500" />
                    <span>View All</span>
                </a>
            </div>

            <!-- Hold Stats Badges -->
            <div class="grid grid-cols-3 gap-2.5">
                <div class="p-3 rounded-2xl bg-slate-50/80 dark:bg-slate-900/60 border border-slate-200/70 dark:border-white/[0.06] text-center">
                    <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Leavers</div>
                    <div class="text-lg font-black text-slate-900 dark:text-white mt-1">{{ $s['total'] }}</div>
                </div>
                <div class="p-3 rounded-2xl bg-amber-500/5 border border-amber-500/15 text-center">
                    <div class="text-[10px] font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400">Salary Holds</div>
                    <div class="text-lg font-black text-amber-600 dark:text-amber-400 mt-1">{{ $s['salary_holds'] }}</div>
                </div>
                <div class="p-3 rounded-2xl bg-rose-500/5 border border-rose-500/15 text-center">
                    <div class="text-[10px] font-bold uppercase tracking-wider text-rose-600 dark:text-rose-400">Tips Holds</div>
                    <div class="text-lg font-black text-rose-600 dark:text-rose-400 mt-1">{{ $s['tips_holds'] }}</div>
                </div>
            </div>

            <!-- Recent Leavers List -->
            <div class="space-y-2">
                <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Recent Leavers</div>
                @if ($leavers->isEmpty())
                    <div class="text-xs text-slate-400 italic py-4 text-center">
                        No recent leavers recorded.
                    </div>
                @else
                    <div class="space-y-1.5">
                        @foreach ($leavers as $l)
                            <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50/80 dark:bg-slate-900/50 border border-slate-200/60 dark:border-white/[0.05]">
                                <div class="flex items-center gap-2.5">
                                    <div class="h-8 w-8 rounded-lg bg-rose-500/10 text-rose-600 dark:text-rose-400 flex items-center justify-center font-bold text-xs">
                                        {{ substr($l->employee->name ?? 'E', 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="text-xs font-bold text-slate-900 dark:text-white">
                                            {{ $l->employee->name ?? $l->employee_id }}
                                        </div>
                                        <div class="text-[10px] text-slate-400">
                                            {{ $l->employee->department->name ?? 'N/A' }} • {{ $l->employee->designation->name ?? 'N/A' }}
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    @if ($l->hold_salary)
                                        <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20">
                                            Hold Sal
                                        </span>
                                    @endif
                                    @if ($l->hold_tips)
                                        <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20">
                                            Hold Tips
                                        </span>
                                    @endif
                                    <span class="text-[11px] font-mono text-slate-500 dark:text-slate-400">
                                        {{ Carbon\Carbon::parse($l->leaving_date)->format('M d, Y') }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-filament-widgets::widget>
