@extends('letters.layout')

@section('title', 'Create Template')

@section('styles')
<style>
/* =====================================================
   GOOGLE DOCS-STYLE DOCUMENT EDITOR — Premium Theme
   ===================================================== */

/* --- Toolbar --- */
.doc-toolbar {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 3px;
    padding: 6px 14px;
    background: linear-gradient(to bottom, #ffffff, #f8fafc);
    border-bottom: 1px solid #e2e8f0;
    min-height: 46px;
    flex-shrink: 0;
    position: relative;
    z-index: 10;
}
.dark .doc-toolbar {
    background: linear-gradient(to bottom, #1c1c1f, #18181b);
    border-bottom-color: #2d2d32;
}

.tb-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 30px;
    height: 30px;
    padding: 0 6px;
    border-radius: 5px;
    border: none;
    background: transparent;
    color: #4b5563;
    cursor: pointer;
    font-size: 13px;
    font-weight: 600;
    transition: all 0.12s;
    font-family: inherit;
    flex-shrink: 0;
}
.dark .tb-btn { color: #a1a1aa; }
.tb-btn:hover {
    background: #f0f4f8;
    color: #1e293b;
}
.dark .tb-btn:hover {
    background: #2d2d32;
    color: #f4f4f5;
}
.tb-btn.is-active {
    background: #fef3c7;
    color: #92400e;
    box-shadow: inset 0 1px 3px rgba(0,0,0,.08);
}
.dark .tb-btn.is-active {
    background: #422006;
    color: #fcd34d;
    box-shadow: none;
}

.tb-sep {
    width: 1px;
    height: 22px;
    background: #e2e8f0;
    margin: 0 5px;
    flex-shrink: 0;
}
.dark .tb-sep { background: #2d2d32; }

.tb-select {
    height: 30px;
    border: 1px solid #e2e8f0;
    border-radius: 5px;
    font-size: 12px;
    padding: 0 8px;
    background: #fff;
    color: #374151;
    cursor: pointer;
    outline: none;
    font-family: inherit;
    flex-shrink: 0;
    transition: border-color .15s, box-shadow .15s;
}
.tb-select:focus {
    border-color: #f59e0b;
    box-shadow: 0 0 0 2px rgba(245,158,11,.12);
}
.dark .tb-select {
    background: #232327;
    border-color: #2d2d32;
    color: #d4d4d8;
}
.dark .tb-select:focus {
    border-color: #f59e0b;
}

/* Page counter bar */
#page-count-bar {
    width: 210mm;
    text-align: right;
    font-size: 10px;
    color: #9ca3af;
    font-family: 'Inter', sans-serif;
    padding: 4px 8px 0;
    user-select: none;
}

/* ---- COPY TOOLTIP ---- */
.copy-tip {
    position: fixed;
    background: linear-gradient(135deg, #1e293b, #0f172a);
    color: #f1f5f9;
    font-size: 11px;
    font-family: 'Inter', sans-serif;
    font-weight: 500;
    padding: 5px 12px;
    border-radius: 7px;
    pointer-events: none;
    z-index: 9999;
    white-space: nowrap;
    opacity: 0;
    transform: translateY(6px);
    transition: opacity 0.15s, transform 0.15s;
    box-shadow: 0 4px 16px rgba(0,0,0,.25);
}
.copy-tip::before {
    content: '';
    position: absolute;
    top: -4px;
    left: 50%;
    transform: translateX(-50%);
    width: 8px;
    height: 4px;
    background: #0f172a;
    clip-path: polygon(0 100%, 50% 0, 100% 100%);
}
.copy-tip.show {
    opacity: 1;
    transform: translateY(0);
}
</style>
@endsection


@section('content')
<!-- ===== Copy Tooltip (global) ===== -->
<div id="copy-tip" class="copy-tip">✓ Copied!</div>

<div x-data="createTemplateState()" class="h-full flex flex-col md:flex-row overflow-hidden">

    <!-- ============================================================
         LEFT SIDEBAR — Configuration
         ============================================================ -->
    <aside class="w-full md:w-[320px] xl:w-[360px] bg-white dark:bg-zinc-900 border-r border-slate-200 dark:border-zinc-800 flex flex-col h-full overflow-hidden shrink-0 transition-colors duration-200">
        <form id="template-form" action="{{ route('letters.store') }}" method="POST" class="flex flex-col h-full overflow-hidden" @submit="syncContent()">
            @csrf

            {{-- Serialize Alpine variables to hidden inputs --}}
            <template x-for="(v, idx) in variables" :key="idx">
                <div>
                    <input type="hidden" :name="'variables['+idx+'][key]'" :value="v.key">
                    <input type="hidden" :name="'variables['+idx+'][type]'" :value="v.type">
                    <input type="hidden" :name="'variables['+idx+'][dummy]'" :value="v.dummy">
                </div>
            </template>
            {{-- Content synced from editor on submit --}}
            <input type="hidden" name="content" id="content-hidden">

            <!-- Scrollable form area -->
            <div class="flex-1 overflow-y-auto p-4 space-y-4">

                <div>
                    <h2 class="text-sm font-bold text-slate-800 dark:text-zinc-100">New Letter Template</h2>
                    <p class="text-[10px] text-slate-400 mt-0.5">Configure this template's settings and variables. Write the content in the document editor on the right.</p>
                </div>

                @if($errors->any())
                <div class="p-3 bg-rose-50 dark:bg-rose-950/20 border border-rose-200 dark:border-rose-800 rounded-lg text-rose-700 dark:text-rose-400 text-xs space-y-1">
                    @foreach($errors->all() as $e)<p>• {{ $e }}</p>@endforeach
                </div>
                @endif

                <!-- Title -->
                <div class="space-y-1">
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-500 dark:text-zinc-400">Title *</label>
                    <input
                        type="text" name="title" x-model="title" required
                        value="{{ old('title') }}"
                        class="w-full px-3 py-2 rounded-lg border border-slate-200 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-950 text-sm text-slate-900 dark:text-zinc-100 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 outline-none transition-all"
                        placeholder="e.g. Promotion Letter"
                    >
                </div>

                <!-- Margins -->
                <div class="bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 rounded-xl p-3 space-y-2">
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">Page Margins (mm)</label>
                    <div class="grid grid-cols-4 gap-1.5">
                        @foreach(['top' => '↑', 'bottom' => '↓', 'left' => '←', 'right' => '→'] as $side => $arrow)
                        <div>
                            <label class="block text-[9px] font-bold text-slate-400 text-center mb-0.5">{{ $arrow }}</label>
                            <input type="number" name="margin_{{ $side }}" x-model.number="margins.{{ $side }}"
                                min="0" max="100"
                                class="w-full p-1.5 border border-slate-200 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-900 text-xs text-center text-slate-800 dark:text-zinc-200 focus:border-amber-500 outline-none transition-all"
                                @change="applyMarginsToPages()">
                        </div>
                        @endforeach
                    </div>
                </div>

                <!-- ---- Employee Variable Pills ---- -->
                <div class="space-y-1.5">
                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">Employee Placeholders</label>
                    <p class="text-[10px] text-slate-400">Click a placeholder to copy it, then paste it into the editor.</p>
                    <div class="flex flex-wrap gap-1">
                        @foreach([
                            'employee_name'         => 'Name',
                            'employee_employee_code'=> 'Code',
                            'employee_gender'       => 'Gender',
                            'employee_department'   => 'Department',
                            'employee_designation'  => 'Designation',
                            'employee_join_date'    => 'Join Date',
                            'employee_email'        => 'Email',
                            'employee_contact_number' => 'Phone',
                            'employee_dob_ad'       => 'DOB',
                        ] as $key => $label)
                        @php $token = '{{ ' . $key . ' }}'; @endphp
                        <button
                            type="button"
                            data-copy="{{ $token }}"
                            onclick="copyToken(this)"
                            class="group flex items-center gap-1 px-2 py-0.5 bg-amber-50 dark:bg-amber-950/30 rounded-md text-[10px] font-semibold font-mono text-amber-700 dark:text-amber-400 hover:bg-amber-100 dark:hover:bg-amber-900/50 transition-colors cursor-pointer border border-amber-200/60 dark:border-amber-800/30"
                            title="{{ $token }}"
                        >
                            <svg class="w-2.5 h-2.5 opacity-50 group-hover:opacity-100 transition-opacity" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                            </svg>
                            {{ $label }}
                        </button>
                        @endforeach
                    </div>
                </div>

                <!-- ---- Custom Variables ---- -->
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-400">Custom Variables</label>
                        <button type="button" @click="addVariable()"
                            class="flex items-center gap-1 px-2 py-0.5 text-[10px] font-semibold bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-400 rounded hover:bg-amber-100 transition-colors cursor-pointer">
                            <svg class="w-2.5 h-2.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                            Add
                        </button>
                    </div>

                    <div class="space-y-2 max-h-64 overflow-y-auto pr-0.5">
                        <template x-for="(v, idx) in variables" :key="idx">
                            <div class="relative p-2.5 bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 rounded-lg space-y-1.5">
                                <button type="button" @click="removeVariable(idx)"
                                    class="absolute top-2 right-2 text-slate-300 hover:text-rose-500 cursor-pointer transition-colors">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>

                                <div class="grid grid-cols-2 gap-1.5 pr-5">
                                    <div>
                                        <label class="block text-[9px] font-bold uppercase text-slate-400 mb-0.5">Key</label>
                                        <input type="text" x-model="v.key" placeholder="my_variable"
                                            class="w-full p-1 border border-slate-200 dark:border-zinc-700 rounded bg-white dark:bg-zinc-900 text-[10px] font-mono text-slate-800 dark:text-zinc-200 focus:border-amber-500 outline-none">
                                    </div>
                                    <div>
                                        <label class="block text-[9px] font-bold uppercase text-slate-400 mb-0.5">Type</label>
                                        <select x-model="v.type" @change="typeChanged(v)"
                                            class="w-full p-1 border border-slate-200 dark:border-zinc-700 rounded bg-white dark:bg-zinc-900 text-[10px] text-slate-800 dark:text-zinc-200 focus:border-amber-500 outline-none">
                                            <option value="text">Text</option>
                                            <option value="date">Date</option>
                                            <option value="number">Number</option>
                                            <option value="boolean">Yes/No</option>
                                        </select>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-[9px] font-bold uppercase text-slate-400 mb-0.5">Preview Value</label>
                                    <template x-if="v.type === 'date'">
                                        <input type="date" x-model="v.dummy" class="w-full p-1 border border-slate-200 dark:border-zinc-700 rounded bg-white dark:bg-zinc-900 text-[10px] focus:border-amber-500 outline-none">
                                    </template>
                                    <template x-if="v.type === 'number'">
                                        <input type="number" x-model="v.dummy" placeholder="1000" class="w-full p-1 border border-slate-200 dark:border-zinc-700 rounded bg-white dark:bg-zinc-900 text-[10px] focus:border-amber-500 outline-none">
                                    </template>
                                    <template x-if="v.type === 'boolean'">
                                        <select x-model="v.dummy" class="w-full p-1 border border-slate-200 dark:border-zinc-700 rounded bg-white dark:bg-zinc-900 text-[10px] focus:border-amber-500 outline-none">
                                            <option value="Yes">Yes</option>
                                            <option value="No">No</option>
                                        </select>
                                    </template>
                                    <template x-if="!v.type || v.type === 'text'">
                                        <input type="text" x-model="v.dummy" placeholder="Sample text" class="w-full p-1 border border-slate-200 dark:border-zinc-700 rounded bg-white dark:bg-zinc-900 text-[10px] focus:border-amber-500 outline-none">
                                    </template>
                                </div>

                                {{-- Copy hint for this variable --}}
                                <div x-show="v.key" class="flex items-center gap-1">
                                    <span class="text-[9px] text-slate-400">Placeholder:</span>
                                    <button
                                        type="button"
                                        :data-copy="'{{ ' + v.key + ' }}'"
                                        onclick="copyToken(this)"
                                        class="text-[9px] font-mono text-amber-600 dark:text-amber-400 hover:text-amber-700 dark:hover:text-amber-300 underline cursor-pointer transition-colors"
                                        x-text="'@{{ ' + v.key + ' }}'">
                                    </button>
                                </div>
                            </div>
                        </template>
                        <div x-show="variables.length === 0" class="py-4 text-center text-[10px] text-slate-400 italic border border-dashed border-slate-200 dark:border-zinc-800 rounded-lg">
                            No custom variables yet.
                        </div>
                    </div>
                </div>

            </div><!-- /scrollable -->

            <!-- Footer -->
            <div class="shrink-0 p-3 border-t border-slate-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 flex gap-2 transition-colors duration-200">
                <button
                    type="submit"
                    @click="syncContent()"
                    class="flex-1 py-2.5 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white text-sm font-semibold rounded-xl shadow-md shadow-amber-500/10 active:scale-[0.98] transition-all cursor-pointer"
                >
                    Create Template
                </button>
                <a href="{{ route('letters.index') }}" class="px-3 py-2.5 bg-white dark:bg-zinc-800 border border-slate-200 dark:border-zinc-700 text-slate-700 dark:text-zinc-300 rounded-xl text-sm font-semibold hover:bg-slate-50 dark:hover:bg-zinc-750 transition-all">
                    Cancel
                </a>
            </div>
        </form>
    </aside>

    <!-- ============================================================
         RIGHT: GOOGLE DOCS-STYLE EDITOR
         ============================================================ -->
    <div class="flex-1 flex flex-col overflow-hidden">

        <!-- === Formatting Toolbar === -->
        <div class="doc-toolbar no-print" id="doc-toolbar">

            <!-- Paragraph / Heading -->
            <select class="tb-select" style="width:108px;" onchange="execBlock(this.value); this.blur();" id="tb-block" title="Paragraph style">
                <option value="p">Normal text</option>
                <option value="h1">Heading 1</option>
                <option value="h2">Heading 2</option>
                <option value="h3">Heading 3</option>
            </select>

            <!-- Font -->
            <select class="tb-select ml-1" style="width:110px;" onchange="execFont(this.value); this.blur();" id="tb-font" title="Font">
                <option value="Times New Roman" selected>Times New Roman</option>
                <option value="Arial">Arial</option>
                <option value="Georgia">Georgia</option>
                <option value="Courier New">Courier New</option>
                <option value="Verdana">Verdana</option>
                <option value="Inter">Inter</option>
                <option value="Playfair Display">Playfair Display</option>
                <option value="Roboto">Roboto</option>
                <option value="Merriweather">Merriweather</option>
                <option value="Montserrat">Montserrat</option>
            </select>

            <!-- Font size -->
            <select class="tb-select ml-1" style="width:52px;" onchange="execFontSize(this.value); this.blur();" id="tb-size" title="Font size">
                @foreach([8,9,10,11,12,14,16,18,20,24,28,32,36,48] as $s)
                <option value="{{ $s }}" {{ $s === 11 ? 'selected' : '' }}>{{ $s }}</option>
                @endforeach
            </select>

            <div class="tb-sep"></div>

            <!-- Bold / Italic / Underline / Strike -->
            <button type="button" class="tb-btn" id="tb-bold"    onclick="exec('bold')"          title="Bold (Ctrl+B)"><strong>B</strong></button>
            <button type="button" class="tb-btn" id="tb-italic"  onclick="exec('italic')"        title="Italic (Ctrl+I)"><em>I</em></button>
            <button type="button" class="tb-btn" id="tb-under"   onclick="exec('underline')"     title="Underline (Ctrl+U)"><u>U</u></button>
            <button type="button" class="tb-btn" id="tb-strike"  onclick="exec('strikeThrough')" title="Strikethrough"><s>S</s></button>

            <div class="tb-sep"></div>

            <!-- Text colour -->
            <button type="button" class="tb-btn relative overflow-visible" title="Text color" style="min-width:32px;" onclick="document.getElementById('color-inp').click()">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><text x="2" y="18" font-size="18" font-family="serif" font-weight="bold">A</text></svg>
                <span id="color-bar" style="position:absolute;bottom:2px;left:4px;right:4px;height:3px;border-radius:1px;background:#111;"></span>
                <input type="color" id="color-inp" value="#111111" style="position:absolute;opacity:0;width:0;height:0;pointer-events:none;" oninput="execColor(this.value)">
            </button>

            <!-- Highlight -->
            <button type="button" class="tb-btn relative overflow-visible" title="Highlight color" style="min-width:32px;" onclick="document.getElementById('highlight-inp').click()">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 11l-2 8 3-2 8-8-5-5-9 9 2 3M15 3l6 6"/></svg>
                <span id="highlight-bar" style="position:absolute;bottom:2px;left:4px;right:4px;height:3px;border-radius:1px;background:#fde68a;"></span>
                <input type="color" id="highlight-inp" value="#fde68a" style="position:absolute;opacity:0;width:0;height:0;pointer-events:none;" oninput="execHighlight(this.value)">
            </button>

            <div class="tb-sep"></div>

            <!-- Alignment -->
            <button type="button" class="tb-btn" id="tb-left"    onclick="exec('justifyLeft')"   title="Align left">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M3 6h18M3 12h12M3 18h15"/></svg>
            </button>
            <button type="button" class="tb-btn" id="tb-center"  onclick="exec('justifyCenter')" title="Align center">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M3 6h18M6 12h12M4 18h16"/></svg>
            </button>
            <button type="button" class="tb-btn" id="tb-right"   onclick="exec('justifyRight')"  title="Align right">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M3 6h18M9 12h12M6 18h15"/></svg>
            </button>
            <button type="button" class="tb-btn" id="tb-justify" onclick="exec('justifyFull')"   title="Justify">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M3 6h18M3 12h18M3 18h18"/></svg>
            </button>

            <div class="tb-sep"></div>

            <!-- Lists -->
            <button type="button" class="tb-btn" onclick="exec('insertUnorderedList')" title="Bullet list">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <circle cx="4.5" cy="7" r="1.5" fill="currentColor" stroke="none"/><path d="M8 7h13"/>
                    <circle cx="4.5" cy="12" r="1.5" fill="currentColor" stroke="none"/><path d="M8 12h13"/>
                    <circle cx="4.5" cy="17" r="1.5" fill="currentColor" stroke="none"/><path d="M8 17h13"/>
                </svg>
            </button>
            <button type="button" class="tb-btn" onclick="exec('insertOrderedList')" title="Numbered list">
                <svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24">
                    <text x="2" y="9" font-size="7.5">1.</text>
                    <text x="2" y="15.5" font-size="7.5">2.</text>
                    <text x="2" y="22" font-size="7.5">3.</text>
                    <line x1="9" y1="7" x2="22" y2="7" stroke="currentColor" stroke-width="1.8"/>
                    <line x1="9" y1="12" x2="22" y2="12" stroke="currentColor" stroke-width="1.8"/>
                    <line x1="9" y1="17" x2="22" y2="17" stroke="currentColor" stroke-width="1.8"/>
                </svg>
            </button>

            <div class="tb-sep"></div>

            <!-- Indent / Outdent -->
            <button type="button" class="tb-btn" onclick="exec('indent')"  title="Indent (Tab)">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M3 6h18M3 18h18M11 12h10M3 9l4 3-4 3"/></svg>
            </button>
            <button type="button" class="tb-btn" onclick="exec('outdent')" title="Outdent (Shift+Tab)">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M3 6h18M3 18h18M3 12h10M15 9l4 3-4 3"/></svg>
            </button>

            <div class="tb-sep"></div>

            <!-- Clear formatting -->
            <button type="button" class="tb-btn" onclick="exec('removeFormat')" title="Clear formatting">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L17.94 6M15 6h5M19 11l-6 7H7"/></svg>
            </button>

            <!-- Insert Horizontal Rule -->
            <button type="button" class="tb-btn" onclick="exec('insertHorizontalRule')" title="Insert horizontal line">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path d="M3 12h18M8 6l-5 6 5 6M16 6l5 6-5 6"/></svg>
            </button>

        </div><!-- /toolbar -->

        <!-- === Document editor area === -->
        <div class="editor-desk" id="editor-desk">
            <div id="pages-container">
                <!-- First page — always present -->
                <div class="page-sheet">
                    <div class="a4-doc-page" contenteditable="true" spellcheck="true"
                         data-placeholder="Start typing your letter content here…"></div>
                </div>
            </div>
            <div id="page-count-bar" class="no-print">
                <span x-text="pageCount + (pageCount === 1 ? ' page' : ' pages')"></span>
            </div>
        </div>

    </div><!-- /right -->

</div><!-- /main -->
@endsection


@section('scripts')
<script>
function copyToken(btn) {
    const text = btn.dataset.copy;
    if (!text) return;
    
    const showTip = () => {
        const tip = document.getElementById('copy-tip');
        if (tip) {
            const rect = btn.getBoundingClientRect();
            tip.style.left      = (rect.left + rect.width / 2) + 'px';
            tip.style.top       = (rect.top - 36) + 'px';
            tip.style.transform = 'translateX(-50%)';
            tip.classList.add('show');
            setTimeout(() => tip.classList.remove('show'), 1600);
        }
    };

    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(text).then(showTip).catch(err => {
            fallbackCopy(text, showTip);
        });
    } else {
        fallbackCopy(text, showTip);
    }
}

function fallbackCopy(text, cb) {
    const ta = document.createElement('textarea');
    ta.value = text;
    ta.style.position = 'fixed';
    ta.style.opacity = '0';
    document.body.appendChild(ta);
    ta.select();
    try {
        document.execCommand('copy');
        cb();
    } catch(e) {
        console.error('Fallback copy failed', e);
    }
    document.body.removeChild(ta);
}

/* ================================================================
   TOOLBAR COMMANDS
   ================================================================ */
function exec(cmd, val) {
    document.execCommand(cmd, false, val ?? null);
    updateToolbarState();
}

function execBlock(tag) {
    document.execCommand('formatBlock', false, tag);
    updateToolbarState();
}

function execFont(name) {
    document.execCommand('fontName', false, name);
}

function execFontSize(pt) {
    document.execCommand('fontSize', false, '7');
    document.querySelectorAll('#doc-editor [size="7"]').forEach(el => {
        el.removeAttribute('size');
        el.style.fontSize = pt + 'pt';
    });
}

function execColor(color) {
    document.getElementById('color-bar').style.background = color;
    document.execCommand('foreColor', false, color);
}

function execHighlight(color) {
    document.getElementById('highlight-bar').style.background = color;
    document.execCommand('hiliteColor', false, color);
}

function updateToolbarState() {
    const pairs = [
        ['bold','tb-bold'], ['italic','tb-italic'], ['underline','tb-under'], ['strikeThrough','tb-strike'],
        ['justifyLeft','tb-left'], ['justifyCenter','tb-center'], ['justifyRight','tb-right'], ['justifyFull','tb-justify']
    ];
    pairs.forEach(([cmd, id]) => {
        const el = document.getElementById(id);
        if (el) el.classList.toggle('is-active', document.queryCommandState(cmd));
    });
    const block = document.queryCommandValue('formatBlock').toLowerCase();
    const sel = document.getElementById('tb-block');
    if (sel) sel.value = ['h1','h2','h3'].includes(block) ? block : 'p';
}

document.addEventListener('selectionchange', updateToolbarState);

/* ================================================================
   ALPINE STATE — no DOM pagination, just tracking page count
   ================================================================ */
function createTemplateState() {
    return {
        title: '',
        margins: { top: 25, bottom: 25, left: 20, right: 20 },
        variables: [],
        pageCount: 1,

        addVariable()      { this.variables.push({ key: '', type: 'text', dummy: '' }); },
        removeVariable(i)  { this.variables.splice(i, 1); },
        typeChanged(v) {
            const d = { boolean: 'Yes', date: new Date().toISOString().slice(0, 10), number: '1000', text: '' };
            v.dummy = d[v.type] ?? '';
        },

        /* Called by the Tab keydown handler on the editor */
        handleKey(e) {
            if (e.key === 'Tab') {
                e.preventDefault();
                exec(e.shiftKey ? 'outdent' : 'indent');
            }
        },

        /* ---- Page management ---- */
        _reflowing: false,
        _reflowTimer: null,

        debouncedReflow() {
            clearTimeout(this._reflowTimer);
            this._reflowTimer = setTimeout(() => this.reflowPages(), 150);
        },

        allPages() {
            return Array.from(document.querySelectorAll('#pages-container .a4-doc-page'));
        },

        applyMarginToPage(el) {
            el.style.paddingTop    = this.margins.top    + 'mm';
            el.style.paddingBottom = this.margins.bottom + 'mm';
            el.style.paddingLeft   = this.margins.left   + 'mm';
            el.style.paddingRight  = this.margins.right  + 'mm';
        },

        createPage() {
            const container = document.getElementById('pages-container');

            // Gap
            const gap = document.createElement('div');
            gap.className = 'page-gap no-print';

            // Sheet wrapper
            const sheet = document.createElement('div');
            sheet.className = 'page-sheet';

            // Content div
            const page = document.createElement('div');
            page.className = 'a4-doc-page';
            page.contentEditable = 'true';
            page.spellcheck = true;
            this.applyMarginToPage(page);
            this.bindPageListeners(page);

            sheet.appendChild(page);
            container.appendChild(gap);
            container.appendChild(sheet);
            this.pageCount = document.querySelectorAll('#pages-container .page-sheet').length;
            return page;
        },

        destroyLastPage() {
            const container = document.getElementById('pages-container');
            const sheets = container.querySelectorAll('.page-sheet');
            if (sheets.length <= 1) return;
            const last = sheets[sheets.length - 1];
            const gap  = last.previousElementSibling;
            if (gap?.classList.contains('page-gap')) gap.remove();
            last.remove();
            this.pageCount = container.querySelectorAll('.page-sheet').length;
        },

        isPageEmpty(page) {
            return !page.firstChild ||
                   page.innerHTML.trim() === '' ||
                   page.innerHTML.trim() === '<br>';
        },

        reflowPages() {
            if (this._reflowing) return;
            this._reflowing = true;
            try {
                const pages = this.allPages();

                // Forward: push overflow from page[i] to page[i+1]
                for (let i = 0; i < pages.length; i++) {
                    let guard = 200;
                    while (pages[i].scrollHeight > pages[i].clientHeight + 2 && guard-- > 0) {
                        let next = pages[i + 1];
                        if (!next) {
                            next = this.createPage();
                            pages.push(next);
                        }
                        // Move last meaningful child to start of next page
                        let child = pages[i].lastChild;
                        while (child && child.nodeType === 3 && !child.textContent.trim()) {
                            child = child.previousSibling;
                        }
                        if (!child) break;
                        next.insertBefore(child, next.firstChild);
                    }
                }

                // Backward: pull content back if prev has room; remove empty tail pages
                const allPgs = this.allPages();
                for (let i = allPgs.length - 1; i > 0; i--) {
                    const curr = allPgs[i];
                    const prev = allPgs[i - 1];
                    let guard = 200;
                    while (curr.firstChild && guard-- > 0) {
                        const node = curr.firstChild;
                        prev.appendChild(node);
                        if (prev.scrollHeight > prev.clientHeight + 2) {
                            curr.insertBefore(prev.lastChild, curr.firstChild);
                            break;
                        }
                    }
                    if (this.isPageEmpty(curr)) this.destroyLastPage();
                }

                this.pageCount = document.querySelectorAll('#pages-container .page-sheet').length;
            } finally {
                this._reflowing = false;
            }
        },

        bindPageListeners(page) {
            page.addEventListener('input', () => {
                this.debouncedReflow();
                updateToolbarState();
            });
            page.addEventListener('paste', (e) => {
                // Strip rich formatting — paste as plain text only to avoid layout thrash
                e.preventDefault();
                const text = (e.clipboardData || window.clipboardData).getData('text/plain');
                document.execCommand('insertText', false, text);
                // Reflow after browser has painted the inserted content
                requestAnimationFrame(() => this.debouncedReflow());
            });
            page.addEventListener('keydown', e => {
                if (e.key === 'Tab') {
                    e.preventDefault();
                    document.execCommand(e.shiftKey ? 'outdent' : 'indent');
                }
                if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
                    e.preventDefault();
                    this.insertManualPageBreak(page);
                }
            });
        },

        insertManualPageBreak(pageEl) {
            const sel = window.getSelection();
            if (!sel.rangeCount) return;
            const range = sel.getRangeAt(0);

            // Create a range starting from caret position to the end of the pageEl
            const postRange = document.createRange();
            postRange.selectNodeContents(pageEl);
            postRange.setStart(range.endContainer, range.endOffset);

            // Extract the contents
            const fragment = postRange.extractContents();

            // Find or create next page
            const pages = this.allPages();
            const idx = pages.indexOf(pageEl);
            let nextEl = pages[idx + 1];
            if (!nextEl) {
                nextEl = this.createPage();
            }

            // Insert at start of the next page
            nextEl.insertBefore(fragment, nextEl.firstChild);

            // Clean up any empty pages or blocks
            this.reflowPages();

            // Focus new page start
            this.focusStartOfPage(nextEl);
        },

        focusStartOfPage(pageEl) {
            pageEl.focus();
            const range = document.createRange();
            range.selectNodeContents(pageEl);
            range.collapse(true);
            const sel = window.getSelection();
            sel.removeAllRanges();
            sel.addRange(range);
        },

        updateAllMargins() {
            this.allPages().forEach(p => this.applyMarginToPage(p));
            // Reflow after margin change because available height changed
            setTimeout(() => this.reflowPages(), 50);
        },

        syncContent() {
            const html = this.allPages().map(p => p.innerHTML).join('\n<!-- PAGE_BREAK -->\n');
            document.getElementById('content-hidden').value = html;
        },

        init() {
            this.$nextTick(() => {
                const container = document.getElementById('pages-container');
                const first = container.querySelector('.a4-doc-page');
                if (!first) return;
                this.applyMarginToPage(first);
                this.bindPageListeners(first);
                first.focus();
                this.$watch('margins', () => this.updateAllMargins());
            });
        }
    };
}
</script>
@endsection
