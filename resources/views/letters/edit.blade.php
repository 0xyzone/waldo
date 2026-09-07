@extends('letters.layout')

@section('title', 'Edit Template')

@section('styles')
<style>
/* Same multi-page styling as create */
#editor-scroll {
    background: #e2e8f0;
    overflow-y: auto;
    overflow-x: hidden;
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 40px 24px 80px;
    gap: 24px;
}
.dark #editor-scroll { background: #0f0f10; }

#pages-container:focus {
    outline: none;
}

.doc-page {
    position: relative;
    width: 210mm;
    background: #ffffff;
    box-shadow: 0 2px 16px rgba(0,0,0,.14), 0 0 0 1px rgba(0,0,0,.06);
    border-radius: 2px;
    box-sizing: border-box;
    padding-top:    var(--mt, 25mm);
    padding-bottom: var(--mb, 25mm);
    padding-left:   var(--ml, 20mm);
    padding-right:  var(--mr, 20mm);
    height: 297mm;
    max-height: 297mm;
    overflow: hidden;
}
.dark .doc-page {
    background: #1c1c1e;
    box-shadow: 0 4px 24px rgba(0,0,0,.5), 0 0 0 1px rgba(255,255,255,.06);
}

.page-break-marker {
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 16px 0;
    border-top: 2px dashed #f59e0b;
    position: relative;
    user-select: none;
    height: 0;
}
.page-break-marker::after {
    content: 'Page Break';
    position: absolute;
    background: #fef3c7;
    color: #b45309;
    font-size: 10px;
    font-weight: bold;
    padding: 2px 8px;
    border-radius: 4px;
    border: 1px solid #fcd34d;
    transform: translateY(-50%);
}
.dark .page-break-marker {
    border-top-color: #d97706;
}
.dark .page-break-marker::after {
    background: rgba(245,158,11,0.18);
    color: #fbbf24;
    border-color: rgba(245,158,11,0.3);
}

.doc-page-content {
    outline: none;
    width: 100%;
    min-height: 10px;
    font-family: 'Times New Roman', Times, serif;
    font-size: 12pt;
    line-height: 1.6;
    color: #1e293b;
    word-break: break-word;
    overflow-wrap: break-word;
    caret-color: #1d4ed8;
}
.dark .doc-page-content { color: #e4e4e7; }
.doc-page-content:focus { outline: none; }
.doc-page-content:empty::before {
    content: attr(data-placeholder);
    color: #94a3b8;
    pointer-events: none;
}

.doc-page-content p  { margin: 0 0 8pt; }
.doc-page-content h1 { font-size: 22pt; font-weight: bold; margin: 14pt 0 6pt; }
.doc-page-content h2 { font-size: 18pt; font-weight: bold; margin: 12pt 0 4pt; }
.doc-page-content h3 { font-size: 14pt; font-weight: bold; margin: 10pt 0 4pt; }
.doc-page-content ul { list-style: disc;    padding-left: 24pt; margin-bottom: 8pt; }
.doc-page-content ol { list-style: decimal; padding-left: 24pt; margin-bottom: 8pt; }
.doc-page-content table {
    width: 100%;
    max-width: 100%;
    border-collapse: collapse;
    margin: 10pt 0;
    table-layout: fixed;
    box-sizing: border-box;
}
.doc-page-content td, .doc-page-content th {
    border: 1px solid #cbd5e1;
    padding: 6px 10px;
    min-width: 30px;
    vertical-align: top;
    position: relative;
    box-sizing: border-box;
    word-break: break-word;
}
.dark .doc-page-content td, .dark .doc-page-content th { border-color: #3f3f46; }

/* ── Table column resize handle ── */
.col-resize-handle {
    position: absolute;
    right: -3px;
    top: 0;
    bottom: 0;
    width: 7px;
    cursor: col-resize;
    user-select: none;
    z-index: 5;
}
.col-resize-handle:hover,
.col-resize-handle.resizing {
    background-color: rgba(245, 158, 11, 0.55);
}
body.is-col-resizing {
    cursor: col-resize !important;
    user-select: none !important;
}

.doc-page-content td.cell-selected, .doc-page-content th.cell-selected {
    outline: 2px solid #2563eb !important;
    outline-offset: -2px;
    background-color: rgba(37, 99, 235, 0.15) !important;
}

/* ── Table floating icon toolbar ── */
#table-ctx-menu {
    position: fixed;
    z-index: 9999;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    box-shadow: 0 10px 30px -5px rgba(0,0,0,.15), 0 0 1px 1px rgba(0,0,0,.05);
    padding: 5px;
    min-width: 270px;
    max-width: 330px;
    display: none;
    font-family: 'Plus Jakarta Sans', sans-serif;
    user-select: none;
}
.dark #table-ctx-menu {
    background: #18181b;
    border-color: #27272a;
    box-shadow: 0 12px 36px -5px rgba(0,0,0,.6);
}

.ctx-tb-row {
    display: flex;
    align-items: center;
    gap: 3px;
}
.ctx-tb-row + .ctx-tb-row {
    margin-top: 4px;
    padding-top: 4px;
    border-top: 1px solid #f1f5f9;
}
.dark .ctx-tb-row + .ctx-tb-row {
    border-top-color: #27272a;
}

