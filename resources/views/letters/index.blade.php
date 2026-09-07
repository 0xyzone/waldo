@extends('letters.layout')

@section('title', 'Templates')

@section('content')
<div class="h-full overflow-y-auto px-8 py-10">
    <div class="max-w-7xl mx-auto space-y-8">
        
        <!-- Header dashboard summary card -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 bg-white dark:bg-zinc-900 p-8 rounded-2xl border border-slate-200/80 dark:border-zinc-800/80 shadow-sm transition-all duration-300">
            <div class="space-y-1.5">
                <div class="flex items-center gap-3">
                    <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-zinc-50">Document Templates</h1>
                    <span class="text-xs bg-amber-500/10 text-amber-600 dark:text-amber-400 font-bold px-2.5 py-1 rounded-full border border-amber-500/20">
                        {{ $templates->count() }} {{ \Illuminate\Support\Str::plural('Template', $templates->count()) }}
                    </span>
                </div>
                <p class="text-sm font-medium text-slate-500 dark:text-zinc-400">Design letter templates with placeholders and generate bulk letters with dynamic variable replacement.</p>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('letters.generate') }}" class="px-5 py-3 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white rounded-xl text-sm font-bold tracking-wide transition-all duration-200 shadow-md shadow-amber-500/10 flex items-center gap-2 active:scale-95 cursor-pointer">
                    <i class="fa-solid fa-file-invoice text-base"></i> Generate Letters
                </a>
                <a href="{{ route('letters.create') }}" class="px-5 py-3 bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 hover:bg-slate-50 dark:hover:bg-zinc-750 text-slate-800 dark:text-zinc-200 rounded-xl text-sm font-bold tracking-wide transition-all duration-200 shadow-sm flex items-center gap-2 active:scale-95">
                    <i class="fa-solid fa-circle-plus text-base text-amber-500"></i> New Template
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="p-4 bg-emerald-50 dark:bg-emerald-950/20 border border-emerald-200 dark:border-emerald-800/50 rounded-xl text-emerald-800 dark:text-emerald-400 text-sm font-semibold flex items-center gap-3 shadow-xs">
                <i class="fa-solid fa-circle-check text-emerald-500 text-lg"></i>
                {{ session('success') }}
            </div>
        @endif

        <!-- Search and Filter Bar -->
        <div class="bg-white dark:bg-zinc-900 p-4 rounded-2xl border border-slate-200/80 dark:border-zinc-800/80 shadow-sm flex flex-col md:flex-row gap-4 justify-between items-center">
            <form method="GET" action="{{ route('letters.index') }}" class="w-full flex flex-col md:flex-row gap-3 items-center">
                <!-- Search Input -->
                <div class="relative flex-1 w-full">
                    <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Search templates by name or ID..." 
                           class="w-full pl-9 pr-4 py-2 border border-slate-200 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-950 text-slate-800 dark:text-zinc-200 text-xs rounded-xl focus:outline-none focus:border-amber-500 transition-colors">
                </div>

                <!-- Search / Reset Buttons -->
                <div class="flex items-center gap-2 shrink-0">
                    <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-white text-xs font-bold rounded-xl shadow-xs transition-all cursor-pointer">
                        Search
                    </button>
                    @if(request('search'))
                        <a href="{{ route('letters.index') }}" class="px-3 py-2 text-xs font-bold text-slate-500 hover:text-slate-800 dark:text-zinc-400 dark:hover:text-zinc-200 transition-colors">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Card Grid of Templates -->
        @if($templates->isEmpty())
            <div class="flex flex-col items-center justify-center p-20 text-center bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-3xl shadow-sm">
                <div class="w-20 h-20 rounded-full bg-slate-100 dark:bg-zinc-800 flex items-center justify-center text-3xl text-slate-400 mb-6 shadow-inner">
                    <i class="fa-regular fa-file-lines text-slate-400"></i>
                </div>
                @if(request('search'))
                    <h3 class="text-xl font-bold text-slate-850 dark:text-zinc-200">No Matching Templates</h3>
                    <p class="text-sm text-slate-500 dark:text-zinc-400 max-w-sm mt-2 mb-8 leading-relaxed">No templates found matching "{{ request('search') }}". Try searching for another name or keyword.</p>
                    <a href="{{ route('letters.index') }}" class="px-5 py-3 bg-slate-100 dark:bg-zinc-800 hover:bg-slate-200 dark:hover:bg-zinc-700 text-slate-700 dark:text-zinc-200 rounded-xl text-sm font-bold transition-all">Clear Search</a>
                @else
                    <h3 class="text-xl font-bold text-slate-850 dark:text-zinc-200">No Templates Found</h3>
                    <p class="text-sm text-slate-500 dark:text-zinc-400 max-w-sm mt-2 mb-8 leading-relaxed">Start by creating your first document template such as an Appointment letter, Promotion notice, or Certificate.</p>
                    <a href="{{ route('letters.create') }}" class="px-5 py-3 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white rounded-xl text-sm font-bold transition-all duration-200 shadow-md shadow-amber-500/10">Create Template</a>
                @endif
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($templates as $template)
                    <div class="bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800/60 rounded-2xl p-6 shadow-sm hover:shadow-md hover:border-amber-500/40 dark:hover:border-amber-500/40 transition-all duration-300 flex flex-col justify-between gap-6 group">
                        
                        <!-- Top Details -->
                        <div class="space-y-4">
                            <div class="flex items-start justify-between gap-3">
                                <div class="flex items-start gap-2.5 min-w-0">
                                    <span class="text-xs font-mono font-bold text-amber-600 dark:text-amber-400 bg-amber-500/10 dark:bg-amber-500/15 border border-amber-500/20 px-2 py-0.5 rounded-lg shrink-0 mt-0.5" title="Template ID: {{ $template->id }}">
                                        #{{ $template->id }}
                                    </span>
                                    <h3 class="font-bold text-slate-900 dark:text-zinc-50 group-hover:text-amber-500 dark:group-hover:text-amber-500 transition-colors text-lg line-clamp-2" title="{{ $template->title }}">{{ $template->title }}</h3>
                                </div>
                                <span class="text-[11px] text-slate-400 dark:text-zinc-500 font-semibold whitespace-nowrap bg-slate-100 dark:bg-zinc-800 px-2 py-1 rounded-md shrink-0">{{ $template->created_at->format('M d, Y') }}</span>
                            </div>
                            
                            <!-- Margins config preview -->
                            <div class="flex items-center gap-2 text-xs text-slate-500 dark:text-zinc-400 font-medium">
                                <i class="fa-solid fa-crop text-slate-400"></i>
                                Margins: {{ $template->margin_top }} • {{ $template->margin_bottom }} • {{ $template->margin_left }} • {{ $template->margin_right }} (mm)
                                @if($template->different_first_page_margins)
                                    <span class="text-[10px] text-amber-600 dark:text-amber-400 font-semibold">(1st: {{ $template->first_page_margin_top ?? $template->margin_top }}•{{ $template->first_page_margin_bottom ?? $template->margin_bottom }}•{{ $template->first_page_margin_left ?? $template->margin_left }}•{{ $template->first_page_margin_right ?? $template->margin_right }})</span>
                                @endif
                            </div>

                            <!-- Variables count and summary list -->
                            <div class="space-y-2">
                                <span class="block text-[10px] uppercase font-bold tracking-wider text-slate-400">Custom Variables:</span>
                                @if(empty($template->variables))
                                    <span class="text-xs text-slate-400 dark:text-zinc-500 italic block">None required</span>
                                @else
                                    <div class="flex flex-wrap gap-1.5">
                                        @foreach($template->variables as $var)
                                            @php 
                                                $varKey = is_array($var) ? ($var['key'] ?? '') : $var; 
                                                $varType = is_array($var) ? ($var['type'] ?? 'text') : 'text';
                                            @endphp
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 dark:bg-zinc-800 text-slate-600 dark:text-zinc-300 font-mono border border-slate-200/40 dark:border-zinc-700/40" title="Type: {{ $varType }}">
                                                {{ $varKey }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Card Action Buttons -->
                        <div class="flex items-center justify-between border-t border-slate-100 dark:border-zinc-800/80 pt-4 mt-auto">
                            <!-- Generate Button -->
                            <a href="{{ route('letters.generate') }}?template_id={{ $template->id }}" class="text-xs font-bold text-amber-600 dark:text-amber-500 hover:text-amber-700 dark:hover:text-amber-400 flex items-center gap-1.5 transition-colors cursor-pointer">
                                <i class="fa-solid fa-wand-magic-sparkles text-sm"></i>
                                Generate Letters
                            </a>

                            <!-- Edit and Delete buttons -->
                            <div class="flex items-center gap-1.5">
                                <a href="{{ route('letters.edit', $template->id) }}" class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-zinc-200 hover:bg-slate-100 dark:hover:bg-zinc-800 rounded-xl transition-all" title="Edit Template">
                                    <i class="fa-solid fa-pen text-sm"></i>
                                </a>

                                <form action="{{ route('letters.destroy', $template->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this template? This cannot be undone.');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 text-rose-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/20 rounded-xl transition-all cursor-pointer" title="Delete Template">
                                        <i class="fa-solid fa-trash text-sm"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
