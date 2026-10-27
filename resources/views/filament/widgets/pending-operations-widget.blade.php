<x-filament-widgets::widget class="fi-wi-clean h-full">
    @php
        $q = $this->queues;
    @endphp

    <div 
        data-widget-card
        class="relative w-full h-full rounded-3xl bg-white dark:bg-[#121826] border border-slate-200/80 dark:border-white/[0.08] p-5 sm:p-7 shadow-[0_4px_24px_rgba(15,23,42,0.04)] dark:shadow-[0_12px_36px_rgba(0,0,0,0.38)] transition-all duration-300 flex flex-col justify-between overflow-hidden"
    >
        <!-- Ambient Background Glow -->
        <div class="pointer-events-none absolute -right-12 -top-12 h-44 w-44 rounded-full bg-gradient-to-br from-amber-500/10 via-orange-500/5 to-transparent blur-3xl"></div>

        <div class="space-y-4 relative">
            <!-- Header Section -->
            <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-white/[0.06]">
                <div class="flex items-center gap-3.5">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-tr from-amber-500/20 via-orange-500/15 to-amber-500/10 border border-amber-500/25 text-amber-600 dark:text-amber-400 shadow-sm shadow-amber-500/10">
                        <x-filament::icon icon="heroicon-s-bell-alert" class="h-5 w-5 text-amber-600 dark:text-amber-400" />
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-base sm:text-lg font-extrabold tracking-tight text-slate-900 dark:text-white">
                                Pending Operations
                            </h2>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold tracking-wide uppercase {{ $q['total'] > 0 ? 'bg-amber-500/15 text-amber-600 dark:text-amber-400 border border-amber-500/30' : 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20' }}">
                                {{ $q['total'] > 0 ? $q['total'].' Action Required' : 'All Clear' }}
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-0.5">
                            Real-time approval queues & active disciplinary actions
                        </p>
                    </div>
                </div>
            </div>

            <!-- Operational Queues List -->
            <div class="space-y-2.5">
                <!-- Pending Adjustments -->
                <a 
                    href="{{ url('/kamkaj/adjustments') }}"
                    class="group flex items-center justify-between p-3.5 rounded-2xl bg-slate-50/80 dark:bg-slate-900/60 hover:bg-amber-500/5 dark:hover:bg-amber-950/20 border border-slate-200/80 dark:border-white/[0.06] hover:border-amber-500/30 transition-all cursor-pointer"
                >
                    <div class="flex items-center gap-3">
                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20">
                            <x-filament::icon icon="heroicon-m-banknotes" class="h-4 w-4" />
                        </div>
                        <div>
                            <div class="text-xs font-bold text-slate-900 dark:text-white group-hover:text-amber-600 dark:group-hover:text-amber-400 transition-colors">
                                Financial Adjustments
                            </div>
                            <div class="text-[11px] font-medium text-slate-400">
                                Pending finance review & signoff
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-xl text-xs font-bold {{ $q['adjustments'] > 0 ? 'bg-amber-500/15 text-amber-600 dark:text-amber-400 border border-amber-500/25' : 'bg-slate-100 dark:bg-slate-800 text-slate-400' }}">
                            {{ $q['adjustments'] }} pending
                        </span>
                        <x-filament::icon icon="heroicon-m-chevron-right" class="h-4 w-4 text-slate-400 group-hover:text-amber-500 group-hover:translate-x-0.5 transition-all" />
                    </div>
                </a>

                <!-- Active Suspensions -->
                <a 
                    href="{{ url('/kamkaj/employee-suspensions') }}"
                    class="group flex items-center justify-between p-3.5 rounded-2xl bg-slate-50/80 dark:bg-slate-900/60 hover:bg-rose-500/5 dark:hover:bg-rose-950/20 border border-slate-200/80 dark:border-white/[0.06] hover:border-rose-500/30 transition-all cursor-pointer"
                >
                    <div class="flex items-center gap-3">
                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20">
                            <x-filament::icon icon="heroicon-m-shield-exclamation" class="h-4 w-4" />
                        </div>
                        <div>
                            <div class="text-xs font-bold text-slate-900 dark:text-white group-hover:text-rose-600 dark:group-hover:text-rose-400 transition-colors">
                                Active Suspensions
                            </div>
                            <div class="text-[11px] font-medium text-slate-400">
                                Ongoing disciplinary suspensions
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-xl text-xs font-bold {{ $q['suspensions'] > 0 ? 'bg-rose-500/15 text-rose-600 dark:text-rose-400 border border-rose-500/25' : 'bg-slate-100 dark:bg-slate-800 text-slate-400' }}">
                            {{ $q['suspensions'] }} active
                        </span>
                        <x-filament::icon icon="heroicon-m-chevron-right" class="h-4 w-4 text-slate-400 group-hover:text-rose-500 group-hover:translate-x-0.5 transition-all" />
                    </div>
                </a>

                <!-- Salary Increment Requests -->
                <a 
                    href="{{ url('/kamkaj/salary-increment-requests') }}"
                    class="group flex items-center justify-between p-3.5 rounded-2xl bg-slate-50/80 dark:bg-slate-900/60 hover:bg-indigo-500/5 dark:hover:bg-indigo-950/20 border border-slate-200/80 dark:border-white/[0.06] hover:border-indigo-500/30 transition-all cursor-pointer"
                >
                    <div class="flex items-center gap-3">
                        <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-500/20">
                            <x-filament::icon icon="heroicon-m-currency-dollar" class="h-4 w-4" />
                        </div>
                        <div>
                            <div class="text-xs font-bold text-slate-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">
                                Salary Increment Requests
                            </div>
                            <div class="text-[11px] font-medium text-slate-400">
                                Pending management approval
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-xl text-xs font-bold {{ $q['increments'] > 0 ? 'bg-indigo-500/15 text-indigo-600 dark:text-indigo-400 border border-indigo-500/25' : 'bg-slate-100 dark:bg-slate-800 text-slate-400' }}">
                            {{ $q['increments'] }} pending
                        </span>
                        <x-filament::icon icon="heroicon-m-chevron-right" class="h-4 w-4 text-slate-400 group-hover:text-indigo-500 group-hover:translate-x-0.5 transition-all" />
                    </div>
                </a>
            </div>
        </div>

        <!-- Footer Notification -->
        <div class="mt-4 pt-3 border-t border-slate-100 dark:border-white/[0.06] flex items-center justify-between text-xs text-slate-400">
            <span class="flex items-center gap-1.5">
                <span class="h-1.5 w-1.5 rounded-full {{ $q['total'] > 0 ? 'bg-amber-500 animate-pulse' : 'bg-emerald-500' }}"></span>
                <span>{{ $q['total'] > 0 ? 'Urgent attention required on pending approvals' : 'Operational workflows are currently synchronized' }}</span>
            </span>
        </div>
    </div>
</x-filament-widgets::widget>
