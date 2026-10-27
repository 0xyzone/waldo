<x-filament-widgets::widget class="fi-wi-clean h-full">
    @php
        $m = $this->metrics;
        $reqs = $this->activeRequests;
    @endphp

    <div 
        data-widget-card
        class="relative w-full h-full rounded-3xl bg-white dark:bg-[#121826] border border-slate-200/80 dark:border-white/[0.08] p-5 sm:p-7 shadow-[0_4px_24px_rgba(15,23,42,0.04)] dark:shadow-[0_12px_36px_rgba(0,0,0,0.38)] transition-all duration-300 flex flex-col justify-between overflow-hidden"
    >
        <!-- Ambient Background Glow -->
        <div class="pointer-events-none absolute -right-12 -top-12 h-44 w-44 rounded-full bg-gradient-to-br from-cyan-500/10 via-blue-500/5 to-transparent blur-3xl"></div>

        <div class="space-y-4 relative">
            <!-- Header Section -->
            <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-white/[0.06]">
                <div class="flex items-center gap-3.5">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-tr from-cyan-500/20 via-blue-500/15 to-cyan-500/10 border border-cyan-500/25 text-cyan-600 dark:text-cyan-400 shadow-sm shadow-cyan-500/10">
                        <x-filament::icon icon="heroicon-s-identification" class="h-5 w-5 text-cyan-600 dark:text-cyan-400" />
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-base sm:text-lg font-extrabold tracking-tight text-slate-900 dark:text-white">
                                IT & Systems Hub
                            </h2>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold tracking-wide uppercase bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 border border-cyan-500/20">
                                IT Category
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-0.5">
                            ID card printing, biometric enrollments & hardware tracking
                        </p>
                    </div>
                </div>

                <a 
                    href="{{ url('/kamkaj/id-card-requests') }}"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 bg-slate-100/90 dark:bg-slate-800/80 hover:bg-cyan-50 dark:hover:bg-cyan-950/30 hover:text-cyan-600 dark:hover:text-cyan-400 border border-slate-200/80 dark:border-white/[0.08] transition-all cursor-pointer shadow-sm active:scale-95"
                >
                    <x-filament::icon icon="heroicon-m-arrow-top-right-on-square" class="h-3.5 w-3.5 text-cyan-500" />
                    <span>ID Cards</span>
                </a>
            </div>

            <!-- IT Metric Badges -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                <!-- Pending ID Requests -->
                <div class="p-3.5 rounded-2xl bg-amber-500/5 border border-amber-500/15 flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400">Pending</span>
                        <span class="h-2 w-2 rounded-full bg-amber-500 {{ $m['id_pending'] > 0 ? 'animate-pulse' : '' }}"></span>
                    </div>
                    <div class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white mt-1.5">
                        {{ $m['id_pending'] }}
                    </div>
                    <span class="text-[10px] font-medium text-slate-400 mt-0.5">Need Design</span>
                </div>

                <!-- Designed -->
                <div class="p-3.5 rounded-2xl bg-cyan-500/5 border border-cyan-500/15 flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-cyan-600 dark:text-cyan-400">Designed</span>
                        <x-filament::icon icon="heroicon-m-paint-brush" class="h-3.5 w-3.5 text-cyan-500" />
                    </div>
                    <div class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white mt-1.5">
                        {{ $m['id_designed'] }}
                    </div>
                    <span class="text-[10px] font-medium text-slate-400 mt-0.5">Ready for Batch</span>
                </div>

                <!-- In Print Queue -->
                <div class="p-3.5 rounded-2xl bg-purple-500/5 border border-purple-500/15 flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-purple-600 dark:text-purple-400">Printing</span>
                        <x-filament::icon icon="heroicon-m-printer" class="h-3.5 w-3.5 text-purple-500" />
                    </div>
                    <div class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white mt-1.5">
                        {{ $m['id_sent_for_print'] }}
                    </div>
                    <span class="text-[10px] font-medium text-slate-400 mt-0.5">{{ $m['print_reports_count'] }} Batches</span>
                </div>

                <!-- Biometric Enrolled -->
                <div class="p-3.5 rounded-2xl bg-emerald-500/5 border border-emerald-500/15 flex flex-col justify-between">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400">Biometrics</span>
                        <x-filament::icon icon="heroicon-m-finger-print" class="h-3.5 w-3.5 text-emerald-500" />
                    </div>
                    <div class="text-xl sm:text-2xl font-black text-slate-900 dark:text-white mt-1.5">
                        {{ $m['biometric_enrolled'] }}
                    </div>
                    <span class="text-[10px] font-medium text-slate-400 mt-0.5">Of {{ $m['biometric_total'] }} Total</span>
                </div>
            </div>

            <!-- Active Print & Design Queue Preview -->
            <div class="space-y-2">
                <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Active ID Card Queue</div>
                @if ($reqs->isEmpty())
                    <div class="text-xs text-slate-400 italic py-3 text-center">
                        All ID cards are processed and completed.
                    </div>
                @else
                    <div class="space-y-1.5">
                        @foreach ($reqs as $r)
                            <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50/80 dark:bg-slate-900/50 border border-slate-200/60 dark:border-white/[0.05]">
                                <div class="flex items-center gap-2.5">
                                    <div class="h-8 w-8 rounded-lg bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 flex items-center justify-center font-bold text-xs">
                                        {{ substr($r->employee_name ?? 'I', 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="text-xs font-bold text-slate-900 dark:text-white">
                                            {{ $r->employee_name }}
                                        </div>
                                        <div class="text-[10px] text-slate-400">
                                            {{ $r->employee_code ?: 'Custom' }} • {{ $r->employee_designation ?: 'Staff' }}
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider
                                        {{ $r->status === 'pending' ? 'bg-amber-500/10 text-amber-600 dark:text-amber-400 border border-amber-500/20' : '' }}
                                        {{ $r->status === 'designed' ? 'bg-cyan-500/10 text-cyan-600 dark:text-cyan-400 border border-cyan-500/20' : '' }}
                                        {{ $r->status === 'sent for print' ? 'bg-purple-500/10 text-purple-600 dark:text-purple-400 border border-purple-500/20' : '' }}
                                    ">
                                        {{ $r->status }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- Resource Links -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 pt-1 border-t border-slate-100 dark:border-white/[0.06]">
                <a href="{{ url('/kamkaj/id-card-requests') }}" class="flex items-center gap-2 p-2 rounded-xl bg-slate-50/70 dark:bg-slate-900/40 hover:bg-cyan-50 dark:hover:bg-cyan-950/20 border border-slate-200/50 dark:border-white/[0.04] transition-colors text-xs font-semibold text-slate-700 dark:text-slate-300">
                    <x-filament::icon icon="heroicon-m-identification" class="h-4 w-4 text-cyan-500" />
                    <span>ID Cards</span>
                </a>
                <a href="{{ url('/kamkaj/biometric-allotments') }}" class="flex items-center gap-2 p-2 rounded-xl bg-slate-50/70 dark:bg-slate-900/40 hover:bg-emerald-50 dark:hover:bg-emerald-950/20 border border-slate-200/50 dark:border-white/[0.04] transition-colors text-xs font-semibold text-slate-700 dark:text-slate-300">
                    <x-filament::icon icon="heroicon-m-finger-print" class="h-4 w-4 text-emerald-500" />
                    <span>Biometrics</span>
                </a>
                <a href="{{ url('/kamkaj/id-card-print-reports') }}" class="flex items-center gap-2 p-2 rounded-xl bg-slate-50/70 dark:bg-slate-900/40 hover:bg-purple-50 dark:hover:bg-purple-950/20 border border-slate-200/50 dark:border-white/[0.04] transition-colors text-xs font-semibold text-slate-700 dark:text-slate-300">
                    <x-filament::icon icon="heroicon-m-printer" class="h-4 w-4 text-purple-500" />
                    <span>Batches</span>
                </a>
                <a href="{{ url('/kamkaj/map-users') }}" class="flex items-center gap-2 p-2 rounded-xl bg-slate-50/70 dark:bg-slate-900/40 hover:bg-blue-50 dark:hover:bg-blue-950/20 border border-slate-200/50 dark:border-white/[0.04] transition-colors text-xs font-semibold text-slate-700 dark:text-slate-300">
                    <x-filament::icon icon="heroicon-m-user-group" class="h-4 w-4 text-blue-500" />
                    <span>User Map</span>
                </a>
            </div>
        </div>
    </div>
</x-filament-widgets::widget>