.ctx-tb-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 26px;
    height: 26px;
    border-radius: 6px;
    border: 1px solid transparent;
    background: transparent;
    color: #475569;
    font-size: 11px;
    cursor: pointer;
    transition: all 0.12s ease;
    padding: 0;
}
.ctx-tb-btn:hover:not(:disabled) {
    background: #f1f5f9;
    color: #0f172a;
}
.dark .ctx-tb-btn { color: #a1a1aa; }
.dark .ctx-tb-btn:hover:not(:disabled) {
    background: #27272a;
    color: #f4f4f5;
}
.ctx-tb-btn.ctx-danger { color: #ef4444; }
.ctx-tb-btn.ctx-danger:hover:not(:disabled) {
    background: #fef2f2;
    color: #dc2626;
}
.dark .ctx-tb-btn.ctx-danger { color: #f87171; }
.dark .ctx-tb-btn.ctx-danger:hover:not(:disabled) {
    background: rgba(239,68,68,0.15);
    color: #fca5a5;
}

.ctx-tb-divider {
    width: 1px;
    height: 18px;
    background: #e2e8f0;
    margin: 0 1px;
    flex-shrink: 0;
}
.dark .ctx-tb-divider { background: #27272a; }

.ctx-side-btns { display: grid; grid-template-columns: repeat(6, 1fr); gap: 2px; flex: 1; }
.ctx-side-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 3px 1px !important;
    text-align: center !important;
    border: 1px solid #e2e8f0 !important;
    border-radius: 4px !important;
    font-size: 9.5px !important;
    font-weight: 600;
    cursor: pointer;
    background: #ffffff;
    color: #64748b;
    transition: all 0.12s ease;
}
.ctx-side-btn:hover { background: #f8fafc; color: #334155; }
.ctx-side-btn.active {
    background: #fef3c7 !important;
    border-color: #f59e0b !important;
    color: #b45309 !important;
}
.dark .ctx-side-btn { background: #18181b; border-color: #27272a !important; color: #a1a1aa !important; }
.dark .ctx-side-btn:hover { background: #27272a; color: #f4f4f5; }
.dark .ctx-side-btn.active { background: rgba(245,158,11,0.2) !important; border-color: #f59e0b !important; color: #fbbf24 !important; }

.ctx-color-btn {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 2px 6px;
    border-radius: 6px;
    border: 1px solid #e2e8f0;
    background: #ffffff;
    cursor: pointer;
    font-size: 11px;
    color: #475569;
    transition: all 0.12s ease;
    height: 24px;
    flex-shrink: 0;
}
.ctx-color-btn:hover { background: #f1f5f9; }
.dark .ctx-color-btn { background: #18181b; border-color: #27272a; color: #a1a1aa; }
.dark .ctx-color-btn:hover { background: #27272a; }
.ctx-swatch-dot { width: 12px; height: 12px; border-radius: 3px; border: 1px solid rgba(0,0,0,.15); flex-shrink: 0; }

/* ── 2-Second Hover Delay Tooltip ── */
#table-toolbar-tooltip {
    position: fixed;
    z-index: 100000;
    pointer-events: none;
    background: #0f172a;
    color: #f8fafc;
    font-size: 11px;
    font-weight: 600;
    padding: 4px 9px;
    border-radius: 6px;
    box-shadow: 0 4px 14px rgba(0,0,0,0.25);
    display: none;
    white-space: nowrap;
    opacity: 0;
    transition: opacity 0.15s ease-in-out;
    font-family: 'Plus Jakarta Sans', sans-serif;
}
#table-toolbar-tooltip.visible {
    display: block;
    opacity: 1;
}
.dark #table-toolbar-tooltip {
    background: #27272a;
    color: #f4f4f5;
    border: 1px solid #3f3f46;
}

/* ── Floating Swatch Palette Popover ── */
#ctx-swatch-popover {
    position: fixed;
    z-index: 10000;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    box-shadow: 0 10px 25px rgba(0,0,0,.18);
    padding: 8px;
    width: 170px;
    display: none;
    font-family: 'Plus Jakarta Sans', sans-serif;
}
.dark #ctx-swatch-popover { background: #18181b; border-color: #27272a; box-shadow: 0 10px 25px rgba(0,0,0,.6); }
.pop-swatch-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 5px; margin-bottom: 8px; }
.pop-swatch-btn { width: 32px; height: 24px; border-radius: 4px; border: 1px solid rgba(0,0,0,.18); cursor: pointer; padding: 0; transition: transform .1s; }
.pop-swatch-btn:hover { transform: scale(1.1); }
.pop-custom-btn {
    width: 100%;
    padding: 5px 8px;
    border-radius: 6px;
    border: 1px solid #cbd5e1;
    background: #f8fafc;
    font-size: 10px;
    font-weight: 700;
    cursor: pointer;
    color: #334155;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 5px;
}
.dark .pop-custom-btn { background: #27272a; border-color: #3f3f46; color: #e4e4e7; }
.pop-custom-btn:hover { background: #e2e8f0; }
.dark .pop-custom-btn:hover { background: #3f3f46; }

/* ── Table grid picker ── */
.tgp-dropdown { position: absolute; left: 0; top: 100%; margin-top: 4px; background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; box-shadow: 0 8px 32px rgba(0,0,0,.13); z-index: 50; padding: 8px; }
.dark .tgp-dropdown { background: #18181b; border-color: #27272a; }
#tgp-label { font-size: 10px; font-weight: 700; text-align: center; color: #6b7280; padding: 2px 4px 6px; font-family: 'Plus Jakarta Sans', sans-serif; min-width: 100px; }
.table-grid-picker { display: grid; grid-template-columns: repeat(6, 22px); gap: 2px; }
.tgp-cell { width: 22px; height: 22px; border: 1px solid #cbd5e1; border-radius: 3px; cursor: pointer; background: #f8fafc; transition: background .08s, border-color .08s; }
.dark .tgp-cell { background: #27272a; border-color: #3f3f46; }
.tgp-cell.tgp-hover { background: #fef3c7; border-color: #f59e0b; }
.dark .tgp-cell.tgp-hover { background: rgba(245,158,11,.25); border-color: #f59e0b; }

.doc-page::before {
    content: '';
    position: absolute;
    inset: 0;
    border: 1px dashed rgba(245,158,11,0.25);
    margin: var(--mt, 25mm) var(--mr, 20mm) var(--mb, 25mm) var(--ml, 20mm);
    pointer-events: none;
    z-index: 1;
}

.page-number-label {
    position: absolute;
    bottom: 8px;
    right: 12px;
    font-size: 9px;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: #94a3b8;
    font-family: 'Plus Jakarta Sans', sans-serif;
    user-select: none;
    pointer-events: none;
}
.dark .page-number-label { color: #52525b; }

.page-gap-label {
    font-size: 9px;
    font-weight: 700;
    letter-spacing: .1em;
    text-transform: uppercase;
    color: #94a3b8;
    font-family: 'Plus Jakarta Sans', sans-serif;
    padding: 3px 10px;
    background: rgba(0,0,0,0.06);
    border-radius: 4px;
    user-select: none;
}
.dark .page-gap-label { background: rgba(255,255,255,.06); }

.tb-btn.active {
    background: #fef3c7 !important;
    color: #b45309 !important;
}
.dark .tb-btn.active {
    background: rgba(245,158,11,.18) !important;
    color: #fbbf24 !important;
}

#page-info-bar {
    position: sticky;
    bottom: 0;
    background: rgba(255,255,255,.9);
    backdrop-filter: blur(8px);
    border-top: 1px solid #e2e8f0;
    padding: 4px 16px;
    font-size: 11px;
    font-weight: 600;
    color: #64748b;
    text-align: center;
    z-index: 10;
    font-family: 'Plus Jakarta Sans', sans-serif;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}
.dark #page-info-bar {
    background: rgba(24,24,27,.9);
    border-color: #27272a;
    color: #71717a;
}

@media print {
    @page {
        size: A4 portrait;
        margin: 0 !important;
    }
    body {
        margin: 0 !important;
        padding: 0 !important;
        background: white !important;
    }
    #editor-scroll { background: white !important; padding: 0 !important; gap: 0 !important; overflow: visible !important; display: block !important; }
    #pages-container { display: block !important; }
    .doc-page {
        box-shadow: none !important;
        border-radius: 0 !important;
        width: 210mm !important;
        height: 297mm !important;
        margin: 0 !important;
        box-sizing: border-box !important;
        page-break-after: always !important;
        break-after: page !important;
        overflow: hidden !important;
        display: block !important;
    }
    .doc-page:last-child {
        page-break-after: auto !important;
        break-after: auto !important;
    }
    .doc-page::before { display: none !important; }
    .page-number-label, .page-gap-label { display: none !important; }
    .no-print { display: none !important; }
}
</style>
@endsection

@section('content')
<div x-data="editTemplateState()" class="flex-1 flex flex-col overflow-hidden">

    <!-- ── TOOLBAR ── -->
    <div class="no-print bg-white dark:bg-zinc-900 border-b border-slate-200 dark:border-zinc-800 px-3 py-2 flex flex-wrap items-center gap-1 shrink-0 shadow-sm z-20">

        <button type="button" @mousedown.prevent="exec('undo')" class="p-1.5 text-slate-600 dark:text-zinc-300 hover:bg-slate-100 dark:hover:bg-zinc-800 rounded-lg cursor-pointer" title="Undo"><i class="fa-solid fa-rotate-left text-sm"></i></button>
        <button type="button" @mousedown.prevent="exec('redo')" class="p-1.5 text-slate-600 dark:text-zinc-300 hover:bg-slate-100 dark:hover:bg-zinc-800 rounded-lg cursor-pointer" title="Redo"><i class="fa-solid fa-rotate-right text-sm"></i></button>

        <div class="h-5 w-px bg-slate-200 dark:bg-zinc-700 mx-0.5"></div>

        <select @mousedown.stop @change="execBlock($el.value); $el.value='p'"
                class="px-2 py-1 border border-slate-200 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-950 text-sm font-semibold rounded-lg text-slate-700 dark:text-zinc-300 focus:outline-none cursor-pointer">
            <option value="p">Normal</option>
            <option value="h1">Heading 1</option>
            <option value="h2">Heading 2</option>
            <option value="h3">Heading 3</option>
        </select>

        <select id="tb-font" @mousedown.stop @change="execFont($el.value)"
                class="px-2 py-1 border border-slate-200 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-950 text-sm font-semibold rounded-lg text-slate-700 dark:text-zinc-300 focus:outline-none cursor-pointer">
            <option>Times New Roman</option>
            <option>Arial</option>
            <option>Georgia</option>
            <option>Courier New</option>
            <option>Verdana</option>
            <option>Plus Jakarta Sans</option>
        </select>

        <div class="flex items-center border border-slate-200 dark:border-zinc-700 rounded-lg bg-slate-50 dark:bg-zinc-950 overflow-visible relative" x-data="{ dropdownOpen: false }" @click.outside="dropdownOpen = false">
            <button type="button" @mousedown.prevent="adjustSize(-1)" class="px-1.5 py-1 hover:bg-slate-200 dark:hover:bg-zinc-800 text-slate-500 cursor-pointer border-r border-slate-200 dark:border-zinc-700"><i class="fa-solid fa-minus text-xs"></i></button>
            <input type="number" id="tb-size" value="12" min="1" max="100"
                   class="w-10 text-center font-bold text-sm bg-transparent border-none focus:outline-none text-slate-700 dark:text-zinc-300 [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"
                   @focus="saveSelection()"
                   @change="restoreSelection(); execFontSize(+$el.value)">
            <button type="button" @click="dropdownOpen = !dropdownOpen" @mousedown.prevent
                    class="px-1 py-1 hover:bg-slate-200 dark:hover:bg-zinc-800 text-slate-400 cursor-pointer border-r border-slate-200 dark:border-zinc-700"><i class="fa-solid fa-caret-down text-xs"></i></button>
            <button type="button" @mousedown.prevent="adjustSize(1)"  class="px-1.5 py-1 hover:bg-slate-200 dark:hover:bg-zinc-800 text-slate-500 cursor-pointer"><i class="fa-solid fa-plus text-xs"></i></button>
            <div x-show="dropdownOpen" x-transition
                 class="absolute left-1/2 -translate-x-1/2 top-full mt-1 bg-white dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 rounded-xl shadow-xl z-50 max-h-48 overflow-y-auto w-20 py-1" style="display:none">
                @foreach([8, 9, 10, 11, 12, 14, 16, 18, 20, 24, 28, 32, 36, 40, 48, 60, 72, 96] as $sz)
                    <button type="button" @click="restoreSelection(); execFontSize({{ $sz }}); document.getElementById('tb-size').value = {{ $sz }}; dropdownOpen = false;" @mousedown.prevent
                            class="w-full text-center px-3 py-1.5 hover:bg-slate-100 dark:hover:bg-zinc-800 text-xs font-bold text-slate-700 dark:text-zinc-300 cursor-pointer">
                        {{ $sz }}
                    </button>
                @endforeach
            </div>
        </div>

        <div class="h-5 w-px bg-slate-200 dark:bg-zinc-700 mx-0.5"></div>

        <button type="button" @mousedown.prevent="exec('bold')"          id="tb-bold"   class="tb-btn p-1.5 text-slate-600 dark:text-zinc-300 hover:bg-slate-100 dark:hover:bg-zinc-800 rounded-lg cursor-pointer"><i class="fa-solid fa-bold text-sm"></i></button>
        <button type="button" @mousedown.prevent="exec('italic')"        id="tb-italic" class="tb-btn p-1.5 text-slate-600 dark:text-zinc-300 hover:bg-slate-100 dark:hover:bg-zinc-800 rounded-lg cursor-pointer"><i class="fa-solid fa-italic text-sm"></i></button>
        <button type="button" @mousedown.prevent="exec('underline')"     id="tb-under"  class="tb-btn p-1.5 text-slate-600 dark:text-zinc-300 hover:bg-slate-100 dark:hover:bg-zinc-800 rounded-lg cursor-pointer"><i class="fa-solid fa-underline text-sm"></i></button>
        <button type="button" @mousedown.prevent="exec('strikeThrough')" id="tb-strike" class="tb-btn p-1.5 text-slate-600 dark:text-zinc-300 hover:bg-slate-100 dark:hover:bg-zinc-800 rounded-lg cursor-pointer"><i class="fa-solid fa-strikethrough text-sm"></i></button>

        <!-- Text Color Dropdown -->
        <div class="relative" x-data="{ open: false }" @click.outside="open = false">
            <button type="button" @click="open = !open" @mousedown.prevent
                    class="p-1.5 text-slate-600 dark:text-zinc-300 hover:bg-slate-100 dark:hover:bg-zinc-800 rounded-lg cursor-pointer" title="Text Color">
                <i class="fa-solid fa-font text-sm"></i>
                <div id="color-bar" class="h-0.5 w-full bg-slate-800 rounded-full mt-0.5"></div>
            </button>
            <div x-show="open" x-transition
                 class="absolute left-0 mt-1 p-3 bg-white dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 rounded-xl shadow-xl z-50 w-48 space-y-2.5" style="display:none">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-zinc-800/80 pb-1.5 shrink-0">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Theme Colors</span>
                    <button type="button" @click="execColor('initial'); open = false;" @mousedown.prevent
                            class="text-[10px] font-bold text-amber-500 hover:text-amber-600 dark:hover:text-amber-400 flex items-center gap-1 cursor-pointer">
                        <i class="fa-solid fa-eraser text-[9px]"></i> Reset
                    </button>
                </div>
                <div class="grid grid-cols-6 gap-1.5">
                    @foreach(['#000000', '#374151', '#4b5563', '#6b7280', '#9ca3af', '#d1d5db',
                              '#ef4444', '#f97316', '#f59e0b', '#10b981', '#3b82f6', '#8b5cf6',
                              '#ec4899', '#fca5a5', '#fdba74', '#fcd34d', '#6ee7b7', '#93c5fd'] as $color)
                    <button type="button" @click="execColor('{{ $color }}'); open = false;" @mousedown.prevent
                            class="w-5 h-5 rounded-md border border-slate-200/50 dark:border-zinc-700/50 cursor-pointer hover:scale-110 transition-transform shadow-xs"
                            style="background-color: {{ $color }}"></button>
                    @endforeach
                </div>
                <div class="border-t border-slate-100 dark:border-zinc-800/80 pt-2 flex items-center justify-between">
                    <span class="text-[10px] font-bold text-slate-400 uppercase">Custom</span>
                    <button type="button" @click="$refs.colorInp.click(); open = false;" @mousedown.prevent class="text-[11px] font-bold text-amber-500 hover:underline cursor-pointer">Choose...</button>
                </div>
                <input type="color" x-ref="colorInp" class="absolute opacity-0 w-0 h-0 pointer-events-none" @change="execColor($el.value)">
            </div>
        </div>

        <!-- Highlight Color Dropdown -->
        <div class="relative" x-data="{ open: false }" @click.outside="open = false">
            <button type="button" @click="open = !open" @mousedown.prevent
                    class="p-1.5 text-slate-600 dark:text-zinc-300 hover:bg-slate-100 dark:hover:bg-zinc-800 rounded-lg cursor-pointer" title="Highlight Color">
                <i class="fa-solid fa-highlighter text-sm"></i>
                <div id="hl-bar" class="h-0.5 w-full bg-yellow-300 rounded-full mt-0.5"></div>
            </button>
            <div x-show="open" x-transition
                 class="absolute left-0 mt-1 p-3 bg-white dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 rounded-xl shadow-xl z-50 w-48 space-y-2.5" style="display:none">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-zinc-800/80 pb-1.5 shrink-0">
                    <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Highlight Colors</span>
                    <button type="button" @click="execHighlight('transparent'); open = false;" @mousedown.prevent
                            class="text-[10px] font-bold text-amber-500 hover:text-amber-600 dark:hover:text-amber-400 flex items-center gap-1 cursor-pointer" title="No Fill">
                        <i class="fa-solid fa-droplet-slash text-[9px]"></i> Clear
                    </button>
                </div>
                <div class="grid grid-cols-6 gap-1.5">
                    @foreach(['#fef08a', '#bbf7d0', '#bfdbfe', '#fbcfe8', '#fed7aa', '#ddd6fe',
                              '#eab308', '#22c55e', '#3b82f6', '#ec4899', '#f97316', '#a855f7',
                              '#facc15', '#4ade80', '#60a5fa', '#f472b6', '#fb923c', '#c084fc'] as $color)
                    <button type="button" @click="execHighlight('{{ $color }}'); open = false;" @mousedown.prevent
                            class="w-5 h-5 rounded-md border border-slate-200/50 dark:border-zinc-700/50 cursor-pointer hover:scale-110 transition-transform shadow-xs"
                            style="background-color: {{ $color }}"></button>
                    @endforeach
                </div>
                <div class="border-t border-slate-100 dark:border-zinc-800/80 pt-2 flex items-center justify-between">
                    <span class="text-[10px] font-bold text-slate-400 uppercase">Custom</span>
                    <button type="button" @click="$refs.hlInp.click(); open = false;" @mousedown.prevent class="text-[11px] font-bold text-amber-500 hover:underline cursor-pointer">Choose...</button>
                </div>
                <input type="color" x-ref="hlInp" class="absolute opacity-0 w-0 h-0 pointer-events-none" @change="execHighlight($el.value)">
            </div>
        </div>

        <div class="h-5 w-px bg-slate-200 dark:bg-zinc-700 mx-0.5"></div>

        <button type="button" @mousedown.prevent="exec('justifyLeft')"   id="tb-left"    class="tb-btn p-1.5 text-slate-600 dark:text-zinc-300 hover:bg-slate-100 dark:hover:bg-zinc-800 rounded-lg cursor-pointer"><i class="fa-solid fa-align-left text-sm"></i></button>
        <button type="button" @mousedown.prevent="exec('justifyCenter')" id="tb-center"  class="tb-btn p-1.5 text-slate-600 dark:text-zinc-300 hover:bg-slate-100 dark:hover:bg-zinc-800 rounded-lg cursor-pointer"><i class="fa-solid fa-align-center text-sm"></i></button>
        <button type="button" @mousedown.prevent="exec('justifyRight')"  id="tb-right"   class="tb-btn p-1.5 text-slate-600 dark:text-zinc-300 hover:bg-slate-100 dark:hover:bg-zinc-800 rounded-lg cursor-pointer"><i class="fa-solid fa-align-right text-sm"></i></button>
        <button type="button" @mousedown.prevent="exec('justifyFull')"   id="tb-justify" class="tb-btn p-1.5 text-slate-600 dark:text-zinc-300 hover:bg-slate-100 dark:hover:bg-zinc-800 rounded-lg cursor-pointer"><i class="fa-solid fa-align-justify text-sm"></i></button>

        <div class="h-5 w-px bg-slate-200 dark:bg-zinc-700 mx-0.5"></div>

        <select id="tb-lh" @mousedown.stop @change="applyLineHeight($el.value); $el.value=''"
                class="px-2 py-1 border border-slate-200 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-950 text-sm rounded-lg text-slate-700 dark:text-zinc-300 focus:outline-none cursor-pointer">
            <option value="">Line Height</option>
            <option value="1.0">1.0</option>
            <option value="1.15">1.15</option>
            <option value="1.5">1.5</option>
            <option value="2.0">2.0</option>
        </select>

        <select id="tb-ls" @mousedown.stop @change="applyLetterSpacing($el.value); $el.value=''"
                class="px-2 py-1 border border-slate-200 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-950 text-sm rounded-lg text-slate-700 dark:text-zinc-300 focus:outline-none cursor-pointer">
            <option value="">Letter Spacing</option>
            <option value="0">Normal</option>
            <option value="0.03em">Slightly Wide</option>
            <option value="0.07em">Wide</option>
            <option value="0.12em">Extra Wide</option>
        </select>

        <div class="h-5 w-px bg-slate-200 dark:bg-zinc-700 mx-0.5"></div>

        <button type="button" @mousedown.prevent="exec('insertUnorderedList')" class="p-1.5 text-slate-600 dark:text-zinc-300 hover:bg-slate-100 dark:hover:bg-zinc-800 rounded-lg cursor-pointer" title="Bullet List"><i class="fa-solid fa-list-ul text-sm"></i></button>
        <button type="button" @mousedown.prevent="exec('insertOrderedList')"   class="p-1.5 text-slate-600 dark:text-zinc-300 hover:bg-slate-100 dark:hover:bg-zinc-800 rounded-lg cursor-pointer" title="Numbered List"><i class="fa-solid fa-list-ol text-sm"></i></button>
        <div class="relative" x-data="{ tgOpen: false }" @click.outside="tgOpen = false">
            <button type="button" @mousedown="saveSelection()" @click="tgOpen = !tgOpen"
                    class="p-1.5 text-slate-600 dark:text-zinc-300 hover:bg-slate-100 dark:hover:bg-zinc-800 rounded-lg cursor-pointer" title="Insert Table">
                <i class="fa-solid fa-table text-sm"></i>
            </button>
            <div x-show="tgOpen" x-transition class="tgp-dropdown" style="display:none">
                <div id="tgp-label">1 × 1</div>
                <div class="table-grid-picker" id="table-grid-picker"></div>
            </div>
        </div>
        <button type="button" @mousedown.prevent="exec('insertHorizontalRule')" class="p-1.5 text-slate-600 dark:text-zinc-300 hover:bg-slate-100 dark:hover:bg-zinc-800 rounded-lg cursor-pointer" title="HR"><i class="fa-solid fa-minus text-sm"></i></button>

        <button type="button" @mousedown.prevent="insertPageBreak()"
                class="flex items-center gap-1 px-2.5 py-1 bg-amber-500/10 hover:bg-amber-500/20 border border-amber-400/30 text-amber-700 dark:text-amber-400 rounded-lg text-xs font-bold cursor-pointer transition-all"
                title="Page Break (Ctrl+Enter)">
            <i class="fa-solid fa-file-circle-plus text-sm"></i> Page Break
        </button>

        <button type="button" @mousedown.prevent="exec('removeFormat')" class="p-1.5 text-slate-600 dark:text-zinc-300 hover:bg-slate-100 dark:hover:bg-zinc-800 rounded-lg cursor-pointer" title="Clear Format"><i class="fa-solid fa-eraser text-sm"></i></button>
    </div>

    <!-- ── TABLE FLOATING ICON TOOLBAR ── -->
    <div id="table-ctx-menu">
        <div class="ctx-tb-row">
            <button type="button" class="ctx-tb-btn" data-tt="Add Row Above" onclick="tableAction('addRowAbove')">
                <i class="fa-solid fa-arrow-up fa-xs"></i>
            </button>
            <button type="button" class="ctx-tb-btn" data-tt="Add Row Below" onclick="tableAction('addRowBelow')">
                <i class="fa-solid fa-arrow-down fa-xs"></i>
            </button>
            <button type="button" class="ctx-tb-btn ctx-danger" data-tt="Delete Row" onclick="tableAction('deleteRow')">
                <i class="fa-solid fa-trash fa-xs"></i>
            </button>
            <div class="ctx-tb-divider"></div>
            <button type="button" class="ctx-tb-btn" data-tt="Add Column Left" onclick="tableAction('addColLeft')">
                <i class="fa-solid fa-arrow-left fa-xs"></i>
            </button>
            <button type="button" class="ctx-tb-btn" data-tt="Add Column Right" onclick="tableAction('addColRight')">
                <i class="fa-solid fa-arrow-right fa-xs"></i>
            </button>
            <button type="button" class="ctx-tb-btn ctx-danger" data-tt="Delete Column" onclick="tableAction('deleteCol')">
                <i class="fa-solid fa-trash fa-xs"></i>
            </button>
            <div class="ctx-tb-divider"></div>
            <button type="button" class="ctx-tb-btn" id="tb-btn-merge" data-tt="Merge Cells" onclick="tableAction('mergeCells')">
                <i class="fa-solid fa-object-group fa-xs"></i>
            </button>
            <button type="button" class="ctx-tb-btn" id="tb-btn-unmerge" data-tt="Unmerge Cells" onclick="tableAction('unmergeCells')">
                <i class="fa-solid fa-object-ungroup fa-xs"></i>
            </button>
        </div>
        <div class="ctx-tb-row">
            <div class="ctx-side-btns">
                <button type="button" class="ctx-side-btn active" id="side-all" data-tt="All Borders" onclick="toggleBorderSide('all')">All</button>
                <button type="button" class="ctx-side-btn" id="side-inside" data-tt="Inside Borders" onclick="toggleBorderSide('inside')">Inside</button>
                <button type="button" class="ctx-side-btn" id="side-top" data-tt="Top Border" onclick="toggleBorderSide('top')">Top</button>
                <button type="button" class="ctx-side-btn" id="side-bottom" data-tt="Bottom Border" onclick="toggleBorderSide('bottom')">Bot</button>
                <button type="button" class="ctx-side-btn" id="side-left" data-tt="Left Border" onclick="toggleBorderSide('left')">Left</button>
                <button type="button" class="ctx-side-btn" id="side-right" data-tt="Right Border" onclick="toggleBorderSide('right')">Right</button>
            </div>
            <div class="ctx-tb-divider"></div>
            <button type="button" class="ctx-color-btn" data-tt="Border Color" onclick="openSwatchPopover('border', this)">
                <i class="fa-solid fa-border-all fa-xs"></i>
                <span class="ctx-swatch-dot" id="cell-border-swatch" style="background:#cbd5e1"></span>
            </button>
            <button type="button" class="ctx-color-btn" data-tt="Background Color" onclick="openSwatchPopover('bg', this)">
                <i class="fa-solid fa-fill-drip fa-xs"></i>
                <span class="ctx-swatch-dot" id="cell-bg-swatch" style="background:#ffffff"></span>
            </button>
        </div>
    </div>
    <div id="table-toolbar-tooltip"></div>

    <!-- ── Separate Floating Swatch Popover ── -->
    <div id="ctx-swatch-popover">
        <div class="pop-swatch-grid" id="pop-swatch-grid"></div>
        <button type="button" class="pop-custom-btn" onclick="triggerCustomColorPicker(this)">
            <i class="fa-solid fa-palette text-amber-500"></i> Custom Color
        </button>
    </div>
    <input type="color" id="cell-border-inp" style="position:fixed;opacity:0;pointer-events:none;width:1px;height:1px;z-index:99999" value="#cbd5e1" oninput="tableAction('cellBorder',this.value)" onchange="tableAction('cellBorder',this.value)">
    <input type="color" id="cell-bg-inp"     style="position:fixed;opacity:0;pointer-events:none;width:1px;height:1px;z-index:99999" value="#ffffff" oninput="tableAction('cellBg',this.value)" onchange="tableAction('cellBg',this.value)">

    <!-- Form wraps the entire 3-pane layout -->
    <form id="template-form" action="{{ route('letters.update', $template->id) }}" method="POST" class="flex-1 flex flex-row overflow-hidden" @submit.prevent="doSave($event)">
        @csrf
        @method('PUT')
        <input type="hidden" name="content" id="content-hidden">

        <!-- LEFT Sidebar: Document Settings & Placeholders -->
        <aside class="no-print w-80 bg-white dark:bg-zinc-900 border-r border-slate-200 dark:border-zinc-800 flex flex-col overflow-hidden shrink-0 shadow-sm z-20">
            <!-- Document Settings section -->
            <div class="p-5 border-b border-slate-200 dark:border-zinc-800 shrink-0 space-y-3">
                <h3 class="text-sm font-bold text-slate-800 dark:text-zinc-200 uppercase tracking-wider">Document Settings</h3>
                <div class="space-y-1.5">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-zinc-400">Template Title *</label>
                    <input type="text" name="title" x-model="title" required
                           class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-950 rounded-xl text-sm font-semibold text-slate-900 dark:text-zinc-100 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 outline-none transition-all"
                           placeholder="e.g. Appointment Letter">
                </div>
            </div>

            <!-- Placeholders section -->
            <div class="p-5 border-b border-slate-200 dark:border-zinc-800 shrink-0 space-y-3">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-800 dark:text-zinc-200 uppercase tracking-wider">Placeholders</h3>
                    <p class="text-xs text-amber-600 dark:text-amber-400 font-semibold"><i class="fa-solid fa-keyboard mr-1"></i>Ctrl+Enter → Break</p>
                </div>
                <input type="text" x-model="varSearch" placeholder="Search variables..."
                       class="w-full px-2.5 py-1.5 border border-slate-200 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-950 rounded-lg text-xs text-slate-850 dark:text-zinc-200 focus:outline-none focus:border-amber-500 transition-all outline-none">
            </div>

            <!-- Prebuilt Variables list -->
            <div class="flex-1 overflow-y-auto p-5 space-y-3 min-h-0">
                <h4 class="text-xs font-bold text-slate-400 uppercase tracking-widest flex items-center gap-1.5">
                    <i class="fa-solid fa-id-card text-amber-500"></i> Employee Fields
                </h4>
                <div class="flex flex-wrap gap-1.5">
                    <template x-for="pv in filteredPrebuiltVars" :key="pv.key">
                        <button type="button" @mousedown.prevent="insertVar(pv.key)"
                                class="px-2.5 py-1 bg-amber-500/10 hover:bg-amber-500/25 text-amber-700 dark:text-amber-400 rounded-md text-xs font-semibold font-mono border border-amber-500/20 active:scale-95 cursor-pointer transition-all"
                                x-text="pv.label"></button>
                    </template>
                </div>
            </div>
        </aside>

        <!-- CENTER Pane: Pages -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <div id="editor-scroll">
                <div id="pages-container" contenteditable="true" class="w-full flex flex-col items-center gap-6">
                    <!-- Pages rendered by init() -->
                </div>
            </div>
            <div id="page-info-bar" class="no-print">
                <span x-text="pages + (pages === 1 ? ' page' : ' pages')"></span>
                <span class="text-slate-300 dark:text-zinc-600">·</span>
                <span class="text-amber-600 dark:text-amber-400 font-bold">Ctrl+Enter</span>
                <span class="text-slate-400">= Page Break</span>
            </div>
        </div>

        <!-- RIGHT Sidebar: Custom Variables, Margins, Page Count, Submit -->
        <aside class="no-print w-80 bg-white dark:bg-zinc-900 border-l border-slate-200 dark:border-zinc-800 flex flex-col overflow-hidden shrink-0 shadow-sm z-20">
            <!-- Header -->
            <div class="p-5 border-b border-slate-200 dark:border-zinc-800 space-y-1 shrink-0">
                <h3 class="text-sm font-bold text-slate-800 dark:text-zinc-200 uppercase tracking-wider">Configuration</h3>
                <p class="text-xs text-slate-400">Custom variables and formatting.</p>
            </div>

            <!-- Body (Scrollable) -->
            <div class="flex-1 overflow-y-auto p-5 space-y-6 min-h-0">
                
                @if($errors->any())
                <div class="p-3 bg-rose-50 dark:bg-rose-950/20 border border-rose-200 dark:border-rose-800 rounded-xl text-rose-700 dark:text-rose-400 text-xs space-y-1">
                    @foreach($errors->all() as $e)<p>• {{ $e }}</p>@endforeach
                </div>
                @endif

                <!-- Page Margins -->
                <div class="p-4 bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 rounded-2xl space-y-3">
                    <div class="flex items-center justify-between">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-zinc-400">Page Margins (mm)</label>
                        <label class="flex items-center gap-1.5 cursor-pointer select-none">
                            <input type="hidden" name="different_first_page_margins" value="0">
                            <input type="checkbox" name="different_first_page_margins" value="1"
                                   x-model="differentFirstPageMargins"
                                   @change="applyMarginsToAll()"
                                   class="rounded border-slate-300 dark:border-zinc-700 text-amber-500 focus:ring-amber-500 text-xs">
                            <span class="text-[10px] font-semibold text-slate-600 dark:text-zinc-400">Diff 1st Page</span>
                        </label>
                    </div>

                    <!-- First Page Margins (shown when differentFirstPageMargins is true) -->
                    <div x-show="differentFirstPageMargins" class="space-y-1.5 pb-2 border-b border-slate-200/60 dark:border-zinc-800/80">
                        <span class="block text-[10px] font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400">
                            1st Page Margins
                        </span>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach(['top' => 'Top', 'bottom' => 'Bottom', 'left' => 'Left', 'right' => 'Right'] as $side => $label)
                            <div>
                                <label class="block text-[9px] font-bold text-slate-400 mb-0.5">{{ $label }}</label>
                                <input type="number" name="first_page_margin_{{ $side }}" x-model.number="firstPageMargins.{{ $side }}"
                                       min="0" max="100"
                                       class="w-full p-1.5 border border-slate-200 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-900 text-xs text-center text-slate-800 dark:text-zinc-200 focus:border-amber-500 outline-none"
                                       @change="applyMarginsToAll()">
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Other / Global Pages Margins -->
                    <div class="space-y-1.5">
                        <span x-show="differentFirstPageMargins" class="block text-[10px] font-bold uppercase tracking-wider text-amber-600 dark:text-amber-400">
                            Other Pages Margins
                        </span>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach(['top' => 'Top', 'bottom' => 'Bottom', 'left' => 'Left', 'right' => 'Right'] as $side => $label)
                            <div>
                                <label class="block text-[9px] font-bold text-slate-400 mb-0.5">{{ $label }}</label>
                                <input type="number" name="margin_{{ $side }}" x-model.number="margins.{{ $side }}"
                                       min="0" max="100"
                                       class="w-full p-1.5 border border-slate-200 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-900 text-xs text-center text-slate-800 dark:text-zinc-200 focus:border-amber-500 outline-none"
                                       @change="applyMarginsToAll()">
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Page Count Display -->
                <div class="p-3 bg-amber-50 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-800/50 rounded-xl text-center">
                    <div class="text-2xl font-black text-amber-600" x-text="pages"></div>
                    <div class="text-xs font-semibold text-amber-700 dark:text-amber-400" x-text="pages === 1 ? 'Page' : 'Pages'"></div>
                </div>

                <!-- Custom Variables Editor -->
                <div class="space-y-3 border-t border-slate-100 dark:border-zinc-800 pt-4">
                    <div class="flex items-center justify-between">
                        <h4 class="text-xs font-bold text-slate-400 uppercase tracking-widest flex items-center gap-1.5">
                            <i class="fa-solid fa-sliders text-amber-500"></i> Custom Variables
                        </h4>
                        <button type="button" @mousedown.prevent="addVariable()"
                                class="px-2 py-1 bg-amber-500 text-white text-[10px] font-bold rounded-md hover:bg-amber-600 cursor-pointer">
                            <i class="fa-solid fa-plus mr-0.5"></i> Add
                        </button>
                    </div>
                    <div class="space-y-2.5">
                        <template x-for="(v, idx) in variables" :key="idx">
                            <div class="relative p-3 bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 rounded-xl space-y-2">
                                <button type="button" @mousedown.prevent="removeVariable(idx)" class="absolute top-2 right-2 text-slate-400 hover:text-rose-500 cursor-pointer"><i class="fa-solid fa-xmark text-xs"></i></button>
                                <div class="grid grid-cols-2 gap-2 pr-4">
                                    <div>
                                        <label class="block text-[9px] font-bold uppercase text-slate-400 mb-0.5">Key</label>
                                        <input type="text" x-model="v.key" placeholder="var_name" class="w-full px-2 py-1 border border-slate-200 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-900 text-xs font-mono focus:border-amber-500 outline-none">
                                    </div>
                                    <div>
                                        <label class="block text-[9px] font-bold uppercase text-slate-400 mb-0.5">Type</label>
                                        <select x-model="v.type" class="w-full px-2 py-1 border border-slate-200 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-900 text-xs focus:border-amber-500 outline-none cursor-pointer">
                                            <option value="text">Text</option>
                                            <option value="date">Date</option>
                                            <option value="number">Number</option>
                                            <option value="boolean">Yes/No</option>
                                            <option value="dropdown">Dropdown</option>
                                        </select>
                                    </div>
                                </div>
                                <div x-show="v.type === 'dropdown'">
                                    <label class="block text-[9px] font-bold uppercase text-slate-400 mb-0.5">Options</label>
                                    <input type="text" x-model="v.options" placeholder="Option 1, Option 2" class="w-full px-2 py-1 border border-slate-200 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-900 text-xs focus:border-amber-500 outline-none">
                                </div>
                                <div x-show="v.key" class="flex items-center justify-between pt-1">
                                    <span class="text-[10px] text-slate-400">Insert:</span>
                                    <button type="button" @mousedown.prevent="insertVar(v.key)" class="text-xs font-bold font-mono text-amber-600 dark:text-amber-400 hover:underline cursor-pointer">@{{ <span x-text="v.key"></span> }}</button>
                                </div>
                            </div>
                        </template>
                        <div x-show="variables.length === 0" class="py-5 text-center text-xs text-slate-400 italic border border-dashed border-slate-200 dark:border-zinc-800 rounded-xl">No custom variables yet.</div>
                    </div>
                </div>

            </div>

            <!-- Footer (Submit buttons) -->
            <div class="p-4 border-t border-slate-200 dark:border-zinc-800 space-y-2 shrink-0 bg-white dark:bg-zinc-900">
                <button type="submit" class="w-full py-2.5 px-4 bg-amber-500 hover:bg-amber-600 text-white font-bold text-sm rounded-xl shadow-md shadow-amber-500/20 transition-all cursor-pointer">
                    <i class="fa-solid fa-floppy-disk mr-1.5"></i> Update Template
                </button>
                <a href="{{ route('letters.index') }}" class="block text-center py-2 text-sm text-slate-500 dark:text-zinc-400 hover:text-slate-800 dark:hover:text-zinc-200 transition-colors">Cancel</a>
            </div>
        </aside>
    </form>

    </div>
</div>
@endsection

@section('scripts')
<script>
let savedRange = null;

function getEditorRoot() {
    return document.getElementById('pages-container');
}

function getActive() {
    const sel = window.getSelection();
    if (sel && sel.rangeCount > 0) {
        const node = sel.getRangeAt(0).commonAncestorContainer;
        const content = node.nodeType === Node.ELEMENT_NODE
            ? node.closest('.doc-page-content')
            : node.parentNode?.closest('.doc-page-content');
        if (content) return content;
    }
    return document.querySelector('.doc-page-content');
}

function saveSelection() {
    const sel = window.getSelection();
    if (sel && sel.rangeCount > 0) {
        const root = getEditorRoot();
        if (root && root.contains(sel.getRangeAt(0).commonAncestorContainer)) {
            savedRange = sel.getRangeAt(0).cloneRange();
        }
    }
}

function restoreSelection() {
    if (savedRange) {
        const root = getEditorRoot();
        if (root) root.focus();
        const sel = window.getSelection();
        sel.removeAllRanges();
        sel.addRange(savedRange);
    }
}

function exec(cmd, val = null) {
    restoreSelection();
    document.execCommand(cmd, false, val);
}

function execBlock(tag) {
    restoreSelection();
    document.execCommand('formatBlock', false, '<' + tag + '>');
}

function execFont(name) {
    restoreSelection();
    document.execCommand('fontName', false, name);
}

function execFontSize(pt) {
    restoreSelection();
    const sel = window.getSelection();
    if (!sel || sel.rangeCount === 0) return;

    const inp = document.getElementById('tb-size');
    if (inp) inp.value = pt;

    if (sel.isCollapsed) {
        const range = sel.getRangeAt(0);
        const span = document.createElement('span');
        span.style.fontSize = pt + 'pt';
        const zwsp = document.createTextNode('\u200B');
        span.appendChild(zwsp);
        range.insertNode(span);
        const newRange = document.createRange();
        newRange.setStart(zwsp, 1);
        newRange.collapse(true);
        sel.removeAllRanges();
        sel.addRange(newRange);
        saveSelection();
        return;
    }

    const range = sel.getRangeAt(0);
    const span = document.createElement('span');
    span.style.fontSize = pt + 'pt';

    try {
        const extracted = range.extractContents();
        span.appendChild(extracted);
        range.insertNode(span);
        const newRange = document.createRange();
        newRange.selectNodeContents(span);
        sel.removeAllRanges();
        sel.addRange(newRange);
        saveSelection();
    } catch (e) {
        document.execCommand('styleWithCSS', false, true);
        document.execCommand('fontSize', false, '7');
        const root = getEditorRoot();
        if (root) {
            root.querySelectorAll('[size="7"], font[size]').forEach(n => {
                n.removeAttribute('size');
                n.style.fontSize = pt + 'pt';
            });
            root.querySelectorAll('span[style*="-webkit-xxx-large"], span[style*="xx-large"]').forEach(n => {
                n.style.fontSize = pt + 'pt';
            });
        }
    }
}

function adjustSize(delta) {
    const inp = document.getElementById('tb-size');
    let s = Math.max(1, Math.min(100, (parseInt(inp.value) || 12) + delta));
    inp.value = s;
    execFontSize(s);
}

function execColor(color) {
    restoreSelection();
    const bar = document.getElementById('color-bar');
    if (bar) bar.style.background = color;
    document.execCommand('foreColor', false, color);
}

function execHighlight(color) {
    restoreSelection();
    const bar = document.getElementById('hl-bar');
    if (bar) bar.style.background = color;
    document.execCommand('hiliteColor', false, color);
}

function applyToCurrentBlock(prop, value) {
    restoreSelection();
    const sel = window.getSelection();
    const el = getActive();
    if (!sel || !el) return;

    const apply = (node) => {
        let cur = node && node.nodeType === 3 ? node.parentNode : node;
        while (cur && cur !== el) {
            const tag = cur.tagName ? cur.tagName.toLowerCase() : '';
            if (['p','div','h1','h2','h3','li','td','th'].includes(tag)) {
                cur.style[prop] = value;
                return;
            }
            cur = cur.parentNode;
        }
        el.style[prop] = value;
    };

    if (sel.rangeCount > 0) {
        const range = sel.getRangeAt(0);
        apply(range.startContainer);
        el.querySelectorAll('p, div, h1, h2, h3, li, td, th').forEach(block => {
            if (sel.containsNode(block, true)) block.style[prop] = value;
        });
    }
}

function insertTable(rows, cols) {
    const curCell = getCurrentCell();
    if (curCell) return; // Prevent nesting table inside cell
    rows = rows || 3;
    cols = cols || 3;
    const el = getActive();
    if (!el) return;
    el.focus();
    const colPct = (100 / cols).toFixed(2);
    let html = '<table style="width:100%;table-layout:fixed;"><tbody>';
    for (let r = 0; r < rows; r++) {
        html += '<tr>';
        for (let c = 0; c < cols; c++) {
            html += '<td style="width:' + colPct + '%;">&nbsp;</td>';
        }
        html += '</tr>';
    }
    html += '</tbody></table><p><br></p>';
    document.execCommand('insertHTML', false, html);
    initTableResizeHandles();
}

/* ── Table manipulation ── */
let _activeCell = null;
let _selectedCells = new Set();
let _lastAnchorCell = null;
let _isCellDragging = false;
let _hasDragged = false;
let _dragStartCell = null;
let _ignoreNextClick = false;
let _borderSides = new Set(['all']); // 'all', 'inside', 'top', 'bottom', 'left', 'right'
let _popoverType = null; // 'border' or 'bg'

function getSelectionBoundingRect(cells) {
    if (!cells || cells.length === 0) return null;
    let minLeft = Infinity, minTop = Infinity, maxRight = -Infinity, maxBottom = -Infinity;
    cells.forEach(c => {
        const r = c.getBoundingClientRect();
        if (r.left < minLeft) minLeft = r.left;
        if (r.top < minTop) minTop = r.top;
        if (r.right > maxRight) maxRight = r.right;
        if (r.bottom > maxBottom) maxBottom = r.bottom;
    });
    if (minLeft === Infinity) return null;
    return {
        left: minLeft,
        top: minTop,
        right: maxRight,
        bottom: maxBottom,
        width: maxRight - minLeft,
        height: maxBottom - minTop
    };
}

function getTableMatrix(table) {
    const matrix = [];
    const rows = Array.from(table.rows);
    for (let r = 0; r < rows.length; r++) {
        matrix[r] = [];
    }
    for (let r = 0; r < rows.length; r++) {
        let col = 0;
        const row = rows[r];
        for (let c = 0; c < row.cells.length; c++) {
            const cell = row.cells[c];
            while (matrix[r][col]) {
                col++;
            }
            const rSpan = cell.rowSpan || 1;
            const cSpan = cell.colSpan || 1;
            for (let dr = 0; dr < rSpan; dr++) {
                for (let dc = 0; dc < cSpan; dc++) {
                    if (!matrix[r + dr]) matrix[r + dr] = [];
                    matrix[r + dr][col + dc] = {
                        cell: cell,
                        isOrigin: dr === 0 && dc === 0,
                        gridRow: r,
                        gridCol: col,
                        rowSpan: rSpan,
                        colSpan: cSpan
                    };
                }
            }
            col += cSpan;
        }
    }
    return matrix;
}

function mergeSelectedCells() {
    if (_selectedCells.size < 2) return;
    const cells = Array.from(_selectedCells).filter(c => c.isConnected);
    if (cells.length < 2) return;
    const table = cells[0].closest('table');
    if (!table) return;

    const matrix = getTableMatrix(table);
    let minR = Infinity, maxR = -Infinity, minC = Infinity, maxC = -Infinity;

    for (let r = 0; r < matrix.length; r++) {
        for (let c = 0; c < matrix[r].length; c++) {
            const item = matrix[r][c];
            if (item && _selectedCells.has(item.cell)) {
                if (r < minR) minR = r;
                if (r > maxR) maxR = r;
                if (c < minC) minC = c;
                if (c > maxC) maxC = c;
            }
        }
    }

    if (minR === Infinity) return;

    const masterItem = matrix[minR][minC];
    if (!masterItem) return;
    const masterCell = masterItem.cell;

    const targetRowSpan = maxR - minR + 1;
    const targetColSpan = maxC - minC + 1;

    const seenCells = new Set([masterCell]);
    const extraContents = [];

    for (let r = minR; r <= maxR; r++) {
        for (let c = minC; c <= maxC; c++) {
            const item = matrix[r] ? matrix[r][c] : null;
            if (item && item.isOrigin && !seenCells.has(item.cell)) {
                seenCells.add(item.cell);
                const txt = item.cell.innerHTML.trim();
                if (txt && txt !== '&nbsp;' && txt !== '<br>') {
                    extraContents.push(txt);
                }
                item.cell.remove();
            }
        }
    }

    if (extraContents.length > 0) {
        const masterTxt = masterCell.innerHTML.trim();
        if (!masterTxt || masterTxt === '&nbsp;' || masterTxt === '<br>') {
            masterCell.innerHTML = extraContents.join(' ');
        } else {
            masterCell.innerHTML = masterTxt + ' ' + extraContents.join(' ');
        }
    }

    masterCell.rowSpan = targetRowSpan;
    masterCell.colSpan = targetColSpan;

    const totalCols = matrix[0] ? matrix[0].length : 1;
    masterCell.style.width = ((targetColSpan / totalCols) * 100).toFixed(2) + '%';

    clearCellSelection();
    _selectedCells.add(masterCell);
    masterCell.classList.add('cell-selected');
    _lastAnchorCell = masterCell;
    _activeCell = masterCell;

    initTableResizeHandles();
    showTableMenuForSelection();
}

function unmergeSelectedCells() {
    const targets = getSelectedOrActiveCells();
    const cell = targets[0] || _activeCell || getCurrentCell();
    if (!cell || !cell.isConnected) return;
    if ((cell.rowSpan || 1) <= 1 && (cell.colSpan || 1) <= 1) return;

    const table = cell.closest('table');
    if (!table) return;

    const matrix = getTableMatrix(table);
    let startR = -1, startC = -1;

    for (let r = 0; r < matrix.length; r++) {
        for (let c = 0; c < matrix[r].length; c++) {
            if (matrix[r][c]?.cell === cell && matrix[r][c]?.isOrigin) {
                startR = r;
                startC = c;
                break;
            }
        }
        if (startR !== -1) break;
    }

    if (startR === -1) return;

    const rSpan = cell.rowSpan || 1;
    const cSpan = cell.colSpan || 1;
    const totalCols = matrix[0] ? matrix[0].length : 1;
    const singlePct = (100 / totalCols).toFixed(2) + '%';

    cell.rowSpan = 1;
    cell.colSpan = 1;
    cell.style.width = singlePct;

    let insertRef = cell;
    for (let c = 1; c < cSpan; c++) {
        const newTd = document.createElement(cell.tagName.toLowerCase());
        newTd.innerHTML = '&nbsp;';
        newTd.style.cssText = cell.style.cssText;
        newTd.style.width = singlePct;
        newTd.rowSpan = 1;
        newTd.colSpan = 1;
        insertRef.after(newTd);
        insertRef = newTd;
    }

    for (let r = startR + 1; r < startR + rSpan; r++) {
        const tr = table.rows[r];
        if (!tr) continue;

        let nextCellInRow = null;
        for (let c = startC + cSpan; c < matrix[r].length; c++) {
            if (matrix[r][c]?.isOrigin) {
                nextCellInRow = matrix[r][c].cell;
                break;
            }
        }

        for (let dc = 0; dc < cSpan; dc++) {
            const newTd = document.createElement(cell.tagName.toLowerCase());
            newTd.innerHTML = '&nbsp;';
            newTd.style.cssText = cell.style.cssText;
            newTd.style.width = singlePct;
            newTd.rowSpan = 1;
            newTd.colSpan = 1;
            if (nextCellInRow && nextCellInRow.parentNode === tr) {
                tr.insertBefore(newTd, nextCellInRow);
            } else {
                tr.appendChild(newTd);
            }
        }
    }

    clearCellSelection();
    _selectedCells.add(cell);
    cell.classList.add('cell-selected');
    _lastAnchorCell = cell;
    _activeCell = cell;

    initTableResizeHandles();
    showTableMenuForSelection();
}

function selectCellRange(cellA, cellB) {
    const tableA = cellA ? cellA.closest('table') : null;
    const tableB = cellB ? cellB.closest('table') : null;
    if (!tableA || tableA !== tableB) return;

    clearCellSelection();

    const matrix = getTableMatrix(tableA);
    let rA = -1, cA = -1, rB = -1, cB = -1;

    for (let r = 0; r < matrix.length; r++) {
        for (let c = 0; c < matrix[r].length; c++) {
            if (matrix[r][c]?.cell === cellA) {
                if (rA === -1 || r < rA) rA = r;
                if (cA === -1 || c < cA) cA = c;
            }
            if (matrix[r][c]?.cell === cellB) {
                if (rB === -1 || r > rB) rB = r;
                if (cB === -1 || c > cB) cB = c;
            }
        }
    }

    if (rA === -1 || rB === -1) {
        const rowA = cellA.parentElement ? cellA.parentElement.rowIndex : 0;
        const rowB = cellB.parentElement ? cellB.parentElement.rowIndex : 0;
        const colA = cellA.cellIndex;
        const colB = cellB.cellIndex;
        rA = rowA; rB = rowB; cA = colA; cB = colB;
    }

    const minRow = Math.min(rA, rB);
    const maxRow = Math.max(rA, rB);
    const minCol = Math.min(cA, cB);
    const maxCol = Math.max(cA, cB);

    for (let r = minRow; r <= maxRow; r++) {
        if (!matrix[r]) continue;
        for (let c = minCol; c <= maxCol; c++) {
            const item = matrix[r][c];
            if (item && item.cell) {
                item.cell.classList.add('cell-selected');
                _selectedCells.add(item.cell);
            }
        }
    }
}

function getSelectedOrActiveCells() {
    if (_selectedCells.size > 0) {
        const connected = Array.from(_selectedCells).filter(c => c.isConnected);
        if (connected.length > 0) return connected;
    }
    const c = _activeCell || getCurrentCell();
    return (c && c.isConnected) ? [c] : [];
}

function clearCellSelection() {
    _selectedCells.forEach(cell => cell.classList.remove('cell-selected'));
    _selectedCells.clear();
}

function showTableMenuForSelection() {
    const cells = getSelectedOrActiveCells();
    if (cells.length === 0) {
        hideTableMenu();
        return;
    }
    const rect = getSelectionBoundingRect(cells);
    const anchor = (_dragStartCell && _dragStartCell.isConnected) ? _dragStartCell : cells[0];
    if (rect && anchor) {
        showTableMenu(anchor, rect.left, rect.bottom + 4, rect.top);
    }
}

function toggleBorderSide(side) {
    const btnAll = document.getElementById('side-all');
    if (side === 'all') {
        _borderSides.clear();
        _borderSides.add('all');
    } else {
        _borderSides.delete('all');
        if (_borderSides.has(side)) {
            _borderSides.delete(side);
            if (_borderSides.size === 0) _borderSides.add('all');
        } else {
            _borderSides.add(side);
        }
    }
    // Update button states
    ['all', 'inside', 'top', 'bottom', 'left', 'right'].forEach(s => {
        const b = document.getElementById('side-' + s);
        if (b) b.classList.toggle('active', _borderSides.has(s));
    });
}

function openSwatchPopover(type, triggerEl) {
    _popoverType = type;
    const popover = document.getElementById('ctx-swatch-popover');
    const grid = document.getElementById('pop-swatch-grid');
    if (!popover || !grid) return;

    grid.innerHTML = '';
    const borderPresets = ['#000000', '#64748b', '#cbd5e1', '#e2e8f0', '#ef4444', '#f59e0b', '#10b981', '#3b82f6', '#8b5cf6', '#ec4899', '#78350f', '#0f172a'];
    const bgPresets = ['transparent', '#ffffff', '#f8fafc', '#f1f5f9', '#fef3c7', '#dcfce7', '#dbeafe', '#f3e8ff', '#fce7f3', '#fed7aa', '#fee2e2', '#e0f2fe'];
    const colors = type === 'border' ? borderPresets : bgPresets;

    colors.forEach(c => {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'pop-swatch-btn';
        if (c === 'transparent') {
            btn.style.background = 'linear-gradient(45deg, #ddd 25%, transparent 25%, transparent 75%, #ddd 75%), linear-gradient(45deg, #ddd 25%, transparent 25%, transparent 75%, #ddd 75%)';
            btn.style.backgroundSize = '8px 8px';
            btn.style.backgroundPosition = '0 0, 4px 4px';
        } else {
            btn.style.background = c;
        }
        btn.title = c;
        btn.onclick = (e) => {
            e.stopPropagation();
            tableAction(type === 'border' ? 'cellBorder' : 'cellBg', c);
            closeSwatchPopover();
        };
        grid.appendChild(btn);
    });

    const rect = triggerEl.getBoundingClientRect();
    popover.style.display = 'block';
    const pRect = popover.getBoundingClientRect();
    let left = rect.right + 6;
    if (left + pRect.width > window.innerWidth - 8) {
        left = Math.max(8, rect.left - pRect.width - 6);
    }
    let top = Math.max(8, Math.min(rect.top, window.innerHeight - pRect.height - 8));
    popover.style.left = left + 'px';
    popover.style.top = top + 'px';
}

function closeSwatchPopover() {
    const popover = document.getElementById('ctx-swatch-popover');
    if (popover) popover.style.display = 'none';
    _popoverType = null;
}

function triggerCustomColorPicker(btn) {
    const inputId = _popoverType === 'border' ? 'cell-border-inp' : 'cell-bg-inp';
    const inp = document.getElementById(inputId);
    if (inp) {
        const rect = btn.getBoundingClientRect();
        inp.style.left = rect.left + 'px';
        inp.style.top = rect.bottom + 'px';
        inp.click();
    }
    closeSwatchPopover();
}

function getCurrentCell() {
    const sel = window.getSelection();
    if (!sel || sel.rangeCount === 0) return null;
    const node = sel.getRangeAt(0).commonAncestorContainer;
    const el = node.nodeType === 3 ? node.parentNode : node;
    return el.closest('td, th');
}

function tableAction(action, value) {
    const targets = getSelectedOrActiveCells();
    const cell = targets[0] || _activeCell || getCurrentCell();
    if (!cell) return;
    const row   = cell.parentElement;
    const table = cell.closest('table');
    if (!row || !table) return;
    const cellIdx = cell.cellIndex;
    const rows    = Array.from(table.querySelectorAll('tr'));

    if (action === 'mergeCells') {
        mergeSelectedCells();
        return;
    } else if (action === 'unmergeCells') {
        unmergeSelectedCells();
        return;
    } else if (action === 'addRowAbove' || action === 'addRowBelow') {
        const newRow = document.createElement('tr');
        for (let i = 0; i < row.cells.length; i++) {
            const td = document.createElement('td');
            td.innerHTML = '&nbsp;';
            td.style.cssText = row.cells[i].style.cssText;
            newRow.appendChild(td);
        }
        if (action === 'addRowAbove') row.parentNode.insertBefore(newRow, row);
        else row.after(newRow);
        initTableResizeHandles();

    } else if (action === 'deleteRow') {
        if (rows.length > 1) row.remove();
        else table.remove();
        clearCellSelection();
        hideTableMenu();
        return;

    } else if (action === 'addColLeft' || action === 'addColRight') {
        const totalCols = row.cells.length + 1;
        const newPct = (100 / totalCols).toFixed(2) + '%';
        rows.forEach(r => {
            const ref = r.cells[cellIdx];
            if (!ref) return;
            const newCell = document.createElement(ref.tagName.toLowerCase());
            newCell.innerHTML = '&nbsp;';
            if (action === 'addColLeft') r.insertBefore(newCell, ref);
            else ref.after(newCell);
        });
        rows.forEach(r => {
            Array.from(r.cells).forEach(c => c.style.width = newPct);
        });
        initTableResizeHandles();

    } else if (action === 'deleteCol') {
        if (row.cells.length > 1) {
            rows.forEach(r => { if (r.cells[cellIdx]) r.cells[cellIdx].remove(); });
            const totalCols = (row.cells.length || 1);
            const newPct = (100 / totalCols).toFixed(2) + '%';
            rows.forEach(r => {
                Array.from(r.cells).forEach(c => c.style.width = newPct);
            });
            initTableResizeHandles();
            clearCellSelection();
            hideTableMenu();
        } else {
            table.remove();
            clearCellSelection();
            hideTableMenu();
        }
        return;

    } else if (action === 'cellBorder') {
        if (_borderSides.has('all')) {
            targets.forEach(c => {
                c.style.border = '1px solid ' + value;
            });
        } else {
            if (_borderSides.has('inside')) {
                if (targets.length === 1) {
                    const table = targets[0].closest('table');
                    if (table) {
                        const rows = table.rows;
                        const totalRows = rows.length;
                        const totalCols = rows[0] ? rows[0].cells.length : 0;
                        for (let r = 0; r < totalRows; r++) {
                            for (let c = 0; c < totalCols; c++) {
                                const cell = rows[r].cells[c];
                                if (!cell) continue;
                                if (r < totalRows - 1) cell.style.borderBottom = '1px solid ' + value;
                                if (r > 0) cell.style.borderTop = '1px solid ' + value;
                                if (c < totalCols - 1) cell.style.borderRight = '1px solid ' + value;
                                if (c > 0) cell.style.borderLeft = '1px solid ' + value;
                            }
                        }
                    }
                } else if (targets.length > 1) {
                    let minR = Infinity, maxR = -Infinity, minC = Infinity, maxC = -Infinity;
                    targets.forEach(c => {
                        const r = c.parentElement.rowIndex;
                        const col = c.cellIndex;
                        if (r < minR) minR = r;
                        if (r > maxR) maxR = r;
                        if (col < minC) minC = col;
                        if (col > maxC) maxC = col;
                    });
                    targets.forEach(c => {
                        const r = c.parentElement.rowIndex;
                        const col = c.cellIndex;
                        if (r < maxR) c.style.borderBottom = '1px solid ' + value;
                        if (r > minR) c.style.borderTop = '1px solid ' + value;
                        if (col < maxC) c.style.borderRight = '1px solid ' + value;
                        if (col > minC) c.style.borderLeft = '1px solid ' + value;
                    });
                }
            }

            targets.forEach(c => {
                if (_borderSides.has('top'))    c.style.borderTop = '1px solid ' + value;
                if (_borderSides.has('bottom')) c.style.borderBottom = '1px solid ' + value;
                if (_borderSides.has('left'))   c.style.borderLeft = '1px solid ' + value;
                if (_borderSides.has('right'))  c.style.borderRight = '1px solid ' + value;
            });
        }
        const sw = document.getElementById('cell-border-swatch');
        if (sw) sw.style.background = value;

    } else if (action === 'cellBg') {
        targets.forEach(c => {
            c.style.backgroundColor = value === 'transparent' ? 'transparent' : value;
        });
        const sw = document.getElementById('cell-bg-swatch');
        if (sw) sw.style.background = value === 'transparent' ? '#ffffff' : value;
    }
}

function updateMergeButtonsState() {
    const btnMerge = document.getElementById('tb-btn-merge');
    const btnUnmerge = document.getElementById('tb-btn-unmerge');
    const targets = getSelectedOrActiveCells();
    const canMerge = _selectedCells.size > 1;
    const canUnmerge = targets.some(c => ((c.colSpan || 1) > 1 || (c.rowSpan || 1) > 1));

    if (btnMerge) {
        btnMerge.disabled = !canMerge;
        btnMerge.style.opacity = canMerge ? '1' : '0.35';
        btnMerge.style.pointerEvents = canMerge ? 'auto' : 'none';
    }
    if (btnUnmerge) {
        btnUnmerge.disabled = !canUnmerge;
        btnUnmerge.style.opacity = canUnmerge ? '1' : '0.35';
        btnUnmerge.style.pointerEvents = canUnmerge ? 'auto' : 'none';
    }
}

let _ttTimer = null;

function showTableToolbarTooltip(el) {
    const tt = document.getElementById('table-toolbar-tooltip');
    if (!tt) return;
    const text = el.getAttribute('data-tt');
    if (!text) return;
    tt.textContent = text;
    tt.style.display = 'block';

    const r = el.getBoundingClientRect();
    const tr = tt.getBoundingClientRect();

    let left = r.left + (r.width / 2) - (tr.width / 2);
    left = Math.max(8, Math.min(left, window.innerWidth - tr.width - 8));
    let top = r.top - tr.height - 6;
    if (top < 8) {
        top = r.bottom + 6;
    }
    tt.style.left = left + 'px';
    tt.style.top = top + 'px';
    tt.classList.add('visible');
}

function hideTableToolbarTooltip() {
    clearTimeout(_ttTimer);
    _ttTimer = null;
    const tt = document.getElementById('table-toolbar-tooltip');
    if (tt) {
        tt.classList.remove('visible');
        tt.style.display = 'none';
    }
}

function initTableToolbarTooltips() {
    const menu = document.getElementById('table-ctx-menu');
    if (!menu) return;

    menu.addEventListener('mouseover', (e) => {
        const item = e.target.closest('[data-tt]');
        if (!item || !menu.contains(item)) return;
        if (item._ttHovered) return;
        item._ttHovered = true;

        clearTimeout(_ttTimer);
        _ttTimer = setTimeout(() => {
            showTableToolbarTooltip(item);
        }, 2000);
    });

    menu.addEventListener('mouseout', (e) => {
        const item = e.target.closest('[data-tt]');
        if (item) {
            item._ttHovered = false;
        }
        hideTableToolbarTooltip();
    });

    menu.addEventListener('click', () => {
        hideTableToolbarTooltip();
    });
}

function showTableMenu(cell, x, y, topY = null) {
    _activeCell = cell;
    const menu = document.getElementById('table-ctx-menu');
    if (!menu) return;
    const bc = cell.style.borderColor || '';
    const bg = cell.style.backgroundColor || '';
    const bsw = document.getElementById('cell-border-swatch');
    const gsw = document.getElementById('cell-bg-swatch');
    if (bsw) bsw.style.background = bc || '#cbd5e1';
    if (gsw) gsw.style.background = bg || '#ffffff';
    const bi = document.getElementById('cell-border-inp');
    const gi = document.getElementById('cell-bg-inp');
    if (bi) bi.value = rgbToHex(bc) || '#cbd5e1';
    if (gi) gi.value = rgbToHex(bg) || '#ffffff';

    updateMergeButtonsState();
    menu.style.display = 'block';

    const vw = window.innerWidth, vh = window.innerHeight;
    const mr = menu.getBoundingClientRect();
    let left = Math.max(8, Math.min(x, vw - mr.width - 12));
    let top = y + 4;
    if (top + mr.height > vh - 10) {
        const altY = topY !== null ? topY : y;
        top = Math.max(10, altY - mr.height - 8);
    }
    menu.style.left = left + 'px';
    menu.style.top  = top  + 'px';
}

function hideTableMenu() {
    const menu = document.getElementById('table-ctx-menu');
    if (menu) menu.style.display = 'none';
    closeSwatchPopover();
    hideTableToolbarTooltip();
    _activeCell = null;
}

function rgbToHex(rgb) {
    if (!rgb || rgb === 'transparent' || rgb === '') return null;
    if (rgb.startsWith('#')) return rgb;
    const m = rgb.match(/\d+/g);
    if (!m || m.length < 3) return null;
    return '#' + m.slice(0, 3).map(n => (+n).toString(16).padStart(2, '0')).join('');
}

/* ── Table column resizing ── */
function initTableResizeHandles() {
    const root = getEditorRoot();
    if (!root) return;
    root.querySelectorAll('table').forEach(table => {
        table.style.tableLayout = 'fixed';
        table.style.width = '100%';
        table.style.maxWidth = '100%';

        table.querySelectorAll('.col-resize-handle').forEach(h => h.remove());

        const rows = table.querySelectorAll('tr');
        if (rows.length === 0) return;
        const firstRow = rows[0];
        const cells = Array.from(firstRow.cells);

        cells.forEach((cell, idx) => {
            if (idx < cells.length - 1) {
                const handle = document.createElement('div');
                handle.className = 'col-resize-handle';
                handle.contentEditable = 'false';
                cell.appendChild(handle);
            }
        });
    });
}

function setupTableResizeListener(container) {
    let activeHandle = null;
    let startX = 0;
    let curCell = null;
    let nextCell = null;
    let curWidth = 0;
    let nextWidth = 0;
    let table = null;

    container.addEventListener('mousedown', (e) => {
        const handle = e.target.closest('.col-resize-handle');
        if (!handle) return;
        e.preventDefault();
        e.stopPropagation();

        activeHandle = handle;
        activeHandle.classList.add('resizing');
        document.body.classList.add('is-col-resizing');

        curCell = handle.closest('td, th');
        table = curCell.closest('table');
        const row = curCell.parentElement;
        nextCell = row.cells[curCell.cellIndex + 1];

        startX = e.clientX;
        curWidth = curCell.offsetWidth;
        nextWidth = nextCell ? nextCell.offsetWidth : 0;
    });

    document.addEventListener('mousemove', (e) => {
        if (!activeHandle || !curCell || !nextCell || !table) return;
        e.preventDefault();

        const diff = e.clientX - startX;
        const minW = 25;
        const totalPairW = curWidth + nextWidth;

        let newCurW = Math.max(minW, curWidth + diff);
        let newNextW = totalPairW - newCurW;

        if (newNextW < minW) {
            newNextW = minW;
            newCurW = totalPairW - newNextW;
        }

        const tableW = table.offsetWidth;
        const curIdx = curCell.cellIndex;
        const nextIdx = nextCell.cellIndex;

        table.querySelectorAll('tr').forEach(r => {
            if (r.cells[curIdx]) r.cells[curIdx].style.width = ((newCurW / tableW) * 100).toFixed(2) + '%';
            if (r.cells[nextIdx]) r.cells[nextIdx].style.width = ((newNextW / tableW) * 100).toFixed(2) + '%';
        });
    });

    document.addEventListener('mouseup', () => {
        if (activeHandle) {
            activeHandle.classList.remove('resizing');
            activeHandle = null;
            curCell = null;
            nextCell = null;
            table = null;
            document.body.classList.remove('is-col-resizing');
        }
    });
}

function updateToolbarState() {
    [['bold','tb-bold'],['italic','tb-italic'],['underline','tb-under'],['strikeThrough','tb-strike'],
     ['justifyLeft','tb-left'],['justifyCenter','tb-center'],['justifyRight','tb-right'],['justifyFull','tb-justify']]
    .forEach(([cmd, id]) => {
        const btn = document.getElementById(id);
        if (btn) btn.classList.toggle('active', document.queryCommandState(cmd));
    });
    const sel = window.getSelection();
    if (sel && sel.rangeCount > 0) {
        let node = sel.getRangeAt(0).startContainer;
        if (node.nodeType === 3) node = node.parentNode;
        const ed = getActive();
        if (ed && ed.contains(node)) {
            const cs = window.getComputedStyle(node);
            const fontSel = document.getElementById('tb-font');
            if (fontSel) {
                const fn = cs.fontFamily.replace(/['"]/g,'');
                for (const opt of fontSel.options) {
                    if (fn.toLowerCase().includes(opt.value.toLowerCase())) { fontSel.value = opt.value; break; }
                }
            }
            const sizInp = document.getElementById('tb-size');
            if (sizInp) sizInp.value = Math.round(parseFloat(cs.fontSize) * 0.75) || 12;
        }
    }
}

document.addEventListener('selectionchange', () => {
    saveSelection();
    updateToolbarState();
});

function editTemplateState() {
    return {
        title: @json($template->title),
        differentFirstPageMargins: {{ $template->different_first_page_margins ? 'true' : 'false' }},
        margins: {
            top:    {{ $template->margin_top    ?? 25 }},
            bottom: {{ $template->margin_bottom ?? 25 }},
            left:   {{ $template->margin_left   ?? 20 }},
            right:  {{ $template->margin_right  ?? 20 }},
        },
        firstPageMargins: {
            top:    {{ $template->first_page_margin_top    ?? $template->margin_top    ?? 25 }},
            bottom: {{ $template->first_page_margin_bottom ?? $template->margin_bottom ?? 25 }},
            left:   {{ $template->first_page_margin_left   ?? $template->margin_left   ?? 20 }},
            right:  {{ $template->first_page_margin_right  ?? $template->margin_right  ?? 20 }},
        },
        variables: @json($template->variables ?? []),
        pages: 1,
        _reflowInProgress: false,
        _reflowTimer: null,
        varSearch: '',
        prebuiltVars: [
            { key: 'employee_name', label: 'Name' },
            { key: 'employee_first_name', label: 'First Name' },
            { key: 'employee_middle_name', label: 'Middle Name' },
            { key: 'employee_last_name', label: 'Last Name' },
            { key: 'employee_employee_code', label: 'Code' },
            { key: 'employee_department', label: 'Department' },
            { key: 'employee_designation', label: 'Designation' },
            { key: 'employee_gender', label: 'Gender' },
            { key: 'employee_join_date', label: 'Join Date' },
            // { key: 'employee_join_date_formatted', label: 'Join Date Formatted' },
            { key: 'employee_contact_number', label: 'Phone' },
            { key: 'employee_email', label: 'Email' },
            { key: 'employee_citizenship_number', label: 'Citizenship No' },
            { key: 'employee_citizenship_issue_date', label: 'Citizenship Issue Date' },
            { key: 'employee_citizenship_issue_place', label: 'Citizenship Issue Place' },
            { key: 'employee_ssid', label: 'SSID' },
            { key: 'employee_dob_ad', label: 'DOB (AD)' },
            { key: 'employee_dob_bs', label: 'DOB (BS)' },
            { key: 'employee_marital_status', label: 'Marital Status' },
            { key: 'employee_employee_status', label: 'Employee Status' },
            { key: 'employee_rank', label: 'Rank' },
            { key: 'employee_dp_rank', label: 'DP Rank' },
            { key: 'employee_tips_amount', label: 'Tips Amount' },
            { key: 'employee_tips_status', label: 'Tips Status' },
            { key: 'employee_point_value', label: 'Point Value' },
            { key: 'employee_tips_blank', label: 'Tips Blank' },
            { key: 'employee_publish_tips', label: 'Publish Tips' },
            { key: 'employee_tips_fixed', label: 'Tips Fixed' },
            { key: 'employee_his_her', label: 'his/her' },
            { key: 'employee_he_she', label: 'he/she' },
            { key: 'employee_him_her', label: 'him/her' },
            { key: 'employee_his_her_cap', label: 'His/Her' },
            { key: 'employee_he_she_cap', label: 'He/She' },
            { key: 'employee_him_her_cap', label: 'Him/Her' }
        ],
        get filteredPrebuiltVars() {
            if (!this.varSearch) return this.prebuiltVars;
            const q = this.varSearch.toLowerCase();
            return this.prebuiltVars.filter(v => 
                v.key.toLowerCase().includes(q) || v.label.toLowerCase().includes(q)
            );
        },

        addVariable()     { this.variables.push({ key: '', type: 'text', dummy: '', options: '' }); },
        removeVariable(i) { this.variables.splice(i, 1); },

        isCursorAtStartOfEditable(editable, range) {
            const preRange = document.createRange();
            preRange.selectNodeContents(editable);
            preRange.setEnd(range.startContainer, range.startOffset);
            const text = preRange.toString().trim();
            const clone = preRange.cloneContents();
            return text.length === 0 && !clone.querySelector('img, table, hr, p, div, h1, h2, h3, ul, ol');
        },

        isCursorAtEndOfEditable(editable, range) {
            const postRange = document.createRange();
            postRange.selectNodeContents(editable);
            postRange.setStart(range.endContainer, range.endOffset);
            const text = postRange.toString().trim();
            const clone = postRange.cloneContents();
            return text.length === 0 && !clone.querySelector('img, table, hr, p, div, h1, h2, h3, ul, ol');
        },

        insertVar(key) {
            const el = getActive();
            if (!el) return;
            el.focus();
            const ob = String.fromCharCode(123,123);
            const cb = String.fromCharCode(125,125);
            document.execCommand('insertText', false, ob + ' ' + key + ' ' + cb + ' ');
        },

        exec(cmd)             { exec(cmd); },
        execBlock(tag)        { execBlock(tag); },
        execFont(name)        { execFont(name); },
        execFontSize(pt)      { execFontSize(pt); },
        insertTable(r, c)      { insertTable(r, c); },
        applyLineHeight(v)    { if (v) applyToCurrentBlock('lineHeight', v); },
        applyLetterSpacing(v) { if (v !== '') applyToCurrentBlock('letterSpacing', v); },

        applyMarginsToAll() {
            document.querySelectorAll('.doc-page').forEach((p, idx) => {
                const pageNum = idx + 1;
                const isFirst = (pageNum === 1 && this.differentFirstPageMargins);
                const m = isFirst ? this.firstPageMargins : this.margins;
                p.style.setProperty('--mt', m.top    + 'mm');
                p.style.setProperty('--mb', m.bottom + 'mm');
                p.style.setProperty('--ml', m.left   + 'mm');
                p.style.setProperty('--mr', m.right  + 'mm');
            });
            this.reflowPages();
        },

        reflowPages() {
            if (this._reflowInProgress) return;
            this._reflowInProgress = true;

            const container = document.getElementById('pages-container');
            if (!container) {
                this._reflowInProgress = false;
                return;
            }

            // 1. Save selection/cursor position
            const sel = window.getSelection();
            let hasSelectionMarkers = false;
            if (sel && sel.rangeCount > 0) {
                const range = sel.getRangeAt(0);
                const activeEl = getActive();
                if (activeEl && activeEl.contains(range.startContainer)) {
                    const startMarker = document.createElement('span');
                    startMarker.id = 'cursor-start-marker';
                    startMarker.style.display = 'none';
                    
                    const endMarker = document.createElement('span');
                    endMarker.id = 'cursor-end-marker';
                    endMarker.style.display = 'none';
                    
                    try {
                        const endRange = range.cloneRange();
                        endRange.collapse(false);
                        endRange.insertNode(endMarker);
                        
                        const startRange = range.cloneRange();
                        startRange.collapse(true);
                        startRange.insertNode(startMarker);
                        
                        hasSelectionMarkers = true;
                    } catch(e) {
                        console.error('Error inserting selection markers:', e);
                        if (startMarker.parentNode) startMarker.remove();
                        if (endMarker.parentNode) endMarker.remove();
                    }
                }
            }

            // Promote all page break markers to the top level (direct children of .doc-page-content)
            const extractMarkerToTop = (mNode, root) => {
                let current = mNode;
                while (current.parentNode && current.parentNode !== root) {
                    const parent = current.parentNode;
                    const clone = parent.cloneNode(false);
                    let sibling = current.nextSibling;
                    while (sibling) {
                        const next = sibling.nextSibling;
                        clone.appendChild(sibling);
                        sibling = next;
                    }
                    if (parent.nextSibling) {
                        parent.parentNode.insertBefore(clone, parent.nextSibling);
                        parent.parentNode.insertBefore(current, clone);
                    } else {
                        parent.parentNode.appendChild(current);
                        parent.parentNode.appendChild(clone);
                    }
                    if (parent.childNodes.length === 0) {
                        parent.remove();
                    }
                }
            };

            const markers = Array.from(container.querySelectorAll('.page-break-marker'));
            markers.forEach(m => {
                const content = m.closest('.doc-page-content');
                if (content) {
                    extractMarkerToTop(m, content);
                }
            });

            // 2. Gather all child nodes of all pages into a temporary container
            const temp = document.createElement('div');
            const pages = Array.from(container.querySelectorAll('.doc-page'));
            pages.forEach(p => {
                const content = p.querySelector('.doc-page-content');
                if (content) {
                    while (content.firstChild) {
                        temp.appendChild(content.firstChild);
                    }
                }
            });

            // 3. Clear container
            container.innerHTML = '';

            // 4. Distribute nodes node-by-node
            let currentPageNum = 1;
            let currentPage = this.createPage(currentPageNum);
            let currentContent = currentPage.querySelector('.doc-page-content');

            let pageH = currentPage.clientHeight;
            let curMarginB = (currentPageNum === 1 && this.differentFirstPageMargins) 
                ? this.firstPageMargins.bottom 
                : this.margins.bottom;
            let marginB = curMarginB * (96 / 25.4);
            let usableBottom = pageH - marginB;

            const splitNode = (node, usableBottom, pageRect, forceFit = false) => {
                if (node.nodeType === 3) {
                    let low = 0;
                    let high = node.length;
                    let bestSplit = node.length;
                    while (low <= high) {
                        let mid = Math.floor((low + high) / 2);
                        const r = document.createRange();
                        r.setStart(node, 0);
                        r.setEnd(node, mid);
                        const rRect = r.getBoundingClientRect();
                        const rBottom = rRect.bottom - pageRect.top;
                        if (rBottom <= usableBottom) {
                            low = mid + 1;
                        } else {
                            bestSplit = mid;
                            high = mid - 1;
                        }
                    }
                    if (bestSplit === 0) {
                        if (forceFit && node.length > 0) {
                            bestSplit = 1;
                        } else {
                            return { fits: null, overflows: node };
                        }
                    }
                    if (bestSplit === node.length) {
                        return { fits: node, overflows: null };
                    } else {
                        // Avoid cutting words in half across pages: backtrack to the nearest preceding whitespace
                        const text = node.textContent;
                        let finalSplit = bestSplit;
                        const lastSpace = Math.max(text.lastIndexOf(' ', bestSplit), text.lastIndexOf('\u00a0', bestSplit));
                        if (lastSpace > 0 && (bestSplit - lastSpace) < 40) {
                            finalSplit = lastSpace + 1;
                        } else if (lastSpace === 0 && !forceFit) {
                            finalSplit = 0;
                        }

                        if (finalSplit === 0) {
                            if (forceFit && node.length > 0) {
                                finalSplit = 1;
                            } else {
                                return { fits: null, overflows: node };
                            }
                        }

                        const secondPart = node.splitText(finalSplit);
                        return { fits: node, overflows: secondPart };
                    }
                }

                if (node.nodeType === 1) {
                    const rect = node.getBoundingClientRect();
                    const nodeBottom = rect.bottom - pageRect.top;
                    const nodeTop = rect.top - pageRect.top;

                    if (nodeBottom <= usableBottom + 1) {
                        return { fits: node, overflows: null };
                    }
                    if (nodeTop >= usableBottom - 1 && !forceFit) {
                        return { fits: null, overflows: node };
                    }

                    if (node.tagName.toLowerCase() === 'tr') {
                        if (forceFit) {
                            return { fits: node, overflows: null };
                        } else {
                            return { fits: null, overflows: node };
                        }
                    }

                    const children = Array.from(node.childNodes);
                    if (children.length === 0) {
                        if (forceFit) {
                            return { fits: node, overflows: null };
                        } else {
                            return { fits: null, overflows: node };
                        }
                    }

                    const clone = node.cloneNode(false);
                    let targetParent = clone;
                    let sourceParent = node;
                    if (node.tagName.toLowerCase() === 'table') {
                        const tbody = node.querySelector('tbody');
                        if (tbody) {
                            const tbodyClone = tbody.cloneNode(false);
                            clone.appendChild(tbodyClone);
                            targetParent = tbodyClone;
                            sourceParent = tbody;
                        }
                    }

                    let hasFits = false;
                    let hasOverflows = false;
                    let childForceFit = forceFit;

                    for (let child of children) {
                        if (hasOverflows) {
                            targetParent.appendChild(child);
                            continue;
                        }

                        const result = splitNode(child, usableBottom, pageRect, childForceFit);
                        childForceFit = false;

                        if (result.fits) {
                            hasFits = true;
                        }
                        if (result.overflows) {
                            hasOverflows = true;
                            targetParent.appendChild(result.overflows);
                        }
                    }

                    return {
                        fits: hasFits ? node : null,
                        overflows: hasOverflows ? clone : null
                    };
                }

                return { fits: node, overflows: null };
            };

            while (temp.firstChild) {
                const node = temp.firstChild;

                // Handle manual page break marker
                if (node.nodeType === 1 && node.classList.contains('page-break-marker')) {
                    currentContent.appendChild(node);
                    currentPageNum++;
                    currentPage = this.createPage(currentPageNum);
                    currentContent = currentPage.querySelector('.doc-page-content');
                    
                    const newPageH = currentPage.clientHeight;
                    const curNewMarginB = (currentPageNum === 1 && this.differentFirstPageMargins) 
                        ? this.firstPageMargins.bottom 
                        : this.margins.bottom;
                    const newMarginB = curNewMarginB * (96 / 25.4);
                    usableBottom = newPageH - newMarginB;
                    continue;
                }

                currentContent.appendChild(node);

                let rect = null;
                if (node.nodeType === 1) {
                    rect = node.getBoundingClientRect();
                } else if (node.nodeType === 3 && node.textContent.trim()) {
                    const r = document.createRange();
                    r.selectNode(node);
                    rect = r.getBoundingClientRect();
                }

                if (rect) {
                    const pageRect = currentPage.getBoundingClientRect();
                    const nodeBottom = rect.bottom - pageRect.top;

                    if (nodeBottom > usableBottom) {
                        const isPageEmpty = (currentContent.childNodes.length === 1);
                        const result = splitNode(node, usableBottom, pageRect, isPageEmpty);

                        if (result.fits === null) {
                            node.remove();
                        }
                        if (result.overflows) {
                            if (temp.firstChild) {
                                temp.insertBefore(result.overflows, temp.firstChild);
                            } else {
                                temp.appendChild(result.overflows);
                            }
                        }

                        currentPageNum++;
                        currentPage = this.createPage(currentPageNum);
                        currentContent = currentPage.querySelector('.doc-page-content');

                        const newPageH = currentPage.clientHeight;
                        const curNewMarginB = (currentPageNum === 1 && this.differentFirstPageMargins) 
                            ? this.firstPageMargins.bottom 
                            : this.margins.bottom;
                        const newMarginB = curNewMarginB * (96 / 25.4);
                        usableBottom = newPageH - newMarginB;
                    }
                }
            }

            // Remove gap label on the first page
            const firstGap = container.firstElementChild;
            if (firstGap && !firstGap.classList.contains('doc-page')) {
                firstGap.remove();
            }

            // Update page count
            this.pages = container.querySelectorAll('.doc-page').length;

            // 5. Restore selection position
            if (hasSelectionMarkers) {
                const startMarker = document.getElementById('cursor-start-marker');
                const endMarker = document.getElementById('cursor-end-marker');
                if (startMarker && endMarker) {
                    const parent = startMarker.parentNode;
                    const range = document.createRange();
                    
                    range.setStartAfter(startMarker);
                    range.setEndBefore(endMarker);
                    
                    const sel = window.getSelection();
                    sel.removeAllRanges();
                    sel.addRange(range);
                    
                    startMarker.remove();
                    endMarker.remove();
                    
                    if (parent) {
                        parent.normalize();
                        const root = getEditorRoot();
                        if (root) root.focus();
                    }
                    
                    const endParent = endMarker.parentNode;
                    if (endParent && endParent !== parent) {
                        endParent.normalize();
                    }
                }
            }

            this._reflowInProgress = false;
        },

        createPage(explicitNum) {
            const container = document.getElementById('pages-container');
            const num = explicitNum || (container.querySelectorAll('.doc-page').length + 1);
            const isFirst = (num === 1 && this.differentFirstPageMargins);
            const m = isFirst ? this.firstPageMargins : this.margins;

            // Gap label
            const gap = document.createElement('div');
            gap.className = 'no-print flex items-center justify-center';
            gap.contentEditable = 'false';
            gap.innerHTML = '<span class="page-gap-label">— Page ' + num + ' —</span>';

            const page = document.createElement('div');
            page.className = 'doc-page';
            page.style.setProperty('--mt', m.top    + 'mm');
            page.style.setProperty('--mb', m.bottom + 'mm');
            page.style.setProperty('--ml', m.left   + 'mm');
            page.style.setProperty('--mr', m.right  + 'mm');

            page.innerHTML = '<span class="page-number-label no-print" contenteditable="false">Page ' + num + '</span>';

            const content = document.createElement('div');
            content.className = 'doc-page-content';
            content.spellcheck = true;
            if (num === 1) content.dataset.placeholder = 'Start typing your letter here…';
            this.bindPageContent(content, page);

            page.appendChild(content);
            container.appendChild(gap);
            container.appendChild(page);
            this.pages = container.querySelectorAll('.doc-page').length;
            return page;
        },

        bindPageContent(content, page) {
            // Managed globally at root pages-container level
        },

        insertPageBreak() {
            const el = getActive();
            if (!el) return;
            el.focus();
            
            const marker = document.createElement('div');
            marker.className = 'page-break-marker';
            marker.contentEditable = 'false';
            marker.title = 'Click to remove page break';
            
            const sel = window.getSelection();
            if (sel && sel.rangeCount > 0) {
                const range = sel.getRangeAt(0);
                range.insertNode(marker);
                
                const nextRange = document.createRange();
                nextRange.setStartAfter(marker);
                nextRange.collapse(true);
                sel.removeAllRanges();
                sel.addRange(nextRange);
            }
            
            this.reflowPages();
        },

        syncContent() {
            const rawPages = Array.from(document.querySelectorAll('.doc-page-content'));
            const pages = rawPages.map(p => {
                const clone = p.cloneNode(true);
                // Convert visual manual break markers to <!-- MANUAL_PAGE_BREAK --> comments
                clone.querySelectorAll('.page-break-marker').forEach(el => {
                    const comment = document.createComment(' MANUAL_PAGE_BREAK ');
                    el.parentNode.replaceChild(comment, el);
                });
                const cms = clone.querySelector('#cursor-start-marker');
                if (cms) cms.remove();
                const cme = clone.querySelector('#cursor-end-marker');
                if (cme) cme.remove();
                const cm = clone.querySelector('#cursor-marker');
                if (cm) cm.remove();
                // Trim trailing empty block nodes so no phantom blank page is created on reload
                let last = clone.lastChild;
                while (last) {
                    if (last.nodeType === 8) {
                        // Do not strip comment nodes (like MANUAL_PAGE_BREAK)
                        break;
                    }
                    if (last.nodeType === 3 && last.textContent.trim() === '') {
                        const prev = last.previousSibling;
                        clone.removeChild(last);
                        last = prev;
                        continue;
                    }
                    const tag = last.tagName ? last.tagName.toLowerCase() : '';
                    if (tag === 'br') { const prev = last.previousSibling; clone.removeChild(last); last = prev; continue; }
                    if ((tag === 'p' || tag === 'div') && (last.innerHTML.replace(/[\s\u00a0]/g, '') === '' || last.innerHTML === '<br>')) {
                        const prev = last.previousSibling;
                        clone.removeChild(last);
                        last = prev;
                        continue;
                    }
                    break;
                }
                return clone.innerHTML.replace(/\u200B/g, '');
            });
            
            let html = '';
            pages.forEach((pageHtml, idx) => {
                if (idx === 0) {
                    html = pageHtml;
                } else {
                    const prevPageHadManual = rawPages[idx - 1].querySelector('.page-break-marker') !== null;
                    if (prevPageHadManual) {
                        html += '\n' + pageHtml;
                    } else {
                        html += '\n<!-- PAGE_BREAK -->\n' + pageHtml;
                    }
                }
            });
            document.getElementById('content-hidden').value = html;
        },

        doSave() {
            this.syncContent();
            const form = document.getElementById('template-form');
            form.querySelectorAll('.dynamic-var-input').forEach(el => el.remove());
            this.variables.forEach((v, idx) => {
                const fields = {
                    key: v.key,
                    type: v.type,
                    dummy: v.dummy ?? '',
                    options: v.options ?? ''
                };
                Object.entries(fields).forEach(([fName, fVal]) => {
                    const inp = document.createElement('input');
                    inp.type = 'hidden';
                    inp.className = 'dynamic-var-input';
                    inp.name = `variables[${idx}][${fName}]`;
                    inp.value = fVal;
                    form.appendChild(inp);
                });
            });
            form.submit();
        },

        init() {
            this.$nextTick(() => {
                const rawContent = @json($template->content ?? '');
                // 1. Only convert deliberate MANUAL page breaks into visual break markers
                let converted = rawContent.replace(/<!--\s*MANUAL_PAGE_BREAK\s*-->/gi, '<div class="page-break-marker" contenteditable="false" title="Click to remove page break"></div>');
                // 2. Strip automatic soft page breaks completely so they never become markers or force extra pages
                converted = converted.replace(/<!--\s*PAGE_BREAK\s*-->/gi, '');
                
                const page = this.createPage(1);
                const content = page.querySelector('.doc-page-content');
                content.innerHTML = converted || '<p><br></p>';
                
                this.reflowPages();
                
                const root = getEditorRoot();
                if (root) root.focus();

                // Register editing and boundary events at the root pages-container level
                const container = document.getElementById('pages-container');
                const self = this;

                // Click to remove manual page break
                container.addEventListener('click', (e) => {
                    const marker = e.target.closest('.page-break-marker');
                    if (marker) {
                        marker.remove();
                        self.reflowPages();
                    }
                });

                container.addEventListener('keydown', (e) => {
                    if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') { e.preventDefault(); self.insertPageBreak(); return; }
                    if (e.key === 'Tab') { e.preventDefault(); document.execCommand(e.shiftKey ? 'outdent' : 'indent'); return; }

                    const sel = window.getSelection();
                    if (sel && sel.isCollapsed && sel.rangeCount > 0) {
                        const range = sel.getRangeAt(0);
                        const node = range.startContainer;
                        const content = node.nodeType === Node.ELEMENT_NODE ? node.closest('.doc-page-content') : node.parentNode?.closest('.doc-page-content');
                        if (content) {
                            if (e.key === 'Backspace') {
                                if (self.isCursorAtStartOfEditable(content, range)) {
                                    const pages = Array.from(document.querySelectorAll('.doc-page-content'));
                                    const idx = pages.indexOf(content);
                                    if (idx > 0) {
                                        e.preventDefault();
                                        const prevPage = pages[idx - 1];
                                        const marker = document.createElement('span');
                                        marker.id = 'cursor-start-marker';
                                        prevPage.appendChild(marker);
                                        while (content.firstChild) {
                                            prevPage.appendChild(content.firstChild);
                                        }
                                        self.reflowPages();
                                        const m = document.getElementById('cursor-start-marker');
                                        if (m) {
                                            const newRange = document.createRange();
                                            newRange.setStartAfter(m);
                                            newRange.collapse(true);
                                            sel.removeAllRanges();
                                            sel.addRange(newRange);
                                            m.remove();
                                        }
                                    }
                                }
                            }
                            if (e.key === 'Delete') {
                                if (self.isCursorAtEndOfEditable(content, range)) {
                                    const pages = Array.from(document.querySelectorAll('.doc-page-content'));
                                    const idx = pages.indexOf(content);
                                    if (idx < pages.length - 1) {
                                        e.preventDefault();
                                        const nextPage = pages[idx + 1];
                                        const marker = document.createElement('span');
                                        marker.id = 'cursor-start-marker';
                                        range.insertNode(marker);
                                        while (nextPage.firstChild) {
                                            content.appendChild(nextPage.firstChild);
                                        }
                                        self.reflowPages();
                                        const m = document.getElementById('cursor-start-marker');
                                        if (m) {
                                            const newRange = document.createRange();
                                            newRange.setStartAfter(m);
                                            newRange.collapse(true);
                                            sel.removeAllRanges();
                                            sel.addRange(newRange);
                                            m.remove();
                                        }
                                    }
                                }
                            }
                        }
                    }
                });

                container.addEventListener('input', () => {
                    updateToolbarState();
                    clearTimeout(self._reflowTimer);
                    self._reflowTimer = setTimeout(() => self.reflowPages(), 150);
                });

                container.addEventListener('mouseup', updateToolbarState);
                container.addEventListener('keyup', updateToolbarState);

                // Table resize listeners and handles
                setupTableResizeListener(container);
                initTableResizeHandles();
                initTableToolbarTooltips();

                // Table context menu & multi-cell selection
                container.addEventListener('mousedown', (e) => {
                    if (e.target.closest('.col-resize-handle')) return;
                    const cell = e.target.closest('td, th');
                    if (cell && container.contains(cell)) {
                        initTableResizeHandles();
                        if (e.shiftKey && _lastAnchorCell && _lastAnchorCell.closest('table') === cell.closest('table')) {
                            e.preventDefault();
                            selectCellRange(_lastAnchorCell, cell);
                            showTableMenuForSelection();
                            return;
                        }
                        if (e.ctrlKey || e.metaKey) {
                            e.preventDefault();
                            if (_selectedCells.has(cell)) {
                                cell.classList.remove('cell-selected');
                                _selectedCells.delete(cell);
                            } else {
                                cell.classList.add('cell-selected');
                                _selectedCells.add(cell);
                                _lastAnchorCell = cell;
                            }
                            if (_selectedCells.size > 0) {
                                showTableMenuForSelection();
                            } else {
                                hideTableMenu();
                            }
                            return;
                        }
                        _isCellDragging = true;
                        _hasDragged = false;
                        _dragStartCell = cell;
                        _lastAnchorCell = cell;
                    }
                });

                const handleCellHover = (target) => {
                    if (!_isCellDragging || !_dragStartCell) return;
                    const cell = target ? target.closest('td, th') : null;
                    if (cell && cell.closest('table') === _dragStartCell.closest('table')) {
                        if (cell !== _dragStartCell || _hasDragged) {
                            _hasDragged = true;
                            document.body.style.userSelect = 'none';
                            window.getSelection()?.removeAllRanges();
                            selectCellRange(_dragStartCell, cell);
                        }
                    }
                };

                container.addEventListener('mouseover', (e) => {
                    handleCellHover(e.target);
                });

                document.addEventListener('mousemove', (e) => {
                    if (_isCellDragging && _dragStartCell) {
                        const el = document.elementFromPoint(e.clientX, e.clientY);
                        handleCellHover(el);
                    }
                });

                document.addEventListener('mouseup', (e) => {
                    if (_isCellDragging) {
                        _isCellDragging = false;
                        document.body.style.userSelect = '';
                        if (_hasDragged) {
                            _ignoreNextClick = true;
                            setTimeout(() => { _ignoreNextClick = false; }, 250);
                            if (_selectedCells.size > 0) {
                                showTableMenuForSelection();
                            }
                        }
                    }
                });

                container.addEventListener('click', (e) => {
                    if (_ignoreNextClick) {
                        _ignoreNextClick = false;
                        e.preventDefault();
                        e.stopPropagation();
                        return;
                    }
                    const cell = e.target.closest('td, th');
                    if (cell && container.contains(cell)) {
                        initTableResizeHandles();
                        if (!e.ctrlKey && !e.metaKey && !e.shiftKey) {
                            clearCellSelection();
                            cell.classList.add('cell-selected');
                            _selectedCells.add(cell);
                            _lastAnchorCell = cell;
                            showTableMenuForSelection();
                        }
                    } else if (!e.target.closest('table') && !e.target.closest('#table-ctx-menu') && !e.target.closest('#ctx-swatch-popover')) {
                        clearCellSelection();
                        hideTableMenu();
                    }
                });

                document.addEventListener('mousedown', (e) => {
                    if (e.target.closest('#pages-container') || e.target.closest('#table-ctx-menu') || e.target.closest('#ctx-swatch-popover') || e.target.closest('#toolbar') || e.target.closest('.color-picker-input')) {
                        return;
                    }
                    clearCellSelection();
                    hideTableMenu();
                });

                document.addEventListener('keydown', (e) => {
                    if (e.key === 'Escape') {
                        clearCellSelection();
                        hideTableMenu();
                    }
                });

                // Table grid picker init
                const tgp = document.getElementById('table-grid-picker');
                const tgpLabel = document.getElementById('tgp-label');
                if (tgp) {
                    for (let r = 1; r <= 6; r++) {
                        for (let c = 1; c <= 6; c++) {
                            const cell = document.createElement('div');
                            cell.className = 'tgp-cell';
                            cell.dataset.r = r;
                            cell.dataset.c = c;
                            cell.addEventListener('mouseover', () => {
                                tgp.querySelectorAll('.tgp-cell').forEach(el => {
                                    el.classList.toggle('tgp-hover', +el.dataset.r <= r && +el.dataset.c <= c);
                                });
                                if (tgpLabel) tgpLabel.textContent = r + ' × ' + c;
                            });
                            cell.addEventListener('click', () => {
                                restoreSelection();
                                insertTable(r, c);
                                const wrap = cell.closest('[x-data]');
                                if (wrap && wrap._x_dataStack) wrap._x_dataStack[0].tgOpen = false;
                            });
                            tgp.appendChild(cell);
                        }
                    }
                    tgp.addEventListener('mouseleave', () => {
                        tgp.querySelectorAll('.tgp-cell').forEach(el => el.classList.remove('tgp-hover'));
                        if (tgpLabel) tgpLabel.textContent = '1 × 1';
                    });
                }
            });
        }
    };
}
</script>
@endsection
