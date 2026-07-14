<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Kamkaj | Waldo HQ Operations</title>
    <meta name="description" content="Kamkaj — the internal command center for Waldo. Where employees are managed, fingers are scanned, and letters get written.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,300;0,14..32,400;0,14..32,500;0,14..32,600;0,14..32,700;1,14..32,400&family=Space+Grotesk:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        display: ['Space Grotesk', 'sans-serif'],
                        mono: ['JetBrains Mono', 'monospace'],
                    },
                    colors: {
                        brand: {
                            50: '#f5f3ff', 100: '#ede9fe', 200: '#ddd6fe',
                            300: '#c084fc', 400: '#a855f7', 500: '#8b5cf6',
                            600: '#7c3aed', 700: '#6d28d9', 800: '#5b21b6',
                            900: '#4c1d95', 950: '#0a0618',
                        }
                    }
                }
            }
        }
    </script>

    <style>
        :root {
            --glow-purple: rgba(139, 92, 246, 0.3);
            --glow-cyan: rgba(6, 182, 212, 0.25);
            --glow-pink: rgba(236, 72, 153, 0.2);
        }

        /* Scrollbar */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(139,92,246,0.4); border-radius: 99px; }

        /* Glass */
        .glass {
            background: rgba(255,255,255,0.45);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(0,0,0,0.06);
        }
        .dark .glass {
            background: rgba(10,6,24,0.5);
            border: 1px solid rgba(255,255,255,0.06);
        }

        /* Glow blob */
        .blob {
            filter: blur(90px);
            will-change: transform;
            animation: blobAnim var(--dur, 8s) ease-in-out infinite;
        }
        @keyframes blobAnim {
            0%,100% { transform: translate(0,0) scale(1); }
            33%      { transform: translate(var(--tx,30px), var(--ty,-40px)) scale(1.08); }
            66%      { transform: translate(calc(-1 * var(--tx,30px)), var(--ty2,20px)) scale(0.94); }
        }

        /* Gradient text */
        .grad { background: linear-gradient(135deg, #8b5cf6, #06b6d4, #ec4899); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
        .grad-subtle { background: linear-gradient(135deg, #a78bfa, #67e8f9); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }

        /* Glow effects */
        .glow-ring { box-shadow: 0 0 0 1px rgba(139,92,246,0.2), 0 0 20px rgba(139,92,246,0.1); }
        .dark .glow-ring { box-shadow: 0 0 0 1px rgba(139,92,246,0.25), 0 0 30px rgba(139,92,246,0.15); }

        /* Module card */
        .module-card {
            transition: transform 0.35s cubic-bezier(.22,1,.36,1), box-shadow 0.35s ease;
        }
        .module-card:hover { transform: translateY(-6px); }
        .dark .module-card:hover { box-shadow: 0 0 40px var(--glow-purple); }

        /* Marquee */
        .marquee-track { display: flex; gap: 3rem; white-space: nowrap; animation: marquee var(--dur, 30s) linear infinite; }
        .marquee-track-r { display: flex; gap: 3rem; white-space: nowrap; animation: marqueeR var(--dur, 28s) linear infinite; }
        @keyframes marquee  { from { transform: translateX(0); } to { transform: translateX(-50%); } }
        @keyframes marqueeR { from { transform: translateX(-50%); } to { transform: translateX(0); } }

        /* Typing cursor */
        .type-cursor::after { content: '|'; animation: blink 1s step-end infinite; }
        @keyframes blink { 50% { opacity: 0; } }

        /* Fingerprint scanner */
        .fp-scan-line { animation: fpScan 2s ease-in-out infinite; }
        @keyframes fpScan {
            0%   { transform: translateY(0); opacity: 0; }
            10%  { opacity: 1; }
            90%  { opacity: 1; }
            100% { transform: translateY(100px); opacity: 0; }
        }
        .fp-pulse { animation: fpPulse 2s ease-in-out infinite; }
        @keyframes fpPulse { 0%,100% { opacity: 0.3; } 50% { opacity: 1; } }

        /* Neon text */
        .neon-purple { text-shadow: 0 0 8px rgba(139,92,246,0.5), 0 0 20px rgba(139,92,246,0.25); }
        .dark .neon-purple { text-shadow: 0 0 15px rgba(139,92,246,0.7), 0 0 40px rgba(139,92,246,0.35); }

        /* Stats counter */
        .stat-glow { transition: all 0.3s ease; }
        .stat-glow:hover { filter: brightness(1.2); }

        /* Grid shimmer */
        .shimmer-border {
            background: linear-gradient(90deg, transparent, rgba(139,92,246,0.3), transparent);
            background-size: 200% 100%;
            animation: shimmer 3s ease infinite;
        }
        @keyframes shimmer { from { background-position: -200% 0; } to { background-position: 200% 0; } }

        /* Panic screen */
        #panic-screen { display: none; z-index: 9999; }

        /* Scroll reveal */
        .reveal { opacity: 0; transform: translateY(30px); transition: opacity 0.7s ease, transform 0.7s ease; }
        .reveal.visible { opacity: 1; transform: translateY(0); }

        /* Mouse cursor glow */
        #cursor-glow {
            position: fixed;
            width: 350px; height: 350px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(139,92,246,0.06) 0%, transparent 70%);
            pointer-events: none;
            transform: translate(-50%, -50%);
            z-index: 1;
            transition: opacity 0.3s ease;
        }
        .dark #cursor-glow { background: radial-gradient(circle, rgba(139,92,246,0.12) 0%, transparent 70%); }
    </style>
</head>
<body class="bg-slate-50 dark:bg-brand-950 text-slate-800 dark:text-slate-100 font-sans min-h-screen overflow-x-hidden antialiased transition-colors duration-300">

    <!-- Cursor glow (desktop only) -->
    <div id="cursor-glow"></div>

    <!-- Background blobs -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none select-none z-0" aria-hidden="true">
        <div class="blob absolute top-[-8%] left-[15%] w-[600px] h-[600px] rounded-full bg-violet-500 opacity-[0.1] dark:opacity-[0.22]" style="--dur:9s;--tx:40px;--ty:-50px;--ty2:25px;"></div>
        <div class="blob absolute top-[40%] right-[5%] w-[500px] h-[500px] rounded-full bg-cyan-400 opacity-[0.08] dark:opacity-[0.18]" style="--dur:11s;--tx:-30px;--ty:-35px;--ty2:30px;"></div>
        <div class="blob absolute bottom-[5%] left-[8%] w-[480px] h-[480px] rounded-full bg-fuchsia-500 opacity-[0.08] dark:opacity-[0.16]" style="--dur:13s;--tx:25px;--ty:-45px;--ty2:20px;"></div>
    </div>

    <!-- Particle canvas -->
    <canvas id="particle-canvas" class="fixed inset-0 z-0 pointer-events-none opacity-30 dark:opacity-60" aria-hidden="true"></canvas>

    <!-- ───────────────────────── NAVBAR ───────────────────────── -->
    <header id="navbar" class="w-full fixed top-0 left-0 z-50 transition-all duration-300">
        <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
            <!-- Logo -->
            <a href="#" class="flex items-center gap-3 group">
                <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-brand-600 to-cyan-500 flex items-center justify-center text-white font-display font-bold text-lg shadow-lg shadow-brand-500/20 group-hover:shadow-brand-500/40 group-hover:scale-105 transition-all duration-300">K</div>
                <span class="font-display font-bold text-2xl leading-none grad">kamkaj</span>
            </a>

            <!-- Center nav links (desktop) -->
            <nav class="hidden md:flex items-center gap-1">
                <a href="#hero" class="px-4 py-2 rounded-xl text-sm font-medium text-slate-600 dark:text-slate-400 hover:text-brand-600 dark:hover:text-brand-300 hover:bg-brand-50 dark:hover:bg-brand-500/10 transition-all">Home</a>
                <a href="#modules" class="px-4 py-2 rounded-xl text-sm font-medium text-slate-600 dark:text-slate-400 hover:text-brand-600 dark:hover:text-brand-300 hover:bg-brand-50 dark:hover:bg-brand-500/10 transition-all">Modules</a>
                <a href="#stats" class="px-4 py-2 rounded-xl text-sm font-medium text-slate-600 dark:text-slate-400 hover:text-brand-600 dark:hover:text-brand-300 hover:bg-brand-50 dark:hover:bg-brand-500/10 transition-all">Stats</a>
                <a href="#fun" class="px-4 py-2 rounded-xl text-sm font-medium text-slate-600 dark:text-slate-400 hover:text-brand-600 dark:hover:text-brand-300 hover:bg-brand-50 dark:hover:bg-brand-500/10 transition-all">Fun Zone</a>
            </nav>

            <!-- Right actions -->
            <div class="flex items-center gap-3">
                <!-- Theme toggle -->
                <button id="theme-toggle" class="p-2.5 rounded-xl glass glow-ring hover:scale-105 transition-all duration-200 cursor-pointer" title="Toggle theme">
                    <svg id="icon-sun" class="w-5 h-5 text-amber-500 hidden" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" /></svg>
                    <svg id="icon-moon" class="w-5 h-5 text-brand-400 hidden" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" /></svg>
                </button>

                <!-- Panic button -->
                <button onclick="triggerPanic()" class="hidden sm:flex items-center gap-2 px-4 py-2.5 rounded-xl bg-rose-500/10 hover:bg-rose-500/15 border border-rose-400/20 text-rose-600 dark:text-rose-400 font-semibold text-sm transition-all hover:scale-105 cursor-pointer">
                    🚨 Boss Mode
                </button>

                <!-- Main CTA -->
                <a href="{{ url('/kamkaj/login') }}" class="flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gradient-to-r from-brand-600 to-cyan-500 hover:from-brand-500 hover:to-cyan-400 text-white font-bold text-sm shadow-lg shadow-brand-500/20 hover:shadow-brand-500/35 hover:scale-105 transition-all duration-200">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" /></svg>
                    Enter Panel
                </a>
            </div>
        </div>
    </header>

    <!-- ───────────────────────── HERO ───────────────────────── -->
    <section id="hero" class="relative min-h-screen flex flex-col items-center justify-center pt-32 pb-20 px-6 z-10">
        
        <!-- Live clock badge -->
        <div class="mb-8 flex items-center gap-3">
            <div class="flex items-center gap-2.5 px-4 py-2 rounded-full glass glow-ring text-xs font-mono font-medium text-brand-600 dark:text-brand-300">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span>WALDO HQ OPERATIONS LIVE</span>
                <span class="text-slate-400 dark:text-slate-500">·</span>
                <span id="live-clock" class="tabular-nums">--:--:--</span>
            </div>
        </div>

        <!-- Main headline -->
        <div class="max-w-5xl text-center space-y-6">
            <h1 class="text-5xl sm:text-7xl lg:text-8xl font-display font-extrabold tracking-tight leading-[0.9]">
                <span class="text-slate-900 dark:text-white block">Where Chaos</span>
                <span class="grad neon-purple block my-2">Meets Structure</span>
                <span class="text-slate-500 dark:text-slate-500 text-3xl sm:text-4xl font-semibold block mt-4">(Sort of. We tried. Mostly.)</span>
            </h1>

            <p class="text-xl text-slate-600 dark:text-slate-400 max-w-2xl mx-auto leading-relaxed">
                Welcome to <span class="font-semibold text-brand-600 dark:text-brand-300">Kamkaj</span> — the internal management console where we track employees, scan fingers, review candidates, and forge letters that make us sound very official in meetings.
            </p>

            <!-- CTA row -->
            <div class="flex flex-wrap justify-center gap-4 pt-4">
                <a href="{{ url('/kamkaj/login') }}" class="group flex items-center gap-3 px-8 py-4 rounded-2xl bg-gradient-to-r from-brand-600 to-cyan-500 hover:from-brand-500 hover:to-cyan-400 text-white font-bold text-base shadow-2xl shadow-brand-500/30 hover:shadow-brand-500/50 hover:-translate-y-1 transition-all duration-300">
                    <span>Launch Command Center</span>
                    <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                </a>
                <button onclick="triggerPanic()" class="sm:hidden flex items-center gap-2 px-8 py-4 rounded-2xl bg-rose-500/10 hover:bg-rose-500/20 border border-rose-400/30 text-rose-600 dark:text-rose-400 font-bold text-base transition-all hover:-translate-y-1 cursor-pointer">
                    🚨 Boss is Coming!
                </button>
                <a href="#modules" class="flex items-center gap-2 px-8 py-4 rounded-2xl glass glow-ring text-slate-700 dark:text-slate-300 font-semibold text-base hover:-translate-y-1 transition-all duration-300">
                    Explore Modules ↓
                </a>
            </div>
        </div>

        <!-- Floating badges -->
        <div class="mt-16 flex flex-wrap justify-center gap-3">
            <span class="flex items-center gap-2 px-3 py-1.5 rounded-full glass text-xs font-mono text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20">✅ Sheets Syncing Live</span>
            <span class="flex items-center gap-2 px-3 py-1.5 rounded-full glass text-xs font-mono text-brand-600 dark:text-brand-400 border border-brand-200 dark:border-brand-500/20">🧬 Biometrics Tracking</span>
            <span class="flex items-center gap-2 px-3 py-1.5 rounded-full glass text-xs font-mono text-cyan-600 dark:text-cyan-400 border border-cyan-200 dark:border-cyan-500/20">📊 Real-time Filament Panel</span>
            <span class="flex items-center gap-2 px-3 py-1.5 rounded-full glass text-xs font-mono text-violet-600 dark:text-violet-400 border border-violet-200 dark:border-violet-500/20">☁️ Google Sheets API</span>
        </div>

        <!-- Scroll indicator -->
        <div class="absolute bottom-8 left-1/2 -translate-x-1/2 flex flex-col items-center gap-2 opacity-40">
            <span class="text-xs font-mono tracking-widest">SCROLL</span>
            <div class="w-0.5 h-8 bg-gradient-to-b from-brand-400 to-transparent animate-pulse"></div>
        </div>
    </section>

    <!-- ───────────────────────── MARQUEE ───────────────────────── -->
    <div class="py-6 border-y border-slate-200 dark:border-white/5 bg-white/30 dark:bg-black/20 overflow-hidden z-10 relative">
        <div class="overflow-hidden">
            <div class="marquee-track" style="--dur:25s;">
                <span class="font-display font-bold text-3xl text-slate-300 dark:text-white/10">Employee Management</span>
                <span class="text-brand-400 text-2xl">◆</span>
                <span class="font-display font-bold text-3xl grad-subtle">Biometric Allotments</span>
                <span class="text-cyan-400 text-2xl">◆</span>
                <span class="font-display font-bold text-3xl text-slate-300 dark:text-white/10">Google Sheets Sync</span>
                <span class="text-fuchsia-400 text-2xl">◆</span>
                <span class="font-display font-bold text-3xl grad-subtle">Letter Forge</span>
                <span class="text-brand-400 text-2xl">◆</span>
                <span class="font-display font-bold text-3xl text-slate-300 dark:text-white/10">Candidate Tracker</span>
                <span class="text-cyan-400 text-2xl">◆</span>
                <!-- Repeat for seamless loop -->
                <span class="font-display font-bold text-3xl text-slate-300 dark:text-white/10">Employee Management</span>
                <span class="text-brand-400 text-2xl">◆</span>
                <span class="font-display font-bold text-3xl grad-subtle">Biometric Allotments</span>
                <span class="text-cyan-400 text-2xl">◆</span>
                <span class="font-display font-bold text-3xl text-slate-300 dark:text-white/10">Google Sheets Sync</span>
                <span class="text-fuchsia-400 text-2xl">◆</span>
                <span class="font-display font-bold text-3xl grad-subtle">Letter Forge</span>
                <span class="text-brand-400 text-2xl">◆</span>
                <span class="font-display font-bold text-3xl text-slate-300 dark:text-white/10">Candidate Tracker</span>
                <span class="text-cyan-400 text-2xl">◆</span>
            </div>
        </div>
    </div>

    <!-- ───────────────────────── MODULES ───────────────────────── -->
    <section id="modules" class="relative z-10 py-32 px-6">
        <div class="max-w-6xl mx-auto">
            <!-- Header -->
            <div class="text-center mb-20 reveal">
                <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full border border-brand-200 dark:border-brand-500/20 bg-brand-50 dark:bg-brand-500/10 text-brand-600 dark:text-brand-300 text-xs font-semibold tracking-widest uppercase mb-6">
                    🏢 Mission Control
                </div>
                <h2 class="text-4xl md:text-6xl font-display font-extrabold text-slate-900 dark:text-white mb-4">
                    Our Critical <span class="grad">Bureaucracy</span> Modules
                </h2>
                <p class="text-lg text-slate-500 dark:text-slate-400 max-w-xl mx-auto">
                    Every module below runs on copious amounts of caffeine and PHP 8.4 constructor promotions.
                </p>
            </div>

            <!-- Module grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                <!-- Employee Database -->
                <div class="module-card glass glow-ring rounded-3xl p-8 group reveal">
                    <div class="flex items-start justify-between mb-6">
                        <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-brand-500 to-violet-600 flex items-center justify-center text-2xl shadow-lg shadow-brand-500/25 group-hover:scale-110 group-hover:shadow-brand-500/40 transition-all duration-300">👥</div>
                        <span class="font-mono text-xs text-slate-400 dark:text-slate-500 bg-slate-100 dark:bg-brand-900/50 px-3 py-1 rounded-full">Module 01</span>
                    </div>
                    <h3 class="font-display font-bold text-2xl mb-3 text-slate-900 dark:text-white group-hover:text-brand-600 dark:group-hover:text-brand-300 transition-colors">Employee Database</h3>
                    <p class="text-slate-600 dark:text-slate-400 leading-relaxed mb-6">
                        The holy ledger of everyone who dares to work at Waldo. Tracks names, departments, ranks, join dates, and biometric IDs. We know everything, except where the office printer paper went.
                    </p>
                    <div class="flex items-center gap-2 text-xs font-mono text-emerald-500 dark:text-emerald-400">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        Synced with Google Sheets · Live
                    </div>
                </div>

                <!-- Biometric Allotments -->
                <div class="module-card glass glow-ring rounded-3xl p-8 group reveal" style="transition-delay:0.1s">
                    <div class="flex items-start justify-between mb-6">
                        <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-cyan-500 to-sky-600 flex items-center justify-center text-2xl shadow-lg shadow-cyan-500/25 group-hover:scale-110 group-hover:shadow-cyan-500/40 transition-all duration-300">🧬</div>
                        <span class="font-mono text-xs text-slate-400 dark:text-slate-500 bg-slate-100 dark:bg-brand-900/50 px-3 py-1 rounded-full">Module 02</span>
                    </div>
                    <h3 class="font-display font-bold text-2xl mb-3 text-slate-900 dark:text-white group-hover:text-cyan-600 dark:group-hover:text-cyan-300 transition-colors">Biometric Allotments</h3>
                    <p class="text-slate-600 dark:text-slate-400 leading-relaxed mb-6">
                        Every new joinee gets their fingerprint catalogued into our biometric devices. Yes, we scan fingers here. No, we cannot read minds — that is HR's job. Tracks device allocation, shift type, and enrollment status.
                    </p>
                    <div class="flex items-center gap-2 text-xs font-mono text-cyan-500 dark:text-cyan-400">
                        <span class="w-2 h-2 rounded-full bg-cyan-400 animate-pulse"></span>
                        407 allotments synced · CWD prefix auto-increment
                    </div>
                </div>

                <!-- Candidate Tracker -->
                <div class="module-card glass glow-ring rounded-3xl p-8 group reveal" style="transition-delay:0.15s">
                    <div class="flex items-start justify-between mb-6">
                        <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-fuchsia-500 to-pink-600 flex items-center justify-center text-2xl shadow-lg shadow-fuchsia-500/25 group-hover:scale-110 group-hover:shadow-fuchsia-500/40 transition-all duration-300">🎯</div>
                        <span class="font-mono text-xs text-slate-400 dark:text-slate-500 bg-slate-100 dark:bg-brand-900/50 px-3 py-1 rounded-full">Module 03</span>
                    </div>
                    <h3 class="font-display font-bold text-2xl mb-3 text-slate-900 dark:text-white group-hover:text-fuchsia-600 dark:group-hover:text-fuchsia-300 transition-colors">Candidate Hunt</h3>
                    <p class="text-slate-600 dark:text-slate-400 leading-relaxed mb-6">
                        Where career dreams go to be catalogued. We track applicant names, phone numbers, statuses, and notes. If it says "Approved" — congratulations. If it says "Rejected" — better luck next life.
                    </p>
                    <div class="flex items-center gap-2 text-xs font-mono text-fuchsia-500 dark:text-fuchsia-400">
                        <span class="w-2 h-2 rounded-full bg-fuchsia-400 animate-pulse"></span>
                        Status pipeline tracking · Photo upload supported
                    </div>
                </div>

                <!-- Letter Forge -->
                <div class="module-card glass glow-ring rounded-3xl p-8 group reveal" style="transition-delay:0.2s">
                    <div class="flex items-start justify-between mb-6">
                        <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-amber-500 to-orange-600 flex items-center justify-center text-2xl shadow-lg shadow-amber-500/25 group-hover:scale-110 group-hover:shadow-amber-500/40 transition-all duration-300">📝</div>
                        <span class="font-mono text-xs text-slate-400 dark:text-slate-500 bg-slate-100 dark:bg-brand-900/50 px-3 py-1 rounded-full">Module 04</span>
                    </div>
                    <h3 class="font-display font-bold text-2xl mb-3 text-slate-900 dark:text-white group-hover:text-amber-600 dark:group-hover:text-amber-300 transition-colors">Letter Forge</h3>
                    <p class="text-slate-600 dark:text-slate-400 leading-relaxed mb-6">
                        Generate professional-sounding letters using templates. Appointment letters, experience letters, whatever you need to sound important. We even let you pick custom fonts so the letterhead looks stately.
                    </p>
                    <div class="flex items-center gap-2 text-xs font-mono text-amber-500 dark:text-amber-400">
                        <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
                        PDF export ready · Custom template engine
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ───────────────────────── STATS ───────────────────────── -->
    <section id="stats" class="relative z-10 py-24 px-6 bg-white/20 dark:bg-black/20 border-y border-slate-200 dark:border-white/5">
        <div class="max-w-5xl mx-auto">
            <div class="text-center mb-16 reveal">
                <h2 class="text-3xl md:text-5xl font-display font-extrabold text-slate-900 dark:text-white">
                    Numbers That Make Us <span class="grad">Sound Impressive</span>
                </h2>
                <p class="text-slate-500 dark:text-slate-400 mt-4">Live data, responsibly fabricated where unavailable.</p>
            </div>

            <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="stat-glow glass glow-ring rounded-2xl p-6 text-center reveal">
                    <div class="text-4xl font-display font-extrabold grad mb-1" data-count="407">0</div>
                    <div class="text-xs font-mono text-slate-500 dark:text-slate-400 uppercase tracking-wider">Biometrics Synced</div>
                </div>
                <div class="stat-glow glass glow-ring rounded-2xl p-6 text-center reveal" style="transition-delay:0.1s">
                    <div class="text-4xl font-display font-extrabold text-cyan-500 dark:text-cyan-400 mb-1" data-count="20">0</div>
                    <div class="text-xs font-mono text-slate-500 dark:text-slate-400 uppercase tracking-wider">Departments</div>
                </div>
                <div class="stat-glow glass glow-ring rounded-2xl p-6 text-center reveal" style="transition-delay:0.15s">
                    <div class="text-4xl font-display font-extrabold text-fuchsia-500 dark:text-fuchsia-400 mb-1" data-count="1">0</div>
                    <div class="text-xs font-mono text-slate-500 dark:text-slate-400 uppercase tracking-wider">Spreadsheet Controlled</div>
                </div>
                <div class="stat-glow glass glow-ring rounded-2xl p-6 text-center reveal" style="transition-delay:0.2s">
                    <div class="text-4xl font-display font-extrabold text-amber-500 dark:text-amber-400 mb-1">∞</div>
                    <div class="text-xs font-mono text-slate-500 dark:text-slate-400 uppercase tracking-wider">Letters Generated</div>
                </div>
            </div>
        </div>
    </section>

    <!-- ───────────────────────── FUN ZONE ───────────────────────── -->
    <section id="fun" class="relative z-10 py-32 px-6">
        <div class="max-w-6xl mx-auto space-y-10">
            <div class="text-center reveal">
                <h2 class="text-3xl md:text-5xl font-display font-extrabold text-slate-900 dark:text-white mb-3">
                    The <span class="grad">Fun Zone</span> 🎪
                </h2>
                <p class="text-slate-500 dark:text-slate-400">Because even serious operations need a laugh break.</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Productivity simulator -->
                <div class="glass glow-ring rounded-3xl p-8 reveal">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="w-10 h-10 rounded-xl bg-emerald-500/15 flex items-center justify-center text-xl">⚙️</div>
                        <div>
                            <h3 class="font-display font-bold text-xl text-slate-900 dark:text-white">Productivity Simulator™</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">For when your activity logs need a boost</p>
                        </div>
                    </div>
                    <div id="work-console" class="font-mono text-xs bg-slate-900 text-emerald-400 p-4 rounded-xl h-36 overflow-y-auto border border-white/5 mb-4 leading-relaxed">
                        <span class="text-slate-500">[System]</span> Kamkaj v2.0 ready. Awaiting tasks...
                    </div>
                    <div class="flex gap-3">
                        <button onclick="simulateWork()" class="flex-1 py-2.5 rounded-xl bg-emerald-500/15 hover:bg-emerald-500/25 border border-emerald-500/25 text-emerald-600 dark:text-emerald-400 font-semibold text-sm transition-all cursor-pointer">
                            ▶ Run Task
                        </button>
                        <button onclick="clearConsole()" class="px-4 py-2.5 rounded-xl bg-slate-200 dark:bg-brand-900/50 hover:bg-slate-300 dark:hover:bg-brand-800 text-slate-600 dark:text-slate-400 text-sm transition-all cursor-pointer">
                            Clear
                        </button>
                    </div>
                </div>

                <!-- Fingerprint scanner Easter egg -->
                <div class="glass glow-ring rounded-3xl p-8 reveal" style="transition-delay:0.1s">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="w-10 h-10 rounded-xl bg-cyan-500/15 flex items-center justify-center text-xl">🔐</div>
                        <div>
                            <h3 class="font-display font-bold text-xl text-slate-900 dark:text-white">Biometric Scanner Demo</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Hold your finger on the scanner. Trust us.</p>
                        </div>
                    </div>

                    <div class="flex flex-col items-center gap-5">
                        <!-- Scanner visual -->
                        <div id="fp-scanner" class="relative w-28 h-36 rounded-2xl border-2 border-brand-400/40 dark:border-brand-500/30 bg-brand-50 dark:bg-brand-900/30 overflow-hidden cursor-pointer hover:border-brand-400/60 transition-all group"
                             onmousedown="startScan()" onmouseup="stopScan()" ontouchstart="startScan()" ontouchend="stopScan()">
                            <!-- Fingerprint SVG -->
                            <svg class="w-full h-full p-4 text-brand-300 dark:text-brand-600 group-hover:text-brand-400 dark:group-hover:text-brand-400 transition-colors" viewBox="0 0 100 120" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round">
                                <path d="M50 10 C30 10, 15 25, 15 45 C15 75, 50 95, 50 95"/>
                                <path d="M50 10 C70 10, 85 25, 85 45 C85 75, 50 95, 50 95"/>
                                <path d="M30 35 C30 22, 38 18, 50 18 C62 18, 70 22, 70 35 C70 55, 50 80, 50 80"/>
                                <path d="M38 30 C38 22, 43 20, 50 20 C57 20, 62 22, 62 30 C62 48, 50 70, 50 70"/>
                                <path d="M44 28 C44 23, 47 22, 50 22 C53 22, 56 23, 56 28 C56 44, 50 60, 50 60"/>
                            </svg>
                            <!-- Scan line -->
                            <div id="fp-scanline" class="absolute top-0 left-0 w-full h-0.5 bg-gradient-to-r from-transparent via-cyan-400 to-transparent opacity-0 transition-all duration-300"></div>
                        </div>

                        <div id="fp-status" class="text-sm font-mono text-slate-500 dark:text-slate-400 text-center min-h-[2em]">
                            Press and hold the scanner to identify...
                        </div>
                    </div>
                </div>

                <!-- Office mood oracle -->
                <div class="glass glow-ring rounded-3xl p-8 reveal" style="transition-delay:0.15s">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="w-10 h-10 rounded-xl bg-fuchsia-500/15 flex items-center justify-center text-xl">🔮</div>
                        <div>
                            <h3 class="font-display font-bold text-xl text-slate-900 dark:text-white">Office Mood Oracle</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Channeling the collective HR energy</p>
                        </div>
                    </div>
                    <div id="oracle-output" class="text-center py-8 text-4xl select-none">🔮</div>
                    <div id="oracle-text" class="text-center text-sm text-slate-500 dark:text-slate-400 mb-6 min-h-[3em]">Click to consult the oracle...</div>
                    <button onclick="consultOracle()" class="w-full py-3 rounded-xl bg-fuchsia-500/15 hover:bg-fuchsia-500/25 border border-fuchsia-500/25 text-fuchsia-600 dark:text-fuchsia-400 font-semibold text-sm transition-all cursor-pointer">
                        ✨ Consult Oracle
                    </button>
                </div>

                <!-- Random HR quote generator -->
                <div class="glass glow-ring rounded-3xl p-8 reveal" style="transition-delay:0.2s">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="w-10 h-10 rounded-xl bg-amber-500/15 flex items-center justify-center text-xl">💬</div>
                        <div>
                            <h3 class="font-display font-bold text-xl text-slate-900 dark:text-white">Official HR Quote Generator</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Professionally meaningless wisdom on demand</p>
                        </div>
                    </div>
                    <div class="bg-slate-100 dark:bg-brand-900/40 rounded-2xl p-5 mb-5 min-h-[5rem] flex items-center justify-center">
                        <p id="hr-quote" class="text-slate-700 dark:text-slate-300 text-sm italic text-center leading-relaxed">
                            "Synergy is achieved when spreadsheets are aligned with the lunar calendar."
                        </p>
                    </div>
                    <button onclick="generateQuote()" class="w-full py-3 rounded-xl bg-amber-500/15 hover:bg-amber-500/25 border border-amber-500/25 text-amber-600 dark:text-amber-400 font-semibold text-sm transition-all cursor-pointer">
                        🎲 New Quote
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- ───────────────────────── CTA BANNER ───────────────────────── -->
    <section class="relative z-10 py-20 px-6">
        <div class="max-w-4xl mx-auto reveal">
            <div class="relative glass glow-ring rounded-3xl p-12 text-center overflow-hidden">
                <!-- Shimmer border top -->
                <div class="absolute top-0 left-0 right-0 h-[1px] shimmer-border"></div>
                <div class="absolute top-0 right-0 w-64 h-64 bg-brand-500/10 rounded-full blur-3xl pointer-events-none"></div>
                <div class="absolute bottom-0 left-0 w-48 h-48 bg-cyan-500/10 rounded-full blur-3xl pointer-events-none"></div>

                <div class="relative z-10">
                    <div class="text-5xl mb-6">🚀</div>
                    <h2 class="font-display font-extrabold text-3xl md:text-5xl text-slate-900 dark:text-white mb-4">
                        Ready to <span class="grad">Get to Work?</span>
                    </h2>
                    <p class="text-slate-600 dark:text-slate-400 text-lg mb-8 max-w-xl mx-auto">
                        The Filament panel awaits. Employees won't manage themselves, biometrics won't scan themselves, and letters definitely won't write themselves.
                    </p>
                    <a href="{{ url('/kamkaj/login') }}" class="inline-flex items-center gap-3 px-10 py-5 rounded-2xl bg-gradient-to-r from-brand-600 to-cyan-500 hover:from-brand-500 hover:to-cyan-400 text-white font-bold text-lg shadow-2xl shadow-brand-500/30 hover:shadow-brand-500/50 hover:-translate-y-1 transition-all duration-300">
                        Enter Kamkaj Panel
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" /></svg>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- ───────────────────────── FOOTER ───────────────────────── -->
    <footer class="relative z-10 border-t border-slate-200 dark:border-white/5 bg-white/20 dark:bg-black/20 py-10 px-6">
        <div class="max-w-6xl mx-auto flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-xl bg-gradient-to-tr from-brand-600 to-cyan-500 flex items-center justify-center text-white font-bold text-sm">K</div>
                <span class="font-display font-bold text-lg grad">kamkaj</span>
                <span class="text-slate-400 dark:text-slate-600">·</span>
                <span class="text-xs text-slate-500 dark:text-slate-500">Waldo HQ Internal Portal</span>
            </div>
            <div class="text-xs text-slate-500 dark:text-slate-500 text-center">
                &copy; {{ date('Y') }} Waldo Resort &amp; Casino. Built with Laravel, Filament &amp; a lot of Google Sheets faith.
            </div>
            <div class="font-mono text-xs text-slate-400 dark:text-slate-600">php 8.4 · filament 5</div>
        </div>
    </footer>

    <!-- ───────────────────────── PANIC SCREEN ───────────────────────── -->
    <div id="panic-screen" class="fixed inset-0 bg-white text-slate-800 font-sans overflow-auto" style="font-size:13px;">
        <!-- Fake Windows taskbar -->
        <div class="sticky top-0 bg-gray-200 border-b border-gray-300 px-2 py-1 flex items-center gap-2 z-10">
            <div class="flex items-center gap-1 bg-white border border-gray-300 rounded px-2 py-0.5 text-xs text-green-800 font-bold">
                <span>📗</span> Microsoft Excel
            </div>
            <div class="ml-auto flex items-center gap-1">
                <button onclick="hidePanic()" class="px-3 py-0.5 text-xs bg-gray-100 hover:bg-gray-300 border border-gray-300 rounded cursor-pointer">✕ Close</button>
            </div>
        </div>
        <!-- Fake ribbon -->
        <div class="bg-slate-100 border-b border-gray-300 px-4 py-1 flex items-center gap-4 text-xs text-slate-600 overflow-x-auto">
            <span class="text-green-800 font-semibold">Q2_Waldo_Finance_Draft_FINAL_v3.xlsx</span>
            <span>·</span>
            <span>File</span><span>Home</span><span>Insert</span><span>Data</span><span>Review</span>
        </div>
        <div class="p-4">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-green-700 text-white">
                        <th class="border border-gray-300 p-2 w-8">#</th>
                        <th class="border border-gray-300 p-2">Department</th>
                        <th class="border border-gray-300 p-2">Q2 Budget</th>
                        <th class="border border-gray-300 p-2">Actual Spent</th>
                        <th class="border border-gray-300 p-2">Variance</th>
                        <th class="border border-gray-300 p-2">Approval Status</th>
                        <th class="border border-gray-300 p-2">Notes</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <tr class="hover:bg-blue-50"><td class="border border-gray-300 p-2">1</td><td class="border border-gray-300 p-2">Marketing</td><td class="border border-gray-300 p-2">NPR 45,000</td><td class="border border-gray-300 p-2">NPR 47,200</td><td class="border border-gray-300 p-2 text-red-600 font-bold">-2,200</td><td class="border border-gray-300 p-2">Pending</td><td class="border border-gray-300 p-2 text-slate-400">Awaiting GM sign-off</td></tr>
                    <tr class="hover:bg-blue-50"><td class="border border-gray-300 p-2">2</td><td class="border border-gray-300 p-2">Surveillance</td><td class="border border-gray-300 p-2">NPR 120,000</td><td class="border border-gray-300 p-2">NPR 115,000</td><td class="border border-gray-300 p-2 text-green-600 font-bold">+5,000</td><td class="border border-gray-300 p-2">Approved ✓</td><td class="border border-gray-300 p-2 text-slate-400">Under budget!</td></tr>
                    <tr class="hover:bg-blue-50"><td class="border border-gray-300 p-2">3</td><td class="border border-gray-300 p-2">IT & Development</td><td class="border border-gray-300 p-2">NPR 85,000</td><td class="border border-gray-300 p-2">NPR 84,500</td><td class="border border-gray-300 p-2 text-green-600 font-bold">+500</td><td class="border border-gray-300 p-2">Approved ✓</td><td class="border border-gray-300 p-2 text-slate-400">Laravel licenses</td></tr>
                    <tr class="hover:bg-blue-50"><td class="border border-gray-300 p-2">4</td><td class="border border-gray-300 p-2">Gaming Operations</td><td class="border border-gray-300 p-2">NPR 350,000</td><td class="border border-gray-300 p-2">NPR 362,000</td><td class="border border-gray-300 p-2 text-red-600 font-bold">-12,000</td><td class="border border-gray-300 p-2">In Review</td><td class="border border-gray-300 p-2 text-slate-400">See attached notes</td></tr>
                    <tr class="hover:bg-blue-50"><td class="border border-gray-300 p-2">5</td><td class="border border-gray-300 p-2">Human Resources</td><td class="border border-gray-300 p-2">NPR 30,000</td><td class="border border-gray-300 p-2">NPR 29,800</td><td class="border border-gray-300 p-2 text-green-600 font-bold">+200</td><td class="border border-gray-300 p-2">Approved ✓</td><td class="border border-gray-300 p-2 text-slate-400">Coffee budget saved</td></tr>
                    <tr class="hover:bg-blue-50"><td class="border border-gray-300 p-2">6</td><td class="border border-gray-300 p-2">Cage</td><td class="border border-gray-300 p-2">NPR 200,000</td><td class="border border-gray-300 p-2">NPR 198,500</td><td class="border border-gray-300 p-2 text-green-600 font-bold">+1,500</td><td class="border border-gray-300 p-2">Approved ✓</td><td class="border border-gray-300 p-2 text-slate-400">-</td></tr>
                    <tr class="hover:bg-blue-50"><td class="border border-gray-300 p-2">7</td><td class="border border-gray-300 p-2">Food & Beverage</td><td class="border border-gray-300 p-2">NPR 95,000</td><td class="border border-gray-300 p-2">NPR 101,000</td><td class="border border-gray-300 p-2 text-red-600 font-bold">-6,000</td><td class="border border-gray-300 p-2">Pending</td><td class="border border-gray-300 p-2 text-slate-400">Snack inflation</td></tr>
                </tbody>
            </table>
            <div class="mt-8 text-center text-slate-400 text-xs py-4">
                (This is just a panic screen. Your boss probably isn't even here. Press ✕ to go back.)
            </div>
        </div>
    </div>

    <!-- ───────────────────────── SCRIPTS ───────────────────────── -->
    <script>
        // ── Theme ──────────────────────────────────────────────────
        const html = document.documentElement;
        const iconSun  = document.getElementById('icon-sun');
        const iconMoon = document.getElementById('icon-moon');

        function applyTheme(t) {
            const dark = t === 'dark' || (t !== 'light' && window.matchMedia('(prefers-color-scheme: dark)').matches);
            html.classList.toggle('dark', dark);
            iconSun.classList.toggle('hidden', !dark);
            iconMoon.classList.toggle('hidden', dark);
        }
        applyTheme(localStorage.getItem('theme') || 'system');
        document.getElementById('theme-toggle').addEventListener('click', () => {
            const next = html.classList.contains('dark') ? 'light' : 'dark';
            localStorage.setItem('theme', next);
            applyTheme(next);
        });

        // ── Cursor glow ─────────────────────────────────────────────
        const cursorGlow = document.getElementById('cursor-glow');
        document.addEventListener('mousemove', e => {
            cursorGlow.style.left = e.clientX + 'px';
            cursorGlow.style.top  = e.clientY + 'px';
        });

        // ── Navbar scroll ──────────────────────────────────────────
        const navbar = document.getElementById('navbar');
        window.addEventListener('scroll', () => {
            if (window.scrollY > 60) {
                navbar.classList.add('glass', 'border-b', 'border-slate-200/50', 'dark:border-white/5');
            } else {
                navbar.classList.remove('glass', 'border-b', 'border-slate-200/50', 'dark:border-white/5');
            }
        }, { passive: true });

        // ── Live Clock ─────────────────────────────────────────────
        function updateClock() {
            document.getElementById('live-clock').textContent = new Date().toLocaleTimeString('en-US', { hour12: false });
        }
        updateClock();
        setInterval(updateClock, 1000);

        // ── Scroll Reveal ──────────────────────────────────────────
        const revealObserver = new IntersectionObserver(entries => {
            entries.forEach(e => { if (e.isIntersecting) e.target.classList.add('visible'); });
        }, { threshold: 0.1 });
        document.querySelectorAll('.reveal').forEach(el => revealObserver.observe(el));

        // ── Animated Counters ──────────────────────────────────────
        const counterObserver = new IntersectionObserver(entries => {
            entries.forEach(entry => {
                if (!entry.isIntersecting) return;
                const el = entry.target;
                const end = parseInt(el.dataset.count, 10);
                if (isNaN(end)) return;
                let start = 0, duration = 1600;
                const step = timestamp => {
                    if (!start) start = timestamp;
                    const progress = Math.min((timestamp - start) / duration, 1);
                    el.textContent = Math.floor(progress * end);
                    if (progress < 1) requestAnimationFrame(step);
                    else el.textContent = end;
                };
                requestAnimationFrame(step);
                counterObserver.unobserve(el);
            });
        }, { threshold: 0.5 });
        document.querySelectorAll('[data-count]').forEach(el => counterObserver.observe(el));

        // ── Particle Canvas ────────────────────────────────────────
        (function() {
            const canvas = document.getElementById('particle-canvas');
            const ctx = canvas.getContext('2d');
            let W, H, particles = [];
            const N = 55;

            function resize() {
                W = canvas.width = window.innerWidth;
                H = canvas.height = window.innerHeight;
            }
            resize();
            window.addEventListener('resize', resize, { passive: true });

            function rand(a, b) { return a + Math.random() * (b - a); }

            for (let i = 0; i < N; i++) {
                particles.push({ x: rand(0,1), y: rand(0,1), r: rand(0.5,2), vx: rand(-0.1,0.1), vy: rand(-0.12,-0.02), alpha: rand(0.2, 0.6) });
            }

            function draw() {
                ctx.clearRect(0, 0, W, H);
                const isDark = document.documentElement.classList.contains('dark');
                particles.forEach(p => {
                    p.x += p.vx / W;
                    p.y += p.vy / H;
                    if (p.y < -0.01) p.y = 1.02;
                    if (p.x < 0) p.x = 1; if (p.x > 1) p.x = 0;
                    ctx.beginPath();
                    ctx.arc(p.x * W, p.y * H, p.r, 0, Math.PI * 2);
                    ctx.fillStyle = isDark ? `rgba(167,139,250,${p.alpha})` : `rgba(109,40,217,${p.alpha * 0.4})`;
                    ctx.fill();
                });
                requestAnimationFrame(draw);
            }
            draw();
        })();

        // ── Panic Mode ─────────────────────────────────────────────
        function triggerPanic() {
            document.getElementById('panic-screen').style.display = 'block';
            document.body.style.overflow = 'hidden';
        }
        function hidePanic() {
            document.getElementById('panic-screen').style.display = 'none';
            document.body.style.overflow = '';
        }
        document.addEventListener('keydown', e => { if (e.key === 'Escape') hidePanic(); });

        // ── Productivity Simulator ─────────────────────────────────
        const tasks = [
            "Compiling biometric scans into binary streams...",
            "Running garbage collector for pending candidates... OK",
            "Syncing Google Sheets with 100% pure optimism... ✓",
            "Aligning department coordinates in spacetime continuum...",
            "Drafting official letter that could have been an email... Done.",
            "Auto-incrementing employee codes with CWD prefix... ✓",
            "Pinging Waldo local servers... Response: 4ms. 👌",
            "Writing PHP 8.4 constructor promotions... Promoted.",
            "Running vendor/bin/pint --dirty... Code is immaculate.",
            "Checking if boss is watching... Status: Unknown. Proceed.",
            "Resolving merge conflict in Q2_Final_v7_REAL_FINAL.xlsx...",
            "Scheduling biometric enrollment for 12 pending joinees...",
            "Querying department count... 20 departments found.",
            "Generating appointment letter with fancy template... 🖨️",
        ];
        const consoleEl = document.getElementById('work-console');
        function simulateWork() {
            const t = tasks[Math.floor(Math.random() * tasks.length)];
            const ts = new Date().toLocaleTimeString('en-US', { hour12: false });
            const line = document.createElement('div');
            line.innerHTML = `<span class="text-slate-500">[${ts}]</span> ${t}`;
            consoleEl.appendChild(line);
            consoleEl.scrollTop = consoleEl.scrollHeight;
        }
        function clearConsole() {
            consoleEl.innerHTML = '<span class="text-slate-500">[System]</span> Console cleared. Ready for more fake work.';
        }

        // ── Fingerprint Scanner ────────────────────────────────────
        let scanTimer = null, scanProgress = 0;
        const fpStatus = document.getElementById('fp-status');
        const fpScanLine = document.getElementById('fp-scanline');
        const names = ['Shuraz', 'Saugat', 'Suraj Raj', 'Bikash Thapa', 'Alina Rai', 'Mr. Unknown', 'Gyan Tamang', 'HR Bhaiya'];

        function startScan() {
            fpStatus.textContent = 'Scanning... keep holding.';
            fpStatus.className = 'text-sm font-mono text-cyan-500 text-center min-h-[2em]';
            fpScanLine.style.opacity = '1';
            fpScanLine.style.animation = 'none';
            fpScanLine.style.top = '0';
            scanProgress = 0;
            scanTimer = setInterval(() => {
                scanProgress += 5;
                const pct = Math.min(scanProgress, 100);
                fpScanLine.style.top = pct + '%';
                if (pct >= 100) {
                    stopScan(true);
                }
            }, 60);
        }
        function stopScan(complete) {
            clearInterval(scanTimer);
            fpScanLine.style.opacity = '0';
            if (complete) {
                const name = names[Math.floor(Math.random() * names.length)];
                fpStatus.innerHTML = `✅ <strong>${name}</strong> identified.<br><span class="text-xs text-emerald-400">Access granted to Waldo HQ.</span>`;
                fpStatus.className = 'text-sm font-mono text-emerald-500 text-center min-h-[2em]';
            } else {
                fpStatus.textContent = 'Scan interrupted. Place finger properly.';
                fpStatus.className = 'text-sm font-mono text-slate-500 dark:text-slate-400 text-center min-h-[2em]';
            }
        }

        // ── Oracle ─────────────────────────────────────────────────
        const oracles = [
            ['🌟', 'Today is a great day to approve pending leave requests.'],
            ['💀', 'Someone forgot to sync their Google Sheet. It is not looking good.'],
            ['🤑', 'Payroll is fine. Probably. Ask Accounts.'],
            ['🎉', 'A candidate will be promoted to "Contacted" today. Big milestone.'],
            ['☕', 'The oracle senses low caffeine levels. Remedy immediately.'],
            ['🧨', 'Warning: a letter template is about to be misused in a meeting.'],
            ['🌈', 'Departmental harmony is at 73%. Still better than last quarter.'],
            ['🕵️', 'Someone in Surveillance is watching. They are always watching.'],
            ['🦆', 'The biometric device in corridor 2 is feeling unappreciated.'],
            ['🍕', 'The oracle predicts Friday pizza. Maintain focus until then.'],
        ];
        function consultOracle() {
            const [emoji, text] = oracles[Math.floor(Math.random() * oracles.length)];
            document.getElementById('oracle-output').textContent = emoji;
            document.getElementById('oracle-text').textContent = `"${text}"`;
        }

        // ── HR Quotes ──────────────────────────────────────────────
        const quotes = [
            '"Synergy is achieved when spreadsheets are aligned with the lunar calendar."',
            '"Our greatest asset is our people. Our second greatest asset is a functioning printer."',
            '"Biometric enrollment is not optional. Neither is breathing."',
            '"In this department, we do not have problems. We have KPIs with room for improvement."',
            '"The onboarding journey begins with the fingerprint and ends with the ID card."',
            '"Think of the org chart as a family tree, except everyone reports to a Google Sheet."',
            '"Communication is key. So is filling out the correct form."',
            '"A candidate who does not follow up is a candidate we do not follow up with."',
            '"Remember: the letter template is the beginning of a professional relationship."',
            '"Alignment does not happen by accident. It happens because we track it in a database."',
        ];
        function generateQuote() {
            document.getElementById('hr-quote').textContent = quotes[Math.floor(Math.random() * quotes.length)];
        }
    </script>
</body>
</html>
