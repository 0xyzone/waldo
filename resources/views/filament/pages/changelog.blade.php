<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Hero Summary Banner -->
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-slate-950 via-slate-900 to-[#131b2e] p-6 md:p-8 text-white shadow-xl border border-slate-800">
            <!-- Decorative Orange Glow -->
            <div class="absolute -top-12 -right-12 w-64 h-64 rounded-full bg-orange-500/15 blur-3xl pointer-events-none"></div>

            <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div class="space-y-2">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-orange-500/15 border border-orange-500/30 text-orange-400 text-xs font-semibold uppercase tracking-wider">
                        <span class="h-2 w-2 rounded-full bg-orange-500 animate-pulse"></span>
                        Active Release v{{ $this->getCurrentVersion() }}
                    </div>
                    <h2 class="text-2xl md:text-3xl font-bold tracking-tight text-white">
                        Waldo Operations Portal Evolution
                    </h2>
                    <p class="text-sm text-slate-300 max-w-2xl leading-relaxed">
                        Track feature additions, HRMS integrations, and architectural enhancements across the internal workforce management platform.
                    </p>
                </div>

                <div class="flex items-center gap-3 shrink-0">
                    <div class="px-4 py-3 rounded-xl bg-white/5 border border-white/10 backdrop-blur-xs text-center">
                        <span class="block text-xl font-bold text-white">{{ count($this->getReleases()) }}</span>
                        <span class="text-[11px] text-slate-400 uppercase tracking-wider">Releases</span>
                    </div>
                    <div class="px-4 py-3 rounded-xl bg-white/5 border border-white/10 backdrop-blur-xs text-center">
                        <span class="block text-xl font-bold text-orange-400">SemVer</span>
                        <span class="text-[11px] text-slate-400 uppercase tracking-wider">Standard</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Release Timeline -->
        <div class="relative pl-4 md:pl-8 space-y-8 before:absolute before:left-[15px] md:before:left-[31px] before:top-4 before:bottom-4 before:w-0.5 before:bg-slate-200 dark:before:bg-slate-800">
            @foreach ($this->getReleases() as $release)
                <div class="relative group">
                    <!-- Timeline Dot -->
                    <div class="absolute -left-[23px] md:-left-[39px] top-6 flex h-4 w-4 items-center justify-center rounded-full bg-white dark:bg-slate-900 border-2 {{ $loop->first ? 'border-orange-500 ring-4 ring-orange-500/20' : 'border-slate-400 dark:border-slate-600' }}">
                        @if ($loop->first)
                            <div class="h-1.5 w-1.5 rounded-full bg-orange-500"></div>
                        @endif
                    </div>

                    <!-- Release Card -->
                    <div class="rounded-2xl border border-slate-200/80 dark:border-slate-800/80 bg-white dark:bg-slate-900/90 p-5 md:p-6 shadow-xs hover:shadow-md transition-all duration-200">
                        <!-- Header Row -->
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2.5 pb-4 border-b border-slate-100 dark:border-slate-800/60">
                            <div class="flex flex-wrap items-center gap-2.5">
                                <span class="px-3 py-1 rounded-lg font-mono text-sm font-bold {{ $loop->first ? 'bg-gradient-to-r from-orange-500 to-red-600 text-white shadow-xs' : 'bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-200' }}">
                                    v{{ $release['version'] }}
                                </span>

                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold
                                    @if($release['badge_color'] === 'primary') bg-orange-50 dark:bg-orange-950/60 text-orange-700 dark:text-orange-300 border border-orange-200/60 dark:border-orange-800/40
                                    @elseif($release['badge_color'] === 'warning') bg-orange-50 dark:bg-orange-950/60 text-orange-600 dark:text-orange-400 border border-orange-200/60 dark:border-orange-800/40
                                    @elseif($release['badge_color'] === 'success') bg-emerald-50 dark:bg-emerald-950/60 text-emerald-600 dark:text-emerald-400 border border-emerald-200/60 dark:border-emerald-800/40
                                    @else bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700
                                    @endif">
                                    {{ $release['badge'] }}
                                </span>

                                @if ($loop->first)
                                    <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/40 px-2 py-0.5 rounded-full">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        Active
                                    </span>
                                @endif
                            </div>

                            <div class="flex items-center gap-1.5 text-xs text-slate-500 dark:text-slate-400">
                                <x-filament::icon icon="heroicon-m-calendar" class="h-4 w-4 text-slate-400" />
                                <time datetime="{{ $release['date'] }}">{{ \Carbon\Carbon::parse($release['date'])->format('F d, Y') }}</time>
                            </div>
                        </div>

                        <!-- Title & Highlights -->
                        <div class="mt-4 space-y-2">
                            <h3 class="text-base md:text-lg font-bold text-slate-900 dark:text-white">
                                {{ $release['title'] }}
                            </h3>
                            @if (!empty($release['description']))
                                <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed bg-slate-50 dark:bg-slate-800/40 border-l-4 border-amber-500 px-4 py-2.5 rounded-r-xl">
                                    {{ $release['description'] }}
                                </p>
                            @elseif (!empty($release['highlights']) && is_string($release['highlights']))
                                <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed bg-slate-50 dark:bg-slate-800/40 border-l-4 border-amber-500 px-4 py-2.5 rounded-r-xl">
                                    {{ $release['highlights'] }}
                                </p>
                            @endif

                            @if (!empty($release['highlights']) && is_array($release['highlights']))
                                <ul class="mt-3 space-y-1.5 pl-1">
                                    @foreach ($release['highlights'] as $hl)
                                        <li class="flex items-start gap-2.5 text-xs md:text-sm text-slate-700 dark:text-slate-300">
                                            <span class="h-1.5 w-1.5 rounded-full bg-amber-500 mt-2 shrink-0"></span>
                                            <span>{{ $hl }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>

                        <!-- Detailed Categorized Items -->
                        <div class="mt-5 space-y-4">
                            @if (!empty($release['changes']['added']))
                                <div class="space-y-2">
                                    <span class="inline-flex items-center gap-1.5 text-xs font-bold text-emerald-700 dark:text-emerald-400 uppercase tracking-wider">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                                        Added
                                    </span>
                                    <ul class="space-y-1.5 pl-1">
                                        @foreach ($release['changes']['added'] as $item)
                                            <li class="flex items-start gap-2.5 text-xs md:text-sm text-slate-700 dark:text-slate-300">
                                                <svg class="h-4 w-4 text-emerald-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                                                <span>{!! preg_replace('/`([^`]+)`/', '<code class="px-1.5 py-0.5 rounded-md text-[11px] font-mono bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-200 border border-slate-200/60 dark:border-slate-700/60">$1</code>', e($item)) !!}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            @if (!empty($release['changes']['changed']))
                                <div class="space-y-2">
                                    <span class="inline-flex items-center gap-1.5 text-xs font-bold text-sky-700 dark:text-sky-400 uppercase tracking-wider">
                                        <span class="h-1.5 w-1.5 rounded-full bg-sky-500"></span>
                                        Changed
                                    </span>
                                    <ul class="space-y-1.5 pl-1">
                                        @foreach ($release['changes']['changed'] as $item)
                                            <li class="flex items-start gap-2.5 text-xs md:text-sm text-slate-700 dark:text-slate-300">
                                                <svg class="h-4 w-4 text-sky-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                                <span>{!! preg_replace('/`([^`]+)`/', '<code class="px-1.5 py-0.5 rounded-md text-[11px] font-mono bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-200 border border-slate-200/60 dark:border-slate-700/60">$1</code>', e($item)) !!}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            @if (!empty($release['changes']['fixed']))
                                <div class="space-y-2">
                                    <span class="inline-flex items-center gap-1.5 text-xs font-bold text-orange-700 dark:text-orange-400 uppercase tracking-wider">
                                        <span class="h-1.5 w-1.5 rounded-full bg-orange-500"></span>
                                        Fixed
                                    </span>
                                    <ul class="space-y-1.5 pl-1">
                                        @foreach ($release['changes']['fixed'] as $item)
                                            <li class="flex items-start gap-2.5 text-xs md:text-sm text-slate-700 dark:text-slate-300">
                                                <svg class="h-4 w-4 text-orange-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                                <span>{!! preg_replace('/`([^`]+)`/', '<code class="px-1.5 py-0.5 rounded-md text-[11px] font-mono bg-slate-100 dark:bg-slate-800 text-slate-800 dark:text-slate-200 border border-slate-200/60 dark:border-slate-700/60">$1</code>', e($item)) !!}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-filament-panels::page>
