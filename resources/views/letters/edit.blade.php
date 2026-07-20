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
.doc-page-content table { width: 100%; border-collapse: collapse; margin: 10pt 0; }
.doc-page-content td, .doc-page-content th {
    border: 1px solid #cbd5e1;
    padding: 6px 10px;
    min-width: 30px;
    vertical-align: top;
}
.dark .doc-page-content td, .dark .doc-page-content th { border-color: #3f3f46; }

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
<div x-data="editTemplateState()" class="flex-1 flex flex-col overflow-hidden" x-init="init()">

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
        <button type="button" @mousedown.prevent="insertTable()"               class="p-1.5 text-slate-600 dark:text-zinc-300 hover:bg-slate-100 dark:hover:bg-zinc-800 rounded-lg cursor-pointer" title="Table"><i class="fa-solid fa-table text-sm"></i></button>
        <button type="button" @mousedown.prevent="exec('insertHorizontalRule')" class="p-1.5 text-slate-600 dark:text-zinc-300 hover:bg-slate-100 dark:hover:bg-zinc-800 rounded-lg cursor-pointer" title="HR"><i class="fa-solid fa-minus text-sm"></i></button>

        <button type="button" @mousedown.prevent="insertPageBreak()"
                class="flex items-center gap-1 px-2.5 py-1 bg-amber-500/10 hover:bg-amber-500/20 border border-amber-400/30 text-amber-700 dark:text-amber-400 rounded-lg text-xs font-bold cursor-pointer transition-all"
                title="Page Break (Ctrl+Enter)">
            <i class="fa-solid fa-file-circle-plus text-sm"></i> Page Break
        </button>

        <button type="button" @mousedown.prevent="exec('removeFormat')" class="p-1.5 text-slate-600 dark:text-zinc-300 hover:bg-slate-100 dark:hover:bg-zinc-800 rounded-lg cursor-pointer" title="Clear Format"><i class="fa-solid fa-eraser text-sm"></i></button>
    </div>

    <!-- ── 3-PANE LAYOUT ── -->
    <div class="flex-1 flex overflow-hidden">

        <!-- LEFT: Variables panel -->
        <aside class="no-print w-72 bg-white dark:bg-zinc-900 border-r border-slate-200 dark:border-zinc-800 flex flex-col overflow-hidden shrink-0">
            <div class="p-4 border-b border-slate-200 dark:border-zinc-800 shrink-0">
                <h3 class="text-sm font-bold text-slate-800 dark:text-zinc-200 uppercase tracking-wider">Placeholders</h3>
                <p class="text-xs text-slate-400 mt-1">Click a pill to insert at cursor.</p>
                <p class="text-xs text-amber-600 dark:text-amber-400 mt-1.5 font-semibold"><i class="fa-solid fa-keyboard mr-1"></i>Ctrl+Enter → Page Break</p>
            </div>
            <div class="flex-1 overflow-y-auto p-4 space-y-5">
                <div class="space-y-2">
                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-widest flex items-center gap-1.5">
                        <i class="fa-solid fa-id-card text-amber-500"></i> Employee
                    </h4>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach([
                            'employee_name'               => 'Name',
                            'employee_employee_code'      => 'Code',
                            'employee_department'         => 'Department',
                            'employee_designation'        => 'Designation',
                            'employee_gender'             => 'Gender',
                            'employee_join_date'          => 'Join Date',
                            'employee_contact_number'     => 'Phone',
                            'employee_email'              => 'Email',
                            'employee_citizenship_number' => 'Citizenship No',
                            'employee_ssid'               => 'SSID',
                            'employee_dob_ad'             => 'DOB (AD)',
                            'employee_marital_status'     => 'Marital Status',
                            'employee_tips_amount'        => 'Tips Amount',
                            'employee_tips_status'        => 'Tips Status',
                            'employee_his_her'            => 'his/her',
                            'employee_he_she'             => 'he/she',
                            'employee_his_her_cap'        => 'His/Her',
                            'employee_he_she_cap'         => 'He/She',
                        ] as $key => $label)
                        <button type="button" @mousedown.prevent="insertVar('{!! $key !!}')"
                                class="px-2.5 py-1 bg-amber-500/10 hover:bg-amber-500/25 text-amber-700 dark:text-amber-400 rounded-md text-xs font-semibold font-mono border border-amber-500/20 active:scale-95 cursor-pointer transition-all">
                            {{ $label }}
                        </button>
                        @endforeach
                    </div>
                </div>

                <div class="space-y-3 border-t border-slate-100 dark:border-zinc-800 pt-4">
                    <div class="flex items-center justify-between">
                        <h4 class="text-xs font-bold text-slate-400 uppercase tracking-widest flex items-center gap-1.5">
                            <i class="fa-solid fa-sliders text-amber-500"></i> Custom
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
        </aside>

        <!-- CENTER: Pages -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <div id="editor-scroll">
                <div id="pages-container" class="w-full flex flex-col items-center gap-6">
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

        <!-- RIGHT: Settings -->
        <aside class="no-print w-80 bg-white dark:bg-zinc-900 border-l border-slate-200 dark:border-zinc-800 flex flex-col overflow-hidden shrink-0">
            <form id="template-form" action="{{ route('letters.update', $template->id) }}" method="POST" class="flex flex-col h-full" @submit.prevent="doSave($event)">
                @csrf
                @method('PUT')
                <input type="hidden" name="content" id="content-hidden">

                <div class="flex-1 overflow-y-auto p-5 space-y-5">
                    <div>
                        <h3 class="text-sm font-bold text-slate-800 dark:text-zinc-200 uppercase tracking-wider">Document Settings</h3>
                        <p class="text-xs text-slate-400 mt-1">Title and page margins.</p>
                    </div>

                    @if($errors->any())
                    <div class="p-3 bg-rose-50 dark:bg-rose-950/20 border border-rose-200 dark:border-rose-800 rounded-xl text-rose-700 dark:text-rose-400 text-xs space-y-1">
                        @foreach($errors->all() as $e)<p>• {{ $e }}</p>@endforeach
                    </div>
                    @endif

                    <div class="space-y-1.5">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-zinc-400">Template Title *</label>
                        <input type="text" name="title" x-model="title" required
                               class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-950 rounded-xl text-sm font-semibold text-slate-900 dark:text-zinc-100 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 outline-none transition-all"
                               placeholder="e.g. Appointment Letter">
                    </div>

                    <div class="p-4 bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 rounded-2xl space-y-3">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-zinc-400">Page Margins (mm)</label>
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

                    <div class="p-3 bg-amber-50 dark:bg-amber-950/20 border border-amber-200 dark:border-amber-800/50 rounded-xl text-center">
                        <div class="text-2xl font-black text-amber-600" x-text="pages"></div>
                        <div class="text-xs font-semibold text-amber-700 dark:text-amber-400" x-text="pages === 1 ? 'Page' : 'Pages'"></div>
                    </div>
                </div>

                <div class="p-4 border-t border-slate-200 dark:border-zinc-800 space-y-2 shrink-0">
                    <button type="submit" class="w-full py-2.5 px-4 bg-amber-500 hover:bg-amber-600 text-white font-bold text-sm rounded-xl shadow-md shadow-amber-500/20 transition-all cursor-pointer">
                        <i class="fa-solid fa-floppy-disk mr-1.5"></i> Update Template
                    </button>
                    <a href="{{ route('letters.index') }}" class="block text-center py-2 text-sm text-slate-500 dark:text-zinc-400 hover:text-slate-800 dark:hover:text-zinc-200 transition-colors">Cancel</a>
                </div>
            </form>
        </aside>

    </div>
</div>
@endsection

@section('scripts')
<script>
let _activeContent = null;
let savedRange = null;

function getActive() {
    return _activeContent || document.querySelector('.doc-page-content');
}

function saveSelection() {
    const sel = window.getSelection();
    if (sel && sel.rangeCount > 0) {
        const active = getActive();
        if (active && active.contains(sel.getRangeAt(0).commonAncestorContainer)) {
            savedRange = sel.getRangeAt(0).cloneRange();
        }
    }
}

function restoreSelection() {
    if (savedRange) {
        const sel = window.getSelection();
        sel.removeAllRanges();
        sel.addRange(savedRange);
    }
}

function exec(cmd, val = null) {
    restoreSelection();
    const el = getActive();
    if (el) { el.focus(); document.execCommand(cmd, false, val); }
}

function execBlock(tag) {
    restoreSelection();
    const el = getActive();
    if (el) { el.focus(); document.execCommand('formatBlock', false, '<' + tag + '>'); }
}

function execFont(name) {
    restoreSelection();
    const el = getActive();
    if (el) { el.focus(); document.execCommand('fontName', false, name); }
}

function execFontSize(pt) {
    restoreSelection();
    const el = getActive();
    if (!el) return;
    el.focus();
    document.execCommand('fontSize', false, '7');
    el.querySelectorAll('[size="7"]').forEach(n => {
        n.removeAttribute('size');
        n.style.fontSize = pt + 'pt';
    });
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
    const el = getActive(); if (el) { el.focus(); document.execCommand('foreColor', false, color); }
}

function execHighlight(color) {
    restoreSelection();
    const bar = document.getElementById('hl-bar');
    if (bar) bar.style.background = color;
    const el = getActive(); if (el) { el.focus(); document.execCommand('hiliteColor', false, color); }
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

    if (sel.rangeCount === 0) { apply(el); return; }
    const range = sel.getRangeAt(0);
    if (sel.isCollapsed) {
        apply(range.startContainer);
    } else {
        el.querySelectorAll('p,div,h1,h2,h3,li,td,th').forEach(block => {
            if (sel.containsNode(block, true)) block.style[prop] = value;
        });
    }
}

function insertTable() {
    const el = getActive();
    if (!el) return;
    el.focus();
    let html = '<table><tbody>';
    for (let r = 0; r < 3; r++) {
        html += '<tr>';
        for (let c = 0; c < 3; c++) html += '<td>&nbsp;</td>';
        html += '</tr>';
    }
    html += '</tbody></table><p><br></p>';
    document.execCommand('insertHTML', false, html);
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
        margins: {
            top:    {{ $template->margin_top    ?? 25 }},
            bottom: {{ $template->margin_bottom ?? 25 }},
            left:   {{ $template->margin_left   ?? 20 }},
            right:  {{ $template->margin_right  ?? 20 }},
        },
        variables: @json($template->variables ?? []),
        pages: 1,
        _reflowInProgress: false,
        _reflowTimer: null,

        addVariable()     { this.variables.push({ key: '', type: 'text', dummy: '', options: '' }); },
        removeVariable(i) { this.variables.splice(i, 1); },

        insertVar(key) {
            const el = getActive();
            if (!el) return;
            el.focus();
            const ob = String.fromCharCode(123,123);
            const cb = String.fromCharCode(125,125);
            document.execCommand('insertText', false, ob + ' ' + key + ' ' + cb);
        },

        exec(cmd)             { exec(cmd); },
        execBlock(tag)        { execBlock(tag); },
        execFont(name)        { execFont(name); },
        execFontSize(pt)      { execFontSize(pt); },
        insertTable()         { insertTable(); },
        applyLineHeight(v)    { if (v) applyToCurrentBlock('lineHeight', v); },
        applyLetterSpacing(v) { if (v !== '') applyToCurrentBlock('letterSpacing', v); },

        applyMarginsToAll() {
            const m = this.margins;
            document.querySelectorAll('.doc-page').forEach(p => {
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
            let currentPage = this.createPage(1);
            let currentContent = currentPage.querySelector('.doc-page-content');

            let pageH = currentPage.clientHeight;
            let marginB = this.margins.bottom * (96 / 25.4);
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
                        const secondPart = node.splitText(bestSplit);
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
                    currentPage = this.createPage();
                    currentContent = currentPage.querySelector('.doc-page-content');
                    
                    const newPageH = currentPage.clientHeight;
                    const newMarginB = this.margins.bottom * (96 / 25.4);
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

                        currentPage = this.createPage();
                        currentContent = currentPage.querySelector('.doc-page-content');

                        const newPageH = currentPage.clientHeight;
                        const newMarginB = this.margins.bottom * (96 / 25.4);
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
                        const activeContent = parent.closest('.doc-page-content');
                        if (activeContent) {
                            _activeContent = activeContent;
                            activeContent.focus();
                        }
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
            const m = this.margins;
            const num = explicitNum || (container.querySelectorAll('.doc-page').length + 1);

            // Gap label
            const gap = document.createElement('div');
            gap.className = 'no-print flex items-center justify-center';
            gap.innerHTML = '<span class="page-gap-label">— Page ' + num + ' —</span>';

            const page = document.createElement('div');
            page.className = 'doc-page';
            page.style.setProperty('--mt', m.top    + 'mm');
            page.style.setProperty('--mb', m.bottom + 'mm');
            page.style.setProperty('--ml', m.left   + 'mm');
            page.style.setProperty('--mr', m.right  + 'mm');

            page.innerHTML = '<span class="page-number-label no-print">Page ' + num + '</span>';

            const content = document.createElement('div');
            content.className = 'doc-page-content';
            content.contentEditable = 'true';
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
            const self = this;
            content.addEventListener('focus', () => { _activeContent = content; });
            content.addEventListener('keydown', (e) => {
                if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') { e.preventDefault(); self.insertPageBreak(); return; }
                if (e.key === 'Tab') { e.preventDefault(); document.execCommand(e.shiftKey ? 'outdent' : 'indent'); }
            });
            content.addEventListener('input', () => {
                updateToolbarState();
                clearTimeout(self._reflowTimer);
                self._reflowTimer = setTimeout(() => self.reflowPages(), 150);
            });
            content.addEventListener('mouseup', updateToolbarState);
            content.addEventListener('keyup', updateToolbarState);
        },

        insertPageBreak() {
            const el = getActive();
            if (!el) return;
            el.focus();
            
            const marker = document.createElement('div');
            marker.className = 'page-break-marker';
            marker.contentEditable = 'false';
            
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
            const pages = Array.from(document.querySelectorAll('.doc-page-content')).map(p => {
                const clone = p.cloneNode(true);
                clone.querySelectorAll('.page-break-marker').forEach(el => el.remove());
                const cms = clone.querySelector('#cursor-start-marker');
                if (cms) cms.remove();
                const cme = clone.querySelector('#cursor-end-marker');
                if (cme) cme.remove();
                const cm = clone.querySelector('#cursor-marker');
                if (cm) cm.remove();
                return clone.innerHTML;
            });
            
            const ob = '<!-- PAGE_BREAK -->';
            const html = pages.join('\n' + ob + '\n');
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
                const converted = rawContent.replace(/<!--\s*PAGE_BREAK\s*-->/gi, '<div class="page-break-marker" contenteditable="false"></div>');
                
                const page = this.createPage(1);
                const content = page.querySelector('.doc-page-content');
                content.innerHTML = converted || '<p><br></p>';
                
                this.reflowPages();
                
                const first = document.querySelector('.doc-page-content');
                if (first) { first.focus(); _activeContent = first; }
            });
        }
    };
}
</script>
@endsection
