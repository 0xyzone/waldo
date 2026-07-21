<x-filament-widgets::widget>
    <div x-data="{ isModalOpen: false }" class="h-full relative overflow-hidden rounded-2xl bg-white dark:bg-gray-900 shadow-[0_8px_30px_rgb(0,0,0,0.04)] dark:shadow-[0_15px_40px_rgba(0,0,0,0.4)] border border-gray-200/50 dark:border-white/10 transition-all duration-500 hover:border-amber-500/30 dark:hover:border-amber-500/20 hover:shadow-[0_20px_50px_rgba(249,115,22,0.08)] group w-full flex flex-col">
        
        <!-- Glowing backdrop elements -->
        <div class="absolute -right-24 -top-24 h-64 w-64 rounded-full bg-gradient-to-br from-amber-500/10 to-orange-500/10 opacity-30 dark:opacity-20 blur-3xl pointer-events-none transition-all duration-700 group-hover:scale-110"></div>
        <div class="absolute -left-20 -bottom-20 h-64 w-64 rounded-full bg-linear-to-tr from-indigo-500/5 to-purple-500/5 opacity-20 dark:opacity-10 blur-3xl pointer-events-none"></div>

        <div class="p-8 sm:p-10 relative z-10 space-y-8">
            <!-- Header Section -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6 pb-6 border-b border-gray-100 dark:border-white/5">
                <div class="flex items-center gap-4">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-linear-to-tr from-amber-500 to-orange-600 text-white shadow-[0_8px_20px_rgba(249,115,22,0.2)] transition-transform duration-500 group-hover:scale-105">
                        <x-filament::icon icon="heroicon-s-cake" class="h-7 w-7 text-white" />
                    </div>
                    <div>
                        <h2 class="text-xl font-bold tracking-tight text-gray-900 dark:text-white">
                            Birthday List Generator
                        </h2>
                        <p class="mt-1.5 text-sm text-gray-500 dark:text-gray-400 font-normal leading-relaxed">
                            Generate a clean, printable PDF report of active employees celebrating birthdays.
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
                <div class="w-full sm:w-auto">
                    <button 
                        type="submit" 
                        class="w-full font-bold tracking-wide text-white bg-linear-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 shadow-[0_4px_15px_rgba(249,115,22,0.2)] hover:shadow-[0_6px_20px_rgba(249,115,22,0.3)] transition-all duration-300 transform hover:-translate-y-0.5 active:translate-y-0 focus:outline-none focus:ring-2 focus:ring-amber-500/50 focus:ring-offset-2 dark:focus:ring-offset-gray-900 rounded-xl px-5 py-2.5 text-sm flex items-center justify-center gap-2 whitespace-nowrap"
                    >
                        <x-filament::icon icon="heroicon-m-printer" class="h-4 w-4" />
                        Print
                    </button>
                </div>
                
            </form>

            <!-- Upcoming Birthdays List -->
            <div class="mt-6 pt-6 border-t border-gray-100 dark:border-white/5 space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500">
                        Birthdays in {{ Carbon\Carbon::create(null, (int)$month)->format('F') }}
                    </h3>
                    <div class="flex items-center gap-3">
                        <span class="inline-flex items-center rounded-md bg-amber-50 dark:bg-amber-500/10 px-2 py-0.5 text-xs font-medium text-amber-700 dark:text-amber-400 ring-1 ring-inset ring-amber-600/10 dark:ring-amber-500/20">
                            {{ $this->birthdays->count() }} Celebrating
                        </span>
                        <!-- Expand Button to trigger Modal -->
                        <button 
                            type="button" 
                            @click="isModalOpen = true"
                            class="p-1 rounded-lg text-gray-400 hover:text-amber-500 hover:bg-gray-100 dark:hover:bg-gray-805 transition-colors focus:outline-none cursor-pointer"
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

        <!-- Modal with custom Zoom transition -->
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

                <!-- Modal Content (Zoom / pop-out animation) -->
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
                                    Birthdays in {{ Carbon\Carbon::create(null, (int)$month)->format('F') }}
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
    </div>
</x-filament-widgets::widget>
