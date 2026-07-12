@extends('letters.layout')

@section('title', 'Font Management')

@section('styles')
<style>
.font-card {
    transition: transform 0.15s, box-shadow 0.15s;
}
.font-card:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 20px rgba(0,0,0,.08);
}
</style>
@endsection

@section('content')
<div class="h-full overflow-y-auto bg-slate-50 dark:bg-zinc-950">

    <div class="max-w-4xl mx-auto p-8 space-y-8">

        <!-- Header -->
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 dark:text-zinc-50 tracking-tight">Font Library</h1>
                <p class="text-sm text-slate-500 dark:text-zinc-400 mt-1">Upload custom fonts (.ttf, .otf, .woff, .woff2) to use in your letter templates.</p>
            </div>
            <a href="{{ route('letters.index') }}"
               class="flex items-center gap-2 px-4 py-2 rounded-xl border border-slate-200 dark:border-zinc-700 text-slate-600 dark:text-zinc-300 hover:bg-slate-100 dark:hover:bg-zinc-800 text-sm font-medium transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Templates
            </a>
        </div>

        @if(session('success'))
        <div class="flex items-center gap-3 p-4 bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800/60 rounded-xl text-emerald-700 dark:text-emerald-400 text-sm">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ session('success') }}
        </div>
        @endif

        @if($errors->any())
        <div class="p-4 bg-rose-50 dark:bg-rose-950/20 border border-rose-200 dark:border-rose-800/60 rounded-xl text-rose-600 dark:text-rose-400 text-sm space-y-1">
            @foreach($errors->all() as $e)<p>• {{ $e }}</p>@endforeach
        </div>
        @endif

        <!-- Upload Card -->
        <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-slate-200 dark:border-zinc-800 shadow-sm p-6">
            <h2 class="text-sm font-bold text-slate-700 dark:text-zinc-200 uppercase tracking-wider mb-5">Upload New Font</h2>
            <form action="{{ route('letters.fonts.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-slate-500 dark:text-zinc-400 uppercase tracking-wider">Family Name *</label>
                        <input type="text" name="family_name" value="{{ old('family_name') }}" required
                               placeholder="e.g. MyCompanyFont"
                               class="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-950 text-sm text-slate-900 dark:text-zinc-100 placeholder-slate-400 dark:placeholder-zinc-500 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 outline-none transition-all">
                        <p class="text-[10px] text-slate-400 dark:text-zinc-500">This is what will appear in the font dropdown in the editor.</p>
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-slate-500 dark:text-zinc-400 uppercase tracking-wider">Font File *</label>
                        <input type="file" name="font_file" required accept=".ttf,.otf,.woff,.woff2"
                               class="w-full text-sm text-slate-700 dark:text-zinc-300 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-amber-50 dark:file:bg-amber-950/30 file:text-amber-700 dark:file:text-amber-400 hover:file:bg-amber-100 dark:hover:file:bg-amber-900/40 file:cursor-pointer cursor-pointer transition-all">
                        <p class="text-[10px] text-slate-400 dark:text-zinc-500">Accepted: .ttf, .otf, .woff, .woff2 (max 5 MB)</p>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-slate-500 dark:text-zinc-400 uppercase tracking-wider">Style</label>
                        <select name="style" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-950 text-sm text-slate-900 dark:text-zinc-100 focus:border-amber-500 outline-none">
                            <option value="normal" selected>Normal</option>
                            <option value="italic">Italic</option>
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <label class="block text-xs font-semibold text-slate-500 dark:text-zinc-400 uppercase tracking-wider">Weight</label>
                        <select name="weight" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-950 text-sm text-slate-900 dark:text-zinc-100 focus:border-amber-500 outline-none">
                            <option value="100">100 — Thin</option>
                            <option value="200">200 — Extra Light</option>
                            <option value="300">300 — Light</option>
                            <option value="400" selected>400 — Regular</option>
                            <option value="500">500 — Medium</option>
                            <option value="600">600 — Semi Bold</option>
                            <option value="700">700 — Bold</option>
                            <option value="800">800 — Extra Bold</option>
                            <option value="900">900 — Black</option>
                        </select>
                    </div>
                </div>
                <div class="flex justify-end">
                    <button type="submit"
                            class="px-6 py-2.5 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white text-sm font-semibold rounded-xl shadow shadow-amber-500/20 transition-all active:scale-[0.98] cursor-pointer flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                        Upload Font
                    </button>
                </div>
            </form>
        </div>

        <!-- Font Library List -->
        <div class="space-y-3">
            <h2 class="text-sm font-bold text-slate-700 dark:text-zinc-300 uppercase tracking-wider">Installed Fonts ({{ $fonts->count() }})</h2>

            @if($fonts->isEmpty())
            <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-dashed border-slate-300 dark:border-zinc-700 p-12 text-center">
                <div class="w-14 h-14 mx-auto mb-4 bg-slate-100 dark:bg-zinc-800 rounded-2xl flex items-center justify-center">
                    <svg class="w-7 h-7 text-slate-400 dark:text-zinc-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M7.5 7.5h-.75A2.25 2.25 0 004.5 9.75v7.5a2.25 2.25 0 002.25 2.25h7.5a2.25 2.25 0 002.25-2.25v-7.5a2.25 2.25 0 00-2.25-2.25h-.75m0-3l-3-3m0 0l-3 3m3-3v11.25m6-2.25h.75a2.25 2.25 0 012.25 2.25v7.5a2.25 2.25 0 01-2.25 2.25h-7.5a2.25 2.25 0 01-2.25-2.25v-.75"/>
                    </svg>
                </div>
                <p class="text-slate-500 dark:text-zinc-400 font-medium">No custom fonts uploaded yet.</p>
                <p class="text-xs text-slate-400 dark:text-zinc-500 mt-1">Upload your first font above to use it in letter templates.</p>
            </div>
            @else
            <div class="grid gap-3">
                @foreach($fonts as $font)
                <div class="font-card bg-white dark:bg-zinc-900 rounded-xl border border-slate-200 dark:border-zinc-800 p-4 flex items-center gap-4 shadow-sm">
                    <!-- Font preview swatch -->
                    <div class="w-14 h-14 bg-gradient-to-br from-amber-50 to-amber-100 dark:from-amber-950/30 dark:to-amber-900/20 rounded-xl flex items-center justify-center border border-amber-200/50 dark:border-amber-800/30 shrink-0">
                        <span class="text-2xl font-bold text-amber-600 dark:text-amber-400 leading-none select-none">Aa</span>
                    </div>
                    <!-- Info -->
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <p class="font-semibold text-slate-900 dark:text-zinc-100 text-sm truncate">{{ $font->family_name }}</p>
                            <span class="px-2 py-0.5 text-[10px] font-semibold rounded-full bg-slate-100 dark:bg-zinc-800 text-slate-500 dark:text-zinc-400 uppercase tracking-wide">{{ $font->style }}</span>
                            <span class="px-2 py-0.5 text-[10px] font-semibold rounded-full bg-amber-50 dark:bg-amber-950/30 text-amber-700 dark:text-amber-400 uppercase tracking-wide">W{{ $font->weight }}</span>
                        </div>
                        <p class="text-xs text-slate-400 dark:text-zinc-500 mt-0.5 truncate">{{ $font->original_name }}</p>
                        <p class="text-[10px] text-slate-300 dark:text-zinc-600 mt-0.5">Uploaded {{ $font->created_at->diffForHumans() }}</p>
                    </div>
                    <!-- Actions -->
                    <form action="{{ route('letters.fonts.destroy', $font) }}" method="POST"
                          onsubmit="return confirm('Delete this font? Templates using it will fall back to their default font.')"
                          class="shrink-0">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="p-2 rounded-lg text-slate-400 hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/30 transition-all cursor-pointer"
                                title="Delete font">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </form>
                </div>
                @endforeach
            </div>
            @endif
        </div>

    </div>
</div>
@endsection
