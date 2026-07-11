<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
        <title>Waldo Casino | Luxury Gaming, Fine Dining & VIP Experience</title>
        <meta name="description" content="Experience the ultimate luxury at Waldo Casino. Enjoy premium card rooms, high-stakes slots, VIP lounges, fine dining, and exclusive gaming access.">
        
        <!-- Google Fonts: Cormorant Garamond & Poppins -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

        <!-- Swiper CSS -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />

        <!-- Styles / Scripts -->
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @else
            <script src="https://cdn.tailwindcss.com"></script>
            <script>
                tailwind.config = {
                    darkMode: 'class',
                    theme: {
                        extend: {
                            fontFamily: {
                                serif: ['Cormorant Garamond', 'Georgia', 'serif'],
                                sans: ['Poppins', 'sans-serif'],
                            },
                            colors: {
                                garud: {
                                    purple: '#66228a',
                                    gold: '#e4b777',
                                    mutedGold: '#d9bb85',
                                    dark: '#050209',
                                    darkPurple: '#480b4e',
                                }
                            }
                        }
                    }
                }
            </script>
        @endif

        <style>
            /* Smooth scroll setup */
            html.lenis {
                height: auto;
            }
            .lenis-smooth {
                scroll-behavior: auto !important;
            }
            .lenis-smooth [data-lenis-prevent] {
                overscroll-behavior: contain;
            }
            .lenis-stopped {
                overflow: hidden;
            }
            .lenis-scrolling iframe {
                pointer-events: none;
            }

            /* Custom scrollbar matching new design */
            ::-webkit-scrollbar {
                width: 8px;
            }
            ::-webkit-scrollbar-track {
                background: #050209;
            }
            ::-webkit-scrollbar-thumb {
                background: #66228a;
                border: 2px solid #e4b777;
                border-radius: 4px;
            }
            ::-webkit-scrollbar-thumb:hover {
                background: #e4b777;
            }

            /* Core theme classes */
            .font-serif-lux {
                font-family: 'Cormorant Garamond', Georgia, serif;
            }
            .font-sans-lux {
                font-family: 'Poppins', sans-serif;
            }

            /* Luxury Gold Gradient Text */
            .gradient-text {
                background: linear-gradient(45deg, #c7893e, #edc78a 50%, #e3ad56 100%);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
            }

            /* Background gradient overlay classes */
            .luxury-gradient-bg {
                background: linear-gradient(270deg, #c7883d 0%, #eec789 48%, #e3ad56 100%);
            }

            /* Glassmorphism panel styling */
            .glass-panel {
                background: rgba(10, 4, 18, 0.45);
                border: 1px solid rgba(228, 183, 119, 0.18);
                backdrop-filter: blur(12px);
                -webkit-backdrop-filter: blur(12px);
            }

            /* Marquee layout classes */
            .marquee-container {
                overflow: hidden;
                white-space: nowrap;
                display: flex;
            }
            .marquee-content {
                display: inline-block;
                animation: marquee 25s linear infinite;
            }
            .marquee-content-reverse {
                display: inline-block;
                animation: marquee-rev 25s linear infinite;
            }
            @keyframes marquee {
                0% { transform: translate3d(0, 0, 0); }
                100% { transform: translate3d(-50%, 0, 0); }
            }
            @keyframes marquee-rev {
                0% { transform: translate3d(-50%, 0, 0); }
                100% { transform: translate3d(0, 0, 0); }
            }

            /* Navigation Hide/Reveal Scroll behaviors */
            .header-wrap {
                transition: transform 0.4s ease, background-color 0.4s ease, border-bottom 0.4s ease;
            }
            .header-wrap.scrolled {
                background-color: rgba(5, 2, 9, 0.92);
                border-bottom: 1px solid rgba(228, 183, 119, 0.2);
            }
            .header-wrap.scrolled_hide {
                transform: translateY(-100%);
            }
            .header-wrap.scrolled .logo-text {
                font-size: 1.5rem;
            }

            /* Mobile menu sliding panel */
            .mobile_menu {
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100vh;
                background: #050209;
                z-index: 100;
                transform: translateX(-100%);
                transition: transform 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            }
            .mobile_menu.active {
                transform: translateX(0);
            }

            /* Shimmer glow effect for progressives */
            .gold-border-glow {
                box-shadow: 0 0 15px rgba(228, 183, 119, 0.2);
                border: 1px solid rgba(228, 183, 119, 0.4);
            }

            /* Custom button styling */
            .btn-luxury-gold {
                background: linear-gradient(270deg, #c7883d 0%, #eec789 48%, #e3ad56 100%);
                color: #000;
                font-weight: 600;
                letter-spacing: 0.1em;
                transition: all 0.3s ease;
                border: 1px solid transparent;
            }
            .btn-luxury-gold:hover {
                background: transparent;
                color: #e4b777;
                border-color: #e4b777;
                box-shadow: 0 0 15px rgba(228, 183, 119, 0.3);
            }

            .btn-luxury-purple {
                background-color: #66228a;
                color: #fff;
                font-weight: 600;
                letter-spacing: 0.1em;
                border: 1px solid #66228a;
                transition: all 0.3s ease;
            }
            .btn-luxury-purple:hover {
                background-color: transparent;
                color: #66228a;
                border-color: #66228a;
            }

            .btn-luxury-outline {
                background: transparent;
                color: #e4b777;
                border: 1px solid #e4b777;
                font-weight: 600;
                letter-spacing: 0.1em;
                transition: all 0.3s ease;
            }
            .btn-luxury-outline:hover {
                background-color: #66228a;
                color: #fff;
                border-color: #66228a;
                box-shadow: 0 0 15px rgba(102, 34, 138, 0.4);
            }
        </style>
    </head>
    <body class="bg-[#050209] text-gray-200 font-sans min-h-screen relative overflow-x-hidden antialiased">
        
        <!-- Desktop Header Navigation Bar -->
        <header class="header-wrap w-full fixed top-0 left-0 z-50 px-6 py-4 flex items-center justify-between border-b border-white/5 backdrop-blur-md bg-transparent">
            <!-- Left links -->
            <nav class="hidden lg:flex items-center gap-8 text-xs font-semibold uppercase tracking-widest">
                <a href="#home" class="hover:text-[#e4b777] transition-colors">Home</a>
                <a href="#lobby" class="hover:text-[#e4b777] transition-colors">Game Floor</a>
                <a href="#vip" class="hover:text-[#e4b777] transition-colors">VIP Room</a>
                <a href="#stay" class="hover:text-[#e4b777] transition-colors">Stay With Us</a>
            </nav>

            <!-- Center Logo -->
            <a href="#" class="logo-wrap flex flex-col items-center justify-center text-center absolute left-1/2 -translate-x-1/2">
                <span class="logo-text font-serif-lux font-bold text-2xl lg:text-3xl tracking-widest text-white leading-none">
                    WALDO <span class="gradient-text font-medium">CASINO</span>
                </span>
                <span class="text-[8px] tracking-[0.4em] uppercase text-gray-400 font-semibold mt-1">Nepal's Resort & Play</span>
            </a>

            <!-- Right links -->
            <div class="hidden lg:flex items-center gap-8 text-xs font-semibold uppercase tracking-widest">
                <a href="#slots" class="hover:text-[#e4b777] transition-colors">Slots Minigame</a>
                <a href="#jackpot" class="hover:text-[#e4b777] transition-colors">Offers</a>
                <a href="#contact" class="hover:text-[#e4b777] transition-colors">Contact</a>
                
                @if (Route::has('login'))
                    @auth
                        <a href="{{ url('/dashboard') }}" class="px-4 py-2 border border-[#e4b777] text-[#e4b777] rounded hover:bg-[#66228a] hover:text-white transition-all text-[10px]">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="hover:text-[#e4b777] transition-colors">Login</a>
                    @endauth
                @endif
            </div>

            <!-- Mobile Hamburger Menu Button -->
            <button class="hema lg:hidden flex flex-col gap-1.5 justify-center items-center w-8 h-8 rounded-full border border-white/10 bg-black/20 hover:border-[#e4b777] transition-all">
                <span class="w-4 h-0.5 bg-white rounded-full"></span>
                <span class="w-4 h-0.5 bg-white rounded-full"></span>
                <span class="w-4 h-0.5 bg-white rounded-full"></span>
            </button>
        </header>

        <!-- Mobile Menu Overlay -->
        <div class="mobile_menu flex flex-col px-8 py-12">
            <div class="flex justify-between items-center border-b border-white/5 pb-6">
                <span class="font-serif-lux font-bold text-2xl tracking-widest text-white">WALDO CASINO</span>
                <button class="close_icon w-8 h-8 rounded-full border border-white/10 flex items-center justify-center text-xl text-white hover:text-[#e4b777]">✕</button>
            </div>
            <nav class="flex flex-col gap-6 font-serif-lux text-2xl tracking-wide mt-12 text-left">
                <a href="#home" class="goto_div hover:text-[#e4b777] transition-colors" data-id="home">Home</a>
                <a href="#lobby" class="goto_div hover:text-[#e4b777] transition-colors" data-id="lobby">Game Floor</a>
                <a href="#slots" class="goto_div hover:text-[#e4b777] transition-colors" data-id="slots">Slots Minigame</a>
                <a href="#vip" class="goto_div hover:text-[#e4b777] transition-colors" data-id="vip">VIP Room</a>
                <a href="#stay" class="goto_div hover:text-[#e4b777] transition-colors" data-id="stay">Stay With Us</a>
                <a href="#contact" class="goto_div hover:text-[#e4b777] transition-colors" data-id="contact">Contact</a>
            </nav>
            <div class="mt-auto border-t border-white/5 pt-8 space-y-4">
                @if (Route::has('login'))
                    @auth
                        <a href="{{ url('/dashboard') }}" class="block w-full py-3 text-center rounded btn-luxury-gold text-sm font-semibold">DASHBOARD</a>
                    @else
                        <div class="grid grid-cols-2 gap-4">
                            <a href="{{ route('login') }}" class="py-3 text-center border border-white/20 rounded text-white text-sm font-semibold">LOGIN</a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="py-3 text-center rounded btn-luxury-gold text-sm font-semibold">SIGN UP</a>
                            @endif
                        </div>
                    @endauth
                @endif
                <p class="text-xs text-gray-500 text-center">OPEN 24/7. RESPONSIBLE GAMING ALWAYS.</p>
            </div>
        </div>

        <!-- Section 1: Hero Section -->
        <section id="home" class="relative min-h-screen flex items-center justify-center pt-24 pb-16 overflow-hidden">
            <!-- Background Video Loop / Fallback Gradient -->
            <div class="absolute inset-0 w-full h-full bg-[#080210] z-[-2]">
                <video autoplay loop muted playsinline class="w-full h-full object-cover opacity-35 scale-105 select-none pointer-events-none">
                    <source src="https://player.vimeo.com/external/435674703.sd.mp4?s=7fdf186291a130e20259be0830026cf94d8259b4&profile_id=165&oauth2_token_id=57447761" type="video/mp4">
                </video>
            </div>
            <!-- Overlay Masking -->
            <div class="absolute inset-0 bg-gradient-to-t from-[#050209] via-transparent to-[#050209]/80 z-[-1]"></div>

            <div class="max-w-7xl mx-auto w-full px-6 grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
                <!-- Hero Left: Welcome Promotions -->
                <div class="lg:col-span-7 text-left space-y-6">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded border border-[#e4b777]/20 bg-[#66228a]/10 text-[#e4b777] text-xs font-semibold tracking-[0.2em] uppercase">
                        <span class="w-2 h-2 rounded-full bg-[#e4b777] animate-pulse"></span>
                        Nepal's Premier Destination
                    </div>
                    
                    <h1 class="text-4xl md:text-6xl xl:text-7xl font-serif-lux font-bold tracking-tight text-white leading-none">
                        WHERE LUCK <br />
                        MEETS <span class="gradient-text">LUXURY</span>
                    </h1>

                    <div class="space-y-2 border-l-2 border-[#e4b777] pl-4 py-1">
                        <p class="text-2xl md:text-4xl font-serif-lux font-semibold text-white tracking-wider">
                            200% UP TO $2,000 WELCOME BONUS
                        </p>
                        <p class="text-sm md:text-md text-[#e4b777] font-semibold tracking-widest uppercase">
                            + 150 Cyber Free Spins on registration
                        </p>
                    </div>

                    <p class="text-gray-400 max-w-xl text-sm leading-relaxed">
                        Step inside Waldo Casino's luxury entertainment lounge. Enjoy direct resort access, premium private tables, high-limit slots, and fine dining for international guests. Play and live like royalty.
                    </p>

                    <div class="flex flex-wrap gap-4 pt-2">
                        <a href="{{ Route::has('register') ? route('register') : '#' }}" class="px-8 py-4 rounded btn-luxury-gold font-semibold text-sm shadow-lg shadow-[#e4b777]/15">
                            CLAIM BONUS NOW
                        </a>
                        <a href="#lobby" class="px-8 py-4 rounded btn-luxury-outline font-semibold text-sm">
                            EXPLORE FLOOR
                        </a>
                    </div>
                </div>

                <!-- Hero Right: Live Winners Feed Visualizer -->
                <div class="lg:col-span-5 flex justify-center w-full">
                    <div class="w-full max-w-md rounded-lg glass-panel p-6 shadow-2xl relative overflow-hidden border border-white/5">
                        <div class="absolute inset-x-0 top-0 h-[1px] bg-gradient-to-r from-transparent via-[#e4b777] to-transparent"></div>
                        <div class="flex justify-between items-center mb-6">
                            <span class="font-serif-lux font-bold text-lg text-white uppercase tracking-wider">🔥 Live Winners Feed</span>
                            <span class="w-2.5 h-2.5 rounded-full bg-green-500 animate-pulse"></span>
                        </div>
                        
                        <!-- List of Winners -->
                        <div class="space-y-3 min-h-[220px]" id="winners-list">
                            <div class="flex justify-between items-center p-3 rounded bg-white/5 border border-white/5 hover:border-[#e4b777]/25 transition-all">
                                <div>
                                    <div class="text-xs font-semibold text-white">Player: Cryptox***</div>
                                    <div class="text-[10px] text-gray-500">Won on Cyber Reels</div>
                                </div>
                                <div class="text-right">
                                    <div class="text-xs font-bold text-[#e4b777]">+$2,480.00</div>
                                    <div class="text-[10px] text-gray-500">12s ago</div>
                                </div>
                            </div>

                            <div class="flex justify-between items-center p-3 rounded bg-white/5 border border-white/5 hover:border-[#e4b777]/25 transition-all">
                                <div>
                                    <div class="text-xs font-semibold text-white">Player: WaldoFa***</div>
                                    <div class="text-[10px] text-gray-500">Won on Mega Dice</div>
                                </div>
                                <div class="text-right">
                                    <div class="text-xs font-bold text-[#e4b777]">+$850.00</div>
                                    <div class="text-[10px] text-gray-500">45s ago</div>
                                </div>
                            </div>

                            <div class="flex justify-between items-center p-3 rounded bg-white/5 border border-white/5 hover:border-[#e4b777]/25 transition-all">
                                <div>
                                    <div class="text-xs font-semibold text-white">Player: SlotsKi***</div>
                                    <div class="text-[10px] text-gray-500">Won on Quantum Roulette</div>
                                </div>
                                <div class="text-right">
                                    <div class="text-xs font-bold text-[#e4b777]">+$4,920.00</div>
                                    <div class="text-[10px] text-gray-500">1m ago</div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 text-center">
                            <a href="{{ Route::has('register') ? route('register') : '#' }}" class="text-[10px] text-[#e4b777] font-semibold tracking-widest uppercase hover:underline">
                                JOIN THE WINNERS CIRCLE →
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Hero Feature List Bottom Bar -->
            <div class="absolute bottom-0 left-0 w-full bg-[#050209]/80 border-t border-white/5 py-4">
                <div class="max-w-7xl mx-auto px-6 grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
                    <div class="py-1">
                        <span class="block text-[10px] uppercase tracking-[0.2em] text-[#e4b777] font-semibold">OPEN 24/7</span>
                        <span class="text-xs text-gray-400">All Year Round</span>
                    </div>
                    <div class="py-1 border-l border-white/5">
                        <span class="block text-[10px] uppercase tracking-[0.2em] text-[#e4b777] font-semibold">DIRECT HOTEL ACCESS</span>
                        <span class="text-xs text-gray-400">No Cabs. No Hassle.</span>
                    </div>
                    <div class="py-1 border-l border-white/5">
                        <span class="block text-[10px] uppercase tracking-[0.2em] text-[#e4b777] font-semibold">PERSONAL VIP HOSTS</span>
                        <span class="text-xs text-gray-400">Elite-Level Care</span>
                    </div>
                    <div class="py-1 border-l border-white/5">
                        <span class="block text-[10px] uppercase tracking-[0.2em] text-[#e4b777] font-semibold">STRATEGIC LOCATION</span>
                        <span class="text-xs text-gray-400">Heart of Kathmandu</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Section 2: Dual Marquee Overview Section -->
        <section class="py-12 bg-black border-y border-white/5 overflow-hidden">
            <div class="space-y-4">
                <!-- Row 1: Scroll Left -->
                <div class="marquee-container text-white opacity-85">
                    <div class="marquee-content flex gap-12 font-serif-lux text-3xl md:text-5xl uppercase tracking-widest font-bold">
                        <span>Step Inside the Action</span>
                        <span class="text-[#66228a]">•</span>
                        <span class="gradient-text">World-class Entertainment</span>
                        <span class="text-[#66228a]">•</span>
                        <span>Step Inside the Action</span>
                        <span class="text-[#66228a]">•</span>
                        <span class="gradient-text">World-class Entertainment</span>
                        <span class="text-[#66228a]">•</span>
                    </div>
                    <div class="marquee-content flex gap-12 font-serif-lux text-3xl md:text-5xl uppercase tracking-widest font-bold" aria-hidden="true">
                        <span>Step Inside the Action</span>
                        <span class="text-[#66228a]">•</span>
                        <span class="gradient-text">World-class Entertainment</span>
                        <span class="text-[#66228a]">•</span>
                        <span>Step Inside the Action</span>
                        <span class="text-[#66228a]">•</span>
                        <span class="gradient-text">World-class Entertainment</span>
                        <span class="text-[#66228a]">•</span>
                    </div>
                </div>

                <!-- Row 2: Scroll Right -->
                <div class="marquee-container text-white opacity-85">
                    <div class="marquee-content-reverse flex gap-12 font-serif-lux text-3xl md:text-5xl uppercase tracking-widest font-bold">
                        <span class="gradient-text">Exciting Games & Unlimited Thrill</span>
                        <span class="text-[#66228a]">•</span>
                        <span>Your Luck Awaits</span>
                        <span class="text-[#66228a]">•</span>
                        <span class="gradient-text">Exciting Games & Unlimited Thrill</span>
                        <span class="text-[#66228a]">•</span>
                        <span>Your Luck Awaits</span>
                        <span class="text-[#66228a]">•</span>
                    </div>
                    <div class="marquee-content-reverse flex gap-12 font-serif-lux text-3xl md:text-5xl uppercase tracking-widest font-bold" aria-hidden="true">
                        <span class="gradient-text">Exciting Games & Unlimited Thrill</span>
                        <span class="text-[#66228a]">•</span>
                        <span>Your Luck Awaits</span>
                        <span class="text-[#66228a]">•</span>
                        <span class="gradient-text">Exciting Games & Unlimited Thrill</span>
                        <span class="text-[#66228a]">•</span>
                        <span>Your Luck Awaits</span>
                        <span class="text-[#66228a]">•</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- Section 3: Game Floor Swiper Catalog -->
        <section id="lobby" class="py-24 bg-[#07040e]">
            <div class="max-w-7xl mx-auto px-6">
                <!-- Catalog Header -->
                <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-6 mb-12">
                    <div class="space-y-2">
                        <p class="text-[#e4b777] font-serif-lux tracking-[0.25em] uppercase text-sm font-semibold">CASINO LOBBY</p>
                        <h2 class="text-3xl md:text-5xl font-serif-lux font-bold text-white">CHOOSE YOUR ADVENTURE</h2>
                    </div>
                    
                    <!-- Swiper Arrows -->
                    <div class="flex items-center gap-4">
                        <div class="flex gap-2">
                            <button id="cat_prev_arrow" class="w-10 h-10 rounded border border-white/10 bg-black/40 flex items-center justify-center text-white hover:border-[#e4b777] hover:text-[#e4b777] transition-all">←</button>
                            <button id="cat_next_arrow" class="w-10 h-10 rounded border border-white/10 bg-black/40 flex items-center justify-center text-white hover:border-[#e4b777] hover:text-[#e4b777] transition-all">→</button>
                        </div>
                    </div>
                </div>

                <!-- Game Swiper Container -->
                <div class="swiper-container overflow-hidden pb-12" id="cats_swiper">
                    <div class="swiper-wrapper">
                        <!-- Slide 1: Baccarat -->
                        <div class="swiper-slide bg-[#0c0818] border border-white/5 rounded-lg overflow-hidden group hover:border-[#e4b777]/30 transition-all flex flex-col h-full">
                            <div class="h-64 overflow-hidden relative">
                                <img src="https://images.unsplash.com/photo-1518609878373-06d740f60d8b?q=80&w=600&auto=format&fit=crop" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" alt="Baccarat">
                                <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                    <a href="{{ Route::has('register') ? route('register') : '#' }}" class="px-5 py-2.5 btn-luxury-gold text-xs rounded uppercase font-semibold">Play Lobby</a>
                                </div>
                            </div>
                            <div class="p-6 space-y-2 flex-grow">
                                <h3 class="text-xl font-serif-lux font-bold text-white group-hover:text-[#e4b777] transition-colors">BACCARAT</h3>
                                <p class="text-xs text-gray-400 leading-relaxed">Experience high-stakes card action with dedicated croupiers and VIP stakes limits.</p>
                            </div>
                        </div>

                        <!-- Slide 2: Roulette -->
                        <div class="swiper-slide bg-[#0c0818] border border-white/5 rounded-lg overflow-hidden group hover:border-[#e4b777]/30 transition-all flex flex-col h-full">
                            <div class="h-64 overflow-hidden relative">
                                <img src="https://images.unsplash.com/photo-1596838132731-3301c3fd4317?q=80&w=600&auto=format&fit=crop" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" alt="Roulette">
                                <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                    <a href="{{ Route::has('register') ? route('register') : '#' }}" class="px-5 py-2.5 btn-luxury-gold text-xs rounded uppercase font-semibold">Spin Wheel</a>
                                </div>
                            </div>
                            <div class="p-6 space-y-2 flex-grow">
                                <h3 class="text-xl font-serif-lux font-bold text-white group-hover:text-[#e4b777] transition-colors">ROULETTE</h3>
                                <p class="text-xs text-gray-400 leading-relaxed">Hear the bounce of the silver ball on our handcrafted luxury spinning wheels.</p>
                            </div>
                        </div>

                        <!-- Slide 3: Blackjack -->
                        <div class="swiper-slide bg-[#0c0818] border border-white/5 rounded-lg overflow-hidden group hover:border-[#e4b777]/30 transition-all flex flex-col h-full">
                            <div class="h-64 overflow-hidden relative">
                                <img src="https://images.unsplash.com/photo-1511193311914-0346f16efe90?q=80&w=600&auto=format&fit=crop" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" alt="Blackjack">
                                <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                    <a href="{{ Route::has('register') ? route('register') : '#' }}" class="px-5 py-2.5 btn-luxury-gold text-xs rounded uppercase font-semibold">Deal Cards</a>
                                </div>
                            </div>
                            <div class="p-6 space-y-2 flex-grow">
                                <h3 class="text-xl font-serif-lux font-bold text-white group-hover:text-[#e4b777] transition-colors">BLACKJACK</h3>
                                <p class="text-xs text-gray-400 leading-relaxed">Challenge the dealer directly. Instant verification, high limits, and wagers.</p>
                            </div>
                        </div>

                        <!-- Slide 4: Poker -->
                        <div class="swiper-slide bg-[#0c0818] border border-white/5 rounded-lg overflow-hidden group hover:border-[#e4b777]/30 transition-all flex flex-col h-full">
                            <div class="h-64 overflow-hidden relative">
                                <img src="https://images.unsplash.com/photo-1435527173128-983b87201f4d?q=80&w=600&auto=format&fit=crop" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" alt="Poker">
                                <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                    <a href="{{ Route::has('register') ? route('register') : '#' }}" class="px-5 py-2.5 btn-luxury-gold text-xs rounded uppercase font-semibold">Open Poker</a>
                                </div>
                            </div>
                            <div class="p-6 space-y-2 flex-grow">
                                <h3 class="text-xl font-serif-lux font-bold text-white group-hover:text-[#e4b777] transition-colors">TEXAS HOLD'EM</h3>
                                <p class="text-xs text-gray-400 leading-relaxed">Cash tables, regular tournaments, and professional tournament play directors.</p>
                            </div>
                        </div>

                        <!-- Slide 5: Andar Bahar -->
                        <div class="swiper-slide bg-[#0c0818] border border-white/5 rounded-lg overflow-hidden group hover:border-[#e4b777]/30 transition-all flex flex-col h-full">
                            <div class="h-64 overflow-hidden relative">
                                <img src="https://images.unsplash.com/photo-1606167668584-78701c57f13d?q=80&w=600&auto=format&fit=crop" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" alt="Andar Bahar">
                                <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                    <a href="{{ Route::has('register') ? route('register') : '#' }}" class="px-5 py-2.5 btn-luxury-gold text-xs rounded uppercase font-semibold">Play Now</a>
                                </div>
                            </div>
                            <div class="p-6 space-y-2 flex-grow">
                                <h3 class="text-xl font-serif-lux font-bold text-white group-hover:text-[#e4b777] transition-colors">ANDAR BAHAR</h3>
                                <p class="text-xs text-gray-400 leading-relaxed">Traditional Indian card games hosted by live human croupiers in premium studios.</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Progress pagination indicator -->
                    <div class="cat-pagination swiper-pagination-progressbar bg-white/10 mt-6 !relative !h-1 rounded-full"></div>
                </div>
            </div>
        </section>

        <!-- Section 4: Highlighted Stagger Info Section -->
        <section class="py-24 bg-[#050209] flex items-center justify-center border-t border-white/5">
            <div class="max-w-4xl mx-auto px-6 text-center general_text_wrap space-y-4">
                <p class="text-xs text-[#e4b777] tracking-[0.3em] font-semibold uppercase">Exclusive Hospitality</p>
                <div class="text-3xl md:text-5xl font-serif-lux text-white leading-relaxed font-bold">
                    <span>DRINKS SERVED RIGHT TO YOUR SEAT.</span> <br />
                    <span>ALWAYS FAST, ALWAYS FRESH.</span> <br />
                    <span>OPEN 24 HOURS A DAY.</span> <br />
                    <span class="gradient-text">BECAUSE YOUR LUCK DOESN’T SLEEP.</span>
                </div>
            </div>
        </section>

        <!-- Section 5: Slots Minigame Section -->
        <section id="slots" class="py-24 bg-[#07040e] border-t border-white/5 relative">
            <div class="max-w-7xl mx-auto px-6">
                <div class="text-center max-w-2xl mx-auto mb-12 space-y-3">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded border border-[#e4b777]/20 bg-[#66228a]/10 text-[#e4b777] text-xs font-semibold tracking-widest uppercase">
                        TRY LUCK BEFORE YOU BOOK
                    </div>
                    <h2 class="text-3xl md:text-5xl font-serif-lux font-bold text-white">WALDO LUXURY SLOT MACHINE</h2>
                    <p class="text-xs text-gray-400 leading-relaxed">
                        Test your luck on our premium interactive slot minigame. Spin and match symbols for free. Win up to 1,000 free bonus points redeemable at registration!
                    </p>
                </div>

                <!-- Slots Frame -->
                <div class="max-w-xl mx-auto rounded-lg border border-[#e4b777]/30 bg-[#0c0818]/65 p-8 shadow-2xl relative overflow-hidden glass-panel">
                    <div class="absolute inset-x-0 top-0 h-[1px] bg-gradient-to-r from-transparent via-[#e4b777] to-transparent"></div>
                    
                    <!-- Sound Toggle & Balance headers -->
                    <div class="flex justify-between items-center mb-8 border-b border-white/5 pb-4">
                        <div>
                            <span class="text-[10px] text-gray-400 uppercase tracking-widest font-semibold block">Bonus Balance</span>
                            <span class="font-serif-lux font-bold text-2xl text-white flex items-center gap-1.5">
                                <span class="text-[#e4b777]">⚡</span>
                                <span id="player-balance" class="gradient-text">1000</span> <span class="text-[10px] text-gray-500 font-normal">Points</span>
                            </span>
                        </div>
                        <div class="text-right">
                            <span class="text-[10px] text-gray-400 uppercase tracking-widest block font-semibold">Cost per Spin</span>
                            <span class="font-serif-lux font-bold text-xl text-[#e4b777]">100 Points</span>
                        </div>
                    </div>

                    <!-- Slots Reels Row -->
                    <div class="grid grid-cols-3 gap-4 mb-6">
                        <!-- Reel 1 -->
                        <div class="h-32 rounded bg-black/60 border border-white/5 flex items-center justify-center text-5xl shadow-inner relative overflow-hidden">
                            <div class="absolute inset-x-0 top-0 h-4 bg-gradient-to-b from-black/60 to-transparent pointer-events-none"></div>
                            <div id="reel-1" class="transition-all duration-300 font-semibold">🍊</div>
                            <div class="absolute inset-x-0 bottom-0 h-4 bg-gradient-to-t from-black/60 to-transparent pointer-events-none"></div>
                        </div>
                        <!-- Reel 2 -->
                        <div class="h-32 rounded bg-black/60 border border-white/5 flex items-center justify-center text-5xl shadow-inner relative overflow-hidden">
                            <div class="absolute inset-x-0 top-0 h-4 bg-gradient-to-b from-black/60 to-transparent pointer-events-none"></div>
                            <div id="reel-2" class="transition-all duration-300 font-semibold">🍊</div>
                            <div class="absolute inset-x-0 bottom-0 h-4 bg-gradient-to-t from-black/60 to-transparent pointer-events-none"></div>
                        </div>
                        <!-- Reel 3 -->
                        <div class="h-32 rounded bg-black/60 border border-white/5 flex items-center justify-center text-5xl shadow-inner relative overflow-hidden">
                            <div class="absolute inset-x-0 top-0 h-4 bg-gradient-to-b from-black/60 to-transparent pointer-events-none"></div>
                            <div id="reel-3" class="transition-all duration-300 font-semibold">🍊</div>
                            <div class="absolute inset-x-0 bottom-0 h-4 bg-gradient-to-t from-black/60 to-transparent pointer-events-none"></div>
                        </div>
                    </div>

                    <!-- Audio option and Alerts -->
                    <div class="flex items-center justify-between mb-6">
                        <label class="flex items-center gap-2 cursor-pointer text-[10px] text-gray-400 hover:text-white transition-colors">
                            <input type="checkbox" id="audio-toggle" checked class="accent-[#66228a] w-4 h-4 rounded">
                            <span>Enable Synth Sound Effects</span>
                        </label>
                        <span id="win-message" class="text-xs font-bold text-[#e4b777] animate-pulse hidden"></span>
                    </div>

                    <!-- Spin Button -->
                    <button id="spin-btn" class="w-full py-4 rounded btn-luxury-gold text-lg tracking-widest font-serif-lux font-bold transition-all flex items-center justify-center gap-2">
                        ⚡ SPIN MATRIX ⚡
                    </button>

                    <!-- Payouts Table -->
                    <div class="mt-8 pt-6 border-t border-white/5 text-[10px] text-gray-500 grid grid-cols-2 gap-4">
                        <div class="space-y-1">
                            <span class="font-bold text-gray-400 uppercase block tracking-wider font-serif-lux text-xs">Payouts Matrix</span>
                            <div class="flex justify-between"><span>🎰 🎰 🎰 (Jackpot)</span> <span class="text-[#e4b777] font-semibold">+1,000</span></div>
                            <div class="flex justify-between"><span>👑 👑 👑 (Royal)</span> <span class="text-[#e4b777] font-semibold">+500</span></div>
                            <div class="flex justify-between"><span>💎 💎 💎 (Crystal)</span> <span class="text-[#e4b777] font-semibold">+300</span></div>
                        </div>
                        <div class="space-y-1 flex flex-col justify-end">
                            <div class="flex justify-between"><span>⚡ ⚡ ⚡ (Energy)</span> <span class="text-[#e4b777] font-semibold">+200</span></div>
                            <div class="flex justify-between"><span>🍊 🍊 🍊 (Waldo Orange)</span> <span class="text-[#e4b777] font-semibold">+150</span></div>
                            <div class="flex justify-between"><span>Any 2 Match</span> <span class="text-[#e4b777] font-semibold">+50</span></div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Section 6: Live Global Jackpot Tracker -->
        <section id="jackpot" class="py-20 bg-black border-y border-white/5 relative overflow-hidden">
            <div class="max-w-7xl mx-auto px-6 text-center space-y-4">
                <p class="text-[#e4b777] font-serif-lux font-bold tracking-[0.3em] uppercase text-sm">Waldo Progressive Jackpot</p>
                <h2 class="text-3xl md:text-5xl font-serif-lux font-bold text-white">OUR HIGHEST ACCUMULATED PRIZE</h2>
                
                <div class="inline-flex items-center justify-center px-10 py-6 rounded bg-[#0c0818] border border-[#e4b777]/35 shadow-2xl relative overflow-hidden gold-border-glow group">
                    <span class="font-serif-lux font-bold text-4xl md:text-6xl text-white tracking-widest gradient-text" id="jackpot-counter">
                        9,482,102.40
                    </span>
                    <span class="text-[#e4b777] font-serif-lux font-bold text-xl md:text-3xl ml-3 tracking-wider">
                        USD
                    </span>
                </div>
                
                <p class="text-gray-500 text-xs">Calculated from total network volume across 100+ partner game rooms.</p>
            </div>
        </section>

        <!-- Section 7: VIP Room Section -->
        <section id="vip" class="relative min-h-[90vh] flex items-center justify-center py-24 overflow-hidden bg-[#050209]">
            <!-- Background Video Loop / Fallback -->
            <div class="absolute inset-0 w-full h-full bg-[#050209] z-[-2]">
                <video autoplay loop muted playsinline class="w-full h-full object-cover opacity-20 scale-105 select-none pointer-events-none">
                    <source src="https://player.vimeo.com/external/370467543.sd.mp4?s=d04b6d44547900b14c30c5e7b233a75877ee5266&profile_id=165&oauth2_token_id=57447761" type="video/mp4">
                </video>
            </div>
            <!-- Overlay Masking -->
            <div class="absolute inset-0 bg-gradient-to-t from-[#050209] via-transparent to-[#050209] z-[-1]"></div>

            <div class="max-w-7xl mx-auto w-full px-6 grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
                <!-- Text contents Left -->
                <div class="lg:col-span-6 space-y-6">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded border border-[#e4b777]/20 bg-[#66228a]/10 text-[#e4b777] text-xs font-semibold tracking-widest uppercase">
                        WALDO CLUB MEMBERSHIP
                    </div>
                    <h2 class="text-4xl md:text-5xl font-serif-lux font-bold text-white leading-tight">
                        PLAY LIKE A LEGEND <br />
                        <span class="gradient-text">EXCLUSIVELY FOR HIGH ROLLERS</span>
                    </h2>
                    <p class="text-gray-400 text-sm leading-relaxed">
                        Earn VIP status points with every wager you make. Claim weekly rakebacks, custom hospitality packages, higher limit withdrawals, and a personal host manager.
                    </p>
                    <ul class="space-y-3 font-semibold font-serif-lux text-lg tracking-wide text-gray-200">
                        <li class="flex items-center gap-3"><span class="text-[#e4b777]">⚡</span> Weekly VIP Rakebacks (Up to 15%)</li>
                        <li class="flex items-center gap-3"><span class="text-[#e4b777]">⚡</span> Priority Customer Support Response &lt; 1 Minute</li>
                        <li class="flex items-center gap-3"><span class="text-[#e4b777]">⚡</span> Personal VIP Account Managers & Butler Service</li>
                    </ul>
                    <div class="pt-4">
                        <a href="{{ Route::has('register') ? route('register') : '#' }}" class="px-8 py-4 rounded btn-luxury-gold text-xs font-semibold">
                            JOIN THE VIP CLUB
                        </a>
                    </div>
                </div>

                <!-- Interactive sequential blurred cards Right -->
                <div class="lg:col-span-6 flex justify-center items-center relative min-h-[300px] w-full font-sans">
                    <!-- Card 1 -->
                    <div class="vip-feature-card absolute w-full max-w-sm rounded border border-[#e4b777]/20 bg-black/60 p-8 text-center glass-panel shadow-2xl flex flex-col justify-center items-center h-56">
                        <span class="text-4xl mb-4">👑</span>
                        <h4 class="text-xl font-serif-lux font-bold text-[#e4b777] mb-2 uppercase tracking-wide">ELITE LIMITS</h4>
                        <p class="text-xs text-gray-400 leading-relaxed font-sans">High-limit betting lounges and private tables with bespoke rules and maximum security.</p>
                    </div>

                    <!-- Card 2 -->
                    <div class="vip-feature-card absolute w-full max-w-sm rounded border border-[#e4b777]/20 bg-black/60 p-8 text-center glass-panel shadow-2xl flex flex-col justify-center items-center h-56">
                        <span class="text-4xl mb-4">🤵</span>
                        <h4 class="text-xl font-serif-lux font-bold text-[#e4b777] mb-2 uppercase tracking-wide">VIP HOSTS</h4>
                        <p class="text-xs text-gray-400 leading-relaxed font-sans font-medium">Personalized VIP host coordinators and butler services dedicated 24/7 to players.</p>
                    </div>

                    <!-- Card 3 -->
                    <div class="vip-feature-card absolute w-full max-w-sm rounded border border-[#e4b777]/20 bg-black/60 p-8 text-center glass-panel shadow-2xl flex flex-col justify-center items-center h-56">
                        <span class="text-4xl mb-4">🗝️</span>
                        <h4 class="text-xl font-serif-lux font-bold text-[#e4b777] mb-2 uppercase tracking-wide">SECURE ACCESS</h4>
                        <p class="text-xs text-gray-400 leading-relaxed font-sans">Exclusive, private salon entries ensuring ultimate discretion and custom limits.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Section 8: Offers & Lobby Games Section -->
        <section class="py-24 bg-[#07040e] border-t border-white/5 relative offers-section overflow-hidden">
            <!-- Parallax decorations -->
            <div class="absolute left-[-100px] top-[20%] w-[350px] opacity-15 select-none pointer-events-none roulette-wheel-img">
                <img src="https://images.unsplash.com/photo-1596838132731-3301c3fd4317?q=80&w=400&auto=format&fit=crop" class="w-full rounded-full" alt="wheel">
            </div>

            <div class="max-w-7xl mx-auto px-6">
                <div class="text-center max-w-2xl mx-auto mb-16 space-y-3">
                    <p class="text-[#e4b777] font-serif-lux tracking-[0.25em] uppercase text-sm font-semibold">EXCLUSIVE EVENTS</p>
                    <h2 class="text-3xl md:text-5xl font-serif-lux font-bold text-white">UNLOCK EXCITING OFFERS</h2>
                </div>

                <!-- Games Grid -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    <!-- Game Card 1 -->
                    <div class="group rounded bg-[#0c0818] border border-white/5 p-6 hover:border-[#e4b777]/35 transition-all duration-300 relative overflow-hidden flex flex-col justify-between min-h-[380px] hover:shadow-2xl font-sans">
                        <div>
                            <div class="h-44 rounded bg-black/45 border border-white/5 flex items-center justify-center text-5xl mb-6 relative overflow-hidden">
                                🎰
                                <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                    <a href="{{ Route::has('register') ? route('register') : '#' }}" class="px-5 py-2 btn-luxury-gold text-xs rounded uppercase font-semibold">Play Now</a>
                                </div>
                            </div>
                            <span class="text-[#e4b777] font-serif-lux font-bold text-xs uppercase tracking-wider block mb-1">Cyber Slots</span>
                            <h3 class="text-2xl font-serif-lux font-bold text-white mb-2">HYPERION REELS</h3>
                            <p class="text-xs text-gray-400 leading-relaxed font-sans">
                                Featuring wild multipliers, volatile bonus scatter triggers, and over 1,024 ways to score high.
                            </p>
                        </div>
                        <div class="pt-6">
                            <span class="text-[10px] text-gray-500 uppercase tracking-widest font-sans">WAGER REWARDS MATCHED</span>
                        </div>
                    </div>

                    <!-- Game Card 2 -->
                    <div class="group rounded bg-[#0c0818] border border-white/5 p-6 hover:border-[#e4b777]/35 transition-all duration-300 relative overflow-hidden flex flex-col justify-between min-h-[380px] hover:shadow-2xl font-sans">
                        <div>
                            <div class="h-44 rounded bg-black/45 border border-white/5 flex items-center justify-center text-5xl mb-6 relative overflow-hidden">
                                🎡
                                <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                    <a href="{{ Route::has('register') ? route('register') : '#' }}" class="px-5 py-2 btn-luxury-gold text-xs rounded uppercase font-semibold">Play Now</a>
                                </div>
                            </div>
                            <span class="text-[#e4b777] font-serif-lux font-bold text-xs uppercase tracking-wider block mb-1">Live Croupier</span>
                            <h3 class="text-2xl font-serif-lux font-bold text-white mb-2">NEON ROULETTE</h3>
                            <p class="text-xs text-gray-400 leading-relaxed font-sans">
                                Place digital tokens on the live spinning wheel. Streamed in HD with interactive chat features.
                            </p>
                        </div>
                        <div class="pt-6">
                            <span class="text-[10px] text-gray-500 uppercase tracking-widest font-sans">HD LIVE STREAMING</span>
                        </div>
                    </div>

                    <!-- Game Card 3 -->
                    <div class="group rounded bg-[#0c0818] border border-white/5 p-6 hover:border-[#e4b777]/35 transition-all duration-300 relative overflow-hidden flex flex-col justify-between min-h-[380px] hover:shadow-2xl font-sans">
                        <div>
                            <div class="h-44 rounded bg-black/45 border border-white/5 flex items-center justify-center text-5xl mb-6 relative overflow-hidden">
                                🃏
                                <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                    <a href="{{ Route::has('register') ? route('register') : '#' }}" class="px-5 py-2 btn-luxury-gold text-xs rounded uppercase font-semibold">Play Now</a>
                                </div>
                            </div>
                            <span class="text-[#e4b777] font-serif-lux font-bold text-xs uppercase tracking-wider block mb-1">Card Classics</span>
                            <h3 class="text-2xl font-serif-lux font-bold text-white mb-2">QUANTUM BLACKJACK</h3>
                            <p class="text-xs text-gray-400 leading-relaxed font-sans">
                                Challenge experienced dealers directly. Options include perfect pairs side bets and early surrender rules.
                            </p>
                        </div>
                        <div class="pt-6">
                            <span class="text-[10px] text-gray-500 uppercase tracking-widest font-sans">99.5% THEORETICAL RTP</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Parallax table container -->
            <div class="flex justify-center mt-12 w-full select-none pointer-events-none opacity-20 roulette-table-img">
                <img src="https://images.unsplash.com/photo-1518609878373-06d740f60d8b?q=80&w=600&auto=format&fit=crop" class="w-full max-w-2xl rounded-lg" alt="table border">
            </div>
        </section>

        <!-- Section 9: Stay Section -->
        <section id="stay" class="py-24 bg-[#050209] border-t border-white/5 relative font-sans">
            <div class="max-w-7xl mx-auto px-6 grid grid-cols-1 lg:grid-cols-2 gap-16 items-center">
                <div class="space-y-6">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded border border-[#e4b777]/20 bg-[#66228a]/10 text-[#e4b777] text-xs font-semibold tracking-widest uppercase font-sans">
                        STAY WITH US
                    </div>
                    <h2 class="text-4xl md:text-5xl font-serif-lux font-bold text-white leading-tight">
                        STAY WHERE YOU PLAY
                    </h2>
                    <p class="text-gray-400 text-sm leading-relaxed font-sans">
                        Indulge in 5-star comfort with exclusive hotel-to-casino access. Avoid traffic, enjoy dedicated concierge assistance, and unlock elite suites featuring mountain views.
                    </p>
                    <div class="flex gap-4 pt-4 font-sans">
                        <a href="#contact" class="px-6 py-3 rounded btn-luxury-gold text-xs font-semibold uppercase">BOOK SUITE NOW</a>
                        <a href="#contact" class="px-6 py-3 rounded btn-luxury-outline text-xs font-semibold uppercase">VIEW ROOMS</a>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-8 font-sans">
                    <div class="p-6 rounded border border-white/5 bg-[#0c0818] space-y-3">
                        <span class="text-3xl">🔑</span>
                        <h4 class="font-serif-lux font-bold text-lg text-white">Direct Access</h4>
                        <p class="text-xs text-gray-400 leading-relaxed font-sans">Step straight from the luxury hotel elevator onto the high-stakes gaming floor.</p>
                    </div>

                    <div class="p-6 rounded border border-white/5 bg-[#0c0818] space-y-3">
                        <span class="text-3xl">🛎️</span>
                        <h4 class="font-serif-lux font-bold text-lg text-white">24/7 Butler Service</h4>
                        <p class="text-xs text-gray-400 leading-relaxed font-sans">Room service, spa treatments, private bars, and dedicated drivers available.</p>
                    </div>

                    <div class="p-6 rounded border border-white/5 bg-[#0c0818] space-y-3">
                        <span class="text-3xl">🍷</span>
                        <h4 class="font-serif-lux font-bold text-lg text-white">Fine Dining</h4>
                        <p class="text-xs text-gray-400 leading-relaxed font-sans font-medium">Multi-cuisine restaurants and whiskey lounges curated by master chefs.</p>
                    </div>

                    <div class="p-6 rounded border border-white/5 bg-[#0c0818] space-y-3">
                        <span class="text-3xl">⛰️</span>
                        <h4 class="font-serif-lux font-bold text-lg text-white">Resort Layout</h4>
                        <p class="text-xs text-gray-400 leading-relaxed font-sans">Ideal setup for international solo VIP rollers or team events.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Section 10: Swiper Continuous Gallery Ticker -->
        <section class="py-12 bg-black border-y border-white/5 overflow-hidden">
            <div class="swiper-container" id="top_rotating_slides">
                <div class="swiper-wrapper select-none pointer-events-none">
                    <div class="swiper-slide h-44 rounded overflow-hidden">
                        <img src="https://images.unsplash.com/photo-1564507592333-c60657eea523?q=80&w=500&auto=format&fit=crop" class="w-full h-full object-cover" alt="Hotel">
                    </div>
                    <div class="swiper-slide h-44 rounded overflow-hidden">
                        <img src="https://images.unsplash.com/photo-1514362545857-3bc16c4c7d1b?q=80&w=500&auto=format&fit=crop" class="w-full h-full object-cover" alt="Bar">
                    </div>
                    <div class="swiper-slide h-44 rounded overflow-hidden">
                        <img src="https://images.unsplash.com/photo-1590490360182-c33d57733427?q=80&w=500&auto=format&fit=crop" class="w-full h-full object-cover" alt="Suite">
                    </div>
                    <div class="swiper-slide h-44 rounded overflow-hidden">
                        <img src="https://images.unsplash.com/photo-1540747737956-37872404f80f?q=80&w=500&auto=format&fit=crop" class="w-full h-full object-cover" alt="VIP">
                    </div>
                    <div class="swiper-slide h-44 rounded overflow-hidden">
                        <img src="https://images.unsplash.com/photo-1596838132731-3301c3fd4317?q=80&w=500&auto=format&fit=crop" class="w-full h-full object-cover" alt="Slots">
                    </div>
                </div>
            </div>
        </section>

        <!-- Section 11: Contact & Booking Footer -->
        <footer id="contact" class="bg-[#030106] border-t border-white/5 py-16 px-6 font-sans">
            <div class="max-w-7xl mx-auto space-y-12">
                <!-- Contact Prompts Row -->
                <div class="text-center space-y-6">
                    <p class="text-[#e4b777] font-serif-lux font-semibold tracking-widest text-sm uppercase">Quick Inquiries</p>
                    <h2 class="text-3xl md:text-5xl font-serif-lux font-bold text-white">BOOK, RESERVE, OR ASK US ANYTHING</h2>
                    <p class="text-gray-400 text-xs max-w-lg mx-auto font-sans">We process hotel reservations, luxury airport pickups, private salon bookings, and group events 24 hours a day, 7 days a week.</p>
                    
                    <div class="flex flex-wrap items-center justify-center gap-4 pt-4 font-sans">
                        <a href="tel:+9771234567" class="px-6 py-3 rounded btn-luxury-gold text-xs font-semibold">📞 CALL RESERVATIONS</a>
                        <a href="https://wa.me/977987654321" target="_blank" class="px-6 py-3 rounded btn-luxury-outline text-xs font-semibold">💬 WHATSAPP VIP ACCESS</a>
                    </div>
                </div>

                <!-- Accepted Payment Providers Row -->
                <div class="flex flex-wrap items-center justify-center gap-8 border-y border-white/5 py-8 text-xs text-gray-500 font-semibold tracking-widest font-sans">
                    <span>ACCEPTED TRANSACTIONS:</span>
                    <span class="text-gray-300">₿ BITCOIN</span>
                    <span class="text-gray-300">♦ ETHEREUM</span>
                    <span class="text-gray-300">⚡ LITECOIN</span>
                    <span class="text-gray-300">₮ USDT</span>
                    <span class="text-gray-300">VISA</span>
                    <span class="text-gray-300">MASTERCARD</span>
                </div>

                <!-- Regulatory details and Logo -->
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start font-sans">
                    <div class="lg:col-span-8 space-y-4">
                        <div class="flex items-center gap-2">
                            <span class="font-serif-lux font-bold text-xl tracking-widest text-white leading-none">
                                WALDO <span class="gradient-text font-medium">CASINO</span>
                            </span>
                        </div>
                        <p class="text-[10px] text-gray-600 leading-relaxed font-sans">
                            Waldo Casino is owned and operated by Waldo Entertainment N.V., a company registered and established under the laws of Curaçao, with registration number 164805. Waldo Entertainment N.V. is licensed and regulated by the Gaming Control Board of Curaçao (GCB) under license number 8048/JAZ. Payments are processed by Waldo Payments Ltd. registered under company number HE 40291.
                        </p>
                        <p class="text-[10px] text-gray-600 font-sans">
                            © 2026 Waldo Casino. All rights reserved. Provably fair gaming systems.
                        </p>
                    </div>

                    <div class="lg:col-span-4 space-y-4 lg:text-right font-sans">
                        <div class="inline-flex items-center gap-3">
                            <span class="w-10 h-10 rounded-full border border-gray-800 flex items-center justify-center text-xs font-bold text-gray-600">18+</span>
                            <span class="px-3 py-1 rounded border border-gray-800 text-[9px] font-bold text-gray-600 tracking-widest uppercase font-sans">PROVABLY FAIR</span>
                        </div>
                        <div class="text-[10px] text-gray-500 space-y-1 font-sans">
                            <p class="font-bold text-gray-400 font-serif-lux text-xs">RESPONSIBLE GAMING</p>
                            <p>Gambling can be addictive. Please play responsibly.</p>
                            <p>For support, contact BeGambleAware.org.</p>
                        </div>
                    </div>
                </div>
            </div>
        </footer>

        <!-- Javascript Scripts: GSAP, ScrollTrigger, Swiper, Lenis -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
        <script src="https://unpkg.com/lenis@1.1.18/dist/lenis.min.js"></script>

        <script>
            // Register GSAP ScrollTrigger
            gsap.registerPlugin(ScrollTrigger);

            // Initialize Lenis Smooth Scroll
            const lenis = new Lenis();
            function raf(time) {
                lenis.raf(time);
                requestAnimationFrame(raf);
            }
            requestAnimationFrame(raf);

            // Mobile menu transition
            const hema = document.querySelector('.hema');
            const mobileMenu = document.querySelector('.mobile_menu');
            const closeIcon = document.querySelector('.mobile_menu .close_icon');
            const gotoDivs = document.querySelectorAll('.goto_div');

            if (hema && mobileMenu) {
                hema.addEventListener('click', () => {
                    mobileMenu.classList.add('active');
                });
                if (closeIcon) {
                    closeIcon.addEventListener('click', () => {
                        mobileMenu.classList.remove('active');
                    });
                }
                gotoDivs.forEach(div => {
                    div.addEventListener('click', (e) => {
                        mobileMenu.classList.remove('active');
                        // Use Lenis for smooth routing target navigation
                        const targetId = div.getAttribute('data-id');
                        const targetEl = document.getElementById(targetId);
                        if (targetEl) {
                            e.preventDefault();
                            lenis.scrollTo(targetEl);
                        }
                    });
                });
            }

            // Header Scroll Effects (Hide/reveal & styling)
            let lastScrollTop = 0;
            const header = document.querySelector('.header-wrap');
            window.addEventListener('scroll', () => {
                let wsc = window.pageYOffset || document.documentElement.scrollTop;
                if (wsc > 50) {
                    header.classList.add('scrolled');
                    if (wsc > lastScrollTop) {
                        header.classList.add('scrolled_hide');
                    } else {
                        header.classList.remove('scrolled_hide');
                    }
                } else {
                    header.classList.remove('scrolled', 'scrolled_hide');
                }
                lastScrollTop = wsc;
            });

            // Initialize Swiper for Game Floor
            const catsSwiper = new Swiper("#cats_swiper", {
                slidesPerView: 1.2,
                spaceBetween: 20,
                loop: false,
                navigation: {
                    nextEl: "#cat_next_arrow",
                    prevEl: "#cat_prev_arrow",
                },
                pagination: {
                    el: ".cat-pagination",
                    type: "progressbar",
                },
                breakpoints: {
                    480: { slidesPerView: 1.8, spaceBetween: 20 },
                    768: { slidesPerView: 2.8, spaceBetween: 20 },
                    1100: { slidesPerView: 4, spaceBetween: 20 }
                }
            });

            // Initialize Swiper for Gallery Ticker (Infinite slide match)
            const gallerySwiper = new Swiper("#top_rotating_slides", {
                loop: true,
                allowTouchMove: false,
                autoplay: {
                    delay: 0,
                    disableOnInteraction: false,
                },
                speed: 5000,
                slidesPerView: 2,
                spaceBetween: 16,
                breakpoints: {
                    640: { slidesPerView: 3 },
                    1024: { slidesPerView: 5 }
                }
            });

            // GSAP word highlights stagger scroll trigger
            gsap.from(".general_text_wrap span", {
                scrollTrigger: {
                    trigger: ".general_text_wrap",
                    start: "top 80%",
                    end: "bottom 60%",
                    scrub: true,
                },
                opacity: 0.2,
                y: 20,
                stagger: 0.1
            });

            // GSAP VIP sequential blurred cards loops
            const vipCards = document.querySelectorAll('.vip-feature-card');
            if (vipCards.length > 0) {
                gsap.set(vipCards, { opacity: 0, scale: 0.9, y: 15, pointerEvents: 'none' });
                const tl = gsap.timeline({ repeat: -1 });
                vipCards.forEach((card) => {
                    tl.to(card, { opacity: 1, scale: 1, y: 0, pointerEvents: 'auto', duration: 0.8 })
                      .to(card, { opacity: 0, scale: 0.9, y: -15, pointerEvents: 'none', duration: 0.6, delay: 2.5 });
                });
            }

            // GSAP Offers Parallax Decorations
            gsap.to(".roulette-wheel-img", {
                scrollTrigger: {
                    trigger: ".offers-section",
                    start: "top bottom",
                    end: "bottom top",
                    scrub: true
                },
                rotation: 360,
                ease: "none"
            });
            gsap.from(".roulette-table-img", {
                scrollTrigger: {
                    trigger: ".offers-section",
                    start: "top bottom",
                    end: "bottom center",
                    scrub: true
                },
                y: 80,
                ease: "power1.out"
            });

            // Live Jackpot Progressive Counter
            const jackpotElement = document.getElementById('jackpot-counter');
            let jackpotAmount = 9482102.40;
            
            setInterval(() => {
                const increment = Math.random() * 0.45 + 0.05;
                jackpotAmount += increment;
                if (jackpotElement) {
                    jackpotElement.textContent = jackpotAmount.toLocaleString(undefined, {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                }
            }, 350);

            // Live Winner Feed Simulator
            const winnersList = document.getElementById('winners-list');
            const winnerNames = ['GoldFlas***', 'SpinMas***', 'CryptoK***', 'WaldoFa***', 'HighRoll***', 'MegaWin***', 'AceSpad***'];
            const gameNames = ['Baccarat Lobby', 'Neon Roulette', 'Quantum Blackjack', 'Texas Holdem', 'Hyperion Reels'];
            
            setInterval(() => {
                if (!winnersList) return;
                const name = winnerNames[Math.floor(Math.random() * winnerNames.length)];
                const game = gameNames[Math.floor(Math.random() * gameNames.length)];
                const winAmount = (Math.random() * 4500 + 50).toFixed(2);
                
                // Create element
                const newWinner = document.createElement('div');
                newWinner.className = 'flex justify-between items-center p-3 rounded bg-white/5 border border-white/5 hover:border-[#e4b777]/25 transition-all transform -translate-y-2 opacity-0';
                newWinner.innerHTML = `
                    <div>
                        <div class="text-xs font-semibold text-white">Player: ${name}</div>
                        <div class="text-[10px] text-gray-500">Won on ${game}</div>
                    </div>
                    <div class="text-right">
                        <div class="text-xs font-bold text-[#e4b777]">+$${parseFloat(winAmount).toLocaleString()}</div>
                        <div class="text-[10px] text-gray-500">Just now</div>
                    </div>
                `;
                
                winnersList.prepend(newWinner);
                // Keep list size constant
                if (winnersList.children.length > 3) {
                    winnersList.removeChild(winnersList.lastChild);
                }

                // Trigger transition
                setTimeout(() => {
                    newWinner.classList.remove('-translate-y-2', 'opacity-0');
                }, 50);
            }, 5000);

            // Audio sound effects using Web Audio API
            function playSynthSound(type) {
                if (!document.getElementById('audio-toggle').checked) return;
                
                try {
                    const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                    
                    if (type === 'spin') {
                        const osc = audioCtx.createOscillator();
                        const gain = audioCtx.createGain();
                        osc.type = 'sawtooth';
                        osc.frequency.setValueAtTime(150, audioCtx.currentTime);
                        osc.frequency.exponentialRampToValueAtTime(800, audioCtx.currentTime + 0.5);
                        
                        gain.gain.setValueAtTime(0.08, audioCtx.currentTime);
                        gain.gain.linearRampToValueAtTime(0.01, audioCtx.currentTime + 0.5);
                        
                        osc.connect(gain);
                        gain.connect(audioCtx.destination);
                        osc.start();
                        osc.stop(audioCtx.currentTime + 0.5);
                    } else if (type === 'win') {
                        const notes = [523.25, 659.25, 783.99, 1046.50];
                        notes.forEach((freq, index) => {
                            const osc = audioCtx.createOscillator();
                            const gain = audioCtx.createGain();
                            osc.type = 'triangle';
                            osc.frequency.setValueAtTime(freq, audioCtx.currentTime + index * 0.1);
                            
                            gain.gain.setValueAtTime(0.08, audioCtx.currentTime + index * 0.1);
                            gain.gain.linearRampToValueAtTime(0.01, audioCtx.currentTime + index * 0.1 + 0.4);
                            
                            osc.connect(gain);
                            gain.connect(audioCtx.destination);
                            osc.start(audioCtx.currentTime + index * 0.1);
                            osc.stop(audioCtx.currentTime + index * 0.1 + 0.4);
                        });
                    } else if (type === 'lose') {
                        const osc = audioCtx.createOscillator();
                        const gain = audioCtx.createGain();
                        osc.type = 'sine';
                        osc.frequency.setValueAtTime(220, audioCtx.currentTime);
                        osc.frequency.linearRampToValueAtTime(90, audioCtx.currentTime + 0.4);
                        
                        gain.gain.setValueAtTime(0.08, audioCtx.currentTime);
                        gain.gain.linearRampToValueAtTime(0.01, audioCtx.currentTime + 0.4);
                        
                        osc.connect(gain);
                        gain.connect(audioCtx.destination);
                        osc.start();
                        osc.stop(audioCtx.currentTime + 0.4);
                    }
                } catch (e) {
                    console.log('AudioContext not allowed or supported', e);
                }
            }

            // Slots Minigame Logic
            const symbols = ['🍊', '💎', '⚡', '🎰', '👑', '🌌'];
            let balance = 1000;
            const cost = 100;
            
            const spinBtn = document.getElementById('spin-btn');
            const balanceEl = document.getElementById('player-balance');
            const winMessageEl = document.getElementById('win-message');
            
            const reels = [
                document.getElementById('reel-1'),
                document.getElementById('reel-2'),
                document.getElementById('reel-3')
            ];

            let isSpinning = false;

            spinBtn.addEventListener('click', () => {
                if (isSpinning) return;
                
                if (balance < cost) {
                    alert("Insufficient points! Your balance has been reloaded with 1,000 free points.");
                    balance = 1000;
                    balanceEl.textContent = balance;
                    return;
                }

                // Deduct cost
                balance -= cost;
                balanceEl.textContent = balance;
                isSpinning = true;
                spinBtn.disabled = true;
                spinBtn.textContent = "ROLLING MATRIX...";
                winMessageEl.classList.add('hidden');

                playSynthSound('spin');

                // Reel spinning animations
                let spinCount = 0;
                const spinInterval = setInterval(() => {
                    reels.forEach(reel => {
                        const randomIndex = Math.floor(Math.random() * symbols.length);
                        reel.textContent = symbols[randomIndex];
                    });
                    spinCount++;
                    
                    if (spinCount > 12) {
                        clearInterval(spinInterval);
                        finalizeSpin();
                    }
                }, 80);
            });

            function finalizeSpin() {
                const finalResult = [];
                reels.forEach(reel => {
                    const randomIndex = Math.floor(Math.random() * symbols.length);
                    reel.textContent = symbols[randomIndex];
                    finalResult.push(symbols[randomIndex]);
                });

                const [s1, s2, s3] = finalResult;
                let wonAmount = 0;
                let wonMessage = "";

                if (s1 === s2 && s2 === s3) {
                    // Match 3
                    if (s1 === '🎰') {
                        wonAmount = 1000;
                        wonMessage = "💥 BONUS JACKPOT! +1,000 Points!";
                    } else if (s1 === '👑') {
                        wonAmount = 500;
                        wonMessage = "👑 ROYAL DECREE! +500 Points!";
                    } else if (s1 === '💎') {
                        wonAmount = 300;
                        wonMessage = "💎 CRYPTO GEMS! +300 Points!";
                    } else if (s1 === '⚡') {
                        wonAmount = 200;
                        wonMessage = "⚡ CHARGED ENERGY! +200 Points!";
                    } else if (s1 === '🍊') {
                        wonAmount = 150;
                        wonMessage = "🍊 WALDO ORANGES! +150 Points!";
                    } else {
                        wonAmount = 100;
                        wonMessage = "🌌 COSMIC UNION! +100 Points!";
                    }
                } else if (s1 === s2 || s2 === s3 || s1 === s3) {
                    // Match 2
                    wonAmount = 50;
                    wonMessage = "✨ DOUBLE MATCH! +50 Points!";
                }

                if (wonAmount > 0) {
                    balance += wonAmount;
                    balanceEl.textContent = balance;
                    winMessageEl.textContent = wonMessage;
                    winMessageEl.classList.remove('hidden');
                    playSynthSound('win');
                } else {
                    playSynthSound('lose');
                }

                isSpinning = false;
                spinBtn.disabled = false;
                spinBtn.textContent = "⚡ SPIN MATRIX ⚡";
            }
        </script>
    </body>
</html>
