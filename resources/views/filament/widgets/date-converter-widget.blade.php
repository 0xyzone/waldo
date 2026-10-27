<x-filament-widgets::widget class="fi-wi-clean h-full">
    <div 
        data-widget-card
        x-data="{
            copied: false,
            copyText(text) {
                if (!text) return;
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(text);
                } else {
                    const el = document.createElement('textarea');
                    el.value = text;
                    document.body.appendChild(el);
                    el.select();
                    document.execCommand('copy');
                    document.body.removeChild(el);
                }
                this.copied = true;
                setTimeout(() => this.copied = false, 2200);
            },

            // Custom Calendar Picker State
            isCalendarOpen: false,
            viewYear: {{ Carbon\Carbon::parse($adDate ?? now())->year }},
            viewMonth: {{ Carbon\Carbon::parse($adDate ?? now())->month - 1 }},
            monthNames: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
            dayNames: ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'],

            toggleCalendar() {
                this.isCalendarOpen = !this.isCalendarOpen;
                if (this.isCalendarOpen) {
                    this.syncCalendar();
                }
            },

            closeCalendar() {
                this.isCalendarOpen = false;
            },

            syncCalendar() {
                const current = @js($adDate) || new Date().toISOString().slice(0, 10);
                if (current) {
                    const parts = current.split('-');
                    if (parts.length === 3) {
                        this.viewYear = parseInt(parts[0], 10);
                        this.viewMonth = parseInt(parts[1], 10) - 1;
                    }
                }
            },

            prevMonth() {
                if (this.viewMonth === 0) {
                    this.viewMonth = 11;
                    this.viewYear--;
                } else {
                    this.viewMonth--;
                }
            },

            nextMonth() {
                if (this.viewMonth === 11) {
                    this.viewMonth = 0;
                    this.viewYear++;
                } else {
                    this.viewMonth++;
                }
            },

            selectDate(y, m, d) {
                const mm = String(m + 1).padStart(2, '0');
                const dd = String(d).padStart(2, '0');
                const formatted = `${y}-${mm}-${dd}`;
                $wire.set('adDate', formatted);
                this.isCalendarOpen = false;
            },

            selectToday() {
                $wire.setToday();
                const now = new Date();
                this.viewYear = now.getFullYear();
                this.viewMonth = now.getMonth();
                this.isCalendarOpen = false;
            },

            get calendarDays() {
                const days = [];
                const firstDayIndex = new Date(this.viewYear, this.viewMonth, 1).getDay(); // 0 is Sunday
                const totalDaysCurrentMonth = new Date(this.viewYear, this.viewMonth + 1, 0).getDate();
                const totalDaysPrevMonth = new Date(this.viewYear, this.viewMonth, 0).getDate();
                const selected = @js($adDate);
                const now = new Date();
                const todayStr = `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}-${String(now.getDate()).padStart(2, '0')}`;

                // Previous month padding
                for (let i = firstDayIndex - 1; i >= 0; i--) {
                    const d = totalDaysPrevMonth - i;
                    const m = this.viewMonth === 0 ? 11 : this.viewMonth - 1;
                    const y = this.viewMonth === 0 ? this.viewYear - 1 : this.viewYear;
                    const str = `${y}-${String(m + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
                    days.push({
                        day: d,
                        month: m,
                        year: y,
                        isCurrentMonth: false,
                        isSelected: str === selected,
                        isToday: str === todayStr
                    });
                }

                // Current month
                for (let d = 1; d <= totalDaysCurrentMonth; d++) {
                    const str = `${this.viewYear}-${String(this.viewMonth + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
                    days.push({
                        day: d,
                        month: this.viewMonth,
                        year: this.viewYear,
                        isCurrentMonth: true,
                        isSelected: str === selected,
                        isToday: str === todayStr
                    });
                }

                // Next month padding to fill a complete 35 or 42 grid
                const remaining = (7 - (days.length % 7)) % 7;
                const targetTotal = (days.length + remaining) < 35 ? 35 : (days.length + remaining);
                const countNext = targetTotal - days.length;

                for (let d = 1; d <= countNext; d++) {
                    const m = this.viewMonth === 11 ? 0 : this.viewMonth + 1;
                    const y = this.viewMonth === 11 ? this.viewYear + 1 : this.viewYear;
                    const str = `${y}-${String(m + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
                    days.push({
                        day: d,
                        month: m,
                        year: y,
                        isCurrentMonth: false,
                        isSelected: str === selected,
                        isToday: str === todayStr
                    });
                }

                return days;
            }
        }"
        class="relative w-full h-full rounded-3xl bg-white dark:bg-[#121826] border border-slate-200/80 dark:border-white/[0.08] p-5 sm:p-7 shadow-[0_4px_24px_rgba(15,23,42,0.04)] dark:shadow-[0_12px_36px_rgba(0,0,0,0.38)] transition-all duration-300 flex flex-col justify-between overflow-visible"
    >
        <!-- Ambient Background Glow -->
        <div class="pointer-events-none absolute -right-12 -top-12 h-44 w-44 rounded-full bg-gradient-to-br from-orange-500/10 via-amber-500/5 to-transparent blur-3xl"></div>

        <div class="space-y-5 relative">
            <!-- Header Section -->
            <div class="flex items-center justify-between pb-4 border-b border-slate-100 dark:border-white/[0.06]">
                <div class="flex items-center gap-3.5">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-tr from-orange-500/20 via-amber-500/15 to-orange-500/10 dark:from-orange-500/25 dark:via-amber-500/20 dark:to-orange-500/15 border border-orange-500/25 text-orange-600 dark:text-orange-400 shadow-sm shadow-orange-500/10">
                        <x-filament::icon icon="heroicon-s-arrow-path-rounded-square" class="h-5 w-5 text-orange-600 dark:text-orange-400" />
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h2 class="text-base sm:text-lg font-extrabold tracking-tight text-slate-900 dark:text-white">
                                Date Converter
                            </h2>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold tracking-wide uppercase bg-orange-500/10 text-orange-600 dark:text-orange-400 border border-orange-500/20">
                                AD ⇄ BS
                            </span>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-0.5">
                            Bidirectional English (Gregorian) & Nepali (Bikram Sambat) calendar conversion
                        </p>
                    </div>
                </div>

                <!-- Quick Today Action Button -->
                <button 
                    type="button" 
                    wire:click="setToday" 
                    title="Reset to today's date"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold text-slate-700 dark:text-slate-300 bg-slate-100/90 dark:bg-slate-800/80 hover:bg-orange-50 dark:hover:bg-orange-950/30 hover:text-orange-600 dark:hover:text-orange-400 border border-slate-200/80 dark:border-white/[0.08] transition-all cursor-pointer shadow-sm active:scale-95"
                >
                    <x-filament::icon icon="heroicon-m-sparkles" class="h-3.5 w-3.5 text-orange-500" />
                    <span>Today</span>
                </button>
            </div>

            <!-- Segmented Mode Control with Swap Button -->
            <div class="flex items-center gap-2">
                <div class="flex-1 flex p-1 rounded-2xl bg-slate-100/90 dark:bg-slate-900/80 border border-slate-200/70 dark:border-white/[0.06] shadow-inner">
                    <button 
                        type="button"
                        wire:click="setTab('ad_to_bs')"
                        class="flex-1 py-2 px-3 text-xs font-bold text-center rounded-xl transition-all duration-200 cursor-pointer flex items-center justify-center gap-2 {{ $activeTab === 'ad_to_bs' ? 'bg-white dark:bg-slate-800 text-slate-900 dark:text-white shadow-sm ring-1 ring-slate-900/5 dark:ring-white/10' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200' }}"
                    >
                        @if ($activeTab === 'ad_to_bs')
                            <span class="h-1.5 w-1.5 rounded-full bg-orange-500 animate-pulse"></span>
                        @endif
                        <span>English (AD) → Nepali (BS)</span>
                    </button>
                    <button 
                        type="button"
                        wire:click="setTab('bs_to_ad')"
                        class="flex-1 py-2 px-3 text-xs font-bold text-center rounded-xl transition-all duration-200 cursor-pointer flex items-center justify-center gap-2 {{ $activeTab === 'bs_to_ad' ? 'bg-white dark:bg-slate-800 text-slate-900 dark:text-white shadow-sm ring-1 ring-slate-900/5 dark:ring-white/10' : 'text-slate-500 dark:text-slate-400 hover:text-slate-800 dark:hover:text-slate-200' }}"
                    >
                        @if ($activeTab === 'bs_to_ad')
                            <span class="h-1.5 w-1.5 rounded-full bg-orange-500 animate-pulse"></span>
                        @endif
                        <span>Nepali (BS) → English (AD)</span>
                    </button>
                </div>

                <!-- Swap Button -->
                <button 
                    type="button"
                    wire:click="swapTab"
                    title="Swap conversion mode"
                    class="p-2.5 rounded-2xl bg-slate-100/90 dark:bg-slate-900/80 hover:bg-orange-50 dark:hover:bg-orange-950/40 text-slate-600 dark:text-slate-400 hover:text-orange-600 dark:hover:text-orange-400 border border-slate-200/70 dark:border-white/[0.06] transition-all cursor-pointer active:rotate-180 duration-300 shadow-sm"
                >
                    <x-filament::icon icon="heroicon-m-arrows-right-left" class="h-4 w-4" />
                </button>
            </div>

            <!-- Date Inputs -->
            <div>
                @if ($activeTab === 'ad_to_bs')
                    <div class="space-y-1.5 relative">
                        <div class="flex items-center justify-between">
                            <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                Select English Date (AD)
                            </label>
                            <span class="text-[11px] font-bold text-orange-600 dark:text-orange-400 font-mono tracking-wide">
                                {{ $adDate }}
                            </span>
                        </div>

                        <!-- Custom Date Picker Trigger Button (Date icon on the RIGHT / END) -->
                        <button 
                            type="button"
                            @click="toggleCalendar()"
                            class="w-full flex items-center justify-between px-4 py-3 rounded-2xl border border-slate-200/90 dark:border-white/[0.08] bg-slate-50/80 dark:bg-slate-900/60 hover:bg-white dark:hover:bg-slate-900 hover:border-orange-500/50 dark:hover:border-orange-500/50 shadow-sm transition-all duration-200 cursor-pointer group text-left"
                            :class="{ 'ring-2 ring-orange-500/20 border-orange-500': isCalendarOpen }"
                        >
                            <div class="flex flex-col">
                                <span class="text-sm sm:text-base font-extrabold text-slate-900 dark:text-white tracking-tight">
                                    {{ $adDateFormatted ?: $adDate }}
                                </span>
                                <span class="text-[11px] font-medium text-slate-400 dark:text-slate-500 mt-0.5 flex items-center gap-1.5">
                                    <span>Click to open calendar</span>
                                    <span>•</span>
                                    <span class="text-orange-600 dark:text-orange-400 font-mono">{{ $adDate }}</span>
                                </span>
                            </div>

                            <!-- Calendar Icon on the RIGHT / END of the input -->
                            <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-orange-500/10 text-orange-600 dark:text-orange-400 border border-orange-500/20 group-hover:bg-orange-500 group-hover:text-white group-hover:shadow-md group-hover:shadow-orange-500/30 transition-all duration-200">
                                <x-filament::icon icon="heroicon-m-calendar-days" class="h-5 w-5" />
                            </div>
                        </button>

                        <!-- Custom Interactive Calendar Popover -->
                        <div 
                            x-show="isCalendarOpen" 
                            x-transition:enter="transition ease-out duration-200"
                            x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                            x-transition:leave="transition ease-in duration-150"
                            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                            x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                            @click.outside="closeCalendar()"
                            @keydown.escape.window="closeCalendar()"
                            style="display: none;"
                            class="absolute left-0 top-full mt-2 w-full max-w-sm sm:max-w-md z-50 rounded-2xl bg-white dark:bg-[#0f172a] border border-slate-200 dark:border-white/[0.12] p-4 shadow-2xl backdrop-blur-xl"
                        >
                            <!-- Month & Year Selector Header with Nav Buttons -->
                            <div class="flex items-center justify-between pb-3 mb-3 border-b border-slate-100 dark:border-white/[0.08]">
                                <div class="flex items-center gap-1.5">
                                    <!-- Month Dropdown -->
                                    <select 
                                        x-model.number="viewMonth" 
                                        class="rounded-xl border border-slate-200 dark:border-white/[0.08] bg-slate-50 dark:bg-slate-800/90 px-2.5 py-1.5 text-xs font-bold text-slate-800 dark:text-white focus:outline-none focus:ring-1 focus:ring-orange-500 cursor-pointer"
                                    >
                                        <template x-for="(monthName, idx) in monthNames" :key="idx">
                                            <option :value="idx" x-text="monthName" class="dark:bg-slate-900"></option>
                                        </template>
                                    </select>

                                    <!-- Year Dropdown -->
                                    <select 
                                        x-model.number="viewYear" 
                                        class="rounded-xl border border-slate-200 dark:border-white/[0.08] bg-slate-50 dark:bg-slate-800/90 px-2.5 py-1.5 text-xs font-bold text-slate-800 dark:text-white focus:outline-none focus:ring-1 focus:ring-orange-500 cursor-pointer"
                                    >
                                        @for ($y = 1944; $y <= 2033; $y++)
                                            <option value="{{ $y }}" class="dark:bg-slate-900">{{ $y }}</option>
                                        @endfor
                                    </select>
                                </div>

                                <!-- Month Navigation Buttons -->
                                <div class="flex items-center gap-1">
                                    <button 
                                        type="button" 
                                        @click="prevMonth()" 
                                        title="Previous Month"
                                        class="p-1.5 rounded-lg text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer"
                                    >
                                        <x-filament::icon icon="heroicon-m-chevron-left" class="h-4 w-4" />
                                    </button>
                                    <button 
                                        type="button" 
                                        @click="nextMonth()" 
                                        title="Next Month"
                                        class="p-1.5 rounded-lg text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-white hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer"
                                    >
                                        <x-filament::icon icon="heroicon-m-chevron-right" class="h-4 w-4" />
                                    </button>
                                </div>
                            </div>

                            <!-- Day-of-week Header (Sunday First per Workspace Rules) -->
                            <div class="grid grid-cols-7 gap-1 text-center mb-1.5">
                                <template x-for="(dayName, dIdx) in dayNames" :key="dIdx">
                                    <span 
                                        class="text-[11px] font-extrabold uppercase tracking-wider py-1"
                                        :class="dIdx === 0 || dIdx === 6 ? 'text-orange-600/80 dark:text-orange-400/80' : 'text-slate-400 dark:text-slate-500'"
                                        x-text="dayName"
                                    ></span>
                                </template>
                            </div>

                            <!-- Interactive Days Grid -->
                            <div class="grid grid-cols-7 gap-1">
                                <template x-for="(cell, cIdx) in calendarDays" :key="cIdx">
                                    <button 
                                        type="button" 
                                        @click="selectDate(cell.year, cell.month, cell.day)"
                                        class="h-8 sm:h-9 w-full flex items-center justify-center text-xs font-bold rounded-xl transition-all duration-150 cursor-pointer"
                                        :class="{
                                            'bg-gradient-to-r from-orange-500 to-amber-500 text-white shadow-md shadow-orange-500/30 scale-105 font-extrabold': cell.isSelected,
                                            'ring-1 ring-orange-500/50 text-orange-600 dark:text-orange-400': cell.isToday && !cell.isSelected,
                                            'text-slate-800 dark:text-slate-200 hover:bg-orange-50 dark:hover:bg-orange-950/40 hover:text-orange-600 dark:hover:text-orange-400': cell.isCurrentMonth && !cell.isSelected,
                                            'text-slate-300 dark:text-slate-600 hover:bg-slate-100 dark:hover:bg-slate-800/60': !cell.isCurrentMonth && !cell.isSelected
                                        }"
                                        x-text="cell.day"
                                    ></button>
                                </template>
                            </div>

                            <!-- Calendar Popover Footer -->
                            <div class="flex items-center justify-between pt-3 mt-3 border-t border-slate-100 dark:border-white/[0.08]">
                                <button 
                                    type="button" 
                                    @click="selectToday()" 
                                    class="inline-flex items-center gap-1.5 text-xs font-bold text-orange-600 dark:text-orange-400 hover:underline cursor-pointer"
                                >
                                    <x-filament::icon icon="heroicon-m-sparkles" class="h-3.5 w-3.5" />
                                    <span>Jump to Today</span>
                                </button>
                                <button 
                                    type="button" 
                                    @click="closeCalendar()" 
                                    class="px-2.5 py-1 text-xs font-semibold text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors cursor-pointer"
                                >
                                    Close
                                </button>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="space-y-1.5">
                        <label class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                            Select Nepali Date (BS)
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                            <!-- BS Year -->
                            <div class="relative">
                                <select 
                                    id="bsYear" 
                                    wire:model.live="bsYear" 
                                    class="w-full rounded-2xl border border-slate-200/90 dark:border-white/[0.08] bg-slate-50/80 dark:bg-slate-900/60 px-3.5 py-2.5 text-xs font-bold text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition-all cursor-pointer appearance-none"
                                >
                                    @foreach ($bsYears as $year)
                                        <option value="{{ $year }}" class="dark:bg-slate-900">{{ $year }} BS</option>
                                    @endforeach
                                </select>
                                <div class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-slate-400">
                                    <x-filament::icon icon="heroicon-m-chevron-down" class="h-3.5 w-3.5" />
                                </div>
                            </div>

                            <!-- BS Month -->
                            <div class="relative">
                                <select 
                                    id="bsMonth" 
                                    wire:model.live="bsMonth" 
                                    class="w-full rounded-2xl border border-slate-200/90 dark:border-white/[0.08] bg-slate-50/80 dark:bg-slate-900/60 px-3.5 py-2.5 text-xs font-bold text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition-all cursor-pointer appearance-none"
                                >
                                    <option value="1" class="dark:bg-slate-900">01 - Baishakh (बैशाख)</option>
                                    <option value="2" class="dark:bg-slate-900">02 - Jestha (जेठ)</option>
                                    <option value="3" class="dark:bg-slate-900">03 - Ashadh (असार)</option>
                                    <option value="4" class="dark:bg-slate-900">04 - Shrawan (साउन)</option>
                                    <option value="5" class="dark:bg-slate-900">05 - Bhadra (भदौ)</option>
                                    <option value="6" class="dark:bg-slate-900">06 - Ashwin (असोज)</option>
                                    <option value="7" class="dark:bg-slate-900">07 - Kartik (कात्तिक)</option>
                                    <option value="8" class="dark:bg-slate-900">08 - Mangsir (मंसिर)</option>
                                    <option value="9" class="dark:bg-slate-900">09 - Paush (पुष)</option>
                                    <option value="10" class="dark:bg-slate-900">10 - Magh (माघ)</option>
                                    <option value="11" class="dark:bg-slate-900">11 - Falgun (फागुन)</option>
                                    <option value="12" class="dark:bg-slate-900">12 - Chaitra (चैत)</option>
                                </select>
                                <div class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-slate-400">
                                    <x-filament::icon icon="heroicon-m-chevron-down" class="h-3.5 w-3.5" />
                                </div>
                            </div>

                            <!-- BS Day -->
                            <div class="relative">
                                <select 
                                    id="bsDay" 
                                    wire:model.live="bsDay" 
                                    class="w-full rounded-2xl border border-slate-200/90 dark:border-white/[0.08] bg-slate-50/80 dark:bg-slate-900/60 px-3.5 py-2.5 text-xs font-bold text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition-all cursor-pointer appearance-none"
                                >
                                    @foreach ($bsDays as $day)
                                        <option value="{{ $day }}" class="dark:bg-slate-900">Day {{ sprintf('%02d', $day) }}</option>
                                    @endforeach
                                </select>
                                <div class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-slate-400">
                                    <x-filament::icon icon="heroicon-m-chevron-down" class="h-3.5 w-3.5" />
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Conversion Result Display Card (fills remaining height harmoniously) -->
        <div class="relative flex-1 mt-5 p-5 sm:p-6 rounded-2xl bg-gradient-to-br from-slate-50/90 via-white to-orange-50/20 dark:from-slate-900/90 dark:via-slate-900/70 dark:to-orange-950/20 border border-slate-200/80 dark:border-orange-500/20 shadow-sm flex flex-col justify-between overflow-hidden">
            <!-- Inner subtle glow -->
            <div class="pointer-events-none absolute -bottom-10 -right-10 h-32 w-32 rounded-full bg-orange-500/10 blur-xl"></div>

            @if ($activeTab === 'ad_to_bs')
                <div class="space-y-3 relative">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                Nepali Date (Bikram Sambat)
                            </span>
                        </div>
                        <button 
                            type="button" 
                            @click="copyText('{{ $convertedBsDateNp ?: $convertedBsDate }}')"
                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold transition-all cursor-pointer active:scale-95"
                            :class="copied 
                                ? 'bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 border border-emerald-500/30' 
                                : 'bg-orange-500/10 text-orange-600 dark:text-orange-400 hover:bg-orange-500/20 border border-orange-500/20'"
                        >
                            <x-filament::icon x-show="!copied" icon="heroicon-m-clipboard-document" class="h-3.5 w-3.5" />
                            <x-filament::icon x-show="copied" icon="heroicon-m-check" class="h-3.5 w-3.5" />
                            <span x-text="copied ? 'Copied!' : 'Copy'"></span>
                        </button>
                    </div>

                    @if ($convertedBsDateNp)
                        <div class="text-3xl sm:text-4xl font-extrabold tracking-tight text-slate-900 dark:text-white leading-tight">
                            {{ $convertedBsDateNp }}
                        </div>

                        <div class="flex flex-wrap items-center gap-2 pt-1">
                            <!-- Numeric Date Badge -->
                            <button 
                                type="button" 
                                @click="copyText('{{ $convertedBsDate }}')" 
                                title="Click to copy numeric format"
                                class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl font-mono text-xs font-bold bg-orange-500/10 text-orange-600 dark:text-orange-400 border border-orange-500/25 hover:bg-orange-500/20 transition-all cursor-pointer"
                            >
                                <x-filament::icon icon="heroicon-m-calendar" class="h-3.5 w-3.5" />
                                <span>{{ $convertedBsDate }}</span>
                            </button>

                            <!-- Weekday Badge -->
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-semibold bg-slate-100/90 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200/70 dark:border-white/[0.08]">
                                <x-filament::icon icon="heroicon-m-clock" class="h-3.5 w-3.5 text-slate-400" />
                                <span>{{ $convertedBsWeekday }}</span>
                                @if ($convertedBsWeekdayEn)
                                    <span class="text-slate-400">• {{ $convertedBsWeekdayEn }}</span>
                                @endif
                            </span>
                        </div>
                    @else
                        <div class="text-sm font-semibold text-rose-500 py-1">
                            {{ $convertedBsDate ?? 'Enter a valid date' }}
                        </div>
                    @endif
                </div>
            @else
                <div class="space-y-3 relative">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="h-2 w-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                English Date (Gregorian)
                            </span>
                        </div>
                        <button 
                            type="button" 
                            @click="copyText('{{ $convertedAdDate }}')"
                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-bold transition-all cursor-pointer active:scale-95"
                            :class="copied 
                                ? 'bg-emerald-500/15 text-emerald-600 dark:text-emerald-400 border border-emerald-500/30' 
                                : 'bg-orange-500/10 text-orange-600 dark:text-orange-400 hover:bg-orange-500/20 border border-orange-500/20'"
                        >
                            <x-filament::icon x-show="!copied" icon="heroicon-m-clipboard-document" class="h-3.5 w-3.5" />
                            <x-filament::icon x-show="copied" icon="heroicon-m-check" class="h-3.5 w-3.5" />
                            <span x-text="copied ? 'Copied!' : 'Copy'"></span>
                        </button>
                    </div>

                    @if ($convertedAdDate && $convertedAdDate !== 'Invalid BS Date' && $convertedAdDate !== 'Conversion failed')
                        <div class="text-3xl sm:text-4xl font-extrabold tracking-tight text-slate-900 dark:text-white leading-tight">
                            {{ Carbon\Carbon::parse($convertedAdDate)->format('F j, Y') }}
                        </div>

                        <div class="flex flex-wrap items-center gap-2 pt-1">
                            <!-- Numeric Date Badge -->
                            <button 
                                type="button" 
                                @click="copyText('{{ $convertedAdDate }}')" 
                                title="Click to copy ISO format"
                                class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl font-mono text-xs font-bold bg-orange-500/10 text-orange-600 dark:text-orange-400 border border-orange-500/25 hover:bg-orange-500/20 transition-all cursor-pointer"
                            >
                                <x-filament::icon icon="heroicon-m-calendar" class="h-3.5 w-3.5" />
                                <span>{{ $convertedAdDate }}</span>
                            </button>

                            <!-- Weekday Badge -->
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-xl text-xs font-semibold bg-slate-100/90 dark:bg-slate-800 text-slate-700 dark:text-slate-300 border border-slate-200/70 dark:border-white/[0.08]">
                                <x-filament::icon icon="heroicon-m-clock" class="h-3.5 w-3.5 text-slate-400" />
                                <span>{{ $convertedAdWeekday }}</span>
                                @if ($convertedAdWeekdayNp)
                                    <span class="text-slate-400">• {{ $convertedAdWeekdayNp }}</span>
                                @endif
                            </span>
                        </div>
                    @else
                        <div class="text-sm font-semibold text-rose-500 py-1">
                            {{ $convertedAdDate ?? 'Enter a valid date' }}
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</x-filament-widgets::widget>
