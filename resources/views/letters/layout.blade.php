<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Letters') - Waldo Dynasty</title>
    <x-favicon />
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <script>
        (function() {
            try {
                if (localStorage.getItem('darkMode') === 'true' || (!('darkMode' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
            } catch (_) {}
        })();
    </script>
    
    <!-- AlpineJS -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Flatpickr for Range Selection -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <!-- FontAwesome for beautiful large icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        @font-face {
            font-family: 'Preeti';
            src: local('Preeti'), local('Preeti Regular'), url('{{ asset('storage/letter-fonts/5e863031-8dcd-4210-9605-aea09f491236.ttf') }}') format('truetype');
            font-weight: normal;
            font-style: normal;
            font-display: swap;
        }

        /* Template variable pill base */
        .template-variable {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif !important;
            font-style: normal !important;
            font-weight: 600 !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 5px !important;
            padding: 1.5px 8px !important;
            margin: 0 2px !important;
            border-radius: 9999px !important;
            font-size: 0.88em !important;
            vertical-align: baseline !important;
            user-select: all !important;
            line-height: 1.4 !important;
            text-decoration: none !important;
            cursor: pointer !important;
            transition: all 0.15s ease !important;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04) !important;
        }

        /* 1. Prebuilt Variables: Primary color (Amber) */
        .template-variable.var-type-prebuilt,
        .template-variable[data-var-type="prebuilt"] {
            background-color: rgba(245, 158, 11, 0.12) !important;
            color: #b45309 !important;
            border: 1px solid rgba(245, 158, 11, 0.38) !important;
        }
        .template-variable.var-type-prebuilt:hover,
        .template-variable[data-var-type="prebuilt"]:hover {
            background-color: rgba(245, 158, 11, 0.22) !important;
            border-color: rgba(245, 158, 11, 0.6) !important;
        }
        .dark .template-variable.var-type-prebuilt,
        .dark .template-variable[data-var-type="prebuilt"] {
            background-color: rgba(245, 158, 11, 0.18) !important;
            color: #fbbf24 !important;
            border-color: rgba(245, 158, 11, 0.45) !important;
        }

        /* 2. Permanent Variables: Lime */
        .template-variable.var-type-permanent,
        .template-variable[data-var-type="permanent"] {
            background-color: rgba(132, 204, 22, 0.15) !important;
            color: #4d7c0f !important;
            border: 1px solid rgba(132, 204, 22, 0.42) !important;
        }
        .template-variable.var-type-permanent:hover,
        .template-variable[data-var-type="permanent"]:hover {
            background-color: rgba(132, 204, 22, 0.25) !important;
            border-color: rgba(132, 204, 22, 0.65) !important;
        }
        .dark .template-variable.var-type-permanent,
        .dark .template-variable[data-var-type="permanent"] {
            background-color: rgba(132, 204, 22, 0.2) !important;
            color: #a3e635 !important;
            border-color: rgba(132, 204, 22, 0.5) !important;
        }

        /* 3. Custom Variables: Purple / Violet */
        .template-variable.var-type-custom,
        .template-variable[data-var-type="custom"] {
            background-color: rgba(168, 85, 247, 0.14) !important;
            color: #7e22ce !important;
            border: 1px solid rgba(168, 85, 247, 0.4) !important;
        }
        .template-variable.var-type-custom:hover,
        .template-variable[data-var-type="custom"]:hover {
            background-color: rgba(168, 85, 247, 0.24) !important;
            border-color: rgba(168, 85, 247, 0.65) !important;
        }
        .dark .template-variable.var-type-custom,
        .dark .template-variable[data-var-type="custom"] {
            background-color: rgba(168, 85, 247, 0.2) !important;
            color: #c084fc !important;
            border-color: rgba(168, 85, 247, 0.5) !important;
        }

        /* Pill Variable Name */
        .template-variable .var-name {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace !important;
            font-size: 0.95em !important;
            letter-spacing: -0.01em !important;
        }

        /* Pill Font Tag */
        .template-variable .var-font-tag {
            display: inline-flex !important;
            align-items: center !important;
            font-size: 0.72em !important;
            font-weight: 700 !important;
            letter-spacing: 0.02em !important;
            padding: 1px 6px !important;
            border-radius: 9999px !important;
            line-height: 1.25 !important;
            pointer-events: auto !important;
            user-select: none !important;
            cursor: pointer !important;
            transition: all 0.15s ease !important;
        }
        .template-variable .var-font-tag:hover {
            transform: scale(1.06) !important;
        }

        /* Nepali Font Tag (Preeti, etc.) */
        .template-variable .var-font-tag.var-font-nepali {
            background-color: #dc2626 !important;
            color: #ffffff !important;
            border: 1px solid #b91c1c !important;
            box-shadow: 0 1px 2px rgba(220, 38, 38, 0.3) !important;
        }

        /* Standard Latin Font Tag */
        .template-variable .var-font-tag.var-font-latin {
            background-color: rgba(0, 0, 0, 0.08) !important;
            color: currentColor !important;
            border: 1px solid rgba(0, 0, 0, 0.12) !important;
            opacity: 0.9 !important;
        }
        .dark .template-variable .var-font-tag.var-font-latin {
            background-color: rgba(255, 255, 255, 0.12) !important;
            border-color: rgba(255, 255, 255, 0.2) !important;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        /* Flatpickr Theme Customization for Waldo */
        .flatpickr-calendar {
            background: #ffffff;
            border-radius: 1rem;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
            border: 1px solid #e2e8f0;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        .dark .flatpickr-calendar {
            background: #18181b;
            border-color: #27272a;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.5);
            color: #f4f4f5;
        }
        .dark .flatpickr-day {
            color: #e4e4e7;
        }
        .flatpickr-day.selected, .flatpickr-day.startRange, .flatpickr-day.endRange {
            background: #f59e0b !important;
            border-color: #f59e0b !important;
            color: #ffffff !important;
            font-weight: 700;
        }
        .flatpickr-day.inRange {
            background: rgba(245, 158, 11, 0.15) !important;
            border-color: transparent !important;
            box-shadow: -5px 0 0 rgba(245, 158, 11, 0.15), 5px 0 0 rgba(245, 158, 11, 0.15);
        }
        .dark .flatpickr-day.inRange {
            background: rgba(245, 158, 11, 0.25) !important;
            box-shadow: -5px 0 0 rgba(245, 158, 11, 0.25), 5px 0 0 rgba(245, 158, 11, 0.25);
            color: #fbbf24;
        }
        .flatpickr-day:hover {
            background: #f1f5f9;
        }
        .dark .flatpickr-day:hover {
            background: #27272a;
        }
        .dark .flatpickr-months .flatpickr-month,
        .dark .flatpickr-current-month .flatpickr-monthDropdown-months,
        .dark .flatpickr-current-month input.cur-year {
            color: #f4f4f5;
            fill: #f4f4f5;
        }
        .dark .flatpickr-weekdays,
        .dark span.flatpickr-weekday {
            color: #a1a1aa;
        }
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: transparent;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 9999px;
        }
        .dark ::-webkit-scrollbar-thumb {
            background: #3f3f46;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
        .dark ::-webkit-scrollbar-thumb:hover {
            background: #52525b;
        }
    </style>
    @yield('styles')
</head>
<body x-data="{ 
          darkMode: document.documentElement.classList.contains('dark'),
          toggleTheme() {
              this.darkMode = !this.darkMode;
              localStorage.setItem('darkMode', this.darkMode);
              if (this.darkMode) {
                  document.documentElement.classList.add('dark');
              } else {
                  document.documentElement.classList.remove('dark');
              }
          }
      }" 
      class="h-full flex flex-col bg-slate-50 dark:bg-zinc-950 text-slate-900 dark:text-zinc-100 transition-colors duration-200">

    <!-- Header bar -->
    <header class="no-print bg-white/90 dark:bg-zinc-900/90 backdrop-blur-md border-b border-slate-200 dark:border-zinc-800 px-8 flex items-center justify-between h-[64px] shrink-0 z-30 transition-colors duration-300 shadow-sm">
        <div class="flex items-center gap-4">
            <a href="{{ route('letters.index') }}" class="flex items-center gap-3 group">
                <div class="bg-gradient-to-br from-amber-500 to-amber-600 text-white w-10 h-10 rounded-xl flex items-center justify-center font-bold text-lg shadow-md shadow-amber-500/20 group-hover:scale-105 transition-transform">
                    W
                </div>
                <div class="flex flex-col">
                    <span class="text-base font-bold tracking-tight text-slate-900 dark:text-zinc-50">Waldo Letters</span>
                    <span class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider">Document Studio</span>
                </div>
            </a>
        </div>

        <nav class="hidden md:flex items-center gap-2">
            <a href="{{ route('letters.index') }}" 
               class="px-4 py-2 text-sm font-semibold rounded-xl transition-all {{ request()->routeIs('letters.index') ? 'bg-amber-500/10 text-amber-600 dark:text-amber-400' : 'text-slate-600 dark:text-zinc-400 hover:bg-slate-100 dark:hover:bg-zinc-800 hover:text-slate-950 dark:hover:text-zinc-50' }}">
                <i class="fa-solid fa-folder-open mr-1.5 text-base"></i> Templates
            </a>
            <a href="{{ route('letters.create') }}" 
               class="px-4 py-2 text-sm font-semibold rounded-xl transition-all {{ request()->routeIs('letters.create') ? 'bg-amber-500/10 text-amber-600 dark:text-amber-400' : 'text-slate-600 dark:text-zinc-400 hover:bg-slate-100 dark:hover:bg-zinc-800 hover:text-slate-950 dark:hover:text-zinc-50' }}">
                <i class="fa-solid fa-circle-plus mr-1.5 text-base"></i> New Template
            </a>
            <a href="{{ route('letters.generate') }}" 
               class="px-4 py-2 text-sm font-semibold rounded-xl transition-all {{ request()->routeIs('letters.generate') ? 'bg-amber-500/10 text-amber-600 dark:text-amber-400' : 'text-slate-600 dark:text-zinc-400 hover:bg-slate-100 dark:hover:bg-zinc-800 hover:text-slate-950 dark:hover:text-zinc-50' }}">
                <i class="fa-solid fa-file-invoice mr-1.5 text-base"></i> Generate Letters
            </a>
            <a href="{{ route('letters.history') }}" 
               class="px-4 py-2 text-sm font-semibold rounded-xl transition-all {{ request()->routeIs('letters.history*') ? 'bg-amber-500/10 text-amber-600 dark:text-amber-400' : 'text-slate-600 dark:text-zinc-400 hover:bg-slate-100 dark:hover:bg-zinc-800 hover:text-slate-950 dark:hover:text-zinc-50' }}">
                <i class="fa-solid fa-clock-rotate-left mr-1.5 text-base"></i> Generated History
            </a>
            <a href="{{ route('letters.variables.index') }}" 
               class="px-4 py-2 text-sm font-semibold rounded-xl transition-all {{ request()->routeIs('letters.variables.*') ? 'bg-amber-500/10 text-amber-600 dark:text-amber-400' : 'text-slate-600 dark:text-zinc-400 hover:bg-slate-100 dark:hover:bg-zinc-800 hover:text-slate-950 dark:hover:text-zinc-50' }}">
                <i class="fa-solid fa-sliders mr-1.5 text-base"></i> Global Variables
            </a>
            <a href="{{ route('letters.fonts') }}" 
               class="px-4 py-2 text-sm font-semibold rounded-xl transition-all {{ request()->routeIs('letters.fonts') ? 'bg-amber-500/10 text-amber-600 dark:text-amber-400' : 'text-slate-600 dark:text-zinc-400 hover:bg-slate-100 dark:hover:bg-zinc-800 hover:text-slate-950 dark:hover:text-zinc-50' }}">
                <i class="fa-solid fa-font mr-1.5 text-base"></i> Fonts
            </a>
        </nav>

        <div class="flex items-center gap-3">
            <!-- Theme Toggle -->
            <button @click="toggleTheme()" 
                    class="p-2.5 rounded-xl border border-slate-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 text-slate-500 dark:text-zinc-400 hover:text-slate-900 dark:hover:text-zinc-100 transition-all shadow-sm active:scale-95 cursor-pointer">
                <i x-show="!darkMode" class="fa-solid fa-sun text-base"></i>
                <i x-show="darkMode" class="fa-solid fa-moon text-base" style="display:none"></i>
            </button>

            <!-- Dashboard Button -->
            <a href="{{ route('filament.kamkaj.pages.dashboard') }}" 
               class="flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 text-slate-600 dark:text-zinc-300 hover:text-slate-900 dark:hover:text-zinc-100 text-sm font-semibold transition-all shadow-sm active:scale-95">
                <i class="fa-solid fa-house text-base"></i> Dashboard
            </a>
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="flex-1 overflow-hidden flex flex-col">
        @yield('content')
    </main>

    @yield('scripts')

    <!-- Load Font Face Config -->
    <script>
    (function loadCustomFonts() {
        const defaultNepaliFonts = [
            { family: 'Preeti', label: 'Preeti (नेपाली)' },
            { family: 'Kalimati', label: 'Kalimati (Unicode)' }
        ];

        const selects = document.querySelectorAll('#tb-font');
        selects.forEach(sel => {
            const existingValues = [...sel.options].map(o => o.value);
            defaultNepaliFonts.forEach(df => {
                if (!existingValues.includes(df.family)) {
                    const opt = document.createElement('option');
                    opt.value = df.family;
                    opt.textContent = df.label;
                    sel.appendChild(opt);
                }
            });
        });

        fetch('{{ route('letters.fonts.api') }}')
            .then(r => r.json())
            .then(fonts => {
                if (!fonts.length) return;
                const style = document.createElement('style');
                style.textContent = fonts.map(f =>
                    `@font-face { font-family: '${f.family}'; src: url('${f.url}'); font-style: ${f.style}; font-weight: ${f.weight}; }`
                ).join('\n');
                document.head.appendChild(style);

                selects.forEach(sel => {
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
            .catch(() => {});
    })();
    </script>
</body>
</html>
