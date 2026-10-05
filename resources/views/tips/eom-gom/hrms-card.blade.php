<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-950">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HRMS Recognition Card - {{ $monthName }} {{ $evaluatedYear ?? $report->year }}</title>
    <x-favicon />

    {{-- Google Fonts: Outfit & Plus Jakarta Sans for Material Typography --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800;900&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@600;700&display=swap" rel="stylesheet">

    {{-- Vite Directive (Tailwind CSS v4 & App JS) --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- html-to-image library for crisp client-side JPG generation --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html-to-image/1.11.11/html-to-image.min.js"></script>

    <style>
        body {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #020617;
            color: #f8fafc;
        }

        .font-display {
            font-family: 'Outfit', system-ui, sans-serif;
        }

        .font-mono-code {
            font-family: 'JetBrains Mono', monospace;
        }

        /* Ambient background glow meshes */
        .ambient-mesh {
            background-image: 
                radial-gradient(at 15% 15%, rgba(245, 158, 11, 0.18) 0px, transparent 45%),
                radial-gradient(at 85% 20%, rgba(14, 165, 233, 0.16) 0px, transparent 45%),
                radial-gradient(at 50% 85%, rgba(99, 102, 241, 0.14) 0px, transparent 50%),
                radial-gradient(at 80% 80%, rgba(245, 158, 11, 0.12) 0px, transparent 40%);
        }

        /* Subtle dot-grid texture overlay */
        .dot-grid {
            background-image: radial-gradient(rgba(255, 255, 255, 0.08) 1.2px, transparent 1.2px);
            background-size: 32px 32px;
        }

        /* Responsive scale container for the fixed 2400x1350 canvas */
        .preview-viewport {
            width: 100%;
            max-width: 1400px;
            margin: 0 auto;
            overflow-x: auto;
            display: flex;
            justify-content: center;
            padding: 24px 16px 48px;
        }

        .canvas-scale-wrapper {
            width: 2400px;
            height: 1350px;
            transform-origin: top center;
            transform: scale(0.55);
            margin-bottom: -607px; /* Collapse negative margin due to transform */
            flex-shrink: 0;
        }

        @media (max-width: 1350px) {
            .canvas-scale-wrapper {
                transform: scale(0.42);
                margin-bottom: -783px;
            }
        }

        @media (max-width: 1024px) {
            .canvas-scale-wrapper {
                transform: scale(0.34);
                margin-bottom: -891px;
            }
        }

        @media (max-width: 768px) {
            .canvas-scale-wrapper {
                transform: scale(0.24);
                margin-bottom: -1026px;
            }
        }

        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background: #020617 !important;
                padding: 0 !important;
            }
            .preview-viewport {
                max-width: none !important;
                padding: 0 !important;
            }
            .canvas-scale-wrapper {
                transform: scale(0.38) !important;
                margin: 0 !important;
            }
        }
    </style>
</head>
<body class="min-h-screen bg-slate-950 text-slate-100 flex flex-col">

    {{-- TOP MATERIAL TOOLBAR (Hidden during print) --}}
    <header class="no-print sticky top-0 z-50 bg-slate-900/80 backdrop-blur-xl border-b border-white/10 px-6 py-3.5 shadow-lg">
        <div class="max-w-7xl mx-auto flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-amber-400 to-amber-600 flex items-center justify-center text-slate-950 font-black text-lg shadow-md shadow-amber-500/20">
                    🏆
                </div>
                <div>
                    <h1 class="text-sm font-bold text-white flex items-center gap-2">
                        <span>HRMS Wish Card Preview</span>
                        <span class="text-[11px] px-2 py-0.5 rounded-full bg-amber-500/10 text-amber-400 border border-amber-500/20 font-semibold font-mono">
                            2400 × 1350 QHD
                        </span>
                    </h1>
                    <p class="text-xs text-slate-400 mt-0.5">
                        Honorees for <strong class="text-slate-200">{{ $monthName }} {{ $evaluatedYear ?? $report->year }}</strong> ({{ $releaseLabel }})
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <button type="button" 
                        id="downloadBtn" 
                        onclick="downloadCardAsJpg()" 
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gradient-to-r from-amber-500 via-amber-400 to-yellow-400 hover:from-amber-600 hover:to-amber-500 text-slate-950 font-extrabold text-xs shadow-md shadow-amber-500/25 transition cursor-pointer active:scale-95">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    <span id="downloadBtnText">Download Card (JPG)</span>
                </button>

                <button type="button" 
                        onclick="window.print()" 
                        class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-white/10 hover:bg-white/15 text-slate-200 border border-white/15 text-xs font-semibold transition cursor-pointer active:scale-95">
                    <svg class="w-4 h-4 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    <span>Print</span>
                </button>

                <button type="button" 
                        onclick="window.close()" 
                        class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-slate-800/80 hover:bg-slate-700/80 text-slate-400 hover:text-white text-xs font-medium transition cursor-pointer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    <span>Close</span>
                </button>
            </div>
        </div>
    </header>

    {{-- PREVIEW CONTAINER --}}
    <main class="flex-1 flex justify-center items-start overflow-hidden">
        <div class="preview-viewport">
            <div class="canvas-scale-wrapper">

                {{-- ================================================================= --}}
                {{-- MASTER 2400 x 1350 WISH CARD CANVAS (MATERIAL 3 AESTHETIC)       --}}
                {{-- ================================================================= --}}
                <div id="master-wish-card-canvas" 
                     class="w-[2400px] h-[1350px] min-w-[2400px] min-h-[1350px] bg-slate-950 relative overflow-hidden ambient-mesh flex flex-col justify-between p-[48px] box-border select-none border border-slate-800 shadow-2xl">

                    {{-- Dot Grid Background Texture --}}
                    <div class="absolute inset-0 dot-grid pointer-events-none opacity-40"></div>

                    {{-- Subtle Glowing Edge Beams --}}
                    <div class="absolute top-0 inset-x-0 h-1 bg-gradient-to-r from-transparent via-amber-400/60 to-transparent"></div>
                    <div class="absolute bottom-0 inset-x-0 h-1 bg-gradient-to-r from-transparent via-sky-400/60 to-transparent"></div>

                    {{-- ============================================================= --}}
                    {{-- 1. HEADER SECTION (MATERIAL DESIGN BRANDING & TITLE)          --}}
                    {{-- ============================================================= --}}
                    <div class="relative z-10 flex items-center justify-between border-b border-white/10 pb-7">
                        <div class="flex items-center gap-6">
                            {{-- Casino Brand Medallion --}}
                            <div class="w-20 h-20 rounded-2xl bg-gradient-to-tr from-amber-500 via-yellow-400 to-amber-200 p-[3px] shadow-xl shadow-amber-500/20 flex-shrink-0">
                                <div class="w-full h-full rounded-[14px] bg-slate-950 flex flex-col items-center justify-center">
                                    <span class="text-3xl">👑</span>
                                </div>
                            </div>

                            <div>
                                <div class="flex items-center gap-3">
                                    <span class="font-display text-base font-extrabold tracking-[4px] uppercase text-amber-400">
                                        Waldo Dynasty Resort & Casino
                                    </span>
                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-600"></span>
                                    <span class="text-sm font-semibold tracking-wider uppercase text-slate-400">
                                        Human Resources & Talent Recognition
                                    </span>
                                </div>
                                <h2 class="font-display text-5xl font-black text-white tracking-tight mt-1">
                                    Employee & Grooming of the Month
                                </h2>
                            </div>
                        </div>

                        {{-- Month Pill Badge --}}
                        <div class="flex flex-col items-end gap-1.5">
                            <div class="inline-flex items-center gap-2 px-5 py-2.5 rounded-2xl bg-slate-900/90 border border-amber-400/40 shadow-xl shadow-amber-950/40 backdrop-blur-md">
                                <span class="w-2.5 h-2.5 rounded-full bg-amber-400 animate-pulse"></span>
                                <span class="font-display text-2xl font-black tracking-wide text-transparent bg-clip-text bg-gradient-to-r from-amber-200 via-amber-300 to-yellow-400">
                                    {{ strtoupper($monthName) }} {{ $evaluatedYear ?? $report->year }}
                                </span>
                            </div>
                            <span class="text-xs font-semibold tracking-wider text-slate-400 uppercase pr-1">
                                {{ $releaseLabel }} Cycle
                            </span>
                        </div>
                    </div>

                    {{-- ============================================================= --}}
                    {{-- 2. MAIN BODY (2 TIERS: 4 EOM CARDS + 2 GOM CARDS)             --}}
                    {{-- ============================================================= --}}
                    <div class="relative z-10 flex-1 flex flex-col justify-between py-7 gap-7">

                        {{-- TIER 1: EMPLOYEES OF THE MONTH (4 HONOREES ACROSS) --}}
                        <div class="flex flex-col gap-4">
                            {{-- Tier 1 Header Chip --}}
                            <div class="flex items-center justify-between">
                                <div class="inline-flex items-center gap-3 px-5 py-2 rounded-xl bg-amber-500/10 border border-amber-500/30 text-amber-300">
                                    <span class="text-lg">🏆</span>
                                    <span class="font-display text-sm font-extrabold tracking-widest uppercase">
                                        Employees of the Month
                                    </span>
                                </div>
                                <span class="text-xs font-bold uppercase tracking-wider text-amber-400/80 font-mono">
                                    4 Honorees Awarded
                                </span>
                            </div>

                            {{-- 4 Columns Grid --}}
                            <div class="grid grid-cols-4 gap-6">
                                @foreach($eomWinners as $winner)
                                    @php $emp = $winner['employee']; @endphp
                                    @if($emp)
                                        <div class="relative rounded-3xl bg-slate-900/80 border border-amber-500/30 p-6 flex flex-col justify-between shadow-xl shadow-amber-950/20 backdrop-blur-xl hover:border-amber-400/60 transition-all min-h-[350px]">
                                            {{-- Top Card Meta (Department & Winner Badge) --}}
                                            <div class="flex items-center justify-between gap-3 pb-3 border-b border-white/5">
                                                <span class="text-[12px] font-extrabold uppercase tracking-wider px-3 py-1 rounded-full bg-amber-400/10 text-amber-300 border border-amber-400/25 truncate max-w-[60%]" title="{{ $winner['department'] }}">
                                                    {{ $winner['department'] }}
                                                </span>
                                                <span class="text-[11px] font-black uppercase tracking-wider px-3 py-1 rounded-full bg-gradient-to-r from-amber-500 to-amber-600 text-slate-950 shadow-sm shrink-0">
                                                    🏆 EOM Winner
                                                </span>
                                            </div>

                                            {{-- Center: Medallion + Name + Title --}}
                                            <div class="flex flex-col items-center text-center my-auto py-2">
                                                {{-- Vibrant Gold Medallion --}}
                                                <div class="w-28 h-28 rounded-full bg-gradient-to-tr from-amber-600 via-amber-400 to-yellow-200 p-[3.5px] shadow-lg shadow-amber-500/25 shrink-0 flex items-center justify-center">
                                                    <div class="w-full h-full rounded-full bg-slate-950 flex items-center justify-center">
                                                        <span class="font-display text-3xl font-black text-amber-300 tracking-wider">
                                                            {{ strtoupper(substr($emp->name, 0, 2)) }}
                                                        </span>
                                                    </div>
                                                </div>

                                                {{-- Name --}}
                                                <h3 class="font-display text-2xl font-black text-white tracking-tight mt-3 line-clamp-1 w-full" title="{{ $emp->name }}">
                                                    {{ $emp->name }}
                                                </h3>

                                                {{-- Designation & Code Chips --}}
                                                <div class="flex items-center justify-center gap-2 mt-1.5 flex-wrap">
                                                    <span class="font-mono-code text-xs font-bold px-2 py-0.5 rounded-md bg-white/10 text-slate-200 border border-white/15">
                                                        {{ $emp->employee_code }}
                                                    </span>
                                                    <span class="text-xs font-semibold text-slate-300 truncate max-w-[180px]">
                                                        {{ $emp->designation?->name ?: 'Staff' }}
                                                    </span>
                                                </div>
                                            </div>

                                            {{-- Bottom: Remarks or Accolade Note --}}
                                            <div class="pt-3 border-t border-white/5">
                                                <div class="h-10 flex items-center justify-center px-3 rounded-xl bg-white/[0.03] text-center">
                                                    <p class="text-[12px] italic text-slate-400 line-clamp-2">
                                                        {{ !empty($winner['remarks']) ? '“' . $winner['remarks'] . '”' : '“Exemplary dedication, leadership & peer support”' }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        {{-- Empty / Unassigned Slot Placeholder --}}
                                        <div class="rounded-3xl bg-slate-900/40 border border-dashed border-slate-800 p-6 flex flex-col items-center justify-center text-center min-h-[350px]">
                                            <div class="w-16 h-16 rounded-full bg-slate-800/80 flex items-center justify-center text-slate-600 text-2xl mb-3">
                                                ✦
                                            </div>
                                            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Slot Unassigned</span>
                                            <span class="text-[11px] text-slate-600 mt-1">{{ $winner['entry_label'] }}</span>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>

                        {{-- TIER 2: GROOMINGS OF THE MONTH (2 HONOREES CENTERED) --}}
                        <div class="flex flex-col gap-4">
                            {{-- Tier 2 Header Chip --}}
                            <div class="flex items-center justify-between">
                                <div class="inline-flex items-center gap-3 px-5 py-2 rounded-xl bg-sky-500/10 border border-sky-500/30 text-sky-300">
                                    <span class="text-lg">✨</span>
                                    <span class="font-display text-sm font-extrabold tracking-widest uppercase">
                                        Grooming of the Month
                                    </span>
                                </div>
                                <span class="text-xs font-bold uppercase tracking-wider text-sky-400/80 font-mono">
                                    2 Honorees Awarded
                                </span>
                            </div>

                            {{-- 2 Columns Centered Grid --}}
                            <div class="grid grid-cols-2 gap-8 max-w-[1700px] w-full mx-auto">
                                @foreach($gomWinners as $winner)
                                    @php $emp = $winner['employee']; @endphp
                                    @if($emp)
                                        <div class="relative rounded-3xl bg-slate-900/80 border border-sky-500/30 p-6 flex flex-col justify-between shadow-xl shadow-sky-950/20 backdrop-blur-xl hover:border-sky-400/60 transition-all min-h-[310px]">
                                            {{-- Top Card Meta --}}
                                            <div class="flex items-center justify-between gap-3 pb-3 border-b border-white/5">
                                                <span class="text-[12px] font-extrabold uppercase tracking-wider px-3 py-1 rounded-full bg-sky-400/10 text-sky-300 border border-sky-400/25 truncate max-w-[60%]" title="{{ $winner['department'] }}">
                                                    {{ $winner['department'] }}
                                                </span>
                                                <span class="text-[11px] font-black uppercase tracking-wider px-3 py-1 rounded-full bg-gradient-to-r from-sky-400 to-blue-500 text-slate-950 shadow-sm shrink-0">
                                                    ✨ GOM Winner
                                                </span>
                                            </div>

                                            {{-- Center: Horizontal Layout for Roomy GOM Cards --}}
                                            <div class="flex items-center gap-6 my-auto py-2 px-2">
                                                {{-- Radiant Cyan Medallion --}}
                                                <div class="w-24 h-24 rounded-full bg-gradient-to-tr from-sky-500 via-cyan-300 to-blue-600 p-[3px] shadow-lg shadow-sky-500/25 shrink-0 flex items-center justify-center">
                                                    <div class="w-full h-full rounded-full bg-slate-950 flex items-center justify-center">
                                                        <span class="font-display text-3xl font-black text-sky-300 tracking-wider">
                                                            {{ strtoupper(substr($emp->name, 0, 2)) }}
                                                        </span>
                                                    </div>
                                                </div>

                                                {{-- Details Column --}}
                                                <div class="flex-1 min-w-0">
                                                    <h3 class="font-display text-3xl font-black text-white tracking-tight truncate" title="{{ $emp->name }}">
                                                        {{ $emp->name }}
                                                    </h3>
                                                    <div class="flex items-center gap-2.5 mt-2">
                                                        <span class="font-mono-code text-xs font-bold px-2 py-0.5 rounded-md bg-white/10 text-slate-200 border border-white/15 shrink-0">
                                                            {{ $emp->employee_code }}
                                                        </span>
                                                        <span class="text-sm font-semibold text-slate-300 truncate">
                                                            {{ $emp->designation?->name ?: 'Staff' }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Bottom: Remarks Box --}}
                                            <div class="pt-3 border-t border-white/5">
                                                <div class="h-9 flex items-center px-4 rounded-xl bg-white/[0.03]">
                                                    <p class="text-[12px] italic text-slate-400 truncate">
                                                        {{ !empty($winner['remarks']) ? '“' . $winner['remarks'] . '”' : '“Pristine grooming, flawless posture & etiquette standards”' }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        {{-- Empty / Unassigned Slot Placeholder --}}
                                        <div class="rounded-3xl bg-slate-900/40 border border-dashed border-slate-800 p-6 flex flex-col items-center justify-center text-center min-h-[310px]">
                                            <div class="w-16 h-16 rounded-full bg-slate-800/80 flex items-center justify-center text-slate-600 text-2xl mb-3">
                                                ✨
                                            </div>
                                            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Slot Unassigned</span>
                                            <span class="text-[11px] text-slate-600 mt-1">{{ $winner['entry_label'] }}</span>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        </div>

                    </div>

                    {{-- ============================================================= --}}
                    {{-- 3. FOOTER SECTION (MATERIAL MOTTO & VERIFIED CREST)           --}}
                    {{-- ============================================================= --}}
                    <div class="relative z-10 flex items-center justify-between border-t border-white/10 pt-5 text-slate-400">
                        <div class="flex items-center gap-4">
                            <span class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span>
                            <span class="font-display text-sm font-bold tracking-wider uppercase text-slate-300">
                                Waldo Resort & Casino HR Excellence Program
                            </span>
                            <span class="text-xs text-slate-600">•</span>
                            <span class="text-xs text-slate-500">
                                Honoring Performance, Passion & Professional Presentation
                            </span>
                        </div>

                        <div class="flex items-center gap-6 text-xs text-slate-400">
                            <span class="font-mono text-slate-500">
                                Cycle: {{ $periodLabel }}
                            </span>
                            <div class="flex items-center gap-2 px-3 py-1 rounded-lg bg-white/5 border border-white/10 text-slate-300">
                                <span>🛡️</span>
                                <span class="font-bold text-[11px] uppercase tracking-wider">Official HRMS Certified</span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </main>

    {{-- CLIENT-SIDE HIGH-RESOLUTION EXPORT ENGINE (JPG DOWNLOAD) --}}
    <script>
        async function downloadCardAsJpg() {
            const btn = document.getElementById('downloadBtn');
            const btnText = document.getElementById('downloadBtnText');
            const targetEl = document.getElementById('master-wish-card-canvas');

            if (!targetEl) {
                alert('Card canvas element not found.');
                return;
            }

            btn.disabled = true;
            btnText.textContent = 'Generating High-Res JPG...';

            try {
                // Generate high-resolution JPG directly using html-to-image
                const dataUrl = await htmlToImage.toJpeg(targetEl, {
                    quality: 0.96,
                    width: 2400,
                    height: 1350,
                    pixelRatio: 1, // Canvas is already 2400x1350 QHD
                    style: {
                        transform: 'none',
                        margin: '0',
                    }
                });

                const filename = 'EOM-GOM-WishCard-{{ \Illuminate\Support\Str::slug($monthName) }}-{{ $evaluatedYear ?? $report->year }}.jpg';
                const link = document.createElement('a');
                link.download = filename;
                link.href = dataUrl;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            } catch (err) {
                console.error('Error generating card image:', err);
                alert('Could not generate JPG automatically. You can still use the "Print" button to save as PDF or image.');
            } finally {
                btn.disabled = false;
                btnText.textContent = 'Download Card (JPG)';
            }
        }
    </script>
</body>
</html>
