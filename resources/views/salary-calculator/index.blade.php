<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salary & OT Live Calculator — Waldo HQ</title>
    <meta name="description" content="Interactive Salary, Overtime, SSF, Adjustments, and Festival Allowance calculator with real-time live calculations.">
    <x-favicon />

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Styles & Scripts from Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- AlpineJS & Flatpickr -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        /* Modern Glass & Bloom Styling */
        :root {
            --bloom-purple: #8b5cf6;
            --bloom-amber: #f59e0b;
            --bloom-emerald: #10b981;
            --bloom-cyan: #06b6d4;
            --bloom-rose: #f43f5e;
        }

        body {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
        }

        .font-mono-numbers {
            font-family: 'JetBrains Mono', monospace;
            font-feature-settings: "tnum" 1, "zero" 1;
        }

        /* Ambient Glow & Bloom effects */
        .bloom-bg {
            background-radial-gradient: radial-gradient(circle at 50% 0%, rgba(139, 92, 246, 0.15), transparent 50%);
        }

        .glass-panel {
            background: rgba(255, 255, 255, 0.75);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(226, 232, 240, 0.8);
        }

        .dark .glass-panel {
            background: rgba(18, 16, 28, 0.7);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }

        .glass-card-interactive {
            transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .glass-card-interactive:hover {
            transform: translateY(-2px);
            box-shadow: 0 16px 32px -8px rgba(139, 92, 246, 0.15);
            border-color: rgba(168, 85, 247, 0.35);
        }

        .dark .glass-card-interactive:hover {
            box-shadow: 0 16px 40px -8px rgba(139, 92, 246, 0.25);
            border-color: rgba(192, 132, 252, 0.3);
        }

        /* Subtle glowing aura */
        .glow-aura {
            position: absolute;
            border-radius: 9999px;
            filter: blur(80px);
            pointer-events: none;
            opacity: 0.6;
            z-index: 0;
        }

        /* Print formatting */
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background: #ffffff !important;
                color: #000000 !important;
            }
            .glass-panel {
                border: 1px solid #cbd5e1 !important;
                background: #ffffff !important;
                box-shadow: none !important;
            }
            .glow-aura {
                display: none !important;
            }
        }
    </style>

    <script>
        // Init dark mode
        (function() {
            try {
                if (localStorage.getItem('waldo_calc_theme') === 'dark' || (!('waldo_calc_theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
            } catch (_) {}
        })();
    </script>
</head>

<body class="h-full min-h-screen bg-slate-50 dark:bg-[#0c0a14] text-slate-800 dark:text-slate-100 antialiased selection:bg-purple-500 selection:text-white relative overflow-x-hidden transition-colors duration-300"
      x-data="salaryCalculator()">

    <!-- Ambient Glowing Bloom Orbs in Background -->
    <div class="glow-aura w-[520px] h-[520px] bg-purple-500/20 dark:bg-purple-600/25 top-[-100px] left-[-80px]"></div>
    <div class="glow-aura w-[600px] h-[600px] bg-amber-500/15 dark:bg-amber-500/20 top-[150px] right-[-120px]"></div>
    <div class="glow-aura w-[500px] h-[500px] bg-emerald-500/15 dark:bg-emerald-500/15 bottom-[-100px] left-[25%]"></div>

    <div class="relative z-10 min-h-screen flex flex-col justify-between">

        <!-- Top Navigation Bar -->
        <header class="no-print sticky top-0 z-40 border-b border-slate-200/80 dark:border-white/10 bg-white/70 dark:bg-[#0e0c19]/75 backdrop-blur-md px-4 sm:px-8 py-3.5 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="/kamkaj" class="flex items-center gap-2 group text-slate-500 dark:text-slate-400 hover:text-purple-600 dark:hover:text-purple-400 transition-colors">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-purple-600 to-indigo-500 flex items-center justify-center text-white shadow-lg shadow-purple-500/25 group-hover:scale-105 transition-transform">
                        <i class="fa-solid fa-calculator text-base"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs uppercase tracking-wider font-bold text-purple-600 dark:text-purple-400">Waldo HQ</span>
                            <span class="text-[10px] bg-purple-100 dark:bg-purple-900/50 text-purple-700 dark:text-purple-300 px-2 py-0.5 rounded-full font-semibold border border-purple-200 dark:border-purple-800">Finance Studio</span>
                        </div>
                        <h1 class="text-base sm:text-lg font-extrabold tracking-tight text-slate-900 dark:text-white flex items-center gap-2">
                            Salary & OT Simulator
                        </h1>
                    </div>
                </a>
            </div>

            <div class="flex items-center gap-2.5">
                <!-- Live State Status Badge -->
                <div class="hidden md:flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-medium bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border border-emerald-200/60 dark:border-emerald-800/60">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                    Live LocalStorage Synced
                </div>

                <!-- Copy Summary Button -->
                <button @click="copySummary()"
                        type="button"
                        class="px-3.5 py-1.5 rounded-xl text-xs sm:text-sm font-semibold bg-white dark:bg-white/5 hover:bg-slate-100 dark:hover:bg-white/10 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-white/10 shadow-sm transition-all flex items-center gap-2 active:scale-95"
                        title="Copy formatted summary to clipboard">
                    <i class="fa-regular fa-copy text-purple-500"></i>
                    <span class="hidden sm:inline" x-text="copied ? 'Copied!' : 'Copy Summary'"></span>
                </button>

                <!-- Print Slip -->
                <button @click="window.print()"
                        type="button"
                        class="px-3.5 py-1.5 rounded-xl text-xs sm:text-sm font-semibold bg-white dark:bg-white/5 hover:bg-slate-100 dark:hover:bg-white/10 text-slate-700 dark:text-slate-200 border border-slate-200 dark:border-white/10 shadow-sm transition-all flex items-center gap-2 active:scale-95"
                        title="Print or save as PDF">
                    <i class="fa-solid fa-print text-amber-500"></i>
                    <span class="hidden sm:inline">Print Slip</span>
                </button>

                <!-- Reset Inputs -->
                <button @click="resetDefaults()"
                        type="button"
                        class="px-3 py-1.5 rounded-xl text-xs sm:text-sm font-medium text-slate-500 hover:text-rose-600 dark:text-slate-400 dark:hover:text-rose-400 hover:bg-rose-50 dark:hover:bg-rose-950/30 transition-all flex items-center gap-1.5"
                        title="Reset inputs to initial default values">
                    <i class="fa-solid fa-rotate-left"></i>
                    <span class="hidden md:inline">Reset</span>
                </button>

                <!-- Dark / Light Mode Toggle -->
                <button @click="toggleTheme()"
                        type="button"
                        class="w-9 h-9 rounded-xl flex items-center justify-center text-slate-600 dark:text-amber-400 bg-white dark:bg-white/5 hover:bg-slate-100 dark:hover:bg-white/10 border border-slate-200 dark:border-white/10 transition-all"
                        aria-label="Toggle Dark/Light Mode">
                    <i class="fa-solid" :class="isDark ? 'fa-sun text-amber-400' : 'fa-moon text-purple-600'"></i>
                </button>

                <!-- Back to Kamkaj -->
                <a href="/kamkaj"
                   class="no-print ml-1 px-3 py-1.5 rounded-xl text-xs sm:text-sm font-semibold bg-purple-600 hover:bg-purple-700 text-white shadow-sm shadow-purple-600/30 transition-all flex items-center gap-1.5 active:scale-95">
                    <i class="fa-solid fa-arrow-left"></i>
                    <span class="hidden lg:inline">Panel</span>
                </a>
            </div>
        </header>

        <!-- Main Content Area -->
        <main class="flex-1 max-w-7xl w-full mx-auto p-4 sm:p-6 lg:p-8 space-y-8">

            <!-- Hero Header Banner with Bloom -->
            <div class="relative overflow-hidden rounded-3xl p-6 sm:p-8 border border-white/20 dark:border-purple-500/20 bg-gradient-to-r from-purple-900/90 via-indigo-900/80 to-slate-900 text-white shadow-2xl shadow-purple-900/20">
                <div class="absolute -right-10 -bottom-10 w-72 h-72 bg-purple-500/30 rounded-full blur-3xl pointer-events-none"></div>
                <div class="absolute -left-10 -top-10 w-72 h-72 bg-amber-500/20 rounded-full blur-3xl pointer-events-none"></div>

                <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                    <div>
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold bg-white/10 backdrop-blur-md border border-white/15 text-purple-200 mb-3">
                            <i class="fa-solid fa-sparkles text-amber-300"></i>
                            Live Dynamic Payroll Simulation Engine
                        </div>
                        <h2 class="text-2xl sm:text-3xl lg:text-4xl font-extrabold tracking-tight">
                            Salary, Overtime & Benefits Studio
                        </h2>
                        <p class="text-purple-200/90 text-sm sm:text-base mt-2 max-w-2xl leading-relaxed">
                            Effortlessly simulate gross-to-net breakdown, basic salary, allowances, Social Security Fund (SSF 11% / 20%), overtime tiers, prorated adjustments, and festival allowance with zero backend latency.
                        </p>
                    </div>

                    <!-- Live KPI Highlight Pill -->
                    <div class="w-full md:w-auto flex-shrink-0 bg-white/10 dark:bg-black/40 backdrop-blur-xl border border-white/15 rounded-2xl p-4 sm:p-5 flex flex-col justify-center shadow-lg">
                        <span class="text-xs uppercase tracking-wider text-purple-200 font-bold">Estimated Take-Home (Net)</span>
                        <div class="text-2xl sm:text-3xl font-extrabold font-mono-numbers text-amber-300 mt-1 flex items-baseline gap-1">
                            <span class="text-lg text-amber-200/70">Rs.</span>
                            <span x-text="formatNumber(netPayable)">0.00</span>
                        </div>
                        <div class="text-[11px] text-purple-200/80 mt-1 flex items-center justify-between gap-4">
                            <span>Cost to Company (CTC):</span>
                            <span class="font-mono font-semibold text-white">Rs. <span x-text="formatNumber(totalCTC)">0.00</span></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Two-Column Grid: Inputs (Left) and Live Visualizations / Cards (Right) -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 items-start">

                <!-- ═══════════════════════════════════════════════════════════════
                     LEFT COLUMN: USER INPUTS (5 Cols on LG)
                ═══════════════════════════════════════════════════════════════ -->
                <div class="lg:col-span-5 space-y-6">

                    <!-- Section 1: Base Salary & Month/Year -->
                    <div class="glass-panel rounded-3xl p-6 shadow-xl border border-slate-200/80 dark:border-white/10 space-y-5">
                        <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-white/10">
                            <div class="flex items-center gap-2.5">
                                <span class="w-8 h-8 rounded-xl bg-purple-500/10 dark:bg-purple-500/20 text-purple-600 dark:text-purple-400 flex items-center justify-center font-bold text-sm">
                                    <i class="fa-solid fa-money-bill-wave"></i>
                                </span>
                                <div>
                                    <h3 class="font-bold text-slate-900 dark:text-white text-base">Gross Salary & Period</h3>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">Monthly gross remuneration and calendar period</p>
                                </div>
                            </div>
                            <span class="text-[11px] font-mono px-2 py-0.5 rounded-md bg-purple-100 dark:bg-purple-950 text-purple-700 dark:text-purple-300 font-semibold">
                                Step 1
                            </span>
                        </div>

                        <!-- Gross Salary Input -->
                        <div class="space-y-2">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300">
                                Gross Salary (NPR) <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative rounded-2xl shadow-sm">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400 font-bold font-mono text-sm">
                                    Rs.
                                </div>
                                <input type="number"
                                       step="any"
                                       min="0"
                                       x-model.number="grossSalary"
                                       @input="saveState()"
                                       placeholder="e.g. 50000"
                                       class="block w-full rounded-2xl border-0 py-3.5 pl-12 pr-12 text-slate-900 dark:text-white bg-slate-100/70 dark:bg-white/5 ring-1 ring-inset ring-slate-300 dark:ring-white/10 placeholder:text-slate-400 focus:ring-2 focus:ring-inset focus:ring-purple-500 font-mono-numbers text-lg font-bold transition-all">
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                    <button type="button"
                                            @click="grossSalary = 0; saveState()"
                                            x-show="grossSalary > 0"
                                            class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 text-xs p-1">
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Quick Preset Buttons -->
                            <div class="flex flex-wrap items-center gap-1.5 pt-1">
                                <span class="text-[11px] text-slate-400 font-medium mr-1">Presets:</span>
                                <template x-for="preset in [25000, 35000, 50000, 75000, 100000, 150000]" :key="preset">
                                    <button type="button"
                                            @click="grossSalary = preset; saveState()"
                                            class="px-2.5 py-1 text-[11px] font-mono font-semibold rounded-lg bg-slate-100 hover:bg-purple-100 dark:bg-white/5 dark:hover:bg-purple-900/40 text-slate-600 hover:text-purple-700 dark:text-slate-300 dark:hover:text-purple-300 transition-colors border border-slate-200/60 dark:border-white/5"
                                            :class="grossSalary === preset ? '!bg-purple-600 !text-white !border-purple-600' : ''"
                                            x-text="(preset/1000) + 'k'">
                                    </button>
                                </template>
                            </div>
                        </div>

                        <!-- Month & Year Selector -->
                        <div class="grid grid-cols-2 gap-3 pt-2">
                            <div class="space-y-1.5">
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300">
                                    Month
                                </label>
                                <select x-model.number="selectedMonth"
                                        @change="saveState()"
                                        class="block w-full rounded-xl border-0 py-2.5 px-3 text-sm text-slate-900 dark:text-white bg-slate-100/70 dark:bg-white/5 ring-1 ring-inset ring-slate-300 dark:ring-white/10 focus:ring-2 focus:ring-purple-500 font-medium">
                                    <template x-for="(mName, idx) in months" :key="idx">
                                        <option :value="idx" :selected="selectedMonth === idx" x-text="mName"></option>
                                    </template>
                                </select>
                            </div>

                            <div class="space-y-1.5">
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300">
                                    Year
                                </label>
                                <select x-model.number="selectedYear"
                                        @change="saveState()"
                                        class="block w-full rounded-xl border-0 py-2.5 px-3 text-sm text-slate-900 dark:text-white bg-slate-100/70 dark:bg-white/5 ring-1 ring-inset ring-slate-300 dark:ring-white/10 focus:ring-2 focus:ring-purple-500 font-medium">
                                    <template x-for="yr in yearOptions" :key="yr">
                                        <option :value="yr" :selected="selectedYear === yr" x-text="yr"></option>
                                    </template>
                                </select>
                            </div>
                        </div>

                        <!-- Days in Month Indicator Pill -->
                        <div class="flex items-center justify-between px-3.5 py-2.5 rounded-xl bg-purple-50 dark:bg-purple-950/30 border border-purple-200/60 dark:border-purple-800/40 text-xs">
                            <span class="text-purple-700 dark:text-purple-300 font-medium flex items-center gap-1.5">
                                <i class="fa-regular fa-calendar-days text-purple-500"></i>
                                Days in <strong x-text="months[selectedMonth] + ' ' + selectedYear"></strong>:
                            </span>
                            <span class="font-mono font-bold text-purple-900 dark:text-purple-100 bg-white dark:bg-purple-900/60 px-2 py-0.5 rounded-md border border-purple-200 dark:border-purple-700"
                                  x-text="daysInSelectedMonth + ' Days'">
                            </span>
                        </div>
                    </div>

                    <!-- Section 2: Overtime Hours (Normal & Special) -->
                    <div class="glass-panel rounded-3xl p-6 shadow-xl border border-slate-200/80 dark:border-white/10 space-y-5">
                        <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-white/10">
                            <div class="flex items-center gap-2.5">
                                <span class="w-8 h-8 rounded-xl bg-amber-500/10 dark:bg-amber-500/20 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold text-sm">
                                    <i class="fa-solid fa-clock"></i>
                                </span>
                                <div>
                                    <h3 class="font-bold text-slate-900 dark:text-white text-base">Overtime Hours</h3>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">Normal (1.5x) and Special holiday/rest day (2.0x)</p>
                                </div>
                            </div>
                            <span class="text-[11px] font-mono px-2 py-0.5 rounded-md bg-amber-100 dark:bg-amber-950 text-amber-700 dark:text-amber-300 font-semibold">
                                Step 2
                            </span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Normal OT Hours -->
                            <div class="space-y-2 p-3.5 rounded-2xl bg-amber-50/50 dark:bg-white/[0.02] border border-amber-200/40 dark:border-white/5">
                                <div class="flex items-center justify-between">
                                    <label class="block text-xs font-bold uppercase tracking-wider text-amber-800 dark:text-amber-300">
                                        Normal OT
                                    </label>
                                    <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-amber-100 dark:bg-amber-900/60 text-amber-700 dark:text-amber-300">
                                        × 1.5 Rate
                                    </span>
                                </div>
                                <div class="relative">
                                    <input type="number"
                                           step="any"
                                           min="0"
                                           x-model.number="normalOtHours"
                                           @input="saveState()"
                                           placeholder="0"
                                           class="block w-full rounded-xl border-0 py-2.5 px-3 text-slate-900 dark:text-white bg-white dark:bg-white/5 ring-1 ring-inset ring-amber-300 dark:ring-white/10 font-mono-numbers text-base font-bold focus:ring-2 focus:ring-amber-500">
                                </div>
                                <!-- Steppers -->
                                <div class="flex items-center justify-between gap-1 pt-1">
                                    <div class="flex gap-1">
                                        <button type="button" @click="normalOtHours = Math.max(0, (normalOtHours || 0) - 1); saveState()"
                                                class="w-7 h-7 rounded-lg bg-white dark:bg-white/10 hover:bg-slate-100 dark:hover:bg-white/20 text-slate-600 dark:text-slate-200 text-xs font-bold border border-slate-200 dark:border-white/10 flex items-center justify-center">
                                            -1
                                        </button>
                                        <button type="button" @click="normalOtHours = (normalOtHours || 0) + 1; saveState()"
                                                class="w-7 h-7 rounded-lg bg-white dark:bg-white/10 hover:bg-slate-100 dark:hover:bg-white/20 text-slate-600 dark:text-slate-200 text-xs font-bold border border-slate-200 dark:border-white/10 flex items-center justify-center">
                                            +1
                                        </button>
                                    </div>
                                    <div class="flex gap-1">
                                        <button type="button" @click="normalOtHours = (normalOtHours || 0) + 4; saveState()"
                                                class="px-2 h-7 rounded-lg bg-amber-100 dark:bg-amber-900/40 hover:bg-amber-200 text-amber-800 dark:text-amber-200 text-[11px] font-bold border border-amber-300/50 flex items-center justify-center">
                                            +4h
                                        </button>
                                        <button type="button" @click="normalOtHours = (normalOtHours || 0) + 8; saveState()"
                                                class="px-2 h-7 rounded-lg bg-amber-100 dark:bg-amber-900/40 hover:bg-amber-200 text-amber-800 dark:text-amber-200 text-[11px] font-bold border border-amber-300/50 flex items-center justify-center">
                                            +8h
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Special OT Hours -->
                            <div class="space-y-2 p-3.5 rounded-2xl bg-rose-50/40 dark:bg-white/[0.02] border border-rose-200/40 dark:border-white/5">
                                <div class="flex items-center justify-between">
                                    <label class="block text-xs font-bold uppercase tracking-wider text-rose-800 dark:text-rose-300">
                                        Special OT
                                    </label>
                                    <span class="text-[10px] font-bold px-1.5 py-0.5 rounded bg-rose-100 dark:bg-rose-900/60 text-rose-700 dark:text-rose-300">
                                        × 2.0 Rate
                                    </span>
                                </div>
                                <div class="relative">
                                    <input type="number"
                                           step="any"
                                           min="0"
                                           x-model.number="specialOtHours"
                                           @input="saveState()"
                                           placeholder="0"
                                           class="block w-full rounded-xl border-0 py-2.5 px-3 text-slate-900 dark:text-white bg-white dark:bg-white/5 ring-1 ring-inset ring-rose-300 dark:ring-white/10 font-mono-numbers text-base font-bold focus:ring-2 focus:ring-rose-500">
                                </div>
                                <!-- Steppers -->
                                <div class="flex items-center justify-between gap-1 pt-1">
                                    <div class="flex gap-1">
                                        <button type="button" @click="specialOtHours = Math.max(0, (specialOtHours || 0) - 1); saveState()"
                                                class="w-7 h-7 rounded-lg bg-white dark:bg-white/10 hover:bg-slate-100 dark:hover:bg-white/20 text-slate-600 dark:text-slate-200 text-xs font-bold border border-slate-200 dark:border-white/10 flex items-center justify-center">
                                            -1
                                        </button>
                                        <button type="button" @click="specialOtHours = (specialOtHours || 0) + 1; saveState()"
                                                class="w-7 h-7 rounded-lg bg-white dark:bg-white/10 hover:bg-slate-100 dark:hover:bg-white/20 text-slate-600 dark:text-slate-200 text-xs font-bold border border-slate-200 dark:border-white/10 flex items-center justify-center">
                                            +1
                                        </button>
                                    </div>
                                    <div class="flex gap-1">
                                        <button type="button" @click="specialOtHours = (specialOtHours || 0) + 4; saveState()"
                                                class="px-2 h-7 rounded-lg bg-rose-100 dark:bg-rose-900/40 hover:bg-rose-200 text-rose-800 dark:text-rose-200 text-[11px] font-bold border border-rose-300/50 flex items-center justify-center">
                                            +4h
                                        </button>
                                        <button type="button" @click="specialOtHours = (specialOtHours || 0) + 8; saveState()"
                                                class="px-2 h-7 rounded-lg bg-rose-100 dark:bg-rose-900/40 hover:bg-rose-200 text-rose-800 dark:text-rose-200 text-[11px] font-bold border border-rose-300/50 flex items-center justify-center">
                                            +8h
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section 3: Adjustments (Days & Manual Amount) -->
                    <div class="glass-panel rounded-3xl p-6 shadow-xl border border-slate-200/80 dark:border-white/10 space-y-5">
                        <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-white/10">
                            <div class="flex items-center gap-2.5">
                                <span class="w-8 h-8 rounded-xl bg-cyan-500/10 dark:bg-cyan-500/20 text-cyan-600 dark:text-cyan-400 flex items-center justify-center font-bold text-sm">
                                    <i class="fa-solid fa-sliders"></i>
                                </span>
                                <div>
                                    <h3 class="font-bold text-slate-900 dark:text-white text-base">Adjustments</h3>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">Prorated days adjustment and direct manual amounts</p>
                                </div>
                            </div>
                            <span class="text-[11px] font-mono px-2 py-0.5 rounded-md bg-cyan-100 dark:bg-cyan-950 text-cyan-700 dark:text-cyan-300 font-semibold">
                                Step 3
                            </span>
                        </div>

                        <!-- Adjustment Days -->
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300">
                                    Adjustment Days
                                </label>
                                <div class="flex items-center gap-1 bg-slate-100 dark:bg-white/5 p-0.5 rounded-lg border border-slate-200/60 dark:border-white/5 text-[11px]">
                                    <button type="button"
                                            @click="adjDaysSign = 1; saveState()"
                                            class="px-2 py-0.5 rounded font-bold transition-all"
                                            :class="adjDaysSign === 1 ? 'bg-emerald-500 text-white shadow-sm' : 'text-slate-500 hover:text-slate-700 dark:text-slate-400'">
                                        + Add
                                    </button>
                                    <button type="button"
                                            @click="adjDaysSign = -1; saveState()"
                                            class="px-2 py-0.5 rounded font-bold transition-all"
                                            :class="adjDaysSign === -1 ? 'bg-rose-500 text-white shadow-sm' : 'text-slate-500 hover:text-slate-700 dark:text-slate-400'">
                                        - Deduct
                                    </button>
                                </div>
                            </div>
                            <div class="relative">
                                <input type="number"
                                       step="any"
                                       min="0"
                                       x-model.number="adjustmentDays"
                                       @input="saveState()"
                                       placeholder="0"
                                       class="block w-full rounded-xl border-0 py-2.5 px-3 text-slate-900 dark:text-white bg-slate-100/70 dark:bg-white/5 ring-1 ring-inset ring-slate-300 dark:ring-white/10 font-mono-numbers text-base font-bold focus:ring-2 focus:ring-cyan-500">
                            </div>
                            <div class="text-[11px] text-slate-500 dark:text-slate-400 flex items-center justify-between">
                                <span>Computed via Per-Day rate (Rs. <span x-text="formatNumber(perDay)">0.00</span>):</span>
                                <span class="font-mono font-bold"
                                      :class="adjDaysSign === 1 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'">
                                    <span x-text="adjDaysSign === 1 ? '+' : '-'"></span> Rs. <span x-text="formatNumber(adjustmentDaysAmountAbsolute)">0.00</span>
                                </span>
                            </div>
                        </div>

                        <!-- Manual Adjustment Amount -->
                        <div class="space-y-2 pt-2 border-t border-slate-200/60 dark:border-white/5">
                            <div class="flex items-center justify-between">
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300">
                                    Manual Adjustment Amount
                                </label>
                                <div class="flex items-center gap-1 bg-slate-100 dark:bg-white/5 p-0.5 rounded-lg border border-slate-200/60 dark:border-white/5 text-[11px]">
                                    <button type="button"
                                            @click="adjAmountSign = 1; saveState()"
                                            class="px-2 py-0.5 rounded font-bold transition-all"
                                            :class="adjAmountSign === 1 ? 'bg-emerald-500 text-white shadow-sm' : 'text-slate-500 hover:text-slate-700 dark:text-slate-400'">
                                        + Add
                                    </button>
                                    <button type="button"
                                            @click="adjAmountSign = -1; saveState()"
                                            class="px-2 py-0.5 rounded font-bold transition-all"
                                            :class="adjAmountSign === -1 ? 'bg-rose-500 text-white shadow-sm' : 'text-slate-500 hover:text-slate-700 dark:text-slate-400'">
                                        - Deduct
                                    </button>
                                </div>
                            </div>
                            <div class="relative">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400 font-bold font-mono text-xs">
                                    Rs.
                                </div>
                                <input type="number"
                                       step="any"
                                       min="0"
                                       x-model.number="adjustmentManualAmount"
                                       @input="saveState()"
                                       placeholder="0.00"
                                       class="block w-full rounded-xl border-0 py-2.5 pl-10 pr-3 text-slate-900 dark:text-white bg-slate-100/70 dark:bg-white/5 ring-1 ring-inset ring-slate-300 dark:ring-white/10 font-mono-numbers text-base font-bold focus:ring-2 focus:ring-cyan-500">
                            </div>
                        </div>
                    </div>

                    <!-- Section 4: Festival Allowance Inputs & Date Range -->
                    <div class="glass-panel rounded-3xl p-6 shadow-xl border border-slate-200/80 dark:border-white/10 space-y-5">
                        <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-white/10">
                            <div class="flex items-center gap-2.5">
                                <span class="w-8 h-8 rounded-xl bg-emerald-500/10 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold text-sm">
                                    <i class="fa-solid fa-gift"></i>
                                </span>
                                <div>
                                    <h3 class="font-bold text-slate-900 dark:text-white text-base">Festival Allowance</h3>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">Prorated Dashain / Tihar / Festival entitlement</p>
                                </div>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" x-model="includeFestival" @change="saveState()" class="sr-only peer">
                                <div class="w-10 h-5 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-white/10 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-gray-600 peer-checked:bg-emerald-500"></div>
                            </label>
                        </div>

                        <div x-show="includeFestival" x-transition.opacity.duration.200ms class="space-y-4">
                            <!-- Calculation Basis Radio Option (Gross vs Basic) -->
                            <div class="space-y-1.5">
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300">
                                    Calculation Basis
                                </label>
                                <div class="grid grid-cols-2 gap-2 bg-slate-100 dark:bg-white/5 p-1 rounded-xl border border-slate-200/60 dark:border-white/5">
                                    <button type="button"
                                            @click="festivalBasis = 'gross'; saveState()"
                                            class="py-2 px-3 rounded-lg text-xs font-bold flex items-center justify-center gap-2 transition-all"
                                            :class="festivalBasis === 'gross' ? 'bg-emerald-600 text-white shadow-md' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'">
                                        <i class="fa-solid fa-coins"></i>
                                        Gross Salary
                                    </button>
                                    <button type="button"
                                            @click="festivalBasis = 'basic'; saveState()"
                                            class="py-2 px-3 rounded-lg text-xs font-bold flex items-center justify-center gap-2 transition-all"
                                            :class="festivalBasis === 'basic' ? 'bg-emerald-600 text-white shadow-md' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white'">
                                        <i class="fa-solid fa-layer-group"></i>
                                        Basic Salary (60%)
                                    </button>
                                </div>
                            </div>

                            <!-- Date Range Selector (From & To) -->
                            <div class="grid grid-cols-2 gap-3 pt-1">
                                <div class="space-y-1.5">
                                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300">
                                        From Date
                                    </label>
                                    <input type="date"
                                           x-model="festivalDateFrom"
                                           @change="calculateDaysFromDates(); saveState()"
                                           class="block w-full rounded-xl border-0 py-2 px-2.5 text-xs text-slate-900 dark:text-white bg-slate-100/70 dark:bg-white/5 ring-1 ring-inset ring-slate-300 dark:ring-white/10 focus:ring-2 focus:ring-emerald-500 font-mono">
                                </div>
                                <div class="space-y-1.5">
                                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300">
                                        To Date (End Date)
                                    </label>
                                    <input type="date"
                                           x-model="festivalDateTo"
                                           @change="calculateDaysFromDates(); saveState()"
                                           class="block w-full rounded-xl border-0 py-2 px-2.5 text-xs text-slate-900 dark:text-white bg-slate-100/70 dark:bg-white/5 ring-1 ring-inset ring-slate-300 dark:ring-white/10 focus:ring-2 focus:ring-emerald-500 font-mono">
                                </div>
                            </div>

                            <!-- Working Days Input (Auto-calculated or Manual Override, Capped at 365) -->
                            <div class="space-y-1.5">
                                <div class="flex items-center justify-between">
                                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300">
                                        Total Working Days
                                    </label>
                                    <span class="text-[11px] text-emerald-600 dark:text-emerald-400 font-semibold"
                                          x-show="workingDays >= 365">
                                        <i class="fa-solid fa-circle-check"></i> Capped at 365 days (Full Year)
                                    </span>
                                </div>
                                <div class="relative">
                                    <input type="number"
                                           step="1"
                                           min="0"
                                           max="365"
                                           x-model.number="workingDays"
                                           @input="if(workingDays > 365) workingDays = 365; saveState()"
                                           placeholder="365"
                                           class="block w-full rounded-xl border-0 py-2.5 px-3 text-slate-900 dark:text-white bg-slate-100/70 dark:bg-white/5 ring-1 ring-inset ring-slate-300 dark:ring-white/10 font-mono-numbers text-base font-bold focus:ring-2 focus:ring-emerald-500">
                                </div>
                                <p class="text-[11px] text-slate-500 dark:text-slate-400 italic">
                                    Formula: ((<span x-text="festivalBasis === 'gross' ? 'Gross' : 'Basic'"></span> Salary) / 365) × <span x-text="workingDays"></span> working days
                                </p>
                            </div>
                        </div>

                        <div x-show="!includeFestival" class="text-xs text-slate-400 text-center py-2 italic">
                            Toggle ON to calculate and include Festival Allowance in the breakdown.
                        </div>
                    </div>

                </div>

                <!-- ═══════════════════════════════════════════════════════════════
                     RIGHT COLUMN: LIVE VISUAL CALCULATIONS & BREAKDOWN (7 Cols on LG)
                ═══════════════════════════════════════════════════════════════ -->
                <div class="lg:col-span-7 space-y-6">

                    <!-- High-Impact Executive Paycard Summary -->
                    <div class="glass-panel glass-card-interactive rounded-3xl p-6 sm:p-7 shadow-2xl border-2 border-purple-500/30 dark:border-purple-400/30 bg-gradient-to-br from-white/90 via-purple-50/40 to-slate-50 dark:from-purple-950/40 dark:via-[#161226]/80 dark:to-[#0f0d1b] relative overflow-hidden">
                        <!-- Top Accent Glow -->
                        <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-purple-500 via-amber-400 to-emerald-400"></div>

                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-5 border-b border-slate-200 dark:border-white/10">
                            <div>
                                <span class="text-xs uppercase tracking-wider font-extrabold text-purple-600 dark:text-purple-400 flex items-center gap-1.5">
                                    <i class="fa-solid fa-receipt"></i>
                                    Payroll Executive Summary
                                </span>
                                <h3 class="text-xl sm:text-2xl font-extrabold text-slate-900 dark:text-white mt-0.5">
                                    Net Payable Computation
                                </h3>
                            </div>
                            <div class="text-right">
                                <span class="text-[11px] font-semibold text-slate-400 block uppercase">Period</span>
                                <span class="font-bold text-sm text-slate-800 dark:text-slate-200" x-text="months[selectedMonth] + ' ' + selectedYear"></span>
                            </div>
                        </div>

                        <!-- Main Figure Highlight -->
                        <div class="py-6 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 bg-purple-500/5 dark:bg-purple-500/10 rounded-2xl p-4 my-4 border border-purple-200/50 dark:border-purple-500/20">
                            <div>
                                <span class="text-xs uppercase tracking-wider font-bold text-slate-500 dark:text-slate-400">Take-Home Salary (Employee Net)</span>
                                <div class="text-3xl sm:text-4xl font-extrabold font-mono-numbers text-purple-600 dark:text-purple-300 mt-1 flex items-baseline gap-1.5">
                                    <span class="text-xl text-purple-500/70 dark:text-purple-400/60 font-sans">NPR</span>
                                    <span x-text="formatNumber(netPayable)">0.00</span>
                                </div>
                            </div>
                            <div class="sm:text-right space-y-1">
                                <div class="text-xs font-semibold text-slate-500 dark:text-slate-400">Total Employer Outlay (CTC)</div>
                                <div class="text-xl font-bold font-mono-numbers text-slate-800 dark:text-slate-200">
                                    Rs. <span x-text="formatNumber(totalCTC)">0.00</span>
                                </div>
                            </div>
                        </div>

                        <!-- Summary Line Items Table -->
                        <div class="space-y-2.5 text-xs sm:text-sm">
                            <!-- Gross Base -->
                            <div class="flex items-center justify-between py-1.5 border-b border-slate-100 dark:border-white/5">
                                <span class="text-slate-600 dark:text-slate-300 flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-purple-500"></span>
                                    Gross Salary Base
                                </span>
                                <span class="font-mono font-semibold text-slate-900 dark:text-white">
                                    Rs. <span x-text="formatNumber(grossSalary)">0.00</span>
                                </span>
                            </div>

                            <!-- Overtime Total -->
                            <div class="flex items-center justify-between py-1.5 border-b border-slate-100 dark:border-white/5">
                                <span class="text-slate-600 dark:text-slate-300 flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                                    Total Overtime (OT Normal + Special)
                                </span>
                                <span class="font-mono font-semibold text-amber-600 dark:text-amber-400">
                                    + Rs. <span x-text="formatNumber(totalOtAmount)">0.00</span>
                                </span>
                            </div>

                            <!-- Total Adjustments -->
                            <div class="flex items-center justify-between py-1.5 border-b border-slate-100 dark:border-white/5">
                                <span class="text-slate-600 dark:text-slate-300 flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-cyan-500"></span>
                                    Total Adjustments (Days + Manual)
                                </span>
                                <span class="font-mono font-semibold"
                                      :class="totalAdjustments >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'">
                                    <span x-text="totalAdjustments >= 0 ? '+' : '-'"></span> Rs. <span x-text="formatNumber(Math.abs(totalAdjustments))">0.00</span>
                                </span>
                            </div>

                            <!-- Festival Allowance (if included) -->
                            <template x-if="includeFestival">
                                <div class="flex items-center justify-between py-1.5 border-b border-slate-100 dark:border-white/5">
                                    <span class="text-slate-600 dark:text-slate-300 flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                        Festival Allowance (<span x-text="workingDays"></span> working days)
                                    </span>
                                    <span class="font-mono font-semibold text-emerald-600 dark:text-emerald-400">
                                        + Rs. <span x-text="formatNumber(festivalAllowanceAmount)">0.00</span>
                                    </span>
                                </div>
                            </template>

                            <!-- Employee SSF Deduction -->
                            <div class="flex items-center justify-between py-1.5 border-b border-slate-100 dark:border-white/5">
                                <span class="text-slate-600 dark:text-slate-300 flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                                    Employee SSF Contribution (11% of Basic)
                                </span>
                                <span class="font-mono font-semibold text-rose-600 dark:text-rose-400">
                                    - Rs. <span x-text="formatNumber(ssf11Amount)">0.00</span>
                                </span>
                            </div>

                            <!-- Employer SSF Contribution (informational) -->
                            <div class="flex items-center justify-between py-1 text-slate-500 dark:text-slate-400 text-xs">
                                <span class="flex items-center gap-2">
                                    <i class="fa-solid fa-building-columns text-[10px]"></i>
                                    Employer SSF Deposit (20% of Basic)
                                </span>
                                <span class="font-mono font-semibold text-slate-600 dark:text-slate-400">
                                    Rs. <span x-text="formatNumber(ssf20Amount)">0.00</span>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Card 1: Salary Structure & Daily/Hourly Rates -->
                    <div class="glass-panel glass-card-interactive rounded-3xl p-6 shadow-xl border border-slate-200/80 dark:border-white/10 space-y-5">
                        <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-white/10">
                            <div class="flex items-center gap-2.5">
                                <span class="w-8 h-8 rounded-xl bg-purple-500/10 dark:bg-purple-500/20 text-purple-600 dark:text-purple-400 flex items-center justify-center font-bold text-sm">
                                    <i class="fa-solid fa-chart-pie"></i>
                                </span>
                                <div>
                                    <h4 class="font-bold text-slate-900 dark:text-white text-base">Salary Structure Breakdown</h4>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">60/40 Basic & Allowance allocation with SSF calculations</p>
                                </div>
                            </div>
                        </div>

                        <!-- Progress visual split -->
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between text-xs font-semibold">
                                <span class="text-purple-600 dark:text-purple-400">Basic Salary (60%)</span>
                                <span class="text-indigo-600 dark:text-indigo-400">Allowance (40%)</span>
                            </div>
                            <div class="h-3 w-full rounded-full bg-slate-200 dark:bg-white/10 overflow-hidden flex shadow-inner">
                                <div class="bg-gradient-to-r from-purple-600 to-purple-500 h-full w-[60%] transition-all duration-300"></div>
                                <div class="bg-gradient-to-r from-indigo-500 to-cyan-500 h-full w-[40%] transition-all duration-300"></div>
                            </div>
                        </div>

                        <!-- 4-Stat Box Grid -->
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3.5 pt-2">
                            <!-- Basic Salary -->
                            <div class="p-3.5 rounded-2xl bg-purple-50/50 dark:bg-white/[0.02] border border-purple-200/40 dark:border-white/5 space-y-1">
                                <span class="text-[11px] font-bold uppercase tracking-wider text-purple-800 dark:text-purple-300">Basic Salary (60%)</span>
                                <div class="text-base sm:text-lg font-bold font-mono-numbers text-slate-900 dark:text-white">
                                    Rs. <span x-text="formatNumber(basicSalary)">0.00</span>
                                </div>
                                <span class="text-[10px] text-slate-500 dark:text-slate-400 block">Gross × 0.60</span>
                            </div>

                            <!-- Allowance -->
                            <div class="p-3.5 rounded-2xl bg-indigo-50/50 dark:bg-white/[0.02] border border-indigo-200/40 dark:border-white/5 space-y-1">
                                <span class="text-[11px] font-bold uppercase tracking-wider text-indigo-800 dark:text-indigo-300">Allowance (40%)</span>
                                <div class="text-base sm:text-lg font-bold font-mono-numbers text-slate-900 dark:text-white">
                                    Rs. <span x-text="formatNumber(allowance)">0.00</span>
                                </div>
                                <span class="text-[10px] text-slate-500 dark:text-slate-400 block">Gross × 0.40</span>
                            </div>

                            <!-- Per Day -->
                            <div class="p-3.5 rounded-2xl bg-slate-100/60 dark:bg-white/[0.02] border border-slate-200/60 dark:border-white/5 space-y-1">
                                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">Per Day Value</span>
                                <div class="text-base sm:text-lg font-bold font-mono-numbers text-purple-600 dark:text-purple-400">
                                    Rs. <span x-text="formatNumber(perDay)">0.00</span>
                                </div>
                                <span class="text-[10px] text-slate-500 dark:text-slate-400 block">Gross / <span x-text="daysInSelectedMonth"></span> Days</span>
                            </div>

                            <!-- Per Hour -->
                            <div class="p-3.5 rounded-2xl bg-slate-100/60 dark:bg-white/[0.02] border border-slate-200/60 dark:border-white/5 space-y-1">
                                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300">Per Hour Value</span>
                                <div class="text-base sm:text-lg font-bold font-mono-numbers text-slate-900 dark:text-white">
                                    Rs. <span x-text="formatNumber(perHour)">0.00</span>
                                </div>
                                <span class="text-[10px] text-slate-500 dark:text-slate-400 block">Per Day / 8 Hours</span>
                            </div>

                            <!-- Normal OT Per Hour -->
                            <div class="p-3.5 rounded-2xl bg-amber-50/50 dark:bg-white/[0.02] border border-amber-200/40 dark:border-white/5 space-y-1">
                                <span class="text-[11px] font-bold uppercase tracking-wider text-amber-800 dark:text-amber-300">Normal OT / Hr</span>
                                <div class="text-base sm:text-lg font-bold font-mono-numbers text-amber-600 dark:text-amber-400">
                                    Rs. <span x-text="formatNumber(normalOtPerHour)">0.00</span>
                                </div>
                                <span class="text-[10px] text-slate-500 dark:text-slate-400 block">Per Hour × 1.5</span>
                            </div>

                            <!-- Special OT Per Hour -->
                            <div class="p-3.5 rounded-2xl bg-rose-50/50 dark:bg-white/[0.02] border border-rose-200/40 dark:border-white/5 space-y-1">
                                <span class="text-[11px] font-bold uppercase tracking-wider text-rose-800 dark:text-rose-300">Special OT / Hr</span>
                                <div class="text-base sm:text-lg font-bold font-mono-numbers text-rose-600 dark:text-rose-400">
                                    Rs. <span x-text="formatNumber(specialOtPerHour)">0.00</span>
                                </div>
                                <span class="text-[10px] text-slate-500 dark:text-slate-400 block">Per Hour × 2.0</span>
                            </div>
                        </div>

                        <!-- Social Security Fund (SSF) Deep Dive -->
                        <div class="pt-2 border-t border-slate-200/60 dark:border-white/5">
                            <span class="text-xs font-bold uppercase tracking-wider text-slate-700 dark:text-slate-300 block mb-2">
                                Social Security Fund (SSF) Breakdown
                            </span>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div class="p-3 rounded-xl bg-slate-100/80 dark:bg-white/5 border border-slate-200/60 dark:border-white/5">
                                    <span class="text-[10px] uppercase font-bold text-slate-500 dark:text-slate-400 block">Employee (11% SSF)</span>
                                    <div class="text-sm font-bold font-mono-numbers text-rose-600 dark:text-rose-400 mt-0.5">
                                        Rs. <span x-text="formatNumber(ssf11Amount)">0.00</span>
                                    </div>
                                </div>
                                <div class="p-3 rounded-xl bg-slate-100/80 dark:bg-white/5 border border-slate-200/60 dark:border-white/5">
                                    <span class="text-[10px] uppercase font-bold text-slate-500 dark:text-slate-400 block">Employer (20% SSF)</span>
                                    <div class="text-sm font-bold font-mono-numbers text-purple-600 dark:text-purple-400 mt-0.5">
                                        Rs. <span x-text="formatNumber(ssf20Amount)">0.00</span>
                                    </div>
                                </div>
                                <div class="p-3 rounded-xl bg-purple-50 dark:bg-purple-950/40 border border-purple-200/60 dark:border-purple-800/40">
                                    <span class="text-[10px] uppercase font-bold text-purple-700 dark:text-purple-300 block">Total SSF (31%)</span>
                                    <div class="text-sm font-bold font-mono-numbers text-purple-900 dark:text-purple-200 mt-0.5">
                                        Rs. <span x-text="formatNumber(totalSsfAmount)">0.00</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card 2: Overtime Calculations & Totals -->
                    <div class="glass-panel glass-card-interactive rounded-3xl p-6 shadow-xl border border-slate-200/80 dark:border-white/10 space-y-4">
                        <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-white/10">
                            <div class="flex items-center gap-2.5">
                                <span class="w-8 h-8 rounded-xl bg-amber-500/10 dark:bg-amber-500/20 text-amber-600 dark:text-amber-400 flex items-center justify-center font-bold text-sm">
                                    <i class="fa-solid fa-business-time"></i>
                                </span>
                                <div>
                                    <h4 class="font-bold text-slate-900 dark:text-white text-base">Overtime Totals</h4>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">Total payable amounts based on logged OT hours</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="text-xs text-slate-400 block font-semibold uppercase">Total OT Amount</span>
                                <span class="text-lg font-extrabold font-mono-numbers text-amber-600 dark:text-amber-400">
                                    Rs. <span x-text="formatNumber(totalOtAmount)">0.00</span>
                                </span>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Normal OT Total -->
                            <div class="p-4 rounded-2xl bg-amber-50/40 dark:bg-white/[0.02] border border-amber-200/50 dark:border-white/5 space-y-1.5">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="font-bold text-amber-800 dark:text-amber-300">Normal OT Amount</span>
                                    <span class="font-mono text-slate-500 dark:text-slate-400" x-text="(normalOtHours || 0) + ' hrs'"></span>
                                </div>
                                <div class="text-xl font-bold font-mono-numbers text-slate-900 dark:text-white">
                                    Rs. <span x-text="formatNumber(normalOtTotalAmount)">0.00</span>
                                </div>
                                <div class="text-[11px] text-slate-500 dark:text-slate-400">
                                    Rs. <span x-text="formatNumber(normalOtPerHour)">0.00</span> / hr × <span x-text="normalOtHours || 0">0</span> hrs
                                </div>
                            </div>

                            <!-- Special OT Total -->
                            <div class="p-4 rounded-2xl bg-rose-50/40 dark:bg-white/[0.02] border border-rose-200/50 dark:border-white/5 space-y-1.5">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="font-bold text-rose-800 dark:text-rose-300">Special OT Amount</span>
                                    <span class="font-mono text-slate-500 dark:text-slate-400" x-text="(specialOtHours || 0) + ' hrs'"></span>
                                </div>
                                <div class="text-xl font-bold font-mono-numbers text-slate-900 dark:text-white">
                                    Rs. <span x-text="formatNumber(specialOtTotalAmount)">0.00</span>
                                </div>
                                <div class="text-[11px] text-slate-500 dark:text-slate-400">
                                    Rs. <span x-text="formatNumber(specialOtPerHour)">0.00</span> / hr × <span x-text="specialOtHours || 0">0</span> hrs
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card 3: Adjustments Breakdown -->
                    <div class="glass-panel glass-card-interactive rounded-3xl p-6 shadow-xl border border-slate-200/80 dark:border-white/10 space-y-4">
                        <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-white/10">
                            <div class="flex items-center gap-2.5">
                                <span class="w-8 h-8 rounded-xl bg-cyan-500/10 dark:bg-cyan-500/20 text-cyan-600 dark:text-cyan-400 flex items-center justify-center font-bold text-sm">
                                    <i class="fa-solid fa-calculator"></i>
                                </span>
                                <div>
                                    <h4 class="font-bold text-slate-900 dark:text-white text-base">Adjustments Breakdown</h4>
                                    <p class="text-xs text-slate-500 dark:text-slate-400">Computed prorated adjustment and direct manual amounts</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="text-xs text-slate-400 block font-semibold uppercase">Net Adjustments</span>
                                <span class="text-lg font-extrabold font-mono-numbers"
                                      :class="totalAdjustments >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'">
                                    <span x-text="totalAdjustments >= 0 ? '+' : '-'"></span> Rs. <span x-text="formatNumber(Math.abs(totalAdjustments))">0.00</span>
                                </span>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Adjustment from Days -->
                            <div class="p-3.5 rounded-2xl bg-slate-100/60 dark:bg-white/[0.02] border border-slate-200/60 dark:border-white/5 space-y-1">
                                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300">Days Adjustment</span>
                                <div class="text-base font-bold font-mono-numbers"
                                      :class="adjDaysSign === 1 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'">
                                    <span x-text="adjDaysSign === 1 ? '+' : '-'"></span> Rs. <span x-text="formatNumber(adjustmentDaysAmountAbsolute)">0.00</span>
                                </div>
                                <span class="text-[10px] text-slate-500 dark:text-slate-400 block">
                                    Rs. <span x-text="formatNumber(perDay)"></span> / day × <span x-text="adjustmentDays || 0"></span> days
                                </span>
                            </div>

                            <!-- Manual Adjustment -->
                            <div class="p-3.5 rounded-2xl bg-slate-100/60 dark:bg-white/[0.02] border border-slate-200/60 dark:border-white/5 space-y-1">
                                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-600 dark:text-slate-300">Manual Amount Adjustment</span>
                                <div class="text-base font-bold font-mono-numbers"
                                      :class="adjAmountSign === 1 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'">
                                    <span x-text="adjAmountSign === 1 ? '+' : '-'"></span> Rs. <span x-text="formatNumber(adjustmentManualAmount || 0)">0.00</span>
                                </div>
                                <span class="text-[10px] text-slate-500 dark:text-slate-400 block">Direct manual allowance/deduction</span>
                            </div>
                        </div>
                    </div>

                    <!-- Card 4: Festival Allowance Detailed Card -->
                    <template x-if="includeFestival">
                        <div class="glass-panel glass-card-interactive rounded-3xl p-6 shadow-xl border border-emerald-500/30 dark:border-emerald-500/20 space-y-4 bg-emerald-50/20 dark:bg-emerald-950/10">
                            <div class="flex items-center justify-between pb-3 border-b border-slate-200 dark:border-white/10">
                                <div class="flex items-center gap-2.5">
                                    <span class="w-8 h-8 rounded-xl bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold text-sm">
                                        <i class="fa-solid fa-gift"></i>
                                    </span>
                                    <div>
                                        <h4 class="font-bold text-slate-900 dark:text-white text-base">Festival Allowance Computed</h4>
                                        <p class="text-xs text-slate-500 dark:text-slate-400">Prorated for <span x-text="workingDays"></span> / 365 days on <span x-text="festivalBasis === 'gross' ? 'Gross' : 'Basic (60%)'"></span> salary</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="text-xs text-slate-400 block font-semibold uppercase">Total Allowance</span>
                                    <span class="text-xl font-extrabold font-mono-numbers text-emerald-600 dark:text-emerald-400">
                                        Rs. <span x-text="formatNumber(festivalAllowanceAmount)">0.00</span>
                                    </span>
                                </div>
                            </div>

                            <div class="p-3.5 rounded-2xl bg-white/70 dark:bg-black/30 border border-emerald-200/60 dark:border-emerald-800/40 text-xs font-mono space-y-1">
                                <div class="text-slate-500 dark:text-slate-400">Applied Formula:</div>
                                <div class="text-emerald-700 dark:text-emerald-300 font-bold">
                                    ((Rs. <span x-text="formatNumber(festivalBasisSalary)">0.00</span>) / 365) × <span x-text="workingDays"></span> Days = Rs. <span x-text="formatNumber(festivalAllowanceAmount)">0.00</span>
                                </div>
                            </div>
                        </div>
                    </template>

                </div>

            </div>

        </main>

        <!-- Footer -->
        <footer class="no-print mt-12 py-6 border-t border-slate-200 dark:border-white/5 bg-white/40 dark:bg-black/20 text-center text-xs text-slate-500 dark:text-slate-400 flex flex-col sm:flex-row items-center justify-between px-6 sm:px-12 max-w-7xl mx-auto w-full gap-2">
            <div>
                Waldo HQ Operations — Designed with precision & elegance.
            </div>
            <div class="flex items-center gap-4">
                <span>Calculations rounded to 2 decimal places</span>
                <span>•</span>
                <a href="/kamkaj" class="text-purple-600 dark:text-purple-400 hover:underline">Back to Kamkaj</a>
            </div>
        </footer>

    </div>

    <!-- Alpine.js Calculator Logic with LocalStorage Sync -->
    <script>
        function salaryCalculator() {
            const STORAGE_KEY = 'waldo_salary_calculator_state_v1';

            // Get initial defaults
            const now = new Date();
            const defaultState = {
                grossSalary: 50000,
                selectedMonth: now.getMonth(),
                selectedYear: now.getFullYear(),
                normalOtHours: 0,
                specialOtHours: 0,
                adjustmentDays: 0,
                adjDaysSign: 1, // 1 for Add, -1 for Deduct
                adjustmentManualAmount: 0,
                adjAmountSign: 1, // 1 for Add, -1 for Deduct
                includeFestival: true,
                festivalBasis: 'gross', // 'gross' or 'basic'
                festivalDateFrom: new Date(now.getFullYear(), 0, 1).toISOString().split('T')[0],
                festivalDateTo: now.toISOString().split('T')[0],
                workingDays: 365,
            };

            // Load saved state from LocalStorage if present
            let initial = defaultState;
            try {
                const saved = localStorage.getItem(STORAGE_KEY);
                if (saved) {
                    initial = { ...defaultState, ...JSON.parse(saved) };
                }
            } catch (_) {}

            return {
                // Reactive State
                grossSalary: initial.grossSalary,
                selectedMonth: initial.selectedMonth,
                selectedYear: initial.selectedYear,
                normalOtHours: initial.normalOtHours,
                specialOtHours: initial.specialOtHours,
                adjustmentDays: initial.adjustmentDays,
                adjDaysSign: initial.adjDaysSign,
                adjustmentManualAmount: initial.adjustmentManualAmount,
                adjAmountSign: initial.adjAmountSign,
                includeFestival: initial.includeFestival,
                festivalBasis: initial.festivalBasis,
                festivalDateFrom: initial.festivalDateFrom,
                festivalDateTo: initial.festivalDateTo,
                workingDays: initial.workingDays,

                // UI State
                isDark: document.documentElement.classList.contains('dark'),
                copied: false,
                months: [
                    'January', 'February', 'March', 'April', 'May', 'June',
                    'July', 'August', 'September', 'October', 'November', 'December'
                ],
                yearOptions: [2023, 2024, 2025, 2026, 2027, 2028, 2029, 2030],

                init() {
                    // Recompute days on initial load if date range exists
                    if (this.festivalDateFrom && this.festivalDateTo) {
                        this.calculateDaysFromDates();
                    }
                },

                // Persist state to browser LocalStorage
                saveState() {
                    try {
                        const payload = {
                            grossSalary: this.grossSalary,
                            selectedMonth: this.selectedMonth,
                            selectedYear: this.selectedYear,
                            normalOtHours: this.normalOtHours,
                            specialOtHours: this.specialOtHours,
                            adjustmentDays: this.adjustmentDays,
                            adjDaysSign: this.adjDaysSign,
                            adjustmentManualAmount: this.adjustmentManualAmount,
                            adjAmountSign: this.adjAmountSign,
                            includeFestival: this.includeFestival,
                            festivalBasis: this.festivalBasis,
                            festivalDateFrom: this.festivalDateFrom,
                            festivalDateTo: this.festivalDateTo,
                            workingDays: this.workingDays,
                        };
                        localStorage.setItem(STORAGE_KEY, JSON.stringify(payload));
                    } catch (_) {}
                },

                // Reset to default values
                resetDefaults() {
                    if (confirm('Reset all values to initial defaults?')) {
                        localStorage.removeItem(STORAGE_KEY);
                        location.reload();
                    }
                },

                // Theme switcher
                toggleTheme() {
                    this.isDark = !this.isDark;
                    if (this.isDark) {
                        document.documentElement.classList.add('dark');
                        localStorage.setItem('waldo_calc_theme', 'dark');
                    } else {
                        document.documentElement.classList.remove('dark');
                        localStorage.setItem('waldo_calc_theme', 'light');
                    }
                },

                // Days in Selected Month Calculation (handles leap years accurately)
                get daysInSelectedMonth() {
                    return new Date(this.selectedYear, this.selectedMonth + 1, 0).getDate();
                },

                // ─────────────────────────────────────────────────────────────
                // COMPUTED SALARY STRUCTURE
                // ─────────────────────────────────────────────────────────────
                // Basic Salary (60% of gross)
                get basicSalary() {
                    return this.round(Number(this.grossSalary || 0) * 0.60);
                },

                // Allowance (40% of gross)
                get allowance() {
                    return this.round(Number(this.grossSalary || 0) * 0.40);
                },

                // 11% SSF (11% of Basic)
                get ssf11Amount() {
                    return this.round(this.basicSalary * 0.11);
                },

                // 20% SSF (20% of Basic)
                get ssf20Amount() {
                    return this.round(this.basicSalary * 0.20);
                },

                // Total SSF (11% + 20%)
                get totalSsfAmount() {
                    return this.round(this.ssf11Amount + this.ssf20Amount);
                },

                // Per Day Value = Gross / Total Days of the month
                get perDay() {
                    const days = this.daysInSelectedMonth;
                    if (!days) return 0;
                    return this.round(Number(this.grossSalary || 0) / days);
                },

                // Per Hour Value = Per Day / 8
                get perHour() {
                    return this.round(this.perDay / 8);
                },

                // Normal OT Per Hour = Per Hour × 1.5
                get normalOtPerHour() {
                    return this.round(this.perHour * 1.5);
                },

                // Special OT Per Hour = Per Hour × 2.0
                get specialOtPerHour() {
                    return this.round(this.perHour * 2.0);
                },

                // ─────────────────────────────────────────────────────────────
                // COMPUTED OVERTIME
                // ─────────────────────────────────────────────────────────────
                get normalOtTotalAmount() {
                    return this.round(this.normalOtPerHour * Number(this.normalOtHours || 0));
                },

                get specialOtTotalAmount() {
                    return this.round(this.specialOtPerHour * Number(this.specialOtHours || 0));
                },

                get totalOtAmount() {
                    return this.round(this.normalOtTotalAmount + this.specialOtTotalAmount);
                },

                // ─────────────────────────────────────────────────────────────
                // COMPUTED ADJUSTMENTS
                // ─────────────────────────────────────────────────────────────
                get adjustmentDaysAmountAbsolute() {
                    return this.round(this.perDay * Number(this.adjustmentDays || 0));
                },

                get adjustmentDaysSignedAmount() {
                    return this.round(this.adjDaysSign * this.adjustmentDaysAmountAbsolute);
                },

                get adjustmentManualSignedAmount() {
                    return this.round(this.adjAmountSign * Number(this.adjustmentManualAmount || 0));
                },

                // Total Adjustments (Sum up manual adjustment + adjustment according to adj days)
                get totalAdjustments() {
                    return this.round(this.adjustmentDaysSignedAmount + this.adjustmentManualSignedAmount);
                },

                // ─────────────────────────────────────────────────────────────
                // COMPUTED FESTIVAL ALLOWANCE
                // ─────────────────────────────────────────────────────────────
                get festivalBasisSalary() {
                    return this.festivalBasis === 'basic' ? this.basicSalary : Number(this.grossSalary || 0);
                },

                get festivalAllowanceAmount() {
                    if (!this.includeFestival) return 0;
                    const days = Math.min(365, Math.max(0, Number(this.workingDays || 0)));
                    return this.round((this.festivalBasisSalary / 365) * days);
                },

                // Calculate working days from Date From & To
                calculateDaysFromDates() {
                    if (!this.festivalDateFrom || !this.festivalDateTo) return;
                    const from = new Date(this.festivalDateFrom);
                    const to = new Date(this.festivalDateTo);
                    if (isNaN(from.getTime()) || isNaN(to.getTime())) return;

                    const diffTime = to.getTime() - from.getTime();
                    let diffDays = Math.round(diffTime / (1000 * 3600 * 24)) + 1; // inclusive
                    if (diffDays < 0) diffDays = 0;
                    if (diffDays >= 365) diffDays = 365;

                    this.workingDays = diffDays;
                },

                // ─────────────────────────────────────────────────────────────
                // COMPUTED MASTER NET PAYABLE & CTC
                // ─────────────────────────────────────────────────────────────
                // Net Take Home = Gross + Total OT + Total Adjustments - 11% SSF + Festival Allowance (if included)
                get netPayable() {
                    let total = Number(this.grossSalary || 0)
                              + this.totalOtAmount
                              + this.totalAdjustments
                              - this.ssf11Amount;
                    if (this.includeFestival) {
                        total += this.festivalAllowanceAmount;
                    }
                    return this.round(Math.max(0, total));
                },

                // Cost to Company (CTC) = Gross + Total OT + Total Adjustments + 20% Employer SSF + Festival Allowance
                get totalCTC() {
                    let ctc = Number(this.grossSalary || 0)
                            + this.totalOtAmount
                            + this.totalAdjustments
                            + this.ssf20Amount;
                    if (this.includeFestival) {
                        ctc += this.festivalAllowanceAmount;
                    }
                    return this.round(Math.max(0, ctc));
                },

                // ─────────────────────────────────────────────────────────────
                // HELPER UTILITIES
                // ─────────────────────────────────────────────────────────────
                round(num) {
                    return Math.round((Number(num) + Number.EPSILON) * 100) / 100;
                },

                formatNumber(val) {
                    const num = Number(val || 0);
                    return num.toLocaleString('en-IN', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                },

                // Copy summary breakdown to clipboard
                async copySummary() {
                    const lines = [
                        `═══════════════════════════════════════════`,
                        ` WALDO HQ — SALARY & OT SIMULATION REPORT`,
                        ` Period: ${this.months[this.selectedMonth]} ${this.selectedYear} (${this.daysInSelectedMonth} Days)`,
                        `═══════════════════════════════════════════`,
                        `Gross Salary: Rs. ${this.formatNumber(this.grossSalary)}`,
                        `• Basic Salary (60%): Rs. ${this.formatNumber(this.basicSalary)}`,
                        `• Allowance (40%): Rs. ${this.formatNumber(this.allowance)}`,
                        `• Daily Rate: Rs. ${this.formatNumber(this.perDay)}`,
                        `• Hourly Rate: Rs. ${this.formatNumber(this.perHour)}`,
                        ``,
                        `Overtime:`,
                        `• Normal OT (${this.normalOtHours || 0} hrs @ 1.5x): Rs. ${this.formatNumber(this.normalOtTotalAmount)}`,
                        `• Special OT (${this.specialOtHours || 0} hrs @ 2.0x): Rs. ${this.formatNumber(this.specialOtTotalAmount)}`,
                        `• Total OT Amount: Rs. ${this.formatNumber(this.totalOtAmount)}`,
                        ``,
                        `Adjustments:`,
                        `• Prorated Days Adj: ${this.adjDaysSign === 1 ? '+' : '-'}Rs. ${this.formatNumber(this.adjustmentDaysAmountAbsolute)}`,
                        `• Manual Adjustment: ${this.adjAmountSign === 1 ? '+' : '-'}Rs. ${this.formatNumber(this.adjustmentManualAmount || 0)}`,
                        `• Total Adjustments: ${this.totalAdjustments >= 0 ? '+' : '-'}Rs. ${this.formatNumber(Math.abs(this.totalAdjustments))}`,
                        ``,
                        this.includeFestival ? `Festival Allowance (${this.workingDays} days on ${this.festivalBasis}): Rs. ${this.formatNumber(this.festivalAllowanceAmount)}\n` : '',
                        `Social Security Fund (SSF):`,
                        `• Employee Contribution (11%): -Rs. ${this.formatNumber(this.ssf11Amount)}`,
                        `• Employer Contribution (20%): Rs. ${this.formatNumber(this.ssf20Amount)}`,
                        `• Total SSF Deposit: Rs. ${this.formatNumber(this.totalSsfAmount)}`,
                        `═══════════════════════════════════════════`,
                        `ESTIMATED NET TAKE-HOME: Rs. ${this.formatNumber(this.netPayable)}`,
                        `TOTAL COST TO COMPANY (CTC): Rs. ${this.formatNumber(this.totalCTC)}`,
                        `═══════════════════════════════════════════`,
                    ].filter(Boolean).join('\n');

                    try {
                        await navigator.clipboard.writeText(lines);
                        this.copied = true;
                        setTimeout(() => this.copied = false, 2500);
                    } catch (err) {
                        alert('Could not copy automatically. Please copy manually.');
                    }
                }
            };
        }
    </script>
</body>
</html>
