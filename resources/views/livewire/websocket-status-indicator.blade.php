<div wire:poll.60s="checkStatus" class="relative inline-flex items-center">
    <!-- Topbar Pill -->
    <div class="flex items-center gap-1.5">
        @if($isRunning)
            <button
                type="button"
                wire:click="toggleModal"
                class="inline-flex items-center gap-1.5 rounded-full border border-emerald-300 bg-emerald-50/80 px-2.5 py-1 text-xs font-semibold text-emerald-800 shadow-sm transition hover:bg-emerald-100 hover:shadow active:scale-95 dark:border-emerald-700/60 dark:bg-emerald-950/40 dark:text-emerald-300 dark:hover:bg-emerald-900/50 cursor-pointer"
                title="Reverb WebSocket is Online on port {{ $port }}. Click for details."
            >
                <span class="relative flex h-2 w-2">
                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-500"></span>
                </span>
                <span class="hidden sm:inline">WS Online</span>
                <span class="text-[10px] font-mono text-emerald-600 dark:text-emerald-400">:{{ $port }}</span>
            </button>
        @else
            <div class="flex items-center gap-1">
                <button
                    type="button"
                    wire:click="toggleModal"
                    class="inline-flex items-center gap-1.5 rounded-full border border-rose-300 bg-rose-50/90 px-2.5 py-1 text-xs font-semibold text-rose-800 shadow-sm transition hover:bg-rose-100 dark:border-rose-800/60 dark:bg-rose-950/40 dark:text-rose-300 dark:hover:bg-rose-900/50 cursor-pointer"
                    title="WebSocket is not running on port {{ $port }}. Click for details."
                >
                    <span class="h-2 w-2 rounded-full bg-rose-500"></span>
                    <span class="hidden sm:inline">WS Offline</span>
                </button>

                @if($isLocal)
                    <button
                        type="button"
                        wire:click="startWebsocket"
                        wire:loading.attr="disabled"
                        class="inline-flex items-center gap-1 rounded-full bg-amber-500 px-2.5 py-1 text-xs font-bold text-white shadow-sm transition hover:bg-amber-600 active:scale-95 disabled:opacity-50 cursor-pointer"
                        title="Start Laravel Reverb WebSocket server"
                    >
                        <span wire:loading.remove wire:target="startWebsocket">▶ Start WS</span>
                        <span wire:loading wire:target="startWebsocket" class="inline-flex items-center gap-1">
                            <svg class="h-3 w-3 animate-spin" viewBox="0 0 24 24" fill="none">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            Starting...
                        </span>
                    </button>
                @endif
            </div>
        @endif
    </div>

    <!-- Status Modal -->
    @if($showInfoModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 p-4 backdrop-blur-sm">
            <div
                wire:click.outside="closeModal"
                class="relative w-full max-w-md overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xl dark:border-slate-800 dark:bg-slate-900 max-h-[90vh] flex flex-col"
            >
                <!-- Modal Header -->
                <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4 dark:border-slate-800">
                    <div class="flex items-center gap-2.5">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-amber-500/10 text-amber-600 dark:bg-amber-400/10 dark:text-amber-400 text-lg">
                            ⚡
                        </span>
                        <div>
                            <h3 class="text-sm font-bold text-gray-900 dark:text-white">WebSocket Services</h3>
                            <p class="text-[11px] text-gray-500 dark:text-slate-400">
                                {{ $isLocal ? 'Local development server status' : 'Production server status' }}
                            </p>
                        </div>
                    </div>
                    <button
                        type="button"
                        wire:click="closeModal"
                        class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-slate-800 dark:hover:text-slate-200 transition"
                    >
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="flex-1 overflow-y-auto p-5 space-y-4 text-xs text-gray-700 dark:text-slate-300">

                    <!-- WebSocket Status Card -->
                    <div class="rounded-xl border p-4 {{ $isRunning ? 'border-emerald-200 bg-emerald-50/50 dark:border-emerald-900/50 dark:bg-emerald-950/30' : 'border-rose-200 bg-rose-50/50 dark:border-rose-900/50 dark:bg-rose-950/30' }}">
                        <div class="flex items-start justify-between gap-3">
                            <div class="space-y-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="relative flex h-3 w-3 flex-shrink-0">
                                        @if($isRunning)
                                            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                                            <span class="relative inline-flex h-3 w-3 rounded-full bg-emerald-500"></span>
                                        @else
                                            <span class="h-3 w-3 rounded-full bg-rose-500"></span>
                                        @endif
                                    </span>
                                    <h4 class="font-bold text-sm {{ $isRunning ? 'text-emerald-900 dark:text-emerald-200' : 'text-rose-900 dark:text-rose-200' }}">
                                        {{ $isRunning ? 'Reverb WebSocket is Running' : 'Reverb WebSocket is Offline' }}
                                    </h4>
                                </div>
                                <p class="text-[11px] {{ $isRunning ? 'text-emerald-700 dark:text-emerald-400' : 'text-rose-700 dark:text-rose-400' }}">
                                    {{ $isRunning
                                        ? 'Real-time notifications, chat messages, reactions, and pin synchronizations are actively broadcasting.'
                                        : 'Real-time push events will not update clients until Reverb is running.' }}
                                </p>
                            </div>
                            <button
                                type="button"
                                wire:click="checkStatus"
                                class="flex-shrink-0 rounded-lg border border-gray-300 bg-white px-2.5 py-1 text-[11px] font-semibold text-gray-700 shadow-sm hover:bg-gray-50 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 transition cursor-pointer"
                                title="Refresh status"
                            >
                                🔄 Re-check
                            </button>
                        </div>

                        <!-- Technical Specs Grid -->
                        <div class="mt-3 grid grid-cols-3 gap-2 text-[11px] font-mono">
                            <div class="rounded-lg bg-white/70 p-2 dark:bg-slate-900/60 border border-gray-100 dark:border-slate-800">
                                <span class="text-gray-400 dark:text-slate-500 block text-[9px] uppercase font-sans">Server</span>
                                <span class="font-bold">Reverb</span>
                            </div>
                            <div class="rounded-lg bg-white/70 p-2 dark:bg-slate-900/60 border border-gray-100 dark:border-slate-800">
                                <span class="text-gray-400 dark:text-slate-500 block text-[9px] uppercase font-sans">Host : Port</span>
                                <span class="font-bold">{{ $host }}:{{ $port }}</span>
                            </div>
                            <div class="rounded-lg bg-white/70 p-2 dark:bg-slate-900/60 border border-gray-100 dark:border-slate-800">
                                <span class="text-gray-400 dark:text-slate-500 block text-[9px] uppercase font-sans">Checked At</span>
                                <span class="font-bold">{{ $lastCheckedAt ?? 'Just now' }}</span>
                            </div>
                        </div>

                        <!-- Action if Offline — Local Only -->
                        @if(!$isRunning && $isLocal)
                            <div class="mt-3 flex items-center justify-between pt-2 border-t border-rose-200 dark:border-rose-900/50">
                                <span class="text-[11px] text-rose-800 dark:text-rose-300">Start Reverb directly from here:</span>
                                <button
                                    type="button"
                                    wire:click="startWebsocket"
                                    wire:loading.attr="disabled"
                                    class="inline-flex items-center gap-1.5 rounded-lg bg-emerald-600 px-3.5 py-1.5 text-xs font-bold text-white shadow-md hover:bg-emerald-500 active:scale-95 disabled:opacity-50 cursor-pointer"
                                >
                                    <span wire:loading.remove wire:target="startWebsocket">▶ Start WebSocket Now</span>
                                    <span wire:loading wire:target="startWebsocket">Starting...</span>
                                </button>
                            </div>
                        @elseif(!$isRunning && !$isLocal)
                            <div class="mt-3 pt-2 border-t border-rose-200 dark:border-rose-900/50">
                                <p class="text-[11px] text-rose-700 dark:text-rose-400">
                                    ⚠️ Reverb is managed by <strong>Supervisor</strong> on this server. If it's offline, please check the server process manager or contact your hosting provider.
                                </p>
                            </div>
                        @endif
                    </div>

                    <!-- LOCAL DEV: Scheduler Guide -->
                    @if($isLocal)
                        <div class="rounded-xl border border-gray-200 bg-gray-50/70 p-4 dark:border-slate-800 dark:bg-slate-950/40 space-y-3">
                            <div class="flex items-center gap-2">
                                <span class="text-base">⏰</span>
                                <div>
                                    <h4 class="font-bold text-sm text-gray-900 dark:text-white">Laravel Scheduler (Local Dev)</h4>
                                    <p class="text-[11px] text-gray-500 dark:text-slate-400">Handles unpinning expired messages and background jobs.</p>
                                </div>
                            </div>

                            <!-- schedule:work -->
                            <div class="rounded-lg border border-amber-200/80 bg-amber-50/60 p-3 dark:border-amber-900/40 dark:bg-amber-950/20 space-y-2">
                                <p class="text-[11px] text-gray-700 dark:text-slate-300">
                                    Open a terminal in your project directory and run:
                                </p>
                                <div x-data="{ copied: false }" class="flex items-center justify-between rounded-lg bg-gray-900 px-3 py-2 text-white font-mono text-xs">
                                    <code>php artisan schedule:work</code>
                                    <button
                                        type="button"
                                        @click="navigator.clipboard.writeText('php artisan schedule:work'); copied = true; setTimeout(() => copied = false, 2000)"
                                        class="rounded bg-gray-800 px-2 py-0.5 text-[10px] font-sans font-semibold text-gray-300 hover:bg-gray-700 hover:text-white transition cursor-pointer"
                                    >
                                        <span x-show="!copied">Copy</span>
                                        <span x-show="copied" class="text-emerald-400 font-bold">✓ Copied!</span>
                                    </button>
                                </div>
                                <p class="text-[10px] text-gray-500 dark:text-slate-400">
                                    Runs continuously and executes scheduled jobs every minute.
                                </p>
                            </div>
                        </div>
                    @else
                        <!-- PRODUCTION: Scheduler Info -->
                        <div class="rounded-xl border border-gray-200 bg-gray-50/70 p-4 dark:border-slate-800 dark:bg-slate-950/40 space-y-2">
                            <div class="flex items-center gap-2">
                                <span class="text-base">✅</span>
                                <div>
                                    <h4 class="font-bold text-sm text-gray-900 dark:text-white">Scheduler & Services</h4>
                                    <p class="text-[11px] text-gray-500 dark:text-slate-400">Managed automatically on this production server.</p>
                                </div>
                            </div>
                            <ul class="space-y-1.5 text-[11px] text-gray-600 dark:text-slate-400 pl-1">
                                <li class="flex items-start gap-2">
                                    <span class="text-emerald-500 flex-shrink-0 mt-px">●</span>
                                    <span><strong class="text-gray-800 dark:text-slate-200">Reverb WebSocket</strong> — managed by Supervisor / systemd</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-emerald-500 flex-shrink-0 mt-px">●</span>
                                    <span><strong class="text-gray-800 dark:text-slate-200">Laravel Scheduler</strong> — runs via server cron every minute</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <span class="text-emerald-500 flex-shrink-0 mt-px">●</span>
                                    <span><strong class="text-gray-800 dark:text-slate-200">Queue Worker</strong> — running in background via Supervisor</span>
                                </li>
                            </ul>
                            <p class="text-[10px] text-gray-400 dark:text-slate-500 pt-1 border-t border-gray-200 dark:border-slate-800">
                                No manual action needed. Contact your server admin if WebSocket remains offline.
                            </p>
                        </div>
                    @endif
                </div>

                <!-- Modal Footer -->
                <div class="flex items-center justify-end border-t border-gray-100 px-5 py-3 dark:border-slate-800 bg-gray-50/50 dark:bg-slate-950/30">
                    <button
                        type="button"
                        wire:click="closeModal"
                        class="rounded-xl bg-gray-200 px-4 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-300 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700 transition cursor-pointer"
                    >
                        Close
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
