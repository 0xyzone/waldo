<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full scroll-smooth dark">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salary & OT Live Calculator — Waldo</title>
    <meta name="description" content="Waldo Salary, Overtime, SSF, Adjustments, and Festival Allowance calculator with real-time live calculations.">
    <x-favicon />

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Styles & Scripts from Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- AlpineJS -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- FontAwesome 6 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        /* ═══════════════════════════════════════════════════
           BRIGHT ORANGE & FRESH FUN DARK THEME
           Easy on the eyes: Slate-dark canvas + electric orange pops
        ═══════════════════════════════════════════════════ */
        :root {
            --brand-orange: #ff6b00;
            --brand-orange-bright: #ff8533;
            --brand-amber: #f59e0b;
        }

        body {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            background-color: #0b0f17;
            color: #e2e8f0;
        }

        .font-mono-numbers {
            font-family: 'JetBrains Mono', monospace;
            font-feature-settings: "tnum" 1, "zero" 1;
        }

        /* Airy Glassmorphism with crisp contrast */
        .glass-panel {
            background: rgba(15, 23, 42, 0.65);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.35);
        }

        .glass-panel-hero {
            background: linear-gradient(135deg, rgba(30, 41, 59, 0.75) 0%, rgba(15, 23, 42, 0.85) 100%);
            backdrop-filter: blur(24px);
            border: 1px solid rgba(255, 107, 0, 0.25);
            box-shadow: 0 16px 40px -8px rgba(0, 0, 0, 0.5), 0 0 25px -5px rgba(255, 107, 0, 0.15);
        }

        .interactive-card {
            transition: all 0.22s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .interactive-card:hover {
            transform: translateY(-2px);
            border-color: rgba(255, 107, 0, 0.35);
            box-shadow: 0 12px 28px -6px rgba(0, 0, 0, 0.4), 0 0 18px -4px rgba(255, 107, 0, 0.2);
        }

        /* Ambient subtle bloom */
        .ambient-glow {
            position: absolute;
            border-radius: 9999px;
            filter: blur(120px);
            pointer-events: none;
            opacity: 0.35;
            z-index: 0;
        }

        /* ═══════════════════════════════════════════════════
           A4 OFFICIAL PRINT SLIP STYLES
        ═══════════════════════════════════════════════════ */
        @media print {
            @page {
                size: A4 portrait;
                margin: 8mm 10mm 8mm 10mm;
            }

            html, body {
                background: #ffffff !important;
                color: #000000 !important;
                font-family: Arial, Helvetica, sans-serif !important;
                font-size: 11px !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            .no-print {
                display: none !important;
            }

            #screen-app {
                display: none !important;
            }

            #print-slip {
                display: block !important;
                width: 100% !important;
                max-width: 190mm !important;
                margin: 0 auto !important;
                box-shadow: none !important;
                border: none !important;
                padding: 0 !important;
            }

            .print-table {
                width: 100%;
                border-collapse: collapse;
            }

            .print-table th, .print-table td {
                border: 1px solid #111827;
                padding: 4.5px 7px;
                font-size: 10.5px;
            }

            .print-table th {
                background-color: #f3f4f6 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                font-weight: bold;
                text-align: left;
            }
        }
    </style>
</head>

