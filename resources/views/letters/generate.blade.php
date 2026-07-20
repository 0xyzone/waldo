@extends('letters.layout')

@section('title', 'Generate Letters')

@section('styles')
<style>
    /* Document workspace */
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

    /* Page container */
    .print-page-wrapper {
        margin-bottom: 32px;
    }
    .page-sheet {
        position: relative;
        background: #ffffff;
        width: 210mm;
        height: 297mm;
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
        height: 100%;
        box-sizing: border-box;
        color: #1e293b;
        font-family: 'Times New Roman', Times, serif;
        font-size: 12pt;
        line-height: 1.6;
        word-break: break-word;
        overflow-wrap: break-word;
        overflow: hidden;
    }
    .dark .a4-page {
        color: #f1f5f9;
    }

    .a4-page p {
        margin-top: 0;
        margin-bottom: 8pt;
    }

    .a4-page h1 {
        font-size: 22pt;
        font-weight: bold;
        margin-top: 14pt;
        margin-bottom: 6pt;
    }

    .a4-page h2 {
        font-size: 18pt;
        font-weight: bold;
        margin-top: 12pt;
        margin-bottom: 4pt;
    }

    .a4-page h3 {
        font-size: 14pt;
        font-weight: bold;
        margin-top: 10pt;
        margin-bottom: 4pt;
    }

    .a4-page ul {
        list-style-type: disc;
        padding-left: 24pt;
        margin-bottom: 8pt;
    }

    .a4-page ol {
        list-style-type: decimal;
        padding-left: 24pt;
        margin-bottom: 8pt;
    }

    .a4-page table {
        width: 100%;
        border-collapse: collapse;
        margin: 12pt 0;
    }

    .a4-page td, .a4-page th {
        border: 1px solid #cbd5e1;
        padding: 8px 12px;
    }
    .dark .a4-page td, .dark .a4-page th {
        border-color: #3f3f46;
    }

    /* Page spacing */
    .page-gap {
        width: 210mm;
        height: 32px;
        flex-shrink: 0;
    }

    /* Guides styles */
    .margin-guide {
        position: absolute;
        border: 1px dashed rgba(245, 158, 11, 0.35);
        pointer-events: none;
        box-sizing: border-box;
    }
    .guide-top { top: 0; left: 0; right: 0; border-bottom-width: 1px; border-top-width: 0; border-left-width: 0; border-right-width: 0; }
    .guide-bottom { bottom: 0; left: 0; right: 0; border-top-width: 1px; border-bottom-width: 0; border-left-width: 0; border-right-width: 0; }
    .guide-left { left: 0; top: 0; bottom: 0; border-right-width: 1px; border-left-width: 0; border-top-width: 0; border-bottom-width: 0; }
    .guide-right { right: 0; top: 0; bottom: 0; border-left-width: 1px; border-right-width: 0; border-top-width: 0; border-bottom-width: 0; }
    
    .guide-label {
        position: absolute;
        font-family: monospace;
        font-size: 9px;
        font-weight: bold;
        color: #d97706;
        background: #fef3c7;
        padding: 1px 3px;
        border-radius: 3px;
        border: 1px solid #fcd34d;
        z-index: 10;
    }

    /* Dynamic print styles using CSS variables */
    @media print {
        @page {
            size: A4 portrait;
            margin: 0 !important;
        }
        html, body, main, main > div, section, .preview-workspace, #preview-container, #preview-container > div {
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
            margin: 0 !important;
            padding: 0 !important;
        }
        #preview-container > div:last-child .print-page-wrapper:last-of-type {
            page-break-after: auto !important;
            break-after: auto !important;
        }
        .page-sheet {
            width: 210mm !important;
            height: 297mm !important;
            margin: 0 !important;
            padding: 0 !important;
            border: none !important;
            box-shadow: none !important;
            box-sizing: border-box !important;
        }
        .a4-page {
            width: 100% !important;
            height: 100% !important;
            box-sizing: border-box !important;
            background: white !important;
            overflow: hidden !important;
        }
    }
</style>
@endsection

