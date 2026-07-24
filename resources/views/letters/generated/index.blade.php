@extends('letters.layout')

@section('title', 'Generated Letters History')

@section('content')
<div class="h-full overflow-y-auto px-8 py-10">
    <div class="max-w-7xl mx-auto space-y-8">
        
        <!-- Header dashboard summary card -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 bg-white dark:bg-zinc-900 p-8 rounded-2xl border border-slate-200/80 dark:border-zinc-800/80 shadow-sm transition-all duration-300">
            <div class="space-y-1.5">
                <div class="flex items-center gap-3">
                    <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-zinc-50">Generated History</h1>
                    <span class="text-xs bg-amber-500/10 text-amber-600 dark:text-amber-400 font-bold px-2.5 py-1 rounded-full border border-amber-500/20">
                        {{ $letters->total() }} Record{{ $letters->total() === 1 ? '' : 's' }}
                    </span>
                </div>
                <p class="text-sm font-medium text-slate-500 dark:text-zinc-400">Access and reprint past letters generated for employees anytime.</p>
            </div>
            
            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('letters.generate') }}" class="px-5 py-3 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white rounded-xl text-sm font-bold tracking-wide transition-all duration-200 shadow-md shadow-amber-500/10 flex items-center gap-2 active:scale-95 cursor-pointer">
                    <i class="fa-solid fa-wand-magic-sparkles text-base"></i> Generate New Letters
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="p-4 bg-emerald-50 dark:bg-emerald-950/20 border border-emerald-200 dark:border-emerald-800/50 rounded-xl text-emerald-800 dark:text-emerald-400 text-sm font-semibold flex items-center gap-3 shadow-xs">
                <i class="fa-solid fa-circle-check text-emerald-500 text-lg"></i>
                {{ session('success') }}
            </div>
        @endif

        <!-- Filter and Search Bar -->
        <div class="bg-white dark:bg-zinc-900 p-4 rounded-2xl border border-slate-200 dark:border-zinc-800 shadow-sm flex flex-col md:flex-row gap-4 justify-between items-center">
            <form method="GET" action="{{ route('letters.history') }}" class="w-full flex flex-col md:flex-row gap-3 items-center">
                
                <!-- Search Input -->
                <div class="relative flex-1 w-full">
                    <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input type="text" name="search" value="{{ request('search') }}" 
                           placeholder="Search by employee name, code, or template title..." 
                           class="w-full pl-9 pr-4 py-2 border border-slate-200 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-950 text-slate-800 dark:text-zinc-200 text-xs rounded-xl focus:outline-none focus:border-amber-500">
                </div>

                <!-- Template Filter -->
                <div class="w-full md:w-64">
                    <select name="template_id" onchange="this.form.submit()" 
                            class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-950 text-slate-800 dark:text-zinc-200 text-xs rounded-xl focus:outline-none focus:border-amber-500">
                        <option value="">— All Templates —</option>
                        @foreach($templates as $t)
                            <option value="{{ $t->id }}" {{ request('template_id') == $t->id ? 'selected' : '' }}>
                                {{ $t->title }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Filter / Reset Buttons -->
                <div class="flex items-center gap-2 shrink-0">
                    <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 dark:bg-zinc-800 dark:hover:bg-zinc-700 text-white text-xs font-bold rounded-xl shadow-xs transition-all cursor-pointer">
                        Filter
                    </button>
                    @if(request('search') || request('template_id'))
                        <a href="{{ route('letters.history') }}" class="px-3 py-2 text-xs font-bold text-slate-500 hover:text-slate-800 dark:text-zinc-400 dark:hover:text-zinc-200 transition-colors">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Grid of Generated Letters -->
        @if($letters->isEmpty())
            <div class="flex flex-col items-center justify-center p-20 text-center bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-3xl shadow-sm space-y-4">
                <div class="w-20 h-20 rounded-full bg-slate-100 dark:bg-zinc-800 flex items-center justify-center text-3xl text-slate-400 shadow-inner">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </div>
                <div class="space-y-1">
                    <h3 class="text-xl font-bold text-slate-850 dark:text-zinc-200">No Generated Letters Found</h3>
                    <p class="text-sm text-slate-500 dark:text-zinc-400 max-w-sm">No historical letter records match your search query or filters.</p>
                </div>
                <a href="{{ route('letters.generate') }}" class="px-5 py-3 bg-gradient-to-r from-amber-500 to-amber-600 text-white rounded-xl text-sm font-bold shadow-md shadow-amber-500/10">
                    Generate Letters Now
                </a>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($letters as $letter)
                    <div class="bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800/60 rounded-2xl p-6 shadow-sm hover:shadow-md hover:border-amber-500/40 transition-all duration-300 flex flex-col justify-between gap-6 group">
                        
                        <!-- Top Header -->
                        <div class="space-y-3">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <h3 class="font-bold text-slate-900 dark:text-zinc-50 group-hover:text-amber-500 transition-colors text-base line-clamp-1">
                                        {{ $letter->template_title }}
                                    </h3>
                                    <div class="flex items-center gap-1.5 mt-1 text-xs text-amber-600 dark:text-amber-400 font-bold">
                                        <i class="fa-solid fa-user-check text-xs"></i>
                                        <span>{{ $letter->employee_name }}</span>
                                        @if($letter->employee_code)
                                            <span class="text-[10px] bg-slate-100 dark:bg-zinc-800 px-1.5 py-0.5 rounded font-mono text-slate-500 dark:text-zinc-400">
                                                {{ $letter->employee_code }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <span class="text-[10px] text-slate-400 font-semibold whitespace-nowrap bg-slate-100 dark:bg-zinc-800 px-2 py-1 rounded-md">
                                    {{ $letter->created_at->format('M d, Y H:i') }}
                                </span>
                            </div>

                            <!-- Custom Variables summary badges if any -->
                            @if(!empty($letter->custom_values) && count($letter->custom_values) > 0)
                                <div class="space-y-1.5 pt-2 border-t border-slate-100 dark:border-zinc-800/60">
                                    <span class="block text-[10px] uppercase font-bold tracking-wider text-slate-400">Values Used:</span>
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($letter->custom_values as $k => $v)
                                            @if($v)
                                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 dark:bg-zinc-800 text-slate-600 dark:text-zinc-300 font-mono">
                                                    <span class="text-slate-400">{{ $k }}:</span>
                                                    <span class="font-bold text-slate-800 dark:text-zinc-200">{{ \Illuminate\Support\Str::limit($v, 20) }}</span>
                                                </span>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Bottom actions -->
                        <div class="flex items-center justify-between border-t border-slate-100 dark:border-zinc-800/80 pt-4 mt-auto">
                            <!-- View / Print button -->
                            <a href="{{ route('letters.history.show', $letter->id) }}" 
                               class="px-3 py-1.5 bg-amber-500/10 hover:bg-amber-500/20 text-amber-700 dark:text-amber-400 border border-amber-500/20 rounded-xl text-xs font-bold flex items-center gap-1.5 transition-colors cursor-pointer">
                                <i class="fa-solid fa-eye text-xs"></i> View & Print
                            </a>

                            <!-- Edit and Delete actions -->
                            <div class="flex items-center gap-1">
                                <a href="{{ route('letters.history.edit', $letter->id) }}" class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-zinc-200 hover:bg-slate-100 dark:hover:bg-zinc-800 rounded-xl transition-all" title="Edit Letter">
                                    <i class="fa-solid fa-pen text-sm"></i>
                                </a>

                                <form action="{{ route('letters.history.destroy', $letter->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this generated letter record?');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 text-rose-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/20 rounded-xl transition-all cursor-pointer" title="Delete record">
                                        <i class="fa-solid fa-trash text-sm"></i>
                                    </button>
                                </form>
                            </div>
                        </div>

                    </div>
                @endforeach
            </div>

            <!-- Pagination Links -->
            <div class="pt-4">
                {{ $letters->links() }}
            </div>
        @endif

    </div>
</div>
@endsection
