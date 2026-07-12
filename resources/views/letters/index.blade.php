@extends('letters.layout')

@section('title', 'Letter Templates')

@section('content')
<div class="h-full overflow-y-auto px-8 py-10">
    <div class="max-w-7xl mx-auto space-y-8">
        
        <!-- Header Dashboard Summary -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white dark:bg-zinc-900 p-6 rounded-2xl border border-slate-200 dark:border-zinc-800 shadow-sm transition-colors duration-200">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 dark:text-zinc-50">Document Templates</h1>
                <p class="text-sm text-slate-500 dark:text-zinc-400 mt-1">Design letter templates with placeholders and generate bulk letters with dynamic variable replacement.</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('letters.generate') }}" class="px-4 py-2.5 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white rounded-xl text-sm font-semibold transition-all duration-200 shadow-md shadow-amber-500/10 flex items-center gap-1.5 active:scale-[0.98]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"></path>
                    </svg>
                    Generate Letters
                </a>
                <a href="{{ route('letters.create') }}" class="px-4 py-2.5 bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 hover:bg-slate-50 dark:hover:bg-zinc-750 text-slate-800 dark:text-zinc-200 rounded-xl text-sm font-semibold transition-all duration-200 shadow-sm flex items-center gap-1.5 active:scale-[0.98]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"></path>
                    </svg>
                    New Template
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="p-4 bg-emerald-50 dark:bg-emerald-950/20 border border-emerald-200 dark:border-emerald-800/50 rounded-xl text-emerald-800 dark:text-emerald-400 text-sm font-medium flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                {{ session('success') }}
            </div>
        @endif

        <!-- Grid of Templates -->
        @if($templates->isEmpty())
            <div class="flex flex-col items-center justify-center p-16 text-center bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-2xl shadow-sm">
                <div class="w-16 h-16 rounded-full bg-slate-100 dark:bg-zinc-800 flex items-center justify-center text-2xl text-slate-400 mb-4">📄</div>
                <h3 class="text-lg font-bold text-slate-800 dark:text-zinc-200">No Templates Found</h3>
                <p class="text-sm text-slate-500 dark:text-zinc-400 max-w-sm mt-1 mb-6">Start by creating your first document template such as an Appointment letter, Promotion notice, or Certificate.</p>
                <a href="{{ route('letters.create') }}" class="px-4 py-2 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white rounded-xl text-sm font-semibold transition-all duration-200 shadow-md shadow-amber-500/10">Create Template</a>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($templates as $template)
                    <div class="bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 rounded-2xl p-5 shadow-sm hover:shadow-md transition-all duration-200 flex flex-col justify-between gap-6 group">
                        
                        <!-- Top details -->
                        <div class="space-y-3">
                            <div class="flex items-start justify-between">
                                <h3 class="font-bold text-slate-900 dark:text-zinc-50 group-hover:text-amber-600 dark:group-hover:text-amber-500 transition-colors text-lg line-clamp-1" title="{{ $template->title }}">{{ $template->title }}</h3>
                                <span class="text-[10px] text-slate-400 font-medium whitespace-nowrap">{{ $template->created_at->format('M d, Y') }}</span>
                            </div>
                            
                            <!-- Margins config preview -->
                            <div class="flex items-center gap-1 text-[11px] text-slate-400 font-medium">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 3.75v16.5h16.5V3.75H3.75zm1.5 1.5h13.5v13.5H5.25V5.25z"></path>
                                </svg>
                                Margins: {{ $template->margin_top }} • {{ $template->margin_bottom }} • {{ $template->margin_left }} • {{ $template->margin_right }} (mm)
                            </div>

                            <!-- Variables count and summary list -->
                            <div>
                                <span class="block text-[10px] uppercase font-bold tracking-wider text-slate-400 mb-1.5">Custom Variables:</span>
                                @if(empty($template->variables))
                                    <span class="text-xs text-slate-400 italic">None required</span>
                                @else
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($template->variables as $var)
                                            @php 
                                                $varKey = is_array($var) ? ($var['key'] ?? '') : $var; 
                                                $varType = is_array($var) ? ($var['type'] ?? 'text') : 'text';
                                            @endphp
                                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 dark:bg-zinc-800 text-slate-600 dark:text-zinc-300 font-mono" title="Type: {{ $varType }}">
                                                {{ $varKey }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Card Action Buttons -->
                        <div class="flex items-center justify-between border-t border-slate-100 dark:border-zinc-800 pt-4 mt-auto">
                            <!-- Generate Button -->
                            <a href="{{ route('letters.generate') }}?template_id={{ $template->id }}" class="text-xs font-semibold text-amber-600 dark:text-amber-500 hover:text-amber-700 dark:hover:text-amber-400 flex items-center gap-1 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.59 14.37a6 6 0 01-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 00-.74-2.106M16 16c.5-1.5 0-3-1.5-4.5M21 3l-6 6m0 0V4m0 5h5M13 12a1 1 0 11-2 0 1 1 0 012 0z"></path>
                                </svg>
                                Generate
                            </a>

                            <!-- Edit and Delete buttons -->
                            <div class="flex items-center gap-1.5">
                                <a href="{{ route('letters.edit', $template->id) }}" class="p-1.5 text-slate-400 hover:text-slate-600 dark:hover:text-zinc-200 hover:bg-slate-100 dark:hover:bg-zinc-800 rounded-lg transition-all" title="Edit Template">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125"></path>
                                    </svg>
                                </a>

                                <form action="{{ route('letters.destroy', $template->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this template? This cannot be undone.');" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 text-rose-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/20 rounded-lg transition-all cursor-pointer" title="Delete Template">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"></path>
                                        </svg>
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
