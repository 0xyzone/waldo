{{-- 2708px x 1492px Birthday Wishing Card Template - Clean SVG/Canvas Compatible Borders --}}
@php
    $count = $birthdays->count();
    
    // Dynamic Layout Configuration to maximize NAME visibility and text contrast
    if ($count <= 8) {
        $gridCols = 'grid-cols-2 gap-8';
        $cardPadding = 'p-6';
        $badgeSize = 'w-14 h-14 text-2xl';
        $nameStyle = 'font-size: 32px; font-weight: 800;';
        $subStyle = 'font-size: 15px;';
        $datePill = 'px-5 py-2.5 text-base font-black';
        $headerPadding = 'pt-4 pb-2 space-y-2';
        $titleStyle = 'font-size: 68px; letter-spacing: 12px;';
    } elseif ($count <= 18) {
        $gridCols = 'grid-cols-3 gap-6';
        $cardPadding = 'p-5';
        $badgeSize = 'w-12 h-12 text-xl';
        $nameStyle = 'font-size: 26px; font-weight: 800;';
        $subStyle = 'font-size: 14px;';
        $datePill = 'px-4 py-2 text-sm font-black';
        $headerPadding = 'pt-3 pb-1 space-y-1.5';
        $titleStyle = 'font-size: 58px; letter-spacing: 10px;';
    } elseif ($count <= 32) {
        $gridCols = 'grid-cols-4 gap-4';
        $cardPadding = 'p-4';
        $badgeSize = 'w-10 h-10 text-base';
        $nameStyle = 'font-size: 22px; font-weight: 800;';
        $subStyle = 'font-size: 13px;';
        $datePill = 'px-3.5 py-1.5 text-xs font-black';
        $headerPadding = 'pt-2 pb-1 space-y-1';
        $titleStyle = 'font-size: 48px; letter-spacing: 8px;';
    } elseif ($count <= 50) {
        $gridCols = 'grid-cols-5 gap-3.5';
        $cardPadding = 'p-3';
        $badgeSize = 'w-9 h-9 text-sm';
        $nameStyle = 'font-size: 19px; font-weight: 800;';
        $subStyle = 'font-size: 12px;';
        $datePill = 'px-3 py-1 text-xs font-black';
        $headerPadding = 'pt-1.5 pb-1 space-y-1';
        $titleStyle = 'font-size: 40px; letter-spacing: 6px;';
    } else {
        // > 50 employees
        $gridCols = 'grid-cols-6 gap-3';
        $cardPadding = 'p-2.5';
        $badgeSize = 'w-8 h-8 text-xs';
        $nameStyle = 'font-size: 17px; font-weight: 800;';
        $subStyle = 'font-size: 11px;';
        $datePill = 'px-2.5 py-0.5 text-[11px] font-black';
        $headerPadding = 'pt-1 pb-0.5 space-y-0.5';
        $titleStyle = 'font-size: 34px; letter-spacing: 4px;';
    }
@endphp

<div 
    style="width: 2708px; height: 1492px; box-sizing: border-box; background: #0b0f19; background-image: radial-gradient(circle at 50% 25%, #1e1b4b 0%, #0f172a 55%, #060a12 100%); font-family: 'Plus Jakarta Sans', sans-serif; color: #ffffff; border: 18px solid #f59e0b;"
    class="relative overflow-hidden flex flex-col justify-between p-10 select-none"
