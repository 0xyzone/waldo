{{-- 2708px x 1492px Sky Blue Birthday Wishcard Template - Dynamic Expansive Container Scaling --}}
@php
    $count = $birthdays->count();
    
    // Dynamic Expansive Container & Typography Scaling Logic based on Celebrants Count
    if ($count <= 8) {
        $containerWidth = 'w-[74%]';
        $leftWidth = 'w-[24%]';
        $cols = 'grid-cols-2 gap-x-10 gap-y-4';
        $nameSize = 'font-size: 32px; font-weight: 900;';
        $codeSize = 'font-size: 26px; font-weight: 800;';
        $dateSize = 'px-4 py-2 text-sm font-black';
        $cakeSize = 'w-[480px] h-[560px]';
    } elseif ($count <= 24) {
        $containerWidth = 'w-[78%]';
        $leftWidth = 'w-[20%]';
        $cols = 'grid-cols-3 gap-x-8 gap-y-3';
        $nameSize = 'font-size: 26px; font-weight: 900;';
        $codeSize = 'font-size: 22px; font-weight: 800;';
        $dateSize = 'px-3.5 py-1.5 text-xs font-black';
        $cakeSize = 'w-[420px] h-[500px]';
    } elseif ($count <= 44) {
        $containerWidth = 'w-[82%]';
        $leftWidth = 'w-[16%]';
        $cols = 'grid-cols-3 gap-x-8 gap-y-2.5';
        $nameSize = 'font-size: 24px; font-weight: 900;';
        $codeSize = 'font-size: 20px; font-weight: 800;';
        $dateSize = 'px-3 py-1 text-xs font-black';
        $cakeSize = 'w-[360px] h-[430px]';
    } elseif ($count <= 64) {
        // 47 or 56 celebrants -> EXPAND CONTAINER TO 84% WIDTH FOR MASSIVE HIGH-VISIBILITY TEXT
        $containerWidth = 'w-[84%]';
        $leftWidth = 'w-[14%]';
        $cols = 'grid-cols-4 gap-x-6 gap-y-2';
        $nameSize = 'font-size: 23px; font-weight: 900;';
        $codeSize = 'font-size: 19px; font-weight: 800;';
        $dateSize = 'px-2.5 py-0.5 text-[11px] font-black';
        $cakeSize = 'w-[320px] h-[380px]';
    } else {
        // > 64 employees -> 86% EXPANDED CONTAINER WIDTH
        $containerWidth = 'w-[86%]';
        $leftWidth = 'w-[12%]';
        $cols = 'grid-cols-5 gap-x-4 gap-y-1.5';
        $nameSize = 'font-size: 20px; font-weight: 900;';
        $codeSize = 'font-size: 17px; font-weight: 800;';
        $dateSize = 'px-2 py-0.5 text-[10px] font-black';
        $cakeSize = 'w-[280px] h-[340px]';
    }
@endphp

<div 
    style="width: 2708px; height: 1492px; box-sizing: border-box; background: #0284c7; background-image: radial-gradient(circle at 30% 30%, #38bdf8 0%, #0284c7 60%, #0369a1 100%); font-family: 'Plus Jakarta Sans', sans-serif; color: #0f172a; border: 16px solid #0369a1;"
    class="relative overflow-hidden flex flex-col justify-between p-10 select-none"
