@extends('letters.layout')

@section('title', 'Edit Generated Letter - ' . $letter->employee_name)

@section('styles')
<style>
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
    min-height: 297mm;
    overflow: hidden;
}
.dark .doc-page {
    background: #1c1c1e;
    box-shadow: 0 4px 24px rgba(0,0,0,.5), 0 0 0 1px rgba(255,255,255,.06);
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

.tb-btn.active {
    background: #fef3c7 !important;
    color: #b45309 !important;
}
.dark .tb-btn.active {
    background: rgba(245,158,11,.18) !important;
    color: #fbbf24 !important;
}
</style>
@endsection

@section('content')
@php
    $m = $letter->margins ?? ['top' => 25, 'bottom' => 25, 'left' => 20, 'right' => 20];
    $emp = $letter->employee;
@endphp
<div x-data="editGeneratedLetterState()" class="flex-1 flex flex-col overflow-hidden">

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

        <div class="h-5 w-px bg-slate-200 dark:bg-zinc-700 mx-0.5"></div>

        <button type="button" @mousedown.prevent="exec('justifyLeft')"   id="tb-left"    class="tb-btn p-1.5 text-slate-600 dark:text-zinc-300 hover:bg-slate-100 dark:hover:bg-zinc-800 rounded-lg cursor-pointer"><i class="fa-solid fa-align-left text-sm"></i></button>
        <button type="button" @mousedown.prevent="exec('justifyCenter')" id="tb-center"  class="tb-btn p-1.5 text-slate-600 dark:text-zinc-300 hover:bg-slate-100 dark:hover:bg-zinc-800 rounded-lg cursor-pointer"><i class="fa-solid fa-align-center text-sm"></i></button>
        <button type="button" @mousedown.prevent="exec('justifyRight')"  id="tb-right"   class="tb-btn p-1.5 text-slate-600 dark:text-zinc-300 hover:bg-slate-100 dark:hover:bg-zinc-800 rounded-lg cursor-pointer"><i class="fa-solid fa-align-right text-sm"></i></button>
        <button type="button" @mousedown.prevent="exec('justifyFull')"   id="tb-justify" class="tb-btn p-1.5 text-slate-600 dark:text-zinc-300 hover:bg-slate-100 dark:hover:bg-zinc-800 rounded-lg cursor-pointer"><i class="fa-solid fa-align-justify text-sm"></i></button>

        <div class="h-5 w-px bg-slate-200 dark:bg-zinc-700 mx-0.5"></div>

        <button type="button" @mousedown.prevent="exec('insertUnorderedList')" class="p-1.5 text-slate-600 dark:text-zinc-300 hover:bg-slate-100 dark:hover:bg-zinc-800 rounded-lg cursor-pointer" title="Bullet List"><i class="fa-solid fa-list-ul text-sm"></i></button>
        <button type="button" @mousedown.prevent="exec('insertOrderedList')"   class="p-1.5 text-slate-600 dark:text-zinc-300 hover:bg-slate-100 dark:hover:bg-zinc-800 rounded-lg cursor-pointer" title="Numbered List"><i class="fa-solid fa-list-ol text-sm"></i></button>
        <button type="button" @mousedown.prevent="insertTable()"               class="p-1.5 text-slate-600 dark:text-zinc-300 hover:bg-slate-100 dark:hover:bg-zinc-800 rounded-lg cursor-pointer" title="Table"><i class="fa-solid fa-table text-sm"></i></button>
        <button type="button" @mousedown.prevent="exec('removeFormat')" class="p-1.5 text-slate-600 dark:text-zinc-300 hover:bg-slate-100 dark:hover:bg-zinc-800 rounded-lg cursor-pointer" title="Clear Format"><i class="fa-solid fa-eraser text-sm"></i></button>
    </div>

    <!-- Form wraps studio layout -->
    <form id="letter-edit-form" action="{{ route('letters.history.update', $letter->id) }}" method="POST" class="flex-1 flex flex-row overflow-hidden" @submit="prepareSubmit($event)">
        @csrf
        @method('PUT')
        <input type="hidden" name="content" id="content-hidden">

        <!-- LEFT Sidebar: Predefined & Custom Variables Insertion -->
        <aside class="no-print w-80 bg-white dark:bg-zinc-900 border-r border-slate-200 dark:border-zinc-800 flex flex-col overflow-hidden shrink-0 shadow-sm z-20">
            <!-- Header & Search -->
            <div class="p-5 border-b border-slate-200 dark:border-zinc-800 shrink-0 space-y-3">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-bold text-slate-800 dark:text-zinc-200 uppercase tracking-wider">Insert Variables</h3>
                    <span class="text-[10px] text-amber-600 dark:text-amber-400 font-semibold">Click to insert value</span>
                </div>
                <input type="text" x-model="varSearch" placeholder="Search variables..."
                       class="w-full px-2.5 py-1.5 border border-slate-200 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-950 rounded-lg text-xs text-slate-850 dark:text-zinc-200 focus:outline-none focus:border-amber-500 transition-all outline-none">
            </div>

            <!-- Scrollable Variables Section -->
            <div class="flex-1 overflow-y-auto p-5 space-y-5 min-h-0">
                
                <!-- Predefined Employee Variables section -->
                <div class="space-y-3">
                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-widest flex items-center gap-1.5">
                        <i class="fa-solid fa-id-card text-amber-500"></i> Predefined Employee Fields
                    </h4>
                    <p class="text-[11px] text-slate-400">Click to insert employee's evaluated value into letter:</p>
                    
                    <div class="flex flex-wrap gap-1.5">
                        <template x-for="pv in filteredPrebuiltVars" :key="pv.key">
                            <button type="button" 
                                    @mousedown.prevent="getEvaluatedPredefinedValue(pv.key) && insertPredefinedValue(pv.key)"
                                    :disabled="!getEvaluatedPredefinedValue(pv.key)"
                                    :class="getEvaluatedPredefinedValue(pv.key) 
                                        ? 'bg-amber-500/10 hover:bg-amber-500/25 text-amber-700 dark:text-amber-400 border-amber-500/20 cursor-pointer active:scale-95' 
                                        : 'opacity-40 cursor-not-allowed bg-slate-100 dark:bg-zinc-800 text-slate-400 dark:text-zinc-500 border-slate-200/50 dark:border-zinc-800'"
                                    class="px-2.5 py-1 rounded-md text-xs font-semibold border transition-all flex items-center gap-1"
                                    :title="getEvaluatedPredefinedValue(pv.key) ? ('Insert value: ' + getEvaluatedPredefinedValue(pv.key)) : (pv.label + ' — No value available for this employee')">
                                <span x-text="pv.label"></span>
                                <template x-if="getEvaluatedPredefinedValue(pv.key)">
                                    <i class="fa-solid fa-plus text-[10px] opacity-60"></i>
                                </template>
                                <template x-if="!getEvaluatedPredefinedValue(pv.key)">
                                    <i class="fa-solid fa-ban text-[10px] opacity-40"></i>
                                </template>
                            </button>
                        </template>
                    </div>
                </div>

                <!-- Custom Variables section -->
                <div class="space-y-3 border-t border-slate-100 dark:border-zinc-800 pt-4">
                    <h4 class="text-xs font-bold text-slate-400 uppercase tracking-widest flex items-center gap-1.5">
                        <i class="fa-solid fa-sliders text-amber-500"></i> Custom Variables
                    </h4>
                    
                    <template x-if="Object.keys(customValues).length === 0">
                        <div class="text-xs text-slate-400 italic">No custom variables defined for this letter.</div>
                    </template>

                    <div class="space-y-3">
                        <template x-for="(val, key) in customValues" :key="key">
                            <div class="p-3 bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 rounded-xl space-y-2">
                                <div class="flex items-center justify-between">
                                    <label class="block text-[10px] font-bold uppercase text-slate-400 tracking-wider" x-text="formatLabel(key)"></label>
                                    <button type="button" @mousedown.prevent="insertCustomValue(key)"
                                            class="px-2 py-0.5 bg-amber-500 text-white text-[10px] font-bold rounded hover:bg-amber-600 cursor-pointer active:scale-95 transition-all">
                                        <i class="fa-solid fa-plus mr-0.5"></i> Insert Value
                                    </button>
                                </div>
                                <input type="text" :name="`custom_values[${key}]`" x-model="customValues[key]" 
                                       class="w-full px-2.5 py-1.5 border border-slate-200 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-900 text-xs text-slate-800 dark:text-zinc-200 focus:border-amber-500 outline-none">
                            </div>
                        </template>
                    </div>
                </div>

            </div>
        </aside>

        <!-- CENTER Pane: Printable Page Editor -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <div id="editor-scroll">
                <div class="doc-page"
                     :style="`
                         --mt: ${margins.top}mm;
                         --mb: ${margins.bottom}mm;
                         --ml: ${margins.left}mm;
                         --mr: ${margins.right}mm;
                     `">
                    <div id="page-content-editor" contenteditable="true" class="doc-page-content">
                        {!! $letter->content !!}
                    </div>
                </div>
            </div>
        </div>

        <!-- RIGHT Sidebar: Letter Details & Margins & Submit -->
        <aside class="no-print w-80 bg-white dark:bg-zinc-900 border-l border-slate-200 dark:border-zinc-800 flex flex-col overflow-hidden shrink-0 shadow-sm z-20">
            <div class="p-5 border-b border-slate-200 dark:border-zinc-800 space-y-1 shrink-0">
                <h3 class="text-sm font-bold text-slate-800 dark:text-zinc-200 uppercase tracking-wider">Document Settings</h3>
                <p class="text-xs text-slate-400">Update title, employee name or margins.</p>
            </div>

            <div class="flex-1 overflow-y-auto p-5 space-y-6 min-h-0">
                
                @if($errors->any())
                <div class="p-3 bg-rose-50 dark:bg-rose-950/20 border border-rose-200 dark:border-rose-800 rounded-xl text-rose-700 dark:text-rose-400 text-xs space-y-1">
                    @foreach($errors->all() as $e)<p>• {{ $e }}</p>@endforeach
                </div>
                @endif

                <!-- Document Details -->
                <div class="space-y-3">
                    <div class="space-y-1">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-zinc-400">Template Title *</label>
                        <input type="text" name="template_title" value="{{ old('template_title', $letter->template_title) }}" required
                               class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-950 rounded-xl text-xs font-semibold text-slate-900 dark:text-zinc-100 focus:border-amber-500 outline-none">
                    </div>

                    <div class="space-y-1">
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-zinc-400">Employee Name *</label>
                        <input type="text" name="employee_name" value="{{ old('employee_name', $letter->employee_name) }}" required
                               class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-950 rounded-xl text-xs font-semibold text-slate-900 dark:text-zinc-100 focus:border-amber-500 outline-none">
                    </div>

                    @if($letter->employee_code)
                        <div class="text-xs text-slate-400 font-mono">Employee Code: {{ $letter->employee_code }}</div>
                    @endif
                </div>

                <!-- Page Margins -->
                <div class="p-4 bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 rounded-2xl space-y-3">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-zinc-400">Page Margins (mm)</label>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[9px] font-bold text-slate-400 mb-0.5">Top</label>
                            <input type="number" name="margin_top" x-model.number="margins.top" min="0" max="100"
                                   class="w-full p-1.5 border border-slate-200 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-900 text-xs text-center text-slate-800 dark:text-zinc-200 focus:border-amber-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-[9px] font-bold text-slate-400 mb-0.5">Bottom</label>
                            <input type="number" name="margin_bottom" x-model.number="margins.bottom" min="0" max="100"
                                   class="w-full p-1.5 border border-slate-200 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-900 text-xs text-center text-slate-800 dark:text-zinc-200 focus:border-amber-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-[9px] font-bold text-slate-400 mb-0.5">Left</label>
                            <input type="number" name="margin_left" x-model.number="margins.left" min="0" max="100"
                                   class="w-full p-1.5 border border-slate-200 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-900 text-xs text-center text-slate-800 dark:text-zinc-200 focus:border-amber-500 outline-none">
                        </div>
                        <div>
                            <label class="block text-[9px] font-bold text-slate-400 mb-0.5">Right</label>
                            <input type="number" name="margin_right" x-model.number="margins.right" min="0" max="100"
                                   class="w-full p-1.5 border border-slate-200 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-900 text-xs text-center text-slate-800 dark:text-zinc-200 focus:border-amber-500 outline-none">
                        </div>
                    </div>
                </div>

            </div>

            <!-- Footer (Submit buttons) -->
            <div class="p-4 border-t border-slate-200 dark:border-zinc-800 space-y-2 shrink-0 bg-white dark:bg-zinc-900">
                <button type="submit" class="w-full py-2.5 px-4 bg-amber-500 hover:bg-amber-600 text-white font-bold text-sm rounded-xl shadow-md shadow-amber-500/20 active:scale-98 transition-all cursor-pointer">
                    <i class="fa-solid fa-floppy-disk mr-1.5"></i> Save Changes
                </button>
                <a href="{{ route('letters.history') }}" class="block text-center py-2 text-sm text-slate-500 dark:text-zinc-400 hover:text-slate-800 dark:hover:text-zinc-200 transition-colors">Cancel</a>
            </div>
        </aside>
    </form>

</div>
@endsection

@section('scripts')
<script>
let savedRange = null;

function saveSelection() {
    const sel = window.getSelection();
    if (sel && sel.rangeCount > 0) {
        const root = document.getElementById('page-content-editor');
        if (root && root.contains(sel.getRangeAt(0).commonAncestorContainer)) {
            savedRange = sel.getRangeAt(0).cloneRange();
        }
    }
}

function restoreSelection() {
    if (savedRange) {
        const root = document.getElementById('page-content-editor');
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
    document.execCommand('fontSize', false, '7');
    const root = document.getElementById('page-content-editor');
    if (root) root.querySelectorAll('[size="7"]').forEach(n => {
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

function insertTable() {
    const root = document.getElementById('page-content-editor');
    if (!root) return;
    root.focus();
    let html = '<table><tbody>';
    for (let r = 0; r < 3; r++) {
        html += '<tr>';
        for (let c = 0; c < 3; c++) html += '<td>&nbsp;</td>';
        html += '</tr>';
    }
    html += '</tbody>mtable><p><br></p>';
    document.execCommand('insertHTML', false, html);
}

document.addEventListener('selectionchange', () => {
    saveSelection();
});

function editGeneratedLetterState() {
    return {
        employee: @json($emp),
        customValues: @json($letter->custom_values ?? []),
        margins: {
            top: {{ $m['top'] ?? 25 }},
            bottom: {{ $m['bottom'] ?? 25 }},
            left: {{ $m['left'] ?? 20 }},
            right: {{ $m['right'] ?? 20 }}
        },
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
            { key: 'employee_join_date_formatted', label: 'Join Date Formatted' },
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
            { key: 'employee_his_her', label: 'his/her' },
            { key: 'employee_he_she', label: 'he/she' },
            { key: 'employee_his_her_cap', label: 'His/Her' },
            { key: 'employee_he_she_cap', label: 'He/She' }
        ],

        get filteredPrebuiltVars() {
            if (!this.varSearch) return this.prebuiltVars;
            const q = this.varSearch.toLowerCase();
            return this.prebuiltVars.filter(v => 
                v.key.toLowerCase().includes(q) || v.label.toLowerCase().includes(q)
            );
        },

        getGenderPrefix(gender) {
            const g = (gender || '').toLowerCase().trim();
            if (g === 'male' || g === 'm') return 'Mr. ';
            if (g === 'female' || g === 'f') return 'Miss ';
            return '';
        },

        getEmployeeNameWithPrefix(name, gender) {
            if (!name) return '';
            const trimmedName = name.trim();
            if (/^(mr|miss|mrs|ms)\.?\s+/i.test(trimmedName)) {
                return trimmedName;
            }
            return (this.getGenderPrefix(gender) + trimmedName).trim();
        },

        getHisHer(gender, capitalize) {
            const g = (gender || '').toLowerCase();
            let val = 'its';
            if (g === 'male' || g === 'm') val = 'his';
            else if (g === 'female' || g === 'f') val = 'her';
            return capitalize ? val.charAt(0).toUpperCase() + val.slice(1) : val;
        },

        getHeShe(gender, capitalize) {
            const g = (gender || '').toLowerCase();
            let val = 'it';
            if (g === 'male' || g === 'm') val = 'he';
            else if (g === 'female' || g === 'f') val = 'she';
            return capitalize ? val.charAt(0).toUpperCase() + val.slice(1) : val;
        },

        formatDate(dateStr) {
            if (!dateStr) return '';
            const date = new Date(dateStr);
            if (isNaN(date.getTime())) return dateStr;
            const day    = date.getDate();
            const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
            const month  = months[date.getMonth()];
            const year   = date.getFullYear();

            let suffix = 'th';
            if (day === 1 || day === 21 || day === 31) suffix = 'st';
            else if (day === 2 || day === 22)          suffix = 'nd';
            else if (day === 3 || day === 23)          suffix = 'rd';

            return day + suffix + ' ' + month + ', ' + year;
        },

        formatLabel(key) {
            return key.replace(/_/g, ' ');
        },

        getEvaluatedPredefinedValue(key) {
            const emp = this.employee;
            if (!emp) return '';

            switch (key) {
                case 'employee_name':
                    return this.getEmployeeNameWithPrefix(emp.name, emp.gender);
                case 'employee_first_name':
                    return emp.first_name || '';
                case 'employee_middle_name':
                    return emp.middle_name || '';
                case 'employee_last_name':
                    return emp.last_name || '';
                case 'employee_employee_code':
                    return emp.employee_code || '';
                case 'employee_department':
                    return emp.department ? (emp.department.name || emp.department) : '';
                case 'employee_designation':
                    return emp.designation ? (emp.designation.name || emp.designation) : '';
                case 'employee_gender':
                    return emp.gender || '';
                case 'employee_join_date':
                    return this.formatDate(emp.join_date);
                case 'employee_join_date_formatted':
                    return emp.join_date_formatted || '';
                case 'employee_contact_number':
                    return emp.contact_number || '';
                case 'employee_email':
                    return emp.email || '';
                case 'employee_citizenship_number':
                    return emp.citizenship_number || '';
                case 'employee_citizenship_issue_date':
                    return emp.citizenship_issue_date || '';
                case 'employee_citizenship_issue_place':
                    return emp.citizenship_issue_place || '';
                case 'employee_ssid':
                    return emp.ssid || '';
                case 'employee_dob_ad':
                    return this.formatDate(emp.dob_ad);
                case 'employee_dob_bs':
                    return emp.dob_bs || '';
                case 'employee_marital_status':
                    return emp.marital_status || '';
                case 'employee_employee_status':
                    return emp.employee_status || '';
                case 'employee_rank':
                    return emp.rank || '';
                case 'employee_dp_rank':
                    return emp.dp_rank || '';
                case 'employee_tips_amount':
                    return emp.tips_amount || '0';
                case 'employee_tips_status':
                    return emp.tips_status || '';
                case 'employee_point_value':
                    return emp.point_value || '0';
                case 'employee_his_her':
                    return this.getHisHer(emp.gender, false);
                case 'employee_he_she':
                    return this.getHeShe(emp.gender, false);
                case 'employee_his_her_cap':
                    return this.getHisHer(emp.gender, true);
                case 'employee_he_she_cap':
                    return this.getHeShe(emp.gender, true);
                default:
                    const cleanKey = key.replace('employee_', '');
                    return emp[cleanKey] !== undefined && emp[cleanKey] !== null ? emp[cleanKey] : '';
            }
        },

        insertPredefinedValue(key) {
            const val = this.getEvaluatedPredefinedValue(key);
            if (!val) return;
            restoreSelection();
            const root = document.getElementById('page-content-editor');
            if (root) root.focus();
            document.execCommand('insertText', false, val);
        },

        insertCustomValue(key) {
            const val = this.customValues[key] || '';
            if (!val) return;
            restoreSelection();
            const root = document.getElementById('page-content-editor');
            if (root) root.focus();
            document.execCommand('insertText', false, val);
        },

        prepareSubmit(e) {
            const ed = document.getElementById('page-content-editor');
            const hidden = document.getElementById('content-hidden');
            if (ed && hidden) {
                hidden.value = ed.innerHTML;
            }
        }
    };
}
</script>
@endsection
