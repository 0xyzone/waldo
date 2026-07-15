@extends('letters.layout')

@section('title', 'Generate Letters')

@section('styles')
    <style>
        /* Sleek custom scrollbars */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: transparent;
        }
        ::-webkit-scrollbar-thumb {
            background: rgba(156, 163, 175, 0.25);
            border-radius: 99px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: rgba(156, 163, 175, 0.45);
        }
        .dark ::-webkit-scrollbar-thumb {
            background: rgba(75, 85, 99, 0.4);
        }
        .dark ::-webkit-scrollbar-thumb:hover {
            background: rgba(75, 85, 99, 0.6);
        }

        /* Sidebar scrollable region */
        .sidebar-scroll {
            overflow-y: auto;
            flex: 1;
        }
    </style>
@endsection

@section('content')
    <div x-data="generatorState()" x-init="init()" class="h-full flex flex-col md:flex-row overflow-hidden">

        <!-- ======== LEFT SIDEBAR – Controls ======== -->
        <aside
            class="no-print w-full md:w-[380px] xl:w-[420px] bg-white dark:bg-zinc-900 border-r border-slate-200 dark:border-zinc-800 flex flex-col h-full overflow-hidden shrink-0 transition-colors duration-200">
            
            <div class="flex flex-col h-full overflow-hidden p-5 gap-4">
                <!-- 1. Template -->
                <div class="bg-white dark:bg-zinc-900 rounded-xl border border-slate-200 dark:border-zinc-800 p-4 shrink-0">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-3">1 — Letter Template</p>
                    <select x-model="selectedTemplateId" @change="onTemplateChange()"
                        class="w-full px-3 py-2.5 rounded-lg border border-slate-200 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-950 text-sm text-slate-900 dark:text-zinc-100 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 outline-none transition-all">
                        <option value="">— Choose a template —</option>
                        <template x-for="t in templates" :key="t.id">
                            <option :value="t.id" x-text="t.title" :selected="t.id == selectedTemplateId"></option>
                        </template>
                    </select>
                </div>

                <!-- 2. Page Margins -->
                <div class="bg-white dark:bg-zinc-900 rounded-xl border border-slate-200 dark:border-zinc-800 p-4 shrink-0">
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">2 — Page Margins (mm)</p>
                        <label class="flex items-center gap-1.5 cursor-pointer select-none">
                            <input type="checkbox" x-model="showGuides" class="w-3.5 h-3.5 accent-amber-500">
                            <span class="text-[10px] font-medium text-slate-400">Show guides</span>
                        </label>
                    </div>
                    <div class="grid grid-cols-4 gap-2">
                        @foreach (['top' => '↑ Top', 'bottom' => '↓ Bottom', 'left' => '← Left', 'right' => '→ Right'] as $side => $label)
                            <div>
                                <label class="block text-[9px] font-bold text-slate-400 mb-0.5">{{ $label }}</label>
                                <input type="number" x-model.number="margins.{{ $side }}" min="0"
                                    max="100"
                                    class="w-full p-2 border border-slate-200 dark:border-zinc-700 rounded-lg bg-slate-50 dark:bg-zinc-950 text-xs text-right text-slate-800 dark:text-zinc-200 focus:border-amber-500 outline-none">
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- 3. Employees (Covers remaining height fully) -->
                <div class="flex-1 flex flex-col min-h-0 bg-white dark:bg-zinc-900 rounded-xl border border-slate-200 dark:border-zinc-800 p-4">
                    <div class="flex items-center justify-between mb-3 shrink-0">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400">3 — Target Employees</p>
                        <span x-text="selectedCodes.length + ' selected'"
                            class="text-[10px] font-semibold px-2 py-0.5 rounded-full"
                            :class="selectedCodes.length > 0 ?
                                'bg-amber-100 dark:bg-amber-950/40 text-amber-700 dark:text-amber-400' :
                                'bg-slate-100 dark:bg-zinc-800 text-slate-400'"></span>
                    </div>

                    <div class="relative mb-2.5 shrink-0">
                        <input type="text" x-model="search" placeholder="Search by name or code…"
                            class="w-full pl-8 pr-3 py-2 rounded-lg border border-slate-200 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-950 text-xs text-slate-900 dark:text-zinc-100 focus:border-amber-500 outline-none transition-all">
                        <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-slate-400" fill="none"
                            stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </div>

                    <div class="flex gap-1.5 mb-2.5 shrink-0">
                        <button @click="selectAll()" type="button"
                            class="px-2.5 py-1 text-[10px] font-semibold border border-slate-200 dark:border-zinc-700 rounded-lg hover:bg-slate-50 dark:hover:bg-zinc-800 text-slate-600 dark:text-zinc-400 transition-colors cursor-pointer">Select
                            All</button>
                        <button @click="clearAll()" type="button"
                            class="px-2.5 py-1 text-[10px] font-semibold border border-slate-200 dark:border-zinc-700 rounded-lg hover:bg-slate-50 dark:hover:bg-zinc-800 text-slate-600 dark:text-zinc-400 transition-colors cursor-pointer">Clear</button>
                    </div>

                    <div class="flex-1 min-h-0 border border-slate-100 dark:border-zinc-800 rounded-lg overflow-y-auto">
                        <template x-for="emp in filteredEmployees" :key="emp.employee_code">
                            <div @click="toggleEmployee(emp.employee_code)"
                                class="flex items-center gap-2.5 px-3 py-2 cursor-pointer border-b border-slate-50 dark:border-zinc-900 last:border-b-0 hover:bg-amber-50 dark:hover:bg-amber-950/20 transition-colors"
                                :class="selectedCodes.includes(emp.employee_code) ? 'bg-amber-50 dark:bg-amber-950/20' : ''">
                                <div class="w-4 h-4 rounded border-2 flex items-center justify-center transition-colors shrink-0"
                                    :class="selectedCodes.includes(emp.employee_code) ? 'bg-amber-500 border-amber-500' :
                                        'border-slate-300 dark:border-zinc-600'">
                                    <svg x-show="selectedCodes.includes(emp.employee_code)" class="w-2.5 h-2.5 text-white"
                                        fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                    </svg>
                                </div>
                                <div class="flex flex-col min-w-0">
                                    <span class="text-xs font-semibold text-slate-800 dark:text-zinc-200 truncate"
                                        x-text="emp.name"></span>
                                    <span class="text-[10px] font-mono text-slate-400"
                                        x-text="emp.employee_code + (emp.designation ? ' · ' + emp.designation.name : '')"></span>
                                </div>
                            </div>
                        </template>
                        <div x-show="filteredEmployees.length === 0" class="py-6 text-center text-xs text-slate-400 italic">
                            No employees match your search.</div>
                    </div>
                </div>

                <!-- Footer Actions -->
                <div class="shrink-0 pt-2 border-t border-slate-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 flex flex-col gap-2.5">
                    <button @click="window.print()" :disabled="selectedCodes.length === 0 || !selectedTemplateId"
                        class="w-full py-2.5 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white text-sm font-semibold rounded-xl shadow-md shadow-amber-500/10 transition-all active:scale-[0.98] cursor-pointer flex items-center justify-center gap-2 disabled:opacity-40 disabled:pointer-events-none">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0110.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0l.229 2.523a1.125 1.125 0 01-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0021 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 00-1.913-.247M6.34 18H5.25A2.25 2.25 0 013 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 011.913-.247m10.5 0a48.536 48.536 0 00-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5zm-3 0h.008v.008H15V10.5z" />
                        </svg>
                        Print / Save as PDF
                    </button>
                </div>
            </div>
        </aside>

        <!-- ======== MIDDLE PANE – Preview ======== -->
        <section class="flex-1 overflow-y-auto bg-[#e8eaed] dark:bg-[#121215] transition-colors duration-200">

            <!-- Empty State -->
            <div x-show="selectedCodes.length === 0 || !selectedTemplateId"
                class="h-full flex flex-col items-center justify-center gap-4 text-center p-10 no-print">
                <div class="w-20 h-20 rounded-2xl bg-slate-200 dark:bg-zinc-800 flex items-center justify-center text-4xl">
                    📄</div>
                <h2 class="text-lg font-bold text-slate-700 dark:text-zinc-300">Letter Preview</h2>
                <p class="text-sm text-slate-400 max-w-xs">Select a template and one or more employees from the sidebar to
                    preview generated letters here.</p>
            </div>

            <!-- Letters Preview Container -->
            <div id="preview-container" x-show="selectedCodes.length > 0 && selectedTemplateId"
                class="flex flex-col gap-10 items-center py-10 px-6">
                <!-- Dynamic Print Styles: Enforce borderless A4 page sizing and absolute padding-based margins -->
                <style
                    x-html="`
                @media print {
                    @page {
                        size: A4 portrait;
                        margin: 0 !important;
                    }
                    html, body, main, main > div, section, #preview-container {
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
                    .no-print, header, aside, .page-gap, .margin-guide, .page-break-overlay {
                        display: none !important;
                    }
                    .print-page-wrapper {
                        display: block !important;
                        page-break-inside: avoid !important;
                        break-inside: avoid !important;
                        page-break-after: always !important;
                        break-after: page !important;
                    }
                    /* Prevent trailing blank page at the end of print */
                    #preview-container > div:last-child .print-page-wrapper:last-of-type {
                        page-break-after: auto !important;
                        break-after: auto !important;
                    }
                    .a4-page {
                        width: 210mm !important;
                        height: 297mm !important;
                        margin: 0 auto !important;
                        padding-top: ${margins.top}mm !important;
                        padding-bottom: ${margins.bottom}mm !important;
                        padding-left: ${margins.left}mm !important;
                        padding-right: ${margins.right}mm !important;
                        border: none !important;
                        box-shadow: none !important;
                        background: white !important;
                        box-sizing: border-box !important;
                        page-break-inside: avoid !important;
                        break-inside: avoid !important;
                        overflow: hidden !important;
                    }
                }
            `">
                </style>

                <template x-for="code in selectedCodes" :key="code">
                    <div>
                        <!-- Employee chip label -->
                        <div class="flex items-center gap-2 mb-3 no-print">
                            <div class="w-6 h-6 rounded-full bg-amber-500 text-white flex items-center justify-center text-[10px] font-bold shrink-0"
                                x-text="getEmployee(code)?.name?.charAt(0) || '?'"></div>
                            <span class="text-xs font-semibold text-slate-700 dark:text-zinc-300"
                                x-text="getEmployee(code)?.name + ' · ' + code"></span>
                        </div>

                        <!-- One A4 page per template page segment -->
                        <template x-for="(pageHtml, pageIdx) in (paginatedLetters[code] || [])" :key="pageIdx">
                            <div class="print-page-wrapper">
                                <!-- Gap between pages of the same letter (not shown on first page) -->
                                <div x-show="pageIdx > 0" class="page-gap no-print"></div>

                                <!-- A4 Page -->
                                <div class="a4-page"
                                    :style="`
                                                                        padding-top: ${margins.top}mm;
                                                                        padding-bottom: ${margins.bottom}mm;
                                                                        padding-left: ${margins.left}mm;
                                                                        padding-right: ${margins.right}mm;
                                                                    `">
                                    <!-- Margin guides (hidden on print) -->
                                    <template x-if="showGuides && pageIdx === 0">
                                        <div>
                                            <div class="margin-guide guide-top no-print"
                                                :style="`height:${margins.top}mm`">
                                                <span class="guide-label" style="left:6px;bottom:2px"
                                                    x-text="`↑ ${margins.top}mm`"></span>
                                            </div>
                                            <div class="margin-guide guide-bottom no-print"
                                                :style="`height:${margins.bottom}mm`">
                                                <span class="guide-label" style="left:6px;top:2px"
                                                    x-text="`↓ ${margins.bottom}mm`"></span>
                                            </div>
                                            <div class="margin-guide guide-left no-print"
                                                :style="`width:${margins.left}mm`">
                                                <span class="guide-label" style="top:6px;right:2px"
                                                    x-text="`← ${margins.left}mm`"></span>
                                            </div>
                                            <div class="margin-guide guide-right no-print"
                                                :style="`width:${margins.right}mm`">
                                                <span class="guide-label" style="top:6px;left:2px"
                                                    x-text="`→ ${margins.right}mm`"></span>
                                            </div>
                                        </div>
                                    </template>

                                    <div class="relative z-10" x-html="pageHtml"></div>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>
            </div>
        </section>

        <!-- ======== RIGHT SIDEBAR – Template Variables ======== -->
        <aside
            x-show="selectedTemplate && selectedTemplate.variables && selectedTemplate.variables.length > 0"
            class="no-print w-full md:w-[320px] xl:w-[350px] bg-white dark:bg-zinc-900 border-l border-slate-200 dark:border-zinc-800 flex flex-col h-full overflow-hidden shrink-0 transition-colors duration-200"
            style="display: none;">
            <div class="sidebar-scroll p-5 flex flex-col gap-4">
                <div class="bg-white dark:bg-zinc-900 rounded-xl border border-slate-200 dark:border-zinc-800 p-4">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 mb-3">Template Variables</p>
                    <div class="space-y-3">
                        <template x-for="v in (selectedTemplate ? selectedTemplate.variables : [])" :key="v.key || v">
                            <div>
                                <label class="block text-[9px] font-bold uppercase text-slate-500 dark:text-zinc-400 mb-0.5"
                                    x-text="formatLabel(v.key || v)"></label>

                                <template x-if="(v.type || 'text') === 'date'">
                                    <input type="date" x-model="customValues[v.key || v]"
                                        class="w-full px-3 py-2 rounded-lg border border-slate-200 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-950 text-xs text-slate-900 dark:text-zinc-100 focus:border-amber-500 outline-none">
                                </template>

                                <template x-if="(v.type || 'text') === 'number'">
                                    <input type="number" x-model="customValues[v.key || v]"
                                        :placeholder="'Enter ' + formatLabel(v.key || v)"
                                        class="w-full px-3 py-2 rounded-lg border border-slate-200 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-950 text-xs text-slate-900 dark:text-zinc-100 focus:border-amber-500 outline-none">
                                </template>

                                <template x-if="(v.type || 'text') === 'boolean'">
                                    <select x-model="customValues[v.key || v]"
                                        class="w-full px-3 py-2 rounded-lg border border-slate-200 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-950 text-xs text-slate-900 dark:text-zinc-100 focus:border-amber-500 outline-none">
                                        <option value="">Select…</option>
                                        <option value="Yes">Yes</option>
                                        <option value="No">No</option>
                                    </select>
                                </template>

                                <template x-if="(v.type || 'text') === 'dropdown'">
                                    <select x-model="customValues[v.key || v]"
                                        class="w-full px-3 py-2 rounded-lg border border-slate-200 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-950 text-xs text-slate-900 dark:text-zinc-100 focus:border-amber-500 outline-none">
                                        <option value="">— Select option —</option>
                                        <template x-for="option in (v.options && v.options.trim() ? v.options.split(',').map(s => s.trim()).filter(s => s) : [])" :key="option">
                                            <option :value="option" x-text="option"></option>
                                        </template>
                                    </select>
                                </template>

                                <template x-if="!v.type || v.type === 'text'">
                                    <input type="text" x-model="customValues[v.key || v]"
                                        :placeholder="'Enter ' + formatLabel(v.key || v)"
                                        class="w-full px-3 py-2 rounded-lg border border-slate-200 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-950 text-xs text-slate-900 dark:text-zinc-100 focus:border-amber-500 outline-none">
                                </template>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </aside>

    </div>
@endsection

@section('scripts')
    <script>
        function generatorState() {
            return {
                templates: @json($templates),
                employees: @json($employees),

                selectedTemplateId: @json($selectedTemplateId ? (int) $selectedTemplateId : ''),
                selectedTemplate: null,
                selectedCodes: [],
                customValues: {},
                search: '',
                showGuides: true,
                margins: {
                    top: 25,
                    bottom: 25,
                    left: 20,
                    right: 20
                },
                paginatedLetters: {},

                init() {
                    if (this.selectedTemplateId) {
                        this.onTemplateChange();
                    }

                    // Watch selectedTemplateId for dropdown changes
                    this.$watch('selectedTemplateId', () => this.onTemplateChange());

                    // Watch state properties reactively to trigger paginator reflows
                    this.$watch('selectedCodes', () => this.updatePaginatedLetters());
                    this.$watch('customValues', () => this.updatePaginatedLetters(), {
                        deep: true
                    });
                    this.$watch('margins', () => this.updatePaginatedLetters(), {
                        deep: true
                    });
                    this.$watch('search', () => this.updatePaginatedLetters());
                },

                onTemplateChange() {
                    this.selectedTemplate = this.templates.find(t => t.id == this.selectedTemplateId) || null;
                    this.customValues = {};

                    if (this.selectedTemplate) {
                        // Inherit template default margins
                        this.margins.top = this.selectedTemplate.margin_top ?? 25;
                        this.margins.bottom = this.selectedTemplate.margin_bottom ?? 25;
                        this.margins.left = this.selectedTemplate.margin_left ?? 20;
                        this.margins.right = this.selectedTemplate.margin_right ?? 20;

                        // Initialise custom value slots
                        (this.selectedTemplate.variables ?? []).forEach(v => {
                            const key = typeof v === 'object' ? v.key : v;
                            const type = typeof v === 'object' ? (v.type || 'text') : 'text';
                            // For dropdown, default to first option if available
                            if (type === 'dropdown' && typeof v === 'object' && v.options && v.options.trim()) {
                                const first = v.options.split(',').map(s => s.trim()).filter(s => s)[0] || '';
                                this.customValues[key] = first;
                            } else {
                                this.customValues[key] = '';
                            }
                        });
                    }
                    this.updatePaginatedLetters();
                },

                updatePaginatedLetters() {
                    if (!this.selectedTemplateId || this.selectedCodes.length === 0) {
                        this.paginatedLetters = {};
                        return;
                    }
                    // Use $nextTick to ensure variables are fully bound before rendering offscreen DOM
                    this.$nextTick(() => {
                        const pagesMap = {};
                        this.selectedCodes.forEach(code => {
                            const full = this.renderLetter(code);
                            pagesMap[code] = this.paginateHtml(full, this.margins);
                        });
                        this.paginatedLetters = pagesMap;
                    });
                },

                paginateHtml(html, margins) {
                    // Create a temp element offscreen to load the full document
                    const temp = document.createElement('div');
                    temp.style.width = '210mm';
                    temp.style.position = 'absolute';
                    temp.style.visibility = 'hidden';
                    temp.style.left = '-9999px';
                    temp.innerHTML = html;
                    document.body.appendChild(temp);

                    const pageHeightPx = 297 * 3.7795275591;

                    // If it already has <!-- PAGE_BREAK -->, respect it
                    if (html.includes('<!-- PAGE_BREAK -->')) {
                        const segments = html.split(/\n?<!--\s*PAGE_BREAK\s*-->\n?/);
                        document.body.removeChild(temp);
                        return segments.filter(s => s.trim() !== '');
                    }

                    // Otherwise, dynamically measure children to fit fixed A4 heights
                    const testDiv = document.createElement('div');
                    testDiv.style.width = '210mm';
                    testDiv.style.paddingTop = margins.top + 'mm';
                    testDiv.style.paddingBottom = margins.bottom + 'mm';
                    testDiv.style.paddingLeft = margins.left + 'mm';
                    testDiv.style.paddingRight = margins.right + 'mm';
                    testDiv.style.boxSizing = 'border-box';
                    testDiv.style.position = 'absolute';
                    testDiv.style.visibility = 'hidden';
                    testDiv.style.left = '-9999px';
                    document.body.appendChild(testDiv);

                    const children = Array.from(temp.childNodes);
                    const pages = [];
                    let currentBatch = [];

                    for (let i = 0; i < children.length; i++) {
                        const child = children[i].cloneNode(true);
                        currentBatch.push(child);
                        testDiv.innerHTML = '';
                        currentBatch.forEach(node => testDiv.appendChild(node.cloneNode(true)));

                        // Check with a tiny 4px buffer
                        if (testDiv.scrollHeight > pageHeightPx + 4) {
                            currentBatch.pop();
                            testDiv.innerHTML = '';
                            currentBatch.forEach(node => testDiv.appendChild(node.cloneNode(true)));
                            pages.push(testDiv.innerHTML);
                            currentBatch = [child];
                        }
                    }
                    if (currentBatch.length > 0) {
                        testDiv.innerHTML = '';
                        currentBatch.forEach(node => testDiv.appendChild(node.cloneNode(true)));
                        pages.push(testDiv.innerHTML);
                    }

                    document.body.removeChild(temp);
                    document.body.removeChild(testDiv);
                    return pages;
                },

                get filteredEmployees() {
                    const q = this.search.toLowerCase();
                    if (!q) return this.employees;
                    return this.employees.filter(e =>
                        e.name.toLowerCase().includes(q) || e.employee_code.toLowerCase().includes(q)
                    );
                },

                getEmployee(code) {
                    return this.employees.find(e => e.employee_code === code);
                },

                toggleEmployee(code) {
                    const idx = this.selectedCodes.indexOf(code);
                    if (idx > -1) {
                        this.selectedCodes.splice(idx, 1);
                    } else {
                        this.selectedCodes.push(code);
                    }
                },

                selectAll() {
                    this.selectedCodes = this.filteredEmployees.map(e => e.employee_code);
                },

                clearAll() {
                    this.selectedCodes = [];
                },

                renderLetter(code) {
                    if (!this.selectedTemplate) return '';
                    const emp = this.getEmployee(code);
                    if (!emp) return '';

                    let html = this.selectedTemplate.content || '';

                    // Replace employee_ placeholders
                    html = html.replace(/\{\{\s*employee_([a-zA-Z0-9_]+)\s*\}\}/gi, (match, k) => {
                        k = k.toLowerCase();
                        if (k === 'name') {
                            const name = emp.name ?? '';
                            const gender = emp.gender ?? '';
                            let prefix = '';
                            if (gender === 'Male') {
                                prefix = 'Mr. ';
                            } else if (gender === 'Female') {
                                prefix = 'Miss. ';
                            }
                            return prefix + name;
                        }
                        if (k === 'employee_code') return emp.employee_code ?? '';
                        if (k === 'gender') return emp.gender ?? '';
                        if (k === 'department') return emp.department?.name ?? '';
                        if (k === 'designation') return emp.designation?.name ?? '';
                        if (k === 'email') return emp.email ?? '';
                        if (k === 'contact_number') return emp.contact_number ?? '';
                        if ((k === 'join_date' || k === 'dob_ad') && emp[k]) {
                            const d = new Date(emp[k]);
                            return isNaN(d) ? emp[k] : d.toLocaleDateString('en-US', {
                                day: 'numeric',
                                month: 'long',
                                year: 'numeric'
                            });
                        }
                        return emp[k] !== undefined ? emp[k] : (emp['employee_' + k] !== undefined ? emp[
                            'employee_' + k] : match);
                    });

                    // Replace custom variable placeholders
                    html = html.replace(/\{\{\s*([a-zA-Z0-9_]+)\s*\}\}/g, (match, k) => {
                        if (k.toLowerCase().startsWith('employee_')) return match;
                        return this.customValues[k] !== undefined ? this.customValues[k] : '';
                    });

                    return html;
                },

                formatLabel(str) {
                    return str.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
                },

            };
        }
    </script>
@endsection
