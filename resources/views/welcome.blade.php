<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Waldo Casino | Premium Decentralized Gaming & Live Dealers</title>
        
        <!-- Google Fonts: Rajdhani & Space Grotesk -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@500;600;700&family=Space+Grotesk:wght@400;500;700&display=swap" rel="stylesheet">

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
                                rajdhani: ['Rajdhani', 'sans-serif'],
                                sans: ['Space Grotesk', 'sans-serif'],
                            },
                            colors: {
                                cyber: {
                                    dark: '#050508',
                                    card: '#0c0d14',
                                    orange: '#ff6600',
                                    accent: '#ff9900',
                                    border: '#2e1c0c',
                                }
                            }
                        }
                    }
                }
            </script>
        @endif

        <style>
            /* Custom Scrollbar */
            ::-webkit-scrollbar {
                width: 8px;
            }
            ::-webkit-scrollbar-track {
                background: #050508;
            }
            ::-webkit-scrollbar-thumb {
                background: #ff6600;
                border-radius: 4px;
            }
            ::-webkit-scrollbar-thumb:hover {
                background: #ff9900;
            }

            /* Neon text and border glows */
            .neon-text-orange {
                text-shadow: 0 0 5px rgba(255, 102, 0, 0.5), 0 0 15px rgba(255, 102, 0, 0.7), 0 0 30px rgba(255, 102, 0, 0.9);
            }
            .neon-border-orange {
                box-shadow: 0 0 10px rgba(255, 102, 0, 0.2), inset 0 0 10px rgba(255, 102, 0, 0.1);
                border-color: rgba(255, 102, 0, 0.4);
            }
            .neon-btn-glow:hover {
                box-shadow: 0 0 20px rgba(255, 102, 0, 0.6);
            }
            
            /* Background Grid Overlay */
            .grid-bg {
                background-size: 40px 40px;
                background-image: 
                    linear-gradient(to right, rgba(255, 102, 0, 0.04) 1px, transparent 1px),
                    linear-gradient(to bottom, rgba(255, 102, 0, 0.04) 1px, transparent 1px);
            }

            /* Keyframes for futuristic animations */
            @keyframes scanline {
                0% { transform: translateY(-100%); }
                100% { transform: translateY(100%); }
            }
            .scanline::after {
                content: '';
                position: absolute;
                top: 0; left: 0; right: 0; bottom: 0;
                background: linear-gradient(to bottom, transparent 50%, rgba(255, 102, 0, 0.08) 50%);
                background-size: 100% 4px;
                pointer-events: none;
            }

            @keyframes pulse-slow {
                0%, 100% { opacity: 0.2; }
                50% { opacity: 0.6; }
            }
            .glow-pulse {
                animation: pulse-slow 4s infinite ease-in-out;
            }

            /* Scroll Reveal Initial Classes */
            .scroll-reveal {
                opacity: 0;
                transform: translateY(30px);
                transition: opacity 0.8s cubic-bezier(0.16, 1, 0.3, 1), transform 0.8s cubic-bezier(0.16, 1, 0.3, 1);
            }
            .scroll-reveal.active {
                opacity: 1;
                transform: translateY(0);
            }
        </style>
    </head>
    <body class="bg-[#050508] text-gray-200 font-sans min-h-screen relative overflow-x-hidden antialiased grid-bg scanline">
        
        <!-- Glowing background blobs -->
        <div class="absolute top-[-10%] left-[-10%] w-[50vw] h-[50vw] bg-[#ff6600] rounded-full filter blur-[150px] opacity-10 pointer-events-none glow-pulse"></div>
        <div class="absolute bottom-[-10%] right-[-10%] w-[50vw] h-[50vw] bg-[#ff3300] rounded-full filter blur-[150px] opacity-10 pointer-events-none glow-pulse" style="animation-delay: 2s;"></div>

        <!-- Navigation Header -->
        <header class="w-full sticky top-0 z-50 backdrop-blur-md border-b border-orange-500/10 bg-[#050508]/80 px-6 py-4 transition-all duration-300">
            <div class="max-w-7xl mx-auto flex items-center justify-between">
                <!-- Logo -->
                <a href="#" class="flex items-center gap-2 group">
                    <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-orange-600 to-orange-400 flex items-center justify-center font-rajdhani font-bold text-2xl text-black shadow-lg shadow-orange-500/30 group-hover:scale-105 transition-all">
                        W
                    </div>
                    <span class="font-rajdhani font-bold text-2xl tracking-wider text-white group-hover:text-orange-500 transition-colors">
                        WALDO<span class="text-orange-500 group-hover:text-white transition-colors">CASINO</span>
                    </span>
                </a>

                <!-- Navigation Links -->
                <nav class="hidden md:flex items-center gap-8 text-sm font-semibold tracking-wider uppercase font-rajdhani">
                    <a href="#lobby" class="text-gray-400 hover:text-orange-500 transition-colors">Games Lobby</a>
                    <a href="#slots" class="text-gray-400 hover:text-orange-500 transition-colors">Interactive Slots</a>
                    <a href="#jackpot" class="text-gray-400 hover:text-orange-500 transition-colors">Jackpots</a>
                    <a href="#vip" class="text-gray-400 hover:text-orange-500 transition-colors">VIP Program</a>
                </nav>

                <!-- Auth Navigation -->
                <div class="flex items-center gap-4">
                    @if (Route::has('login'))
                        @auth
                            <a href="{{ url('/dashboard') }}" class="px-5 py-2 rounded-full border border-orange-500/30 bg-orange-500/10 text-white font-rajdhani font-semibold tracking-wider hover:bg-orange-500 hover:text-black transition-all duration-300 neon-btn-glow">
                                DASHBOARD
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="text-gray-300 hover:text-white transition-colors text-sm font-rajdhani font-semibold tracking-wider">
                                LOGIN
                            </a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="px-5 py-2 rounded-full bg-orange-500 text-black font-rajdhani font-bold tracking-wider hover:bg-orange-400 transition-all duration-300 shadow-md shadow-orange-500/20 hover:scale-105">
                                    SIGN UP
                                </a>
                            @endif
                        @endauth
                    @endif
                </div>
            </div>
        </header>

        <!-- Hero Promo Banner Section -->
        <section class="relative min-h-[85vh] flex items-center justify-center px-6 pt-12 pb-20">
            <div class="max-w-7xl mx-auto w-full grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
                
                <!-- Hero Left: Welcome Bonus Offer -->
                <div class="lg:col-span-7 space-y-8 text-left">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full border border-orange-500/20 bg-orange-500/5 text-orange-400 text-xs font-semibold tracking-widest uppercase">
                        <span class="w-2 h-2 rounded-full bg-orange-500 animate-ping"></span>
                        OFFICIAL WELCOME OFFER
                    </div>
                    
                    <h1 class="text-5xl md:text-7xl font-rajdhani font-bold tracking-tight text-white leading-none">
                        CLAIM YOUR <br />
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-orange-500 via-orange-400 to-amber-500 neon-text-orange">WELCOME BONUS</span>
                    </h1>

                    <div class="space-y-2">
                        <div class="text-4xl md:text-6xl font-rajdhani font-extrabold text-white tracking-wide">
                            200% UP TO $2,000
                        </div>
                        <div class="text-lg md:text-xl text-orange-400 font-semibold tracking-wider uppercase">
                            + 150 CYBER FREE SPINS ON SIGN UP
                        </div>
                    </div>

                    <p class="text-gray-400 max-w-xl text-md leading-relaxed">
                        Join the most secure crypto-casino matrix. Instantly deposit with your favorite cryptocurrency or traditional credit cards and start playing 5,000+ slots, live blackjack, and custom game show rooms.
                    </p>

                    <div class="flex flex-wrap gap-4 pt-2">
                        <a href="{{ Route::has('register') ? route('register') : '#' }}" class="px-8 py-4 rounded-xl bg-orange-500 text-black font-rajdhani font-bold text-lg tracking-wider hover:bg-orange-400 transition-all duration-300 shadow-lg shadow-orange-500/30 neon-btn-glow hover:-translate-y-0.5">
                            CLAIM BONUS NOW
                        </a>
                        <a href="#lobby" class="px-8 py-4 rounded-xl border border-orange-500/30 bg-orange-500/5 text-orange-400 font-rajdhani font-bold text-lg tracking-wider hover:bg-orange-500 hover:text-black transition-all duration-300 hover:-translate-y-0.5">
                            BROWSE GAMES
                        </a>
                    </div>

                    <!-- Trust indicators -->
                    <div class="grid grid-cols-3 gap-6 pt-8 border-t border-orange-500/10">
                        <div>
                            <div class="text-3xl font-rajdhani font-bold text-white">100% SECURE</div>
                            <div class="text-xs text-gray-500 uppercase tracking-wider mt-1">Provably Fair Verification</div>
                        </div>
                        <div>
                            <div class="text-3xl font-rajdhani font-bold text-white">&lt; 5 MINS</div>
                            <div class="text-xs text-gray-500 uppercase tracking-wider mt-1">Ultra-Fast Withdrawals</div>
                        </div>
                        <div>
                            <div class="text-3xl font-rajdhani font-bold text-white">CURAÇAO</div>
                            <div class="text-xs text-gray-500 uppercase tracking-wider mt-1">Fully Licensed & Regulated</div>
                        </div>
                    </div>
                </div>

                <!-- Hero Right: Live Winners Feed Visualizer -->
                <div class="lg:col-span-5 flex justify-center relative">
                    <div class="w-full max-w-md rounded-2xl border border-orange-500/20 bg-gradient-to-b from-[#0c0d14] to-black p-6 shadow-2xl relative overflow-hidden">
                        <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-orange-500 to-transparent"></div>
                        <div class="flex justify-between items-center mb-6">
                            <span class="font-rajdhani font-bold text-lg text-white uppercase tracking-wider">🔥 LIVE WINNERS FEED</span>
                            <span class="w-2.5 h-2.5 rounded-full bg-green-500 animate-pulse"></span>
                        </div>
                        
                        <!-- List of Winners (Automatically shifts periodically with script) -->
                        <div class="space-y-4" id="winners-list">
                            <div class="flex justify-between items-center p-3 rounded-lg bg-orange-500/5 border border-orange-500/10 hover:border-orange-500/20 transition-all">
                                <div>
                                    <div class="text-sm font-semibold text-white">Player: Cryptox***</div>
                                    <div class="text-xs text-gray-500">Won on Cyber Reels</div>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm font-bold text-orange-400">+$2,480.00</div>
                                    <div class="text-xs text-gray-500">12s ago</div>
                                </div>
                            </div>

                            <div class="flex justify-between items-center p-3 rounded-lg bg-orange-500/5 border border-orange-500/10 hover:border-orange-500/20 transition-all">
                                <div>
                                    <div class="text-sm font-semibold text-white">Player: WaldoFa***</div>
                                    <div class="text-xs text-gray-500">Won on Mega Dice</div>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm font-bold text-orange-400">+$850.00</div>
                                    <div class="text-xs text-gray-500">45s ago</div>
                                </div>
                            </div>

                            <div class="flex justify-between items-center p-3 rounded-lg bg-orange-500/5 border border-orange-500/10 hover:border-orange-500/20 transition-all">
                                <div>
                                    <div class="text-sm font-semibold text-white">Player: SlotsKi***</div>
                                    <div class="text-xs text-gray-500">Won on Quantum Roulette</div>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm font-bold text-orange-400">+$4,920.00</div>
                                    <div class="text-xs text-gray-500">1m ago</div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 text-center">
                            <a href="{{ Route::has('register') ? route('register') : '#' }}" class="text-xs text-orange-400 font-semibold tracking-widest uppercase hover:underline">
                                JOIN THE WINNERS CIRCLE →
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </section>

        <!-- Live Global Jackpot Tracker -->
        <section id="jackpot" class="py-16 bg-[#08080f] border-y border-orange-500/10 scroll-reveal">
            <div class="max-w-7xl mx-auto px-6 text-center">
                <p class="text-orange-500 font-rajdhani font-bold tracking-widest uppercase">WALDO PROGRESSIVE JACKPOT</p>
                <h2 class="text-3xl md:text-5xl font-rajdhani font-bold text-white mt-2 mb-6">OUR HIGHEST ACCUMULATED PRIZE</h2>
                
                <div class="inline-flex items-center justify-center px-8 py-6 rounded-2xl bg-black border-2 border-orange-500/30 shadow-2xl relative overflow-hidden group">
                    <div class="absolute inset-0 w-full bg-gradient-to-r from-transparent via-orange-500/5 to-transparent -translate-x-full animate-[shimmer_3s_infinite]"></div>
                    
                    <span class="font-rajdhani font-bold text-4xl md:text-6xl text-white tracking-widest neon-text-orange" id="jackpot-counter">
                        9,482,102.40
                    </span>
                    <span class="text-orange-500 font-rajdhani font-bold text-xl md:text-3xl ml-3 tracking-wider">
                        USD
                    </span>
                </div>
                
                <p class="text-gray-500 text-sm mt-4">Calculated from total network volume across 100+ partner game rooms.</p>
            </div>
        </section>

        <!-- Interactive Slots Minigame Section -->
        <section id="slots" class="py-24 px-6 relative max-w-7xl mx-auto scroll-reveal">
            <div class="text-center max-w-2xl mx-auto mb-16 space-y-4">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full border border-orange-500/20 bg-orange-500/5 text-orange-400 text-xs font-semibold tracking-widest uppercase">
                    TRY BEFORE YOU DEPOSIT
                </div>
                <h2 class="text-4xl md:text-5xl font-rajdhani font-bold text-white">THE WALDO ORANGE SLOT MACHINE</h2>
                <p class="text-gray-400">
                    Test your luck on our signature interactive slot minigame. Spin and match symbols for free. Win up to 1,000 free bonus points redeemable at sign-up!
                </p>
            </div>

            <!-- Slots Machine Frame -->
            <div class="max-w-xl mx-auto rounded-3xl border-2 border-orange-500/40 bg-gradient-to-b from-[#131420] to-[#08080f] p-8 shadow-2xl relative overflow-hidden neon-border-orange">
                
                <!-- Glowing Headers -->
                <div class="flex justify-between items-center mb-8 border-b border-orange-500/10 pb-4">
                    <div>
                        <span class="text-xs text-gray-500 uppercase tracking-widest font-semibold block">Bonus Points Balance</span>
                        <span class="font-rajdhani font-bold text-2xl text-white flex items-center gap-1">
                            <span class="text-orange-500">⚡</span>
                            <span id="player-balance">1000</span> <span class="text-xs text-gray-400 font-normal">Points</span>
                        </span>
                    </div>
                    <div class="text-right">
                        <span class="text-xs text-gray-500 uppercase tracking-widest block font-semibold">Cost per Spin</span>
                        <span class="font-rajdhani font-bold text-xl text-orange-500">100 Points</span>
                    </div>
                </div>

                <!-- Slots Reels Row -->
                <div class="grid grid-cols-3 gap-4 mb-8">
                    <!-- Reel 1 -->
                    <div class="h-36 rounded-2xl bg-black/60 border border-orange-500/20 flex items-center justify-center text-6xl shadow-inner relative overflow-hidden group">
                        <div class="absolute inset-0 bg-gradient-to-b from-black/20 via-transparent to-black/20 pointer-events-none"></div>
                        <div id="reel-1" class="transition-all duration-300 font-semibold transform scale-110">🍊</div>
                    </div>
                    <!-- Reel 2 -->
                    <div class="h-36 rounded-2xl bg-black/60 border border-orange-500/20 flex items-center justify-center text-6xl shadow-inner relative overflow-hidden group">
                        <div class="absolute inset-0 bg-gradient-to-b from-black/20 via-transparent to-black/20 pointer-events-none"></div>
                        <div id="reel-2" class="transition-all duration-300 font-semibold transform scale-110">🍊</div>
                    </div>
                    <!-- Reel 3 -->
                    <div class="h-36 rounded-2xl bg-black/60 border border-orange-500/20 flex items-center justify-center text-6xl shadow-inner relative overflow-hidden group">
                        <div class="absolute inset-0 bg-gradient-to-b from-black/20 via-transparent to-black/20 pointer-events-none"></div>
                        <div id="reel-3" class="transition-all duration-300 font-semibold transform scale-110">🍊</div>
                    </div>
                </div>

                <!-- Audio feedback trigger & Sound Option -->
                <div class="flex items-center justify-between mb-6">
                    <label class="flex items-center gap-2 cursor-pointer text-xs text-gray-400 hover:text-white transition-colors">
                        <input type="checkbox" id="audio-toggle" checked class="accent-orange-500 w-4 h-4 rounded">
                        <span>Enable Retro Synth Sound Effects</span>
                    </label>
                    <span id="win-message" class="text-sm font-bold text-orange-400 animate-pulse hidden"></span>
                </div>

                <!-- Spin Trigger Action Button -->
                <button id="spin-btn" class="w-full py-4 rounded-2xl bg-gradient-to-r from-orange-600 to-orange-500 text-black font-rajdhani font-bold text-2xl tracking-widest hover:from-orange-500 hover:to-orange-400 transition-all duration-300 shadow-lg shadow-orange-600/30 hover:scale-[1.02] active:scale-95 flex items-center justify-center gap-2">
                    ⚡ SPIN MATRIX ⚡
                </button>

                <!-- Payout/Rules Panel -->
                <div class="mt-8 pt-6 border-t border-orange-500/10 text-xs text-gray-500 grid grid-cols-2 gap-4">
                    <div class="space-y-1">
                        <span class="font-bold text-gray-300 uppercase block tracking-wider">PAYOUTS MATRIX</span>
                        <div class="flex justify-between"><span>🎰 🎰 🎰 (JACKPOT)</span> <span class="text-orange-400 font-semibold">+1,000</span></div>
                        <div class="flex justify-between"><span>👑 👑 👑 (ROYAL)</span> <span class="text-orange-400 font-semibold">+500</span></div>
                        <div class="flex justify-between"><span>💎 💎 💎 (CRYSTAL)</span> <span class="text-orange-400 font-semibold">+300</span></div>
                    </div>
                    <div class="space-y-1 flex flex-col justify-end">
                        <div class="flex justify-between"><span>⚡ ⚡ ⚡ (ENERGY)</span> <span class="text-orange-400 font-semibold">+200</span></div>
                        <div class="flex justify-between"><span>🍊 🍊 🍊 (WALDO ORANGE)</span> <span class="text-orange-400 font-semibold">+150</span></div>
                        <div class="flex justify-between"><span>Any 2 Match</span> <span class="text-orange-400 font-semibold">+50</span></div>
                    </div>
                </div>

            </div>
        </section>

        <!-- Games Lobby Section -->
        <section id="lobby" class="py-24 bg-[#08080f] scroll-reveal">
            <div class="max-w-7xl mx-auto px-6">
                
                <!-- Lobby Header -->
                <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-6 mb-12">
                    <div class="space-y-4">
                        <p class="text-orange-500 font-rajdhani font-bold tracking-widest uppercase">CASINO GAMES LOBBY</p>
                        <h2 class="text-4xl md:text-5xl font-rajdhani font-bold text-white">CHOOSE YOUR ADVENTURE</h2>
                    </div>
                    <!-- Game Category Filters -->
                    <div class="flex flex-wrap gap-2 text-sm font-semibold tracking-wider font-rajdhani uppercase">
                        <button class="px-4 py-2 rounded-lg bg-orange-500 text-black">All Games</button>
                        <button class="px-4 py-2 rounded-lg border border-orange-500/20 bg-orange-500/5 text-orange-400 hover:bg-orange-500 hover:text-black transition-all">Slots</button>
                        <button class="px-4 py-2 rounded-lg border border-orange-500/20 bg-orange-500/5 text-orange-400 hover:bg-orange-500 hover:text-black transition-all">Live Casino</button>
                        <button class="px-4 py-2 rounded-lg border border-orange-500/20 bg-orange-500/5 text-orange-400 hover:bg-orange-500 hover:text-black transition-all">Table Games</button>
                    </div>
                </div>

                <!-- Games Grid -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                    <!-- Game Card 1 -->
                    <div class="group rounded-2xl border border-orange-500/10 bg-[#0d0e17] p-6 hover:border-orange-500/40 transition-all duration-500 hover:shadow-2xl hover:shadow-orange-500/5 relative overflow-hidden">
                        <div class="h-48 rounded-xl bg-gradient-to-br from-orange-950/20 to-black border border-orange-500/10 flex items-center justify-center text-5xl mb-6 relative group-hover:border-orange-500/30 transition-all">
                            🎰
                            <!-- Play overlay -->
                            <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                <a href="{{ Route::has('register') ? route('register') : '#' }}" class="px-5 py-2 rounded-full bg-orange-500 text-black font-rajdhani font-bold text-sm tracking-wider shadow-lg">PLAY NOW</a>
                            </div>
                        </div>
                        <div class="text-orange-500 font-rajdhani font-bold text-xs mb-1 uppercase tracking-wider">Cyber Slots</div>
                        <h3 class="text-2xl font-rajdhani font-bold text-white mb-2">HYPERION REELS</h3>
                        <p class="text-gray-400 text-sm leading-relaxed mb-4">
                            Featuring wild orange multipliers, high volatility scatter triggers, and over 1,024 different winning paylines.
                        </p>
                    </div>

                    <!-- Game Card 2 -->
                    <div class="group rounded-2xl border border-orange-500/10 bg-[#0d0e17] p-6 hover:border-orange-500/40 transition-all duration-500 hover:shadow-2xl hover:shadow-orange-500/5 relative overflow-hidden">
                        <div class="h-48 rounded-xl bg-gradient-to-br from-orange-950/20 to-black border border-orange-500/10 flex items-center justify-center text-5xl mb-6 relative group-hover:border-orange-500/30 transition-all">
                            🎡
                            <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                <a href="{{ Route::has('register') ? route('register') : '#' }}" class="px-5 py-2 rounded-full bg-orange-500 text-black font-rajdhani font-bold text-sm tracking-wider shadow-lg">PLAY NOW</a>
                            </div>
                        </div>
                        <div class="text-orange-500 font-rajdhani font-bold text-xs mb-1 uppercase tracking-wider">Live Dealer</div>
                        <h3 class="text-2xl font-rajdhani font-bold text-white mb-2">NEON ROULETTE</h3>
                        <p class="text-gray-400 text-sm leading-relaxed mb-4">
                            Place your digital coins on the spinning neon wheel. Experience instant game show multipliers and interactive community chats.
                        </p>
                    </div>

                    <!-- Game Card 3 -->
                    <div class="group rounded-2xl border border-orange-500/10 bg-[#0d0e17] p-6 hover:border-orange-500/40 transition-all duration-500 hover:shadow-2xl hover:shadow-orange-500/5 relative overflow-hidden">
                        <div class="h-48 rounded-xl bg-gradient-to-br from-orange-950/20 to-black border border-orange-500/10 flex items-center justify-center text-5xl mb-6 relative group-hover:border-orange-500/30 transition-all">
                            🃏
                            <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                <a href="{{ Route::has('register') ? route('register') : '#' }}" class="px-5 py-2 rounded-full bg-orange-500 text-black font-rajdhani font-bold text-sm tracking-wider shadow-lg">PLAY NOW</a>
                            </div>
                        </div>
                        <div class="text-orange-500 font-rajdhani font-bold text-xs mb-1 uppercase tracking-wider">Card Classics</div>
                        <h3 class="text-2xl font-rajdhani font-bold text-white mb-2">QUANTUM BLACKJACK</h3>
                        <p class="text-gray-400 text-sm leading-relaxed mb-4">
                            Go head to head with professional live human dealers. Features high-stakes tables and premium side bets.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- VIP Perks Program -->
        <section id="vip" class="py-24 px-6 max-w-7xl mx-auto scroll-reveal">
            <div class="rounded-3xl border border-orange-500/20 bg-gradient-to-r from-[#0d0e17] via-black to-[#0d0e17] p-8 md:p-12 lg:p-16 flex flex-col lg:flex-row items-center justify-between gap-12 relative overflow-hidden">
                <div class="absolute top-[-50%] left-[-20%] w-[60vw] h-[60vw] bg-orange-500/5 rounded-full filter blur-[120px] pointer-events-none"></div>
                
                <div class="space-y-6 max-w-2xl">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full border border-orange-500/20 bg-orange-500/5 text-orange-400 text-xs font-semibold tracking-widest uppercase">
                        WALDO VIP PROGRAM
                    </div>
                    <h2 class="text-4xl md:text-5xl font-rajdhani font-bold text-white leading-tight">
                        UNLOCK DEDICATED <br /> PLAYER REWARDS
                    </h2>
                    <p class="text-gray-400 leading-relaxed">
                        Earn VIP status points with every wager you make. Claim weekly cashbacks, customized birthday bonuses, higher deposit/withdrawal limits, and personal managers.
                    </p>
                    <ul class="space-y-3 font-semibold font-rajdhani text-lg tracking-wide text-gray-200">
                        <li class="flex items-center gap-3"><span class="text-orange-500">⚡</span> Weekly VIP Rakebacks (Up to 15%)</li>
                        <li class="flex items-center gap-3"><span class="text-orange-500">⚡</span> Priority Customer Support Response &lt; 1 Minute</li>
                        <li class="flex items-center gap-3"><span class="text-orange-500">⚡</span> Personal VIP Account Managers</li>
                    </ul>
                </div>

                <div class="shrink-0 w-full lg:w-auto text-center lg:text-right">
                    <a href="{{ Route::has('register') ? route('register') : '#' }}" class="inline-block px-10 py-5 rounded-2xl bg-orange-500 text-black font-rajdhani font-bold text-xl tracking-wider hover:bg-orange-400 transition-all duration-300 shadow-xl shadow-orange-500/20 hover:scale-105">
                        JOIN THE VIP CLUB
                    </a>
                    <p class="text-gray-500 text-xs mt-3">Free signup. Instant points matching starts on first play.</p>
                </div>
            </div>
        </section>

        <!-- Footer / Regulatory / Trust Section -->
        <footer class="border-t border-orange-500/10 bg-[#020204] py-16 px-6">
            <div class="max-w-7xl mx-auto space-y-12">
                
                <!-- Payment Providers Icons Row -->
                <div class="flex flex-wrap items-center justify-center gap-8 border-b border-orange-500/10 pb-10">
                    <span class="text-xs text-gray-600 uppercase tracking-widest font-semibold">Accepted Crypto & Fiat:</span>
                    <span class="text-sm font-bold text-gray-400 tracking-wider">₿ BITCOIN</span>
                    <span class="text-sm font-bold text-gray-400 tracking-wider">♦ ETHEREUM</span>
                    <span class="text-sm font-bold text-gray-400 tracking-wider">⚡ LITECOIN</span>
                    <span class="text-sm font-bold text-gray-400 tracking-wider">₮ USDT</span>
                    <span class="text-sm font-bold text-gray-400 tracking-wider">VISA</span>
                    <span class="text-sm font-bold text-gray-400 tracking-wider">MASTERCARD</span>
                </div>

                <!-- Main footer block -->
                <div class="grid grid-cols-1 md:grid-cols-12 gap-8 items-start">
                    
                    <!-- Left: Logo & License Text -->
                    <div class="md:col-span-8 space-y-6">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-orange-500 flex items-center justify-center font-rajdhani font-bold text-lg text-black">
                                W
                            </div>
                            <span class="font-rajdhani font-bold text-xl tracking-wider text-white">
                                WALDO<span class="text-orange-500">CASINO</span>
                            </span>
                        </div>
                        
                        <p class="text-xs text-gray-600 leading-relaxed max-w-3xl">
                            Waldo Casino is owned and operated by Waldo Entertainment N.V., a company registered and established under the laws of Curaçao, with registration number 164805. Waldo Entertainment N.V. is licensed and regulated by the Gaming Control Board of Curaçao (GCB) under license number 8048/JAZ. Payments are processed by Waldo Payments Ltd. registered under company number HE 40291.
                        </p>
                        
                        <p class="text-xs text-gray-600">
                            © 2026 Waldo Casino. All rights reserved. Provably fair gaming systems.
                        </p>
                    </div>

                    <!-- Right: Responsible Gaming & Age limits -->
                    <div class="md:col-span-4 space-y-6 md:text-right">
                        <div class="inline-flex items-center justify-center md:justify-end gap-3">
                            <!-- 18+ badge -->
                            <span class="w-10 h-10 rounded-full border border-gray-700 flex items-center justify-center text-xs font-bold text-gray-500">18+</span>
                            <!-- Provably Fair -->
                            <span class="px-3 py-1 rounded border border-gray-700 text-[10px] font-bold text-gray-500 tracking-widest uppercase">PROVABLY FAIR</span>
                        </div>
                        
                        <div class="text-xs text-gray-500 space-y-1">
                            <p class="font-bold text-gray-400">RESPONSIBLE GAMING</p>
                            <p>Gambling can be addictive. Please play responsibly.</p>
                            <p>For help and support, contact BeGambleAware.org.</p>
                        </div>
                    </div>

                </div>

            </div>
        </footer>

        <!-- Javascript Interactive Logic -->
        <script>
            // Live Jackpot Counter Logic
            const jackpotElement = document.getElementById('jackpot-counter');
            let jackpotAmount = 9482102.40;
            
            setInterval(() => {
                const increment = Math.random() * 0.45 + 0.05;
                jackpotAmount += increment;
                jackpotElement.textContent = jackpotAmount.toLocaleString(undefined, {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                });
            }, 350);

            // Live Winner Feed Simulator
            const winnersList = document.getElementById('winners-list');
            const winnerNames = ['GoldFlas***', 'SpinMas***', 'CryptoK***', 'WaldoFa***', 'HighRoll***', 'MegaWin***', 'AceSpad***'];
            const gameNames = ['Cyber Reels', 'Neon Roulette', 'Quantum Blackjack', 'Mega Dice', 'Hyperion Reels'];
            
            setInterval(() => {
                if (!winnersList) return;
                const name = winnerNames[Math.floor(Math.random() * winnerNames.length)];
                const game = gameNames[Math.floor(Math.random() * gameNames.length)];
                const winAmount = (Math.random() * 4500 + 50).toFixed(2);
                
                // Create element
                const newWinner = document.createElement('div');
                newWinner.className = 'flex justify-between items-center p-3 rounded-lg bg-orange-500/5 border border-orange-500/10 hover:border-orange-500/20 transition-all transform -translate-y-2 opacity-0';
                newWinner.innerHTML = `
                    <div>
                        <div class="text-sm font-semibold text-white">Player: ${name}</div>
                        <div class="text-xs text-gray-500">Won on ${game}</div>
                    </div>
                    <div class="text-right">
                        <div class="text-sm font-bold text-orange-400">+$${parseFloat(winAmount).toLocaleString()}</div>
                        <div class="text-xs text-gray-500">Just now</div>
                    </div>
                `;
                
                winnersList.prepend(newWinner);
                // Remove the last winner to keep list size constant
                if (winnersList.children.length > 3) {
                    winnersList.removeChild(winnersList.lastChild);
                }

                // Trigger transition
                setTimeout(() => {
                    newWinner.classList.remove('-translate-y-2', 'opacity-0');
                }, 50);
            }, 5000);

            // Web Audio API Sound Synthesizer for self-contained retro audio effects
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
                        
                        gain.gain.setValueAtTime(0.1, audioCtx.currentTime);
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
                            
                            gain.gain.setValueAtTime(0.1, audioCtx.currentTime + index * 0.1);
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
                        osc.frequency.setValueAtTime(200, audioCtx.currentTime);
                        osc.frequency.linearRampToValueAtTime(80, audioCtx.currentTime + 0.4);
                        
                        gain.gain.setValueAtTime(0.1, audioCtx.currentTime);
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

            // Cyber-Slots Logic
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

                // Deduct balance
                balance -= cost;
                balanceEl.textContent = balance;
                isSpinning = true;
                spinBtn.disabled = true;
                spinBtn.textContent = "ROLLING MATRIX...";
                winMessageEl.classList.add('hidden');

                playSynthSound('spin');

                // Reel spinning interval animation
                let spinCount = 0;
                const spinInterval = setInterval(() => {
                    reels.forEach(reel => {
                        const randomIndex = Math.floor(Math.random() * symbols.length);
                        reel.textContent = symbols[randomIndex];
                    });
                    spinCount++;
                    
                    if (spinCount > 10) {
                        clearInterval(spinInterval);
                        finalizeSpin();
                    }
                }, 100);
            });

            function finalizeSpin() {
                // Determine final symbols
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
                    wonMessage = "✨ DOUBLE SHIFT! +50 Points!";
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

            // Scroll Reveal Animation Logic
            const revealElements = document.querySelectorAll('.scroll-reveal');
            
            const revealOnScroll = () => {
                const triggerBottom = window.innerHeight * 0.85;
                revealElements.forEach(el => {
                    const elTop = el.getBoundingClientRect().top;
                    if (elTop < triggerBottom) {
                        el.classList.add('active');
                    }
                });
            };

            window.addEventListener('scroll', revealOnScroll);
            revealOnScroll();
        </script>
    </body>
</html>
