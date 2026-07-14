<x-filament-widgets::widget>
    <div class="relative overflow-hidden rounded-2xl bg-white dark:bg-gray-900 shadow-[0_8px_30px_rgb(0,0,0,0.04)] dark:shadow-[0_15px_40px_rgba(0,0,0,0.4)] border border-gray-200/50 dark:border-white/10 transition-all duration-500 hover:border-amber-500/30 dark:hover:border-amber-500/20 hover:shadow-[0_20px_50px_rgba(249,115,22,0.08)] group w-full">
        
        <!-- Glowing backdrop elements -->
        <div class="absolute -right-24 -top-24 h-64 w-64 rounded-full bg-linear-to-br from-amber-500/10 to-orange-500/10 opacity-30 dark:opacity-20 blur-3xl pointer-events-none transition-all duration-700 group-hover:scale-110"></div>
        <div class="absolute -left-20 -bottom-20 h-64 w-64 rounded-full bg-linear-to-tr from-indigo-500/5 to-purple-500/5 opacity-20 dark:opacity-10 blur-3xl pointer-events-none"></div>

        <div class="p-6 sm:p-8 space-y-6 relative z-10">
            <!-- Header Section -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6 pb-6 border-b border-gray-100 dark:border-white/5">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-linear-to-tr from-amber-500 to-orange-600 text-white shadow-[0_8px_20px_rgba(249,115,22,0.2)] transition-transform duration-500 group-hover:scale-105">
                        <x-filament::icon icon="heroicon-s-briefcase" class="h-6 w-6 text-white" />
                    </div>
                    <div>
                        <h2 class="text-lg font-bold tracking-tight text-gray-900 dark:text-white">
                            Department Stats
                        </h2>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 font-normal leading-relaxed">
                            Overview of active employee counts by department and designation.
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ url('/reports/departments/view') }}" target="_blank"
                       class="inline-flex items-center justify-center gap-2 font-bold tracking-wide text-amber-500 hover:text-white border border-amber-500/30 hover:border-amber-500 bg-transparent hover:bg-linear-to-r hover:from-orange-500 hover:to-amber-500 transition-all duration-300 transform hover:-translate-y-0.5 active:translate-y-0 focus:outline-none focus:ring-2 focus:ring-amber-500/50 rounded-xl px-4 py-2 text-xs whitespace-nowrap">
                        <x-filament::icon icon="heroicon-m-arrow-top-right-on-square" class="h-3.5 w-3.5" />
                        Interactive View
                    </a>
                    <a href="{{ url('/reports/departments') }}" target="_blank"
                       class="inline-flex items-center justify-center gap-2 font-bold tracking-wide text-white bg-linear-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 shadow-[0_4px_15px_rgba(249,115,22,0.2)] hover:shadow-[0_6px_20px_rgba(249,115,22,0.3)] transition-all duration-300 transform hover:-translate-y-0.5 active:translate-y-0 focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:ring-offset-2 dark:focus:ring-offset-gray-900 rounded-xl px-4 py-2 text-xs whitespace-nowrap">
                        <x-filament::icon icon="heroicon-m-printer" class="h-3.5 w-3.5" />
                        Print Report
                    </a>
                </div>
            </div>

            <!-- Stats Display Section: Grid-based full width, no scrolling -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                @foreach($this->stats as $dept)
                    <div class="p-4 rounded-xl border border-gray-100 dark:border-white/5 bg-gray-50/30 dark:bg-gray-800/30 transition-all hover:bg-gray-50/60 dark:hover:bg-gray-800/60 flex flex-col justify-between min-h-22.5">
                        <div>
                            <!-- Department Info -->
                            <div class="flex items-center justify-between gap-3 mb-2.5">
                                <h3 class="text-xs font-bold text-gray-900 dark:text-white flex items-center gap-1.5">
                                    <span class="w-1 h-3.5 rounded-full bg-amber-500"></span>
                                    <span class="truncate max-w-35 sm:max-w-none">{{ $dept['name'] }}</span>
                                </h3>
                                <div class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-500/10 dark:text-amber-400 border border-amber-200/50 dark:border-amber-500/20 shadow-sm shrink-0">
                                    {{ $dept['count'] }}
                                </div>
                            </div>

                            <!-- Designations Breakdown (Active only for dashboard) -->
                            @php
                                $activeDesignations = collect($dept['designations'])->filter(fn($d) => $d['count'] > 0);
                            @endphp

                            @if($activeDesignations->isNotEmpty())
                                <div class="flex flex-wrap gap-1.5">
                                    @foreach($activeDesignations as $desig)
                                        <div class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md bg-white dark:bg-gray-950/60 border border-gray-100 dark:border-white/5 text-[10px] font-medium text-gray-600 dark:text-gray-400">
                                            <span>{{ $desig['name'] }}</span>
                                            <span class="px-1 py-0.1 rounded bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-200 font-bold text-[9px]">
                                                {{ $desig['count'] }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-[10px] text-gray-400 dark:text-gray-500 italic pl-2.5">
                                    No active designations
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</x-filament-widgets::widget>