<body class="h-full min-h-screen bg-[#0b0f17] text-slate-100 antialiased selection:bg-orange-500 selection:text-white relative overflow-x-hidden"
      x-data="salaryCalculator()">

    <!-- Ambient Glowing Bloom Orbs (Soft, pleasant, non-fatiguing) -->
    <div class="no-print ambient-glow w-[550px] h-[550px] bg-orange-500/20 top-[-100px] left-[-60px]"></div>
    <div class="no-print ambient-glow w-[500px] h-[500px] bg-amber-500/15 top-[20%] right-[-80px]"></div>
    <div class="no-print ambient-glow w-[450px] h-[450px] bg-orange-600/15 bottom-[-80px] left-[35%]"></div>

    <!-- ═══════════════════════════════════════════════════════════════
         INTERACTIVE SCREEN APPLICATION (Hidden when printing)
    ═══════════════════════════════════════════════════════════════ -->
    <div id="screen-app" class="relative z-10 min-h-screen flex flex-col justify-between">

        <!-- Top Navigation Bar -->
        <header class="no-print sticky top-0 z-40 border-b border-white/[0.08] bg-[#0b0f17]/85 backdrop-blur-md px-4 sm:px-6 lg:px-8 py-2.5 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <a href="/kamkaj" class="flex items-center gap-2.5 group text-slate-300 hover:text-orange-400 transition-colors">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-orange-500 via-amber-500 to-orange-600 flex items-center justify-center text-white shadow-lg shadow-orange-500/25 group-hover:scale-105 transition-transform">
                        <i class="fa-solid fa-calculator text-base"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="text-[11px] uppercase tracking-wider font-extrabold text-orange-400">Waldo HQ</span>
                            <span class="text-[10px] bg-orange-500/15 text-orange-300 px-2 py-0.5 rounded-full font-bold border border-orange-500/30">Finance Studio</span>
                        </div>
                        <h1 class="text-sm sm:text-base font-extrabold tracking-tight text-white flex items-center gap-1.5">
                            Salary & OT Simulator <span class="text-xs">⚡</span>
                        </h1>
                    </div>
                </a>
            </div>

            <div class="flex items-center gap-2">
                <!-- Status Pill -->
                <div class="hidden sm:flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-semibold bg-slate-800/80 text-emerald-400 border border-slate-700">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                    Live LocalStorage Synced
                </div>

                <!-- Print Slip Button (Triggers A4 Format) -->
                <button @click="triggerPrint()"
                        type="button"
                        class="px-3.5 py-1.5 rounded-xl text-xs font-bold bg-gradient-to-r from-orange-500 via-orange-600 to-amber-500 hover:brightness-110 text-white shadow-md shadow-orange-500/25 transition-all flex items-center gap-1.5 active:scale-95 cursor-pointer"
                        title="Print official A4 sized slip">
                    <i class="fa-solid fa-print"></i>
                    <span>Print Slip</span>
                </button>

                <!-- Copy Summary Button -->
                <button @click="copySummary()"
                        type="button"
                        class="px-3 py-1.5 rounded-xl text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 transition-all flex items-center gap-1.5 active:scale-95 cursor-pointer">
                    <i class="fa-regular fa-copy text-orange-400"></i>
                    <span x-text="copied ? 'Copied!' : 'Copy'"></span>
                </button>

                <!-- Reset Inputs -->
                <button @click="resetDefaults()"
                        type="button"
                        class="px-2.5 py-1.5 rounded-xl text-xs font-medium text-slate-400 hover:text-orange-400 hover:bg-orange-500/10 transition-all flex items-center gap-1 cursor-pointer"
                        title="Reset inputs to initial default values">
                    <i class="fa-solid fa-rotate-left"></i>
                    <span class="hidden md:inline">Reset</span>
                </button>

                <!-- Back to Kamkaj -->
                <a href="/kamkaj"
                   class="ml-1 px-3 py-1.5 rounded-xl text-xs font-semibold bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 transition-all flex items-center gap-1.5 active:scale-95">
                    <i class="fa-solid fa-arrow-left"></i>
                    <span class="hidden lg:inline">Panel</span>
                </a>
            </div>
        </header>

        <!-- Main Content Area: Wide & Compact (Max Width 1760px, pleasant balanced layout) -->
        <main class="flex-1 max-w-[1760px] w-full mx-auto px-3 sm:px-5 lg:px-7 py-4 space-y-4">

            <!-- Fresh Cheerful Banner with Live Hero Figures -->
            <div class="relative overflow-hidden rounded-2xl p-4 sm:p-5 border border-orange-500/30 bg-gradient-to-r from-slate-900 via-[#131b2e] to-[#1a1528] text-white shadow-xl">
                <div class="absolute -right-10 -bottom-10 w-60 h-60 bg-orange-500/20 rounded-full blur-3xl pointer-events-none"></div>
                <div class="absolute -left-10 -top-10 w-60 h-60 bg-amber-500/15 rounded-full blur-3xl pointer-events-none"></div>

                <div class="relative z-10 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                    <div>
                        <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-orange-500/20 border border-orange-500/35 text-orange-300 mb-1.5">
                            <span>✨</span> Live Dynamic Payroll Simulation Engine
                        </div>
                        <h2 class="text-xl sm:text-2xl font-black tracking-tight flex items-center gap-2">
                            Salary & Benefits Calculator
                        </h2>
                        <p class="text-slate-400 text-xs mt-0.5 max-w-xl">
                            Instant breakdown of basic pay (60%), allowances (40%), SSF contributions (11% / 20%), normal & special overtime, prorated days, and festival allowance.
                        </p>
                    </div>

                    <!-- Live KPI Cards -->
                    <div class="w-full md:w-auto flex-shrink-0 flex items-center gap-3">
                        <div class="bg-slate-900/90 backdrop-blur-xl border border-orange-500/40 rounded-xl px-4 py-2.5 flex flex-col justify-center shadow-lg">
                            <span class="text-[10px] uppercase tracking-wider text-orange-400 font-extrabold flex items-center gap-1">
                                <span>💵</span> Employee Net Take-Home
                            </span>
                            <div class="text-xl sm:text-2xl font-black font-mono-numbers text-orange-400 flex items-baseline gap-1 mt-0.5">
                                <span class="text-xs text-orange-300/80 font-sans">Rs.</span>
                                <span x-text="formatNumber(netPayable)">0.00</span>
                            </div>
                        </div>

                        <div class="bg-slate-900/90 backdrop-blur-xl border border-slate-700 rounded-xl px-4 py-2.5 flex flex-col justify-center shadow-lg">
                            <span class="text-[10px] uppercase tracking-wider text-slate-400 font-extrabold flex items-center gap-1">
                                <span>🏢</span> Company Outlay (CTC)
                            </span>
                            <div class="text-xl sm:text-2xl font-black font-mono-numbers text-white flex items-baseline gap-1 mt-0.5">
                                <span class="text-xs text-slate-400 font-sans">Rs.</span>
                                <span x-text="formatNumber(totalCTC)">0.00</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Two-Column Grid: Left Inputs (5 Cols) and Right Outputs (7 Cols) -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 lg:gap-5 items-start">

                <!-- ═══════════════════════════════════════════════════════════════
                     LEFT COLUMN: USER INPUTS (5 Cols on LG)
                ═══════════════════════════════════════════════════════════════ -->
                <div class="lg:col-span-5 space-y-4">

                    <!-- Section 1: Gross Salary & Month/Year -->
                    <div class="glass-panel interactive-card rounded-2xl p-4 sm:p-5 space-y-3.5">
                        <div class="flex items-center justify-between pb-2 border-b border-white/[0.08]">
                            <div class="flex items-center gap-2">
                                <span class="w-7 h-7 rounded-lg bg-orange-500/20 text-orange-400 flex items-center justify-center font-bold text-xs">
                                    <i class="fa-solid fa-money-bill-wave"></i>
                                </span>
                                <div>
                                    <h3 class="font-bold text-white text-sm">Gross Salary & Period</h3>
                                </div>
                            </div>
                            <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-orange-500/15 text-orange-400 font-semibold border border-orange-500/30">
                                Step 1
                            </span>
                        </div>

                        <!-- Gross Salary Input -->
                        <div class="space-y-1.5">
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-300">
                                Gross Remuneration (NPR) <span class="text-orange-400">*</span>
                            </label>
                            <div class="relative rounded-xl shadow-sm">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-orange-400 font-bold font-mono text-sm">
                                    Rs.
                                </div>
                                <input type="number"
                                       step="any"
                                       min="0"
                                       x-model.number="grossSalary"
                                       @input="saveState()"
                                       placeholder="e.g. 25000"
                                       class="block w-full rounded-xl border-0 py-2.5 pl-11 pr-10 text-white bg-slate-950/70 ring-1 ring-inset ring-slate-700 placeholder:text-slate-500 focus:ring-2 focus:ring-inset focus:ring-orange-500 font-mono-numbers text-lg font-bold transition-all">
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                    <button type="button"
                                            @click="grossSalary = 0; saveState()"
                                            x-show="grossSalary > 0"
                                            class="text-slate-400 hover:text-white text-xs p-1 cursor-pointer">
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Fun Quick Preset Buttons -->
                            <div class="pt-1">
                                <div class="text-[10px] uppercase font-bold text-slate-400 mb-1.5 flex items-center gap-1">
                                    <span class="text-orange-400">⚡</span> Quick Salary Presets:
                                </div>
                                <div class="flex flex-wrap items-center gap-1.5">
                                    <template x-for="preset in [19550, 20000, 22000, 23000, 25000, 30000, 35000, 40000, 50000]" :key="preset">
                                        <button type="button"
                                                @click="grossSalary = preset; saveState()"
                                                class="px-2.5 py-1 text-[11px] font-mono font-bold rounded-lg transition-all border cursor-pointer active:scale-95"
                                                :class="grossSalary === preset ? 'bg-orange-500 text-white border-orange-400 shadow-md shadow-orange-500/30' : 'bg-slate-800/80 hover:bg-slate-700 text-slate-300 hover:text-white border-slate-700'"
                                                x-text="preset === 19550 ? '19,550' : (preset/1000) + 'k'">
                                        </button>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <!-- Month & Year Selector -->
                        <div class="grid grid-cols-2 gap-2.5 pt-1">
                            <div class="space-y-1">
                                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-300">
                                    Month
                                </label>
                                <select x-model.number="selectedMonth"
                                        @change="saveState()"
                                        class="block w-full rounded-xl border-0 py-2 px-2.5 text-xs text-white bg-slate-950/70 ring-1 ring-inset ring-slate-700 focus:ring-2 focus:ring-orange-500 font-semibold cursor-pointer">
                                    <template x-for="(mName, idx) in months" :key="idx">
                                        <option :value="idx" :selected="selectedMonth === idx" x-text="mName" class="bg-slate-900 text-white"></option>
                                    </template>
                                </select>
                            </div>

                            <div class="space-y-1">
                                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-300">
                                    Year
                                </label>
                                <select x-model.number="selectedYear"
                                        @change="saveState()"
                                        class="block w-full rounded-xl border-0 py-2 px-2.5 text-xs text-white bg-slate-950/70 ring-1 ring-inset ring-slate-700 focus:ring-2 focus:ring-orange-500 font-semibold cursor-pointer">
                                    <template x-for="yr in yearOptions" :key="yr">
                                        <option :value="yr" :selected="selectedYear === yr" x-text="yr" class="bg-slate-900 text-white"></option>
                                    </template>
                                </select>
                            </div>
                        </div>

                        <!-- Days in Month Indicator Pill -->
                        <div class="flex items-center justify-between px-3 py-1.5 rounded-xl bg-slate-800/60 border border-slate-700 text-xs">
                            <span class="text-slate-300 font-medium flex items-center gap-1.5">
                                <i class="fa-regular fa-calendar-days text-orange-400"></i>
                                Days in <strong class="text-white" x-text="months[selectedMonth] + ' ' + selectedYear"></strong>:
                            </span>
                            <span class="font-mono font-bold text-orange-300 bg-orange-500/15 px-2 py-0.5 rounded border border-orange-500/30"
                                  x-text="daysInSelectedMonth + ' Days'">
                            </span>
                        </div>
                    </div>

                    <!-- Section 2: Overtime Hours (Normal & Special) -->
                    <div class="glass-panel interactive-card rounded-2xl p-4 sm:p-5 space-y-3">
                        <div class="flex items-center justify-between pb-2 border-b border-white/[0.08]">
                            <div class="flex items-center gap-2">
                                <span class="w-7 h-7 rounded-lg bg-orange-500/20 text-orange-400 flex items-center justify-center font-bold text-xs">
                                    <i class="fa-solid fa-clock"></i>
                                </span>
                                <div>
                                    <h3 class="font-bold text-white text-sm">Overtime Hours</h3>
                                </div>
                            </div>
                            <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-orange-500/15 text-orange-400 font-semibold border border-orange-500/30">
                                Step 2
                            </span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <!-- Normal OT Hours (x1.5) -->
                            <div class="p-3 rounded-xl bg-slate-900/80 border border-slate-800 space-y-2">
                                <div class="flex items-center justify-between">
                                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-300">
                                        Normal OT
                                    </label>
                                    <span class="text-[10px] font-bold px-1.5 py-0.2 rounded bg-orange-500/20 text-orange-300">
                                        × 1.5 Rate
                                    </span>
                                </div>
                                <input type="number"
                                       step="any"
                                       min="0"
                                       x-model.number="normalOtHours"
                                       @input="saveState()"
                                       placeholder="0"
                                       class="block w-full rounded-lg border-0 py-1.5 px-2.5 text-white bg-slate-950/70 ring-1 ring-inset ring-slate-700 font-mono-numbers text-base font-bold focus:ring-2 focus:ring-orange-500">
                                <!-- Steppers -->
                                <div class="flex items-center justify-between gap-1 pt-0.5">
                                    <div class="flex gap-1">
                                        <button type="button" @click="normalOtHours = Math.max(0, (normalOtHours || 0) - 1); saveState()"
                                                class="w-6 h-6 rounded bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold border border-slate-700 flex items-center justify-center cursor-pointer">
                                            -1
                                        </button>
                                        <button type="button" @click="normalOtHours = (normalOtHours || 0) + 1; saveState()"
                                                class="w-6 h-6 rounded bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold border border-slate-700 flex items-center justify-center cursor-pointer">
                                            +1
                                        </button>
                                    </div>
                                    <div class="flex gap-1">
                                        <button type="button" @click="normalOtHours = (normalOtHours || 0) + 4; saveState()"
                                                class="px-1.5 h-6 rounded bg-orange-500/20 hover:bg-orange-500/30 text-orange-300 text-[10px] font-bold border border-orange-500/30 flex items-center justify-center cursor-pointer">
                                            +4h
                                        </button>
                                        <button type="button" @click="normalOtHours = (normalOtHours || 0) + 8; saveState()"
                                                class="px-1.5 h-6 rounded bg-orange-500/20 hover:bg-orange-500/30 text-orange-300 text-[10px] font-bold border border-orange-500/30 flex items-center justify-center cursor-pointer">
                                            +8h
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Special OT Hours (x2.0) -->
                            <div class="p-3 rounded-xl bg-slate-900/80 border border-slate-800 space-y-2">
                                <div class="flex items-center justify-between">
                                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-300">
                                        Special OT
                                    </label>
                                    <span class="text-[10px] font-bold px-1.5 py-0.2 rounded bg-amber-500/20 text-amber-300">
                                        × 2.0 Rate
                                    </span>
                                </div>
                                <input type="number"
                                       step="any"
                                       min="0"
                                       x-model.number="specialOtHours"
                                       @input="saveState()"
                                       placeholder="0"
                                       class="block w-full rounded-lg border-0 py-1.5 px-2.5 text-white bg-slate-950/70 ring-1 ring-inset ring-slate-700 font-mono-numbers text-base font-bold focus:ring-2 focus:ring-orange-500">
                                <!-- Steppers -->
                                <div class="flex items-center justify-between gap-1 pt-0.5">
                                    <div class="flex gap-1">
                                        <button type="button" @click="specialOtHours = Math.max(0, (specialOtHours || 0) - 1); saveState()"
                                                class="w-6 h-6 rounded bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold border border-slate-700 flex items-center justify-center cursor-pointer">
                                            -1
                                        </button>
                                        <button type="button" @click="specialOtHours = (specialOtHours || 0) + 1; saveState()"
                                                class="w-6 h-6 rounded bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-bold border border-slate-700 flex items-center justify-center cursor-pointer">
                                            +1
                                        </button>
                                    </div>
                                    <div class="flex gap-1">
                                        <button type="button" @click="specialOtHours = (specialOtHours || 0) + 4; saveState()"
                                                class="px-1.5 h-6 rounded bg-amber-500/20 hover:bg-amber-500/30 text-amber-300 text-[10px] font-bold border border-amber-500/30 flex items-center justify-center cursor-pointer">
                                            +4h
                                        </button>
                                        <button type="button" @click="specialOtHours = (specialOtHours || 0) + 8; saveState()"
                                                class="px-1.5 h-6 rounded bg-amber-500/20 hover:bg-amber-500/30 text-amber-300 text-[10px] font-bold border border-amber-500/30 flex items-center justify-center cursor-pointer">
                                            +8h
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section 3: Adjustments (Days & Manual Amount) -->
                    <div class="glass-panel interactive-card rounded-2xl p-4 sm:p-5 space-y-3">
                        <div class="flex items-center justify-between pb-2 border-b border-white/[0.08]">
                            <div class="flex items-center gap-2">
                                <span class="w-7 h-7 rounded-lg bg-orange-500/20 text-orange-400 flex items-center justify-center font-bold text-xs">
                                    <i class="fa-solid fa-sliders"></i>
                                </span>
                                <div>
                                    <h3 class="font-bold text-white text-sm">Adjustments</h3>
                                </div>
                            </div>
                            <span class="text-[10px] font-mono px-2 py-0.5 rounded bg-orange-500/15 text-orange-400 font-semibold border border-orange-500/30">
                                Step 3
                            </span>
                        </div>

                        <!-- Adjustment Days -->
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between">
                                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-300">
                                    Adjustment Days
                                </label>
                                <div class="flex items-center gap-1 bg-slate-900 p-0.5 rounded-lg border border-slate-700 text-[10px]">
                                    <button type="button"
                                            @click="adjDaysSign = 1; saveState()"
                                            class="px-2 py-0.5 rounded font-bold transition-all cursor-pointer"
                                            :class="adjDaysSign === 1 ? 'bg-orange-500 text-white shadow-sm' : 'text-slate-400 hover:text-white'">
                                        + Add
                                    </button>
                                    <button type="button"
                                            @click="adjDaysSign = -1; saveState()"
                                            class="px-2 py-0.5 rounded font-bold transition-all cursor-pointer"
                                            :class="adjDaysSign === -1 ? 'bg-rose-600 text-white shadow-sm' : 'text-slate-400 hover:text-white'">
                                        - Deduct
                                    </button>
                                </div>
                            </div>
                            <input type="number"
                                   step="any"
                                   min="0"
                                   x-model.number="adjustmentDays"
                                   @input="saveState()"
                                   placeholder="0"
                                   class="block w-full rounded-xl border-0 py-2 px-3 text-white bg-slate-950/70 ring-1 ring-inset ring-slate-700 font-mono-numbers text-base font-bold focus:ring-2 focus:ring-orange-500">
                            <div class="text-[10.5px] text-slate-400 flex items-center justify-between pt-0.5">
                                <span>Prorated @ Rs. <span x-text="formatNumber(perDay)"></span>/day:</span>
                                <span class="font-mono font-bold"
                                      :class="adjDaysSign === 1 ? 'text-orange-400' : 'text-rose-400'">
                                    <span x-text="adjDaysSign === 1 ? '+' : '-'"></span> Rs. <span x-text="formatNumber(adjustmentDaysAmountAbsolute)">0.00</span>
                                </span>
                            </div>
                        </div>

                        <!-- Manual Adjustment Amount -->
                        <div class="space-y-1.5 pt-2 border-t border-white/[0.08]">
                            <div class="flex items-center justify-between">
                                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-300">
                                    Manual Adjustment Amount
                                </label>
                                <div class="flex items-center gap-1 bg-slate-900 p-0.5 rounded-lg border border-slate-700 text-[10px]">
                                    <button type="button"
                                            @click="adjAmountSign = 1; saveState()"
                                            class="px-2 py-0.5 rounded font-bold transition-all cursor-pointer"
                                            :class="adjAmountSign === 1 ? 'bg-orange-500 text-white shadow-sm' : 'text-slate-400 hover:text-white'">
                                        + Add
                                    </button>
                                    <button type="button"
                                            @click="adjAmountSign = -1; saveState()"
                                            class="px-2 py-0.5 rounded font-bold transition-all cursor-pointer"
                                            :class="adjAmountSign === -1 ? 'bg-rose-600 text-white shadow-sm' : 'text-slate-400 hover:text-white'">
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
                                       class="block w-full rounded-xl border-0 py-2 pl-9 pr-3 text-white bg-slate-950/70 ring-1 ring-inset ring-slate-700 font-mono-numbers text-base font-bold focus:ring-2 focus:ring-orange-500">
                            </div>
                        </div>
                    </div>

                    <!-- Section 4: Festival Allowance Inputs & Date Range -->
                    <div class="glass-panel interactive-card rounded-2xl p-4 sm:p-5 space-y-3">
                        <div class="flex items-center justify-between pb-2 border-b border-white/[0.08]">
                            <div class="flex items-center gap-2">
                                <span class="w-7 h-7 rounded-lg bg-orange-500/20 text-orange-400 flex items-center justify-center font-bold text-xs">
                                    <i class="fa-solid fa-gift"></i>
                                </span>
                                <div>
                                    <h3 class="font-bold text-white text-sm">Festival Allowance</h3>
                                </div>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" x-model="includeFestival" @change="saveState()" class="sr-only peer">
                                <div class="w-9 h-5 bg-slate-800 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all peer-checked:bg-orange-500"></div>
                            </label>
                        </div>

                        <div x-show="includeFestival" x-transition.opacity.duration.200ms class="space-y-3">
                            <!-- Calculation Basis Radio Option (Gross vs Basic) -->
                            <div class="space-y-1">
                                <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-300">
                                    Calculation Basis
                                </label>
                                <div class="grid grid-cols-2 gap-2 bg-slate-950/70 p-1 rounded-xl border border-slate-800">
                                    <button type="button"
                                            @click="festivalBasis = 'gross'; saveState()"
                                            class="py-1.5 px-2 rounded-lg text-xs font-bold flex items-center justify-center gap-1.5 transition-all cursor-pointer"
                                            :class="festivalBasis === 'gross' ? 'bg-orange-500 text-white shadow-md' : 'text-slate-400 hover:text-white'">
                                        <i class="fa-solid fa-coins"></i>
                                        Gross Salary
                                    </button>
                                    <button type="button"
                                            @click="festivalBasis = 'basic'; saveState()"
                                            class="py-1.5 px-2 rounded-lg text-xs font-bold flex items-center justify-center gap-1.5 transition-all cursor-pointer"
                                            :class="festivalBasis === 'basic' ? 'bg-orange-500 text-white shadow-md' : 'text-slate-400 hover:text-white'">
                                        <i class="fa-solid fa-layer-group"></i>
                                        Basic (60%)
                                    </button>
                                </div>
                            </div>

                            <!-- Date Range Selector (From & To) -->
                            <div class="grid grid-cols-2 gap-2.5 pt-0.5">
                                <div class="space-y-1">
                                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-300">
                                        From Date
                                    </label>
                                    <input type="date"
                                           x-model="festivalDateFrom"
                                           @change="calculateDaysFromDates(); saveState()"
                                           class="block w-full rounded-xl border-0 py-1.5 px-2.5 text-xs text-white bg-slate-950/70 ring-1 ring-inset ring-slate-700 focus:ring-2 focus:ring-orange-500 font-mono cursor-pointer">
                                </div>
                                <div class="space-y-1">
                                    <label class="block text-[10px] font-bold uppercase tracking-wider text-slate-300">
                                        To Date (End Date)
                                    </label>
                                    <input type="date"
                                           x-model="festivalDateTo"
                                           @change="calculateDaysFromDates(); saveState()"
                                           class="block w-full rounded-xl border-0 py-1.5 px-2.5 text-xs text-white bg-slate-950/70 ring-1 ring-inset ring-slate-700 focus:ring-2 focus:ring-orange-500 font-mono cursor-pointer">
                                </div>
                            </div>

                            <!-- Working Days Input -->
                            <div class="space-y-1">
                                <div class="flex items-center justify-between">
                                    <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-300">
                                        Total Working Days
                                    </label>
                                    <span class="text-[10px] text-orange-400 font-semibold"
                                          x-show="workingDays >= 365">
                                        <i class="fa-solid fa-circle-check"></i> Capped at 365 days
                                    </span>
                                </div>
                                <input type="number"
                                       step="1"
                                       min="0"
                                       max="365"
                                       x-model.number="workingDays"
                                       @input="if(workingDays > 365) workingDays = 365; saveState()"
                                       placeholder="365"
                                       class="block w-full rounded-xl border-0 py-2 px-3 text-white bg-slate-950/70 ring-1 ring-inset ring-slate-700 font-mono-numbers text-base font-bold focus:ring-2 focus:ring-orange-500">
                                <p class="text-[10px] text-slate-400 italic">
                                    Formula: ((<span x-text="festivalBasis === 'gross' ? 'Gross' : 'Basic'"></span>) / 365) × <span x-text="workingDays"></span> working days
                                </p>
                            </div>
                        </div>

                        <div x-show="!includeFestival" class="text-[11px] text-slate-400 text-center py-1.5 italic">
                            Toggle ON to compute Festival Allowance in breakdown.
                        </div>
                    </div>

                </div>

                <!-- ═══════════════════════════════════════════════════════════════
                     RIGHT COLUMN: LIVE VISUAL CALCULATIONS & BREAKDOWN (7 Cols on LG)
                ═══════════════════════════════════════════════════════════════ -->
                <div class="lg:col-span-7 space-y-4">

                    <!-- High-Impact Executive Paycard Summary -->
                    <div class="glass-panel-hero interactive-card rounded-2xl p-5 sm:p-6 shadow-2xl relative overflow-hidden">
                        <!-- Top Accent Bar -->
                        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-orange-500 via-amber-400 to-orange-600"></div>

                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3.5 border-b border-white/[0.08]">
                            <div>
                                <span class="text-[10px] uppercase tracking-wider font-extrabold text-orange-400 flex items-center gap-1.5">
                                    <i class="fa-solid fa-receipt"></i>
                                    Waldo Executive Payroll Statement
                                </span>
                                <h3 class="text-lg sm:text-xl font-extrabold text-white mt-0.5">
                                    Net Remuneration Summary
                                </h3>
                            </div>
                            <div class="text-right">
                                <span class="text-[10px] font-semibold text-slate-400 block uppercase">Period</span>
                                <span class="font-bold text-xs text-orange-300" x-text="months[selectedMonth] + ' ' + selectedYear"></span>
                            </div>
                        </div>

                        <!-- Main Take Home Display -->
                        <div class="py-4 px-4 my-3 bg-slate-950/70 rounded-xl border border-orange-500/30 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 shadow-inner">
                            <div>
                                <span class="text-[11px] uppercase tracking-wider font-bold text-slate-400">Employee Net Take-Home</span>
                                <div class="text-2xl sm:text-3xl font-black font-mono-numbers text-orange-400 mt-0.5 flex items-baseline gap-1.5">
                                    <span class="text-base text-orange-300 font-sans">NPR</span>
                                    <span x-text="formatNumber(netPayable)">0.00</span>
                                </div>
                            </div>
                            <div class="sm:text-right space-y-0.5">
                                <div class="text-[11px] font-semibold text-slate-400">Total Company Outlay (CTC)</div>
                                <div class="text-lg font-bold font-mono-numbers text-white">
                                    Rs. <span x-text="formatNumber(totalCTC)">0.00</span>
                                </div>
                            </div>
                        </div>

                        <!-- Line Items Table -->
                        <div class="space-y-1.5 text-xs">
                            <!-- Gross Base -->
                            <div class="flex items-center justify-between py-1 border-b border-white/[0.05]">
                                <span class="text-slate-300 flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-orange-400"></span>
                                    Gross Salary Base
                                </span>
                                <span class="font-mono font-semibold text-white">
                                    Rs. <span x-text="formatNumber(grossSalary)">0.00</span>
                                </span>
                            </div>

                            <!-- Overtime Total -->
                            <div class="flex items-center justify-between py-1 border-b border-white/[0.05]">
                                <span class="text-slate-300 flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-amber-400"></span>
                                    Total Overtime (OT Normal + Special)
                                </span>
                                <span class="font-mono font-semibold text-orange-400">
                                    + Rs. <span x-text="formatNumber(totalOtAmount)">0.00</span>
                                </span>
                            </div>

                            <!-- Total Adjustments -->
                            <div class="flex items-center justify-between py-1 border-b border-white/[0.05]">
                                <span class="text-slate-300 flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                                    Total Adjustments (Days + Manual)
                                </span>
                                <span class="font-mono font-semibold"
                                      :class="totalAdjustments >= 0 ? 'text-emerald-400' : 'text-rose-400'">
                                    <span x-text="totalAdjustments >= 0 ? '+' : '-'"></span> Rs. <span x-text="formatNumber(Math.abs(totalAdjustments))">0.00</span>
                                </span>
                            </div>

                            <!-- Festival Allowance (if included) -->
                            <template x-if="includeFestival">
                                <div class="flex items-center justify-between py-1 border-b border-white/[0.05]">
                                    <span class="text-slate-300 flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full bg-orange-400"></span>
                                        Festival Allowance (<span x-text="workingDays"></span> working days)
                                    </span>
                                    <span class="font-mono font-semibold text-orange-400">
                                        + Rs. <span x-text="formatNumber(festivalAllowanceAmount)">0.00</span>
                                    </span>
                                </div>
                            </template>

                            <!-- Employee SSF Deduction -->
                            <div class="flex items-center justify-between py-1 border-b border-white/[0.05]">
                                <span class="text-slate-300 flex items-center gap-2">
                                    <span class="w-2 h-2 rounded-full bg-rose-400"></span>
                                    Employee SSF Contribution (11% of Basic)
                                </span>
                                <span class="font-mono font-semibold text-rose-400">
                                    - Rs. <span x-text="formatNumber(ssf11Amount)">0.00</span>
                                </span>
                            </div>

                            <!-- Employer SSF Contribution (informational) -->
                            <div class="flex items-center justify-between py-0.5 text-slate-400 text-[11px]">
                                <span class="flex items-center gap-2">
                                    <i class="fa-solid fa-building-columns text-[10px] text-orange-400"></i>
                                    Employer SSF Deposit (20% of Basic)
                                </span>
                                <span class="font-mono font-semibold text-slate-300">
                                    Rs. <span x-text="formatNumber(ssf20Amount)">0.00</span>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Card 1: Salary Structure & Daily/Hourly Rates -->
                    <div class="glass-panel interactive-card rounded-2xl p-4 sm:p-5 space-y-3.5">
                        <div class="flex items-center justify-between pb-2 border-b border-white/[0.08]">
                            <div class="flex items-center gap-2">
                                <span class="w-7 h-7 rounded-lg bg-orange-500/20 text-orange-400 flex items-center justify-center font-bold text-xs">
                                    <i class="fa-solid fa-chart-pie"></i>
                                </span>
                                <div>
                                    <h4 class="font-bold text-white text-sm">Salary Structure Breakdown</h4>
                                </div>
                            </div>
                            <span class="text-[10px] text-slate-400">60% Basic / 40% Allowance</span>
                        </div>

                        <!-- Progress visual split -->
                        <div class="space-y-1">
                            <div class="flex items-center justify-between text-[11px] font-semibold">
                                <span class="text-orange-400">Basic Salary (60%)</span>
                                <span class="text-amber-300">Allowance (40%)</span>
                            </div>
                            <div class="h-2.5 w-full rounded-full bg-slate-950 overflow-hidden flex border border-slate-800">
                                <div class="bg-gradient-to-r from-orange-500 to-orange-400 h-full w-[60%]"></div>
                                <div class="bg-gradient-to-r from-amber-400 to-amber-300 h-full w-[40%]"></div>
                            </div>
                        </div>

                        <!-- 6-Stat Compact Box Grid -->
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5 pt-1">
                            <!-- Basic Salary -->
                            <div class="p-2.5 rounded-xl bg-slate-900/80 border border-slate-800 space-y-0.5">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-orange-400">Basic (60%)</span>
                                <div class="text-sm font-bold font-mono-numbers text-white">
                                    Rs. <span x-text="formatNumber(basicSalary)">0.00</span>
                                </div>
                                <span class="text-[9px] text-slate-400 block">Gross × 0.60</span>
                            </div>

                            <!-- Allowance -->
                            <div class="p-2.5 rounded-xl bg-slate-900/80 border border-slate-800 space-y-0.5">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-amber-300">Allowance (40%)</span>
                                <div class="text-sm font-bold font-mono-numbers text-white">
                                    Rs. <span x-text="formatNumber(allowance)">0.00</span>
                                </div>
                                <span class="text-[9px] text-slate-400 block">Gross × 0.40</span>
                            </div>

                            <!-- Per Day -->
                            <div class="p-2.5 rounded-xl bg-slate-900/80 border border-slate-800 space-y-0.5">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-300">Per Day Rate</span>
                                <div class="text-sm font-bold font-mono-numbers text-orange-300">
                                    Rs. <span x-text="formatNumber(perDay)">0.00</span>
                                </div>
                                <span class="text-[9px] text-slate-400 block">Gross / <span x-text="daysInSelectedMonth"></span> Days</span>
                            </div>

                            <!-- Per Hour -->
                            <div class="p-2.5 rounded-xl bg-slate-900/80 border border-slate-800 space-y-0.5">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-300">Per Hour Rate</span>
                                <div class="text-sm font-bold font-mono-numbers text-white">
                                    Rs. <span x-text="formatNumber(perHour)">0.00</span>
                                </div>
                                <span class="text-[9px] text-slate-400 block">Per Day / 8 Hours</span>
                            </div>

                            <!-- Normal OT Per Hour -->
                            <div class="p-2.5 rounded-xl bg-slate-900/80 border border-orange-500/25 space-y-0.5">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-orange-400">Normal OT / Hr</span>
                                <div class="text-sm font-bold font-mono-numbers text-orange-400">
                                    Rs. <span x-text="formatNumber(normalOtPerHour)">0.00</span>
                                </div>
                                <span class="text-[9px] text-slate-400 block">Per Hour × 1.5</span>
                            </div>

                            <!-- Special OT Per Hour -->
                            <div class="p-2.5 rounded-xl bg-slate-900/80 border border-amber-500/25 space-y-0.5">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-amber-300">Special OT / Hr</span>
                                <div class="text-sm font-bold font-mono-numbers text-amber-300">
                                    Rs. <span x-text="formatNumber(specialOtPerHour)">0.00</span>
                                </div>
                                <span class="text-[9px] text-slate-400 block">Per Hour × 2.0</span>
                            </div>
                        </div>

                        <!-- Social Security Fund (SSF) Deep Dive -->
                        <div class="pt-2 border-t border-white/[0.08]">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-300 block mb-1.5">
                                Social Security Fund (SSF)
                            </span>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                                <div class="p-2.5 rounded-xl bg-slate-900/80 border border-slate-800">
                                    <span class="text-[9px] uppercase font-bold text-rose-400 block">Employee (11% SSF)</span>
                                    <div class="text-xs font-bold font-mono-numbers text-rose-400 mt-0.5">
                                        Rs. <span x-text="formatNumber(ssf11Amount)">0.00</span>
                                    </div>
                                </div>
                                <div class="p-2.5 rounded-xl bg-slate-900/80 border border-slate-800">
                                    <span class="text-[9px] uppercase font-bold text-orange-400 block">Employer (20% SSF)</span>
                                    <div class="text-xs font-bold font-mono-numbers text-orange-400 mt-0.5">
                                        Rs. <span x-text="formatNumber(ssf20Amount)">0.00</span>
                                    </div>
                                </div>
                                <div class="p-2.5 rounded-xl bg-orange-500/10 border border-orange-500/30">
                                    <span class="text-[9px] uppercase font-bold text-orange-300 block">Total SSF (31%)</span>
                                    <div class="text-xs font-bold font-mono-numbers text-white mt-0.5">
                                        Rs. <span x-text="formatNumber(totalSsfAmount)">0.00</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card 2: Overtime Calculations & Totals -->
                    <div class="glass-panel interactive-card rounded-2xl p-4 sm:p-5 space-y-3">
                        <div class="flex items-center justify-between pb-2 border-b border-white/[0.08]">
                            <div class="flex items-center gap-2">
                                <span class="w-7 h-7 rounded-lg bg-orange-500/20 text-orange-400 flex items-center justify-center font-bold text-xs">
                                    <i class="fa-solid fa-business-time"></i>
                                </span>
                                <div>
                                    <h4 class="font-bold text-white text-sm">Overtime Totals</h4>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="text-[10px] text-slate-400 block font-semibold uppercase">Total OT Amount</span>
                                <span class="text-base font-extrabold font-mono-numbers text-orange-400">
                                    Rs. <span x-text="formatNumber(totalOtAmount)">0.00</span>
                                </span>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <!-- Normal OT Total -->
                            <div class="p-3 rounded-xl bg-slate-900/80 border border-slate-800 space-y-1">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="font-bold text-slate-300">Normal OT Total</span>
                                    <span class="font-mono text-orange-400" x-text="(normalOtHours || 0) + ' hrs'"></span>
                                </div>
                                <div class="text-base font-bold font-mono-numbers text-white">
                                    Rs. <span x-text="formatNumber(normalOtTotalAmount)">0.00</span>
                                </div>
                                <div class="text-[10px] text-slate-400">
                                    Rs. <span x-text="formatNumber(normalOtPerHour)">0.00</span>/hr × <span x-text="normalOtHours || 0">0</span> hrs
                                </div>
                            </div>

                            <!-- Special OT Total -->
                            <div class="p-3 rounded-xl bg-slate-900/80 border border-slate-800 space-y-1">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="font-bold text-slate-300">Special OT Total</span>
                                    <span class="font-mono text-amber-400" x-text="(specialOtHours || 0) + ' hrs'"></span>
                                </div>
                                <div class="text-base font-bold font-mono-numbers text-white">
                                    Rs. <span x-text="formatNumber(specialOtTotalAmount)">0.00</span>
                                </div>
                                <div class="text-[10px] text-slate-400">
                                    Rs. <span x-text="formatNumber(specialOtPerHour)">0.00</span>/hr × <span x-text="specialOtHours || 0">0</span> hrs
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card 3: Adjustments Breakdown -->
                    <div class="glass-panel interactive-card rounded-2xl p-4 sm:p-5 space-y-3">
                        <div class="flex items-center justify-between pb-2 border-b border-white/[0.08]">
                            <div class="flex items-center gap-2">
                                <span class="w-7 h-7 rounded-lg bg-orange-500/20 text-orange-400 flex items-center justify-center font-bold text-xs">
                                    <i class="fa-solid fa-calculator"></i>
                                </span>
                                <div>
                                    <h4 class="font-bold text-white text-sm">Adjustments Breakdown</h4>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="text-[10px] text-slate-400 block font-semibold uppercase">Net Adjustments</span>
                                <span class="text-base font-extrabold font-mono-numbers"
                                      :class="totalAdjustments >= 0 ? 'text-emerald-400' : 'text-rose-400'">
                                    <span x-text="totalAdjustments >= 0 ? '+' : '-'"></span> Rs. <span x-text="formatNumber(Math.abs(totalAdjustments))">0.00</span>
                                </span>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <!-- Adjustment from Days -->
                            <div class="p-3 rounded-xl bg-slate-900/80 border border-slate-800 space-y-0.5">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-300">Days Adjustment</span>
                                <div class="text-sm font-bold font-mono-numbers"
                                      :class="adjDaysSign === 1 ? 'text-emerald-400' : 'text-rose-400'">
                                    <span x-text="adjDaysSign === 1 ? '+' : '-'"></span> Rs. <span x-text="formatNumber(adjustmentDaysAmountAbsolute)">0.00</span>
                                </div>
                                <span class="text-[9px] text-slate-400 block">
                                    Rs. <span x-text="formatNumber(perDay)"></span>/day × <span x-text="adjustmentDays || 0"></span> days
                                </span>
                            </div>

                            <!-- Manual Adjustment -->
                            <div class="p-3 rounded-xl bg-slate-900/80 border border-slate-800 space-y-0.5">
                                <span class="text-[10px] font-bold uppercase tracking-wider text-slate-300">Manual Amount Adjustment</span>
                                <div class="text-sm font-bold font-mono-numbers"
                                      :class="adjAmountSign === 1 ? 'text-emerald-400' : 'text-rose-400'">
                                    <span x-text="adjAmountSign === 1 ? '+' : '-'"></span> Rs. <span x-text="formatNumber(adjustmentManualAmount || 0)">0.00</span>
                                </div>
                                <span class="text-[9px] text-slate-400 block">Direct manual allowance/deduction</span>
                            </div>
                        </div>
                    </div>

                    <!-- Card 4: Festival Allowance Detailed Card -->
                    <template x-if="includeFestival">
                        <div class="glass-panel interactive-card rounded-2xl p-4 sm:p-5 space-y-2.5 border border-orange-500/30">
                            <div class="flex items-center justify-between pb-2 border-b border-white/[0.08]">
                                <div class="flex items-center gap-2">
                                    <span class="w-7 h-7 rounded-lg bg-orange-500/20 text-orange-400 flex items-center justify-center font-bold text-xs">
                                        <i class="fa-solid fa-gift"></i>
                                    </span>
                                    <div>
                                        <h4 class="font-bold text-white text-sm">Festival Allowance Computed</h4>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="text-[10px] text-slate-400 block font-semibold uppercase">Total Allowance</span>
                                    <span class="text-base font-extrabold font-mono-numbers text-orange-400">
                                        Rs. <span x-text="formatNumber(festivalAllowanceAmount)">0.00</span>
                                    </span>
                                </div>
                            </div>

                            <div class="p-2.5 rounded-xl bg-slate-950/70 border border-slate-800 text-xs font-mono space-y-0.5">
                                <div class="text-slate-400 text-[10px]">Formula: ((Rs. <span x-text="formatNumber(festivalBasisSalary)">0.00</span> <span x-text="festivalBasis"></span>) / 365) × <span x-text="workingDays"></span> Days</div>
                                <div class="text-orange-400 font-bold">
                                    = Rs. <span x-text="formatNumber(festivalAllowanceAmount)">0.00</span>
                                </div>
                            </div>
                        </div>
                    </template>

                </div>

            </div>

        </main>

        <!-- Footer -->
        <footer class="no-print mt-6 py-4 border-t border-white/[0.08] bg-slate-950/60 text-center text-xs text-slate-500 flex flex-col sm:flex-row items-center justify-between px-4 sm:px-8 max-w-[1760px] mx-auto w-full gap-2">
            <div>
                Waldo HQ Operations — Designed with precision & elegance.
            </div>
            <div class="flex items-center gap-4">
                <span>Calculations rounded to 2 decimal places</span>
                <span>•</span>
                <a href="/kamkaj" class="text-orange-400 hover:underline">Back to Kamkaj</a>
            </div>
        </footer>

    </div>

    <!-- ═══════════════════════════════════════════════════════════════
         A4 OFFICIAL PRINT SLIP (Visible ONLY during window.print)
    ═══════════════════════════════════════════════════════════════ -->
    <div id="print-slip" class="hidden">
        <div style="font-family: Arial, Helvetica, sans-serif; color: #111; line-height: 1.4; padding: 2mm 0;">

            <!-- Header -->
            <div style="text-align: center; border-bottom: 2px solid #000; padding-bottom: 8px; margin-bottom: 10px;">
                <h1 style="margin: 0; font-size: 19px; font-weight: bold; text-transform: uppercase; letter-spacing: 1px;">
                    WALDO CASINO & ENTERTAINMENT
                </h1>
                <h2 style="margin: 2px 0 0 0; font-size: 13px; font-weight: bold; text-transform: uppercase; color: #333;">
                    SALARY, OVERTIME & REMUNERATION CALCULATION SLIP
                </h2>
                <div style="font-size: 10px; color: #555; margin-top: 3px;">
                    Waldo HQ Operations • Confidential Payroll Record
                </div>
            </div>

            <!-- Meta Information Bar -->
            <table style="width: 100%; border: 1px solid #000; border-collapse: collapse; margin-bottom: 12px; font-size: 10.5px;">
                <tr style="background-color: #f3f4f6;">
                    <td style="padding: 5px 8px; border: 1px solid #000; font-weight: bold; width: 25%;">Payroll Period:</td>
                    <td style="padding: 5px 8px; border: 1px solid #000; width: 25%;" x-text="months[selectedMonth] + ' ' + selectedYear"></td>
                    <td style="padding: 5px 8px; border: 1px solid #000; font-weight: bold; width: 25%;">Days in Month:</td>
                    <td style="padding: 5px 8px; border: 1px solid #000; width: 25%;" x-text="daysInSelectedMonth + ' Days'"></td>
                </tr>
                <tr>
                    <td style="padding: 5px 8px; border: 1px solid #000; font-weight: bold;">Date Generated:</td>
                    <td style="padding: 5px 8px; border: 1px solid #000;" x-text="new Date().toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' })"></td>
                    <td style="padding: 5px 8px; border: 1px solid #000; font-weight: bold;">Generated By:</td>
                    <td style="padding: 5px 8px; border: 1px solid #000;">{{ auth()->user()->name ?? 'System User' }}</td>
                </tr>
                <tr x-show="includeFestival">
                    <td style="padding: 5px 8px; border: 1px solid #000; font-weight: bold;">Festival Allowance:</td>
                    <td style="padding: 5px 8px; border: 1px solid #000;" x-text="workingDays + ' working days (' + festivalBasis.toUpperCase() + ' basis)'"></td>
                    <td style="padding: 5px 8px; border: 1px solid #000; font-weight: bold;">Date Range:</td>
                    <td style="padding: 5px 8px; border: 1px solid #000;" x-text="(festivalDateFrom || 'N/A') + ' to ' + (festivalDateTo || 'N/A')"></td>
                </tr>
            </table>

            <!-- Two-Column Side-by-Side Breakdown Table -->
            <div style="display: flex; gap: 12px; margin-bottom: 12px;">

                <!-- Left Column: Earnings & Additions -->
                <div style="flex: 1;">
                    <table class="print-table">
                        <thead>
                            <tr>
                                <th colspan="2" style="text-align: center; background: #e5e7eb; font-size: 11px;">1. EARNINGS & ENTITLEMENTS</th>
                            </tr>
                            <tr>
                                <th>Item Description</th>
                                <th style="text-align: right; width: 40%;">Amount (NPR)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Basic Salary (60% of Gross)</td>
                                <td style="text-align: right; font-family: monospace;" x-text="formatNumber(basicSalary)"></td>
                            </tr>
                            <tr>
                                <td>Allowance (40% of Gross)</td>
                                <td style="text-align: right; font-family: monospace;" x-text="formatNumber(allowance)"></td>
                            </tr>
                            <tr style="background: #fafafa; font-weight: bold;">
                                <td>GROSS BASE SALARY</td>
                                <td style="text-align: right; font-family: monospace;" x-text="formatNumber(grossSalary)"></td>
                            </tr>
                            <tr>
                                <td>Normal Overtime (<span x-text="normalOtHours || 0"></span> hrs @ 1.5x)</td>
                                <td style="text-align: right; font-family: monospace;" x-text="formatNumber(normalOtTotalAmount)"></td>
                            </tr>
                            <tr>
                                <td>Special Overtime (<span x-text="specialOtHours || 0"></span> hrs @ 2.0x)</td>
                                <td style="text-align: right; font-family: monospace;" x-text="formatNumber(specialOtTotalAmount)"></td>
                            </tr>
                            <tr x-show="totalAdjustments > 0">
                                <td>Positive Adjustments (Days & Manual)</td>
                                <td style="text-align: right; font-family: monospace;" x-text="formatNumber(totalAdjustments)"></td>
                            </tr>
                            <tr x-show="includeFestival">
                                <td>Festival Allowance (<span x-text="workingDays"></span>/365 days)</td>
                                <td style="text-align: right; font-family: monospace;" x-text="formatNumber(festivalAllowanceAmount)"></td>
                            </tr>
                            <tr style="background: #f3f4f6; font-weight: bold;">
                                <td>TOTAL GROSS EARNINGS</td>
                                <td style="text-align: right; font-family: monospace;" x-text="formatNumber(grossEarningsForPrint)"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Right Column: Rates & Deductions -->
                <div style="flex: 1;">
                    <table class="print-table">
                        <thead>
                            <tr>
                                <th colspan="2" style="text-align: center; background: #e5e7eb; font-size: 11px;">2. DEDUCTIONS & CONTRIBUTIONS</th>
                            </tr>
                            <tr>
                                <th>Item Description</th>
                                <th style="text-align: right; width: 40%;">Amount (NPR)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Employee SSF Contribution (11% of Basic)</td>
                                <td style="text-align: right; font-family: monospace; color: #b91c1c;" x-text="formatNumber(ssf11Amount)"></td>
                            </tr>
                            <tr x-show="totalAdjustments < 0">
                                <td>Negative Adjustments (Days / Deductions)</td>
                                <td style="text-align: right; font-family: monospace; color: #b91c1c;" x-text="formatNumber(Math.abs(totalAdjustments))"></td>
                            </tr>
                            <tr style="background: #fafafa; font-weight: bold;">
                                <td>TOTAL EMPLOYEE DEDUCTIONS</td>
                                <td style="text-align: right; font-family: monospace; color: #b91c1c;" x-text="formatNumber(totalDeductionsForPrint)"></td>
                            </tr>
                            <tr>
                                <th colspan="2" style="text-align: center; background: #f3f4f6; font-size: 10px;">REFERENCE HOURLY & DAILY RATES</th>
                            </tr>
                            <tr>
                                <td>Per Day Rate (Gross / <span x-text="daysInSelectedMonth"></span> days)</td>
                                <td style="text-align: right; font-family: monospace;" x-text="formatNumber(perDay)"></td>
                            </tr>
                            <tr>
                                <td>Per Hour Rate (Per Day / 8 hrs)</td>
                                <td style="text-align: right; font-family: monospace;" x-text="formatNumber(perHour)"></td>
                            </tr>
                            <tr>
                                <td>Normal OT Rate (1.5x) / hr</td>
                                <td style="text-align: right; font-family: monospace;" x-text="formatNumber(normalOtPerHour)"></td>
                            </tr>
                            <tr>
                                <td>Special OT Rate (2.0x) / hr</td>
                                <td style="text-align: right; font-family: monospace;" x-text="formatNumber(specialOtPerHour)"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>

            <!-- Grand Totals Banner -->
            <table style="width: 100%; border: 2px solid #000; border-collapse: collapse; margin-bottom: 16px; background-color: #f9fafb;">
                <tr>
                    <td style="padding: 10px 14px; border-right: 2px solid #000; width: 60%;">
                        <div style="font-size: 11px; text-transform: uppercase; font-weight: bold; color: #444;">
                            FINAL NET PAYABLE TO EMPLOYEE (TAKE-HOME)
                        </div>
                        <div style="font-size: 21px; font-weight: bold; font-family: monospace; color: #000; margin-top: 3px;">
                            NPR <span x-text="formatNumber(netPayable)"></span>
                        </div>
                        <div style="font-size: 10px; color: #555; margin-top: 2px;">
                            Gross Remuneration + OT + Adjustments - 11% SSF + Festival Allowance
                        </div>
                    </td>
                    <td style="padding: 10px 14px; width: 40%;">
                        <div style="font-size: 10px; text-transform: uppercase; font-weight: bold; color: #555;">
                            EMPLOYER SSF DEPOSIT (20%):
                        </div>
                        <div style="font-size: 13px; font-weight: bold; font-family: monospace; color: #222; margin-bottom: 4px;">
                            NPR <span x-text="formatNumber(ssf20Amount)"></span>
                        </div>
                        <div style="font-size: 10px; text-transform: uppercase; font-weight: bold; color: #555;">
                            TOTAL COST TO COMPANY (CTC):
                        </div>
                        <div style="font-size: 14px; font-weight: bold; font-family: monospace; color: #000;">
                            NPR <span x-text="formatNumber(totalCTC)"></span>
                        </div>
                    </td>
                </tr>
            </table>

            <!-- Official Signatures Box -->
            <table style="width: 100%; border: 1px solid #999; border-collapse: collapse; margin-top: 24px; font-size: 10px;">
                <tr>
                    <td style="width: 25%; height: 50px; vertical-align: bottom; text-align: center; border: 1px solid #999; padding-bottom: 5px;">
                        _______________________<br>
                        <strong>Prepared By</strong>
                    </td>
                    <td style="width: 25%; height: 50px; vertical-align: bottom; text-align: center; border: 1px solid #999; padding-bottom: 5px;">
                        _______________________<br>
                        <strong>Checked By (HR)</strong>
                    </td>
                    <td style="width: 25%; height: 50px; vertical-align: bottom; text-align: center; border: 1px solid #999; padding-bottom: 5px;">
                        _______________________<br>
                        <strong>Approved By (Finance)</strong>
                    </td>
                    <td style="width: 25%; height: 50px; vertical-align: bottom; text-align: center; border: 1px solid #999; padding-bottom: 5px;">
                        _______________________<br>
                        <strong>Employee Acknowledgment</strong>
                    </td>
                </tr>
            </table>

            <div style="text-align: center; font-size: 9px; color: #777; margin-top: 10px;">
                This document is a computer-generated official payroll calculation sheet issued by Waldo Casino & Entertainment.
            </div>

        </div>
    </div>

    <!-- Alpine.js Calculator Logic with LocalStorage Sync -->
    <script>
        function salaryCalculator() {
            const STORAGE_KEY = 'waldo_salary_calculator_state_v3';

            const now = new Date();
            const defaultState = {
                grossSalary: 25000,
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

            let initial = defaultState;
            try {
                const saved = localStorage.getItem(STORAGE_KEY) || localStorage.getItem('waldo_salary_calculator_state_v2');
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
                copied: false,
                months: [
                    'January', 'February', 'March', 'April', 'May', 'June',
                    'July', 'August', 'September', 'October', 'November', 'December'
                ],
                yearOptions: [2023, 2024, 2025, 2026, 2027, 2028, 2029, 2030],

                init() {
                    if (this.festivalDateFrom && this.festivalDateTo) {
                        this.calculateDaysFromDates();
                    }
                },

                triggerPrint() {
                    window.print();
                },

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

                resetDefaults() {
                    if (confirm('Reset all values to initial defaults?')) {
                        localStorage.removeItem(STORAGE_KEY);
                        localStorage.removeItem('waldo_salary_calculator_state_v2');
                        localStorage.removeItem('waldo_salary_calculator_state_v1');
                        location.reload();
                    }
                },

                get daysInSelectedMonth() {
                    return new Date(this.selectedYear, this.selectedMonth + 1, 0).getDate();
                },

                // ─────────────────────────────────────────────────────────────
                // COMPUTED SALARY STRUCTURE
                // ─────────────────────────────────────────────────────────────
                get basicSalary() {
                    return this.round(Number(this.grossSalary || 0) * 0.60);
                },

                get allowance() {
                    return this.round(Number(this.grossSalary || 0) * 0.40);
                },

                get ssf11Amount() {
                    return this.round(this.basicSalary * 0.11);
                },

                get ssf20Amount() {
                    return this.round(this.basicSalary * 0.20);
                },

                get totalSsfAmount() {
                    return this.round(this.ssf11Amount + this.ssf20Amount);
                },

                get perDay() {
                    const days = this.daysInSelectedMonth;
                    if (!days) return 0;
                    return this.round(Number(this.grossSalary || 0) / days);
                },

                get perHour() {
                    return this.round(this.perDay / 8);
                },

                get normalOtPerHour() {
                    return this.round(this.perHour * 1.5);
                },

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

                calculateDaysFromDates() {
                    if (!this.festivalDateFrom || !this.festivalDateTo) return;
                    const from = new Date(this.festivalDateFrom);
                    const to = new Date(this.festivalDateTo);
                    if (isNaN(from.getTime()) || isNaN(to.getTime())) return;

                    const diffTime = to.getTime() - from.getTime();
                    let diffDays = Math.round(diffTime / (1000 * 3600 * 24)) + 1;
                    if (diffDays < 0) diffDays = 0;
                    if (diffDays >= 365) diffDays = 365;

                    this.workingDays = diffDays;
                },

                // ─────────────────────────────────────────────────────────────
                // PRINT CALCULATIONS
                // ─────────────────────────────────────────────────────────────
                get grossEarningsForPrint() {
                    let total = Number(this.grossSalary || 0) + this.totalOtAmount;
                    if (this.totalAdjustments > 0) total += this.totalAdjustments;
                    if (this.includeFestival) total += this.festivalAllowanceAmount;
                    return this.round(total);
                },

                get totalDeductionsForPrint() {
                    let total = this.ssf11Amount;
                    if (this.totalAdjustments < 0) total += Math.abs(this.totalAdjustments);
                    return this.round(total);
                },

                // ─────────────────────────────────────────────────────────────
                // COMPUTED MASTER NET PAYABLE & CTC
                // ─────────────────────────────────────────────────────────────
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

                async copySummary() {
                    const lines = [
                        `═══════════════════════════════════════════`,
                        ` WALDO CASINO & ENTERTAINMENT — SALARY & OT`,
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
