@php
    use Filament\Support\Colors\ColorManager;

    // Read the exact same primary color palette Filament uses for the panel.
    // When someone changes Color::Amber → Color::Violet in KamkajPanelProvider,
    // this page automatically picks up the new palette — zero manual work needed.
    $colorManager = app(ColorManager::class);
    $primary = $colorManager->getColor('primary');
    $gray = $colorManager->getColor('gray');
    $shades = [50, 100, 200, 300, 400, 500, 600, 700, 800, 900, 950];
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kamkaj | Waldo HQ Operations</title>
    <meta name="description"
        content="Kamkaj — the internal command center for Waldo. Where employees are managed, fingers are scanned, and letters get written.">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,300;0,14..32,400;0,14..32,500;0,14..32,600;0,14..32,700&family=Space+Grotesk:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap"
        rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- ─────────────────────────────────────────────────────────────────────────
         Inject Filament's exact primary color palette as CSS custom properties.
         These values come directly from the panel provider, so changing
         Color::Amber to Color::Violet there will cascade here automatically.
    ──────────────────────────────────────────────────────────────────────────── --}}
    <style>
        :root {
            @foreach ($shades as $shade)
                --p-{{ $shade }}: {{ $primary[$shade] ?? 'oklch(0.769 0.188 70.08)' }};
            @endforeach
            /* Alias set for convenience */
            --primary: var(--p-500);
            --primary-lt: var(--p-400);
            --primary-dk: var(--p-600);
            --primary-bg: var(--p-50);
            --primary-bg-dk: color-mix(in oklch, var(--p-500) 12%, transparent);
            --primary-border: color-mix(in oklch, var(--p-500) 30%, transparent);
        }

        /* ── Scrollbar ─────────────────────────────────────────────────────── */
        ::-webkit-scrollbar {
            width: 5px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: color-mix(in oklch, var(--primary) 40%, transparent);
            border-radius: 99px;
        }

        /* ── Typography ────────────────────────────────────────────────────── */
        body {
            font-family: 'Inter', sans-serif;
        }

        .font-display {
            font-family: 'Space Grotesk', sans-serif;
        }

        .font-mono {
            font-family: 'JetBrains Mono', monospace;
        }

        /* ── Glassmorphism ─────────────────────────────────────────────────── */
        .glass {
            background: rgba(255, 255, 255, 0.5);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(0, 0, 0, 0.06);
        }

        .dark .glass {
            background: rgba(8, 5, 20, 0.55);
            border: 1px solid rgba(255, 255, 255, 0.06);
        }

        /* ── Primary-aware utilities ───────────────────────────────────────── */
        .grad {
            background: linear-gradient(135deg, var(--p-500), var(--p-400), color-mix(in oklch, var(--p-500) 60%, oklch(0.72 0.2 200)));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .grad-btn {
            background: linear-gradient(135deg, var(--p-600), var(--p-500));
        }

        .grad-btn:hover {
            background: linear-gradient(135deg, var(--p-500), var(--p-400));
        }

        .p-glow {
            box-shadow: 0 0 30px color-mix(in oklch, var(--p-500) 20%, transparent);
        }

        .p-ring {
            box-shadow: 0 0 0 1px var(--primary-border), 0 0 20px color-mix(in oklch, var(--p-500) 10%, transparent);
        }

        .dark .p-ring {
            box-shadow: 0 0 0 1px color-mix(in oklch, var(--p-500) 25%, transparent), 0 0 30px color-mix(in oklch, var(--p-500) 12%, transparent);
        }

        .p-text {
            color: var(--p-500);
        }

        .dark .p-text {
            color: var(--p-400);
        }

        .p-bg {
            background: var(--primary-bg-dk);
        }

        .p-border {
            border-color: var(--primary-border);
        }

        /* ── Blobs ─────────────────────────────────────────────────────────── */
        .blob {
            filter: blur(90px);
            will-change: transform;
            animation: blobAnim var(--dur, 9s) ease-in-out infinite;
        }

        @keyframes blobAnim {

            0%,
            100% {
                transform: translate(0, 0) scale(1);
            }

            33% {
                transform: translate(var(--tx, 35px), var(--ty, -45px)) scale(1.07);
            }

            66% {
                transform: translate(calc(-1 * var(--tx, 35px)), var(--ty2, 22px)) scale(0.93);
            }
        }

        /* ── Primary blob color ────────────────────────────────────────────── */
        .blob-p {
            background: var(--p-500);
        }

        /* ── Marquee ───────────────────────────────────────────────────────── */
        .marquee-wrap {
            overflow: hidden;
        }

        .marquee-track {
            display: flex;
            gap: 3rem;
            white-space: nowrap;
            animation: marquee var(--dur, 30s) linear infinite;
        }

        @keyframes marquee {
            from {
                transform: translateX(0);
            }

            to {
                transform: translateX(-50%);
            }
        }

        /* ── Module card ───────────────────────────────────────────────────── */
        .m-card {
            transition: transform 0.35s cubic-bezier(.22, 1, .36, 1), box-shadow 0.35s ease;
        }

        .m-card:hover {
            transform: translateY(-7px);
        }

        .dark .m-card:hover {
            box-shadow: 0 20px 60px color-mix(in oklch, var(--p-500) 12%, transparent);
        }

        /* ── Timeline dot ──────────────────────────────────────────────────── */
        .tl-dot {
            background: var(--p-500);
            box-shadow: 0 0 12px color-mix(in oklch, var(--p-500) 50%, transparent);
        }

        /* ── Shimmer border ────────────────────────────────────────────────── */
        .shimmer-border {
            background: linear-gradient(90deg, transparent, color-mix(in oklch, var(--p-500) 40%, transparent), transparent);
            background-size: 200% 100%;
            animation: shimmer 3s ease infinite;
        }

        @keyframes shimmer {
            from {
                background-position: -200% 0;
            }

            to {
                background-position: 200% 0;
            }
        }

        /* ── Neon text ─────────────────────────────────────────────────────── */
        .neon {
            text-shadow: 0 0 20px color-mix(in oklch, var(--p-500) 40%, transparent);
        }

        .dark .neon {
            text-shadow: 0 0 30px color-mix(in oklch, var(--p-500) 60%, transparent), 0 0 60px color-mix(in oklch, var(--p-500) 25%, transparent);
        }

        /* ── Scroll reveal ─────────────────────────────────────────────────── */
        .reveal {
            opacity: 0;
            transform: translateY(28px);
            transition: opacity 0.65s ease, transform 0.65s ease;
        }

        .reveal.visible {
            opacity: 1;
            transform: translateY(0);
        }


        /* ── Timeline connector ────────────────────────────────────────────── */
        .tl-connector {
            background: linear-gradient(to bottom, var(--p-500), color-mix(in oklch, var(--p-500) 20%, transparent));
        }

        /* ── Floating badge ────────────────────────────────────────────────── */
        .badge-p {
            background: color-mix(in oklch, var(--p-500) 10%, transparent);
            border-color: color-mix(in oklch, var(--p-500) 30%, transparent);
            color: var(--p-600);
        }

        .dark .badge-p {
            color: var(--p-300);
        }

        /* ── Skill bar ─────────────────────────────────────────────────────── */
        .skill-bar {
            background: var(--p-500);
        }

        /* ── Particle canvas ───────────────────────────────────────────────── */
        #particles {
            position: fixed;
            inset: 0;
            z-index: 0;
            pointer-events: none;
        }

        /* ── Cursor glow ───────────────────────────────────────────────────── */
        #cursor-glow {
            position: fixed;
            width: 400px;
            height: 400px;
            border-radius: 50%;
            background: radial-gradient(circle, color-mix(in oklch, var(--p-500) 6%, transparent) 0%, transparent 70%);
            pointer-events: none;
            transform: translate(-50%, -50%);
            z-index: 1;
        }

        .dark #cursor-glow {
            background: radial-gradient(circle, color-mix(in oklch, var(--p-500) 12%, transparent) 0%, transparent 70%);
        }

        /* ── Testimonial card hover ────────────────────────────────────────── */
        .t-card:hover {
            border-color: var(--primary-border) !important;
        }

        /* ── FAQ accordion ─────────────────────────────────────────────────── */
        .faq-body {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.4s ease, padding 0.3s ease;
        }

        .faq-body.open {
            max-height: 300px;
        }

        .faq-chevron {
            transition: transform 0.3s ease;
        }

        .faq-chevron.open {
            transform: rotate(180deg);
        }
    </style>
