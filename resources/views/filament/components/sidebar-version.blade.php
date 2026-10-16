<div x-data="{}" class="w-full">
    {{-- Full Version Pill (Shown when sidebar is open or on mobile) --}}
    <a href="{{ \App\Filament\Pages\Changelog::getUrl() }}" 
       x-show="$store.sidebar.isOpen"
       class="px-3.5 py-2 mx-2 mb-2 rounded-xl bg-slate-50 dark:bg-slate-900/60 hover:bg-orange-50/60 dark:hover:bg-orange-950/30 border border-slate-200/70 dark:border-slate-800/60 hover:border-orange-400/60 dark:hover:border-orange-500/40 flex items-center justify-between text-xs text-slate-600 dark:text-slate-400 transition-all duration-200 group shadow-xs active:scale-[0.98]"
       title="View Changelog & Release Notes">
        <div class="flex items-center gap-2 truncate">
            <span class="relative flex h-2 w-2 shrink-0">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-orange-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2 w-2 bg-orange-500"></span>
            </span>
            <span class="font-semibold text-slate-700 dark:text-slate-200 group-hover:text-orange-600 dark:group-hover:text-orange-400 transition-colors truncate">Waldo Portal</span>
        </div>
        <span class="px-1.5 py-0.5 rounded-md font-mono text-[10px] font-bold bg-orange-500/10 text-orange-600 dark:text-orange-400 border border-orange-500/20 group-hover:bg-gradient-to-r group-hover:from-orange-500 group-hover:to-red-500 group-hover:text-white group-hover:border-transparent transition-all shadow-xs shrink-0">
            v{{ \App\Services\ChangelogService::getCurrentVersion() }}
        </span>
    </a>

    {{-- Compact Icon Pill (Shown when sidebar is collapsed on desktop) --}}
    <a href="{{ \App\Filament\Pages\Changelog::getUrl() }}" 
       x-show="! $store.sidebar.isOpen"
       class="flex flex-col items-center justify-center p-1.5 mx-auto mb-2 w-10 h-10 rounded-xl bg-slate-50 dark:bg-slate-900/60 hover:bg-orange-50/60 dark:hover:bg-orange-950/30 border border-slate-200/70 dark:border-slate-800/60 hover:border-orange-400/60 dark:hover:border-orange-500/40 text-xs text-slate-600 dark:text-slate-400 transition-all duration-200 group shadow-xs active:scale-[0.95]"
       title="Waldo Portal v{{ \App\Services\ChangelogService::getCurrentVersion() }} – View Changelog">
        <span class="relative flex h-1.5 w-1.5 mb-0.5">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-orange-400 opacity-75"></span>
            <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-orange-500"></span>
        </span>
        <span class="font-mono text-[8px] font-bold text-orange-600 dark:text-orange-400 group-hover:text-orange-500 leading-none truncate max-w-full">
            v{{ \App\Services\ChangelogService::getCompactVersion() }}
        </span>
    </a>
</div>
