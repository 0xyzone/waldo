<x-filament-widgets::widget>
    <div class="h-full relative overflow-hidden rounded-2xl bg-white dark:bg-gray-900 shadow-[0_8px_30px_rgb(0,0,0,0.04)] dark:shadow-[0_15px_40px_rgba(0,0,0,0.4)] border border-gray-200/50 dark:border-white/10 transition-all duration-500 hover:border-amber-500/30 dark:hover:border-amber-500/20 hover:shadow-[0_20px_50px_rgba(249,115,22,0.08)] group w-full flex flex-col">
        
        <!-- Glowing backdrop elements -->
        <div class="absolute -right-24 -top-24 h-64 w-64 rounded-full bg-gradient-to-br from-amber-500/10 to-orange-500/10 opacity-30 dark:opacity-20 blur-3xl pointer-events-none transition-all duration-700 group-hover:scale-110"></div>
        <div class="absolute -left-20 -bottom-20 h-64 w-64 rounded-full bg-gradient-to-tr from-indigo-500/5 to-purple-500/5 opacity-20 dark:opacity-10 blur-3xl pointer-events-none"></div>

        <div class="p-8 sm:p-10 relative z-10 space-y-6">
            <!-- Header Section -->
            <div class="flex items-center gap-4 pb-5 border-b border-gray-100 dark:border-white/5">
                <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-tr from-amber-500 to-orange-600 text-white shadow-[0_8px_20px_rgba(249,115,22,0.2)] transition-transform duration-500 group-hover:scale-105">
                    <x-filament::icon icon="heroicon-s-arrow-path" class="h-7 w-7 text-white transition-transform duration-700 group-hover:rotate-180" />
                </div>
                <div>
                    <h2 class="text-xl font-bold tracking-tight text-gray-900 dark:text-white">
                        Date Converter
                    </h2>
                    <p class="mt-1.5 text-sm text-gray-500 dark:text-gray-400 font-normal leading-relaxed">
                        Convert dates between English (AD) and Nepali (BS) calendars.
                    </p>
                </div>
            </div>

            <!-- Tab Switcher -->
            <div class="flex p-1 rounded-xl bg-gray-100 dark:bg-gray-800/80 border border-gray-200/40 dark:border-white/5">
                <button 
                    type="button"
                    wire:click="setTab('ad_to_bs')"
                    class="flex-1 py-2 text-xs font-bold text-center rounded-lg transition-all duration-300 {{ $activeTab === 'ad_to_bs' ? 'bg-gradient-to-r from-orange-500 to-amber-500 text-white shadow-xs' : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-200/30 dark:hover:bg-gray-700/30' }}"
                >
                    AD to BS
                </button>
                <button 
                    type="button"
                    wire:click="setTab('bs_to_ad')"
                    class="flex-1 py-2 text-xs font-bold text-center rounded-lg transition-all duration-300 {{ $activeTab === 'bs_to_ad' ? 'bg-gradient-to-r from-orange-500 to-amber-500 text-white shadow-xs' : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-200/30 dark:hover:bg-gray-700/30' }}"
                >
                    BS to AD
                </button>
            </div>

            <!-- Form Inputs -->
            <div class="space-y-4">
                @if ($activeTab === 'ad_to_bs')
                    <!-- AD Date Picker -->
                    <div class="space-y-2">
                        <label for="adDate" class="block text-[10px] font-bold uppercase tracking-wider text-gray-450 dark:text-gray-500">
                            English Date (AD)
                        </label>
                        <div class="relative w-full">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400 dark:text-gray-500">
                                <x-filament::icon icon="heroicon-m-calendar" class="h-4 w-4 text-amber-500" />
                            </div>
                            <input 
                                type="date" 
                                id="adDate" 
                                wire:model.live="adDate" 
                                min="1944-01-01" 
                                max="2033-12-31"
                                class="w-full rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50/50 dark:bg-gray-800/50 pl-9 pr-3 py-2.5 text-sm font-medium text-gray-950 dark:text-white shadow-inner focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition-colors" 
                            />
                        </div>
                    </div>
                @else
                    <!-- BS Date Inputs -->
                    <div class="grid grid-cols-3 gap-3">
                        <!-- BS Year -->
                        <div class="space-y-2">
                            <label for="bsYear" class="block text-[10px] font-bold uppercase tracking-wider text-gray-450 dark:text-gray-500">
                                Year (BS)
                            </label>
                            <div class="relative">
                                <select 
                                    id="bsYear" 
                                    wire:model.live="bsYear" 
                                    class="w-full appearance-none rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50/50 dark:bg-gray-800/50 px-3 py-2.5 text-sm font-medium text-gray-950 dark:text-white shadow-inner focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition-colors cursor-pointer"
                                >
                                    @foreach ($bsYears as $year)
                                        <option value="{{ $year }}" class="dark:bg-gray-900 text-gray-950 dark:text-white">{{ $year }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- BS Month -->
                        <div class="space-y-2">
                            <label for="bsMonth" class="block text-[10px] font-bold uppercase tracking-wider text-gray-450 dark:text-gray-500">
                                Month (BS)
                            </label>
                            <div class="relative">
                                <select 
                                    id="bsMonth" 
                                    wire:model.live="bsMonth" 
                                    class="w-full appearance-none rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50/50 dark:bg-gray-800/50 px-3 py-2.5 text-sm font-medium text-gray-950 dark:text-white shadow-inner focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition-colors cursor-pointer"
                                >
                                    <option value="1" class="dark:bg-gray-900 text-gray-950 dark:text-white">Baishakh (वैशाख)</option>
                                    <option value="2" class="dark:bg-gray-900 text-gray-950 dark:text-white">Jestha (जेठ)</option>
                                    <option value="3" class="dark:bg-gray-900 text-gray-950 dark:text-white">Ashadh (असार)</option>
                                    <option value="4" class="dark:bg-gray-900 text-gray-950 dark:text-white">Shrawan (साउन)</option>
                                    <option value="5" class="dark:bg-gray-900 text-gray-950 dark:text-white">Bhadra (भदौ)</option>
                                    <option value="6" class="dark:bg-gray-900 text-gray-950 dark:text-white">Ashwin (असोज)</option>
                                    <option value="7" class="dark:bg-gray-900 text-gray-950 dark:text-white">Kartik (कात्तिक)</option>
                                    <option value="8" class="dark:bg-gray-900 text-gray-950 dark:text-white">Mangsir (मंसिर)</option>
                                    <option value="9" class="dark:bg-gray-900 text-gray-950 dark:text-white">Paush (पुष)</option>
                                    <option value="10" class="dark:bg-gray-900 text-gray-950 dark:text-white">Magh (माघ)</option>
                                    <option value="11" class="dark:bg-gray-900 text-gray-950 dark:text-white">Falgun (फागुन)</option>
                                    <option value="12" class="dark:bg-gray-900 text-gray-950 dark:text-white">Chaitra (चैत)</option>
                                </select>
                            </div>
                        </div>

                        <!-- BS Day -->
                        <div class="space-y-2">
                            <label for="bsDay" class="block text-[10px] font-bold uppercase tracking-wider text-gray-450 dark:text-gray-500">
                                Day (BS)
                            </label>
                            <div class="relative">
                                <select 
                                    id="bsDay" 
                                    wire:model.live="bsDay" 
                                    class="w-full appearance-none rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50/50 dark:bg-gray-800/50 px-3 py-2.5 text-sm font-medium text-gray-950 dark:text-white shadow-inner focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition-colors cursor-pointer"
                                >
                                    @foreach ($bsDays as $day)
                                        <option value="{{ $day }}" class="dark:bg-gray-900 text-gray-950 dark:text-white">{{ $day }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Result Cards Section -->
            <div class="bg-gradient-to-br from-amber-500/[0.03] to-orange-500/[0.03] dark:from-amber-500/[0.05] dark:to-orange-500/[0.05] border-l-4 border-l-amber-500 border-y border-r border-gray-200/50 dark:border-white/5 rounded-r-2xl rounded-l-md p-6 space-y-3 shadow-xs">
                @if ($activeTab === 'ad_to_bs')
                    <div>
                        <span class="block text-[9px] font-bold uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-1.5">
                            Converted Nepali Date (BS)
                        </span>
                        
                        @if ($convertedBsDateNp)
                            <div class="space-y-2">
                                <div class="text-2xl font-black text-amber-500 dark:text-amber-400 tracking-tight leading-none">
                                    {{ $convertedBsDateNp }}
                                </div>
                                <div class="text-xs font-medium text-gray-500 dark:text-gray-400 flex items-center gap-2">
                                    <span>{{ $convertedBsDate }}</span>
                                    <span class="h-1.5 w-1.5 rounded-full bg-gray-300 dark:bg-gray-700"></span>
                                    <span>{{ $convertedBsWeekday }}</span>
                                </div>
                            </div>
                        @else
                            <div class="text-sm font-medium text-rose-500 dark:text-rose-400">
                                {{ $convertedBsDate ?? 'Enter a valid date' }}
                            </div>
                        @endif
                    </div>
                @else
                    <div>
                        <span class="block text-[9px] font-bold uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-1.5">
                            Converted English Date (AD)
                        </span>

                        @if ($convertedAdDate && $convertedAdDate !== 'Invalid BS Date' && $convertedAdDate !== 'Conversion failed')
                            <div class="space-y-2">
                                <div class="text-2xl font-black text-amber-500 dark:text-amber-400 tracking-tight leading-none">
                                    {{ Carbon\Carbon::parse($convertedAdDate)->format('F d, Y') }}
                                </div>
                                <div class="text-xs font-medium text-gray-500 dark:text-gray-400 flex items-center gap-2">
                                    <span>{{ $convertedAdDate }}</span>
                                    <span class="h-1.5 w-1.5 rounded-full bg-gray-300 dark:bg-gray-700"></span>
                                    <span>{{ $convertedAdWeekday }}</span>
                                </div>
                            </div>
                        @else
                            <div class="text-sm font-medium text-rose-500 dark:text-rose-400">
                                {{ $convertedAdDate ?? 'Enter a valid date' }}
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-filament-widgets::widget>