</head>

<body
    class="bg-slate-50 dark:bg-[oklch(0.07_0.01_270)] text-slate-800 dark:text-slate-100 min-h-screen overflow-x-hidden antialiased transition-colors duration-300">

    <!-- Cursor glow -->
    <div id="cursor-glow"></div>

    <!-- Particle canvas -->
    <canvas id="particles" aria-hidden="true" class="opacity-20 dark:opacity-50"></canvas>

    <!-- Blobs -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none select-none z-0" aria-hidden="true">
        <div class="blob blob-p absolute top-[-12%] left-[12%] w-[640px] h-[640px] rounded-full opacity-[0.12] dark:opacity-[0.25]"
            style="--dur:9s;--tx:40px;--ty:-50px;--ty2:28px;"></div>
        <div class="blob absolute top-[38%] right-[3%] w-[520px] h-[520px] rounded-full opacity-[0.08] dark:opacity-[0.18]"
            style="background:oklch(0.72 0.2 200);--dur:12s;--tx:-35px;--ty:-40px;--ty2:32px;"></div>
        <div class="blob blob-p absolute bottom-[3%] left-[5%] w-[480px] h-[480px] rounded-full opacity-[0.07] dark:opacity-[0.15]"
            style="--dur:14s;--tx:28px;--ty:-48px;--ty2:18px;"></div>
    </div>

    {{-- ═══════════════════════ NAVBAR ═══════════════════════ --}}
    <header id="navbar" class="w-full fixed top-0 left-0 z-50 transition-all duration-300">
        <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
            <!-- Logo -->
            <a href="#" class="flex items-center gap-3 group" aria-label="Kamkaj Home">
                <div
                    class="w-auto h-auto px-2 py-1 rounded-xl grad-btn flex items-center justify-center text-white font-display font-extrabold text-lg shadow-lg p-glow group-hover:scale-110 transition-all duration-300">
                    Waldo</div>
                <span class="font-display font-extrabold text-2xl leading-none grad">kamkaj</span>
            </a>

            <!-- Nav links -->
            <nav class="hidden lg:flex items-center gap-1">
                @foreach (['Home' => '#hero', 'Modules' => '#modules', 'How It Works' => '#how', 'FAQ' => '#faq'] as $label => $href)
                    <a href="{{ $href }}"
                        class="px-4 py-2 rounded-xl text-sm font-medium text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-white/5 transition-all">{{ $label }}</a>
                @endforeach
            </nav>

            <!-- Actions -->
            <div class="flex items-center gap-3">
                <button id="theme-toggle"
                    class="p-2.5 rounded-xl glass p-ring hover:scale-105 transition-all cursor-pointer"
                    title="Toggle theme">
                    <svg id="icon-sun" class="w-5 h-5 text-amber-500 hidden" fill="none" viewBox="0 0 24 24"
                        stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" />
                    </svg>
                    <svg id="icon-moon" class="w-5 h-5 text-slate-500 dark:text-slate-400 hidden" fill="none"
                        viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z" />
                    </svg>
                </button>
                <a href="{{ url('/kamkaj/login') }}"
                    class="flex items-center gap-2 px-5 py-2.5 rounded-xl grad-btn text-white font-bold text-sm shadow-lg p-glow hover:scale-105 hover:opacity-90 transition-all duration-200">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                    </svg>
                    Enter Panel
                </a>
            </div>
        </div>
    </header>

    {{-- ═══════════════════════ HERO ═══════════════════════ --}}
    <section id="hero"
        class="relative min-h-screen flex flex-col items-center justify-center pt-32 pb-24 px-6 z-10">

        <!-- Live pulse badge -->
        <div class="mb-8 animate-bounce" style="animation-duration:2.5s">
            <div
                class="flex items-center gap-2.5 px-5 py-2 rounded-full glass p-ring font-mono text-xs font-medium badge-p">
                <span class="w-2 h-2 rounded-full animate-pulse" style="background:var(--p-400)"></span>
                WALDO HQ OPERATIONS — LIVE
                <span class="text-slate-400">·</span>
                <span id="live-clock" class="tabular-nums text-slate-600 dark:text-slate-300">--:--:--</span>
            </div>
        </div>

        <!-- Headline -->
        <div class="max-w-5xl text-center space-y-6">
            <h1 class="text-5xl sm:text-7xl lg:text-8xl font-display font-extrabold tracking-tighter leading-[0.88]">
                <span class="block text-slate-900 dark:text-white">Centralized</span>
                <span class="grad neon block my-3 pb-2">HQ Operations</span>
            </h1>

            <p class="text-xl text-slate-600 dark:text-slate-400 max-w-2xl mx-auto leading-relaxed">
                Welcome to <strong class="p-text font-semibold">Kamkaj</strong> — the internal management portal for
                Waldo Resort & Casino. Streamline employee records, biometrics, recruitment tracking, and official
                documentation in one secure environment.
            </p>

            <!-- CTAs -->
            <div class="flex flex-wrap justify-center gap-4 pt-4">
                <a href="{{ url('/kamkaj/login') }}"
                    class="group flex items-center gap-3 px-8 py-4 rounded-2xl grad-btn text-white font-bold text-base shadow-2xl p-glow hover:-translate-y-1.5 transition-all duration-300">
                    Launch Command Center
                    <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none"
                        viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                    </svg>
                </a>
                <a href="#modules"
                    class="flex items-center gap-2 px-8 py-4 rounded-2xl glass p-ring text-slate-700 dark:text-slate-300 font-semibold text-base hover:-translate-y-1.5 transition-all duration-300">
                    Explore Modules ↓
                </a>
            </div>
        </div>

        <!-- Tech stack badges -->
        <div class="mt-14 flex flex-wrap justify-center gap-3">
            @foreach ([['✅', 'Sheets Syncing Live', 'emerald'], ['🧬', 'Biometrics Tracking', 'primary'], ['🏗️', 'PHP 8.4 · Laravel 13', 'blue'], ['⚡', 'Filament v5 Panel', 'primary'], ['☁️', 'Google Sheets API', 'sky']] as [$icon, $text, $color])
                @if ($color === 'primary')
                    <span
                        class="flex items-center gap-2 px-3 py-1.5 rounded-full glass badge-p border font-mono text-xs p-border">{{ $icon }}
                        {{ $text }}</span>
                @elseif($color === 'emerald')
                    <span
                        class="flex items-center gap-2 px-3 py-1.5 rounded-full glass text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20 font-mono text-xs">{{ $icon }}
                        {{ $text }}</span>
                @elseif($color === 'blue')
                    <span
                        class="flex items-center gap-2 px-3 py-1.5 rounded-full glass text-blue-600 dark:text-blue-400 border border-blue-200 dark:border-blue-500/20 font-mono text-xs">{{ $icon }}
                        {{ $text }}</span>
                @else
                    <span
                        class="flex items-center gap-2 px-3 py-1.5 rounded-full glass text-sky-600 dark:text-sky-400 border border-sky-200 dark:border-sky-500/20 font-mono text-xs">{{ $icon }}
                        {{ $text }}</span>
                @endif
            @endforeach
        </div>

        <!-- Scroll indicator -->
        <div class="absolute bottom-8 left-1/2 -translate-x-1/2 flex flex-col items-center gap-2 opacity-30">
            <span class="text-xs font-mono tracking-widest">SCROLL</span>
            <div class="w-0.5 h-10 animate-pulse"
                style="background: linear-gradient(to bottom, var(--p-400), transparent)"></div>
        </div>
    </section>

    {{-- ═══════════════════════ MARQUEE ═══════════════════════ --}}
    <div
        class="py-5 border-y border-slate-200 dark:border-white/5 bg-white/30 dark:bg-black/20 overflow-hidden relative z-10">
        <div class="marquee-wrap">
            <div class="marquee-track" style="--dur:28s;">
                @php $items = ['Employee Management', 'Biometric Allotments', 'Google Sheets Sync', 'Letter Forge', 'Candidate Tracker', 'CWD Auto-Increment', 'Filament v5 Admin', 'Real-time Sync']; @endphp
                @foreach (array_merge($items, $items) as $i => $item)
                    <span
                        class="font-display font-bold text-3xl {{ $i % 2 === 0 ? 'text-slate-200 dark:text-white/8' : '' }}"
                        @if ($i % 2 !== 0) style="-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;background:linear-gradient(135deg, var(--p-400), var(--p-600))" @endif>
                        {{ $item }}
                    </span>
                    <span class="text-slate-300 dark:text-white/10 text-2xl">◆</span>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ═══════════════════════ MODULES ═══════════════════════ --}}
    <section id="modules" class="relative z-10 py-32 px-6">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-20 reveal">
                <div
                    class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full glass p-ring badge-p border p-border text-xs font-semibold tracking-widest uppercase mb-6 font-mono">
                    🏢 Mission-Critical Modules
                </div>
                <h2 class="text-4xl md:text-6xl font-display font-extrabold text-slate-900 dark:text-white mb-4">
                    Core <span class="grad neon">Modules</span>
                </h2>
                <p class="text-lg text-slate-500 dark:text-slate-400 max-w-xl mx-auto">
                    A comprehensive suite of tools designed to optimize internal workflows and maintain data accuracy
                    across departments.
                </p>
            </div>

            <!-- 2-col main + 2 smaller -->
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">

                <!-- Employee — large -->
                <div class="m-card glass p-ring rounded-3xl p-8 group reveal xl:col-span-2">
                    <div class="flex items-start justify-between mb-6">
                        <div
                            class="w-16 h-16 rounded-2xl grad-btn flex items-center justify-center text-3xl shadow-xl p-glow group-hover:scale-110 transition-all duration-300">
                            👥</div>
                        <span
                            class="font-mono text-xs text-slate-400 dark:text-slate-500 bg-slate-100 dark:bg-white/5 px-3 py-1 rounded-full">Module
                            01</span>
                    </div>
                    <h3
                        class="font-display font-extrabold text-3xl mb-3 text-slate-900 dark:text-white group-hover:grad transition-all">
                        Employee Database</h3>
                    <p class="text-slate-600 dark:text-slate-400 leading-relaxed mb-6 text-base max-w-lg">
                        A centralized directory for all active personnel. Maintain accurate records of names,
                        departmental assignments, designations, joining dates, and essential health information.
                    </p>
                    <div class="grid grid-cols-2 gap-3 mb-6">
                        @foreach (['Name & Code', 'Department', 'Designation', 'Join Date', 'Blood Group', 'Biometric ID'] as $field)
                            <div class="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-400">
                                <span class="w-1.5 h-1.5 rounded-full"
                                    style="background:var(--p-400)"></span>{{ $field }}
                            </div>
                        @endforeach
                    </div>
                    <div class="flex items-center gap-2 text-xs font-mono" style="color:var(--p-500)">
                        <span class="w-2 h-2 rounded-full animate-pulse" style="background:var(--p-400)"></span>
                        Synced with Google Sheets · Live · Filament Resource
                    </div>
                </div>

                <!-- Biometric -->
                <div class="m-card glass p-ring rounded-3xl p-8 group reveal" style="transition-delay:.1s">
                    <div class="flex items-start justify-between mb-6">
                        <div
                            class="w-14 h-14 rounded-2xl bg-cyan-500/15 dark:bg-cyan-400/10 flex items-center justify-center text-2xl group-hover:scale-110 transition-all duration-300 border border-cyan-300/20">
                            🧬</div>
                        <span
                            class="font-mono text-xs text-slate-400 dark:text-slate-500 bg-slate-100 dark:bg-white/5 px-3 py-1 rounded-full">Module
                            02</span>
                    </div>
                    <h3
                        class="font-display font-extrabold text-2xl mb-3 text-slate-900 dark:text-white group-hover:text-cyan-500 dark:group-hover:text-cyan-300 transition-colors">
                        Biometric Allotments</h3>
                    <p class="text-slate-600 dark:text-slate-400 leading-relaxed mb-5 text-sm">
                        Manage fingerprint enrollments and device allocations seamlessly. Automatically generates
                        incremental employee codes with the <code class="font-mono text-xs px-1.5 py-0.5 rounded"
                            style="background:var(--primary-bg-dk);color:var(--p-500)">CWD</code> prefix for structured
                        identification.
                    </p>
                    <div class="flex items-center gap-2 text-xs font-mono text-cyan-500 dark:text-cyan-400">
                        <span class="w-2 h-2 rounded-full bg-cyan-400 animate-pulse"></span>
                        407 allotments synced · Two-way sync
                    </div>
                </div>

                <!-- Candidate -->
                <div class="m-card glass p-ring rounded-3xl p-8 group reveal" style="transition-delay:.15s">
                    <div class="flex items-start justify-between mb-6">
                        <div
                            class="w-14 h-14 rounded-2xl bg-fuchsia-500/15 dark:bg-fuchsia-400/10 flex items-center justify-center text-2xl group-hover:scale-110 transition-all duration-300 border border-fuchsia-300/20">
                            🎯</div>
                        <span
                            class="font-mono text-xs text-slate-400 dark:text-slate-500 bg-slate-100 dark:bg-white/5 px-3 py-1 rounded-full">Module
                            03</span>
                    </div>
                    <h3
                        class="font-display font-extrabold text-2xl mb-3 text-slate-900 dark:text-white group-hover:text-fuchsia-500 dark:group-hover:text-fuchsia-300 transition-colors">
                        Candidate Hunt</h3>
                    <p class="text-slate-600 dark:text-slate-400 leading-relaxed mb-5 text-sm">
                        Track the entire recruitment pipeline. Monitor application statuses, manage candidate contact
                        information, upload relevant documents, and record interview feedback systematically.
                    </p>
                    <div class="flex items-center gap-2 text-xs font-mono text-fuchsia-500 dark:text-fuchsia-400">
                        <span class="w-2 h-2 rounded-full bg-fuchsia-400 animate-pulse"></span>
                        Status pipeline · Photo uploads supported
                    </div>
                </div>

                <!-- Letter Forge -->
                <div class="m-card glass p-ring rounded-3xl p-8 group reveal xl:col-span-2"
                    style="transition-delay:.2s">
                    <div class="flex items-start justify-between mb-6">
                        <div
                            class="w-14 h-14 rounded-2xl bg-amber-500/15 dark:bg-amber-400/10 flex items-center justify-center text-2xl group-hover:scale-110 transition-all duration-300 border border-amber-300/20">
                            📝</div>
                        <span
                            class="font-mono text-xs text-slate-400 dark:text-slate-500 bg-slate-100 dark:bg-white/5 px-3 py-1 rounded-full">Module
                            04</span>
                    </div>
                    <h3
                        class="font-display font-extrabold text-2xl mb-3 text-slate-900 dark:text-white group-hover:text-amber-500 dark:group-hover:text-amber-300 transition-colors">
                        Letter Forge</h3>
                    <p class="text-slate-600 dark:text-slate-400 leading-relaxed mb-5 text-sm max-w-xl">
                        Streamline the creation of official documentation. Use predefined templates for appointment
                        letters, experience certificates, and internal memos with automated variable substitution for
                        accuracy.
                    </p>
                    <div class="flex flex-wrap gap-2 mb-5">
                        @foreach (['Appointment', 'Experience', 'Salary Certificate', 'Warning', 'Recommendation'] as $type)
                            <span
                                class="px-2.5 py-1 rounded-lg bg-amber-500/10 border border-amber-400/20 text-amber-600 dark:text-amber-400 text-xs font-mono">{{ $type }}</span>
                        @endforeach
                    </div>
                    <div class="flex items-center gap-2 text-xs font-mono text-amber-500 dark:text-amber-400">
                        <span class="w-2 h-2 rounded-full bg-amber-400 animate-pulse"></span>
                        PDF export · Custom template engine · Variable substitution
                    </div>
                </div>

            </div>
        </div>
    </section>

    {{-- ═══════════════════════ HOW IT WORKS ═══════════════════════ --}}
    <section id="how"
        class="relative z-10 py-28 px-6 bg-white/20 dark:bg-black/20 border-y border-slate-200 dark:border-white/5">
        <div class="max-w-5xl mx-auto">
            <div class="text-center mb-20 reveal">
                <div
                    class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full glass p-ring badge-p border p-border text-xs font-semibold tracking-widest uppercase mb-6 font-mono">
                    🔄 The Flow
                </div>
                <h2 class="text-4xl md:text-5xl font-display font-extrabold text-slate-900 dark:text-white mb-4">
                    Streamlined <span class="grad neon">Operations</span>
                </h2>
                <p class="text-slate-500 dark:text-slate-400 max-w-lg mx-auto">From manual tracking to an automated,
                    fully managed digital workflow.</p>
            </div>

            <div class="relative">
                <!-- Vertical connector -->
                <div
                    class="absolute left-6 md:left-1/2 top-0 bottom-0 w-0.5 tl-connector opacity-30 -translate-x-0.5 hidden md:block">
                </div>

                <div class="space-y-12">
                    @foreach ([['01', '📥', 'Data Entry', 'Records are created securely within the Filament administration panel, providing a structured and validated input method.'], ['02', '⚙️', 'Automated Processing', 'The system handles code generation (e.g., CWD0001) and formatting rules automatically, minimizing manual data entry errors.'], ['03', '☁️', 'Two-Way Sync', 'Integration with the Google Sheets API ensures changes in the panel are reflected in external reporting sheets in real-time.'], ['04', '📊', 'Reporting', 'Data is readily available for both internal panel review and external auditor requirements through synchronized spreadsheets.']] as $i => [$num, $icon, $title, $desc])
                        <div class="flex gap-8 items-start {{ $i % 2 !== 0 ? 'md:flex-row-reverse' : '' }} reveal"
                            style="transition-delay:{{ $i * 0.1 }}s">
                            <!-- Content -->
                            <div
                                class="flex-1 glass p-ring rounded-2xl p-6 {{ $i % 2 !== 0 ? 'md:text-right' : '' }}">
                                <div
                                    class="flex items-center gap-3 mb-3 {{ $i % 2 !== 0 ? 'md:flex-row-reverse' : '' }}">
                                    <span class="text-2xl">{{ $icon }}</span>
                                    <h3 class="font-display font-bold text-xl text-slate-900 dark:text-white">
                                        {{ $title }}</h3>
                                </div>
                                <p class="text-slate-600 dark:text-slate-400 text-sm leading-relaxed">
                                    {{ $desc }}</p>
                            </div>
                            <!-- Step dot -->
                            <div
                                class="flex-shrink-0 flex items-center justify-center w-12 h-12 rounded-full tl-dot text-white font-display font-bold text-sm shadow-lg p-glow">
                                {{ $num }}
                            </div>
                            <!-- Spacer for alternating layout -->
                            <div class="flex-1 hidden md:block"></div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>



    {{-- ═══════════════════════ FAQ ═══════════════════════ --}}
    <section id="faq"
        class="relative z-10 py-28 px-6 bg-white/20 dark:bg-black/20 border-y border-slate-200 dark:border-white/5">
        <div class="max-w-3xl mx-auto">
            <div class="text-center mb-16 reveal">
                <div
                    class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full glass p-ring badge-p border p-border text-xs font-semibold tracking-widest uppercase mb-6 font-mono">
                    ❓ FAQ
                </div>
                <h2 class="text-4xl md:text-5xl font-display font-extrabold text-slate-900 dark:text-white mb-4">
                    Frequently Asked <span class="grad neon">Questions</span>
                </h2>
                <p class="text-slate-500 dark:text-slate-400">Essential information about Kamkaj operations.</p>
            </div>

            <div class="space-y-3">
                @foreach ([['How are biometric codes generated?', 'The system automatically assigns sequential codes using the CWD prefix (e.g., CWD0001, CWD0002) based on the highest existing record, eliminating the need for manual tracking.'], ['How does the Google Sheets integration work?', 'The application uses the Google Sheets API to perform a two-way synchronization. Records created, updated, or deleted in the panel are immediately reflected in the designated reporting spreadsheet.'], ['Who can access the Kamkaj administration panel?', 'Access is strictly controlled via Filament Shield role-based permissions. Only authorized personnel can log in and view or modify operational data.'], ['Can the interface theme be modified?', 'Yes, the homepage design automatically synchronizes its color palette with the Filament admin panel. Changes to the primary theme color within the panel settings will dynamically update this page.']] as $i => [$q, $a])
                    <div class="glass p-ring rounded-2xl overflow-hidden reveal"
                        style="transition-delay:{{ $i * 0.07 }}s">
                        <button class="w-full flex items-center justify-between p-5 text-left cursor-pointer faq-btn"
                            onclick="toggleFaq(this)">
                            <span
                                class="font-semibold text-slate-900 dark:text-white text-sm pr-4">{{ $q }}</span>
                            <svg class="faq-chevron flex-shrink-0 w-5 h-5 text-slate-400" fill="none"
                                viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
                            </svg>
                        </button>
                        <div class="faq-body px-5">
                            <p class="text-slate-600 dark:text-slate-400 text-sm leading-relaxed pb-5">
                                {{ $a }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ═══════════════════════ CTA BANNER ═══════════════════════ --}}
    <section class="relative z-10 py-24 px-6">
        <div class="max-w-4xl mx-auto reveal">
            <div class="relative glass p-ring rounded-3xl p-14 text-center overflow-hidden">
                <div class="absolute top-0 left-0 right-0 h-[1px] shimmer-border"></div>
                <div class="absolute top-0 right-0 w-72 h-72 rounded-full blur-3xl opacity-20 pointer-events-none"
                    style="background:var(--p-500)"></div>
                <div class="absolute bottom-0 left-0 w-56 h-56 rounded-full blur-3xl opacity-10 pointer-events-none"
                    style="background:oklch(0.72 0.2 200)"></div>

                <div class="relative z-10">
                    <div class="text-6xl mb-6 animate-bounce" style="animation-duration:2s">🚀</div>
                    <h2 class="font-display font-extrabold text-3xl md:text-5xl text-slate-900 dark:text-white mb-4">
                        Ready to <span class="grad neon">Get to Work?</span>
                    </h2>
                    <p class="text-slate-600 dark:text-slate-400 text-lg mb-8 max-w-xl mx-auto">
                        Employees won't manage themselves, biometrics won't scan themselves, and letters definitely
                        won't write themselves. Mostly.
                    </p>
                    <div class="flex flex-wrap justify-center gap-4">
                        <a href="{{ url('/kamkaj/login') }}"
                            class="inline-flex items-center gap-3 px-10 py-4 rounded-2xl grad-btn text-white font-bold text-lg shadow-2xl p-glow hover:-translate-y-1.5 hover:opacity-90 transition-all duration-300">
                            Enter Kamkaj Panel
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke-width="2"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                            </svg>
                        </a>
                        <button onclick="triggerPanic()"
                            class="inline-flex items-center gap-2 px-8 py-4 rounded-2xl bg-rose-500/10 hover:bg-rose-500/20 border border-rose-400/30 text-rose-600 dark:text-rose-400 font-bold text-base hover:-translate-y-1.5 transition-all cursor-pointer">
                            🚨 Boss is Coming!
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ═══════════════════════ FOOTER ═══════════════════════ --}}
    <footer
        class="relative z-10 border-t border-slate-200 dark:border-white/5 bg-white/20 dark:bg-black/20 py-12 px-6">
        <div class="max-w-7xl mx-auto">
            <div class="flex flex-col md:flex-row items-start justify-between gap-8 mb-10">
                <!-- Brand -->
                <div>
                    <div class="flex items-center gap-3 mb-3">
                        <div
                            class="w-9 h-9 rounded-xl grad-btn flex items-center justify-center text-white font-bold text-sm">
                            K</div>
                        <span class="font-display font-extrabold text-xl grad">kamkaj</span>
                    </div>
                    <p class="text-sm text-slate-500 dark:text-slate-400 max-w-xs">The internal operations portal for
                        Waldo Resort & Casino. Not for external use. (You are probably already internal.)</p>
                </div>
                <!-- Links -->
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-8 text-sm">
                    <div>
                        <h4 class="font-semibold text-slate-900 dark:text-white mb-3">Modules</h4>
                        <ul class="space-y-2 text-slate-500 dark:text-slate-400">
                            <li>Employees</li>
                            <li>Biometrics</li>
                            <li>Candidates</li>
                            <li>Letters</li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="font-semibold text-slate-900 dark:text-white mb-3">Tech Stack</h4>
                        <ul class="space-y-2 text-slate-500 dark:text-slate-400">
                            <li>Laravel 13</li>
                            <li>Filament v5</li>
                            <li>PHP 8.4</li>
                            <li>Google Sheets API</li>
                        </ul>
                    </div>
                    <div>
                        <h4 class="font-semibold text-slate-900 dark:text-white mb-3">Quick Links</h4>
                        <ul class="space-y-2">
                            <li><a href="{{ url('/kamkaj/login') }}"
                                    class="text-slate-500 dark:text-slate-400 hover:p-text transition-colors">Login
                                    Panel</a></li>
                            <li><a href="#faq"
                                    class="text-slate-500 dark:text-slate-400 hover:p-text transition-colors">FAQ</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div
                class="pt-8 border-t border-slate-200 dark:border-white/5 flex flex-col sm:flex-row items-center justify-between gap-4">
                <p class="text-xs text-slate-500 dark:text-slate-400">&copy; {{ date('Y') }} Waldo Kamkaj. Built
                    with ❤️ by <a href="https://instagram.com/0xyzone" target="_blank"
                        class="font-bold text-xl hover:cursor-pointer transform hover:scale-105 duration-300">OxyZone</a>
                </p>
                <p class="font-mono text-xs text-slate-400 dark:text-slate-600">php 8.4 · laravel 13 · filament 5</p>
            </div>
        </div>
    </footer>


    {{-- ═══════════════════════ SCRIPTS ═══════════════════════ --}}
    <script>
        // ── Theme ───────────────────────────────────────────────────────────────
        const html = document.documentElement;
        const iconSun = document.getElementById('icon-sun');
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

        // ── Cursor glow ─────────────────────────────────────────────────────────
        const cursorGlow = document.getElementById('cursor-glow');
        let mx = -999,
            my = -999;
        document.addEventListener('mousemove', e => {
            mx = e.clientX;
            my = e.clientY;
            cursorGlow.style.left = mx + 'px';
            cursorGlow.style.top = my + 'px';
        }, {
            passive: true
        });

        // ── Navbar scroll glass ──────────────────────────────────────────────────
        const navbar = document.getElementById('navbar');
        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) {
                navbar.style.cssText =
                    'backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);background:rgba(255,255,255,0.5);border-bottom:1px solid rgba(0,0,0,0.06);';
                if (html.classList.contains('dark')) navbar.style.background = 'rgba(8,5,20,0.6)';
            } else {
                navbar.style.cssText = '';
            }
        }, {
            passive: true
        });

        // ── Live Clock ───────────────────────────────────────────────────────────
        const clockEl = document.getElementById('live-clock');

        function tick() {
            clockEl.textContent = new Date().toLocaleTimeString('en-US', {
                hour12: false
            });
        }
        tick();
        setInterval(tick, 1000);

        // ── Scroll Reveal ────────────────────────────────────────────────────────
        const ro = new IntersectionObserver(entries => {
            entries.forEach(e => {
                if (e.isIntersecting) e.target.classList.add('visible');
            });
        }, {
            threshold: 0.08
        });
        document.querySelectorAll('.reveal').forEach(el => ro.observe(el));

        // ── Animated Counters ────────────────────────────────────────────────────
        const co = new IntersectionObserver(entries => {
            entries.forEach(({
                isIntersecting,
                target
            }) => {
                if (!isIntersecting) return;
                const end = parseInt(target.dataset.count, 10);
                if (isNaN(end)) return;
                let start = null;
                const step = ts => {
                    if (!start) start = ts;
                    const p = Math.min((ts - start) / 1600, 1);
                    target.textContent = Math.floor(p * end);
                    if (p < 1) requestAnimationFrame(step);
                    else target.textContent = end;
                };
                requestAnimationFrame(step);
                co.unobserve(target);
            });
        }, {
            threshold: 0.5
        });
        document.querySelectorAll('[data-count]').forEach(el => co.observe(el));

        // ── Skill bars ───────────────────────────────────────────────────────────
        const sbo = new IntersectionObserver(entries => {
            entries.forEach(({
                isIntersecting,
                target
            }) => {
                if (isIntersecting) {
                    target.querySelectorAll('.skill-bar').forEach(b => {
                        b.style.width = b.dataset.w || b.style.width;
                    });
                }
            });
        }, {
            threshold: 0.3
        });
        document.querySelectorAll('.skill-bar').forEach(b => {
            b.dataset.w = b.style.width;
            b.style.width = '0';
            sbo.observe(b.closest('.reveal') || b);
        });

        // ── Particle Canvas ──────────────────────────────────────────────────────
        (function() {
            const canvas = document.getElementById('particles');
            const ctx = canvas.getContext('2d');
            let W, H, pts = [];
            const N = 60;

            function resize() {
                W = canvas.width = window.innerWidth;
                H = canvas.height = window.innerHeight;
            }
            resize();
            window.addEventListener('resize', resize, {
                passive: true
            });

            for (let i = 0; i < N; i++) {
                pts.push({
                    x: Math.random(),
                    y: Math.random(),
                    r: 0.5 + Math.random() * 1.5,
                    vx: (Math.random() - 0.5) * 0.08,
                    vy: -0.04 - Math.random() * 0.06,
                    a: 0.2 + Math.random() * 0.5
                });
            }

            function draw() {
                ctx.clearRect(0, 0, W, H);
                const isDark = html.classList.contains('dark');
                pts.forEach(p => {
                    p.x += p.vx / W;
                    p.y += p.vy / H;
                    if (p.y < -0.02) p.y = 1.01;
                    if (p.x < 0) p.x = 1;
                    if (p.x > 1) p.x = 0;
                    ctx.beginPath();
                    ctx.arc(p.x * W, p.y * H, p.r, 0, Math.PI * 2);
                    // Use the CSS var for color — approximate with amber hue in light, primary in dark
                    ctx.fillStyle = isDark ?
                        `rgba(251,191,36,${p.a * 0.7})` :
                        `rgba(180,120,0,${p.a * 0.3})`;
                    ctx.fill();
                });
                requestAnimationFrame(draw);
            }
            draw();
        })();

        // ── Panic Mode ───────────────────────────────────────────────────────────
        function triggerPanic() {
            document.getElementById('panic-screen').style.display = 'block';
            document.body.style.overflow = 'hidden';
        }

        function hidePanic() {
            document.getElementById('panic-screen').style.display = 'none';
            document.body.style.overflow = '';
        }
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape') hidePanic();
        });


        // ── FAQ Accordion ─────────────────────────────────────────────────────────
        function toggleFaq(btn) {
            const body = btn.nextElementSibling;
            const chevron = btn.querySelector('.faq-chevron');
            const isOpen = body.classList.contains('open');
            // Close all
            document.querySelectorAll('.faq-body.open').forEach(b => b.classList.remove('open'));
            document.querySelectorAll('.faq-chevron.open').forEach(c => c.classList.remove('open'));
            if (!isOpen) {
                body.classList.add('open');
                chevron.classList.add('open');
            }
        }
    </script>
</body>

</html>
