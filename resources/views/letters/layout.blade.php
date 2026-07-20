<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Letters') - Waldo Dynasty</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Playfair+Display:ital,wght@0,400..900;1,400..900&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- AlpineJS -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- FontAwesome for beautiful large icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
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
<body x-data="{ darkMode: localStorage.getItem('darkMode') === 'true' }" 
      x-init="if (darkMode) document.documentElement.classList.add('dark');
              $watch('darkMode', val => {
                  localStorage.setItem('darkMode', val);
                  val ? document.documentElement.classList.add('dark') : document.documentElement.classList.remove('dark');
              })" 
      :class="{ 'dark': darkMode }"
      class="h-full flex flex-col bg-slate-50 dark:bg-zinc-950 text-slate-900 dark:text-zinc-100 transition-colors duration-300">

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
            <a href="{{ route('letters.fonts') }}" 
               class="px-4 py-2 text-sm font-semibold rounded-xl transition-all {{ request()->routeIs('letters.fonts') ? 'bg-amber-500/10 text-amber-600 dark:text-amber-400' : 'text-slate-600 dark:text-zinc-400 hover:bg-slate-100 dark:hover:bg-zinc-800 hover:text-slate-950 dark:hover:text-zinc-50' }}">
                <i class="fa-solid fa-font mr-1.5 text-base"></i> Fonts
            </a>
        </nav>

        <div class="flex items-center gap-3">
            <!-- Theme Toggle -->
            <button @click="darkMode = !darkMode" 
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
        fetch('{{ route('letters.fonts.api') }}')
            .then(r => r.json())
            .then(fonts => {
                if (!fonts.length) return;
                const style = document.createElement('style');
                style.textContent = fonts.map(f =>
                    `@font-face { font-family: '${f.family}'; src: url('${f.url}'); font-style: ${f.style}; font-weight: ${f.weight}; }`
                ).join('\n');
                document.head.appendChild(style);

                const selects = document.querySelectorAll('#tb-font');
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
