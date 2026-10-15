<x-filament-widgets::widget>
    <!-- Load Google Fonts for luxury birthday wishing card typography -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@500;700;900&family=Cormorant+Garamond:ital,wght@0,600;0,700;1,400&family=Playfair+Display:ital,wght@0,600;0,800;1,400&family=Plus+Jakarta+Sans:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

    <div 
        x-data="{ 
            isModalOpen: false, 
            isCardModalOpen: false,
            isGenerating: false,
            previewScale: 0.3,
            updateScale() {
                this.$nextTick(() => {
                    const wrapper = this.$refs.previewWrapper;
                    if (wrapper) {
                        this.previewScale = wrapper.clientWidth / 2708;
                    }
                });
            },
            async ensureExporter() {
                if (typeof window.html2canvas !== 'undefined' || typeof window.htmlToImage !== 'undefined') {
                    return true;
                }

                return new Promise((resolve) => {
                    const scriptsToLoad = [
                        'https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js',
                        'https://cdnjs.cloudflare.com/ajax/libs/html-to-image/1.11.11/html-to-image.min.js'
                    ];

                    let loadedCount = 0;
                    scriptsToLoad.forEach(src => {
                        const script = document.createElement('script');
                        script.src = src;
                        script.onload = () => {
                            loadedCount++;
                            if (loadedCount >= 1) resolve(true);
                        };
                        script.onerror = () => {
                            loadedCount++;
                            if (loadedCount >= scriptsToLoad.length) resolve(false);
                        };
                        document.head.appendChild(script);
                    });
                });
            },
            async downloadCardJpg() {
                this.isGenerating = true;
                
                await this.ensureExporter();

                const cardEl = document.getElementById('birthday-wishing-card-canvas');
                if (!cardEl) {
                    alert('Target card element missing.');
                    this.isGenerating = false;
                    return;
                }

                const monthName = '{{ $this->month_name }}' || 'Month';
                const year = '{{ $this->year }}' || 'Year';
                const fileName = `Birthday_Wishing_Card_${monthName}_${year}.jpg`;

                // Temporarily bring target card element to 100% opacity with fixed 2708x1492 dimensions
                cardEl.style.position = 'fixed';
                cardEl.style.left = '0';
                cardEl.style.top = '0';
                cardEl.style.width = '2708px';
                cardEl.style.height = '1492px';
                cardEl.style.minWidth = '2708px';
                cardEl.style.minHeight = '1492px';
                cardEl.style.maxWidth = '2708px';
                cardEl.style.maxHeight = '1492px';
                cardEl.style.boxSizing = 'border-box';
                cardEl.style.opacity = '1';
                cardEl.style.zIndex = '999999';
                cardEl.style.pointerEvents = 'none';

                if (document.fonts && document.fonts.ready) {
                    try { await document.fonts.ready; } catch(e) {}
                }
                await new Promise(r => setTimeout(r, 200));

                try {
                    let dataUrl = null;

                    if (typeof window.html2canvas !== 'undefined') {
                        try {
                            const canvas = await window.html2canvas(cardEl, {
                                width: 2708,
                                height: 1492,
                                scale: 1,
                                useCORS: true,
                                allowTaint: true,
                                backgroundColor: '#0284c7',
                                logging: false
                            });
                            dataUrl = canvas.toDataURL('image/jpeg', 0.95);
                        } catch (e) {
                            console.warn('html2canvas failed, attempting htmlToImage fallback', e);
                        }
                    }

                    if (!dataUrl && typeof window.htmlToImage !== 'undefined') {
                        dataUrl = await window.htmlToImage.toJpeg(cardEl, {
                            width: 2708,
                            height: 1492,
                            pixelRatio: 1,
                            quality: 0.95
                        });
                    }

                    if (dataUrl) {
                        const link = document.createElement('a');
                        link.download = fileName;
                        link.href = dataUrl;
                        link.target = '_self';
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                    } else {
                        alert('Could not render image. Please try again.');
                    }
                } catch (err) {
                    console.error('Export error:', err);
                    alert('Failed to generate JPG image: ' + (err.message || err));
                } finally {
                    cardEl.style.position = 'absolute';
                    cardEl.style.left = '-9999px';
                    cardEl.style.top = '0';
                    cardEl.style.width = '2708px';
                    cardEl.style.height = '1492px';
                    cardEl.style.minWidth = '';
                    cardEl.style.maxWidth = '';
                    cardEl.style.opacity = '0';
                    cardEl.style.zIndex = '-9999';
                    this.isGenerating = false;
                }
            }
        }" 
        x-init="
            $watch('isCardModalOpen', value => { 
                if (value) {
                    updateScale();
                    ensureExporter();
                } 
            }); 
            window.addEventListener('resize', () => updateScale());
            ensureExporter();
        "
        class="relative w-full rounded-3xl bg-white dark:bg-[#121826] border border-slate-200/80 dark:border-white/[0.07] p-6 sm:p-8 shadow-[0_2px_12px_rgba(15,23,42,0.03)] dark:shadow-[0_10px_30px_rgba(0,0,0,0.35)] transition-all duration-300 flex flex-col justify-between"
    >
        <div class="space-y-6">
            <!-- Header Section -->
            <div class="flex items-center gap-4 pb-5 border-b border-slate-100 dark:border-white/[0.06]">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-tr from-orange-500 to-red-600 text-white shadow-md shadow-orange-500/20">
                    <x-filament::icon icon="heroicon-s-cake" class="h-6 w-6 text-white" />
                </div>
                <div>
                    <h2 class="text-xl font-extrabold tracking-tight text-slate-900 dark:text-slate-100">
                        Birthdays & Cards
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 font-medium mt-0.5">
                        Track upcoming celebrations and generate 2708x1492 high-res wishing cards.
                    </p>
                </div>
            </div>

            <!-- Form Section -->
            <form target="_blank" action="{{ url('/reports/birthdays') }}" method="GET" class="flex flex-col sm:flex-row items-end gap-3">
                <!-- Month Selector -->
                <div class="space-y-1.5 flex-1 w-full">
                    <label for="month" class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        Month
                    </label>
                    <div class="relative w-full">
                        <x-filament::icon icon="heroicon-m-calendar" class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-orange-500" />
                        <select name="month" id="month" wire:model.live="month" class="w-full rounded-xl border border-slate-200 dark:border-white/[0.08] bg-slate-50/70 dark:bg-slate-900/60 pl-10 pr-8 py-2.5 text-xs font-semibold text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition-all cursor-pointer">
                            <option value="1">January</option>
                            <option value="2">February</option>
                            <option value="3">March</option>
                            <option value="4">April</option>
                            <option value="5">May</option>
                            <option value="6">June</option>
                            <option value="7">July</option>
                            <option value="8">August</option>
                            <option value="9">September</option>
                            <option value="10">October</option>
                            <option value="11">November</option>
                            <option value="12">December</option>
                        </select>
                    </div>
                </div>

                <!-- Year Selector -->
                <div class="space-y-1.5 w-full sm:w-28">
                    <label for="year" class="block text-[11px] font-bold uppercase tracking-wider text-slate-500 dark:text-slate-400">
                        Year
                    </label>
                    <input type="number" name="year" id="year" wire:model.live="year" min="1900" max="2100" placeholder="{{ date('Y') }}" class="w-full rounded-xl border border-slate-200 dark:border-white/[0.08] bg-slate-50/70 dark:bg-slate-900/60 px-3 py-2.5 text-xs font-semibold text-slate-900 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 transition-all text-center" />
                </div>

                <!-- Actions -->
                <div class="w-full sm:w-auto flex items-center gap-2">
                    <button 
                        type="submit" 
                        class="w-full sm:w-auto font-bold tracking-wide text-white bg-gradient-to-r from-orange-500 to-red-600 hover:from-orange-600 hover:to-red-700 shadow-md shadow-orange-500/20 transition-all active:scale-95 rounded-xl px-4 py-2.5 text-xs flex items-center justify-center gap-1.5 whitespace-nowrap cursor-pointer"
                    >
                        <x-filament::icon icon="heroicon-m-printer" class="h-4 w-4" />
                        <span>Print PDF</span>
                    </button>

                    <button 
                        type="button" 
                        @click="isCardModalOpen = true"
                        class="w-full sm:w-auto font-bold tracking-wide text-slate-800 dark:text-slate-100 bg-slate-100/90 dark:bg-slate-800/80 hover:bg-orange-50 dark:hover:bg-orange-950/30 hover:text-orange-600 dark:hover:text-orange-400 border border-slate-200/80 dark:border-white/[0.08] transition-all active:scale-95 rounded-xl px-4 py-2.5 text-xs flex items-center justify-center gap-1.5 whitespace-nowrap cursor-pointer"
                    >
                        <x-filament::icon icon="heroicon-s-sparkles" class="h-4 w-4 text-orange-500" />
                        <span>Card</span>
                    </button>
                </div>
            </form>

            <!-- Upcoming Birthdays List -->
            <div class="pt-4 border-t border-slate-100 dark:border-white/[0.06] space-y-3">
                <div class="flex items-center justify-between">
                    <h3 class="text-[10px] font-bold uppercase tracking-wider text-slate-400">
                        {{ $this->month_name }} Celebrations
                    </h3>
                    <div class="flex items-center gap-2">
                        <span class="inline-flex items-center rounded-full bg-orange-500/10 text-orange-600 dark:text-orange-400 px-2.5 py-0.5 text-xs font-bold border border-orange-500/20">
                            {{ $this->birthdays->count() }}
                        </span>
                        <button 
                            type="button" 
                            @click="isModalOpen = true"
                            class="p-1 rounded-lg text-slate-400 hover:text-orange-500 transition-colors focus:outline-none cursor-pointer"
                            title="Expand List"
                        >
                            <x-filament::icon icon="heroicon-m-arrows-pointing-out" class="h-4 w-4" />
                        </button>
                    </div>
                </div>

                @if ($this->birthdays->isEmpty())
                    <div class="text-xs text-slate-400 dark:text-slate-500 py-3 italic text-center">
                        No birthdays found celebrating this month.
                    </div>
                @else
                    <div class="grid gap-2 max-h-56 overflow-y-auto pr-1">
                        @foreach ($this->birthdays as $employee)
                            <div class="flex items-center justify-between p-2.5 bg-slate-50/60 dark:bg-slate-900/40 border border-slate-200/60 dark:border-white/[0.04] rounded-xl transition-all hover:border-orange-500/30">
                                <div class="flex items-center gap-2.5">
                                    <div class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-orange-500/10 text-orange-600 dark:text-orange-400 font-bold text-xs">
                                        {{ $loop->iteration }}
                                    </div>
                                    <div class="truncate max-w-[170px]">
                                        <div class="text-xs font-bold text-slate-900 dark:text-slate-100 truncate">
                                            {{ $employee->name }}
                                        </div>
                                        <div class="text-[11px] text-slate-500 dark:text-slate-400 truncate">
                                            {{ $employee->designation?->name }} • {{ $employee->department?->name }}
                                        </div>
                                    </div>
                                </div>
                                <div class="text-[11px] font-mono font-bold text-orange-600 dark:text-orange-400 bg-orange-500/10 px-2 py-0.5 rounded-lg shrink-0">
                                    {{ $employee->dob_ad ? $employee->dob_ad->format('M d') : '' }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- 1. Full List Modal -->
        <template x-teleport="body">
            <div 
                x-show="isModalOpen" 
                class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-6"
                style="display: none;"
                x-cloak
            >
                <!-- Backdrop -->
                <div 
                    x-show="isModalOpen"
                    x-transition:enter="ease-out duration-250"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    @click="isModalOpen = false"
                    class="fixed inset-0 bg-slate-950/70 backdrop-blur-md transition-opacity"
                ></div>

                <!-- Modal Content -->
                <div 
                    x-show="isModalOpen"
                    x-transition:enter="transition ease-out duration-300 transform"
                    x-transition:enter-start="opacity-0 scale-95 translate-y-3"
                    x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-200 transform"
                    x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                    x-transition:leave-end="opacity-0 scale-95 translate-y-3"
                    class="relative w-full max-w-2xl rounded-3xl bg-white dark:bg-[#121826] border border-slate-200/80 dark:border-white/[0.08] text-slate-900 dark:text-slate-100 shadow-2xl z-10 flex flex-col max-h-[85vh] overflow-hidden"
                >
                    <!-- Modal Header -->
                    <div class="px-6 py-5 border-b border-slate-100 dark:border-white/[0.06] flex items-center justify-between bg-slate-50/50 dark:bg-slate-900/30">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-tr from-orange-500 to-red-600 text-white shadow-sm">
                                <x-filament::icon icon="heroicon-s-cake" class="h-5 w-5 text-white" />
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-slate-900 dark:text-slate-50">
                                    Birthdays in {{ $this->month_name }} {{ $year }}
                                </h3>
                                <p class="text-xs text-slate-500 dark:text-slate-400">
                                    {{ $this->birthdays->count() }} active staff members celebrating.
                                </p>
                            </div>
                        </div>
                        
                        <button 
                            type="button" 
                            @click="isModalOpen = false"
                            class="p-2 rounded-xl text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 transition-all cursor-pointer"
                        >
                            <x-filament::icon icon="heroicon-m-x-mark" class="h-5 w-5" />
                        </button>
                    </div>

                    <!-- Modal Body -->
                    <div class="p-6 overflow-y-auto flex-1 space-y-2.5">
                        @foreach ($this->birthdays as $employee)
                            <div class="flex items-center justify-between p-3.5 bg-slate-50/60 dark:bg-slate-900/40 border border-slate-200/60 dark:border-white/[0.04] rounded-xl hover:border-orange-500/30 transition-all">
                                <div class="flex items-center gap-3.5">
                                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-orange-500/10 text-orange-600 dark:text-orange-400 font-bold text-xs">
                                        {{ $loop->iteration }}
                                    </div>
                                    <div>
                                        <div class="text-xs font-bold text-slate-900 dark:text-slate-100">
                                            {{ $employee->name }}
                                        </div>
                                        <div class="text-[11px] text-slate-500 dark:text-slate-400">
                                            {{ $employee->designation?->name }} • {{ $employee->department?->name }}
                                        </div>
                                    </div>
                                </div>
                                <div class="text-xs font-mono font-bold text-orange-600 dark:text-orange-400 bg-orange-500/10 px-2.5 py-1 rounded-lg">
                                    {{ $employee->dob_ad ? $employee->dob_ad->format('M d') : '' }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </template>

        <!-- 2. Wishing Card Preview Modal (2708px x 1492px) -->
        <template x-teleport="body">
            <div 
                x-show="isCardModalOpen" 
                class="fixed inset-0 z-50 flex items-center justify-center p-3 sm:p-6"
                style="display: none;"
                x-cloak
            >
                <!-- Backdrop -->
                <div 
                    x-show="isCardModalOpen"
                    x-transition:enter="ease-out duration-250"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    @click="isCardModalOpen = false"
                    class="fixed inset-0 bg-slate-950/80 backdrop-blur-md transition-opacity"
                ></div>

                <!-- Modal Dialog -->
                <div 
                    x-show="isCardModalOpen"
                    x-transition:enter="transition ease-out duration-300 transform"
                    x-transition:enter-start="opacity-0 scale-95 translate-y-3"
                    x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-200 transform"
                    x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                    x-transition:leave-end="opacity-0 scale-95 translate-y-3"
                    class="relative w-full max-w-6xl rounded-3xl bg-[#090d16] border border-white/[0.08] shadow-2xl z-10 flex flex-col max-h-[92vh] overflow-hidden"
                >
                    <!-- Top Bar -->
                    <div class="px-6 py-4 border-b border-white/[0.06] flex items-center justify-between bg-[#121826]">
                        <div class="flex items-center gap-3">
                            <span class="w-1.5 h-6 rounded-full bg-gradient-to-b from-orange-500 to-red-600"></span>
                            <h3 class="text-sm font-bold text-white">
                                Wishing Card Canvas (2708 × 1492 Ultra-HD)
                            </h3>
                        </div>

                        <div class="flex items-center gap-3">
                            <button 
                                type="button" 
                                @click="downloadCardJpg()"
                                :disabled="isGenerating"
                                class="font-bold text-white bg-gradient-to-r from-orange-500 to-red-600 hover:from-orange-600 hover:to-red-700 shadow-md shadow-orange-500/20 active:scale-95 disabled:opacity-50 rounded-xl px-4 py-2 text-xs flex items-center gap-1.5 cursor-pointer"
                            >
                                <template x-if="!isGenerating">
                                    <span class="flex items-center gap-1.5">
                                        <x-filament::icon icon="heroicon-m-arrow-down-tray" class="h-4 w-4" />
                                        Download JPG
                                    </span>
                                </template>
                                <template x-if="isGenerating">
                                    <span class="flex items-center gap-1.5">
                                        <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        Generating...
                                    </span>
                                </template>
                            </button>

                            <button 
                                type="button" 
                                @click="isCardModalOpen = false"
                                class="p-2 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800 transition-all cursor-pointer"
                            >
                                <x-filament::icon icon="heroicon-m-x-mark" class="h-5 w-5" />
                            </button>
                        </div>
                    </div>

                    <!-- Modal Body / Scaled Preview Wrapper -->
                    <div class="p-4 sm:p-6 overflow-hidden flex-1 bg-black flex items-center justify-center">
                        <div 
                            x-ref="previewWrapper" 
                            class="relative w-full overflow-hidden rounded-xl border border-white/[0.08] bg-black shadow-2xl flex items-center justify-center"
                            style="aspect-ratio: 2708 / 1492;"
                        >
                            <div 
                                style="width: 2708px; height: 1492px; transform-origin: top left; position: absolute; top: 0; left: 0;"
                                :style="'transform: scale(' + previewScale + ');'"
                            >
                                @include('filament.widgets.birthday-card-template', ['monthName' => $this->month_name, 'year' => $this->year, 'birthdays' => $this->birthdays])
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>

        <!-- Hidden Target Container for Native 2708px x 1492px JPG Rendering -->
        <div style="position: absolute; left: -9999px; top: 0; width: 2708px; height: 1492px; overflow: hidden; opacity: 0; pointer-events: none; z-index: -9999;" id="birthday-wishing-card-canvas">
            @include('filament.widgets.birthday-card-template', ['monthName' => $this->month_name, 'year' => $this->year, 'birthdays' => $this->birthdays])
        </div>
    </div>
</x-filament-widgets::widget>
