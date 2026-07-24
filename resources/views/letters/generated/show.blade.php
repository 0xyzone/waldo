@extends('letters.layout')

@section('title', $letter->template_title . ' - ' . $letter->employee_name)

@section('styles')
<style>
    .preview-workspace {
        background: #f1f5f9;
        overflow-y: auto;
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 40px 24px;
    }
    .dark .preview-workspace {
        background: #09090b;
    }

    .page-sheet {
        position: relative;
        background: #ffffff;
        width: 210mm;
        min-height: 297mm;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
        border: 1px solid #e2e8f0;
        box-sizing: border-box;
    }
    .dark .page-sheet {
        background: #18181b;
        border-color: #27272a;
        box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.5);
    }

    .a4-page {
        width: 100%;
        min-height: 100%;
        box-sizing: border-box;
        color: #1e293b;
        font-family: 'Times New Roman', Times, serif;
        font-size: 12pt;
        line-height: 1.6;
        word-break: break-word;
        overflow-wrap: break-word;
    }
    .dark .a4-page {
        color: #f1f5f9;
    }

    .a4-page p { margin-top: 0; margin-bottom: 8pt; }
    .a4-page h1 { font-size: 22pt; font-weight: bold; margin-top: 14pt; margin-bottom: 6pt; }
    .a4-page h2 { font-size: 18pt; font-weight: bold; margin-top: 12pt; margin-bottom: 4pt; }
    .a4-page h3 { font-size: 14pt; font-weight: bold; margin-top: 10pt; margin-bottom: 4pt; }
    .a4-page ul { list-style-type: disc; padding-left: 24pt; margin-bottom: 8pt; }
    .a4-page ol { list-style-type: decimal; padding-left: 24pt; margin-bottom: 8pt; }
    .a4-page table { width: 100%; border-collapse: collapse; margin: 12pt 0; }
    .a4-page td, .a4-page th { border: 1px solid #cbd5e1; padding: 8px 12px; }
    .dark .a4-page td, .dark .a4-page th { border-color: #3f3f46; }

    @media print {
        @page {
            size: A4 portrait;
            margin: 0 !important;
        }
        html, body, main, main > div, .preview-workspace {
            display: block !important;
            height: auto !important;
            min-height: auto !important;
            overflow: visible !important;
            padding: 0 !important;
            margin: 0 !important;
            background: white !important;
            border: none !important;
            box-shadow: none !important;
        }
        .no-print, header, aside {
            display: none !important;
        }
        .page-sheet {
            width: 210mm !important;
            min-height: 297mm !important;
            margin: 0 !important;
            padding: 0 !important;
            border: none !important;
            box-shadow: none !important;
            box-sizing: border-box !important;
        }
        .a4-page {
            width: 100% !important;
            box-sizing: border-box !important;
            background: white !important;
        }
    }
</style>
@endsection

@section('content')
<div class="h-full flex flex-col md:flex-row overflow-hidden">
    
    <!-- Left Sidebar: Letter Metadata & Controls -->
    <aside class="no-print w-full md:w-80 bg-white dark:bg-zinc-900 border-r border-slate-200 dark:border-zinc-800 flex flex-col overflow-hidden shrink-0 shadow-sm z-20">
        <div class="p-6 border-b border-slate-200 dark:border-zinc-800 space-y-4 shrink-0">
            <div class="flex items-center justify-between">
                <a href="{{ route('letters.history') }}" class="text-xs font-bold text-slate-500 hover:text-slate-800 dark:text-zinc-400 dark:hover:text-zinc-200 flex items-center gap-1.5 transition-colors">
                    <i class="fa-solid fa-arrow-left"></i> Back to History
                </a>
                
                <div class="flex items-center gap-2">
                    <a href="{{ route('letters.history.edit', $letter->id) }}" 
                       class="px-3 py-2 bg-slate-100 dark:bg-zinc-800 hover:bg-slate-200 dark:hover:bg-zinc-700 text-slate-700 dark:text-zinc-300 text-xs font-bold rounded-xl shadow-xs transition-all active:scale-95 cursor-pointer flex items-center gap-1.5"
                       title="Edit Letter">
                        <i class="fa-solid fa-pen text-amber-500"></i> Edit
                    </a>
                    <button type="button" onclick="window.print()" 
                            class="px-4 py-2 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white text-xs font-bold rounded-xl shadow-md tracking-wide transition-all active:scale-95 cursor-pointer flex items-center gap-1.5">
                        <i class="fa-solid fa-print"></i> Print
                    </button>
                </div>
            </div>

            <div class="space-y-1">
                <h2 class="text-base font-bold text-slate-900 dark:text-zinc-50">{{ $letter->template_title }}</h2>
                <p class="text-xs text-slate-400">Generated on {{ $letter->created_at->format('F d, Y \a\t H:i') }}</p>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto p-6 space-y-6">
            <!-- Target Employee Details -->
            <div class="p-4 bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 rounded-2xl space-y-2">
                <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">Target Employee</span>
                <div class="text-sm font-bold text-slate-900 dark:text-zinc-100">{{ $letter->employee_name }}</div>
                @if($letter->employee_code)
                    <div class="text-xs text-slate-500 dark:text-zinc-400 font-mono">Code: {{ $letter->employee_code }}</div>
                @endif
                @if($letter->employee && $letter->employee->department)
                    <div class="text-xs text-slate-500 dark:text-zinc-400">Department: {{ $letter->employee->department->name ?? $letter->employee->department }}</div>
                @endif
                @if($letter->employee && $letter->employee->designation)
                    <div class="text-xs text-slate-500 dark:text-zinc-400">Designation: {{ $letter->employee->designation->name ?? $letter->employee->designation }}</div>
                @endif
            </div>

            <!-- Custom Values used -->
            @if(!empty($letter->custom_values) && count($letter->custom_values) > 0)
                <div class="space-y-3">
                    <span class="block text-xs font-bold uppercase tracking-wider text-slate-400">Custom Variables</span>
                    <div class="space-y-2">
                        @foreach($letter->custom_values as $key => $val)
                            <div class="p-3 bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 rounded-xl space-y-0.5">
                                <span class="block text-[10px] font-bold text-slate-400 uppercase tracking-wider">{{ str_replace('_', ' ', $key) }}</span>
                                <span class="text-xs font-semibold text-slate-800 dark:text-zinc-200">{{ $val ?: '—' }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Margins used -->
            @php $m = $letter->margins ?? ['top' => 25, 'bottom' => 25, 'left' => 20, 'right' => 20]; @endphp
            <div class="p-4 bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 rounded-2xl space-y-2">
                <span class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">Document Margins</span>
                <div class="grid grid-cols-2 gap-2 text-xs font-semibold text-slate-700 dark:text-zinc-300">
                    <div>Top: {{ $m['top'] ?? 25 }}mm</div>
                    <div>Bottom: {{ $m['bottom'] ?? 25 }}mm</div>
                    <div>Left: {{ $m['left'] ?? 20 }}mm</div>
                    <div>Right: {{ $m['right'] ?? 20 }}mm</div>
                </div>
            </div>

            <!-- Delete Letter -->
            <div class="border-t border-slate-100 dark:border-zinc-800 pt-4">
                <form action="{{ route('letters.history.destroy', $letter->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this letter record from history?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full py-2 bg-rose-50 hover:bg-rose-100 dark:bg-rose-950/20 dark:hover:bg-rose-950/40 text-rose-600 dark:text-rose-400 text-xs font-bold rounded-xl border border-rose-200 dark:border-rose-800 transition-colors cursor-pointer flex items-center justify-center gap-1.5">
                        <i class="fa-solid fa-trash text-xs"></i> Delete Record
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Center Workspace Page View -->
    <div class="preview-workspace flex-1">
        @php
            $mt = ($letter->margins['top'] ?? 25) . 'mm';
            $mb = ($letter->margins['bottom'] ?? 25) . 'mm';
            $ml = ($letter->margins['left'] ?? 20) . 'mm';
            $mr = ($letter->margins['right'] ?? 20) . 'mm';
        @endphp

        <div class="page-sheet">
            <div class="a4-page" style="padding-top: {{ $mt }}; padding-bottom: {{ $mb }}; padding-left: {{ $ml }}; padding-right: {{ $mr }};">
                {!! $letter->content !!}
            </div>
        </div>
    </div>

</div>
@endsection
