<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Department Staff Insights - Waldo Dynasty</title>
    
    <!-- Google Fonts: Outfit and Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Vite / Compiled assets (includes tailwind classes) -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        .heading-font {
            font-family: 'Outfit', sans-serif;
        }
        /* Custom scrollbar for premium look */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.03);
            border-radius: 99px;
        }
        ::-webkit-scrollbar-thumb {
            background: rgba(249, 115, 22, 0.2);
            border-radius: 99px;
            border: 2px solid transparent;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: rgba(249, 115, 22, 0.4);
        }
    </style>
</head>
<body class="h-full bg-slate-950 text-slate-100 antialiased overflow-x-hidden relative">

    <!-- Ambient Glowing Backdrops -->
    <div class="absolute top-0 right-0 -mr-40 -mt-40 w-[600px] h-[600px] rounded-full bg-gradient-to-br from-amber-500/10 to-orange-600/10 blur-3xl pointer-events-none z-0"></div>
    <div class="absolute bottom-0 left-0 -ml-40 -mb-40 w-[600px] h-[600px] rounded-full bg-gradient-to-tr from-indigo-500/5 to-purple-500/5 blur-3xl pointer-events-none z-0"></div>
    <div class="absolute top-[30%] left-[20%] w-[500px] h-[500px] rounded-full bg-amber-500/[0.02] blur-3xl pointer-events-none z-0"></div>

    <div class="min-h-full flex flex-col justify-between relative z-10 px-4 py-8 sm:px-6 lg:px-8 max-w-7xl mx-auto space-y-8">
        
        <!-- Header Row -->
        <header class="flex flex-col md:flex-row md:items-center justify-between gap-6 pb-6 border-b border-white/10">
            <div class="flex items-center gap-4">
                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-tr from-amber-500 to-orange-600 text-white shadow-[0_8px_25px_rgba(249,115,22,0.3)] animate-pulse">
                    <svg class="h-7 w-7 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold uppercase tracking-wider text-amber-500 heading-font">Waldo Dynasty</span>
                        <span class="h-1.5 w-1.5 rounded-full bg-white/20"></span>
                        <span class="text-xs text-slate-400">Staff Distribution</span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white heading-font mt-1">
                        Department & Designation Insights
                    </h1>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ url('/reports/departments') }}" target="_blank"
                   class="inline-flex items-center justify-center gap-2 font-bold tracking-wide text-white bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 shadow-[0_4px_15px_rgba(249,115,22,0.25)] hover:shadow-[0_6px_20px_rgba(249,115,22,0.4)] transition-all duration-300 transform hover:-translate-y-0.5 active:translate-y-0 focus:outline-none focus:ring-2 focus:ring-amber-500/50 rounded-xl px-5 py-3 text-sm whitespace-nowrap">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.821V21m0 0v-7.18m0 7.18-3.08-1.54A2.25 2.25 0 0 1 2 17.472v-9.623c0-.98.622-1.854 1.547-2.18l3.18-1.127a2.25 2.25 0 0 1 1.493 0l3.18 1.127a2.25 2.25 0 0 1 1.547 2.18v9.623a2.25 2.25 0 0 1-1.293 2.018L6.72 21Zm6.36-12.012v12.012m0 0 3.078-1.54A2.25 2.25 0 0 0 17.5 17.472v-9.623c0-.98-.622-1.854-1.547-2.18l-3.18-1.127a2.25 2.25 0 0 0-1.493 0L8.1 3.418a2.25 2.25 0 0 0-1.547 2.18v9.623a2.25 2.25 0 0 0 1.293 2.018L10.9 21m2.18-19.191v12.012" />
                    </svg>
                    Print PDF Report
                </a>
            </div>
        </header>

        <!-- KPI Metrics Ribbon -->
        <section class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
            
            <!-- Metric 1 -->
            <div class="relative overflow-hidden rounded-2xl bg-white/[0.03] border border-white/5 p-5 shadow-[0_8px_30px_rgba(0,0,0,0.2)] hover:border-white/10 transition-all duration-300">
                <div class="absolute -right-6 -bottom-6 h-20 w-20 rounded-full bg-amber-500/5 blur-xl"></div>
                <p class="text-xs font-semibold text-slate-400 tracking-wider uppercase heading-font">Total Active Staff</p>
                <div class="mt-2 flex items-baseline gap-2">
                    <span class="text-3xl font-extrabold tracking-tight text-white heading-font">{{ $totalEmployees }}</span>
                    <span class="text-xs text-emerald-400 font-medium">Employees</span>
                </div>
            </div>

            <!-- Metric 2 -->
            <div class="relative overflow-hidden rounded-2xl bg-white/[0.03] border border-white/5 p-5 shadow-[0_8px_30px_rgba(0,0,0,0.2)] hover:border-white/10 transition-all duration-300">
                <div class="absolute -right-6 -bottom-6 h-20 w-20 rounded-full bg-orange-500/5 blur-xl"></div>
                <p class="text-xs font-semibold text-slate-400 tracking-wider uppercase heading-font">Departments</p>
                <div class="mt-2 flex items-baseline gap-2">
                    <span class="text-3xl font-extrabold tracking-tight text-white heading-font">{{ $totalDepartments }}</span>
                    <span class="text-xs text-orange-400 font-medium">Active units</span>
                </div>
            </div>

            <!-- Metric 3 -->
            <div class="relative overflow-hidden rounded-2xl bg-white/[0.03] border border-white/5 p-5 shadow-[0_8px_30px_rgba(0,0,0,0.2)] hover:border-white/10 transition-all duration-300">
                <div class="absolute -right-6 -bottom-6 h-20 w-20 rounded-full bg-indigo-500/5 blur-xl"></div>
                <p class="text-xs font-semibold text-slate-400 tracking-wider uppercase heading-font">Staff Avg / Dept</p>
                <div class="mt-2 flex items-baseline gap-2">
                    <span class="text-3xl font-extrabold tracking-tight text-white heading-font">{{ $avgEmployeesPerDept }}</span>
                    <span class="text-xs text-indigo-400 font-medium">per department</span>
                </div>
            </div>

            <!-- Metric 4 -->
            <div class="relative overflow-hidden rounded-2xl bg-white/[0.03] border border-white/5 p-5 shadow-[0_8px_30px_rgba(0,0,0,0.2)] hover:border-white/10 transition-all duration-300">
                <div class="absolute -right-6 -bottom-6 h-20 w-20 rounded-full bg-purple-500/5 blur-xl"></div>
                <p class="text-xs font-semibold text-slate-400 tracking-wider uppercase heading-font">Top Staff Unit</p>
                <div class="mt-2 flex flex-col">
                    <span class="text-lg font-bold text-white heading-font truncate">{{ $topDepartmentName }}</span>
                    <span class="text-xs text-purple-400 font-medium mt-0.5">{{ $topDepartmentCount }} active staff</span>
                </div>
            </div>
        </section>

        <!-- Search and Filter Section -->
        <section class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4 bg-white/[0.02] border border-white/5 p-4 rounded-2xl backdrop-blur-md">
            <!-- Search bar with custom inner icon -->
            <div class="relative flex-grow max-w-lg">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                    <svg class="h-5 w-5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.637 10.637Z" />
                    </svg>
                </div>
                <input type="text" id="searchInput" placeholder="Search departments or designations..." 
                       class="w-full rounded-xl border border-white/10 bg-slate-900/50 pl-10 pr-4 py-3 text-sm text-white placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 transition-all shadow-inner">
            </div>

            <!-- Tab Filters -->
            <div class="flex items-center gap-1.5 p-1 rounded-xl bg-slate-900 border border-white/5 overflow-x-auto self-start sm:self-auto">
                <button onclick="filterType('all')" id="tab-all" class="px-4 py-2 rounded-lg text-xs font-semibold bg-white/10 text-white transition-all">All</button>
                <button onclick="filterType('large')" id="tab-large" class="px-4 py-2 rounded-lg text-xs font-semibold text-slate-400 hover:text-white transition-all">Large (>15)</button>
                <button onclick="filterType('medium')" id="tab-medium" class="px-4 py-2 rounded-lg text-xs font-semibold text-slate-400 hover:text-white transition-all">Medium (5-15)</button>
                <button onclick="filterType('small')" id="tab-small" class="px-4 py-2 rounded-lg text-xs font-semibold text-slate-400 hover:text-white transition-all">Small (<5)</button>
            </div>
        </section>

        <!-- Department Cards Grid -->
        <main id="departmentsGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($departments as $dept)
                @php
                    // Percentage distribution
                    $percentage = $totalEmployees > 0 ? round(($dept['count'] / $totalEmployees) * 100, 1) : 0;
                    
                    // Classification for tabs
                    $class = 'small';
                    if($dept['count'] > 15) {
                        $class = 'large';
                    } elseif ($dept['count'] >= 5) {
                        $class = 'medium';
                    }
                @endphp
                <article onclick="openDeptModal(this)" data-name="{{ strtolower($dept['name']) }}" data-class="{{ $class }}" data-designations="{{ strtolower(collect($dept['designations'])->pluck('name')->implode(' ')) }}"
                         class="dept-card cursor-pointer group relative overflow-hidden rounded-2xl bg-white/[0.02] border border-white/5 hover:border-amber-500/30 p-6 shadow-[0_8px_30px_rgba(0,0,0,0.15)] hover:shadow-[0_15px_40px_rgba(249,115,22,0.05)] transition-all duration-500 hover:scale-[1.02] flex flex-col justify-between space-y-6">
                    
                    <!-- Embedded department data payload -->
                    <script type="application/json" class="dept-data-payload">
                        {!! json_encode([
                            'name' => $dept['name'],
                            'count' => $dept['count'],
                            'employees' => $dept['employees'],
                            'stats' => $dept['stats']
                        ]) !!}
                    </script>

                    <!-- Decorative corner glow -->
                    <div class="absolute -right-16 -top-16 h-36 w-36 rounded-full bg-amber-500/[0.01] group-hover:bg-amber-500/[0.04] blur-2xl pointer-events-none transition-all duration-500"></div>

                    <div class="space-y-4">
                        <!-- Header of card -->
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex items-center gap-3">
                                <span class="w-1.5 h-7 rounded-full bg-gradient-to-b from-amber-500 to-orange-500 shrink-0"></span>
                                <h2 class="text-lg font-bold tracking-tight text-white heading-font group-hover:text-amber-400 transition-colors">
                                    {{ $dept['name'] }}
                                </h2>
                            </div>
                            <div class="flex flex-col items-end">
                                <span class="text-2xl font-extrabold text-white heading-font">{{ $dept['count'] }}</span>
                                <span class="text-[9px] uppercase tracking-wider font-bold text-slate-400">Staff</span>
                            </div>
                        </div>

                        <!-- Progress Bar (Staff Distribution) -->
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between text-xs">
                                <span class="text-slate-400">Staff Share</span>
                                <span class="font-semibold text-slate-200">{{ $percentage }}%</span>
                            </div>
                            <div class="w-full h-1.5 bg-slate-900 rounded-full overflow-hidden border border-white/5">
                                <div class="h-full bg-gradient-to-r from-amber-500 to-orange-500 rounded-full group-hover:scale-x-105 transition-transform origin-left duration-700" style="width: {{ $percentage }}%"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Designations Segment -->
                    <div class="space-y-3 pt-4 border-t border-white/5">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 heading-font">Designations Details</p>
                        
                        @php
                            $activeDesignations = collect($dept['designations'])->filter(fn($d) => $d['count'] > 0);
                        @endphp

                        @if($activeDesignations->isNotEmpty())
                            <div class="flex flex-wrap gap-2">
                                @foreach($activeDesignations as $desig)
                                    <div data-desig-name="{{ strtolower($desig['name']) }}"
                                         class="desig-tag inline-flex items-center gap-1.5 px-2.5 py-1 rounded-xl bg-slate-900/60 border border-white/5 text-xs text-slate-300 font-medium transition-all hover:border-white/10 hover:bg-slate-900">
                                        <span class="w-1 h-1 rounded-full bg-orange-400"></span>
                                        <span class="name-text">{{ $desig['name'] }}</span>
                                        <span class="px-1.5 py-0.2 rounded bg-white/5 text-slate-200 font-bold text-[10px]">{{ $desig['count'] }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-xs text-slate-400 italic">No active designations in this unit</p>
                        @endif
                    </div>
                </article>
            @endforeach
        </main>

        <!-- Footer -->
        <footer class="pt-8 border-t border-white/10 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-slate-500">
            <p>&copy; {{ date('Y') }} Waldo Dynasty. All rights reserved.</p>
            <p>Active Staff Distribution Engine v2.0</p>
        </footer>
    </div>

    <!-- Interactive Detail Modal -->
    <div id="deptModalOverlay" onclick="closeDeptModal()" class="fixed inset-0 bg-slate-950/70 backdrop-blur-md z-50 flex items-center justify-center p-4 transition-all duration-300 opacity-0 pointer-events-none">
        
        <!-- Modal Card Content -->
        <div id="deptModalContent" onclick="event.stopPropagation()" class="relative w-full max-w-6xl rounded-3xl border border-white/10 bg-slate-900 shadow-[0_25px_60px_rgba(0,0,0,0.6)] overflow-hidden transition-all duration-300 transform scale-95 opacity-0 flex flex-col max-h-[85vh] translate-y-4">
            
            <!-- Ambient glows inside modal -->
            <div class="absolute -right-40 -top-40 h-80 w-80 rounded-full bg-amber-500/10 blur-3xl pointer-events-none"></div>
            <div class="absolute -left-40 -bottom-40 h-80 w-80 rounded-full bg-indigo-500/5 blur-3xl pointer-events-none"></div>

            <!-- Modal Header -->
            <div class="p-6 sm:p-8 border-b border-white/5 flex items-center justify-between relative z-10">
                <div class="flex items-center gap-3">
                    <span class="w-1.5 h-8 rounded-full bg-gradient-to-b from-amber-500 to-orange-500 shrink-0"></span>
                    <div>
                        <h2 id="modalTitle" class="text-xl sm:text-2xl font-bold tracking-tight text-white heading-font">
                            Department Details
                        </h2>
                        <p id="modalDeptCount" class="text-xs text-slate-400 font-medium mt-1">
                            Active Unit Stats
                        </p>
                    </div>
                </div>
                <!-- Close Button -->
                <button onclick="closeDeptModal()" class="flex h-10 w-10 items-center justify-center rounded-full bg-white/5 hover:bg-white/10 border border-white/10 text-slate-300 hover:text-white transition-all transform hover:scale-105 active:scale-95">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Modal Body (Two Column Split) -->
            <div class="flex-grow overflow-y-auto p-6 sm:p-8 grid grid-cols-1 lg:grid-cols-3 gap-6 relative z-10">
                
                <!-- Left Column: Department Metrics -->
                <div class="space-y-6 lg:border-r lg:border-white/5 lg:pr-6">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 heading-font">Department Metrics</h3>
                    
                    <!-- Metric Card 1: Service Tenure -->
                    <div class="p-4 rounded-2xl bg-white/[0.02] border border-white/5 shadow-inner">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider heading-font">Average Service Tenure</p>
                        <div class="mt-2 flex items-baseline gap-2">
                            <span id="statTenure" class="text-2xl font-extrabold text-white heading-font">--</span>
                            <span class="text-xs text-slate-400">average tenure</span>
                        </div>
                    </div>

                    <!-- Metric Card 2: Gender distribution -->
                    <div class="p-4 rounded-2xl bg-white/[0.02] border border-white/5 shadow-inner space-y-4">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider heading-font">Gender Distribution</p>
                        <div class="space-y-3">
                            <!-- Male bar -->
                            <div class="space-y-1">
                                <div class="flex justify-between text-xs font-semibold">
                                    <span class="text-indigo-400">Male</span>
                                    <span id="statGenderMale" class="text-slate-200">0 (0%)</span>
                                </div>
                                <div class="w-full h-2 bg-slate-950 rounded-full overflow-hidden border border-white/5">
                                    <div id="barGenderMale" class="h-full bg-indigo-500 rounded-full transition-all duration-700" style="width: 0%"></div>
                                </div>
                            </div>
                            <!-- Female bar -->
                            <div class="space-y-1">
                                <div class="flex justify-between text-xs font-semibold">
                                    <span class="text-pink-400">Female</span>
                                    <span id="statGenderFemale" class="text-slate-200">0 (0%)</span>
                                </div>
                                <div class="w-full h-2 bg-slate-950 rounded-full overflow-hidden border border-white/5">
                                    <div id="barGenderFemale" class="h-full bg-pink-500 rounded-full transition-all duration-700" style="width: 0%"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Metric Card 3: Tips Distribution -->
                    <div class="p-4 rounded-2xl bg-white/[0.02] border border-white/5 shadow-inner space-y-3">
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider heading-font">Tips Status Summary</p>
                        <div id="statTipsList" class="space-y-2">
                            <!-- Dynamically loaded pills -->
                        </div>
                    </div>
                </div>

                <!-- Right Column: Employee Table & List Filter -->
                <div class="lg:col-span-2 flex flex-col space-y-4 max-h-[500px]">
                    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-slate-400 heading-font">Active Employees</h3>
                        
                        <!-- Mini search within modal -->
                        <div class="relative max-w-xs w-full">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-2.5">
                                <svg class="h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.637 10.637Z" />
                                </svg>
                            </div>
                            <input type="text" id="modalSearchInput" placeholder="Filter employees..." 
                                   class="w-full rounded-lg border border-white/10 bg-slate-950/60 pl-8 pr-3 py-1.5 text-xs text-white placeholder-slate-400 focus:outline-none focus:ring-1 focus:ring-amber-500/30 transition-all">
                        </div>
                    </div>

                    <!-- Scrollable Table Wrapper -->
                    <div class="flex-grow overflow-auto border border-white/5 rounded-2xl bg-slate-950/20">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-white/10 bg-slate-950/50 text-[10px] font-bold uppercase tracking-wider text-slate-400 heading-font">
                                    <th class="px-4 py-3">Code</th>
                                    <th class="px-4 py-3">Name</th>
                                    <th class="px-4 py-3">Gender</th>
                                    <th class="px-4 py-3">Join Date</th>
                                    <th class="px-4 py-3">Designation</th>
                                    <th class="px-4 py-3">Tips</th>
                                </tr>
                            </thead>
                            <tbody id="modalEmployeesBody" class="divide-y divide-white/[0.02]">
                                <!-- Dynamically populated rows -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Client-side Interactive Logic (Search, Filtering & Modal Controllers) -->
    <script>
        const searchInput = document.getElementById('searchInput');
        const cards = document.querySelectorAll('.dept-card');
        let currentFilter = 'all';
        let activeModalEmployees = [];

        // Real-time instant search
        searchInput.addEventListener('input', () => {
            filterElements();
        });

        // Tab Filter click event
        function filterType(type) {
            currentFilter = type;
            
            // Highlight active tab
            ['all', 'large', 'medium', 'small'].forEach(t => {
                const btn = document.getElementById(`tab-${t}`);
                if (t === type) {
                    btn.classList.add('bg-white/10', 'text-white');
                    btn.classList.remove('text-slate-400');
                } else {
                    btn.classList.remove('bg-white/10', 'text-white');
                    btn.classList.add('text-slate-400');
                }
            });

            filterElements();
        }

        // Aggregate Search and Classification filters
        function filterElements() {
            const query = searchInput.value.toLowerCase().trim();

            cards.forEach(card => {
                const name = card.getAttribute('data-name');
                const classifications = card.getAttribute('data-class');
                const designations = card.getAttribute('data-designations');

                // Check Tab matching
                const matchesTab = (currentFilter === 'all' || classifications === currentFilter);

                // Check Search matching (searches both Department name and Designation names)
                const matchesSearch = !query || name.includes(query) || designations.includes(query);

                if (matchesTab && matchesSearch) {
                    card.style.display = 'flex';
                    card.style.opacity = '1';
                    card.style.transform = 'scale(1)';

                    // Highlight specific designations matching search query
                    const tags = card.querySelectorAll('.desig-tag');
                    tags.forEach(tag => {
                        const desigName = tag.getAttribute('data-desig-name');
                        if (query && desigName.includes(query)) {
                            tag.classList.add('border-amber-500/50', 'bg-amber-500/10');
                            tag.classList.remove('border-white/5');
                        } else {
                            tag.classList.remove('border-amber-500/50', 'bg-amber-500/10');
                            tag.classList.add('border-white/5');
                        }
                    });
                } else {
                    card.style.display = 'none';
                    card.style.opacity = '0';
                    card.style.transform = 'scale(0.95)';
                }
            });
        }

        // Modal Controllers
        function openDeptModal(cardElement) {
            const payload = JSON.parse(cardElement.querySelector('.dept-data-payload').textContent);
            activeModalEmployees = payload.employees;

            // Set Title & Count
            document.getElementById('modalTitle').textContent = payload.name;
            document.getElementById('modalDeptCount').textContent = `${payload.count} Active Staff Members`;

            // Set Stats: Tenure
            document.getElementById('statTenure').textContent = `${payload.stats.avg_tenure} Yrs`;

            // Set Stats: Gender
            const gender = payload.stats.gender;
            document.getElementById('statGenderMale').textContent = `${gender.male_count} (${gender.male_percent}%)`;
            document.getElementById('statGenderFemale').textContent = `${gender.female_count} (${gender.female_percent}%)`;
            document.getElementById('barGenderMale').style.width = `${gender.male_percent}%`;
            document.getElementById('barGenderFemale').style.width = `${gender.female_percent}%`;

            // Set Stats: Tips Statuses
            const tipsContainer = document.getElementById('statTipsList');
            tipsContainer.innerHTML = '';
            const tips = payload.stats.tips;
            if (Object.keys(tips).length > 0) {
                Object.entries(tips).forEach(([status, count]) => {
                    const pill = document.createElement('div');
                    pill.className = 'flex items-center justify-between p-2 rounded-lg bg-slate-950/40 border border-white/5 text-xs text-slate-300';
                    pill.innerHTML = `<span>${status}</span><span class="font-bold text-amber-500">${count}</span>`;
                    tipsContainer.appendChild(pill);
                });
            } else {
                tipsContainer.innerHTML = '<p class="text-xs text-slate-500 italic">No tips stats available</p>';
            }

            // Render employee tables
            renderModalEmployees(activeModalEmployees);

            // Open animations
            const overlay = document.getElementById('deptModalOverlay');
            const modalContent = document.getElementById('deptModalContent');

            overlay.classList.remove('opacity-0', 'pointer-events-none');
            overlay.classList.add('opacity-100', 'pointer-events-auto');

            modalContent.classList.remove('scale-95', 'opacity-0', 'translate-y-4');
            modalContent.classList.add('scale-100', 'opacity-100', 'translate-y-0');

            // Reset modal internal search field
            document.getElementById('modalSearchInput').value = '';
        }

        function closeDeptModal() {
            const overlay = document.getElementById('deptModalOverlay');
            const modalContent = document.getElementById('deptModalContent');

            overlay.classList.remove('opacity-100', 'pointer-events-auto');
            overlay.classList.add('opacity-0', 'pointer-events-none');

            modalContent.classList.remove('scale-100', 'opacity-100', 'translate-y-0');
            modalContent.classList.add('scale-95', 'opacity-0', 'translate-y-4');
        }

        function renderModalEmployees(list) {
            const tbody = document.getElementById('modalEmployeesBody');
            tbody.innerHTML = '';

            if (list.length === 0) {
                tbody.innerHTML = '<tr><td colspan="6" class="p-6 text-center text-slate-500 italic text-xs">No active staff matched current filter</td></tr>';
                return;
            }

            list.forEach(emp => {
                const tr = document.createElement('tr');
                tr.className = 'border-b border-white/5 hover:bg-white/[0.02] transition-colors';

                const genderColor = emp.gender === 'Male' ? 'text-indigo-400' : (emp.gender === 'Female' ? 'text-pink-400' : 'text-slate-400');

                tr.innerHTML = `
                    <td class="px-4 py-3 font-semibold text-slate-200 text-xs">${emp.code}</td>
                    <td class="px-4 py-3 text-white text-xs font-semibold">${emp.name}</td>
                    <td class="px-4 py-3 text-xs">
                        <span class="${genderColor}">${emp.gender}</span>
                    </td>
                    <td class="px-4 py-3 text-slate-400 text-xs">${emp.join_date}</td>
                    <td class="px-4 py-3 text-slate-300 text-xs">${emp.designation}</td>
                    <td class="px-4 py-3 text-xs">
                        <span class="inline-flex items-center px-2 py-0.5 rounded bg-amber-500/10 border border-amber-500/20 text-amber-400 font-bold text-[9px] uppercase tracking-wider">
                            ${emp.tips_status}
                        </span>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        }

        // Inner modal search filter
        document.getElementById('modalSearchInput').addEventListener('input', (e) => {
            const q = e.target.value.toLowerCase().trim();
            const filtered = activeModalEmployees.filter(emp =>
                emp.code.toLowerCase().includes(q) ||
                emp.name.toLowerCase().includes(q) ||
                emp.designation.toLowerCase().includes(q) ||
                emp.tips_status.toLowerCase().includes(q)
            );
            renderModalEmployees(filtered);
        });

        // Close on escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeDeptModal();
            }
        });
    </script>
</body>
</html>
