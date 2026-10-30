<x-filament-panels::page>
    <div class="w-full space-y-5">
        {{-- 1. Top Executive Control Strip & KPI Summary --}}
        <div class="relative overflow-hidden rounded-2xl bg-gradient-to-r from-gray-900 via-gray-900 to-gray-800 p-5 sm:p-6 text-white shadow-xl border border-gray-800">
            {{-- Ambient glow accents --}}
            <div class="absolute -right-20 -top-20 w-80 h-80 rounded-full bg-primary-500/10 blur-3xl pointer-events-none"></div>
            <div class="absolute right-1/4 -bottom-20 w-64 h-64 rounded-full bg-emerald-500/10 blur-3xl pointer-events-none"></div>

            <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-5">
                {{-- Title & Context --}}
                <div class="space-y-1">
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-primary-500/15 text-primary-300 border border-primary-500/30">
                        <x-heroicon-m-sparkles class="w-3.5 h-3.5 text-primary-400" />
                        <span>Royalty Bonus Policy & Allocation Studio</span>
                    </div>
                    <h1 class="text-xl sm:text-2xl font-black tracking-tight text-white flex items-center gap-2.5">
                        <span>Royalty Bonus Configuration</span>
                        <span class="text-[11px] font-mono font-medium px-2 py-0.5 rounded-md bg-gray-800 border border-gray-700 text-gray-300">
                            v{{ \App\Services\ChangelogService::getCurrentVersion() }}
                        </span>
                    </h1>
                    <p class="text-xs text-gray-400 max-w-2xl leading-relaxed">
                        Fine-tune global attendance deduction formulas, grace thresholds, biometric penalties, and toggle participating job roles side-by-side.
                    </p>
                </div>

                {{-- View Mode Switcher --}}
                <div class="flex items-center gap-1 p-1 rounded-xl bg-gray-950/80 border border-gray-800 self-start lg:self-center shadow-inner shrink-0">
                    <button
                        type="button"
                        wire:click="setViewMode('split')"
                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold transition {{ $viewMode === 'split' ? 'bg-primary-600 text-white shadow-md' : 'text-gray-400 hover:text-white' }}"
                        title="Side-by-side Split Studio (Rules on left, Designations on right)"
                    >
                        <x-heroicon-m-view-columns class="w-3.5 h-3.5" />
                        <span>Split Studio</span>
                    </button>
                    <button
                        type="button"
                        wire:click="setViewMode('designations')"
                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold transition {{ $viewMode === 'designations' ? 'bg-primary-600 text-white shadow-md' : 'text-gray-400 hover:text-white' }}"
                        title="Focus only on Designation Allocation Matrix"
                    >
                        <x-heroicon-m-table-cells class="w-3.5 h-3.5" />
                        <span>Designations Only</span>
                    </button>
                    <button
                        type="button"
                        wire:click="setViewMode('rules')"
                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold transition {{ $viewMode === 'rules' ? 'bg-primary-600 text-white shadow-md' : 'text-gray-400 hover:text-white' }}"
                        title="Focus only on Deduction & Attendance Policy Rules"
                    >
                        <x-heroicon-m-adjustments-horizontal class="w-3.5 h-3.5" />
                        <span>Rules Only</span>
                    </button>
                </div>
            </div>

            {{-- 5 Executive KPI Stat Pills --}}
            <div class="mt-5 pt-4 border-t border-gray-800/80 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 sm:gap-4">
                {{-- Stat 1: Total Roles --}}
                <div class="flex items-center gap-3 p-2.5 sm:p-3 rounded-xl bg-gray-800/40 border border-gray-800/80">
                    <div class="w-9 h-9 rounded-lg bg-blue-500/10 border border-blue-500/20 text-blue-400 flex items-center justify-center shrink-0">
                        <x-heroicon-m-briefcase class="w-4 h-4" />
                    </div>
                    <div class="min-w-0">
                        <div class="text-[10px] uppercase tracking-wider font-semibold text-gray-400 truncate">Total Roles</div>
                        <div class="text-base sm:text-lg font-black text-white truncate">{{ $this->totalDesignationsCount }} <span class="text-xs font-normal text-gray-400">Designations</span></div>
                    </div>
                </div>

                {{-- Stat 2: Participating (Eligible) Roles --}}
                <div class="flex items-center gap-3 p-2.5 sm:p-3 rounded-xl bg-gray-800/40 border border-gray-800/80">
                    <div class="w-9 h-9 rounded-lg bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 flex items-center justify-center shrink-0">
                        <x-heroicon-m-check-badge class="w-4 h-4" />
                    </div>
                    <div class="min-w-0">
                        <div class="text-[10px] uppercase tracking-wider font-semibold text-gray-400 truncate">Eligible Roles</div>
                        <div class="text-base sm:text-lg font-black text-emerald-400 flex items-center gap-1.5 truncate">
                            <span>{{ $this->eligibleDesignationsCount }} Active</span>
                            <span class="text-[10px] font-semibold px-1.5 py-0.2 rounded-full bg-emerald-500/20 text-emerald-300">
                                {{ $this->eligiblePercentage }}%
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Stat 3: Excluded Roles --}}
                <div class="flex items-center gap-3 p-2.5 sm:p-3 rounded-xl bg-gray-800/40 border border-gray-800/80">
                    <div class="w-9 h-9 rounded-lg bg-rose-500/10 border border-rose-500/20 text-rose-400 flex items-center justify-center shrink-0">
                        <x-heroicon-m-no-symbol class="w-4 h-4" />
                    </div>
                    <div class="min-w-0">
                        <div class="text-[10px] uppercase tracking-wider font-semibold text-gray-400 truncate">Excluded Roles</div>
                        <div class="text-base sm:text-lg font-black text-rose-400 flex items-center gap-1.5 truncate">
                            <span>{{ $this->excludedDesignationsCount }} Skipped</span>
                            <span class="text-[10px] font-semibold px-1.5 py-0.2 rounded-full bg-rose-500/20 text-rose-300">
                                {{ $this->excludedPercentage }}%
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Stat 4: Monthly Pool Cap --}}
                <div class="flex items-center gap-3 p-2.5 sm:p-3 rounded-xl bg-gray-800/40 border border-gray-800/80">
                    <div class="w-9 h-9 rounded-lg bg-amber-500/10 border border-amber-500/20 text-amber-400 flex items-center justify-center shrink-0">
                        <x-heroicon-m-banknotes class="w-4 h-4" />
                    </div>
                    <div class="min-w-0">
                        <div class="text-[10px] uppercase tracking-wider font-semibold text-gray-400 truncate">Monthly Max Pool</div>
                        <div class="text-base sm:text-lg font-black text-amber-400 font-mono truncate">
                            Rs. {{ number_format($this->totalMonthlyMaxBudget, 0) }}
                        </div>
                    </div>
                </div>

                {{-- Stat 5: Minimum Floor Baseline --}}
                <div class="flex items-center gap-3 p-2.5 sm:p-3 rounded-xl bg-gray-800/40 border border-gray-800/80">
                    <div class="w-9 h-9 rounded-lg {{ $royalty_enforce_min_floor ? 'bg-indigo-500/10 border-indigo-500/20 text-indigo-400' : 'bg-gray-800 text-gray-500' }} border flex items-center justify-center shrink-0">
                        <x-heroicon-m-shield-check class="w-4 h-4" />
                    </div>
                    <div class="min-w-0">
                        <div class="text-[10px] uppercase tracking-wider font-semibold text-gray-400 truncate">Payout Floor</div>
                        <div class="text-xs sm:text-sm font-bold {{ $royalty_enforce_min_floor ? 'text-indigo-400' : 'text-gray-400' }} flex items-center gap-1.5 truncate">
                            <span class="w-2 h-2 rounded-full {{ $royalty_enforce_min_floor ? 'bg-indigo-500 animate-pulse' : 'bg-gray-500' }}"></span>
                            <span>{{ $royalty_enforce_min_floor ? 'Guaranteed Active' : 'Disabled' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 2. Interactive Studio Body --}}
        <div class="grid grid-cols-1 {{ $viewMode === 'split' ? 'lg:grid-cols-12 gap-5' : ($viewMode === 'rules' ? 'grid-cols-1 gap-5' : 'grid-cols-1 gap-5') }} items-start">
            
            {{-- ======================================================== --}}
            {{-- LEFT CONSOLE: DEDUCTION POLICY RULES & PRESETS           --}}
            {{-- ======================================================== --}}
            @if($viewMode === 'split' || $viewMode === 'rules')
                <div class="{{ $viewMode === 'split' ? 'lg:col-span-5 space-y-3.5 max-h-[640px] overflow-y-auto pr-1' : 'w-full space-y-5' }}">
                    <form wire:submit="save" class="space-y-3.5">
                        
                        {{-- Policy Console Header & Presets Bar --}}
                        <div class="rounded-2xl bg-white dark:bg-gray-900 border border-gray-200/80 dark:border-gray-800 p-3.5 shadow-sm space-y-3">
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-lg bg-primary-100 dark:bg-primary-950/60 text-primary-600 dark:text-primary-400 flex items-center justify-center">
                                        <x-heroicon-m-adjustments-horizontal class="w-4 h-4" />
                                    </div>
                                    <h2 class="text-sm font-bold text-gray-900 dark:text-white">Deduction Rules</h2>
                                </div>

                                {{-- Quick Save Button --}}
                                <x-filament::button
                                    type="submit"
                                    size="sm"
                                    icon="heroicon-m-check"
                                    color="primary"
                                    class="shadow-sm font-bold text-xs"
                                >
                                    <span wire:loading.remove wire:target="save">Save Rules</span>
                                    <span wire:loading wire:target="save">Saving...</span>
                                </x-filament::button>
                            </div>

                            {{-- Policy Presets Bar --}}
                            <div class="pt-2 border-t border-gray-100 dark:border-gray-800/80 flex items-center justify-between gap-2">
                                <span class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider">Quick Presets:</span>
                                <div class="flex items-center gap-1.5">
                                    <button
                                        type="button"
                                        wire:click="applyPreset('standard')"
                                        class="px-2 py-0.5 rounded text-[11px] font-semibold bg-gray-100 hover:bg-primary-50 hover:text-primary-700 dark:bg-gray-800 dark:hover:bg-primary-950/60 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700 transition"
                                        title="Standard: 3 late grace, 2 thumb grace, Rs. 500 flat absent"
                                    >
                                        ⚡ Standard
                                    </button>
                                    <button
                                        type="button"
                                        wire:click="applyPreset('strict')"
                                        class="px-2 py-0.5 rounded text-[11px] font-semibold bg-gray-100 hover:bg-rose-50 hover:text-rose-700 dark:bg-gray-800 dark:hover:bg-rose-950/60 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700 transition"
                                        title="Strict: 2 late grace, 1 thumb grace, Rs. 750 per absent day"
                                    >
                                        🛡️ Strict
                                    </button>
                                    <button
                                        type="button"
                                        wire:click="applyPreset('lenient')"
                                        class="px-2 py-0.5 rounded text-[11px] font-semibold bg-gray-100 hover:bg-emerald-50 hover:text-emerald-700 dark:bg-gray-800 dark:hover:bg-emerald-950/60 text-gray-700 dark:text-gray-300 border border-gray-200 dark:border-gray-700 transition"
                                        title="Lenient: 4 late grace, 3 thumb grace, flat caps"
                                    >
                                        🌿 Lenient
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Cards Grid (1 column in split mode, 2 columns in rules mode) --}}
                        <div class="grid grid-cols-1 {{ $viewMode === 'rules' ? 'md:grid-cols-2 gap-4' : 'gap-3' }}">

                            {{-- CARD 1: Absent Days Deduction --}}
                            <div class="rounded-2xl bg-white dark:bg-gray-900 border border-gray-200/80 dark:border-gray-800 p-3.5 shadow-sm hover:border-blue-400/50 dark:hover:border-blue-500/50 transition">
                                <div class="flex items-center justify-between gap-3 mb-2.5">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-lg bg-blue-50 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400 border border-blue-200 dark:border-blue-800 flex items-center justify-center shrink-0">
                                            <x-heroicon-m-calendar-days class="w-4 h-4" />
                                        </div>
                                        <div>
                                            <h3 class="text-xs font-bold text-gray-900 dark:text-white">Absent Days Policy</h3>
                                            <p class="text-[11px] text-gray-500">Unapproved employee work absences</p>
                                        </div>
                                    </div>
                                    <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full {{ $royalty_absent_mode === 'countable' ? 'bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300' : 'bg-blue-100 text-blue-800 dark:bg-blue-950/60 dark:text-blue-300' }}">
                                        {{ $royalty_absent_mode === 'countable' ? 'Per Day' : 'Flat Penalty' }}
                                    </span>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                                    <div>
                                        <label class="block text-[11px] font-semibold text-gray-700 dark:text-gray-300 mb-1">Deduction Mode</label>
                                        <select
                                            wire:model.live="royalty_absent_mode"
                                            class="w-full text-xs py-1.5 px-2.5 rounded-lg border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 outline-none"
                                        >
                                            <option value="boolean">Flat (Deduct once if absent > 0)</option>
                                            <option value="countable">Per Absent Day (Multiply by count)</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-semibold text-gray-700 dark:text-gray-300 mb-1">Penalty Amount</label>
                                        <div class="relative flex items-center">
                                            <span class="pointer-events-none absolute inset-y-0 left-0 z-10 pl-3 flex items-center text-gray-500 dark:text-gray-400 text-[11px] font-mono font-semibold select-none">Rs.</span>
                                            <input
                                                type="number"
                                                step="50"
                                                wire:model="royalty_absent_amount"
                                                style="padding-left: 2.5rem !important;"
                                                class="w-full text-xs !pl-10 pr-2.5 py-1.5 rounded-lg border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white font-mono focus:ring-2 focus:ring-primary-500 outline-none"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- CARD 2: Late In & Early Out --}}
                            <div class="rounded-2xl bg-white dark:bg-gray-900 border border-gray-200/80 dark:border-gray-800 p-3.5 shadow-sm hover:border-amber-400/50 dark:hover:border-amber-500/50 transition">
                                <div class="flex items-center justify-between gap-3 mb-2.5">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-lg bg-amber-50 dark:bg-amber-950/50 text-amber-600 dark:text-amber-400 border border-amber-200 dark:border-amber-800 flex items-center justify-center shrink-0">
                                            <x-heroicon-m-clock class="w-4 h-4" />
                                        </div>
                                        <div>
                                            <h3 class="text-xs font-bold text-gray-900 dark:text-white">Late In & Early Out</h3>
                                            <p class="text-[11px] text-gray-500">Tardiness and premature departures</p>
                                        </div>
                                    </div>
                                    <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 dark:bg-amber-950/60 dark:text-amber-300">
                                        Grace: {{ $royalty_late_early_threshold }}x
                                    </span>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 text-xs">
                                    <div>
                                        <label class="block text-[11px] font-semibold text-gray-700 dark:text-gray-300 mb-1">Mode</label>
                                        <select
                                            wire:model.live="royalty_late_early_mode"
                                            class="w-full text-xs py-1.5 px-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 outline-none"
                                        >
                                            <option value="countable">Per Excess</option>
                                            <option value="boolean">Flat Penalty</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-semibold text-gray-700 dark:text-gray-300 mb-1">Grace Limit</label>
                                        <input
                                            type="number"
                                            wire:model="royalty_late_early_threshold"
                                            class="w-full text-xs px-2.5 py-1.5 rounded-lg border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white font-mono focus:ring-2 focus:ring-primary-500 outline-none"
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-semibold text-gray-700 dark:text-gray-300 mb-1">Penalty</label>
                                        <div class="relative flex items-center">
                                            <span class="pointer-events-none absolute inset-y-0 left-0 z-10 pl-2.5 flex items-center text-gray-500 dark:text-gray-400 text-[11px] font-mono font-semibold select-none">Rs.</span>
                                            <input
                                                type="number"
                                                step="25"
                                                wire:model="royalty_late_early_penalty"
                                                style="padding-left: 2.35rem !important;"
                                                class="w-full text-xs !pl-9 pr-2 py-1.5 rounded-lg border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white font-mono focus:ring-2 focus:ring-primary-500 outline-none"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- CARD 3: Missing Thumb / Biometric Punch --}}
                            <div class="rounded-2xl bg-white dark:bg-gray-900 border border-gray-200/80 dark:border-gray-800 p-3.5 shadow-sm hover:border-purple-400/50 dark:hover:border-purple-500/50 transition">
                                <div class="flex items-center justify-between gap-3 mb-2.5">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-lg bg-purple-50 dark:bg-purple-950/50 text-purple-600 dark:text-purple-400 border border-purple-200 dark:border-purple-800 flex items-center justify-center shrink-0">
                                            <x-heroicon-m-finger-print class="w-4 h-4" />
                                        </div>
                                        <div>
                                            <h3 class="text-xs font-bold text-gray-900 dark:text-white">Missing Thumb Punches</h3>
                                            <p class="text-[11px] text-gray-500">Net unpunched logs (Missing Thumb &minus; Absent Days)</p>
                                        </div>
                                    </div>
                                    <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-purple-100 text-purple-800 dark:bg-purple-950/60 dark:text-purple-300">
                                        Grace: {{ $royalty_missing_thumb_threshold }}x
                                    </span>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 text-xs">
                                    <div>
                                        <label class="block text-[11px] font-semibold text-gray-700 dark:text-gray-300 mb-1">Mode</label>
                                        <select
                                            wire:model.live="royalty_missing_thumb_mode"
                                            class="w-full text-xs py-1.5 px-2 rounded-lg border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 outline-none"
                                        >
                                            <option value="countable">Per Excess</option>
                                            <option value="boolean">Flat Penalty</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-semibold text-gray-700 dark:text-gray-300 mb-1">Grace Limit</label>
                                        <input
                                            type="number"
                                            wire:model="royalty_missing_thumb_threshold"
                                            class="w-full text-xs px-2.5 py-1.5 rounded-lg border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white font-mono focus:ring-2 focus:ring-primary-500 outline-none"
                                        />
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-semibold text-gray-700 dark:text-gray-300 mb-1">Penalty</label>
                                        <div class="relative flex items-center">
                                            <span class="pointer-events-none absolute inset-y-0 left-0 z-10 pl-2.5 flex items-center text-gray-500 dark:text-gray-400 text-[11px] font-mono font-semibold select-none">Rs.</span>
                                            <input
                                                type="number"
                                                step="25"
                                                wire:model="royalty_missing_thumb_penalty"
                                                style="padding-left: 2.35rem !important;"
                                                class="w-full text-xs !pl-9 pr-2 py-1.5 rounded-lg border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white font-mono focus:ring-2 focus:ring-primary-500 outline-none"
                                            />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- CARD 4: Minimum Floor Payout Guarantee --}}
                            <div class="rounded-2xl bg-white dark:bg-gray-900 border border-gray-200/80 dark:border-gray-800 p-3.5 shadow-sm hover:border-emerald-400/50 dark:hover:border-emerald-500/50 transition flex flex-col justify-between">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="flex items-start gap-2.5">
                                        <div class="w-8 h-8 rounded-lg bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800 flex items-center justify-center shrink-0 mt-0.5">
                                            <x-heroicon-m-shield-check class="w-4 h-4" />
                                        </div>
                                        <div>
                                            <h3 class="text-xs font-bold text-gray-900 dark:text-white">Minimum Floor Protection</h3>
                                            <p class="text-[11px] text-gray-500 mt-0.5 leading-snug">
                                                Net bonus never drops below designation's Monthly Min payout floor.
                                            </p>
                                        </div>
                                    </div>
                                    
                                    {{-- Interactive Toggle Switch --}}
                                    <button
                                        type="button"
                                        wire:click="toggleFloorProtection"
                                        class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none {{ $royalty_enforce_min_floor ? 'bg-emerald-600' : 'bg-gray-300 dark:bg-gray-700' }}"
                                        role="switch"
                                        aria-checked="{{ $royalty_enforce_min_floor ? 'true' : 'false' }}"
                                    >
                                        <span
                                            aria-hidden="true"
                                            class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow-sm ring-0 transition duration-200 ease-in-out {{ $royalty_enforce_min_floor ? 'translate-x-5' : 'translate-x-0' }}"
                                        ></span>
                                    </button>
                                </div>

                                <div class="mt-3 pt-2 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between text-[11px]">
                                    <span class="text-gray-500">Floor Status:</span>
                                    <span class="font-bold {{ $royalty_enforce_min_floor ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-400' }}">
                                        {{ $royalty_enforce_min_floor ? 'Active & Guaranteed' : 'Disabled (Allow < Min)' }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Sticky Footer Save Action --}}
                        <div class="rounded-xl bg-gray-50 dark:bg-gray-800/60 border border-gray-200/80 dark:border-gray-800 p-3 flex items-center justify-between gap-3 shadow-xs">
                            <span class="text-xs text-gray-500">
                                Parameters apply to all future royalty calculations.
                            </span>
                            <x-filament::button
                                type="submit"
                                size="sm"
                                icon="heroicon-m-check"
                                color="primary"
                                class="shadow-sm font-bold text-xs"
                            >
                                <span wire:loading.remove wire:target="save">Commit Deduction Rules</span>
                                <span wire:loading wire:target="save">Updating...</span>
                            </x-filament::button>
                        </div>
                    </form>
                </div>
            @endif

            {{-- ======================================================== --}}
            {{-- RIGHT CONSOLE: DESIGNATION PARTICIPATION STUDIO          --}}
            {{-- ======================================================== --}}
            @if($viewMode === 'split' || $viewMode === 'designations')
                <div class="{{ $viewMode === 'split' ? 'lg:col-span-7' : 'w-full' }}">
                    <div class="fi-section rounded-2xl bg-white dark:bg-gray-900 shadow-sm ring-1 ring-gray-950/5 dark:ring-white/10 overflow-hidden border border-gray-200/80 dark:border-gray-800 flex flex-col">
                        
                        {{-- Studio Top Bar --}}
                        <div class="p-4 sm:p-5 border-b border-gray-100 dark:border-gray-800 bg-gray-50/70 dark:bg-gray-800/40 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div class="flex items-center gap-2.5">
                                <div class="w-9 h-9 rounded-xl bg-primary-100 dark:bg-primary-950/60 border border-primary-200 dark:border-primary-800 flex items-center justify-center text-primary-700 dark:text-primary-300 font-bold shrink-0">
                                    <x-heroicon-m-table-cells class="w-4 h-4" />
                                </div>
                                <div>
                                    <h2 class="text-sm font-bold text-gray-900 dark:text-white flex flex-wrap items-center gap-2">
                                        <span>Designation Eligibility Matrix</span>
                                        <span class="px-2 py-0.5 text-[11px] font-semibold rounded-full bg-emerald-100 text-emerald-800 dark:bg-emerald-950/80 dark:text-emerald-300 border border-emerald-200 dark:border-emerald-800">
                                            {{ $this->eligibleDesignationsCount }} Eligible
                                        </span>
                                        <span class="px-2 py-0.5 text-[11px] font-semibold rounded-full bg-rose-100 text-rose-800 dark:bg-rose-950/80 dark:text-rose-300 border border-rose-200 dark:border-rose-800">
                                            {{ $this->excludedDesignationsCount }} Excluded
                                        </span>
                                    </h2>
                                    <p class="text-[11px] text-gray-500 dark:text-gray-400 mt-0.5">
                                        Toggle roles calculated in Royalty Bonus reports. Unchecked roles are skipped.
                                    </p>
                                </div>
                            </div>

                            {{-- Bulk Action Buttons --}}
                            <div class="flex items-center gap-2 shrink-0">
                                <button
                                    type="button"
                                    wire:click="bulkSetEligibility(true)"
                                    class="text-xs px-2.5 py-1.5 rounded-lg border border-emerald-300 dark:border-emerald-800 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 hover:bg-emerald-100 dark:hover:bg-emerald-900/40 font-semibold transition flex items-center gap-1 shadow-xs"
                                    title="Include all currently filtered roles in Royalty calculations"
                                >
                                    <x-heroicon-m-check class="w-3.5 h-3.5" />
                                    <span>Enable Filtered</span>
                                </button>
                                <button
                                    type="button"
                                    wire:click="bulkSetEligibility(false)"
                                    class="text-xs px-2.5 py-1.5 rounded-lg border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 font-semibold transition flex items-center gap-1 shadow-xs"
                                    title="Exclude all currently filtered roles from Royalty calculations"
                                >
                                    <x-heroicon-m-x-mark class="w-3.5 h-3.5" />
                                    <span>Disable Filtered</span>
                                </button>
                            </div>
                        </div>

                        {{-- Filter Toolbar --}}
                        <div class="p-3 sm:p-3.5 bg-white dark:bg-gray-900 border-b border-gray-100 dark:border-gray-800 flex flex-col md:flex-row items-stretch md:items-center justify-between gap-2.5">
                            <div class="flex flex-wrap items-center gap-2 flex-1">
                                {{-- Search Input with Centered Icon --}}
                                <div class="relative flex items-center w-full sm:w-56 md:w-60">
                                    <div class="pointer-events-none absolute inset-y-0 left-0 z-10 flex items-center pl-3 text-gray-400 dark:text-gray-500">
                                        <x-heroicon-m-magnifying-glass class="w-3.5 h-3.5 shrink-0" />
                                    </div>
                                    <input
                                        type="text"
                                        wire:model.live.debounce.250ms="searchDesignation"
                                        placeholder="Search designation title..."
                                        style="padding-left: 2.25rem !important;"
                                        class="w-full text-xs !pl-9 pr-3 py-1.5 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white placeholder-gray-400 focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none shadow-xs"
                                    />
                                </div>

                                {{-- Department Dropdown --}}
                                <select
                                    wire:model.live="selectedDepartment"
                                    class="text-xs py-1.5 px-2.5 rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-primary-500 focus:border-transparent outline-none shadow-xs"
                                >
                                    <option value="all">All Departments ({{ $this->departments->count() }})</option>
                                    @foreach($this->departments as $dept)
                                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                                    @endforeach
                                </select>

                                {{-- Status Filter Tabs --}}
                                <div class="inline-flex rounded-lg border border-gray-200 dark:border-gray-700 p-0.5 bg-gray-50 dark:bg-gray-800/60 text-xs">
                                    <button
                                        type="button"
                                        wire:click="setRoyaltyFilter('all')"
                                        class="px-2.5 py-1 rounded-md font-medium transition {{ $royaltyFilter === 'all' ? 'bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-xs font-bold' : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300' }}"
                                    >
                                        All ({{ $this->scopedAllCount }})
                                    </button>
                                    <button
                                        type="button"
                                        wire:click="setRoyaltyFilter('eligible')"
                                        class="px-2.5 py-1 rounded-md font-medium transition {{ $royaltyFilter === 'eligible' ? 'bg-white dark:bg-gray-700 text-emerald-700 dark:text-emerald-400 shadow-xs font-bold' : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300' }}"
                                    >
                                        Eligible ({{ $this->scopedEligibleCount }})
                                    </button>
                                    <button
                                        type="button"
                                        wire:click="setRoyaltyFilter('ineligible')"
                                        class="px-2.5 py-1 rounded-md font-medium transition {{ $royaltyFilter === 'ineligible' ? 'bg-white dark:bg-gray-700 text-rose-700 dark:text-rose-400 shadow-xs font-bold' : 'text-gray-500 hover:text-gray-700 dark:hover:text-gray-300' }}"
                                    >
                                        Excluded ({{ $this->scopedExcludedCount }})
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Internal Scrollable Table --}}
                        <div class="overflow-x-auto overflow-y-auto {{ $viewMode === 'split' ? 'max-h-[580px]' : 'max-h-[720px]' }}">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-gray-50 dark:bg-gray-800/80 uppercase font-semibold text-gray-600 dark:text-gray-400 sticky top-0 z-10 border-b border-gray-200 dark:border-gray-700 backdrop-blur-xs">
                                    <tr>
                                        <th class="px-3.5 py-2.5">#</th>
                                        <th class="px-3.5 py-2.5">Department</th>
                                        <th class="px-3.5 py-2.5">Designation Title</th>
                                        <th class="px-3.5 py-2.5 text-right">Annual Budget</th>
                                        <th class="px-3.5 py-2.5 text-right">Monthly Max</th>
                                        <th class="px-3.5 py-2.5 text-right">Monthly Min</th>
                                        <th class="px-3.5 py-2.5 text-center">Royalty Bonus?</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                    @forelse($this->filteredDesignations as $index => $desig)
                                        <tr wire:key="desig-row-{{ $desig->id }}" class="hover:bg-gray-50/80 dark:hover:bg-gray-800/50 transition {{ $desig->is_royalty_eligible ? 'bg-emerald-50/25 dark:bg-emerald-950/15' : '' }}">
                                            <td class="px-3.5 py-2.5 text-gray-400 font-mono text-[11px]">{{ $index + 1 }}</td>
                                            <td class="px-3.5 py-2.5">
                                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 border border-gray-200/60 dark:border-gray-700/60">
                                                    {{ $desig->department?->name ?? 'General' }}
                                                </span>
                                            </td>
                                            <td class="px-3.5 py-2.5">
                                                <div class="font-bold text-gray-900 dark:text-white flex items-center gap-1.5">
                                                    <span>{{ $desig->name }}</span>
                                                    @if($desig->is_royalty_eligible)
                                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0"></span>
                                                    @endif
                                                </div>
                                            </td>
                                            <td class="px-3.5 py-2 text-right">
                                                <div class="relative inline-flex items-center justify-end w-36">
                                                    <span class="pointer-events-none absolute inset-y-0 left-0 z-10 pl-2.5 flex items-center text-gray-400 dark:text-gray-500 text-[10px] font-mono font-semibold select-none">Rs.</span>
                                                    <input
                                                        type="number"
                                                        step="1000"
                                                        min="0"
                                                        value="{{ $desig->royalty_annual_budget > 0 ? (float) $desig->royalty_annual_budget : '' }}"
                                                        placeholder="0.00"
                                                        wire:key="budget-input-{{ $desig->id }}"
                                                        wire:change="updateDesignationBudget({{ $desig->id }}, $event.target.value)"
                                                        x-on:keydown.enter.prevent="$el.blur()"
                                                        style="padding-left: 2.1rem !important; padding-right: 0.5rem !important;"
                                                        class="w-full text-xs !pl-8 !pr-2 py-1 text-right rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50/80 dark:bg-gray-800/80 text-gray-900 dark:text-white font-mono focus:bg-white dark:focus:bg-gray-800 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 outline-none transition"
                                                        title="Enter Annual Royalty Budget (Rs.) and press Enter or Tab to save"
                                                    />
                                                </div>
                                            </td>
                                            <td class="px-3.5 py-2.5 text-right font-mono font-semibold {{ $desig->royalty_monthly_max > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-gray-400' }}">
                                                {{ $desig->royalty_monthly_max > 0 ? 'Rs. ' . number_format($desig->royalty_monthly_max, 2) : '-' }}
                                            </td>
                                            <td class="px-3.5 py-2.5 text-right font-mono text-gray-500">
                                                {{ $desig->royalty_monthly_min > 0 ? 'Rs. ' . number_format($desig->royalty_monthly_min, 2) : '-' }}
                                            </td>
                                            <td class="px-3.5 py-2.5 text-center">
                                                {{-- Sleek iOS-style Toggle Button with live update --}}
                                                <button
                                                    type="button"
                                                    wire:click="toggleDesignation({{ $desig->id }})"
                                                    wire:loading.class="opacity-50"
                                                    wire:target="toggleDesignation({{ $desig->id }})"
                                                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold transition shadow-xs {{ $desig->is_royalty_eligible ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/80 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-700 hover:bg-emerald-200' : 'bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400 border border-gray-300 dark:border-gray-700 hover:bg-gray-200' }}"
                                                    title="Click to toggle {{ $desig->name }} royalty participation"
                                                >
                                                    @if($desig->is_royalty_eligible)
                                                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                                        <span>Eligible</span>
                                                    @else
                                                        <span class="w-2 h-2 rounded-full bg-gray-400"></span>
                                                        <span>Excluded</span>
                                                    @endif
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="px-4 py-12 text-center text-gray-400">
                                                <x-heroicon-o-magnifying-glass class="w-8 h-8 mx-auto mb-2 opacity-50" />
                                                <p class="font-medium text-xs">No designations matching your filter criteria.</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{-- Footer Summary --}}
                        <div class="p-3 bg-gray-50 dark:bg-gray-800/40 border-t border-gray-100 dark:border-gray-800 flex items-center justify-between text-xs text-gray-500">
                            <span>Showing {{ $this->filteredDesignations->count() }} of {{ $this->totalDesignationsCount }} roles</span>
                            <div class="flex items-center gap-3">
                                <span>Eligible: <strong class="text-emerald-600 dark:text-emerald-400">{{ $this->eligibleDesignationsCount }}</strong></span>
                                <span class="text-gray-300 dark:text-gray-700">|</span>
                                <span>Excluded: <strong class="text-rose-600 dark:text-rose-400">{{ $this->excludedDesignationsCount }}</strong></span>
                                <span class="hidden sm:inline text-gray-300 dark:text-gray-700">|</span>
                                <span class="hidden sm:inline">Pool Max: <strong class="text-amber-500 font-mono">Rs. {{ number_format($this->totalMonthlyMaxBudget, 2) }}</strong></span>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-filament-panels::page>
