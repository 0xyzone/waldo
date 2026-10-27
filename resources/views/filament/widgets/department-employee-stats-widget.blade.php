<x-filament-widgets::widget>
    @php
        $totalWorkforce = collect($this->stats)->sum('count');
        $activeDeptsCount = count($this->stats);
        $totalDesignationsCount = collect($this->stats)->flatMap(fn($d) => $d['designations'])->filter(fn($des) => $des['count'] > 0)->count();
    @endphp

    <div x-data="{
            isOpen: false,
            activeDeptId: null,
            activeName: '',
            activeCount: 0,
            avgTenure: '--',
            gender: { male_count: 0, female_count: 0, male_percent: 0, female_percent: 0 },
            allEmployees: [],
            filteredEmployees: [],
            searchQuery: '',

            openModal(deptData) {
                this.activeDeptId = deptData.id;
                this.activeName = deptData.name;
                this.activeCount = deptData.count;
                this.avgTenure = (deptData.stats && deptData.stats.avg_tenure) ? deptData.stats.avg_tenure + ' Yrs' : '--';
                this.gender = (deptData.stats && deptData.stats.gender) ? deptData.stats.gender : { male_count: 0, female_count: 0, male_percent: 0, female_percent: 0 };
                this.allEmployees = deptData.employees || [];
                this.searchQuery = '';
                this.filterEmployees();
                this.isOpen = true;
            },

            closeModal() {
                this.isOpen = false;
            },

            filterEmployees() {
                const q = (this.searchQuery || '').toLowerCase().trim();
                if (!q) {
                    this.filteredEmployees = this.allEmployees;
                } else {
                    this.filteredEmployees = this.allEmployees.filter(emp =>
                        (emp.code && emp.code.toLowerCase().includes(q)) ||
                        (emp.name && emp.name.toLowerCase().includes(q)) ||
                        (emp.designation && emp.designation.toLowerCase().includes(q))
                    );
                }
            },

            exportExcel() {
                if (!this.activeDeptId) return;
                window.location.href = `{{ url('/reports/departments') }}/${this.activeDeptId}/export`;
            },

            printReport() {
                if (!this.activeDeptId) return;
                window.open(`{{ url('/reports/departments') }}/${this.activeDeptId}/print`, '_blank');
            },

            // Print Report Scope Modal State
            isPrintModalOpen: false,
            printScope: 'all',
            selectedDeptIds: [],
            deptSearch: '',
            isDeptDropdownOpen: false,
            printValidationWarning: false,
            availableDepartments: @js(collect($this->stats)->map(fn($d) => ['id' => $d['id'], 'name' => $d['name'], 'count' => $d['count']])->values()),

            openPrintModal() {
                this.isPrintModalOpen = true;
                this.printValidationWarning = false;
                this.deptSearch = '';
                this.isDeptDropdownOpen = false;
            },

            closePrintModal() {
                this.isPrintModalOpen = false;
                this.isDeptDropdownOpen = false;
                this.printValidationWarning = false;
            },

            toggleDept(id) {
                const idx = this.selectedDeptIds.indexOf(id);
                if (idx > -1) {
                    this.selectedDeptIds.splice(idx, 1);
                } else {
                    this.selectedDeptIds.push(id);
                }
                if (this.selectedDeptIds.length > 0) {
                    this.printValidationWarning = false;
                }
            },

            selectAllDepts() {
                this.selectedDeptIds = this.availableDepartments.map(d => d.id);
                this.printValidationWarning = false;
            },

            clearDeptSelection() {
                this.selectedDeptIds = [];
            },

            get filteredAvailableDepartments() {
                const q = (this.deptSearch || '').toLowerCase().trim();
                if (!q) return this.availableDepartments;
                return this.availableDepartments.filter(d => d.name.toLowerCase().includes(q));
            },

            submitPrintReport() {
                if (this.printScope === 'all') {
                    window.open('{{ url('/reports/departments') }}', '_blank');
                    this.closePrintModal();
                } else {
                    if (this.selectedDeptIds.length === 0) {
                        this.printValidationWarning = true;
                        this.isDeptDropdownOpen = true;
                        return;
                    }
                    const url = '{{ url('/reports/departments') }}?department_ids=' + this.selectedDeptIds.join(',');
                    window.open(url, '_blank');
                    this.closePrintModal();
                }
            }
         }"
         @keydown.escape.window="if (isPrintModalOpen) { closePrintModal(); } else if (isOpen) { closeModal(); }"
         class="relative w-full rounded-3xl bg-white dark:bg-[#121826] border border-slate-200/80 dark:border-white/[0.07] p-6 sm:p-8 shadow-[0_2px_12px_rgba(15,23,42,0.03)] dark:shadow-[0_10px_30px_rgba(0,0,0,0.35)] transition-all duration-300">

        <!-- Top Header & Actions -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 pb-6 border-b border-slate-100 dark:border-white/[0.06]">
            <div class="flex items-center gap-4">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-tr from-orange-500 to-red-600 text-white shadow-md shadow-orange-500/20">
                    <x-filament::icon icon="heroicon-s-briefcase" class="h-6 w-6 text-white" />
                </div>
                <div>
                    <h2 class="text-xl font-extrabold tracking-tight text-slate-900 dark:text-slate-100">
                        Department Overview
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-0.5">
                        Active staff allocation across organizational departments and designations.
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2.5">
                <a href="{{ url('/reports/departments/view') }}" target="_blank"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-200 bg-slate-100/80 dark:bg-slate-800/80 hover:bg-orange-50 dark:hover:bg-orange-950/30 hover:text-orange-600 dark:hover:text-orange-400 border border-slate-200/80 dark:border-white/[0.08] transition-all cursor-pointer">
                    <x-filament::icon icon="heroicon-m-arrow-top-right-on-square" class="h-4 w-4" />
                    <span>Interactive View</span>
                </a>
                <button type="button" @click="openPrintModal()"
                   class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-orange-500 to-red-600 hover:from-orange-600 hover:to-red-700 shadow-md shadow-orange-500/20 hover:shadow-lg hover:shadow-orange-500/30 transition-all active:scale-95 cursor-pointer">
                    <x-filament::icon icon="heroicon-m-printer" class="h-4 w-4" />
                    <span>Print Report</span>
                </button>
            </div>
        </div>

        <!-- Metric Summary Chips -->
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 pt-6 pb-6 border-b border-slate-100 dark:border-white/[0.06]">
            <div class="p-4 rounded-2xl bg-slate-50/70 dark:bg-slate-900/40 border border-slate-100 dark:border-white/[0.04]">
                <div class="text-[10.5px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Active Workforce</div>
                <div class="text-2xl font-black text-slate-900 dark:text-slate-100 mt-1 flex items-baseline gap-1">
                    {{ $totalWorkforce }}
                    <span class="text-xs font-normal text-slate-400">staff</span>
                </div>
            </div>
            <div class="p-4 rounded-2xl bg-slate-50/70 dark:bg-slate-900/40 border border-slate-100 dark:border-white/[0.04]">
                <div class="text-[10.5px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Active Departments</div>
                <div class="text-2xl font-black text-orange-600 dark:text-orange-400 mt-1 flex items-baseline gap-1">
                    {{ $activeDeptsCount }}
                    <span class="text-xs font-normal text-slate-400">units</span>
                </div>
            </div>
            <div class="p-4 rounded-2xl bg-slate-50/70 dark:bg-slate-900/40 border border-slate-100 dark:border-white/[0.04] col-span-2 sm:col-span-1">
                <div class="text-[10.5px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">Staffed Roles</div>
                <div class="text-2xl font-black text-slate-900 dark:text-slate-100 mt-1 flex items-baseline gap-1">
                    {{ $totalDesignationsCount }}
                    <span class="text-xs font-normal text-slate-400">positions</span>
                </div>
            </div>
        </div>

        <!-- Department Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-5 pt-6">
            @foreach($this->stats as $dept)
                @php
                    $deptJson = json_encode([
                        'id' => $dept['id'],
                        'name' => $dept['name'],
                        'count' => $dept['count'],
                        'employees' => $dept['employees'],
                        'stats' => $dept['stats']
                    ]);
                    $activeDesignations = collect($dept['designations'])->filter(fn($d) => $d['count'] > 0);
                @endphp
                <div @click="openModal({{ $deptJson }})"
                     class="p-5 rounded-2xl bg-slate-50/50 dark:bg-slate-900/30 border border-slate-200/70 dark:border-white/[0.06] hover:bg-white dark:hover:bg-slate-800/40 hover:border-orange-500/40 dark:hover:border-orange-500/40 hover:shadow-md hover:shadow-orange-500/5 transition-all duration-200 flex flex-col justify-between min-h-[220px] cursor-pointer group">

                    <div>
                        <!-- Department Title & Count Pill -->
                        <div class="flex items-start justify-between gap-2 mb-3.5">
                            <div class="flex items-center gap-2">
                                <span class="w-1.5 h-4.5 rounded-full bg-gradient-to-b from-orange-500 to-red-600 shrink-0"></span>
                                <h3 class="font-bold text-slate-900 dark:text-slate-100 text-sm tracking-tight truncate group-hover:text-orange-600 dark:group-hover:text-orange-400 transition-colors">
                                    {{ $dept['name'] }}
                                </h3>
                            </div>
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-orange-500/10 text-orange-600 dark:text-orange-400 border border-orange-500/20 shrink-0">
                                {{ $dept['count'] }}
                            </span>
                        </div>

                        <!-- Top Designations Mini-bars -->
                        <div class="space-y-2.5 pr-0.5">
                            @if($activeDesignations->isNotEmpty())
                                @foreach($activeDesignations->take(4) as $desig)
                                    @php
                                        $percent = $dept['count'] > 0 ? ($desig['count'] / $dept['count']) * 100 : 0;
                                    @endphp
                                    <div class="space-y-1">
                                        <div class="flex items-center justify-between text-[11px]">
                                            <span class="text-slate-600 dark:text-slate-400 truncate max-w-[150px] font-medium">{{ $desig['name'] }}</span>
                                            <span class="font-bold text-slate-800 dark:text-slate-200 text-[10.5px]">{{ $desig['count'] }}</span>
                                        </div>
                                        <div class="w-full h-1.5 rounded-full bg-slate-200/60 dark:bg-slate-800/60 overflow-hidden">
                                            <div class="h-full rounded-full bg-gradient-to-r from-orange-500 to-red-500 transition-all duration-500" style="width: {{ $percent }}%"></div>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="py-6 text-center text-xs text-slate-400 dark:text-slate-500 italic">
                                    No active designations
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Bottom Explore Action Link -->
                    <div class="pt-3 border-t border-slate-100 dark:border-white/[0.04] mt-4 flex items-center justify-between text-[11px] font-semibold text-slate-400 group-hover:text-orange-600 dark:group-hover:text-orange-400 transition-colors">
                        <span>View Members</span>
                        <x-filament::icon icon="heroicon-m-chevron-right" class="h-3.5 w-3.5 group-hover:translate-x-1 transition-transform" />
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Teleported Detail Modal -->
        <template x-teleport="body">
            <div x-show="isOpen"
                 x-transition:enter="transition ease-out duration-250"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="closeModal()"
                 class="fixed inset-0 bg-slate-950/70 backdrop-blur-md z-[9999] flex items-center justify-center p-3 sm:p-6"
                 style="display: none;">

                <div @click.stop
                     x-show="isOpen"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 scale-95 translate-y-3"
                     x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                     x-transition:leave-end="opacity-0 scale-95 translate-y-3"
                     class="relative w-full max-w-5xl rounded-3xl bg-white dark:bg-[#121826] border border-slate-200/80 dark:border-white/[0.08] text-slate-900 dark:text-slate-100 shadow-2xl overflow-hidden flex flex-col max-h-[88vh]">

                    <!-- Modal Header -->
                    <div class="p-6 sm:p-7 border-b border-slate-100 dark:border-white/[0.06] flex items-center justify-between bg-slate-50/50 dark:bg-slate-900/30">
                        <div class="flex items-center gap-3">
                            <span class="w-1.5 h-8 rounded-full bg-gradient-to-b from-orange-500 to-red-600 shrink-0"></span>
                            <div>
                                <h2 x-text="activeName || 'Department'" class="text-xl sm:text-2xl font-bold tracking-tight text-slate-900 dark:text-slate-50"></h2>
                                <p x-text="`${activeCount} Active Personnel`" class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-0.5"></p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 sm:gap-3">
                            <button type="button" @click="printReport()"
                                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-orange-500 to-red-600 hover:from-orange-600 hover:to-red-700 shadow-md shadow-orange-500/20 active:scale-95 transition-all cursor-pointer">
                                <x-filament::icon icon="heroicon-m-printer" class="h-4 w-4" />
                                <span>Print</span>
                            </button>

                            @if(auth()->check() && auth()->user()->hasRole('HR|super_admin'))
                            <button type="button" @click="exportExcel()"
                                    class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 shadow-md shadow-emerald-500/20 active:scale-95 transition-all cursor-pointer">
                                <x-filament::icon icon="heroicon-m-arrow-down-tray" class="h-4 w-4" />
                                <span>Excel</span>
                            </button>
                            @endif

                            <button type="button" @click="closeModal()"
                                    class="p-2 rounded-xl text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all cursor-pointer">
                                <x-filament::icon icon="heroicon-m-x-mark" class="h-5 w-5" />
                            </button>
                        </div>
                    </div>

                    <!-- Modal Body -->
                    <div class="p-6 sm:p-7 overflow-y-auto flex-grow grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <!-- Left Summary Metrics -->
                        <div class="space-y-4 lg:border-r lg:border-slate-100 dark:lg:border-white/[0.06] lg:pr-6">
                            <div class="p-4 rounded-2xl bg-slate-50/70 dark:bg-slate-900/40 border border-slate-100 dark:border-white/[0.04]">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Average Tenure</span>
                                <div class="text-2xl font-black text-slate-900 dark:text-slate-100 mt-1" x-text="avgTenure"></div>
                            </div>

                            <div class="p-4 rounded-2xl bg-slate-50/70 dark:bg-slate-900/40 border border-slate-100 dark:border-white/[0.04] space-y-3">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Gender Ratio</span>
                                <div class="space-y-2">
                                    <div>
                                        <div class="flex justify-between text-xs font-semibold mb-1">
                                            <span class="text-slate-600 dark:text-slate-400">Male</span>
                                            <span class="text-slate-800 dark:text-slate-200 font-bold" x-text="`${gender.male_count} (${gender.male_percent}%)`"></span>
                                        </div>
                                        <div class="w-full h-2 rounded-full bg-slate-200/60 dark:bg-slate-800 overflow-hidden">
                                            <div class="h-full bg-blue-500 rounded-full transition-all duration-500" :style="`width: ${gender.male_percent}%`"></div>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="flex justify-between text-xs font-semibold mb-1">
                                            <span class="text-slate-600 dark:text-slate-400">Female</span>
                                            <span class="text-slate-800 dark:text-slate-200 font-bold" x-text="`${gender.female_count} (${gender.female_percent}%)`"></span>
                                        </div>
                                        <div class="w-full h-2 rounded-full bg-slate-200/60 dark:bg-slate-800 overflow-hidden">
                                            <div class="h-full bg-rose-500 rounded-full transition-all duration-500" :style="`width: ${gender.female_percent}%`"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Employee List Table -->
                        <div class="lg:col-span-2 flex flex-col space-y-3">
                            <div class="flex items-center justify-between gap-3">
                                <span class="text-xs font-bold uppercase tracking-wider text-slate-400">Staff Members</span>
                                <div class="relative w-full max-w-xs">
                                    <x-filament::icon icon="heroicon-m-magnifying-glass" class="absolute left-3 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-slate-400" />
                                    <input type="text" x-model="searchQuery" @input="filterEmployees()"
                                           placeholder="Filter by name, code, designation..."
                                           class="w-full pl-8 pr-3 py-1.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-white/[0.08] focus:border-orange-500 focus:ring-1 focus:ring-orange-500/20 outline-none">
                                </div>
                            </div>

                            <div class="overflow-auto rounded-2xl border border-slate-200/80 dark:border-white/[0.08] max-h-[420px]">
                                <table class="w-full text-left border-collapse text-xs">
                                    <thead>
                                        <tr class="border-b border-slate-100 dark:border-white/[0.06] bg-slate-50/80 dark:bg-slate-900/60 text-[10px] uppercase font-bold text-slate-400 tracking-wider">
                                            <th class="px-4 py-2.5">Code</th>
                                            <th class="px-4 py-2.5">Name</th>
                                            <th class="px-4 py-2.5">Designation</th>
                                            <th class="px-4 py-2.5">Join Date</th>
                                            <th class="px-4 py-2.5">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 dark:divide-white/[0.04]">
                                        <template x-for="emp in filteredEmployees" :key="emp.code">
                                            <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition-colors">
                                                <td class="px-4 py-2.5 font-mono font-semibold text-slate-700 dark:text-slate-300" x-text="emp.code"></td>
                                                <td class="px-4 py-2.5 font-semibold text-slate-900 dark:text-slate-100" x-text="emp.name"></td>
                                                <td class="px-4 py-2.5 text-slate-600 dark:text-slate-400" x-text="emp.designation"></td>
                                                <td class="px-4 py-2.5 text-slate-500" x-text="emp.join_date"></td>
                                                <td class="px-4 py-2.5">
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold"
                                                          :class="emp.onboarded === true || emp.onboarded === 'yes' ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-500/20' : 'bg-rose-500/10 text-rose-600 dark:text-rose-400 border border-rose-500/20'"
                                                          x-text="emp.onboarded === true || emp.onboarded === 'yes' ? 'Onboarded' : 'Pending'">
                                                    </span>
                                                </td>
                                            </tr>
                                        </template>
                                        <tr x-show="filteredEmployees.length === 0">
                                            <td colspan="5" class="py-8 text-center text-slate-400 italic">No staff found matching filter.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <!-- Print Report Scope Selection Modal -->
        <template x-teleport="body">
            <div x-show="isPrintModalOpen"
                 x-cloak
                 class="fixed inset-0 bg-slate-950/70 backdrop-blur-md z-[9999] flex items-center justify-center p-3 sm:p-6"
                 style="display: none;">

                <div @click.stop
                     x-show="isPrintModalOpen"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 scale-95 translate-y-3"
                     x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                     x-transition:leave-end="opacity-0 scale-95 translate-y-3"
                     class="relative w-full max-w-lg rounded-3xl bg-white dark:bg-[#121826] border border-slate-200/80 dark:border-white/[0.08] text-slate-900 dark:text-slate-100 shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">

                    <!-- Modal Header -->
                    <div class="p-6 border-b border-slate-100 dark:border-white/[0.06] flex items-center justify-between bg-slate-50/50 dark:bg-slate-900/30">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-tr from-orange-500 to-red-600 text-white shadow-md shadow-orange-500/20">
                                <x-filament::icon icon="heroicon-m-printer" class="h-5 w-5 text-white" />
                            </div>
                            <div>
                                <h3 class="text-lg font-bold tracking-tight text-slate-900 dark:text-slate-100">
                                    Print Department Report
                                </h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400 font-medium">
                                    Choose whether to print all departments or select specific ones.
                                </p>
                            </div>
                        </div>

                        <button type="button" @click="closePrintModal()"
                                class="p-2 rounded-xl text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all cursor-pointer">
                            <x-filament::icon icon="heroicon-m-x-mark" class="h-5 w-5" />
                        </button>
                    </div>

                    <!-- Modal Body -->
                    <div class="p-6 space-y-5 overflow-y-auto">
                        <!-- Radio Options -->
                        <div class="space-y-3">
                            <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                                Report Scope
                            </label>

                            <!-- Option 1: All Departments -->
                            <label class="relative flex items-start gap-3.5 p-4 rounded-2xl border transition-all cursor-pointer select-none"
                                   :class="printScope === 'all'
                                       ? 'bg-orange-50/60 dark:bg-orange-950/20 border-orange-500 ring-2 ring-orange-500/20 dark:border-orange-500'
                                       : 'bg-slate-50/50 dark:bg-slate-900/40 border-slate-200/80 dark:border-white/[0.06] hover:bg-slate-100/60 dark:hover:bg-slate-800/40'">
                                <input type="radio" name="print_scope" value="all" x-model="printScope"
                                       class="mt-1 h-4 w-4 text-orange-600 border-slate-300 dark:border-slate-600 focus:ring-orange-500 focus:ring-offset-0 cursor-pointer">
                                <div class="flex-1">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm font-bold text-slate-900 dark:text-slate-100">All Departments</span>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-orange-100 dark:bg-orange-900/40 text-orange-700 dark:text-orange-300">
                                            {{ count($this->stats) }} Departments
                                        </span>
                                    </div>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                        Print a full summary report including all active departments and designations.
                                    </p>
                                </div>
                            </label>

                            <!-- Option 2: Select Departments -->
                            <label class="relative flex items-start gap-3.5 p-4 rounded-2xl border transition-all cursor-pointer select-none"
                                   :class="printScope === 'selected'
                                       ? 'bg-orange-50/60 dark:bg-orange-950/20 border-orange-500 ring-2 ring-orange-500/20 dark:border-orange-500'
                                       : 'bg-slate-50/50 dark:bg-slate-900/40 border-slate-200/80 dark:border-white/[0.06] hover:bg-slate-100/60 dark:hover:bg-slate-800/40'">
                                <input type="radio" name="print_scope" value="selected" x-model="printScope"
                                       class="mt-1 h-4 w-4 text-orange-600 border-slate-300 dark:border-slate-600 focus:ring-orange-500 focus:ring-offset-0 cursor-pointer">
                                <div class="flex-1">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm font-bold text-slate-900 dark:text-slate-100">Select Departments</span>
                                        <span x-show="selectedDeptIds.length > 0"
                                              x-text="`${selectedDeptIds.length} Selected`"
                                              class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-100 dark:bg-emerald-900/40 text-emerald-700 dark:text-emerald-300">
                                        </span>
                                    </div>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                        Choose one or more specific departments to include in the printed report.
                                    </p>
                                </div>
                            </label>
                        </div>

                        <!-- Department Selection Dropdown (Only shown when 'selected' is active) -->
                        <div x-show="printScope === 'selected'"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 -translate-y-2"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             class="space-y-2 pt-2 border-t border-slate-100 dark:border-white/[0.06]">
                            <div class="flex items-center justify-between">
                                <label class="text-[11px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500">
                                    Target Departments
                                </label>
                                <div class="flex items-center gap-2">
                                    <button type="button" @click="selectAllDepts()"
                                            class="text-[11px] font-semibold text-orange-600 dark:text-orange-400 hover:underline cursor-pointer">
                                        Select All
                                    </button>
                                    <span class="text-slate-300 dark:text-slate-700">&bull;</span>
                                    <button type="button" @click="clearDeptSelection()"
                                            class="text-[11px] font-semibold text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 hover:underline cursor-pointer">
                                        Clear
                                    </button>
                                </div>
                            </div>

                            <!-- Dropdown Trigger Box -->
                            <div class="relative" @click.outside="isDeptDropdownOpen = false">
                                <button type="button" @click="isDeptDropdownOpen = !isDeptDropdownOpen"
                                        class="w-full flex items-center justify-between gap-2 px-3.5 py-2.5 rounded-2xl bg-slate-50 dark:bg-slate-900 border text-left transition-all cursor-pointer focus:outline-none"
                                        :class="printValidationWarning
                                            ? 'border-red-500 ring-2 ring-red-500/20'
                                            : (isDeptDropdownOpen ? 'border-orange-500 ring-2 ring-orange-500/20' : 'border-slate-200 dark:border-white/[0.08] hover:border-slate-300 dark:hover:border-white/[0.15]')">
                                    <div class="flex-1 flex flex-wrap items-center gap-1.5 overflow-hidden">
                                        <template x-if="selectedDeptIds.length === 0">
                                            <span class="text-xs text-slate-400 dark:text-slate-500">
                                                Click to select departments...
                                            </span>
                                        </template>
                                        <template x-if="selectedDeptIds.length > 0">
                                            <div class="flex flex-wrap items-center gap-1">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-bold bg-orange-500/10 text-orange-600 dark:text-orange-400 border border-orange-500/20"
                                                      x-text="`${selectedDeptIds.length} Department${selectedDeptIds.length > 1 ? 's' : ''} Selected`">
                                                </span>
                                            </div>
                                        </template>
                                    </div>
                                    <x-filament::icon icon="heroicon-m-chevron-down"
                                                      class="h-4 w-4 text-slate-400 transition-transform duration-200"
                                                      ::class="isDeptDropdownOpen ? 'rotate-180 text-orange-500' : ''" />
                                </button>

                                <!-- Dropdown List Panel -->
                                <div x-show="isDeptDropdownOpen"
                                     x-transition:enter="transition ease-out duration-150"
                                     x-transition:enter-start="opacity-0 scale-95"
                                     x-transition:enter-end="opacity-100 scale-100"
                                     x-transition:leave="transition ease-in duration-100"
                                     x-transition:leave-start="opacity-100 scale-100"
                                     x-transition:leave-end="opacity-0 scale-95"
                                     class="absolute z-50 left-0 right-0 mt-1.5 rounded-2xl bg-white dark:bg-[#121826] border border-slate-200 dark:border-white/[0.1] shadow-2xl p-2.5 space-y-2">

                                    <!-- Search input -->
                                    <div class="relative">
                                        <x-filament::icon icon="heroicon-m-magnifying-glass" class="absolute left-2.5 top-1/2 -translate-y-1/2 h-3.5 w-3.5 text-slate-400" />
                                        <input type="text" x-model="deptSearch"
                                               placeholder="Filter departments..."
                                               class="w-full pl-8 pr-3 py-1.5 text-xs rounded-xl bg-slate-50 dark:bg-slate-900/80 border border-slate-200 dark:border-white/[0.08] focus:border-orange-500 focus:ring-1 focus:ring-orange-500/20 outline-none text-slate-900 dark:text-slate-100">
                                    </div>

                                    <!-- Department Items List -->
                                    <div class="max-h-56 overflow-y-auto space-y-1 pr-1">
                                        <template x-for="dept in filteredAvailableDepartments" :key="dept.id">
                                            <div @click="toggleDept(dept.id)"
                                                 class="flex items-center justify-between p-2 rounded-xl text-xs cursor-pointer select-none transition-colors"
                                                 :class="selectedDeptIds.includes(dept.id)
                                                     ? 'bg-orange-50/80 dark:bg-orange-950/30 text-orange-900 dark:text-orange-200 font-semibold'
                                                     : 'hover:bg-slate-50 dark:hover:bg-slate-800/50 text-slate-700 dark:text-slate-300'">
                                                <div class="flex items-center gap-2.5">
                                                    <input type="checkbox"
                                                           :checked="selectedDeptIds.includes(dept.id)"
                                                           class="h-3.5 w-3.5 rounded text-orange-600 border-slate-300 dark:border-slate-600 focus:ring-orange-500 pointer-events-none">
                                                    <span x-text="dept.name"></span>
                                                </div>
                                                <span class="text-[10px] text-slate-400 font-mono"
                                                      x-text="`${dept.count} staff`">
                                                </span>
                                            </div>
                                        </template>

                                        <div x-show="filteredAvailableDepartments.length === 0"
                                             class="py-4 text-center text-xs text-slate-400 italic">
                                            No departments match search.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Validation Warning -->
                            <div x-show="printValidationWarning"
                                 x-transition
                                 class="flex items-center gap-1.5 text-xs font-semibold text-red-500 pt-1">
                                <x-filament::icon icon="heroicon-m-exclamation-circle" class="h-4 w-4 shrink-0 text-red-500" />
                                <span>Please select at least one department to print.</span>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Footer -->
                    <div class="p-5 border-t border-slate-100 dark:border-white/[0.06] flex items-center justify-end gap-3 bg-slate-50/50 dark:bg-slate-900/30">
                        <button type="button" @click="closePrintModal()"
                                class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer">
                            Cancel
                        </button>
                        <button type="button" @click="submitPrintReport()"
                                class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-orange-500 to-red-600 hover:from-orange-600 hover:to-red-700 shadow-md shadow-orange-500/20 hover:shadow-lg hover:shadow-orange-500/30 active:scale-95 transition-all cursor-pointer">
                            <x-filament::icon icon="heroicon-m-printer" class="h-4 w-4 text-white" />
                            <span>Print Report</span>
                        </button>
                    </div>
                </div>
            </div>
        </template>
    </div>
</x-filament-widgets::widget>
