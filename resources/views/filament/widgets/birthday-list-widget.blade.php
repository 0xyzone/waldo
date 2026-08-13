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

                // Allow browser to render layout frame & wait for Google Fonts to be ready in browser memory
                if (document.fonts && document.fonts.ready) {
                    try { await document.fonts.ready; } catch(e) {}
                }
                await new Promise(r => setTimeout(r, 200));

                try {
                    let dataUrl = null;

                    // Prefer html2canvas for 100% exact font rendering matching live browser canvas
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
                            console.warn('html2canvas failed, falling back to htmlToImage:', e);
                        }
                    }

                    // Fallback to htmlToImage if html2canvas is unavailable
                    if (!dataUrl && typeof window.htmlToImage !== 'undefined') {
                        dataUrl = await window.htmlToImage.toJpeg(cardEl, {
                            width: 2708,
                            height: 1492,
                            quality: 0.95,
                            canvasWidth: 2708,
                            canvasHeight: 1492,
                            cacheBust: true,
                            style: {
                                transform: 'none',
                                opacity: '1',
                                width: '2708px',
                                height: '1492px',
                                minWidth: '2708px',
                                minHeight: '1492px'
                            }
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
        class="h-full relative overflow-hidden rounded-2xl bg-white dark:bg-gray-900 shadow-[0_8px_30px_rgb(0,0,0,0.04)] dark:shadow-[0_15px_40px_rgba(0,0,0,0.4)] border border-gray-200/50 dark:border-white/10 transition-all duration-500 hover:border-amber-500/30 dark:hover:border-amber-500/20 hover:shadow-[0_20px_50px_rgba(249,115,22,0.08)] group w-full flex flex-col"
    >
        
        <!-- Glowing backdrop elements -->
        <div class="absolute -right-24 -top-24 h-64 w-64 rounded-full bg-gradient-to-br from-amber-500/10 to-orange-500/10 opacity-30 dark:opacity-20 blur-3xl pointer-events-none transition-all duration-700 group-hover:scale-110"></div>
        <div class="absolute -left-20 -bottom-20 h-64 w-64 rounded-full bg-gradient-to-tr from-indigo-500/5 to-purple-500/5 opacity-20 dark:opacity-10 blur-3xl pointer-events-none"></div>

        <div class="p-8 sm:p-10 relative z-10 space-y-8">
            <!-- Header Section -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6 pb-6 border-b border-gray-100 dark:border-white/5">
                <div class="flex items-center gap-4">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-tr from-amber-500 to-orange-600 text-white shadow-[0_8px_20px_rgba(249,115,22,0.2)] transition-transform duration-500 group-hover:scale-105">
                        <x-filament::icon icon="heroicon-s-cake" class="h-7 w-7 text-white" />
                    </div>
                    <div>
                        <h2 class="text-xl font-bold tracking-tight text-gray-900 dark:text-white">
                            Birthday List Generator
                        </h2>
                        <p class="mt-1.5 text-sm text-gray-500 dark:text-gray-400 font-normal leading-relaxed">
                            Generate printable PDF reports or luxurious 2708x1492 JPG wishing cards.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Form Section -->
            <form target="_blank" action="{{ url('/reports/birthdays') }}" method="GET" class="flex flex-col sm:flex-row items-end gap-4">
                
                <!-- Month Selector -->
                <div class="space-y-2 flex-1 w-full">
                    <label for="month" class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500">
                        Select Month
                    </label>
                    <div class="relative w-full">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400 dark:text-gray-500">
                            <x-filament::icon icon="heroicon-m-calendar" class="h-4 w-4 text-amber-500" />
                        </div>
                        <select name="month" id="month" wire:model.live="month" class="w-full appearance-none rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50/50 dark:bg-gray-800/50 pl-9 pr-8 py-2.5 text-sm font-medium text-gray-950 dark:text-white shadow-inner focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition-all cursor-pointer">
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
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 dark:text-gray-500">
                            <x-filament::icon icon="heroicon-m-chevron-down" class="h-4 w-4" />
                        </div>
                    </div>
                </div>

                <!-- Year Selector -->
                <div class="space-y-2 w-full sm:w-32">
                    <label for="year" class="block text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500">
                        Select Year
                    </label>
                    <div class="relative w-full">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400 dark:text-gray-500">
                            <x-filament::icon icon="heroicon-m-clock" class="h-4 w-4 text-pink-500" />
                        </div>
                        <input type="number" name="year" id="year" wire:model.live="year" min="1900" max="2100" placeholder="{{ date('Y') }}" class="w-full rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50/50 dark:bg-gray-800/50 pl-9 pr-3 py-2.5 text-sm font-medium text-gray-950 dark:text-white shadow-inner focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition-colors [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none" />
                    </div>
                </div>

                <!-- Action Button Section -->
                <div class="w-full sm:w-auto flex items-center gap-2">
                    <!-- Print Button -->
                    <button 
                        type="submit" 
                        class="w-full sm:w-auto font-bold tracking-wide text-white bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 shadow-[0_4px_15px_rgba(249,115,22,0.2)] hover:shadow-[0_6px_20px_rgba(249,115,22,0.3)] transition-all duration-300 transform hover:-translate-y-0.5 active:translate-y-0 focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:ring-offset-2 dark:focus:ring-offset-gray-900 rounded-xl px-4 py-2.5 text-sm flex items-center justify-center gap-2 whitespace-nowrap cursor-pointer"
                    >
                        <x-filament::icon icon="heroicon-m-printer" class="h-4 w-4" />
                        Print
                    </button>

                    <!-- Wishing Card Generator Button -->
                    <button 
                        type="button" 
                        @click="isCardModalOpen = true"
                        class="w-full sm:w-auto font-bold tracking-wide text-amber-950 bg-gradient-to-r from-amber-300 via-amber-400 to-yellow-500 hover:from-amber-400 hover:to-yellow-600 shadow-[0_4px_15px_rgba(212,175,55,0.3)] hover:shadow-[0_6px_22px_rgba(212,175,55,0.45)] transition-all duration-300 transform hover:-translate-y-0.5 active:translate-y-0 focus:outline-none focus:ring-2 focus:ring-amber-400/50 rounded-xl px-4 py-2.5 text-sm flex items-center justify-center gap-2 whitespace-nowrap cursor-pointer"
                    >
                        <x-filament::icon icon="heroicon-s-sparkles" class="h-4 w-4 text-amber-950" />
                        Wishing Card
                    </button>
                </div>
                
            </form>

            <!-- Upcoming Birthdays List -->
            <div class="mt-6 pt-6 border-t border-gray-100 dark:border-white/5 space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500">
                        Birthdays in {{ $this->month_name }}
                    </h3>
                    <div class="flex items-center gap-3">
                        <span class="inline-flex items-center rounded-md bg-amber-50 dark:bg-amber-500/10 px-2 py-0.5 text-xs font-medium text-amber-700 dark:text-amber-400 ring-1 ring-inset ring-amber-600/10 dark:ring-amber-500/20">
                            {{ $this->birthdays->count() }} Celebrating
                        </span>
                        <!-- Expand Button to trigger Modal -->
                        <button 
                            type="button" 
                            @click="isModalOpen = true"
                            class="p-1 rounded-lg text-gray-400 hover:text-amber-500 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors focus:outline-none cursor-pointer"
                            title="Expand to Full View"
                        >
                            <x-filament::icon icon="heroicon-m-arrows-pointing-out" class="h-4.5 w-4.5" />
                        </button>
                    </div>
                </div>
                
                @if ($this->birthdays->isEmpty())
                    <div class="text-sm font-medium text-gray-500 dark:text-gray-400 py-2">
                        No birthdays found celebrating this month.
                    </div>
                @else
                    <!-- Scrollable Full List inside Widget -->
                    <div class="grid gap-3 max-h-64 overflow-y-auto pr-1">
                        @foreach ($this->birthdays as $employee)
                            <div class="flex items-center justify-between p-3 bg-gray-50/50 dark:bg-gray-800/40 border border-gray-100 dark:border-white/5 rounded-xl transition-all hover:bg-amber-50/30 dark:hover:bg-amber-500/[0.03] hover:border-amber-500/10">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-amber-100 dark:bg-amber-500/15 text-amber-600 dark:text-amber-400">
                                        <span class="text-sm font-black">{{ $loop->iteration }}</span>
                                    </div>
                                    <div>
                                        <div class="text-sm font-bold text-gray-900 dark:text-white">
                                            {{ $employee->name }}
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ $employee->designation?->name }} • {{ $employee->department?->name }}
                                        </div>
                                    </div>
                                </div>
                                <div class="text-xs font-bold text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-500/10 px-2 py-1 rounded-md">
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
                class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 md:p-10"
                style="display: none;"
                x-cloak
            >
                <!-- Backdrop -->
                <div 
                    x-show="isModalOpen"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    @click="isModalOpen = false"
                    class="fixed inset-0 bg-gray-950/60 backdrop-blur-md transition-opacity"
                ></div>

                <!-- Modal Content -->
                <div 
                    x-show="isModalOpen"
                    x-transition:enter="transition ease-out duration-400 transform"
                    x-transition:enter-start="opacity-0 scale-75 translate-y-4"
                    x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-250 transform"
                    x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                    x-transition:leave-end="opacity-0 scale-75 translate-y-4"
                    class="relative overflow-hidden w-full max-w-2xl rounded-2xl bg-white dark:bg-gray-900 shadow-[0_20px_50px_rgba(0,0,0,0.3)] dark:shadow-[0_25px_60px_rgba(0,0,0,0.6)] border border-gray-200/60 dark:border-white/10 z-10 flex flex-col max-h-[85vh] transition-all duration-300"
                >
                    <!-- Modal Header -->
                    <div class="px-6 py-5 border-b border-gray-100 dark:border-white/5 flex items-center justify-between bg-gray-50/50 dark:bg-gray-800/30">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-tr from-amber-500 to-orange-600 text-white shadow-sm">
                                <x-filament::icon icon="heroicon-s-cake" class="h-5 w-5 text-white" />
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-gray-900 dark:text-white">
                                    Birthdays in {{ $this->month_name }} {{ $year }}
                                </h3>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    Full list of {{ $this->birthdays->count() }} active employees celebrating birthdays.
                                </p>
                            </div>
                        </div>
                        
                        <button 
                            type="button" 
                            @click="isModalOpen = false"
                            class="p-2 rounded-xl text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-800 transition-all focus:outline-none cursor-pointer"
                        >
                            <x-filament::icon icon="heroicon-m-x-mark" class="h-5 w-5" />
                        </button>
                    </div>

                    <!-- Modal Body -->
                    <div class="p-6 overflow-y-auto flex-1 space-y-3">
                        @foreach ($this->birthdays as $employee)
                            <div class="flex items-center justify-between p-3.5 bg-gray-50/50 dark:bg-gray-800/40 border border-gray-100 dark:border-white/5 rounded-xl transition-all hover:bg-amber-50/30 dark:hover:bg-amber-500/[0.03] hover:border-amber-500/10">
                                <div class="flex items-center gap-4">
                                    <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-amber-100 dark:bg-amber-500/15 text-amber-600 dark:text-amber-400">
                                        <span class="text-sm font-black">{{ $loop->iteration }}</span>
                                    </div>
                                    <div>
                                        <div class="text-sm font-bold text-gray-900 dark:text-white">
                                            {{ $employee->name }}
                                        </div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ $employee->designation?->name }} • {{ $employee->department?->name }}
                                        </div>
                                    </div>
                                </div>
                                <div class="text-xs font-bold text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-500/10 px-2 py-1 rounded-md">
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
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0"
                    x-transition:enter-end="opacity-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100"
                    x-transition:leave-end="opacity-0"
                    @click="isCardModalOpen = false"
                    class="fixed inset-0 bg-gray-950/80 backdrop-blur-lg transition-opacity"
                ></div>

                <!-- Modal Dialog -->
                <div 
                    x-show="isCardModalOpen"
                    x-transition:enter="transition ease-out duration-300 transform"
                    x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                    x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-200 transform"
                    x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                    x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                    class="relative overflow-hidden w-full max-w-6xl rounded-2xl bg-gray-900 border border-amber-500/30 shadow-[0_25px_70px_rgba(0,0,0,0.8)] z-10 flex flex-col max-h-[92vh]"
                >
                    <!-- Modal Header Bar -->
                    <div class="px-6 py-4 border-b border-amber-500/20 flex flex-wrap items-center justify-between gap-4 bg-gradient-to-r from-gray-950 via-gray-900 to-gray-950">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-tr from-amber-400 via-yellow-500 to-amber-600 text-amber-950 font-bold shadow-[0_4px_12px_rgba(212,175,55,0.4)]">
                                <x-filament::icon icon="heroicon-s-sparkles" class="h-5 w-5 text-amber-950" />
                            </div>
                            <div>
                                <h3 class="text-base font-bold text-white flex items-center gap-2" style="font-family: 'Plus Jakarta Sans', sans-serif;">
                                    Birthday Wishing Card Preview
                                    <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-amber-500/20 text-amber-300 border border-amber-500/40">
                                        2708 × 1492 px
                                    </span>
                                </h3>
                                <p class="text-xs text-gray-400">
                                    High-resolution luxury card for {{ $this->month_name }} {{ $year }} ({{ $this->birthdays->count() }} celebrants)
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3">
                            <!-- Download JPG Button -->
                            <button 
                                type="button" 
                                @click="downloadCardJpg()"
                                :disabled="isGenerating"
                                class="font-bold tracking-wide text-amber-950 bg-gradient-to-r from-amber-300 via-amber-400 to-yellow-500 hover:from-amber-400 hover:to-yellow-600 shadow-[0_4px_20px_rgba(212,175,55,0.4)] transition-all duration-300 transform hover:-translate-y-0.5 active:translate-y-0 disabled:opacity-50 rounded-xl px-5 py-2.5 text-sm flex items-center gap-2 cursor-pointer"
                            >
                                <template x-if="!isGenerating">
                                    <span class="flex items-center gap-2">
                                        <x-filament::icon icon="heroicon-m-arrow-down-tray" class="h-4 w-4 text-amber-950" />
                                        Download JPG
                                    </span>
                                </template>
                                <template x-if="isGenerating">
                                    <span class="flex items-center gap-2">
                                        <svg class="animate-spin h-4 w-4 text-amber-950" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        Generating 2708x1492...
                                    </span>
                                </template>
                            </button>

                            <!-- Close Button -->
                            <button 
                                type="button" 
                                @click="isCardModalOpen = false"
                                class="p-2 rounded-xl text-gray-400 hover:text-white hover:bg-gray-800 transition-all focus:outline-none cursor-pointer"
                            >
                                <x-filament::icon icon="heroicon-m-x-mark" class="h-5 w-5" />
                            </button>
                        </div>
                    </div>

                    <!-- Modal Body / Scaled Preview Wrapper -->
                    <div class="p-4 sm:p-6 overflow-hidden flex-1 bg-gray-950 flex items-center justify-center">
                        <div 
                            x-ref="previewWrapper" 
                            class="relative w-full overflow-hidden rounded-xl border border-amber-500/30 bg-gray-950 shadow-2xl flex items-center justify-center"
                            style="aspect-ratio: 2708 / 1492;"
                        >
                            <!-- Scaled Replica inside viewport -->
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
