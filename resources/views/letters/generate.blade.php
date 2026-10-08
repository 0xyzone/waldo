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
        min-height: 297mm;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
        border: 1px solid #e2e8f0;
        box-sizing: border-box;
    }
    .dark .page-sheet {
        background: #ffffff;
        border-color: rgba(255, 255, 255, 0.1);
        box-shadow: 0 12px 36px -8px rgba(0, 0, 0, 0.75);
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
        color: #1e293b;
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
        border-color: #cbd5e1;
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

    /* Mini Rich Text Variable Editor */
    .rtv-tb-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 22px;
        height: 22px;
        padding: 0 4px;
        font-size: 11px;
        border-radius: 4px;
        color: #475569;
        cursor: pointer;
        transition: background 0.1s;
    }
    .rtv-tb-btn:hover {
        background: #e2e8f0;
    }
    .dark .rtv-tb-btn {
        color: #a1a1aa;
    }
    .dark .rtv-tb-btn:hover {
        background: #3f3f46;
    }
    .rtv-editor-content:focus {
        outline: none;
    }
    .rtv-editor-content p {
        margin: 0 0 4px 0;
    }
    .rtv-editor-content ul {
        list-style: disc;
        padding-left: 18px;
        margin: 2px 0;
    }
    .rtv-editor-content ol {
        list-style: decimal;
        padding-left: 18px;
        margin: 2px 0;
    }
    .rtv-size-select {
        height: 22px;
        padding: 0 4px;
        font-size: 10px;
        border-radius: 4px;
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        color: #334155;
        cursor: pointer;
        outline: none;
    }
    .dark .rtv-size-select {
        border-color: #52525b;
        color: #e4e4e7;
        background: #27272a;
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
                <div class="flex items-center gap-2">
                    <button type="button" @click="saveToHistory(false)" :disabled="selectedCodes.length === 0 || !selectedTemplateId || isSaving"
                            class="px-3 py-2 bg-slate-100 dark:bg-zinc-800 hover:bg-slate-200 dark:hover:bg-zinc-700 disabled:opacity-50 text-slate-700 dark:text-zinc-300 text-xs font-bold rounded-xl shadow-xs transition-all active:scale-95 cursor-pointer flex items-center gap-1.5"
                            title="Save generated letters to history">
                        <i class="fa-solid fa-floppy-disk text-amber-500"></i> Save
                    </button>
                    <button type="button" @click="window.print()" :disabled="selectedCodes.length === 0 || !selectedTemplateId"
                            class="px-3.5 py-2 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 disabled:from-slate-300 disabled:to-slate-400 disabled:cursor-not-allowed text-white text-xs font-bold rounded-xl shadow-md tracking-wide transition-all active:scale-95 cursor-pointer flex items-center gap-1.5">
                        <i class="fa-solid fa-print"></i> Print
                    </button>
                </div>
            </div>
            <div x-show="saveMessage" x-transition class="text-xs font-bold text-emerald-600 dark:text-emerald-400 flex items-center gap-1">
                <i class="fa-solid fa-circle-check"></i> <span x-text="saveMessage"></span>
            </div>
            
            <!-- Select Template -->
            <div class="space-y-1.5">
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-400">Select Template</label>
                <div class="flex items-center gap-2">
                    <select x-model="selectedTemplateId" 
                            class="flex-1 min-w-0 px-3 py-2 border border-slate-200 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-950 rounded-xl text-sm font-semibold text-slate-850 dark:text-zinc-200 focus:outline-none focus:border-amber-500 transition-all">
                        <option value="">— Select Template —</option>
                        @foreach($templates as $t)
                            <option value="{{ $t->id }}">{{ $t->title }}</option>
                        @endforeach
                    </select>
                    <a x-show="selectedTemplateId"
                       :href="selectedTemplateId ? '{{ url('letters') }}/' + selectedTemplateId + '/edit' : '#'"
                       target="_blank"
                       class="shrink-0 flex items-center gap-1.5 px-2.5 py-2 bg-slate-100 dark:bg-zinc-800 hover:bg-amber-50 dark:hover:bg-amber-950/40 border border-slate-200 dark:border-zinc-700 hover:border-amber-400 dark:hover:border-amber-600 text-slate-500 dark:text-zinc-400 hover:text-amber-600 dark:hover:text-amber-400 rounded-xl text-xs font-bold transition-all"
                       title="Edit this template">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="flex-1 flex flex-col overflow-y-auto p-6 space-y-4">
            
            <!-- Employee Selector -->
            <div class="flex flex-col min-h-0 space-y-3 shrink-0">
                <div class="flex items-center justify-between shrink-0">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-400">Target Employees</label>
                    <div class="flex items-center gap-2">
                        <button type="button"
                                x-show="selectedCodes.length > 0"
                                @click="deselectAll()"
                                class="text-[10px] text-rose-500 hover:text-rose-700 dark:hover:text-rose-400 font-bold hover:underline cursor-pointer flex items-center gap-1 transition-colors">
                            <i class="fa-solid fa-xmark text-[9px]"></i> Deselect All
                        </button>
                        <button type="button"
                                x-show="filteredEmployees.length > 0 && selectedCodes.length < filteredEmployees.length"
                                @click="selectAllFiltered()"
                                class="text-[10px] text-amber-600 dark:text-amber-400 hover:underline font-bold cursor-pointer transition-colors">
                            Select All
                        </button>
                        <span class="text-[10px] bg-slate-100 dark:bg-zinc-800 text-slate-500 dark:text-zinc-400 px-2 py-0.5 rounded font-bold" 
                              x-text="selectedCodes.length + ' selected'"></span>
                    </div>
                </div>
                
                <!-- Search bar & Filter Toggle -->
                <div class="space-y-2 shrink-0">
                    <div class="flex items-center gap-1.5">
                        <div class="relative flex-1">
                            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
                            <input type="text" x-model="search" placeholder="Search name or code..." 
                                   class="w-full pl-8 pr-7 py-2 border border-slate-200 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-950 rounded-xl text-xs text-slate-850 dark:text-zinc-200 focus:outline-none focus:border-amber-500 transition-all">
                            <button type="button" x-show="search" @click="search = ''"
                                    class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-zinc-200 cursor-pointer text-xs">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>
                        <button type="button" @click="showFilters = !showFilters"
                                :class="{'bg-amber-500 text-white border-amber-500': showFilters || hasActiveFilters, 'bg-slate-50 dark:bg-zinc-950 text-slate-600 dark:text-zinc-300 border-slate-200 dark:border-zinc-700': !showFilters && !hasActiveFilters}"
                                class="px-2.5 py-2 border rounded-xl text-xs font-semibold hover:border-amber-400 flex items-center gap-1.5 transition-all cursor-pointer relative"
                                title="Filter options">
                            <i class="fa-solid fa-filter text-xs"></i>
                            <span x-show="activeFilterCount > 0" class="w-1.5 h-1.5 rounded-full bg-amber-400 absolute top-1 right-1" :class="{'bg-white': showFilters || hasActiveFilters}"></span>
                        </button>
                    </div>

                    <!-- Filter Dropdowns Tray -->
                    <div x-show="showFilters" x-transition class="p-3 bg-slate-100/70 dark:bg-zinc-950/70 border border-slate-200 dark:border-zinc-800 rounded-xl space-y-2 text-xs">
                        <div class="flex items-center justify-between pb-1 border-b border-slate-200/50 dark:border-zinc-800">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Filter By</span>
                            <button type="button" x-show="hasActiveFilters" @click="resetFilters()"
                                    class="text-[10px] text-rose-500 hover:underline font-bold cursor-pointer">
                                Reset Filters
                            </button>
                        </div>
                        <div class="grid grid-cols-1 gap-2">
                            <!-- Department Filter -->
                            <div>
                                <label class="block text-[9px] font-bold text-slate-500 dark:text-zinc-400 uppercase mb-0.5">Department</label>
                                <select x-model="filterDepartment"
                                        class="w-full px-2 py-1.5 border border-slate-200 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-900 text-xs text-slate-800 dark:text-zinc-200 focus:border-amber-500 outline-none">
                                    <option value="">All Departments</option>
                                    <template x-for="dept in departmentOptions" :key="dept">
                                        <option :value="dept" x-text="dept"></option>
                                    </template>
                                </select>
                            </div>
                            <!-- Designation Filter -->
                            <div>
                                <label class="block text-[9px] font-bold text-slate-500 dark:text-zinc-400 uppercase mb-0.5">Designation</label>
                                <select x-model="filterDesignation"
                                        class="w-full px-2 py-1.5 border border-slate-200 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-900 text-xs text-slate-800 dark:text-zinc-200 focus:border-amber-500 outline-none">
                                    <option value="">All Designations</option>
                                    <template x-for="desig in designationOptions" :key="desig">
                                        <option :value="desig" x-text="desig"></option>
                                    </template>
                                </select>
                            </div>
                            <!-- Status & Gender Filter in 2 cols -->
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-[9px] font-bold text-slate-500 dark:text-zinc-400 uppercase mb-0.5">Status</label>
                                    <select x-model="filterStatus"
                                            class="w-full px-2 py-1.5 border border-slate-200 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-900 text-xs text-slate-800 dark:text-zinc-200 focus:border-amber-500 outline-none">
                                        <option value="">All Statuses</option>
                                        <template x-for="st in statusOptions" :key="st">
                                            <option :value="st" x-text="st"></option>
                                        </template>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-[9px] font-bold text-slate-500 dark:text-zinc-400 uppercase mb-0.5">Gender</label>
                                    <select x-model="filterGender"
                                            class="w-full px-2 py-1.5 border border-slate-200 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-900 text-xs text-slate-850 dark:text-zinc-200 focus:border-amber-500 outline-none">
                                        <option value="">All Genders</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Count Info -->
                <div class="flex items-center justify-between text-[10px] text-slate-400 px-0.5">
                    <span x-text="'Showing ' + filteredEmployees.length + ' of ' + employees.length + ' employees'"></span>
                    <span x-show="hasActiveFilters" class="text-amber-600 dark:text-amber-400 font-semibold">• Filters active</span>
                </div>
                
                <!-- Checkboxes list -->
                <div class="h-44 overflow-y-auto border border-slate-200 dark:border-zinc-800 rounded-xl divide-y divide-slate-100 dark:divide-zinc-800/80 bg-slate-50 dark:bg-zinc-950 shadow-inner shrink-0">
                    <template x-for="e in filteredEmployees" :key="e.employee_code">
                        <label class="flex items-center gap-3 px-3 py-2.5 hover:bg-slate-100 dark:hover:bg-zinc-800/50 cursor-pointer transition-colors">
                            <input type="checkbox" :value="e.employee_code" x-model="selectedCodes" 
                                    class="rounded border-slate-300 text-amber-500 focus:ring-amber-500">
                            <div class="flex flex-col min-w-0 flex-1">
                                <div class="flex items-center justify-between gap-1">
                                    <span class="text-xs font-bold text-slate-800 dark:text-zinc-200 truncate" x-text="e.employee_code + ' | ' + e.name"></span>
                                    <span x-show="e.employee_status"
                                          :class="{
                                              'text-emerald-600 dark:text-emerald-400 bg-emerald-500/10': (e.employee_status || '').toLowerCase() === 'active',
                                              'text-slate-400 bg-slate-200/50': (e.employee_status || '').toLowerCase() !== 'active'
                                          }"
                                          class="text-[9px] font-bold px-1.5 py-0.2 rounded shrink-0"
                                          x-text="e.employee_status"></span>
                                </div>
                                <span class="text-[10px] text-slate-400 dark:text-zinc-500 truncate" x-text="(e.designation ? (e.designation.name || e.designation) : 'Staff') + ' · ' + (e.department ? (e.department.name || e.department) : 'N/A')"></span>
                            </div>
                        </label>
                    </template>
                    <div x-show="filteredEmployees.length === 0" class="py-8 text-center text-xs text-slate-400 italic">
                        No matching employees found.
                    </div>
                </div>
            </div>

            <!-- Page Margins (Editable on the go) -->
            <div x-show="selectedTemplateId" class="p-4 bg-slate-50 dark:bg-zinc-950 border border-slate-200 dark:border-zinc-800 rounded-2xl space-y-3 shrink-0">
                <div class="flex items-center justify-between">
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 dark:text-zinc-400">Page Margins (mm)</label>
                    <label class="flex items-center gap-1.5 cursor-pointer select-none">
                        <input type="checkbox"
                               x-model="differentFirstPageMargins"
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
                            <input type="number" x-model.number="firstPageMargins.{{ $side }}"
                                   min="0" max="100"
                                   class="w-full p-1.5 border border-slate-200 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-900 text-xs text-center text-slate-800 dark:text-zinc-200 focus:border-amber-500 outline-none">
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
                            <input type="number" x-model.number="margins.{{ $side }}"
                                   min="0" max="100"
                                   class="w-full p-1.5 border border-slate-200 dark:border-zinc-700 rounded-lg bg-white dark:bg-zinc-900 text-xs text-center text-slate-800 dark:text-zinc-200 focus:border-amber-500 outline-none">
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            
            <!-- Margin Guides Toggle -->
            <div class="border-t border-slate-100 dark:border-zinc-800/80 pt-4 shrink-0">
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
        <div x-show="!selectedTemplateId" 
             class="flex flex-col items-center justify-center p-16 text-center max-w-sm my-auto space-y-6">
            <div class="w-20 h-20 rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 flex items-center justify-center text-4xl shadow-sm">📄</div>
            <div class="space-y-1.5">
                <h2 class="text-lg font-bold text-slate-800 dark:text-zinc-200">Letter Preview</h2>
                <p class="text-xs text-slate-400 leading-relaxed">Select a template from the sidebar to preview and print generated letters.</p>
            </div>
        </div>

        <!-- No Employee Selected Disclaimer (shown when template is selected but no employee is checked) -->
        <template x-if="selectedTemplateId && selectedCodes.length === 0">
            <div class="flex flex-col items-center justify-center p-12 text-center max-w-md my-auto space-y-5">
                <div class="w-20 h-20 rounded-2xl bg-white dark:bg-zinc-900 border border-slate-200 dark:border-zinc-800 flex items-center justify-center text-4xl shadow-sm">
                    👤
                </div>
                <div class="space-y-2">
                    <h2 class="text-lg font-bold text-slate-800 dark:text-zinc-200">No Employee Selected</h2>
                    <p class="text-sm text-slate-400 dark:text-zinc-500 leading-relaxed">
                        Please select one or more employees from the <strong class="text-slate-600 dark:text-zinc-300">Target Employees</strong> list on the left to preview and generate their letters.
                    </p>
                </div>
                <div class="flex items-center gap-2 px-4 py-2.5 bg-amber-500/10 border border-amber-500/20 rounded-xl">
                    <i class="fa-solid fa-arrow-left text-amber-500 text-sm"></i>
                    <span class="text-xs font-semibold text-amber-700 dark:text-amber-400">Select employees from the sidebar</span>
                </div>
            </div>
        </template>

        <!-- Letters Preview Container (only shown when employees are selected) -->
        <div id="preview-container" x-show="selectedTemplateId && selectedCodes.length > 0" class="space-y-8 flex flex-col items-center">

            <template x-for="code in selectedCodes" :key="code">
                <div class="space-y-2">
                    <!-- Employee label tag -->
                    <div class="flex items-center gap-2 mb-2 no-print self-start bg-amber-500/10 border border-amber-500/20 text-amber-800 dark:text-amber-400 px-3 py-1.5 rounded-xl text-xs font-bold shadow-xs">
                        <i class="fa-solid fa-circle-user text-amber-500"></i>
                        <span x-text="(getEmployee(code)?.name || code) + ' (' + code + ')'"></span>
                    </div>

                    <!-- Rendering each letter page -->
                    <template x-for="(pageHtml, pageIdx) in (paginatedLetters[code] || [])" :key="pageIdx">
                        <div class="print-page-wrapper">
                            <!-- Gap between pages on screen -->
                            <div x-show="pageIdx > 0" class="page-gap no-print"></div>

                            <div class="page-sheet">
                                <div class="a4-page"
                                     :style="`
                                         padding-top: ${(pageIdx === 0 && differentFirstPageMargins) ? firstPageMargins.top : margins.top}mm;
                                         padding-bottom: ${(pageIdx === 0 && differentFirstPageMargins) ? firstPageMargins.bottom : margins.bottom}mm;
                                         padding-left: ${(pageIdx === 0 && differentFirstPageMargins) ? firstPageMargins.left : margins.left}mm;
                                         padding-right: ${(pageIdx === 0 && differentFirstPageMargins) ? firstPageMargins.right : margins.right}mm;
                                     `"
                                     x-html="pageHtml">
                                </div>

                                <!-- Margin guides (not printed) -->
                                <template x-if="showGuides">
                                    <div>
                                        <div class="margin-guide guide-top no-print" :style="`height:${(pageIdx === 0 && differentFirstPageMargins) ? firstPageMargins.top : margins.top}mm`">
                                            <span class="guide-label" style="left:6px;bottom:2px" x-text="`↑ ${(pageIdx === 0 && differentFirstPageMargins) ? firstPageMargins.top : margins.top}mm`"></span>
                                        </div>
                                        <div class="margin-guide guide-bottom no-print" :style="`height:${(pageIdx === 0 && differentFirstPageMargins) ? firstPageMargins.bottom : margins.bottom}mm`">
                                            <span class="guide-label" style="left:6px;top:2px" x-text="`↓ ${(pageIdx === 0 && differentFirstPageMargins) ? firstPageMargins.bottom : margins.bottom}mm`"></span>
                                        </div>
                                        <div class="margin-guide guide-left no-print" :style="`width:${(pageIdx === 0 && differentFirstPageMargins) ? firstPageMargins.left : margins.left}mm`">
                                            <span class="guide-label" style="top:6px;right:2px" x-text="`← ${(pageIdx === 0 && differentFirstPageMargins) ? firstPageMargins.left : margins.left}mm`"></span>
                                        </div>
                                        <div class="margin-guide guide-right no-print" :style="`width:${(pageIdx === 0 && differentFirstPageMargins) ? firstPageMargins.right : margins.right}mm`">
                                            <span class="guide-label" style="top:6px;left:2px" x-text="`→ ${(pageIdx === 0 && differentFirstPageMargins) ? firstPageMargins.right : margins.right}mm`"></span>
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

    <!-- Right Sidebar: Custom & Permanent variables input (no-print) -->
    <aside x-show="selectedTemplate && allEffectiveVariables.length > 0"
           class="no-print w-full md:w-80 bg-white dark:bg-zinc-900 border-l border-slate-200 dark:border-zinc-800 flex flex-col overflow-hidden shrink-0 shadow-sm z-20">
        
        <div class="p-6 border-b border-slate-200 dark:border-zinc-800 space-y-2 shrink-0">
            <h2 class="text-sm font-bold text-slate-800 dark:text-zinc-200 uppercase tracking-wider">Template Variables</h2>
            <p class="text-xs text-slate-400 mt-1">Specify values for custom & permanent placeholders.</p>
        </div>
        
        <div class="flex-1 overflow-y-auto p-6 space-y-4">
            <template x-for="v in allEffectiveVariables" :key="v.key || v">
                <div class="space-y-1.5">
                    <div class="flex items-center justify-between">
                        <label class="block text-[11px] font-bold text-slate-500 dark:text-zinc-400 uppercase tracking-wider" 
                               x-text="formatLabel(v.key || v)"></label>
                        <template x-if="v.is_permanent">
                            <span class="text-[9px] font-bold uppercase tracking-wider px-1.5 py-0.2 rounded bg-emerald-500/10 text-emerald-700 dark:text-emerald-400 border border-emerald-500/20 flex items-center gap-1">
                                <i class="fa-solid fa-lock text-[8px]"></i> Permanent
                            </span>
                        </template>
                    </div>
                    
                    <!-- Date Field -->
                    <template x-if="(v.type || 'text') === 'date'">
                        <div class="space-y-2">
                            <input type="date" x-model="customValues[v.key || v]" @change="computeFormulas(); updatePaginatedLetters()"
                                   class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-950 rounded-xl text-xs text-slate-850 dark:text-zinc-200 focus:outline-none focus:border-amber-500">
                            
                            <!-- Nepali (BS) Date Conversion & Format Selector -->
                            <div x-show="customValues[v.key || v]" class="p-2.5 bg-amber-500/5 dark:bg-amber-950/20 border border-amber-500/20 rounded-xl space-y-2">
                                <div class="flex items-center justify-between text-[11px]">
                                    <span class="font-bold text-amber-800 dark:text-amber-400 flex items-center gap-1.5">
                                        <i class="fa-solid fa-calendar-check text-amber-500"></i>
                                        <span>Nepali (BS) Date:</span>
                                    </span>
                                    <span class="font-mono font-bold text-slate-800 dark:text-zinc-200"
                                          x-text="getNepaliDateInfo(customValues[v.key || v])?.bs_words || '—'"></span>
                                </div>
                                <div class="text-[10px] text-slate-500 dark:text-zinc-400 flex items-center justify-between border-t border-amber-500/10 pt-1.5">
                                    <span>Devanagari: <strong class="text-slate-700 dark:text-zinc-200" x-text="getNepaliDateInfo(customValues[v.key || v])?.bs_unicode || '—'"></strong></span>
                                    <span>Digits: <strong class="text-slate-700 dark:text-zinc-200 font-mono" x-text="getNepaliDateInfo(customValues[v.key || v])?.bs_date || '—'"></strong></span>
                                </div>
                                <div class="text-[10px] text-slate-500 dark:text-zinc-400 flex items-center justify-between border-t border-amber-500/10 pt-1.5">
                                    <span>Preeti:</span>
                                    <strong class="text-slate-700 dark:text-zinc-200 font-bold" style="font-family: 'Preeti', sans-serif;" x-text="getNepaliDateInfo(customValues[v.key || v])?.bs_preeti_words || '—'"></strong>
                                </div>
                                <div class="space-y-1 pt-1.5 border-t border-amber-500/10">
                                    <label class="block text-[9px] font-bold uppercase tracking-wider text-slate-400 dark:text-zinc-500">Use In Letter As:</label>
                                    <select x-model="dateFormats[v.key || v]" @change="updatePaginatedLetters()"
                                            class="w-full px-2 py-1.5 border border-amber-500/30 bg-white dark:bg-zinc-900 rounded-lg text-xs font-semibold text-slate-800 dark:text-zinc-200 focus:outline-none focus:border-amber-500 cursor-pointer">
                                        <option value="ad_full">AD Full: 28th September, 2026</option>
                                        <option value="ad_standard">AD Standard: 2026-09-28</option>
                                        <option value="bs_words">BS in Words: 12 Ashoj, 2083</option>
                                        <option value="bs_digits">BS Digits: 2083-06-12</option>
                                        <option value="bs_unicode">BS Devanagari: १२ असोज २०८३</option>
                                        <option value="bs_preeti_words">BS Preeti (नेपाली): १२ असोज २०८३</option>
                                        <option value="bs_preeti_digits">BS Preeti Digits: २०८३.०६.१२</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- Date Range Field (Single Calendar for Start & End) -->
                    <template x-if="v.type === 'daterange'">
                        <div class="space-y-2"
                             x-data="{
                                fp: null,
                                init() {
                                    const key = v.key || v;
                                    const el = this.$refs.rangeInput;
                                    this.$nextTick(() => {
                                        if (typeof flatpickr === 'undefined') return;
                                        this.fp = flatpickr(el, {
                                            mode: 'range',
                                            dateFormat: 'Y-m-d',
                                            defaultDate: (customValues[key + '_from'] && customValues[key + '_to']) 
                                                ? [customValues[key + '_from'], customValues[key + '_to']] 
                                                : [],
                                            onChange: (selectedDates, dateStr) => {
                                                if (selectedDates.length === 2) {
                                                    const d1 = this.fp.formatDate(selectedDates[0], 'Y-m-d');
                                                    const d2 = this.fp.formatDate(selectedDates[1], 'Y-m-d');
                                                    customValues[key] = d1 + ' to ' + d2;
                                                    customValues[key + '_from'] = d1;
                                                    customValues[key + '_to'] = d2;
                                                    updatePaginatedLetters();
                                                }
                                            }
                                        });
                                    });
                                }
                             }">
                            <div class="relative">
                                <input type="text"
                                       x-ref="rangeInput"
                                       :value="(customValues[(v.key || v) + '_from'] && customValues[(v.key || v) + '_to']) ? (customValues[(v.key || v) + '_from'] + ' to ' + customValues[(v.key || v) + '_to']) : ''"
                                       placeholder="Select start and end date..."
                                       readonly
                                       class="w-full pl-8 pr-7 py-2 border border-slate-200 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-950 rounded-xl text-xs text-slate-850 dark:text-zinc-200 focus:outline-none focus:border-amber-500 cursor-pointer">
                                <i class="fa-solid fa-calendar-days absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs pointer-events-none"></i>
                                <button type="button"
                                        x-show="customValues[(v.key || v) + '_from'] && customValues[(v.key || v) + '_to']"
                                        @click="if (fp) fp.clear(); customValues[v.key || v] = ''; customValues[(v.key || v) + '_from'] = ''; customValues[(v.key || v) + '_to'] = ''; updatePaginatedLetters();"
                                        class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-rose-500 text-xs cursor-pointer"
                                        title="Clear date range">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </div>
                            <div class="flex items-center justify-between text-[10px] text-slate-400 px-0.5">
                                <span class="truncate">From: <strong class="text-slate-600 dark:text-zinc-300 font-mono" x-text="customValues[(v.key || v) + '_from'] || '—'"></strong></span>
                                <span class="truncate">To: <strong class="text-slate-600 dark:text-zinc-300 font-mono" x-text="customValues[(v.key || v) + '_to'] || '—'"></strong></span>
                            </div>

                            <!-- Nepali (BS) Date Conversion & Format Selector for Range -->
                            <div x-show="customValues[(v.key || v) + '_from'] && customValues[(v.key || v) + '_to']"
                                 class="p-2.5 bg-amber-500/5 dark:bg-amber-950/20 border border-amber-500/20 rounded-xl space-y-2">
                                <div class="text-[11px] font-bold text-amber-800 dark:text-amber-400 flex items-center gap-1.5">
                                    <i class="fa-solid fa-calendar-check text-amber-500"></i>
                                    <span>Nepali (BS) Range:</span>
                                </div>
                                <div class="text-[10px] text-slate-600 dark:text-zinc-300 space-y-1 border-t border-amber-500/10 pt-1.5">
                                    <div class="flex items-center justify-between">
                                        <span class="text-slate-400">From (BS):</span>
                                        <strong class="font-mono text-slate-700 dark:text-zinc-200" x-text="getNepaliDateInfo(customValues[(v.key || v) + '_from'])?.bs_words || '—'"></strong>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span class="text-slate-400">To (BS):</span>
                                        <strong class="font-mono text-slate-700 dark:text-zinc-200" x-text="getNepaliDateInfo(customValues[(v.key || v) + '_to'])?.bs_words || '—'"></strong>
                                    </div>
                                </div>
                                <div class="text-[10px] text-slate-500 dark:text-zinc-400 space-y-1 border-t border-amber-500/10 pt-1.5">
                                    <div class="flex items-center justify-between">
                                        <span>Devanagari:</span>
                                        <strong class="text-slate-700 dark:text-zinc-200" x-text="formatDateRangeWithFormat(customValues[(v.key || v) + '_from'], customValues[(v.key || v) + '_to'], 'bs_unicode') || '—'"></strong>
                                    </div>
                                    <div class="flex items-center justify-between">
                                        <span>Preeti:</span>
                                        <strong class="text-slate-700 dark:text-zinc-200 font-bold" style="font-family: 'Preeti', sans-serif;" x-text="formatDateRangeWithFormat(customValues[(v.key || v) + '_from'], customValues[(v.key || v) + '_to'], 'bs_preeti_words') || '—'"></strong>
                                    </div>
                                </div>
                                <div class="space-y-1 pt-1.5 border-t border-amber-500/10">
                                    <label class="block text-[9px] font-bold uppercase tracking-wider text-slate-400 dark:text-zinc-500">Use In Letter As:</label>
                                    <select x-model="dateFormats[v.key || v]" @change="updatePaginatedLetters()"
                                            class="w-full px-2 py-1.5 border border-amber-500/30 bg-white dark:bg-zinc-900 rounded-lg text-xs font-semibold text-slate-800 dark:text-zinc-200 focus:outline-none focus:border-amber-500 cursor-pointer">
                                        <option value="ad_full">AD Full: from 1st Sep, 2026 to 28th Sep, 2026</option>
                                        <option value="ad_standard">AD Standard: 2026-09-01 to 2026-09-28</option>
                                        <option value="bs_words">BS in Words: from 16 Bhadra, 2083 to 12 Ashoj, 2083</option>
                                        <option value="bs_digits">BS Digits: 2083-05-16 to 2083-06-12</option>
                                        <option value="bs_unicode">BS Devanagari: १६ भदौ २०८३ देखि १२ असोज २०८३ सम्म</option>
                                        <option value="bs_preeti_words">BS Preeti (नेपाली): १६ भदौ २०८३ देखि १२ असोज २०८३ सम्म</option>
                                        <option value="bs_preeti_digits">BS Preeti Digits: २०८३.०५.१६ देखि २०८३.०६.१२ सम्म</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </template>


                    <!-- Number Field -->
                    <template x-if="(v.type || 'text') === 'number'">
                        <div class="space-y-2">
                            <input type="number" x-model="customValues[v.key || v]" 
                                   @input="computeFormulas(); updatePaginatedLetters()" 
                                   :placeholder="'Enter ' + formatLabel(v.key || v)" 
                                   class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-950 rounded-xl text-xs text-slate-850 dark:text-zinc-200 focus:outline-none focus:border-amber-500">

                            <!-- Nepali & Preeti Number Conversion & Format Selector -->
                            <div x-show="customValues[v.key || v] !== '' && customValues[v.key || v] !== undefined && customValues[v.key || v] !== null" 
                                 class="p-2.5 bg-amber-500/5 dark:bg-amber-950/20 border border-amber-500/20 rounded-xl space-y-2">
                                <div class="flex items-center justify-between text-[11px]">
                                    <span class="font-bold text-amber-800 dark:text-amber-400 flex items-center gap-1.5">
                                        <i class="fa-solid fa-arrow-right-arrow-left text-amber-500"></i>
                                        <span>Preeti Digits:</span>
                                    </span>
                                    <span class="font-bold text-slate-800 dark:text-zinc-200 text-sm" style="font-family: 'Preeti', sans-serif;"
                                          x-text="formatNumberWithFormat(customValues[v.key || v], 'preeti_comma') || '—'"></span>
                                </div>
                                <div class="text-[10px] text-slate-500 dark:text-zinc-400 flex items-center justify-between border-t border-amber-500/10 pt-1.5">
                                    <span>Devanagari: <strong class="text-slate-700 dark:text-zinc-200 font-semibold" x-text="formatNumberWithFormat(customValues[v.key || v], 'devanagari_comma') || '—'"></strong></span>
                                    <span>Standard: <strong class="text-slate-700 dark:text-zinc-200 font-mono font-semibold" x-text="formatNumberWithFormat(customValues[v.key || v], 'en_comma') || '—'"></strong></span>
                                </div>
                                <div class="space-y-1 pt-1.5 border-t border-amber-500/10">
                                    <label class="block text-[9px] font-bold uppercase tracking-wider text-slate-400 dark:text-zinc-500">Use In Letter As:</label>
                                    <select x-model="dateFormats[v.key || v]" @change="updatePaginatedLetters()"
                                            class="w-full px-2 py-1.5 border border-amber-500/30 bg-white dark:bg-zinc-900 rounded-lg text-xs font-semibold text-slate-800 dark:text-zinc-200 focus:outline-none focus:border-amber-500 cursor-pointer">
                                        <option value="en">Standard (English): 50000</option>
                                        <option value="en_comma">English with Western Comma: 50,000</option>
                                        <option value="nepali_comma">English with Nepali Comma: 1,00,00,000</option>
                                        <option value="devanagari">Devanagari: ५००००</option>
                                        <option value="devanagari_comma">Devanagari with Nepali Comma: १,००,००,०००</option>
                                        <option value="preeti">BS Preeti Digits: ५००००</option>
                                        <option value="preeti_comma">BS Preeti Digits with Nepali Comma: १,००,००,०००</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- Amount Field (with live conversions: raw, commas, nepali commas, and words) -->
                    <template x-if="(v.type || 'text') === 'amount'">
                        <div class="space-y-2">
                            <div class="relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 font-bold text-xs select-none">Rs.</span>
                                <input type="number" step="any" x-model="customValues[v.key || v]" 
                                       @input="computeFormulas(); updatePaginatedLetters()" 
                                       :placeholder="'Enter ' + formatLabel(v.key || v)" 
                                       class="w-full pl-9 pr-3 py-2 border border-slate-200 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-950 rounded-xl text-xs text-slate-850 dark:text-zinc-200 focus:outline-none focus:border-amber-500 font-mono">
                            </div>

                            <!-- Nepali & English Amount Conversions Card -->
                            <div x-show="customValues[v.key || v] !== '' && customValues[v.key || v] !== undefined && customValues[v.key || v] !== null" 
                                 class="p-2.5 bg-amber-500/5 dark:bg-amber-950/20 border border-amber-500/20 rounded-xl space-y-2">
                                
                                <div class="space-y-1.5 text-[11px]">
                                    <div class="flex items-center justify-between">
                                        <span class="font-bold text-amber-800 dark:text-amber-400 flex items-center gap-1.5">
                                            <i class="fa-solid fa-coins text-amber-500"></i>
                                            <span>Nepali Commas:</span>
                                        </span>
                                        <strong class="text-slate-800 dark:text-zinc-100 font-mono font-bold"
                                                x-text="nepaliCalendar.formatNepaliComma(customValues[v.key || v]) || '—'"></strong>
                                    </div>

                                    <div class="text-[10px] text-slate-500 dark:text-zinc-400 flex items-center justify-between border-t border-amber-500/10 pt-1.5">
                                        <span>Preeti Digits: <strong class="text-slate-800 dark:text-zinc-200 font-bold" style="font-family: 'Preeti', sans-serif;" x-text="nepaliCalendar.toPreetiDigits(nepaliCalendar.formatNepaliComma(customValues[v.key || v])) || '—'"></strong></span>
                                        <span>Devanagari: <strong class="text-slate-700 dark:text-zinc-200 font-semibold" x-text="nepaliCalendar.toDevanagariDigits(nepaliCalendar.formatNepaliComma(customValues[v.key || v])) || '—'"></strong></span>
                                    </div>

                                    <div class="text-[10px] text-slate-500 dark:text-zinc-400 flex items-center justify-between border-t border-amber-500/10 pt-1.5">
                                        <span>Western Commas: <strong class="text-slate-700 dark:text-zinc-200 font-mono font-semibold" x-text="nepaliCalendar.formatWesternComma(customValues[v.key || v]) || '—'"></strong></span>
                                        <span>As is: <strong class="text-slate-700 dark:text-zinc-200 font-mono" x-text="customValues[v.key || v]"></strong></span>
                                    </div>

                                    <div class="border-t border-amber-500/10 pt-1.5 text-[10px] space-y-1">
                                        <div class="text-slate-600 dark:text-zinc-300">
                                            <span class="text-slate-400 font-bold uppercase text-[9px] block">In Words (English):</span>
                                            <span class="font-medium text-slate-800 dark:text-zinc-100" x-text="nepaliCalendar.amountToWordsEnglish(customValues[v.key || v]) || '—'"></span>
                                        </div>
                                        <div class="text-slate-600 dark:text-zinc-300">
                                            <span class="text-slate-400 font-bold uppercase text-[9px] block">In Words (Nepali Unicode):</span>
                                            <span class="font-medium text-slate-800 dark:text-zinc-100" x-text="nepaliCalendar.amountToWordsNepali(customValues[v.key || v], false) || '—'"></span>
                                        </div>
                                        <div class="text-slate-600 dark:text-zinc-300">
                                            <span class="text-slate-400 font-bold uppercase text-[9px] block font-sans">In Words (Preeti):</span>
                                            <span class="font-bold text-slate-800 dark:text-zinc-100" style="font-family: 'Preeti', sans-serif;" x-text="nepaliCalendar.amountToWordsNepali(customValues[v.key || v], true) || '—'"></span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Use Base Placeholder As: -->
                                <div class="space-y-1 pt-1.5 border-t border-amber-500/10">
                                    <label class="block text-[9px] font-bold uppercase tracking-wider text-slate-400 dark:text-zinc-500">
                                        Use Base Placeholder &#123;&#123;&nbsp;<span x-text="v.key || v"></span>&nbsp;&#125;&#125; As:
                                    </label>
                                    <select x-model="dateFormats[v.key || v]" @change="updatePaginatedLetters()"
                                            class="w-full px-2 py-1.5 border border-amber-500/30 bg-white dark:bg-zinc-900 rounded-lg text-xs font-semibold text-slate-800 dark:text-zinc-200 focus:outline-none focus:border-amber-500 cursor-pointer">
                                        <option value="raw">Amount As It Is (e.g. 10000000)</option>
                                        <option value="en_comma">Amount with Commas - Western (e.g. 10,000,000)</option>
                                        <option value="nepali_comma">Amount with Nepali Commas (e.g. 1,00,00,000)</option>
                                        <option value="devanagari_comma">Devanagari Digits with Nepali Comma (e.g. १,००,००,०००)</option>
                                        <option value="preeti_comma">Preeti Digits with Nepali Comma (e.g. १,००,००,०००)</option>
                                        <option value="words_en">In Words - English (e.g. One Crore Only)</option>
                                        <option value="words_np">In Words - Nepali (e.g. एक करोड मात्र)</option>
                                        <option value="words_preeti">In Words - Preeti (e.g. Ps s/f]8 dfq)</option>
                                    </select>
                                    <p class="text-[9px] text-slate-400 italic pt-0.5">Or use specific sub-keys in template: <code class="font-mono text-amber-600 dark:text-amber-400">&#123;&#123;&nbsp;<span x-text="(v.key || v) + '_nepali_comma'"></span>&nbsp;&#125;&#125;</code>, <code class="font-mono text-amber-600 dark:text-amber-400">&#123;&#123;&nbsp;<span x-text="(v.key || v) + '_words_en'"></span>&nbsp;&#125;&#125;</code>, etc.</p>
                                </div>
                            </div>
                        </div>
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

                    <!-- Rich Text Field -->
                    <template x-if="v.type === 'richtext'">
                        <div class="rtv-editor-wrap border border-slate-200 dark:border-zinc-700 rounded-xl overflow-hidden bg-white dark:bg-zinc-950">
                            <!-- Mini toolbar row 1: text styles -->
                            <div class="flex flex-wrap items-center gap-0.5 px-2 py-1.5 border-b border-slate-100 dark:border-zinc-800 bg-slate-50 dark:bg-zinc-900">
                                <button type="button" @mousedown.prevent="document.execCommand('bold')"          class="rtv-tb-btn font-bold" title="Bold">B</button>
                                <button type="button" @mousedown.prevent="document.execCommand('italic')"        class="rtv-tb-btn italic" title="Italic">I</button>
                                <button type="button" @mousedown.prevent="document.execCommand('underline')"     class="rtv-tb-btn underline" title="Underline">U</button>
                                <button type="button" @mousedown.prevent="document.execCommand('strikeThrough')" class="rtv-tb-btn line-through" title="Strikethrough">S</button>
                                <div class="w-px h-3 bg-slate-200 dark:bg-zinc-700 mx-0.5"></div>
                                <button type="button" @mousedown.prevent="document.execCommand('insertUnorderedList')" class="rtv-tb-btn" title="Bullet List"><i class="fa-solid fa-list-ul fa-xs"></i></button>
                                <button type="button" @mousedown.prevent="document.execCommand('insertOrderedList')"   class="rtv-tb-btn" title="Numbered List"><i class="fa-solid fa-list-ol fa-xs"></i></button>
                                <div class="w-px h-3 bg-slate-200 dark:bg-zinc-700 mx-0.5"></div>
                                <button type="button" @mousedown.prevent="document.execCommand('outdent')" class="rtv-tb-btn" title="Outdent">⇤</button>
                                <button type="button" @mousedown.prevent="document.execCommand('indent')"  class="rtv-tb-btn" title="Indent">⇥</button>
                                <div class="w-px h-3 bg-slate-200 dark:bg-zinc-700 mx-0.5"></div>
                                <button type="button" @mousedown.prevent="document.execCommand('justifyLeft')"   class="rtv-tb-btn" title="Align Left"><i class="fa-solid fa-align-left fa-xs"></i></button>
                                <button type="button" @mousedown.prevent="document.execCommand('justifyCenter')" class="rtv-tb-btn" title="Align Center"><i class="fa-solid fa-align-center fa-xs"></i></button>
                                <button type="button" @mousedown.prevent="document.execCommand('justifyRight')"  class="rtv-tb-btn" title="Align Right"><i class="fa-solid fa-align-right fa-xs"></i></button>
                                <div class="w-px h-3 bg-slate-200 dark:bg-zinc-700 mx-0.5"></div>
                                <!-- Font size picker -->
                                <select @mousedown.stop
                                        @change="document.execCommand('fontSize', false, $event.target.value)"
                                        class="rtv-size-select" title="Font Size">
                                    <option value="">px</option>
                                    <option value="1">8</option>
                                    <option value="2">10</option>
                                    <option value="3">12</option>
                                    <option value="4">14</option>
                                    <option value="5">18</option>
                                    <option value="6">24</option>
                                    <option value="7">36</option>
                                </select>
                                <div class="w-px h-3 bg-slate-200 dark:bg-zinc-700 mx-0.5"></div>
                                <button type="button" @mousedown.prevent="document.execCommand('removeFormat')" class="rtv-tb-btn text-rose-400" title="Clear Format"><i class="fa-solid fa-eraser fa-xs"></i></button>
                            </div>
                            <!-- Editable area -->
                            <div contenteditable="true"
                                 :id="'rtv-' + (v.key || v)"
                                 x-init="$el.innerHTML = customValues[v.key || v] || ''"
                                 @input="customValues[v.key || v] = $el.innerHTML"
                                 class="rtv-editor-content min-h-[80px] px-3 py-2 text-xs text-slate-800 dark:text-zinc-200 focus:outline-none">
                            </div>
                        </div>
                    </template>

                    <!-- Calculated Field (number input + live formula results) -->
                    <template x-if="v.type === 'calculated'">
                        <div class="space-y-2">
                            <input type="number"
                                   :placeholder="'Enter ' + formatLabel(v.key || v)"
                                   x-model.number="customValues[v.key || v]"
                                   @input="computeFormulas(); updatePaginatedLetters()"
                                   class="w-full px-3 py-2 border border-slate-200 dark:border-zinc-700 bg-slate-50 dark:bg-zinc-950 rounded-xl text-xs text-slate-850 dark:text-zinc-200 focus:outline-none focus:border-indigo-400 transition-all">

                            <!-- Nepali & Preeti Number Conversion & Format Selector for Calculated Base -->
                            <div x-show="customValues[v.key || v] !== '' && customValues[v.key || v] !== undefined && customValues[v.key || v] !== null" 
                                 class="p-2.5 bg-amber-500/5 dark:bg-amber-950/20 border border-amber-500/20 rounded-xl space-y-2">
                                <div class="flex items-center justify-between text-[11px]">
                                    <span class="font-bold text-amber-800 dark:text-amber-400 flex items-center gap-1.5">
                                        <i class="fa-solid fa-arrow-right-arrow-left text-amber-500"></i>
                                        <span>Preeti Digits:</span>
                                    </span>
                                    <span class="font-bold text-slate-800 dark:text-zinc-200 text-sm" style="font-family: 'Preeti', sans-serif;"
                                          x-text="formatNumberWithFormat(customValues[v.key || v], 'preeti_comma') || '—'"></span>
                                </div>
                                <div class="text-[10px] text-slate-500 dark:text-zinc-400 flex items-center justify-between border-t border-amber-500/10 pt-1.5">
                                    <span>Devanagari: <strong class="text-slate-700 dark:text-zinc-200 font-semibold" x-text="formatNumberWithFormat(customValues[v.key || v], 'devanagari_comma') || '—'"></strong></span>
                                    <span>Standard: <strong class="text-slate-700 dark:text-zinc-200 font-mono font-semibold" x-text="formatNumberWithFormat(customValues[v.key || v], 'en_comma') || '—'"></strong></span>
                                </div>
                                <div class="space-y-1 pt-1.5 border-t border-amber-500/10">
                                    <label class="block text-[9px] font-bold uppercase tracking-wider text-slate-400 dark:text-zinc-500">Use In Letter As:</label>
                                    <select x-model="dateFormats[v.key || v]" @change="updatePaginatedLetters()"
                                            class="w-full px-2 py-1.5 border border-amber-500/30 bg-white dark:bg-zinc-900 rounded-lg text-xs font-semibold text-slate-800 dark:text-zinc-200 focus:outline-none focus:border-amber-500 cursor-pointer">
                                        <option value="en">Standard (English): 50000</option>
                                        <option value="en_comma">English with Western Comma: 50,000</option>
                                        <option value="nepali_comma">English with Nepali Comma: 1,00,00,000</option>
                                        <option value="devanagari">Devanagari: ५००००</option>
                                        <option value="devanagari_comma">Devanagari with Nepali Comma: १,००,००,०००</option>
                                        <option value="preeti">BS Preeti Digits: ५००००</option>
                                        <option value="preeti_comma">BS Preeti Digits with Nepali Comma: १,००,००,०००</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Formula results preview -->
                            <template x-if="v.formulas && v.formulas.length">
                                <div class="rounded-xl border border-indigo-100 dark:border-indigo-900/50 overflow-hidden">
                                    <div class="px-2.5 py-1.5 bg-indigo-50 dark:bg-indigo-950/30 border-b border-indigo-100 dark:border-indigo-900/50">
                                        <span class="text-[9px] font-bold uppercase tracking-wider text-indigo-500 dark:text-indigo-400">Computed Values</span>
                                    </div>
                                    <table class="w-full text-[10px]">
                                        <template x-for="f in (v.formulas || [])" :key="f.key">
                                            <tr class="border-b border-indigo-50 dark:border-indigo-900/30 last:border-0">
                                                <td class="px-2.5 py-1.5 font-semibold text-slate-500 dark:text-zinc-400" x-text="f.label || f.key"></td>
                                                <td class="px-2.5 py-1.5 font-mono font-bold text-right text-indigo-600 dark:text-indigo-400" x-text="formatFormulaResult(customValues[f.key])"></td>
                                            </tr>
                                        </template>
                                    </table>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
            </template>
        </div>
    </aside>

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
const nepaliCalendar = {
    bsData: [
        [2000, 30, 32, 31, 32, 31, 30, 30, 30, 29, 30, 29, 31],
        [2001, 31, 31, 32, 31, 31, 31, 30, 29, 30, 29, 30, 30],
        [2002, 31, 31, 32, 32, 31, 30, 30, 29, 30, 29, 30, 30],
        [2003, 31, 32, 31, 32, 31, 30, 30, 30, 29, 29, 30, 31],
        [2004, 30, 32, 31, 32, 31, 30, 30, 30, 29, 30, 29, 31],
        [2005, 31, 31, 32, 31, 31, 31, 30, 29, 30, 29, 30, 30],
        [2006, 31, 31, 32, 32, 31, 30, 30, 29, 30, 29, 30, 30],
        [2007, 31, 32, 31, 32, 31, 30, 30, 30, 29, 29, 30, 31],
        [2008, 31, 31, 31, 32, 31, 31, 29, 30, 30, 29, 29, 31],
        [2009, 31, 31, 32, 31, 31, 31, 30, 29, 30, 29, 30, 30],
        [2010, 31, 31, 32, 32, 31, 30, 30, 29, 30, 29, 30, 30],
        [2011, 31, 32, 31, 32, 31, 30, 30, 30, 29, 29, 30, 31],
        [2012, 31, 31, 31, 32, 31, 31, 29, 30, 30, 29, 30, 30],
        [2013, 31, 31, 32, 31, 31, 31, 30, 29, 30, 29, 30, 30],
        [2014, 31, 31, 32, 32, 31, 30, 30, 29, 30, 29, 30, 30],
        [2015, 31, 32, 31, 32, 31, 30, 30, 30, 29, 29, 30, 31],
        [2016, 31, 31, 31, 32, 31, 31, 29, 30, 30, 29, 30, 30],
        [2017, 31, 31, 32, 31, 31, 31, 30, 29, 30, 29, 30, 30],
        [2018, 31, 32, 31, 32, 31, 30, 30, 29, 30, 29, 30, 30],
        [2019, 31, 32, 31, 32, 31, 30, 30, 30, 29, 30, 29, 31],
        [2020, 31, 31, 31, 32, 31, 31, 30, 29, 30, 29, 30, 30],
        [2021, 31, 31, 32, 31, 31, 31, 30, 29, 30, 29, 30, 30],
        [2022, 31, 32, 31, 32, 31, 30, 30, 30, 29, 29, 30, 30],
        [2023, 31, 32, 31, 32, 31, 30, 30, 30, 29, 30, 29, 31],
        [2024, 31, 31, 31, 32, 31, 31, 30, 29, 30, 29, 30, 30],
        [2025, 31, 31, 32, 31, 31, 31, 30, 29, 30, 29, 30, 30],
        [2026, 31, 32, 31, 32, 31, 30, 30, 30, 29, 29, 30, 31],
        [2027, 30, 32, 31, 32, 31, 30, 30, 30, 29, 30, 29, 31],
        [2028, 31, 31, 32, 31, 31, 31, 30, 29, 30, 29, 30, 30],
        [2029, 31, 31, 32, 31, 32, 30, 30, 29, 30, 29, 30, 30],
        [2030, 31, 32, 31, 32, 31, 30, 30, 30, 29, 29, 30, 31],
        [2031, 30, 32, 31, 32, 31, 30, 30, 30, 29, 30, 29, 31],
        [2032, 31, 31, 32, 31, 31, 31, 30, 29, 30, 29, 30, 30],
        [2033, 31, 31, 32, 32, 31, 30, 30, 29, 30, 29, 30, 30],
        [2034, 31, 32, 31, 32, 31, 30, 30, 30, 29, 29, 30, 31],
        [2035, 30, 32, 31, 32, 31, 31, 29, 30, 30, 29, 29, 31],
        [2036, 31, 31, 32, 31, 31, 31, 30, 29, 30, 29, 30, 30],
        [2037, 31, 31, 32, 32, 31, 30, 30, 29, 30, 29, 30, 30],
        [2038, 31, 32, 31, 32, 31, 30, 30, 30, 29, 29, 30, 31],
        [2039, 31, 31, 31, 32, 31, 31, 29, 30, 30, 29, 30, 30],
        [2040, 31, 31, 32, 31, 31, 31, 30, 29, 30, 29, 30, 30],
        [2041, 31, 31, 32, 32, 31, 30, 30, 29, 30, 29, 30, 30],
        [2042, 31, 32, 31, 32, 31, 30, 30, 30, 29, 29, 30, 31],
        [2043, 31, 31, 31, 32, 31, 31, 29, 30, 30, 29, 30, 30],
        [2044, 31, 31, 32, 31, 31, 31, 30, 29, 30, 29, 30, 30],
        [2045, 31, 32, 31, 32, 31, 30, 30, 29, 30, 29, 30, 30],
        [2046, 31, 32, 31, 32, 31, 30, 30, 30, 29, 29, 30, 31],
        [2047, 31, 31, 31, 32, 31, 31, 30, 29, 30, 29, 30, 30],
        [2048, 31, 31, 32, 31, 31, 31, 30, 29, 30, 29, 30, 30],
        [2049, 31, 32, 31, 32, 31, 30, 30, 30, 29, 29, 30, 30],
        [2050, 31, 32, 31, 32, 31, 30, 30, 30, 29, 30, 29, 31],
        [2051, 31, 31, 31, 32, 31, 31, 30, 29, 30, 29, 30, 30],
        [2052, 31, 31, 32, 31, 31, 31, 30, 29, 30, 29, 30, 30],
        [2053, 31, 32, 31, 32, 31, 30, 30, 30, 29, 29, 30, 30],
        [2054, 31, 32, 31, 32, 31, 30, 30, 30, 29, 30, 29, 31],
        [2055, 31, 31, 32, 31, 31, 31, 30, 29, 30, 29, 30, 30],
        [2056, 31, 31, 32, 31, 32, 30, 30, 29, 30, 29, 30, 30],
        [2057, 31, 32, 31, 32, 31, 30, 30, 30, 29, 29, 30, 31],
        [2058, 30, 32, 31, 32, 31, 30, 30, 30, 29, 30, 29, 31],
        [2059, 31, 31, 32, 31, 31, 31, 30, 29, 30, 29, 30, 30],
        [2060, 31, 31, 32, 32, 31, 30, 30, 29, 30, 29, 30, 30],
        [2061, 31, 32, 31, 32, 31, 30, 30, 30, 29, 29, 30, 31],
        [2062, 30, 32, 31, 32, 31, 31, 29, 30, 29, 30, 29, 31],
        [2063, 31, 31, 32, 31, 31, 31, 30, 29, 30, 29, 30, 30],
        [2064, 31, 31, 32, 32, 31, 30, 30, 29, 30, 29, 30, 30],
        [2065, 31, 32, 31, 32, 31, 30, 30, 30, 29, 29, 30, 31],
        [2066, 31, 31, 31, 32, 31, 31, 29, 30, 30, 29, 29, 31],
        [2067, 31, 31, 32, 31, 31, 31, 30, 29, 30, 29, 30, 30],
        [2068, 31, 31, 32, 32, 31, 30, 30, 29, 30, 29, 30, 30],
        [2069, 31, 32, 31, 32, 31, 30, 30, 30, 29, 29, 30, 31],
        [2070, 31, 31, 31, 32, 31, 31, 29, 30, 30, 29, 30, 30],
        [2071, 31, 31, 32, 31, 31, 31, 30, 29, 30, 29, 30, 30],
        [2072, 31, 32, 31, 32, 31, 30, 30, 29, 30, 29, 30, 30],
        [2073, 31, 32, 31, 32, 31, 30, 30, 30, 29, 29, 30, 31],
        [2074, 31, 31, 31, 32, 31, 31, 30, 29, 30, 29, 30, 30],
        [2075, 31, 31, 32, 31, 31, 31, 30, 29, 30, 29, 30, 30],
        [2076, 31, 32, 31, 32, 31, 30, 30, 30, 29, 29, 30, 30],
        [2077, 31, 32, 31, 32, 31, 30, 30, 30, 29, 30, 29, 31],
        [2078, 31, 31, 31, 32, 31, 31, 30, 29, 30, 29, 30, 30],
        [2079, 31, 31, 32, 31, 31, 31, 30, 29, 30, 29, 30, 30],
        [2080, 31, 32, 31, 32, 31, 30, 30, 30, 29, 29, 30, 30],
        [2081, 31, 31, 32, 32, 31, 30, 30, 30, 29, 30, 30, 30],
        [2082, 30, 32, 31, 32, 31, 30, 30, 30, 29, 30, 30, 30],
        [2083, 31, 31, 32, 31, 31, 30, 30, 30, 29, 30, 30, 30],
        [2084, 31, 31, 32, 31, 31, 30, 30, 30, 29, 30, 30, 30],
        [2085, 31, 32, 31, 32, 30, 31, 30, 30, 29, 30, 30, 30],
        [2086, 30, 32, 31, 32, 31, 30, 30, 30, 29, 30, 30, 30],
        [2087, 31, 31, 32, 31, 31, 31, 30, 30, 29, 30, 30, 30],
        [2088, 30, 31, 32, 32, 30, 31, 30, 30, 29, 30, 30, 30],
        [2089, 30, 32, 31, 32, 31, 30, 30, 30, 29, 30, 30, 30],
        [2090, 30, 32, 31, 32, 31, 30, 30, 30, 29, 30, 30, 30]
    ],
    isLeapYear(year) {
        return (year % 4 === 0 && (year % 100 !== 0 || year % 400 === 0));
    },
    monthsEn: ['', 'Baishakh', 'Jestha', 'Ashadh', 'Shrawan', 'Bhadra', 'Ashoj', 'Kartik', 'Mangsir', 'Poush', 'Magh', 'Falgun', 'Chaitra'],
    monthsNp: ['', 'वैशाख', 'जेठ', 'असार', 'साउन', 'भदौ', 'असोज', 'कार्तिक', 'मंसिर', 'पुस', 'माघ', 'फागुन', 'चैत'],
    monthsPreeti: ['', 'j}zfv', 'h]7', 'c;f/', ';fpg', 'ebf}', 'c;f]h', 'sflt{s', 'd+l;/', "k';", 'df3', "kmfu'g", 'r}t'],
    digitsNp: ['०', '१', '२', '३', '४', '५', '६', '७', '८', '९'],
    digitsPreeti: [')', '!', '@', '#', '$', '%', '^', '&', '*', '('],

    toDevanagariDigits(str) {
        return String(str).replace(/[0-9]/g, d => this.digitsNp[parseInt(d, 10)]);
    },
    toPreetiDigits(str) {
        return String(str).replace(/[0-9]/g, d => this.digitsPreeti[parseInt(d, 10)]);
    },

    formatNepaliComma(val) {
        if (val === '' || val === null || val === undefined) return '';
        const strVal = String(val).trim();
        if (!strVal) return '';
        const isNegative = strVal.startsWith('-');
        const cleanStr = isNegative ? strVal.slice(1) : strVal;
        const parts = cleanStr.split('.');
        let intPart = parts[0].replace(/^0+(?=\d)/, '');
        if (!intPart) intPart = '0';
        
        let formattedInt = '';
        if (intPart.length <= 3) {
            formattedInt = intPart;
        } else {
            const last3 = intPart.slice(-3);
            const remaining = intPart.slice(0, -3);
            const formattedRemaining = remaining.replace(/\B(?=(\d{2})+(?!\d))/g, ',');
            formattedInt = formattedRemaining + ',' + last3;
        }
        
        const decPart = parts.length > 1 ? '.' + parts[1] : '';
        return (isNegative ? '-' : '') + formattedInt + decPart;
    },

    formatWesternComma(val) {
        if (val === '' || val === null || val === undefined) return '';
        const strVal = String(val).trim();
        if (!strVal) return '';
        const isNegative = strVal.startsWith('-');
        const cleanStr = isNegative ? strVal.slice(1) : strVal;
        const parts = cleanStr.split('.');
        let intPart = parts[0].replace(/^0+(?=\d)/, '');
        if (!intPart) intPart = '0';
        const formattedInt = intPart.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        const decPart = parts.length > 1 ? '.' + parts[1] : '';
        return (isNegative ? '-' : '') + formattedInt + decPart;
    },

    amountToWordsEnglish(numStr) {
        if (numStr === '' || numStr === null || numStr === undefined) return '';
        let str = String(numStr).trim();
        if (!str) return '';
        let isNeg = str.startsWith('-');
        if (isNeg) str = str.slice(1);
        const parts = str.split('.');
        let intStr = parts[0].replace(/^0+(?=\d)/, '');
        if (!intStr) intStr = '0';

        const ones = ['', 'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Nine',
            'Ten', 'Eleven', 'Twelve', 'Thirteen', 'Fourteen', 'Fifteen', 'Sixteen', 'Seventeen', 'Eighteen', 'Nineteen'];
        const tens = ['', '', 'Twenty', 'Thirty', 'Forty', 'Fifty', 'Sixty', 'Seventy', 'Eighty', 'Ninety'];

        function twoDigits(n) {
            if (n === 0) return '';
            if (n < 20) return ones[n];
            const t = Math.floor(n / 10);
            const o = n % 10;
            return tens[t] + (o > 0 ? ' ' + ones[o] : '');
        }

        function threeDigits(n) {
            if (n === 0) return '';
            const h = Math.floor(n / 100);
            const rem = n % 100;
            let res = '';
            if (h > 0) res += ones[h] + ' Hundred';
            if (rem > 0) {
                if (res) res += ' ';
                res += twoDigits(rem);
            }
            return res;
        }

        if (intStr === '0') {
            let decWords = '';
            if (parts.length > 1 && parts[1].length > 0) {
                let p = parseInt(parts[1].slice(0, 2).padEnd(2, '0'), 10);
                if (p > 0) decWords = ' and ' + twoDigits(p) + ' Paisa';
            }
            return (isNeg ? 'Minus ' : '') + 'Zero' + decWords + ' Only';
        }

        let words = [];
        let remStr = intStr;

        // 1. Hundreds (last 3 digits)
        if (remStr.length > 0) {
            let take = remStr.slice(-3);
            remStr = remStr.slice(0, -3);
            let n = parseInt(take, 10);
            if (n > 0) words.unshift(threeDigits(n));
        }
        // 2. Thousands (next 2 digits)
        if (remStr.length > 0) {
            let take = remStr.slice(-2);
            remStr = remStr.slice(0, -2);
            let n = parseInt(take, 10);
            if (n > 0) words.unshift(twoDigits(n) + ' Thousand');
        }
        // 3. Lakhs (next 2 digits)
        if (remStr.length > 0) {
            let take = remStr.slice(-2);
            remStr = remStr.slice(0, -2);
            let n = parseInt(take, 10);
            if (n > 0) words.unshift(twoDigits(n) + ' Lakh');
        }
        // 4. Crores (next 2 digits)
        if (remStr.length > 0) {
            let take = remStr.slice(-2);
            remStr = remStr.slice(0, -2);
            let n = parseInt(take, 10);
            if (n > 0) words.unshift(twoDigits(n) + ' Crore');
        }
        // 5. Arabs (next 2 digits)
        if (remStr.length > 0) {
            let take = remStr.slice(-2);
            remStr = remStr.slice(0, -2);
            let n = parseInt(take, 10);
            if (n > 0) words.unshift(twoDigits(n) + ' Arab');
        }
        // 6. Kharabs (next 2 digits)
        if (remStr.length > 0) {
            let take = remStr.slice(-2);
            remStr = remStr.slice(0, -2);
            let n = parseInt(take, 10);
            if (n > 0) words.unshift(twoDigits(n) + ' Kharab');
        }

        let result = words.join(' ').trim();
        if (!result) result = 'Zero';

        if (parts.length > 1 && parts[1].length > 0) {
            let p = parseInt(parts[1].slice(0, 2).padEnd(2, '0'), 10);
            if (p > 0) {
                result += ' and ' + twoDigits(p) + ' Paisa';
            }
        }

        return (isNeg ? 'Minus ' : '') + result + ' Only';
    },

    amountToWordsNepali(numStr, isPreeti = false) {
        if (numStr === '' || numStr === null || numStr === undefined) return '';
        let str = String(numStr).trim();
        if (!str) return '';
        let isNeg = str.startsWith('-');
        if (isNeg) str = str.slice(1);
        const parts = str.split('.');
        let intStr = parts[0].replace(/^0+(?=\d)/, '');
        if (!intStr) intStr = '0';

        const npDigitsWords = [
            'शून्य', 'एक', 'दुई', 'तीन', 'चार', 'पाँच', 'छ', 'सात', 'आठ', 'नौ',
            'दश', 'एघार', 'बाह्र', 'तेह्र', 'चौध', 'पन्ध्र', 'सोह्र', 'सत्र', 'अठार', 'उन्नाइस',
            'बीस', 'एक्काइस', 'बाइस', 'तेइस', 'चौबिस', 'पच्चिस', 'छब्बीस', 'सत्ताइस', 'अट्ठाइस', 'उनन्तिस',
            'तीस', 'एकत्तिस', 'बत्तिस', 'तेत्तिस', 'चौँतिस', 'पैँतिस', 'छत्तीस', 'सैँतिस', 'अड्तीस', 'उनन्चालीस',
            'चालीस', 'एकचलीस', 'बयानलीस', 'त्रिचालीस', 'चौवालिस', 'पैँतालीस', 'छयालीस', 'सत्चालीस', 'अड्चालीस', 'उनन्पचास',
            'पचास', 'एकाउन्न', 'बाउन्न', 'त्रिपन्न', 'चौवन्न', 'पचपन्न', 'छपन्न', 'सन्ताउन्न', 'अन्ठाउन्न', 'उनन्साठ्ठी',
            'साठ्ठी', 'एकसट्ठी', 'बासट्ठी', 'त्रिसट्ठी', 'चौंसट्ठी', 'पैंसट्ठी', 'छयसट्ठी', 'सतसट्ठी', 'अठसट्ठी', 'उनन्सत्तरी',
            'सत्तरी', 'एकहत्तर', 'बहत्तर', 'त्रिहत्तर', 'चौहत्तर', 'पचहत्तर', 'छहत्तर', 'सतहत्तर', 'अठहत्तर', 'उन्यासी',
            'असी', 'एकासी', 'बयासी', 'त्रियासी', 'चौरासी', 'पचासी', 'छयासी', 'सत्तासी', 'अठासी', 'उनन्नब्बे',
            'नब्बे', 'एकान्नब्बे', 'बयानब्बे', 'त्रियान्नब्बे', 'चौरानब्बे', 'पञ्चानब्बे', 'छयानब्बे', 'सन्तान्नब्बे', 'अन्ठान्ब्बे', 'उनान्सय'
        ];

        const preetiDigitsWords = [
            'z"Go', 'Ps', "b'O{", 'tLg', 'rf/', 'kfFr', '5', ';ft', 'cf7', 'gf}',
            'bz', 'P3f/', 'afx|', 't]x|', 'rf}w', 'kGw|', ';f]x|', ';q', 'c7f/', 'pGgfO;',
            'aL;', 'PssfO;', 'afO;', 't]O;', 'rf}la;', 'klRr;', '5AaL;', ';QfO;', 'c7\\7fO;', 'pgGtL;',
            'tL;', 'PsQL;', 'aQL;', 't]QL;', 'rf}+tL;', 'k}+tL;', '5QL;', ';}+tL;', 'c8\\tL;', 'pggrfnL;',
            'rfnL;', 'PssfnL;', 'aofgnL;', 'lqrfnL;', 'rf}jfnL;', 'k}+tfnL;', '5ofnL;', ';TrfnL;', 'c8\\rfnL;', 'pggkrf;',
            'krf;', 'PsfpGg', 'afpGg', 'lqkGg', 'rf}jGg', 'krkGg', '5kGg', ';GtfpGg', 'cG7fpGg', 'pgg;f7L',
            ';f7L', 'Pss7L', 'af;7L', 'lq;7L', 'rf}+;7L', 'k}+;7L', '5o;7L', ';t;7L', 'c7;7L', 'pgg;Q/L',
            ';Q/L', 'PsgQ/', 'axQ/', 'lqxgQ/', 'rf}xQ/', 'krxgQ/', '5xQ/', ';txQ/', 'c7xgQ/', 'pgof;L',
            'c;L', 'Pscf;L', 'aof;L', 'lqof;L', 'rf}/f;L', 'krf;L', '5of;L', ';Qf;L', 'c7f;L', 'pggAa]',
            'gAa]', 'PsfGga]', 'aofGga]', 'lqoGga]', 'rf}/fGga]', 'k~rfGga]', '5ofGga]', ';GtfGga]', 'cG7fGga]', 'pgfg;o'
        ];

        const wordsMap = isPreeti ? preetiDigitsWords : npDigitsWords;
        const sHundred = isPreeti ? ';o' : 'सय';
        const sThousand = isPreeti ? 'xhf/' : 'हजार';
        const sLakh = isPreeti ? 'nfv' : 'लाख';
        const sCrore = isPreeti ? 's/f]8' : 'करोड';
        const sArab = isPreeti ? 'c/a' : 'अरब';
        const sKharab = isPreeti ? 'v/a' : 'खरब';
        const sOnly = isPreeti ? 'dfq' : 'मात्र';
        const sPaisa = isPreeti ? 'k};f' : 'पैसा';
        const sMinus = isPreeti ? 'dfOg;' : 'माइनस';
        const sZero = wordsMap[0];

        function threeDigits(n) {
            if (n === 0) return '';
            const h = Math.floor(n / 100);
            const rem = n % 100;
            let res = '';
            if (h > 0) res += wordsMap[h] + ' ' + sHundred;
            if (rem > 0) {
                if (res) res += ' ';
                res += wordsMap[rem];
            }
            return res;
        }

        if (intStr === '0') {
            let decWords = '';
            if (parts.length > 1 && parts[1].length > 0) {
                let p = parseInt(parts[1].slice(0, 2).padEnd(2, '0'), 10);
                if (p > 0) decWords = ' ' + wordsMap[p] + ' ' + sPaisa;
            }
            return (isNeg ? sMinus + ' ' : '') + sZero + decWords + ' ' + sOnly;
        }

        let words = [];
        let remStr = intStr;

        // 1. Hundreds (last 3 digits)
        if (remStr.length > 0) {
            let take = remStr.slice(-3);
            remStr = remStr.slice(0, -3);
            let n = parseInt(take, 10);
            if (n > 0) words.unshift(threeDigits(n));
        }
        // 2. Thousands (next 2 digits)
        if (remStr.length > 0) {
            let take = remStr.slice(-2);
            remStr = remStr.slice(0, -2);
            let n = parseInt(take, 10);
            if (n > 0) words.unshift(wordsMap[n] + ' ' + sThousand);
        }
        // 3. Lakhs (next 2 digits)
        if (remStr.length > 0) {
            let take = remStr.slice(-2);
            remStr = remStr.slice(0, -2);
            let n = parseInt(take, 10);
            if (n > 0) words.unshift(wordsMap[n] + ' ' + sLakh);
        }
        // 4. Crores (next 2 digits)
        if (remStr.length > 0) {
            let take = remStr.slice(-2);
            remStr = remStr.slice(0, -2);
            let n = parseInt(take, 10);
            if (n > 0) words.unshift(wordsMap[n] + ' ' + sCrore);
        }
        // 5. Arabs (next 2 digits)
        if (remStr.length > 0) {
            let take = remStr.slice(-2);
            remStr = remStr.slice(0, -2);
            let n = parseInt(take, 10);
            if (n > 0) words.unshift(wordsMap[n] + ' ' + sArab);
        }
        // 6. Kharabs (next 2 digits)
        if (remStr.length > 0) {
            let take = remStr.slice(-2);
            remStr = remStr.slice(0, -2);
            let n = parseInt(take, 10);
            if (n > 0) words.unshift(wordsMap[n] + ' ' + sKharab);
        }

        let result = words.join(' ').trim();
        if (!result) result = sZero;

        if (parts.length > 1 && parts[1].length > 0) {
            let p = parseInt(parts[1].slice(0, 2).padEnd(2, '0'), 10);
            if (p > 0) {
                result += ' ' + wordsMap[p] + ' ' + sPaisa;
            }
        }

        return (isNeg ? sMinus + ' ' : '') + result + ' ' + sOnly;
    },

    convertEnglishToNepali(yy, mm, dd) {
        if (yy < 1944 || yy > 2033) return null;
        const month = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
        const lmonth = [31, 29, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];

        const def_eyy = 1944;
        const def_nyy = 2000;
        const def_nmm = 9;
        const def_ndd = 17 - 1;
        let total_edays = 0;
        let total_ndays = 0;

        let day = 7 - 1;
        let m = 0;
        let y = 0;

        for (let i = 0; i < (yy - def_eyy); i++) {
            const isLeap = this.isLeapYear(def_eyy + i);
            for (let j = 0; j < 12; j++) {
                total_edays += isLeap ? lmonth[j] : month[j];
            }
        }

        for (let i = 0; i < (mm - 1); i++) {
            total_edays += this.isLeapYear(yy) ? lmonth[i] : month[i];
        }
        total_edays += dd;

        let i = 0;
        let j = def_nmm;
        total_ndays = def_ndd;
        m = def_nmm;
        y = def_nyy;

        while (total_edays !== 0) {
            let a = this.bsData[i][j];
            total_ndays++;
            day++;

            if (total_ndays > a) {
                m++;
                total_ndays = 1;
                j++;
            }

            if (day > 7) day = 1;

            if (m > 12) {
                y++;
                m = 1;
            }

            if (j > 12) {
                j = 1;
                i++;
            }

            total_edays--;
        }

        const mmStr = String(m).padStart(2, '0');
        const ddStr = String(total_ndays).padStart(2, '0');

        return {
            year: y,
            month: m,
            day: total_ndays,
            weekday: day,
            bs_date: `${y}-${mmStr}-${ddStr}`,
            bs_words: `${total_ndays} ${this.monthsEn[m]}, ${y}`,
            bs_unicode: `${this.toDevanagariDigits(total_ndays)} ${this.monthsNp[m]} ${this.toDevanagariDigits(y)}`,
            bs_unicode_digits: `${this.toDevanagariDigits(y)}-${this.toDevanagariDigits(mmStr)}-${this.toDevanagariDigits(ddStr)}`,
            bs_preeti_words: `${this.toPreetiDigits(total_ndays)} ${this.monthsPreeti[m]} ${this.toPreetiDigits(y)}`,
            bs_preeti_digits: `${this.toPreetiDigits(y)}.${this.toPreetiDigits(mmStr)}.${this.toPreetiDigits(ddStr)}`
        };
    }
};

function generatorState() {
    const rawPermanentVars = @json($permanentVariables ?? []);
    const normalizedPermanentVars = rawPermanentVars.map(gv => ({
        key: gv.key,
        label: gv.label || gv.key,
        type: gv.type || 'text',
        dummy: gv.default_value || '',
        options: gv.options || '',
        formulas: Array.isArray(gv.formulas) ? JSON.parse(JSON.stringify(gv.formulas)) : [],
        is_permanent: true
    }));

    return {
        templates: @json($templates),
        employees: @json($employees),
        permanentVariables: normalizedPermanentVars,

        selectedTemplateId: @json($selectedTemplateId ? (string) $selectedTemplateId : ''),
        selectedTemplate: null,

        get allEffectiveVariables() {
            if (!this.selectedTemplate) return [];
            const tmplVars = (this.selectedTemplate.variables || []).map(v => {
                if (typeof v === 'string') return { key: v, type: 'text', label: v };
                return v;
            });
            const tmplKeySet = new Set(tmplVars.map(v => v.key));

            // Start with template variables, then append permanent global variables that aren't already explicitly defined in the template
            const combined = [...tmplVars];
            this.permanentVariables.forEach(pv => {
                if (!tmplKeySet.has(pv.key)) {
                    combined.push(pv);
                } else {
                    // Mark matching template variable as permanent
                    const existing = combined.find(item => item.key === pv.key);
                    if (existing) existing.is_permanent = true;
                }
            });
            return combined;
        },

        selectedCodes: [],
        customValues: {},
        dateFormats: {},
        search: '',
        showFilters: false,
        filterDepartment: '',
        filterDesignation: '',
        filterStatus: '',
        filterGender: '',
        showGuides: true,
        margins: { top: 25, bottom: 25, left: 20, right: 20 },
        differentFirstPageMargins: false,
        firstPageMargins: { top: 25, bottom: 25, left: 20, right: 20 },
        paginatedLetters: {},
        isSaving: false,
        saveMessage: '',

        deselectAll() {
            this.selectedCodes = [];
        },

        selectAllFiltered() {
            const codes = this.filteredEmployees.map(e => e.employee_code);
            // Union with existing selection to not lose others
            const set = new Set([...this.selectedCodes, ...codes]);
            this.selectedCodes = Array.from(set);
        },

        resetFilters() {
            this.filterDepartment = '';
            this.filterDesignation = '';
            this.filterStatus = '';
            this.filterGender = '';
            this.search = '';
        },

        get hasActiveFilters() {
            return Boolean(this.filterDepartment || this.filterDesignation || this.filterStatus || this.filterGender);
        },

        get activeFilterCount() {
            let count = 0;
            if (this.filterDepartment) count++;
            if (this.filterDesignation) count++;
            if (this.filterStatus) count++;
            if (this.filterGender) count++;
            return count;
        },

        get departmentOptions() {
            const set = new Set();
            this.employees.forEach(e => {
                const name = e.department ? (e.department.name || e.department) : '';
                if (name) set.add(name);
            });
            return Array.from(set).sort();
        },

        get designationOptions() {
            const set = new Set();
            this.employees.forEach(e => {
                const name = e.designation ? (e.designation.name || e.designation) : '';
                if (name) set.add(name);
            });
            return Array.from(set).sort();
        },

        get statusOptions() {
            const set = new Set();
            this.employees.forEach(e => {
                if (e.employee_status) set.add(e.employee_status);
            });
            return Array.from(set).sort();
        },

        async saveToHistory() {
            if (this.selectedCodes.length === 0 || !this.selectedTemplateId || this.isSaving) return;

            this.isSaving = true;
            this.saveMessage = '';

            const payloadLetters = this.selectedCodes.map(code => {
                const emp = this.getEmployee(code);
                return {
                    letter_template_id: this.selectedTemplateId ? parseInt(this.selectedTemplateId) : null,
                    employee_code: code,
                    template_title: this.selectedTemplate ? this.selectedTemplate.title : 'Untitled Template',
                    employee_name: emp ? emp.name : code,
                    content: this.renderLetter(code),
                    custom_values: this.customValues,
                    margins: {
                        top: this.margins.top,
                        bottom: this.margins.bottom,
                        left: this.margins.left,
                        right: this.margins.right,
                        different_first_page: this.differentFirstPageMargins,
                        first_page: {
                            top: this.firstPageMargins.top,
                            bottom: this.firstPageMargins.bottom,
                            left: this.firstPageMargins.left,
                            right: this.firstPageMargins.right,
                        }
                    }
                };
            });

            try {
                const res = await fetch('{{ route('letters.save-generated') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ letters: payloadLetters })
                });

                const data = await res.json();
                if (data.success) {
                    this.saveMessage = 'Saved to history!';
                    setTimeout(() => { this.saveMessage = ''; }, 3000);
                } else {
                    alert(data.message || 'Failed to save letters to history.');
                }
            } catch (e) {
                alert('An error occurred while saving letters to history.');
            } finally {
                this.isSaving = false;
            }
        },

        init() {
            if (!this.selectedTemplateId && this.templates && this.templates.length > 0) {
                this.selectedTemplateId = String(this.templates[0].id);
            }
            if (this.selectedTemplateId) {
                this.onTemplateChange();
            }

            this.$watch('selectedTemplateId', () => this.onTemplateChange());
            this.$watch('selectedCodes', () => this.updatePaginatedLetters());
            this.$watch('customValues', () => this.updatePaginatedLetters(), { deep: true });
            this.$watch('dateFormats', () => this.updatePaginatedLetters(), { deep: true });
            this.$watch('margins', () => this.updatePaginatedLetters(), { deep: true });
            this.$watch('firstPageMargins', () => this.updatePaginatedLetters(), { deep: true });
            this.$watch('differentFirstPageMargins', () => this.updatePaginatedLetters());
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
            this.dateFormats = {};

            if (this.selectedTemplate) {
                this.differentFirstPageMargins = Boolean(this.selectedTemplate.different_first_page_margins);
                this.margins.top    = this.selectedTemplate.margin_top    ?? 25;
                this.margins.bottom = this.selectedTemplate.margin_bottom ?? 25;
                this.margins.left   = this.selectedTemplate.margin_left   ?? 20;
                this.margins.right  = this.selectedTemplate.margin_right  ?? 20;
                this.firstPageMargins.top    = this.selectedTemplate.first_page_margin_top    ?? this.margins.top;
                this.firstPageMargins.bottom = this.selectedTemplate.first_page_margin_bottom ?? this.margins.bottom;
                this.firstPageMargins.left   = this.selectedTemplate.first_page_margin_left   ?? this.margins.left;
                this.firstPageMargins.right  = this.selectedTemplate.first_page_margin_right  ?? this.margins.right;

                const isTemplatePreeti = Boolean(
                    (this.selectedTemplate && this.selectedTemplate.font_family === 'Preeti') ||
                    (this.selectedTemplate && (this.selectedTemplate.content || '').includes("'Preeti'")) ||
                    (this.selectedTemplate && (this.selectedTemplate.content || '').includes('"Preeti"'))
                );

                this.allEffectiveVariables.forEach(v => {
                    const key  = typeof v === 'object' ? v.key : v;
                    const type = typeof v === 'object' ? (v.type || 'text') : 'text';
                    if (type === 'date' || type === 'daterange') {
                        this.dateFormats[key] = isTemplatePreeti ? 'bs_preeti_words' : 'ad_full';
                    } else if (type === 'number' || type === 'calculated') {
                        this.dateFormats[key] = isTemplatePreeti ? 'preeti_comma' : 'en';
                    } else if (type === 'amount') {
                        this.dateFormats[key] = isTemplatePreeti ? 'preeti_comma' : 'nepali_comma';
                    }
                    if (type === 'dropdown' && typeof v === 'object' && v.options && v.options.trim()) {
                        const first = v.options.split(',').map(s => s.trim()).filter(s => s)[0] || '';
                        this.customValues[key] = (typeof v === 'object' && v.dummy) ? v.dummy : first;
                    } else if (typeof v === 'object' && v.dummy) {
                        this.customValues[key] = v.dummy;
                    } else {
                        this.customValues[key] = '';
                    }

                    if (type === 'daterange') {
                        const val = this.customValues[key] || '';
                        const parts = val ? val.split(' to ') : [];
                        this.customValues[key + '_from'] = parts[0] || '';
                        this.customValues[key + '_to'] = parts[1] || parts[0] || '';
                    }

                    // Initialize all formula child keys too
                    if (type === 'calculated' && Array.isArray(v.formulas)) {
                        v.formulas.forEach(f => {
                            if (f.key) {
                                this.customValues[f.key] = '';
                                this.dateFormats[f.key] = isTemplatePreeti ? 'preeti_comma' : 'en_comma';
                            }
                        });
                    }
                });
                this.computeFormulas();
            }
            this.updatePaginatedLetters();
        },

        // Evaluate formula expressions for all 'calculated' variables in sequence
        computeFormulas() {
            if (!this.selectedTemplate || this.allEffectiveVariables.length === 0) return;

            for (let pass = 0; pass < 5; pass++) {
                let changed = false;

                // 1. Build a clean JS dictionary object of all known variable values
                const scopeObj = {};

                // Include all customValues (numerical conversion)
                Object.keys(this.customValues).forEach(k => {
                    const raw = this.customValues[k];
                    const n = (raw === '' || raw === null || raw === undefined) ? 0 : parseFloat(String(raw).replace(/,/g, ''));
                    const val = isNaN(n) ? 0 : n;
                    scopeObj[k] = val;
                    // Also alias normalized keys (lowercase, no spaces/special chars)
                    const norm = k.toLowerCase().replace(/[^a-z0-9]/g, '');
                    if (norm) scopeObj[norm] = val;
                });

                // Include effective variable keys & labels
                this.allEffectiveVariables.forEach(v => {
                    if (typeof v === 'object') {
                        const raw = this.customValues[v.key];
                        const n = (raw === '' || raw === null || raw === undefined) ? 0 : parseFloat(String(raw).replace(/,/g, ''));
                        const val = isNaN(n) ? 0 : n;
                        if (v.key) {
                            scopeObj[v.key] = val;
                            const normKey = v.key.toLowerCase().replace(/[^a-z0-9]/g, '');
                            if (normKey) scopeObj[normKey] = val;
                        }
                    }
                });

                // 2. Evaluate formulas using scopeObj
                this.allEffectiveVariables.forEach(v => {
                    if (typeof v === 'object' && v.type === 'calculated' && Array.isArray(v.formulas)) {
                        const parentVal = this.customValues[v.key];
                        const isParentEmpty = parentVal === '' || parentVal === null || parentVal === undefined;

                        v.formulas.forEach(f => {
                            if (!f.key || !f.expression) return;

                            // If parent calculated variable has no value set, keep formula result blank
                            if (isParentEmpty) {
                                if (this.customValues[f.key] !== '') {
                                    this.customValues[f.key] = '';
                                    delete scopeObj[f.key];
                                    const normFKey = f.key.toLowerCase().replace(/[^a-z0-9]/g, '');
                                    if (normFKey) delete scopeObj[normFKey];
                                    changed = true;
                                }
                                return;
                            }

                            try {
                                const rawExpr = String(f.expression).trim();
                                if (!rawExpr) return;

                                // Transform user expression: replace text identifiers with scope lookup
                                // Example: "gross * 0.5" or "GROSS * 0.5" or "gross_salary * 0.5" -> "scope['gross'] * 0.5"
                                const transformedExpr = rawExpr.replace(/\b([a-zA-Z_][a-zA-Z0-9_]*)\b/g, (match) => {
                                    // If match is a JS math operator/keyword, preserve it
                                    if (['Math', 'abs', 'round', 'ceil', 'floor', 'min', 'max', 'pow', 'sqrt', 'return', 'true', 'false'].includes(match)) {
                                        return match;
                                    }
                                    const exactMatch = Object.keys(scopeObj).find(k => k === match);
                                    if (exactMatch) return `scope['${exactMatch}']`;
                                    const normMatch = match.toLowerCase().replace(/[^a-z0-9]/g, '');
                                    const foundKey = Object.keys(scopeObj).find(k => k.toLowerCase().replace(/[^a-z0-9]/g, '') === normMatch);
                                    if (foundKey) return `scope['${foundKey}']`;
                                    return `(scope['${match}'] || 0)`;
                                });

                                const fn = new Function('scope', 'return (' + transformedExpr + ')');
                                const result = fn(scopeObj);
                                const num = parseFloat(result);
                                const formatted = isNaN(num) ? '' : (num % 1 === 0 ? String(num) : num.toFixed(2));

                                if (this.customValues[f.key] !== formatted) {
                                    this.customValues[f.key] = formatted;
                                    scopeObj[f.key] = isNaN(num) ? 0 : num;
                                    const normFKey = f.key.toLowerCase().replace(/[^a-z0-9]/g, '');
                                    if (normFKey) scopeObj[normFKey] = isNaN(num) ? 0 : num;
                                    changed = true;
                                }
                            } catch (e) {
                                if (this.customValues[f.key] !== '') {
                                    this.customValues[f.key] = '';
                                    changed = true;
                                }
                            }
                        });
                    }
                });

                if (!changed) break;
            }
        },

        // Format a formula result value for the preview table
        formatFormulaResult(val) {
            if (val === '' || val === null || val === undefined) return '—';
            const n = parseFloat(val);
            if (isNaN(n)) return val;
            // Add thousands separator
            return n % 1 === 0
                ? n.toLocaleString('en-US', { maximumFractionDigits: 0 })
                : n.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
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
            if (!this.selectedTemplateId) {
                this.paginatedLetters = {};
                return;
            }

            const codes = this.selectedCodes;

            if (codes.length === 0) {
                this.paginatedLetters = {};
                return;
            }

            const pagesMap = {};

            codes.forEach(code => {
                let full = this.renderLetter(code);
                if (!full) return;

                const splitPages = full.split(/<!--\s*(?:MANUAL_)?PAGE_BREAK\s*-->/gi)
                    .map(p => p.trim())
                    .filter(p => p.length > 0);

                pagesMap[code] = splitPages.length > 0 ? splitPages : [full];
            });

            this.paginatedLetters = pagesMap;
        },

        renderLetter(code) {
            if (!this.selectedTemplate) return '';
            const emp = this.getEmployee(code);
            if (!emp) return '';

            let html = this.selectedTemplate.content || '';

            // Clean any potential template variable chips to tokens, preserving data-font if specified
            html = html.replace(/<span\b[^>]*class="[^"]*template-variable[^"]*"[^>]*data-var(?:-key)?="([^"]+)"[^>]*data-font="([^"]+)"[^>]*>[\s\S]*?<\/span>/gi, function(match, key, font) {
                return `<span style="font-family: '${font}';" data-var-font="${font}">` + '{' + '{ ' + key + ' }' + '}' + `</span>`;
            });
            html = html.replace(/<span\b[^>]*class="[^"]*template-variable[^"]*"[^>]*data-var(?:-key)?="([^"]+)"[^>]*>[\s\S]*?<\/span>/gi, '{' + '{ $1 }' + '}');

            // Determine if template uses legacy ASCII Preeti font
            const isTemplatePreeti = Boolean(
                (this.selectedTemplate && this.selectedTemplate.font_family === 'Preeti') ||
                (this.selectedTemplate && (this.selectedTemplate.content || '').includes("'Preeti'")) ||
                (this.selectedTemplate && (this.selectedTemplate.content || '').includes('"Preeti"'))
            );

            // Replace prebuilt employee variables — use character class [{}]{2} to avoid Blade parsing
            const prebuilts = {
                employee_name:                     this.getEmployeeNameWithPrefix(emp.name, emp.gender),
                employee_first_name:               emp.first_name || '',
                employee_middle_name:              emp.middle_name || '',
                employee_last_name:                emp.last_name || '',
                employee_employee_code:            emp.employee_code || '',
                employee_department:               emp.department ? (emp.department.name || '') : '',
                employee_designation:              emp.designation ? (emp.designation.name || '') : '',
                employee_job_description:          emp.designation ? (emp.designation.job_description || '') : '',
                employee_gender:                   emp.gender || '',
                employee_join_date:                this.formatDate(emp.join_date_formatted),
                employee_contact_number:           emp.contact_number || '',
                employee_email:                    emp.email || '',
                employee_citizenship_number:       emp.citizenship_number || '',
                employee_citizenship_issue_date:   emp.citizenship_issue_date || '',
                employee_citizenship_issue_place:  emp.citizenship_issue_place || '',
                employee_ssid:                     emp.ssid || '',
                employee_dob_ad:                   this.formatDate(emp.dob_ad),
                employee_dob_bs:                   emp.dob_bs || '',
                employee_marital_status:           emp.marital_status || '',
                employee_employee_status:          emp.employee_status || '',
                employee_rank:                     emp.rank || '',
                employee_dp_rank:                  emp.dp_rank || '',
                employee_tips_amount:              emp.tips_amount || '0',
                employee_tips_status:              emp.tips_status || '',
                employee_point_value:              emp.point_value || '0',
                employee_tips_blank:               emp.tips_blank ? 'Yes' : 'No',
                employee_publish_tips:             emp.publish_tips ? 'Yes' : 'No',
                employee_tips_fixed:               emp.tips_fixed ? 'Yes' : 'No',
                employee_his_her:                  this.getHisHer(emp.gender, false),
                employee_he_she:                   this.getHeShe(emp.gender, false),
                employee_him_her:                  this.getHimHer(emp.gender, false),
                employee_his_her_cap:              this.getHisHer(emp.gender, true),
                employee_he_she_cap:               this.getHeShe(emp.gender, true),
                employee_him_her_cap:              this.getHimHer(emp.gender, true),
            };

            Object.entries(prebuilts).forEach(([k, val]) => {
                const safeVal = this.wrapVarVal(val, 'text', isTemplatePreeti);
                const rx = new RegExp('[{]{2}\\s*' + k + '\\s*[}]{2}', 'g');
                html = html.replace(rx, safeVal);
            });

            // Replace custom and permanent template variables
            (this.allEffectiveVariables || []).forEach(v => {
                const key  = typeof v === 'object' ? v.key : v;
                const type = typeof v === 'object' ? (v.type || 'text') : 'text';
                let val = this.customValues[key] || '';
                const fmt = this.dateFormats[key] || (type === 'number' || type === 'calculated' ? (isTemplatePreeti ? 'preeti_comma' : 'en') : 'ad_full');

                if (type === 'date' && val) {
                    val = this.formatDateWithFormat(val, fmt);
                    val = this.wrapVarVal(val, fmt, isTemplatePreeti);
                }

                if (type === 'daterange') {
                    const rawFrom = this.customValues[key + '_from'] || '';
                    const rawTo   = this.customValues[key + '_to']   || '';

                    let rangeStr = '';
                    let formattedFrom = '';
                    let formattedTo   = '';

                    if (rawFrom && rawTo) {
                        rangeStr = this.formatDateRangeWithFormat(rawFrom, rawTo, fmt);
                        rangeStr = this.wrapVarVal(rangeStr, fmt, isTemplatePreeti);
                    }
                    if (rawFrom) {
                        formattedFrom = this.formatDateWithFormat(rawFrom, fmt);
                        formattedFrom = this.wrapVarVal(formattedFrom, fmt, isTemplatePreeti);
                    }
                    if (rawTo) {
                        formattedTo = this.formatDateWithFormat(rawTo, fmt);
                        formattedTo = this.wrapVarVal(formattedTo, fmt, isTemplatePreeti);
                    }

                    val = rangeStr;

                    // Substitute sub-keys (key_from and key_to)
                    const rxFrom = new RegExp('[{]{2}\\s*' + key + '_from\\s*[}]{2}', 'g');
                    html = html.replace(rxFrom, formattedFrom);

                    const rxTo = new RegExp('[{]{2}\\s*' + key + '_to\\s*[}]{2}', 'g');
                    html = html.replace(rxTo, formattedTo);
                }

                if (type === 'amount') {
                    const conv = this.getAmountConversions(val);
                    if (conv) {
                        const subs = {
                            [key + '_raw']: this.wrapVarVal(conv.raw, 'text', isTemplatePreeti),
                            [key + '_comma']: this.wrapVarVal(conv.en_comma, 'en_comma', isTemplatePreeti),
                            [key + '_commas']: this.wrapVarVal(conv.en_comma, 'en_comma', isTemplatePreeti),
                            [key + '_en_comma']: this.wrapVarVal(conv.en_comma, 'en_comma', isTemplatePreeti),
                            [key + '_nepali_comma']: this.wrapVarVal(conv.nepali_comma, 'en_comma', isTemplatePreeti),
                            [key + '_nepali']: this.wrapVarVal(conv.devanagari_comma, 'devanagari_comma', isTemplatePreeti),
                            [key + '_devanagari_comma']: this.wrapVarVal(conv.devanagari_comma, 'devanagari_comma', isTemplatePreeti),
                            [key + '_devanagari']: this.wrapVarVal(conv.devanagari_comma, 'devanagari_comma', isTemplatePreeti),
                            [key + '_preeti']: this.wrapVarVal(conv.preeti_comma, 'preeti_comma', isTemplatePreeti),
                            [key + '_preeti_comma']: this.wrapVarVal(conv.preeti_comma, 'preeti_comma', isTemplatePreeti),
                            [key + '_words_en']: this.wrapVarVal(conv.words_en, 'text', isTemplatePreeti),
                            [key + '_in_words_en']: this.wrapVarVal(conv.words_en, 'text', isTemplatePreeti),
                            [key + '_words']: this.wrapVarVal(conv.words_en, 'text', isTemplatePreeti),
                            [key + '_words_np']: this.wrapVarVal(conv.words_np, 'devanagari', isTemplatePreeti),
                            [key + '_in_words_np']: this.wrapVarVal(conv.words_np, 'devanagari', isTemplatePreeti),
                            [key + '_nepali_words']: this.wrapVarVal(conv.words_np, 'devanagari', isTemplatePreeti),
                            [key + '_words_preeti']: this.wrapVarVal(conv.words_preeti, 'preeti', isTemplatePreeti),
                            [key + '_preeti_words']: this.wrapVarVal(conv.words_preeti, 'preeti', isTemplatePreeti),
                        };

                        Object.entries(subs).forEach(([subKey, subVal]) => {
                            const rxSub = new RegExp('[{]{2}\\s*' + subKey + '\\s*[}]{2}', 'g');
                            html = html.replace(rxSub, subVal);
                        });

                        const fmt = this.dateFormats[key] || (isTemplatePreeti ? 'preeti_comma' : 'nepali_comma');
                        let formattedBase = conv.raw;
                        if (fmt === 'raw') formattedBase = conv.raw;
                        else if (fmt === 'en_comma') formattedBase = conv.en_comma;
                        else if (fmt === 'nepali_comma') formattedBase = conv.nepali_comma;
                        else if (fmt === 'devanagari_comma') formattedBase = conv.devanagari_comma;
                        else if (fmt === 'preeti_comma') formattedBase = conv.preeti_comma;
                        else if (fmt === 'words_en') formattedBase = conv.words_en;
                        else if (fmt === 'words_np') formattedBase = conv.words_np;
                        else if (fmt === 'words_preeti') formattedBase = conv.words_preeti;
                        else formattedBase = conv.raw;

                        val = this.wrapVarVal(formattedBase, fmt, isTemplatePreeti);
                    } else {
                        val = '';
                    }
                }

                if ((type === 'number' || type === 'calculated') && (val !== '' && val !== null && val !== undefined)) {
                    val = this.formatNumberWithFormat(val, fmt);
                    val = this.wrapVarVal(val, fmt, isTemplatePreeti);
                }

                if (type !== 'richtext' && type !== 'date' && type !== 'daterange' && type !== 'number' && type !== 'amount' && type !== 'calculated' && val) {
                    val = this.wrapVarVal(val, 'text', isTemplatePreeti);
                }

                const rx = new RegExp('[{]{2}\\s*' + key + '\\s*[}]{2}', 'g');
                html = html.replace(rx, val);

                // Also substitute formula child keys for 'calculated' variables
                if (type === 'calculated' && Array.isArray(v.formulas)) {
                    v.formulas.forEach(f => {
                        if (!f.key) return;
                        let fVal = this.customValues[f.key] || '';
                        const fFmt = this.dateFormats[f.key] || fmt || (isTemplatePreeti ? 'preeti_comma' : 'en_comma');
                        if (fVal !== '' && fVal !== null && fVal !== undefined) {
                            fVal = this.formatNumberWithFormat(fVal, fFmt);
                            fVal = this.wrapVarVal(fVal, fFmt, isTemplatePreeti);
                        }
                        const frx = new RegExp('[{]{2}\\s*' + f.key + '\\s*[}]{2}', 'g');
                        html = html.replace(frx, fVal);
                    });
                }
            });

            return html;
        },

        getNepaliDateInfo(dateStr) {
            if (!dateStr) return null;
            const parts = String(dateStr).split('-');
            if (parts.length < 3) return null;
            const y = parseInt(parts[0], 10);
            const m = parseInt(parts[1], 10);
            const d = parseInt(parts[2], 10);
            if (isNaN(y) || isNaN(m) || isNaN(d)) return null;
            return nepaliCalendar.convertEnglishToNepali(y, m, d);
        },

        formatDateWithFormat(dateStr, formatType) {
            if (!dateStr) return '';
            formatType = formatType || 'ad_full';
            if (formatType === 'ad_full') {
                return this.formatDate(dateStr);
            }
            if (formatType === 'ad_standard') {
                return dateStr;
            }
            const info = this.getNepaliDateInfo(dateStr);
            if (!info) return this.formatDate(dateStr);
            if (formatType === 'bs_words') {
                return info.bs_words;
            }
            if (formatType === 'bs_digits') {
                return info.bs_date;
            }
            if (formatType === 'bs_unicode') {
                return info.bs_unicode;
            }
            if (formatType === 'bs_preeti_words') {
                return info.bs_preeti_words;
            }
            if (formatType === 'bs_preeti_digits') {
                return info.bs_preeti_digits;
            }
            return this.formatDate(dateStr);
        },

        formatDateRangeWithFormat(fromStr, toStr, formatType) {
            if (!fromStr || !toStr) return '';
            formatType = formatType || 'ad_full';
            if (formatType === 'ad_full') {
                return 'from ' + this.formatDate(fromStr) + ' to ' + this.formatDate(toStr);
            }
            if (formatType === 'ad_standard') {
                return fromStr + ' to ' + toStr;
            }
            const fInfo = this.getNepaliDateInfo(fromStr);
            const tInfo = this.getNepaliDateInfo(toStr);
            if (!fInfo || !tInfo) {
                return 'from ' + this.formatDate(fromStr) + ' to ' + this.formatDate(toStr);
            }
            if (formatType === 'bs_words') {
                return 'from ' + fInfo.bs_words + ' to ' + tInfo.bs_words;
            }
            if (formatType === 'bs_digits') {
                return fInfo.bs_date + ' to ' + tInfo.bs_date;
            }
            if (formatType === 'bs_unicode') {
                return fInfo.bs_unicode + ' देखि ' + tInfo.bs_unicode + ' सम्म';
            }
            if (formatType === 'bs_preeti_words') {
                return fInfo.bs_preeti_words + ' b]lv ' + tInfo.bs_preeti_words + ' ;Dd';
            }
            if (formatType === 'bs_preeti_digits') {
                return fInfo.bs_preeti_digits + ' b]lv ' + tInfo.bs_preeti_digits + ' ;Dd';
            }
            return 'from ' + this.formatDate(fromStr) + ' to ' + this.formatDate(toStr);
        },

        getAmountConversions(val) {
            if (val === '' || val === null || val === undefined) return null;
            const strVal = String(val).trim();
            if (strVal === '') return null;
            
            const raw = strVal;
            const enComma = nepaliCalendar.formatWesternComma(strVal);
            const nepaliComma = nepaliCalendar.formatNepaliComma(strVal);
            const devanagariComma = nepaliCalendar.toDevanagariDigits(nepaliComma);
            const preetiComma = nepaliCalendar.toPreetiDigits(nepaliComma);
            const wordsEn = nepaliCalendar.amountToWordsEnglish(strVal);
            const wordsNp = nepaliCalendar.amountToWordsNepali(strVal, false);
            const wordsPreeti = nepaliCalendar.amountToWordsNepali(strVal, true);

            return {
                raw,
                en_comma: enComma,
                nepali_comma: nepaliComma,
                devanagari_comma: devanagariComma,
                preeti_comma: preetiComma,
                words_en: wordsEn,
                words_np: wordsNp,
                words_preeti: wordsPreeti
            };
        },

        formatNumberWithFormat(val, formatType) {
            if (val === '' || val === null || val === undefined) return '';
            formatType = formatType || 'en';

            const strVal = String(val).trim();
            if (strVal === '') return '';

            const isNegative = strVal.startsWith('-');
            const cleanStr = isNegative ? strVal.slice(1) : strVal;
            const parts = cleanStr.split('.');

            const intNoComma = parts[0].replace(/^0+(?=\d)/, '') || '0';
            const decPart = parts.length > 1 ? '.' + parts[1] : '';

            const enNoComma = (isNegative ? '-' : '') + intNoComma + decPart;
            const enWithComma = (isNegative ? '-' : '') + nepaliCalendar.formatWesternComma(cleanStr);
            const nepaliWithComma = (isNegative ? '-' : '') + nepaliCalendar.formatNepaliComma(cleanStr);

            if (formatType === 'en') {
                return enNoComma;
            }
            if (formatType === 'en_comma') {
                return enWithComma;
            }
            if (formatType === 'nepali_comma') {
                return nepaliWithComma;
            }
            if (formatType === 'devanagari') {
                return nepaliCalendar.toDevanagariDigits(enNoComma);
            }
            if (formatType === 'devanagari_comma') {
                return nepaliCalendar.toDevanagariDigits(nepaliWithComma);
            }
            if (formatType === 'preeti') {
                return nepaliCalendar.toPreetiDigits(enNoComma);
            }
            if (formatType === 'preeti_comma') {
                return nepaliCalendar.toPreetiDigits(nepaliWithComma);
            }

            return enNoComma;
        },

        wrapVarVal(val, formatType, isTemplatePreeti) {
            if (val === '' || val === null || val === undefined) return '';
            const strVal = String(val);

            if (formatType === 'bs_preeti_words' || formatType === 'bs_preeti_digits' || formatType === 'preeti' || formatType === 'preeti_comma' || formatType === 'words_preeti') {
                return `<span style="font-family: 'Preeti', sans-serif !important;">${strVal}</span>`;
            }

            if (formatType === 'bs_unicode' || formatType === 'devanagari' || formatType === 'devanagari_comma' || formatType === 'words_np') {
                return `<span style="font-family: 'Kalimati', 'Noto Sans Devanagari', 'Mangal', sans-serif !important;">${strVal}</span>`;
            }

            if (isTemplatePreeti && (formatType === 'en' || formatType === 'en_comma' || formatType === 'nepali_comma' || formatType === 'raw' || formatType === 'words_en' || formatType === 'ad_full' || formatType === 'ad_standard' || formatType === 'text')) {
                return `<span style="font-family: Arial, 'Times New Roman', sans-serif !important;">${strVal}</span>`;
            }

            return strVal;
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

        getHimHer(gender, capitalize) {
            const g = (gender || '').toLowerCase();
            let val = 'them';
            if (g === 'male' || g === 'm') val = 'him';
            else if (g === 'female' || g === 'f') val = 'her';
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

        get filteredEmployees() {
            const q = (this.search || '').toLowerCase().trim();
            const dept = (this.filterDepartment || '').toLowerCase();
            const desig = (this.filterDesignation || '').toLowerCase();
            const status = (this.filterStatus || '').toLowerCase();
            const gender = (this.filterGender || '').toLowerCase();

            return this.employees.filter(e => {
                // Search query match
                if (q) {
                    const name = (e.name || '').toLowerCase();
                    const code = (e.employee_code || '').toLowerCase();
                    const dName = (e.department ? (e.department.name || e.department) : '').toLowerCase();
                    const desigName = (e.designation ? (e.designation.name || e.designation) : '').toLowerCase();
                    if (!name.includes(q) && !code.includes(q) && !dName.includes(q) && !desigName.includes(q)) {
                        return false;
                    }
                }

                // Department filter
                if (dept) {
                    const eDept = (e.department ? (e.department.name || e.department) : '').toLowerCase();
                    if (eDept !== dept) return false;
                }

                // Designation filter
                if (desig) {
                    const eDesig = (e.designation ? (e.designation.name || e.designation) : '').toLowerCase();
                    if (eDesig !== desig) return false;
                }

                // Status filter
                if (status) {
                    const eStatus = (e.employee_status || '').toLowerCase();
                    if (eStatus !== status) return false;
                }

                // Gender filter
                if (gender) {
                    const eGender = (e.gender || '').toLowerCase();
                    if (eGender !== gender) return false;
                }

                return true;
            });
        },

        getEmployee(code) {
            return this.employees.find(e => String(e.employee_code) === String(code));
        }
    };
}
</script>
@endsection

