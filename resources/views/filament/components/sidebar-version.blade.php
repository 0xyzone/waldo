<a href="{{ \App\Filament\Pages\Changelog::getUrl() }}" 
   class="px-4 py-2.5 mx-3 mb-3 rounded-xl bg-slate-50 dark:bg-slate-900/60 hover:bg-amber-50/50 dark:hover:bg-amber-950/30 border border-slate-200/60 dark:border-slate-800/60 hover:border-amber-300 dark:hover:border-amber-800/60 flex items-center justify-between text-xs text-slate-600 dark:text-slate-400 transition-all duration-150 group"
   title="View Changelog & Release Notes">
    <div class="flex items-center gap-2">
        <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
        <span class="font-medium text-slate-700 dark:text-slate-300 group-hover:text-amber-600 dark:group-hover:text-amber-400 transition-colors">Waldo Portal</span>
    </div>
    <span class="px-2 py-0.5 rounded-md font-mono text-[11px] font-semibold bg-amber-50 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300 border border-amber-200/40 dark:border-amber-800/40 group-hover:bg-amber-600 group-hover:text-white transition-colors">
        v{{ \App\Services\ChangelogService::getCurrentVersion() }}
    </span>
</a>
