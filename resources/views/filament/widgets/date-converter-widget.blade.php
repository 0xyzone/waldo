<x-filament-widgets::widget>
    <div x-data="{
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
                setTimeout(() => this.copied = false, 2000);
            }
         }"
         class="relative w-full rounded-3xl bg-white dark:bg-[#121826] border border-slate-200/80 dark:border-white/[0.07] p-6 sm:p-8 shadow-[0_2px_12px_rgba(15,23,42,0.03)] dark:shadow-[0_10px_30px_rgba(0,0,0,0.35)] transition-all duration-300 flex flex-col justify-between">

        <div class="space-y-6">
            <!-- Header Section -->
            <div class="flex items-center gap-4 pb-5 border-b border-slate-100 dark:border-white/[0.06]">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-tr from-orange-500 to-red-600 text-white shadow-md shadow-orange-500/20">
                    <x-filament::icon icon="heroicon-s-arrow-path" class="h-6 w-6 text-white" />
                </div>
                <div>
                    <h2 class="text-xl font-extrabold tracking-tight text-slate-900 dark:text-slate-100">
                        Date Converter
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-0.5">
                        Seamless bidirectional English (AD) and Nepali (BS) calendar conversion.
                    </p>
                </div>
            </div>

            <!-- Segmented Tab Switcher -->
            <div class="flex p-1 rounded-2xl bg-slate-100/80 dark:bg-slate-900/60 border border-slate-200/60 dark:border-white/[0.04]">
                <button 
                    type="button"
                    wire:click="setTab('ad_to_bs')"
                    class="flex-1 py-2 text-xs font-bold text-center rounded-xl transition-all duration-200 cursor-pointer {{ $activeTab === 'ad_to_bs' ? 'bg-gradient-to-r from-orange-500 to-red-600 text-white shadow-sm shadow-orange-500/20' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100' }}"
                >
                    AD to BS
                </button>
                <button 
                    type="button"
                    wire:click="setTab('bs_to_ad')"
                    class="flex-1 py-2 text-xs font-bold text-center rounded-xl transition-all duration-200 cursor-pointer {{ $activeTab === 'bs_to_ad' ? 'bg-gradient-to-r from-orange-500 to-red-600 text-white shadow-sm shadow-orange-500/20' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100' }}"
                >
                    BS to AD
                </button>
            </div>

            <!-- Form Inputs -->
            <div class="space-y-4">
                @if ($activeTab === 'ad_to_bs')
                    <div class="space-y-1.5">
                        <label for="adDate" class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                            English Date (AD)
                        </label>
                        <div class="relative w-full">
                            <x-filament::icon icon="heroicon-m-calendar" class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-orange-500" />
                            <input 
                                type="date" 
                                id="adDate" 
                                wire:model.live="adDate" 
                                min="1944-01-01" 
                                max="2033-12-31"
                                class="w-full rounded-xl border border-slate-200 dark:border-white/[0.08] bg-slate-50/70 dark:bg-slate-900/60 pl-10 pr-3 py-2.5 text-sm font-semibold text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition-all" 
                            />
                        </div>
                    </div>
                @else
                    <div class="grid grid-cols-3 gap-2.5">
                        <!-- BS Year -->
                        <div class="space-y-1.5">
                            <label for="bsYear" class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                Year (BS)
                            </label>
                            <select 
                                id="bsYear" 
                                wire:model.live="bsYear" 
                                class="w-full rounded-xl border border-slate-200 dark:border-white/[0.08] bg-slate-50/70 dark:bg-slate-900/60 px-3 py-2.5 text-xs font-semibold text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition-all cursor-pointer"
                            >
                                @foreach ($bsYears as $year)
                                    <option value="{{ $year }}" class="dark:bg-slate-900">{{ $year }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- BS Month -->
                        <div class="space-y-1.5">
                            <label for="bsMonth" class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                Month
                            </label>
                            <select 
                                id="bsMonth" 
                                wire:model.live="bsMonth" 
                                class="w-full rounded-xl border border-slate-200 dark:border-white/[0.08] bg-slate-50/70 dark:bg-slate-900/60 px-3 py-2.5 text-xs font-semibold text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition-all cursor-pointer"
                            >
                                <option value="1" class="dark:bg-slate-900">Baishakh</option>
                                <option value="2" class="dark:bg-slate-900">Jestha</option>
                                <option value="3" class="dark:bg-slate-900">Ashadh</option>
                                <option value="4" class="dark:bg-slate-900">Shrawan</option>
                                <option value="5" class="dark:bg-slate-900">Bhadra</option>
                                <option value="6" class="dark:bg-slate-900">Ashwin</option>
                                <option value="7" class="dark:bg-slate-900">Kartik</option>
                                <option value="8" class="dark:bg-slate-900">Mangsir</option>
                                <option value="9" class="dark:bg-slate-900">Paush</option>
                                <option value="10" class="dark:bg-slate-900">Magh</option>
                                <option value="11" class="dark:bg-slate-900">Falgun</option>
                                <option value="12" class="dark:bg-slate-900">Chaitra</option>
                            </select>
                        </div>

                        <!-- BS Day -->
                        <div class="space-y-1.5">
                            <label for="bsDay" class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                                Day
                            </label>
                            <select 
                                id="bsDay" 
                                wire:model.live="bsDay" 
                                class="w-full rounded-xl border border-slate-200 dark:border-white/[0.08] bg-slate-50/70 dark:bg-slate-900/60 px-3 py-2.5 text-xs font-semibold text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition-all cursor-pointer"
                            >
                                @foreach ($bsDays as $day)
                                    <option value="{{ $day }}" class="dark:bg-slate-900">{{ $day }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Result Display Card -->
            <div class="p-5 rounded-2xl bg-slate-50/80 dark:bg-slate-900/50 border border-slate-200/70 dark:border-white/[0.06] space-y-2">
                @if ($activeTab === 'ad_to_bs')
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Nepali Date (BS)</span>
                        <button type="button" @click="copyText('{{ $convertedBsDateNp ?: $convertedBsDate }}')"
                                class="inline-flex items-center gap-1 text-[11px] font-bold text-orange-600 dark:text-orange-400 hover:underline cursor-pointer">
                            <x-filament::icon icon="heroicon-m-clipboard-document" class="h-3.5 w-3.5" />
                            <span x-text="copied ? 'Copied!' : 'Copy'"></span>
                        </button>
                    </div>

                    @if ($convertedBsDateNp)
                        <div class="text-2xl font-black text-orange-600 dark:text-orange-400 tracking-tight leading-tight">
                            {{ $convertedBsDateNp }}
                        </div>
                        <div class="text-xs font-medium text-slate-500 dark:text-slate-400 flex items-center gap-2">
                            <span class="font-mono">{{ $convertedBsDate }}</span>
                            <span class="h-1.5 w-1.5 rounded-full bg-slate-300 dark:bg-slate-700"></span>
                            <span>{{ $convertedBsWeekday }}</span>
                        </div>
                    @else
                        <div class="text-sm font-semibold text-rose-500">
                            {{ $convertedBsDate ?? 'Enter a valid date' }}
                        </div>
                    @endif
                @else
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">English Date (AD)</span>
                        <button type="button" @click="copyText('{{ $convertedAdDate }}')"
                                class="inline-flex items-center gap-1 text-[11px] font-bold text-orange-600 dark:text-orange-400 hover:underline cursor-pointer">
                            <x-filament::icon icon="heroicon-m-clipboard-document" class="h-3.5 w-3.5" />
                            <span x-text="copied ? 'Copied!' : 'Copy'"></span>
                        </button>
                    </div>

                    @if ($convertedAdDate && $convertedAdDate !== 'Invalid BS Date' && $convertedAdDate !== 'Conversion failed')
                        <div class="text-2xl font-black text-orange-600 dark:text-orange-400 tracking-tight leading-tight">
                            {{ Carbon\Carbon::parse($convertedAdDate)->format('F d, Y') }}
                        </div>
                        <div class="text-xs font-medium text-slate-500 dark:text-slate-400 flex items-center gap-2">
                            <span class="font-mono">{{ $convertedAdDate }}</span>
                            <span class="h-1.5 w-1.5 rounded-full bg-slate-300 dark:bg-slate-700"></span>
                            <span>{{ $convertedAdWeekday }}</span>
                        </div>
                    @else
                        <div class="text-sm font-semibold text-rose-500">
                            {{ $convertedAdDate ?? 'Enter a valid date' }}
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</x-filament-widgets::widget>