>
    <!-- Inner Inset Gold Line -->
    <div class="absolute inset-4 pointer-events-none rounded-xl" style="border: 3px solid #fbbf24;"></div>

    <!-- Glowing Background Aura -->
    <div class="absolute top-10 left-1/2 -translate-x-1/2 w-[1400px] h-[450px] rounded-full pointer-events-none" style="background: radial-gradient(circle, rgba(251, 191, 36, 0.2) 0%, rgba(245, 158, 11, 0.1) 50%, transparent 80%);"></div>

    <!-- Top Header Banner (Sleek & Space-Efficient) -->
    <div class="relative z-10 text-center flex flex-col items-center shrink-0 {{ $headerPadding }}">
        
        <!-- Header Badge & Title -->
        <div class="flex items-center justify-center gap-4">
            <div class="flex items-center justify-center w-12 h-12 rounded-xl shadow-lg" style="background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%); color: #451a03;">
                <svg class="w-7 h-7" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2l2.4 4.8 5.3.8-3.8 3.7.9 5.3L12 14.1l-4.8 2.5.9-5.3L4.3 7.6l5.3-.8L12 2z"/>
                    <path d="M5 21h14v-2H5v2z"/>
                </svg>
            </div>
            <h1 
                style="font-family: 'Cinzel', serif; {{ $titleStyle }} text-shadow: 0 4px 20px rgba(251, 191, 36, 0.5); background: linear-gradient(135deg, #ffffff 0%, #fef08a 40%, #f59e0b 80%, #ffffff 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;"
                class="font-black uppercase tracking-widest leading-none"
            >
                Happy Birthday
            </h1>
            <div class="flex items-center justify-center w-12 h-12 rounded-xl shadow-lg" style="background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%); color: #451a03;">
                <svg class="w-7 h-7" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2l2.4 4.8 5.3.8-3.8 3.7.9 5.3L12 14.1l-4.8 2.5.9-5.3L4.3 7.6l5.3-.8L12 2z"/>
                    <path d="M5 21h14v-2H5v2z"/>
                </svg>
            </div>
        </div>

        <!-- Subtitle Bar -->
        <div class="flex items-center gap-4 pt-1">
            <div class="h-0.5 w-28" style="background: linear-gradient(90deg, transparent, #fbbf24);"></div>
            <p 
                style="font-family: 'Cinzel', serif; font-size: 22px; letter-spacing: 4px; color: #fcd34d; text-shadow: 0 2px 4px rgba(0,0,0,0.5);"
                class="font-bold uppercase tracking-widest"
            >
                Celebrating Our Team Members • {{ strtoupper($monthName) }} {{ $year }}
            </p>
            <div class="h-0.5 w-28" style="background: linear-gradient(270deg, transparent, #fbbf24);"></div>
        </div>
    </div>

    <!-- Main Content Area: High-Contrast High-Visibility Cards -->
    <div class="relative z-10 my-3 flex-1 flex items-center justify-center px-6 overflow-hidden">
        @if ($birthdays->isEmpty())
            <div class="text-center py-16 px-16 rounded-3xl shadow-2xl" style="background-color: rgba(15, 23, 42, 0.9); border: 2px solid rgba(251, 191, 36, 0.5);">
                <h3 class="text-3xl font-bold" style="font-family: 'Cinzel', serif; color: #fcd34d;">
                    No Birthdays in {{ $monthName }} {{ $year }}
                </h3>
                <p class="text-xl mt-2" style="color: #cbd5e1;">
                    There are no active employee birthdays scheduled for this month.
                </p>
            </div>
        @else
            <div class="w-full grid {{ $gridCols }} content-center justify-center align-middle">
                @foreach ($birthdays as $employee)
                    <div 
                        style="background: linear-gradient(135deg, #ffffff 0%, #fefce8 100%); border: 2.5px solid #f59e0b; box-shadow: 0 10px 30px rgba(0,0,0,0.6);"
                        class="rounded-2xl {{ $cardPadding }} flex items-center justify-between gap-3 relative group transition-all"
                    >
                        <!-- Left side: Badge + High Visibility Bold Name -->
                        <div class="flex items-center gap-3 min-w-0 flex-1">
                            <!-- Circular Gold Badge Number -->
                            <div 
                                style="background: linear-gradient(135deg, #1e1b4b 0%, #0f172a 100%); border: 2px solid #f59e0b; color: #fef08a;"
                                class="{{ $badgeSize }} rounded-full shrink-0 flex items-center justify-center font-black shadow-md"
                            >
                                {{ $loop->iteration }}
                            </div>

                            <!-- Name & Position (HIGH READABILITY) -->
                            <div class="min-w-0 flex-1">
                                <h4 
                                    style="{{ $nameStyle }} color: #0f172a;"
                                    class="truncate tracking-tight leading-snug"
                                >
                                    {{ $employee->name }}
                                </h4>
                                <p 
                                    style="{{ $subStyle }} color: #854d0e; font-weight: 700;"
                                    class="truncate mt-0.5 leading-none"
                                >
                                    {{ $employee->designation?->name ?? 'Staff' }}
                                    @if ($count <= 32 && $employee->department?->name)
                                        <span style="color: #64748b; font-weight: 600;">• {{ $employee->department->name }}</span>
                                    @endif
                                </p>
                            </div>
                        </div>

                        <!-- Right side: High Contrast Birthday Date Pill -->
                        <div 
                            style="background: linear-gradient(135deg, #f59e0b 0%, #b45309 100%); color: #ffffff; text-shadow: 0 1px 2px rgba(0,0,0,0.4); border: 1px solid rgba(251, 191, 36, 0.4);"
                            class="shrink-0 {{ $datePill }} rounded-xl uppercase tracking-wider shadow-md"
                        >
                            {{ $employee->dob_ad ? $employee->dob_ad->format('M d') : '' }}
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Bottom Footer (High Contrast Gold & White) -->
    <div class="relative z-10 pt-3 pb-1 flex items-center justify-between text-base font-bold shrink-0" style="border-top: 2px solid rgba(251, 191, 36, 0.4); color: #fef08a;">
        <div class="flex items-center gap-2 uppercase tracking-widest" style="font-family: 'Cinzel', serif; color: #fcd34d;">
            <svg class="w-5 h-5" style="color: #fbbf24;" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2L15 9L22 12L15 15L12 22L9 15L2 12L9 9L12 2Z"/>
            </svg>
            <span>Casino Staff • Official Birthday Celebration</span>
        </div>

        <div class="flex items-center gap-2" style="color: #fbbf24;">
            <span style="font-family: 'Cinzel', serif; color: #fcd34d;" class="font-bold">✦ ✦ ✦</span>
        </div>

        <div class="uppercase tracking-widest" style="font-family: 'Cinzel', serif; color: #fcd34d;">
            With Warmest Regards From Management & Staff
        </div>
    </div>
</div>
