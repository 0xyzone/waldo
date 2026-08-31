<x-filament-widgets::widget>
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
            }
         }"
         @keydown.escape.window="closeModal()"
         class="relative overflow-hidden rounded-2xl bg-white dark:bg-gray-900 shadow-[0_8px_30px_rgb(0,0,0,0.04)] dark:shadow-[0_15px_40px_rgba(0,0,0,0.4)] border border-gray-200/50 dark:border-white/10 transition-all duration-500 hover:border-amber-500/30 dark:hover:border-amber-500/20 hover:shadow-[0_20px_50px_rgba(249,115,22,0.08)] group w-full">
        
        <!-- Glowing backdrop elements -->
        <div class="absolute -right-24 -top-24 h-64 w-64 rounded-full bg-gradient-to-br from-amber-500/10 to-orange-500/10 opacity-30 dark:opacity-20 blur-3xl pointer-events-none transition-all duration-700 group-hover:scale-110"></div>
        <div class="absolute -left-20 -bottom-20 h-64 w-64 rounded-full bg-linear-to-tr from-indigo-500/5 to-purple-500/5 opacity-20 dark:opacity-10 blur-3xl pointer-events-none"></div>

        <div class="p-6 sm:p-8 space-y-6 relative z-10">
            <!-- Header Section -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6 pb-6 border-b border-gray-100 dark:border-white/5">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-linear-to-tr from-amber-500 to-orange-600 text-white shadow-[0_8px_20px_rgba(249,115,22,0.2)] transition-transform duration-500 group-hover:scale-105">
                        <x-filament::icon icon="heroicon-s-briefcase" class="h-6 w-6 text-white" />
                    </div>
                    <div>
                        <h2 class="text-lg font-bold tracking-tight text-gray-900 dark:text-white font-['Outfit',sans-serif]">
                            Department Stats
                        </h2>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 font-normal leading-relaxed">
                            Overview of active employee counts by department and designation. Click any department for detailed insights.
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ url('/reports/departments/view') }}" target="_blank"
                       class="inline-flex items-center justify-center gap-2 font-bold tracking-wide text-amber-500 hover:text-white border border-amber-500/30 hover:border-amber-500 bg-transparent hover:bg-linear-to-r hover:from-orange-500 hover:to-amber-500 transition-all duration-300 transform hover:-translate-y-0.5 active:translate-y-0 focus:outline-none focus:ring-2 focus:ring-amber-500/50 rounded-xl px-4 py-2 text-xs whitespace-nowrap cursor-pointer">
                        <x-filament::icon icon="heroicon-m-arrow-top-right-on-square" class="h-3.5 w-3.5" />
                        Interactive View
                    </a>
                    <a href="{{ url('/reports/departments') }}" target="_blank"
                       class="inline-flex items-center justify-center gap-2 font-bold tracking-wide text-white bg-linear-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 shadow-[0_4px_15px_rgba(249,115,22,0.2)] hover:shadow-[0_6px_20px_rgba(249,115,22,0.3)] transition-all duration-300 transform hover:-translate-y-0.5 active:translate-y-0 focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:ring-offset-2 dark:focus:ring-offset-gray-900 rounded-xl px-4 py-2 text-xs whitespace-nowrap cursor-pointer">
                        <x-filament::icon icon="heroicon-m-printer" class="h-3.5 w-3.5" />
                        Print Report
                    </a>
                </div>
            </div>

            <!-- Stats Display Section: Grid-based full width, no scrolling -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                @foreach($this->stats as $dept)
                    @php
                        $deptJson = json_encode([
                            'id' => $dept['id'],
                            'name' => $dept['name'],
                            'count' => $dept['count'],
                            'employees' => $dept['employees'],
                            'stats' => $dept['stats']
                        ]);
                    @endphp
                    <div @click="openModal({{ $deptJson }})"
                         class="widget-dept-card p-5 rounded-2xl border border-gray-200/50 dark:border-white/10 bg-gray-50/20 dark:bg-gray-800/10 hover:bg-gray-50/50 dark:hover:bg-gray-800/20 transition-all duration-300 hover:shadow-lg hover:border-amber-500/25 flex flex-col justify-between h-[230px] cursor-pointer group/card hover:scale-[1.01]">

                        <div class="flex flex-col h-full justify-between">
                            <!-- Department Header -->
                            <div class="flex items-center justify-between gap-3 pb-2.5">
                                <h3 class="text-xs font-black text-gray-900 dark:text-white flex items-center gap-2 group-hover/card:text-amber-500 transition-colors">
                                    <span class="w-1.5 h-4 rounded-full bg-gradient-to-b from-orange-500 to-amber-500"></span>
                                    <span class="truncate max-w-40 font-bold uppercase tracking-wider text-[11px]">{{ $dept['name'] }}</span>
                                </h3>
                                <div class="px-2.5 py-1 rounded-lg text-xs font-black bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400 border border-amber-200/30 dark:border-amber-500/20 shadow-xs shrink-0">
                                    {{ $dept['count'] }}
                                </div>
                            </div>

                            <!-- Designations Breakdown -->
                            @php
                                $activeDesignations = collect($dept['designations'])->filter(fn($d) => $d['count'] > 0);
                            @endphp

                            <div class="flex-1 mt-2 overflow-y-auto pr-1 space-y-3 scrollbar-thin">
                                @if($activeDesignations->isNotEmpty())
                                    @foreach($activeDesignations as $desig)
                                        @php
                                            $percentage = $dept['count'] > 0 ? ($desig['count'] / $dept['count']) * 100 : 0;
                                        @endphp
                                        <div class="space-y-1">
                                            <div class="flex items-center justify-between text-[10px]">
                                                <span class="font-medium text-gray-500 dark:text-gray-400 truncate max-w-[160px]">{{ $desig['name'] }}</span>
                                                <span class="font-bold text-gray-800 dark:text-gray-200">{{ $desig['count'] }}</span>
                                            </div>
                                            <div class="h-1 w-full bg-gray-100 dark:bg-gray-950/60 rounded-full overflow-hidden">
                                                <div class="h-full bg-gradient-to-r from-orange-500 to-amber-500 rounded-full" style="width: {{ $percentage }}%"></div>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="h-full flex items-center justify-center text-[10px] text-gray-400 dark:text-gray-500 italic">
                                        No active designations
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Interactive Detail Modal (Teleported to body for correct z-index layering) -->
        <template x-teleport="body">
            <div x-show="isOpen"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 @click="closeModal()"
                 class="fixed inset-0 bg-slate-950/80 backdrop-blur-md z-[9999] flex items-center justify-center p-4"
                 style="display: none;">
                
                <!-- Modal Card Content -->
                <div @click.stop
                     x-show="isOpen"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 transform scale-95 translate-y-4"
                     x-transition:enter-end="opacity-100 transform scale-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100 transform scale-100 translate-y-0"
                     x-transition:leave-end="opacity-0 transform scale-95 translate-y-4"
                     class="relative w-full max-w-6xl rounded-3xl border border-white/10 bg-slate-900 text-slate-100 shadow-[0_25px_60px_rgba(0,0,0,0.6)] overflow-hidden flex flex-col max-h-[85vh]">
                    
                    <!-- Ambient glows inside modal -->
                    <div class="absolute -right-40 -top-40 h-80 w-80 rounded-full bg-amber-500/10 blur-3xl pointer-events-none"></div>
                    <div class="absolute -left-40 -bottom-40 h-80 w-80 rounded-full bg-indigo-500/5 blur-3xl pointer-events-none"></div>

                    <!-- Modal Header -->
                    <div class="p-6 sm:p-8 border-b border-white/5 flex items-center justify-between relative z-10">
                        <div class="flex items-center gap-3">
                            <span class="w-1.5 h-8 rounded-full bg-gradient-to-b from-amber-500 to-orange-500 shrink-0"></span>
                            <div>
                                <h2 x-text="activeName || 'Department Details'" class="text-xl sm:text-2xl font-bold tracking-tight text-white font-['Outfit',sans-serif]">
                                </h2>
                                <p x-text="`${activeCount} Active Staff Members`" class="text-xs text-slate-400 font-medium mt-1">
                                </p>
                            </div>
                        </div>
                        
                        <div class="flex items-center gap-2 sm:gap-3">
                            <!-- Print PDF/Report Button -->
                            <button type="button" @click="printReport()" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 text-white text-xs font-bold tracking-wide shadow-lg shadow-orange-950/40 border border-orange-400/20 transition-all transform hover:scale-105 active:scale-95 cursor-pointer">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.821V21m0 0v-7.18m0 7.18-3.08-1.54A2.25 2.25 0 0 1 2 17.472v-9.623c0-.98.622-1.854 1.547-2.18l3.18-1.127a2.25 2.25 0 0 1 1.493 0l3.18 1.127a2.25 2.25 0 0 1 1.547 2.18v9.623a2.25 2.25 0 0 1-1.293 2.018L6.72 21Zm6.36-12.012v12.012m0 0 3.078-1.54A2.25 2.25 0 0 0 17.5 17.472v-9.623c0-.98-.622-1.854-1.547-2.18l-3.18-1.127a2.25 2.25 0 0 0-1.493 0L8.1 3.418a2.25 2.25 0 0 0-1.547 2.18v9.623a2.25 2.25 0 0 0 1.293 2.018L10.9 21m2.18-19.191v12.012" />
                                </svg>
                                <span>Print Report</span>
                            </button>

                            <!-- Export to Excel Button -->
                            @if(auth()->check() && auth()->user()->hasRole('HR|super_admin'))
                            <button type="button" @click="exportExcel()" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white text-xs font-bold tracking-wide shadow-lg shadow-emerald-950/40 border border-emerald-400/20 transition-all transform hover:scale-105 active:scale-95 cursor-pointer">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                </svg>
                                <span>Download Excel</span>
                            </button>
                            @endif

                            <!-- Close Button -->
                            <button type="button" @click="closeModal()" class="flex h-10 w-10 items-center justify-center rounded-full bg-white/5 hover:bg-white/10 border border-white/10 text-slate-300 hover:text-white transition-all transform hover:scale-105 active:scale-95 cursor-pointer">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Modal Body (Two Column Split) -->
                    <div class="flex-grow overflow-y-auto p-6 sm:p-8 grid grid-cols-1 lg:grid-cols-3 gap-6 relative z-10">
                        
                        <!-- Left Column: Department Metrics -->
                        <div class="space-y-6 lg:border-r lg:border-white/5 lg:pr-6">
                            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 font-['Outfit',sans-serif]">Department Metrics</h3>
                            
                            <!-- Metric Card 1: Service Tenure -->
                            <div class="p-4 rounded-2xl bg-white/[0.02] border border-white/5 shadow-inner">
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider font-['Outfit',sans-serif]">Average Service Tenure</p>
                                <div class="mt-2 flex items-baseline gap-2">
                                    <span x-text="avgTenure" class="text-2xl font-extrabold text-white font-['Outfit',sans-serif]">--</span>
                                    <span class="text-xs text-slate-400">average tenure</span>
                                </div>
                            </div>

                            <!-- Metric Card 2: Gender distribution -->
                            <div class="p-4 rounded-2xl bg-white/[0.02] border border-white/5 shadow-inner space-y-4">
                                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider font-['Outfit',sans-serif]">Gender Distribution</p>
                                <div class="space-y-3">
                                    <!-- Male bar -->
                                    <div class="space-y-1">
                                        <div class="flex justify-between text-xs font-semibold">
                                            <span class="text-indigo-400">Male</span>
                                            <span class="text-slate-200" x-text="`${gender.male_count} (${gender.male_percent}%)`">0 (0%)</span>
                                        </div>
                                        <div class="w-full h-2 bg-slate-950 rounded-full overflow-hidden border border-white/5">
                                            <div class="h-full bg-indigo-500 rounded-full transition-all duration-700" :style="`width: ${gender.male_percent}%`"></div>
                                        </div>
                                    </div>
                                    <!-- Female bar -->
                                    <div class="space-y-1">
                                        <div class="flex justify-between text-xs font-semibold">
                                            <span class="text-pink-400">Female</span>
                                            <span class="text-slate-200" x-text="`${gender.female_count} (${gender.female_percent}%)`">0 (0%)</span>
                                        </div>
                                        <div class="w-full h-2 bg-slate-950 rounded-full overflow-hidden border border-white/5">
                                            <div class="h-full bg-pink-500 rounded-full transition-all duration-700" :style="`width: ${gender.female_percent}%`"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column: Employee Table & List Filter -->
                        <div class="lg:col-span-2 flex flex-col space-y-4 max-h-[500px]">
                            <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
                                <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 font-['Outfit',sans-serif]">Active Employees</h3>
                                
                                <!-- Mini search within modal -->
                                <div class="relative max-w-xs w-full">
                                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-2.5">
                                        <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.637 10.637Z" />
                                        </svg>
                                    </div>
                                    <input type="text"
                                           x-model="searchQuery"
                                           @input="filterEmployees()"
                                           placeholder="Filter employees..." 
                                           class="w-full rounded-lg border border-white/10 bg-slate-950/60 pl-8 pr-3 py-1.5 text-xs text-white placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-amber-500/30 transition-all">
                                </div>
                            </div>

                            <!-- Scrollable Table Wrapper -->
                            <div class="flex-grow overflow-auto border border-white/5 rounded-2xl bg-slate-950/20">
                                <table class="w-full text-left border-collapse">
                                    <thead>
                                        <tr class="border-b border-white/10 bg-slate-950/50 text-[10px] font-bold uppercase tracking-wider text-slate-400 font-['Outfit',sans-serif]">
                                            <th class="px-4 py-3">Code</th>
                                            <th class="px-4 py-3">Name</th>
                                            <th class="px-4 py-3">Gender</th>
                                            <th class="px-4 py-3">Join Date</th>
                                            <th class="px-4 py-3">Designation</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-white/[0.02]">
                                        <template x-for="emp in filteredEmployees" :key="emp.code">
                                            <tr class="border-b border-white/5 hover:bg-white/[0.02] transition-colors">
                                                <td class="px-4 py-3 font-semibold text-slate-200 text-xs" x-text="emp.code"></td>
                                                <td class="px-4 py-3 text-white text-xs font-semibold" x-text="emp.name"></td>
                                                <td class="px-4 py-3 text-xs">
                                                    <span :class="emp.gender === 'Male' ? 'text-indigo-400' : (emp.gender === 'Female' ? 'text-pink-400' : 'text-slate-400')" x-text="emp.gender"></span>
                                                </td>
                                                <td class="px-4 py-3 text-slate-400 text-xs" x-text="emp.join_date"></td>
                                                <td class="px-4 py-3 text-slate-300 text-xs" x-text="emp.designation"></td>
                                            </tr>
                                        </template>
                                        <tr x-show="filteredEmployees.length === 0">
                                            <td colspan="5" class="p-6 text-center text-slate-500 italic text-xs">No active staff matched current filter</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
</x-filament-widgets::widget>