@section('content')
<div x-data="generatorState()" x-init="init()"
     :style="`
         --page-margin-top: ${margins.top}mm;
         --page-margin-bottom: ${margins.bottom}mm;
         --page-margin-left: ${margins.left}mm;
         --page-margin-right: ${margins.right}mm;
     `"
     class="h-full flex flex-col md:flex-row overflow-hidden">
     
    <!-- Left Sidebar: Selection controls & inputs -->
    <aside class="no-print w-full md:w-96 bg-white dark:bg-zinc-900 border-r border-slate-200 dark:border-zinc-800 flex flex-col overflow-hidden shrink-0 shadow-sm z-20">
        
        <div class="p-6 border-b border-slate-200 dark:border-zinc-800 space-y-4 shrink-0">
            <div class="flex items-center justify-between">
                <h2 class="text-sm font-bold text-slate-800 dark:text-zinc-200 uppercase tracking-wider">Letter Generation</h2>
                <!-- Print Trigger Button -->
                <button type="button" @click="window.print()" :disabled="selectedCodes.length === 0 || !selectedTemplateId"
                        class="px-4 py-2 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 disabled:from-slate-300 disabled:to-slate-400 disabled:cursor-not-allowed text-white text-xs font-bold rounded-xl shadow-md tracking-wide transition-all active:scale-95 cursor-pointer">
                    <i class="fa-solid fa-print mr-1"></i> Print
                </button>
            </div>
            
            <!-- Select Template -->
            <div class="space-y-1.5">
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400">Select Template</label>
                <select x-model="selectedTemplateId" 
                        class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-950 rounded-xl text-sm font-semibold text-slate-850 dark:text-zinc-200 focus:outline-none focus:border-amber-500 transition-all">
                    <option value="">— Select Template —</option>
                    @foreach($templates as $t)
                        <option value="{{ $t->id }}">{{ $t->title }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto p-6 space-y-6">
            
            <!-- Employee Selector -->
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400">Target Employees</label>
                    <span class="text-[10px] bg-slate-100 dark:bg-zinc-800 text-slate-500 dark:text-zinc-400 px-2 py-0.5 rounded font-bold" 
                          x-text="selectedCodes.length + ' selected'"></span>
                </div>
                
                <!-- Search bar -->
                <input type="text" x-model="search" placeholder="Search by name or employee code..." 
                       class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-950 rounded-xl text-xs text-slate-850 dark:text-zinc-200 focus:outline-none focus:border-amber-500 transition-all">
                
                <!-- Checkboxes list -->
                <div class="border border-slate-200 dark:border-zinc-800 rounded-xl max-h-48 overflow-y-auto divide-y divide-slate-100 dark:divide-zinc-800/80 bg-slate-50 dark:bg-zinc-950 shadow-inner">
                    <template x-for="e in filteredEmployees" :key="e.employee_code">
                        <label class="flex items-center gap-3 px-3 py-2.5 hover:bg-slate-100 dark:hover:bg-zinc-800/50 cursor-pointer transition-colors">
                            <input type="checkbox" :value="e.employee_code" x-model="selectedCodes" 
                                   class="rounded border-slate-300 text-amber-500 focus:ring-amber-500">
                            <div class="flex flex-col">
                                <span class="text-xs font-bold text-slate-800 dark:text-zinc-200" x-text="e.employee_code + ' | ' + e.name"></span>
                                <span class="text-[10px] text-slate-400 dark:text-zinc-500" x-text="(e.designation || 'Staff') + ' · ' + (e.department || 'N/A')"></span>
                            </div>
                        </label>
                    </template>
                </div>
            </div>

            <!-- Custom Variables Form Fields -->
            <div x-show="selectedTemplate && selectedTemplate.variables && selectedTemplate.variables.length > 0" class="space-y-4 border-t border-slate-100 dark:border-zinc-800/80 pt-6">
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400">Custom Template Variables</label>
                
                <div class="space-y-4">
                    <template x-for="v in (selectedTemplate ? selectedTemplate.variables : [])" :key="v.key || v">
                        <div class="space-y-1.5">
                            <label class="block text-[11px] font-bold text-slate-500 dark:text-zinc-400 uppercase tracking-wider" 
                                   x-text="formatLabel(v.key || v)"></label>
                            
                            <!-- Date Field -->
                            <template x-if="(v.type || 'text') === 'date'">
                                <input type="date" x-model="customValues[v.key || v]" 
                                       class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-950 rounded-xl text-xs text-slate-850 dark:text-zinc-200 focus:outline-none focus:border-amber-500">
                            </template>

                            <!-- Number Field -->
                            <template x-if="(v.type || 'text') === 'number'">
                                <input type="number" x-model="customValues[v.key || v]" :placeholder="'Enter ' + formatLabel(v.key || v)" 
                                       class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-950 rounded-xl text-xs text-slate-850 dark:text-zinc-200 focus:outline-none focus:border-amber-500">
                            </template>

                            <!-- Boolean Yes/No Dropdown -->
                            <template x-if="(v.type || 'text') === 'boolean'">
                                <select x-model="customValues[v.key || v]" 
                                        class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-950 rounded-xl text-xs text-slate-850 dark:text-zinc-200 focus:outline-none focus:border-amber-500">
                                    <option value="">Select Option</option>
                                    <option value="Yes">Yes</option>
                                    <option value="No">No</option>
                                </select>
                            </template>

                            <!-- Dropdown Field -->
                            <template x-if="(v.type || 'text') === 'dropdown'">
                                <select x-model="customValues[v.key || v]" 
                                        class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-950 rounded-xl text-xs text-slate-850 dark:text-zinc-200 focus:outline-none focus:border-amber-500">
                                    <option value="">— Select Option —</option>
                                    <template x-for="option in (v.options && v.options.trim() ? v.options.split(',').map(s => s.trim()).filter(s => s) : [])" :key="option">
                                        <option :value="option" x-text="option"></option>
                                    </template>
                                </select>
                            </template>

                            <!-- Default Text Field -->
                            <template x-if="!v.type || v.type === 'text'">
                                <input type="text" x-model="customValues[v.key || v]" :placeholder="'Enter ' + formatLabel(v.key || v)" 
                                       class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-950 rounded-xl text-xs text-slate-850 dark:text-zinc-200 focus:outline-none focus:border-amber-500">
                            </template>
                        </div>
                    </template>
                </div>
            </div>
            
            <!-- Margin Guides Toggle -->
            <div class="border-t border-slate-100 dark:border-zinc-800/80 pt-6">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" x-model="showGuides" class="rounded border-slate-300 text-amber-500 focus:ring-amber-500">
                    <span class="text-xs font-bold text-slate-600 dark:text-zinc-400">Show page margin guides</span>
                </label>
            </div>

        </div>
    </aside>

    <!-- Center Preview Area -->
    <div class="preview-workspace flex-1">
        
        <!-- Empty Preview State -->
        <div x-show="selectedCodes.length === 0 || !selectedTemplateId" 
             class="flex flex-col items-center justify-center p-16 text-center max-w-sm my-auto space-y-6">
            <div class="w-20 h-20 rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 flex items-center justify-center text-4xl shadow-sm">📄</div>
            <div class="space-y-1.5">
                <h2 class="text-lg font-bold text-slate-800 dark:text-zinc-200">Letter Preview</h2>
                <p class="text-xs text-slate-400 leading-relaxed">Select a template and choose one or more target employees from the sidebar to preview and print generated letters.</p>
            </div>
        </div>

        <!-- Letters Preview Container -->
        <div id="preview-container" x-show="selectedCodes.length > 0 && selectedTemplateId" class="space-y-8 flex flex-col items-center">
            
            <template x-for="code in selectedCodes" :key="code">
                <div class="space-y-2">
                    <!-- Employee label tag -->
                    <div class="flex items-center gap-2 mb-2 no-print self-start bg-amber-500/10 border border-amber-500/20 text-amber-800 dark:text-amber-400 px-3 py-1.5 rounded-xl text-xs font-bold shadow-xs">
                        <i class="fa-solid fa-circle-user text-amber-500"></i>
                        <span x-text="getEmployee(code)?.name + ' (' + code + ')'"></span>
                    </div>

                    <!-- Rendering each letter page -->
                    <template x-for="(pageHtml, pageIdx) in (paginatedLetters[code] || [])" :key="pageIdx">
                        <div class="print-page-wrapper">
                            <!-- Gap between pages on screen -->
                            <div x-show="pageIdx > 0" class="page-gap no-print"></div>

                            <div class="page-sheet">
                                <div class="a4-page"
                                     :style="`
                                         padding-top: var(--page-margin-top);
                                         padding-bottom: var(--page-margin-bottom);
                                         padding-left: var(--page-margin-left);
                                         padding-right: var(--page-margin-right);
                                     `"
                                     x-html="pageHtml">
                                </div>

                                <!-- Margin guides (not printed) -->
                                <template x-if="showGuides && pageIdx === 0">
                                    <div>
                                        <div class="margin-guide guide-top no-print" :style="`height:${margins.top}mm`">
                                            <span class="guide-label" style="left:6px;bottom:2px" x-text="`↑ ${margins.top}mm`"></span>
                                        </div>
                                        <div class="margin-guide guide-bottom no-print" :style="`height:${margins.bottom}mm`">
                                            <span class="guide-label" style="left:6px;top:2px" x-text="`↓ ${margins.bottom}mm`"></span>
                                        </div>
                                        <div class="margin-guide guide-left no-print" :style="`width:${margins.left}mm`">
                                            <span class="guide-label" style="top:6px;right:2px" x-text="`← ${margins.left}mm`"></span>
                                        </div>
                                        <div class="margin-guide guide-right no-print" :style="`width:${margins.right}mm`">
                                            <span class="guide-label" style="top:6px;left:2px" x-text="`→ ${margins.right}mm`"></span>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </template>

        </div>

    </div>

    <!-- Offscreen sandbox container for paginating generated letters -->
    <div id="sandbox-container" class="no-print"
         :style="`
             position: absolute; left: -9999px; top: -9999px;
             width: 210mm;
             --page-margin-top: ${margins.top}mm;
             --page-margin-bottom: ${margins.bottom}mm;
             --page-margin-left: ${margins.left}mm;
             --page-margin-right: ${margins.right}mm;
         `">
    </div>

</div>
@endsection

@section('scripts')
<script>
function generatorState() {
    return {
        templates: @json($templates),
        employees: @json($employees),

        selectedTemplateId: @json($selectedTemplateId ? (string) $selectedTemplateId : ''),
        selectedTemplate: null,
        selectedCodes: [],
        customValues: {},
        search: '',
        showGuides: true,
        margins: { top: 25, bottom: 25, left: 20, right: 20 },
        paginatedLetters: {},

        init() {
            if (this.selectedTemplateId) {
                this.onTemplateChange();
            }

            this.$watch('selectedTemplateId', () => this.onTemplateChange());
            this.$watch('selectedCodes', () => this.updatePaginatedLetters());
            this.$watch('customValues', () => this.updatePaginatedLetters(), { deep: true });
            this.$watch('margins', () => this.updatePaginatedLetters(), { deep: true });
            this.$watch('search', () => this.updatePaginatedLetters());

            if (document.fonts) {
                document.fonts.ready.then(() => {
                    this.updatePaginatedLetters();
                });
            }
        },

        onTemplateChange() {
            this.selectedTemplate = this.templates.find(t => t.id == this.selectedTemplateId) || null;
            this.customValues = {};

            if (this.selectedTemplate) {
                this.margins.top    = this.selectedTemplate.margin_top    ?? 25;
                this.margins.bottom = this.selectedTemplate.margin_bottom ?? 25;
                this.margins.left   = this.selectedTemplate.margin_left   ?? 20;
                this.margins.right  = this.selectedTemplate.margin_right  ?? 20;

                (this.selectedTemplate.variables ?? []).forEach(v => {
                    const key  = typeof v === 'object' ? v.key : v;
                    const type = typeof v === 'object' ? (v.type || 'text') : 'text';
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

        createSandboxPage(sandbox) {
            const m = this.margins;
            const page = document.createElement('div');
            page.className = 'page-sheet';
            
            const a4 = document.createElement('div');
            a4.className = 'a4-page';
            a4.style.paddingTop = m.top + 'mm';
            a4.style.paddingBottom = m.bottom + 'mm';
            a4.style.paddingLeft = m.left + 'mm';
            a4.style.paddingRight = m.right + 'mm';
            
            page.appendChild(a4);
            sandbox.appendChild(page);
            return page;
        },

        splitSandboxNode(node, usableBottom, pageRect, forceFit = false) {
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
                    if (forceFit || nodeBottom <= usableBottom) {
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

                    const result = this.splitSandboxNode(child, usableBottom, pageRect, childForceFit);
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
        },

        updatePaginatedLetters() {
            if (!this.selectedTemplateId || this.selectedCodes.length === 0) {
                this.paginatedLetters = {};
                return;
            }
            this.$nextTick(() => {
                const pagesMap = {};
                const sandbox = document.getElementById('sandbox-container');
                if (!sandbox) return;

                this.selectedCodes.forEach(code => {
                    let full = this.renderLetter(code);
                    
                    full = full.replace(/<!--\s*PAGE_BREAK\s*-->/gi, '<div class="page-break-marker" contenteditable="false"></div>');
                    
                    const temp = document.createElement('div');
                    temp.innerHTML = full;
                    
                    sandbox.innerHTML = '';
                    
                    let currentPage = this.createSandboxPage(sandbox);
                    let currentContent = currentPage.querySelector('.a4-page');
                    
                    let pageH = currentPage.clientHeight;
                    let marginB = this.margins.bottom * (96/25.4);
                    let usableBottom = pageH - marginB;
                    
                    const pagesHTML = [];
                    
                    while (temp.firstChild) {
                        const node = temp.firstChild;
                        
                        if (node.nodeType === 1 && node.classList.contains('page-break-marker')) {
                            pagesHTML.push(currentContent.innerHTML);
                            
                            currentPage = this.createSandboxPage(sandbox);
                            currentContent = currentPage.querySelector('.a4-page');
                            
                            const newPageH = currentPage.clientHeight;
                            const newMarginB = this.margins.bottom * (96/25.4);
                            usableBottom = newPageH - newMarginB;
                            
                            node.remove();
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
                                const result = this.splitSandboxNode(node, usableBottom, pageRect, isPageEmpty);
                                
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
                                
                                pagesHTML.push(currentContent.innerHTML);
                                
                                currentPage = this.createSandboxPage(sandbox);
                                currentContent = currentPage.querySelector('.a4-page');
                                
                                const newPageH = currentPage.clientHeight;
                                const newMarginB = this.margins.bottom * (96/25.4);
                                usableBottom = newPageH - newMarginB;
                            }
                        }
                    }
                    
                    pagesHTML.push(currentContent.innerHTML);
                    pagesMap[code] = pagesHTML;
                });
                
                this.paginatedLetters = pagesMap;
                sandbox.innerHTML = '';
            });
        },

        renderLetter(code) {
            if (!this.selectedTemplate) return '';
            const emp = this.getEmployee(code);
            if (!emp) return '';

            let html = this.selectedTemplate.content || '';

             // Replace prebuilt employee variables — use character class [{}]{2} to avoid Blade parsing
            const prebuilts = {
                employee_name:               emp.name || '',
                employee_employee_code:      emp.employee_code || '',
                employee_department:         emp.department || '',
                employee_designation:        emp.designation || '',
                employee_gender:             emp.gender || '',
                employee_join_date:          this.formatDate(emp.join_date),
                employee_contact_number:     emp.contact_number || '',
                employee_email:              emp.email || '',
                employee_citizenship_number: emp.citizenship_number || '',
                employee_ssid:               emp.ssid || '',
                employee_dob_ad:             this.formatDate(emp.dob_ad),
                employee_marital_status:     emp.marital_status || '',
                employee_tips_amount:        emp.tips_amount || '0',
                employee_tips_status:        emp.tips_status || '',
                employee_his_her:            this.getHisHer(emp.gender, false),
                employee_he_she:             this.getHeShe(emp.gender, false),
                employee_his_her_cap:        this.getHisHer(emp.gender, true),
                employee_he_she_cap:         this.getHeShe(emp.gender, true),
            };

            Object.entries(prebuilts).forEach(([k, val]) => {
                const rx = new RegExp('[{]{2}\\s*' + k + '\\s*[}]{2}', 'g');
                html = html.replace(rx, val);
            });

            // Replace custom template variables
            (this.selectedTemplate.variables || []).forEach(v => {
                const key  = typeof v === 'object' ? v.key : v;
                const type = typeof v === 'object' ? (v.type || 'text') : 'text';
                let val = this.customValues[key] || '';

                if (type === 'date' && val) {
                    val = this.formatDate(val);
                }

                const rx = new RegExp('[{]{2}\\s*' + key + '\\s*[}]{2}', 'g');
                html = html.replace(rx, val);
            });

            return html;
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
            const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            const month  = months[date.getMonth()];
            const year   = date.getFullYear();

            let suffix = 'th';
            if (day === 1 || day === 21 || day === 31) suffix = 'st';
            else if (day === 2 || day === 22)          suffix = 'nd';
            else if (day === 3 || day === 23)          suffix = 'rd';

            return day + suffix + ' ' + month + ' ' + year;
        },

        formatLabel(key) {
            return key.replace(/_/g, ' ');
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
        }
    };
}
</script>
@endsection