>
    <!-- Outer Fine Frame Inset -->
    <div class="absolute inset-4 pointer-events-none rounded-2xl" style="border: 2px solid rgba(255, 255, 255, 0.4);"></div>

    <!-- BACKGROUND CONFETTI SPRINKLES (NO EMOJIS, VECTOR SVG ONLY) -->
    <!-- Top Left Confetti Cluster -->
    <svg class="absolute top-8 left-10 w-44 h-44 pointer-events-none opacity-80" viewBox="0 0 100 100" fill="none">
        <polygon points="15,20 22,10 28,22 20,30" fill="#fbbf24"/>
        <circle cx="45" cy="18" r="4" fill="#34d399"/>
        <polygon points="55,32 70,25 60,42" fill="#f43f5e"/>
        <circle cx="22" cy="48" r="5" fill="#ffffff"/>
        <rect x="75" y="12" width="8" height="18" transform="rotate(25 79 21)" fill="#fbbf24" rx="2"/>
    </svg>
    <!-- Top Right Confetti Cluster -->
    <svg class="absolute top-8 right-10 w-44 h-44 pointer-events-none opacity-80 transform rotate-90" viewBox="0 0 100 100" fill="none">
        <polygon points="15,20 22,10 28,22 20,30" fill="#fbbf24"/>
        <circle cx="45" cy="18" r="4" fill="#34d399"/>
        <polygon points="55,32 70,25 60,42" fill="#f43f5e"/>
        <circle cx="22" cy="48" r="5" fill="#ffffff"/>
        <rect x="75" y="12" width="8" height="18" transform="rotate(25 79 21)" fill="#fbbf24" rx="2"/>
    </svg>
    <!-- Bottom Left Confetti Cluster -->
    <svg class="absolute bottom-8 left-10 w-40 h-40 pointer-events-none opacity-75 transform -rotate-45" viewBox="0 0 100 100" fill="none">
        <polygon points="20,15 28,28 15,35" fill="#34d399"/>
        <circle cx="60" cy="20" r="5" fill="#fbbf24"/>
        <rect x="30" y="50" width="10" height="20" transform="rotate(40 35 60)" fill="#f43f5e" rx="2"/>
        <circle cx="75" cy="65" r="4" fill="#ffffff"/>
    </svg>

    <!-- MAIN HERO CONTENT CONTAINER -->
    <div class="relative z-10 w-full h-full flex gap-8 items-center justify-between p-4">
        
        <!-- LEFT SIDE: CUTE CHUBBY TASTY-LOOKING CAKE (DYNAMICALLY SCALED, NO RECTANGLE BOX BEHIND IT) -->
        <div class="{{ $leftWidth }} h-full flex flex-col items-center justify-center relative pointer-events-none select-none transition-all">
            
            <!-- Soft Ambient Backdrop Glow behind Cake -->
            <div class="absolute w-[450px] h-[450px] rounded-full bg-white/25 blur-3xl pointer-events-none"></div>

            <!-- Extra Large Tasty & Cute Chubby Birthday Cake SVG -->
            <svg class="{{ $cakeSize }} relative z-10 filter drop-shadow-[0_30px_55px_rgba(0,0,0,0.38)] transition-all" viewBox="0 0 120 140" fill="none">
                <!-- Lit Flame Glow Effects -->
                <circle cx="60" cy="14" r="11" fill="#fef08a" opacity="0.85" class="animate-pulse"/>
                <circle cx="42" cy="18" r="9" fill="#fef08a" opacity="0.8" class="animate-pulse"/>
                <circle cx="78" cy="18" r="9" fill="#fef08a" opacity="0.8" class="animate-pulse"/>

                <!-- Micro Flame Tips -->
                <path d="M60 6 Q65 0 65 6 Q65 13 60 6 Z" fill="#fef08a"/>
                <circle cx="61.5" cy="5" r="2.5" fill="#f59e0b"/>
                <path d="M42 10 Q46 5 46 10 Q46 16 42 10 Z" fill="#fef08a"/>
                <circle cx="43.5" cy="9" r="2" fill="#f59e0b"/>
                <path d="M78 10 Q82 5 82 10 Q82 16 78 10 Z" fill="#fef08a"/>
                <circle cx="79.5" cy="9" r="2" fill="#f59e0b"/>

                <!-- Cute Chubby Striped Candles -->
                <!-- Center Main Candle -->
                <rect x="57" y="14" width="6" height="26" fill="#ffffff" rx="2"/>
                <rect x="57" y="18" width="6" height="4" fill="#38bdf8"/>
                <rect x="57" y="26" width="6" height="4" fill="#38bdf8"/>
                <rect x="57" y="34" width="6" height="4" fill="#38bdf8"/>

                <!-- Left Candle -->
                <rect x="39" y="18" width="6" height="22" fill="#ffffff" rx="2"/>
                <rect x="39" y="22" width="6" height="4" fill="#f43f5e"/>
                <rect x="39" y="30" width="6" height="4" fill="#f43f5e"/>

                <!-- Right Candle -->
                <rect x="75" y="18" width="6" height="22" fill="#ffffff" rx="2"/>
                <rect x="75" y="22" width="6" height="4" fill="#10b981"/>
                <rect x="75" y="30" width="6" height="4" fill="#10b981"/>

                <!-- TOP CHUBBY CAKE TIER -->
                <rect x="30" y="36" width="60" height="34" rx="14" fill="url(#chubby-top-tier)"/>
                <!-- Creamy Frosting Drips Top Tier -->
                <path d="M30 46 C34 52, 40 42, 46 50 C52 56, 58 44, 64 52 C70 56, 76 44, 82 50 C86 54, 90 46, 90 38 L30 38 Z" fill="url(#cream-frosting)"/>

                <!-- GOLD LEAF MIDDLE DIVIDER -->
                <rect x="22" y="68" width="76" height="6" fill="#fbbf24" rx="3"/>

                <!-- BOTTOM CHUBBY CAKE TIER -->
                <rect x="16" y="72" width="88" height="46" rx="18" fill="url(#chubby-bottom-tier)"/>
                <!-- Creamy Frosting Drips Bottom Tier -->
                <path d="M16 86 C22 94, 30 80, 38 92 C46 100, 54 82, 62 94 C70 102, 78 84, 86 94 C94 98, 104 88, 104 74 L16 74 Z" fill="url(#cream-frosting)"/>

                <!-- CUTE COLORFUL SPRINKLES ON FROSTING -->
                <circle cx="34" cy="44" r="2.5" fill="#f43f5e"/>
                <circle cx="48" cy="42" r="2.5" fill="#fbbf24"/>
                <circle cx="62" cy="46" r="2.5" fill="#0284c7"/>
                <circle cx="76" cy="42" r="2.5" fill="#10b981"/>

                <rect x="26" y="82" width="5" height="2.5" fill="#fbbf24" transform="rotate(30 26 82)" rx="1"/>
                <rect x="42" y="86" width="5" height="2.5" fill="#f43f5e" transform="rotate(-20 42 86)" rx="1"/>
                <rect x="58" y="84" width="5" height="2.5" fill="#0284c7" transform="rotate(40 58 84)" rx="1"/>
                <rect x="74" y="88" width="5" height="2.5" fill="#10b981" transform="rotate(-15 74 88)" rx="1"/>
                <rect x="90" y="82" width="5" height="2.5" fill="#fbbf24" transform="rotate(25 90 82)" rx="1"/>

                <!-- CHUBBY GOLDEN STAND BASE -->
                <ellipse cx="60" cy="116" rx="52" ry="9" fill="#d97706"/>
                <rect x="46" y="115" width="28" height="12" fill="#b45309"/>
                <ellipse cx="60" cy="127" rx="40" ry="7" fill="#f59e0b"/>

                <defs>
                    <linearGradient id="chubby-top-tier" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" stop-color="#38bdf8"/>
                        <stop offset="60%" stop-color="#0284c7"/>
                        <stop offset="100%" stop-color="#0369a1"/>
                    </linearGradient>
                    <linearGradient id="chubby-bottom-tier" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" stop-color="#0284c7"/>
                        <stop offset="70%" stop-color="#0369a1"/>
                        <stop offset="100%" stop-color="#075985"/>
                    </linearGradient>
                    <linearGradient id="cream-frosting" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" stop-color="#ffffff"/>
                        <stop offset="70%" stop-color="#fff1f2"/>
                        <stop offset="100%" stop-color="#fecdd3"/>
                    </linearGradient>
                </defs>
            </svg>
        </div>

        <!-- RIGHT PANEL: HIGH-CONTRAST HEADER & DYNAMIC EXPANSIVE EMPLOYEE NAME GRID -->
        <div 
            style="background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%); border: 2.5px solid #e2e8f0; box-shadow: 0 20px 50px rgba(0,0,0,0.25);"
            class="{{ $containerWidth }} h-full rounded-3xl p-8 flex flex-col justify-between relative overflow-hidden shrink-0 transition-all"
        >
            <!-- TOP HEADER SECTION INSIDE CARD -->
            <div class="flex items-center justify-between border-b border-slate-200 pb-5">
                <!-- Title & Subtitle -->
                <div>
                    <p 
                        style="font-family: 'Playfair Display', serif; font-style: italic; font-size: 26px; color: #0284c7;"
                        class="font-semibold tracking-wide"
                    >
                        Wishing you all the happiness on your special day!
                    </p>
                    <h1 
                        style="font-family: 'Cinzel', serif; font-size: 58px; letter-spacing: 2px; color: #0f172a;"
                        class="font-black leading-none uppercase mt-1"
                    >
                        Happy Birthmonth
                    </h1>
                </div>

                <!-- Slanted Month Ribbon Badge -->
                <div 
                    style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); box-shadow: 0 8px 20px rgba(16,185,129,0.3);"
                    class="px-8 py-3 rounded-xl flex items-center justify-center transform rotate-2 border border-emerald-300/40"
                >
                    <span 
                        style="font-family: 'Plus Jakarta Sans', sans-serif; font-size: 28px; letter-spacing: 3px; color: #ffffff;"
                        class="font-black uppercase drop-shadow-sm"
                    >
                        {{ strtoupper($monthName) }} {{ $year }}
                    </span>
                </div>
            </div>

            <!-- MAIN EMPLOYEE NAMES GRID CONTAINER (100% READABLE & CLEAR) -->
            <div class="my-4 flex-1 flex items-center justify-center overflow-hidden">
                @if ($birthdays->isEmpty())
                    <div class="text-center py-16 px-16 rounded-2xl bg-slate-50 border border-slate-200">
                        <h3 class="text-3xl font-bold text-slate-800" style="font-family: 'Cinzel', serif;">
                            No Birthdays in {{ $monthName }} {{ $year }}
                        </h3>
                        <p class="text-xl mt-2 text-slate-600">
                            There are no active employee birthdays scheduled for this month.
                        </p>
                    </div>
                @else
                    <div class="w-full grid {{ $cols }} content-center justify-between items-center h-full">
                        @foreach ($birthdays as $employee)
                            @php
                                $empCode = $employee->employee_code ?? $employee->code ?? ('EMP' . str_pad($employee->id ?? $loop->iteration, 3, '0', STR_PAD_LEFT));
                            @endphp
                            <div class="flex items-center justify-between gap-2 p-2 bg-slate-50/80 hover:bg-sky-50 border border-slate-200/80 rounded-xl transition-all">
                                <div class="flex items-center gap-2.5 min-w-0 flex-1">
                                    <!-- Employee Code Badge -->
                                    <span 
                                        style="{{ $codeSize }} color: #0284c7; background: #e0f2fe;"
                                        class="shrink-0 px-2 py-0.5 rounded-md font-black tracking-tight border border-sky-200"
                                    >
                                        {{ $empCode }}
                                    </span>
                                    
                                    <!-- Employee Full Name (PURE DARK NAVY EXTRA BOLD) -->
                                    <span 
                                        style="{{ $nameSize }} color: #0f172a;"
                                        class="truncate tracking-tight"
                                    >
                                        {{ $employee->name }}
                                    </span>
                                </div>

                                <!-- Birthday Date Badge Tag -->
                                <div 
                                    style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); color: #ffffff;"
                                    class="shrink-0 {{ $dateSize }} rounded-md uppercase tracking-wider font-black shadow-sm"
                                >
                                    {{ $employee->dob_ad ? $employee->dob_ad->format('M d') : '' }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <!-- CARD FOOTER STATS -->
            <div class="pt-3 border-t border-slate-200 flex items-center justify-between text-sm font-bold text-slate-500">
                <span class="uppercase tracking-widest text-sky-800" style="font-family: 'Cinzel', serif;">
                    Official Birthday Celebration List
                </span>
                <span class="font-extrabold text-slate-800">
                    Total {{ $birthdays->count() }} Celebrants
                </span>
            </div>

        </div>
    </div>
</div>
