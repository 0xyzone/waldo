<a href="{{ \App\Filament\Pages\Changelog::getUrl() }}" 
   class="px-4 py-2.5 mx-3 mb-3 rounded-xl bg-slate-50 dark:bg-slate-900/60 hover:bg-orange-50/60 dark:hover:bg-orange-950/30 border border-slate-200/70 dark:border-slate-800/60 hover:border-orange-400/60 dark:hover:border-orange-500/40 flex items-center justify-between text-xs text-slate-600 dark:text-slate-400 transition-all duration-200 group shadow-xs active:scale-[0.98]"
   title="View Changelog & Release Notes">
    <div class="flex items-center gap-2">
        <span class="relative flex h-2 w-2">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-orange-400 opacity-75"></span>
            <span class="relative inline-flex rounded-full h-2 w-2 bg-orange-500"></span>
        </span>
        <span class="font-semibold text-slate-700 dark:text-slate-200 group-hover:text-orange-600 dark:group-hover:text-orange-400 transition-colors">Waldo Portal</span>
    </div>
    <span class="px-2 py-0.5 rounded-lg font-mono text-[11px] font-bold bg-orange-500/10 text-orange-600 dark:text-orange-400 border border-orange-500/20 group-hover:bg-gradient-to-r group-hover:from-orange-500 group-hover:to-red-500 group-hover:text-white group-hover:border-transparent transition-all shadow-xs">
        v{{ \App\Services\ChangelogService::getCurrentVersion() }}
    </span>
</a>
