<!DOCTYPE html>
<html lang="en" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Letters & Templates') - Waldo Dynasty</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,300;0,14..32,400;0,14..32,500;0,14..32,600;0,14..32,700;1,14..32,400&family=JetBrains+Mono:wght@400;500&family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=Roboto:ital,wght@0,300;0,400;0,500;0,700;1,400&family=Merriweather:ital,wght@0,300;0,400;0,700;1,300&family=Montserrat:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">

    <!-- Vite: Tailwind CSS & app JS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Trix Rich Editor -->
    <link rel="stylesheet" href="https://unpkg.com/trix@2.0.8/dist/trix.css">
    <script src="https://unpkg.com/trix@2.0.8/dist/trix.umd.min.js"></script>

    <!-- html2pdf for PDF download -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"
        integrity="sha512-GsLlZN/3F2ErC5ifS5QtgpiJtWd43JWSuIgh7mbzZ8zBps+dvLusV+eNQATqgA/HdeKFVgA5v3S/cIrLF7QnIg=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <style>
        /* ----  Editor desk & doc page  ---- */
        .editor-desk {
            flex: 1;
            overflow-y: auto;
            background: #e8eaed;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 32px 24px 80px;
            position: relative;
        }

        .dark .editor-desk {
            background: #121215;
        }

        /* =====================================================
           GLOBAL GOOGLE DOCS-STYLE DOCUMENT ARCHITECTURE
           ===================================================== */
        /* --- Editor scroll background --- */
        .editor-desk {
            flex: 1;
            overflow-y: auto;
            background: #e8eaed;
            padding: 32px 48px 80px;
            display: flex;
            flex-direction: column;
            align-items: center;
            transition: background 0.25s;
        }
        .dark .editor-desk {
            background: #121215;
        }

        /* --- Page Gap between pages (Google Docs style) --- */
        .page-gap {
            width: 210mm;
            height: 24px;
            background: #e8eaed;
            flex-shrink: 0;
            position: relative;
        }
        .page-gap::before, .page-gap::after {
            content: '';
            position: absolute;
            left: 0; right: 0;
            height: 3px;
        }
        .page-gap::before { top: 0;    background: linear-gradient(to bottom, rgba(0,0,0,0.08), transparent); }
        .page-gap::after  { bottom: 0; background: linear-gradient(to top,   rgba(0,0,0,0.08), transparent); }
        .dark .page-gap { background: #121215; }

        /* --- A4 fixed-height page (one per sheet) --- */
        .a4-doc-page, .a4-page {
            width: 210mm;
            height: 297mm;
            overflow: hidden;
            background: #fff;
            box-shadow: 0 2px 8px rgba(0,0,0,.12), 0 0 0 1px rgba(0,0,0,.05);
            box-sizing: border-box;
            outline: none;
            color: #1a1a1a;
            font-family: 'Times New Roman', serif;
            font-size: 11pt;
            line-height: 1.6;
            word-break: break-word;
            overflow-wrap: break-word;
            position: relative;
        }
        .dark .a4-doc-page, .dark .a4-page {
            background-color: #1e1e22;
            color: #e8e8e8;
            box-shadow: 0 2px 12px rgba(0,0,0,.5);
        }
        
        .a4-doc-page:empty::before {
            content: attr(data-placeholder);
            color: #9ca3af;
            pointer-events: none;
            font-style: italic;
            font-family: 'Inter', sans-serif;
            font-size: 10pt;
        }

        /* ---- Margin guide overlays ---- */
        .margin-guide {
            position: absolute;
            pointer-events: none;
        }

        .guide-top {
            top: 0; left: 0; right: 0;
            border-bottom: 1.5px dashed rgba(245, 158, 11, 0.4);
        }

        .guide-bottom {
            bottom: 0; left: 0; right: 0;
            border-top: 1.5px dashed rgba(245, 158, 11, 0.4);
        }

        .guide-left {
            top: 0; bottom: 0; left: 0;
            border-right: 1.5px dashed rgba(245, 158, 11, 0.4);
        }

        .guide-right {
            top: 0; bottom: 0; right: 0;
            border-left: 1.5px dashed rgba(245, 158, 11, 0.4);
        }

        .guide-label {
            position: absolute;
            font-size: 8pt;
            font-family: 'JetBrains Mono', monospace;
            color: #d97706;
            background: rgba(255, 251, 235, 0.95);
            padding: 1px 4px;
            border-radius: 2px;
            white-space: nowrap;
        }

        /* ---- Print ---- */
        @media print {
            .no-print, header, nav, aside, .page-gap, .margin-guide, .page-break-overlay {
                display: none !important;
            }
            body {
                background: white !important;
                margin: 0 !important;
                padding: 0 !important;
            }
        }
    </style>

    @yield('styles')
</head>

<body x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }" x-init="if (darkMode) document.documentElement.classList.add('dark');
$watch('darkMode', val => {
    localStorage.setItem('darkMode', val);
    val ? document.documentElement.classList.add('dark') : document.documentElement.classList.remove('dark');
});" :class="{ 'dark': darkMode }"
    class="font-sans antialiased h-full flex flex-col bg-slate-100 dark:bg-zinc-950 text-slate-900 dark:text-zinc-100 transition-colors duration-200"
    style="font-family: 'Inter', sans-serif;">

    <!-- ===== Top Header ===== -->
    <header
        class="no-print bg-white/95 dark:bg-zinc-900/95 backdrop-blur border-b border-slate-200/80 dark:border-zinc-800/80 px-6 flex items-center justify-between h-[58px] shrink-0 z-20 transition-colors duration-200 shadow-sm">

        <!-- Brand -->
        <div class="flex items-center gap-3">
            <a href="{{ route('letters.index') }}" class="flex items-center gap-2.5 group">
                <div
                    class="bg-gradient-to-br from-amber-500 to-amber-700 text-white w-9 h-9 rounded-xl flex items-center justify-center font-bold text-base shadow transition-transform group-hover:scale-105">
                    W</div>
                <div class="flex flex-col leading-none">
                    <span class="text-[15px] font-bold tracking-tight text-slate-900 dark:text-zinc-50">Waldo
                        Letters</span>
                    <span class="text-[10px] text-slate-400 font-medium">HR Document Center</span>
                </div>
            </a>
        </div>

        <!-- Navigation Tabs -->
        <nav class="hidden md:flex items-center gap-1">
            <a href="{{ route('letters.index') }}"
                class="px-4 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('letters.index') ? 'bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-400' : 'text-slate-500 dark:text-zinc-400 hover:text-slate-900 dark:hover:text-zinc-100 hover:bg-slate-100 dark:hover:bg-zinc-800' }}">
                Templates
            </a>
            <a href="{{ route('letters.create') }}"
                class="px-4 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('letters.create') ? 'bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-400' : 'text-slate-500 dark:text-zinc-400 hover:text-slate-900 dark:hover:text-zinc-100 hover:bg-slate-100 dark:hover:bg-zinc-800' }}">
                New Template
            </a>
            <a href="{{ route('letters.generate') }}"
                class="px-4 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('letters.generate') ? 'bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-400' : 'text-slate-500 dark:text-zinc-400 hover:text-slate-900 dark:hover:text-zinc-100 hover:bg-slate-100 dark:hover:bg-zinc-800' }}">
                Generate Letters
            </a>
            <a href="{{ route('letters.fonts') }}"
                class="px-4 py-2 text-sm font-medium rounded-lg transition-colors {{ request()->routeIs('letters.fonts') ? 'bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-400' : 'text-slate-500 dark:text-zinc-400 hover:text-slate-900 dark:hover:text-zinc-100 hover:bg-slate-100 dark:hover:bg-zinc-800' }}">
                Fonts
            </a>
        </nav>

        <!-- Right Actions -->
        <div class="flex items-center gap-2">
            <!-- Dark mode toggle -->
            <button @click="darkMode = !darkMode"
                class="p-2 rounded-lg border border-slate-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 text-slate-500 dark:text-zinc-400 hover:text-slate-900 dark:hover:text-zinc-100 transition-all cursor-pointer shadow-sm"
                title="Toggle Dark/Light Mode">
                <svg x-show="!darkMode" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707m0-12.728l.707.707m12.728 12.728l.707-.707M12 8a4 4 0 100 8 4 4 0 000-8z" />
                </svg>
                <svg x-show="darkMode" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24" style="display:none">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                </svg>
            </button>

            <!-- Back to panel -->
            <a href="{{ route('filament.kamkaj.pages.dashboard') }}"
                class="flex items-center gap-1.5 px-3 py-2 rounded-lg border border-slate-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 text-slate-500 dark:text-zinc-400 hover:text-slate-900 dark:hover:text-zinc-100 text-sm font-medium transition-all shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Dashboard
            </a>
        </div>
    </header>

    <!-- ===== Main Content ===== -->
    <main class="flex-1 overflow-hidden">
        @yield('content')
    </main>

    @yield('scripts')

    <!-- Load custom uploaded fonts into the editor toolbar -->
    <script>
    (function loadCustomFonts() {
        fetch('{{ route('letters.fonts.api') }}')
            .then(r => r.json())
            .then(fonts => {
                if (!fonts.length) return;

                // Inject @font-face into document
                const style = document.createElement('style');
                style.textContent = fonts.map(f =>
                    `@font-face { font-family: '${f.family}'; src: url('${f.url}'); font-style: ${f.style}; font-weight: ${f.weight}; }`
                ).join('\n');
                document.head.appendChild(style);

                // Append to any font dropdowns on the page
                const selects = document.querySelectorAll('#tb-font');
                selects.forEach(sel => {
                    // Avoid duplicates
                    const existingValues = [...sel.options].map(o => o.value);
                    fonts.forEach(f => {
                        if (!existingValues.includes(f.family)) {
                            const opt = document.createElement('option');
                            opt.value = f.family;
                            opt.textContent = f.family + ' ★';
                            sel.appendChild(opt);
                        }
                    });
                });
            })
            .catch(() => {}); // Silently ignore if endpoint unavailable
    })();
    </script>
</body>

</html>
