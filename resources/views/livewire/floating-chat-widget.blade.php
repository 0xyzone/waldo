<div>
@if(!request()->is('*kamkaj/chat*'))
<div
    x-data="{
        isOpen: @entangle('isOpen'),
        activeConversationId: @entangle('activeConversationId'),
        isRecording: false,
        isRecordingEnded: false,
        isCancelled: false,
        isPaused: false,
        shouldSendImmediately: false,
        isUploadingVoice: false,
        preparedVoiceFile: null,
        recordedMimeType: 'audio/webm',
        mediaRecorder: null,
        audioChunks: [],
        recordingTime: 0,
        recordingInterval: null,
        maxRecordingSeconds: 60,
        audioCtx: null,
        analyserNode: null,
        micStream: null,
        vizBars: Array(32).fill(3),
        vizRaf: null,

        init() {
            this.initEcho();

            $wire.on('floating-conversation-changed', (event) => {
                if (this.isRecording) {
                    this.cancelRecording();
                }
                this.$nextTick(() => {
                    this.subscribeToConversation(event.conversationId);
                    this.scrollToBottom();
                });
            });

            $wire.on('scroll-floating-chat', () => {
                this.$nextTick(() => this.scrollToBottom());
            });

            $wire.on('scroll-to-message', (event) => {
                const id = (typeof event === 'object' && event !== null && event.messageId) ? event.messageId : event;
                this.$nextTick(() => this.scrollToMessage(id));
            });

            window.addEventListener('EchoLoaded', () => {
                this.initEcho();
            });
        },

        initEcho() {
            if (!window.Echo) return;

            window.Echo.private(`chat.user.{{ auth()->id() }}`)
                .listen('.message.sent', (e) => {
                    this.playTing();
                    $wire.$refresh();
                    if (this.isOpen && this.activeConversationId === e.conversation_id) {
                        this.$nextTick(() => this.scrollToBottom());
                    }
                })
                .listen('.message.deleted', (e) => {
                    $wire.$refresh();
                })
                .listen('.message.pinned', (e) => {
                    $wire.$refresh();
                })
                .listen('.message.reacted', (e) => {
                    $wire.$refresh();
                });

            if (this.activeConversationId) {
                this.subscribeToConversation(this.activeConversationId);
            }
        },

        subscribeToConversation(conversationId) {
            if (!window.Echo || !conversationId) return;

            window.Echo.private(`chat.conversation.${conversationId}`)
                .stopListening('.message.sent')
                .stopListening('.message.deleted')
                .stopListening('.message.pinned')
                .stopListening('.message.reacted')
                .listen('.message.sent', (payload) => {
                    if (payload.sender_id !== {{ auth()->id() }}) {
                        this.playTing();
                    }
                    $wire.incomingMessage(payload);
                    $wire.$refresh();
                    this.$nextTick(() => this.scrollToBottom());
                })
                .listen('.message.deleted', () => {
                    $wire.$refresh();
                })
                .listen('.message.pinned', () => {
                    $wire.$refresh();
                })
                .listen('.message.reacted', () => {
                    $wire.$refresh();
                });
        },

        scrollToBottom() {
            const feed = this.$refs.floatingFeed;
            if (feed) {
                feed.scrollTop = feed.scrollHeight;
            }
        },

        scrollToMessage(msgId) {
            const el = document.getElementById('float-msg-' + msgId);
            if (el) {
                el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                el.classList.add('ring-2', 'ring-amber-500', 'rounded-2xl', 'transition-all');
                setTimeout(() => {
                    el.classList.remove('ring-2', 'ring-amber-500');
                }, 2000);
            }
        },

        playTing() {
            try {
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                const now = ctx.currentTime;
                
                const osc1 = ctx.createOscillator();
                const gain1 = ctx.createGain();
                osc1.type = 'sine';
                osc1.frequency.setValueAtTime(880, now);
                gain1.gain.setValueAtTime(0.35, now);
                gain1.gain.exponentialRampToValueAtTime(0.001, now + 0.45);
                osc1.connect(gain1);
                gain1.connect(ctx.destination);
                osc1.start(now);
                osc1.stop(now + 0.45);

                const osc2 = ctx.createOscillator();
                const gain2 = ctx.createGain();
                osc2.type = 'sine';
                osc2.frequency.setValueAtTime(1318.5, now + 0.08);
                gain2.gain.setValueAtTime(0.4, now + 0.08);
                gain2.gain.exponentialRampToValueAtTime(0.001, now + 0.65);
                osc2.connect(gain2);
                gain2.connect(ctx.destination);
                osc2.start(now + 0.08);
                osc2.stop(now + 0.65);
            } catch(e) {}
        },

        isCancelled: false,
        isPaused: false,
        recordedMimeType: 'audio/webm',
        analyserNode: null,
        audioCtx: null,
        vizBars: Array(32).fill(2),
        vizRaf: null,
        maxRecordingSeconds: 60,

        async startRecording() {
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                alert('Voice recording is not available. Please ensure you are using HTTPS and a supported browser (Chrome/Firefox).');
                return;
            }
            if (!window.MediaRecorder) {
                alert('Your browser does not support voice recording. Please use Chrome or Firefox.');
                return;
            }
            try {
                const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                this.micStream = stream;

                // Detect best supported MIME type
                const mimeType = [
                    'audio/webm;codecs=opus',
                    'audio/webm',
                    'audio/ogg;codecs=opus',
                    'audio/ogg',
                    'audio/mp4',
                ].find(t => MediaRecorder.isTypeSupported(t)) || '';

                this.recordedMimeType = mimeType || 'audio/webm';
                const options = mimeType ? { mimeType } : {};

                this.mediaRecorder = new MediaRecorder(stream, options);
                this.audioChunks = [];
                this.preparedVoiceFile = null;
                this.recordingTime = 0;
                this.isCancelled = false;
                this.isPaused = false;
                this.isRecordingEnded = false;
                this.shouldSendImmediately = false;
                this.isUploadingVoice = false;
                this.isRecording = true;

                // Web Audio API for real-time reactive mic visualization
                try {
                    const AudioCtx = window.AudioContext || window.webkitAudioContext;
                    this.audioCtx = new AudioCtx();
                    if (this.audioCtx.state === 'suspended') {
                        await this.audioCtx.resume();
                    }
                    const source = this.audioCtx.createMediaStreamSource(stream);
                    this.analyserNode = this.audioCtx.createAnalyser();
                    this.analyserNode.fftSize = 128;
                    this.analyserNode.smoothingTimeConstant = 0.35;
                    source.connect(this.analyserNode);
                    this.startVizLoop();
                } catch (e) {
                    console.warn('Microphone analyser setup failed:', e);
                }

                // Timer + 60s auto-stop (stops mic & recording, waits for send click)
                this.recordingInterval = setInterval(() => {
                    if (!this.isPaused && !this.isRecordingEnded) {
                        this.recordingTime++;
                        if (this.recordingTime >= this.maxRecordingSeconds) {
                            this.handleRecordingTimerEnd();
                        }
                    }
                }, 1000);

                this.mediaRecorder.ondataavailable = (e) => {
                    if (!this.isCancelled && e.data && e.data.size > 0) {
                        this.audioChunks.push(e.data);
                    }
                };

                this.mediaRecorder.onstop = () => {
                    clearInterval(this.recordingInterval);
                    this.stopVizLoop();
                    if (this.audioCtx) {
                        try { this.audioCtx.close(); } catch(e) {}
                        this.audioCtx = null;
                    }
                    if (this.micStream) {
                        this.micStream.getTracks().forEach(track => track.stop());
                        this.micStream = null;
                    }

                    if (!this.isCancelled && this.audioChunks.length > 0) {
                        const mime = this.recordedMimeType;
                        const ext = mime.includes('ogg') ? 'ogg' : mime.includes('mp4') ? 'mp4' : 'webm';
                        const audioBlob = new Blob(this.audioChunks, { type: mime });
                        this.preparedVoiceFile = new File([audioBlob], 'voice_note_' + Date.now() + '.' + ext, { type: mime });
                        this.audioChunks = [];

                        if (this.shouldSendImmediately) {
                            this.sendPreparedVoiceFile();
                        } else {
                            this.isRecordingEnded = true;
                            this.isPaused = true;
                        }
                    } else if (this.isCancelled) {
                        this.resetRecordingState();
                    }
                };

                this.mediaRecorder.start(200);
            } catch (err) {
                this.resetRecordingState();
                if (err.name === 'NotAllowedError' || err.name === 'PermissionDeniedError') {
                    alert('Microphone access was denied. Please allow microphone access in your browser settings and try again.');
                } else if (err.name === 'NotFoundError' || err.name === 'DevicesNotFoundError') {
                    alert('No microphone found. Please connect a microphone and try again.');
                } else {
                    alert('Could not start recording: ' + (err.message || err.name));
                }
            }
        },

        startVizLoop() {
            this.stopVizLoop();
            const numBars = 32;
            const timeBuf = new Uint8Array(this.analyserNode.fftSize);
            const freqBuf = new Uint8Array(this.analyserNode.frequencyBinCount);

            const draw = () => {
                if (!this.isRecording || !this.analyserNode) return;

                if (this.isPaused || this.isRecordingEnded) {
                    this.vizBars = Array(numBars).fill(3);
                    this.vizRaf = requestAnimationFrame(draw);
                    return;
                }

                // 1. Measure voice amplitude from waveform time domain
                this.analyserNode.getByteTimeDomainData(timeBuf);
                let sumSquares = 0;
                for (let i = 0; i < timeBuf.length; i++) {
                    const norm = (timeBuf[i] - 128) / 128;
                    sumSquares += norm * norm;
                }
                const volume = Math.sqrt(sumSquares / timeBuf.length);

                // 2. Measure frequency distribution
                this.analyserNode.getByteFrequencyData(freqBuf);

                // Silence threshold
                if (volume < 0.02) {
                    this.vizBars = Array(numBars).fill(3);
                } else {
                    const amplifiedVol = Math.min(1, volume * 3.8);
                    const bars = [];
                    const step = Math.floor(freqBuf.length / numBars);

                    for (let i = 0; i < numBars; i++) {
                        const freqVal = (freqBuf[i * step] || 0) / 255;
                        const energy = (freqVal * 0.55) + (amplifiedVol * 0.45);
                        const distFromCenter = Math.abs(i - (numBars / 2)) / (numBars / 2);
                        const curveFactor = 1 - (distFromCenter * 0.35);
                        const height = Math.max(3, Math.min(26, Math.round(energy * curveFactor * 26 + 3)));
                        bars.push(height);
                    }
                    this.vizBars = bars;
                }

                this.vizRaf = requestAnimationFrame(draw);
            };

            this.vizRaf = requestAnimationFrame(draw);
        },

        stopVizLoop() {
            if (this.vizRaf) { cancelAnimationFrame(this.vizRaf); this.vizRaf = null; }
            this.vizBars = Array(32).fill(3);
        },

        handleRecordingTimerEnd() {
            clearInterval(this.recordingInterval);
            this.stopVizLoop();
            if (this.audioCtx) {
                try { this.audioCtx.close(); } catch(e) {}
                this.audioCtx = null;
            }
            if (this.micStream) {
                this.micStream.getTracks().forEach(track => track.stop());
                this.micStream = null;
            }
            this.shouldSendImmediately = false;
            this.isRecordingEnded = true;
            this.isPaused = true;
            if (this.mediaRecorder && this.mediaRecorder.state !== 'inactive') {
                this.mediaRecorder.stop();
            }
        },

        sendPreparedVoiceFile() {
            if (!this.preparedVoiceFile || this.isUploadingVoice) return;
            this.isUploadingVoice = true;

            @this.upload('voiceNote', this.preparedVoiceFile, () => {
                $wire.sendMessage();
                this.resetRecordingState();
            }, (err) => {
                console.error('Voice upload failed:', err);
                alert('Failed to upload voice note. Please try again.');
                this.isUploadingVoice = false;
            }, () => {});
        },

        resetRecordingState() {
            clearInterval(this.recordingInterval);
            this.stopVizLoop();
            if (this.audioCtx) {
                try { this.audioCtx.close(); } catch(e) {}
                this.audioCtx = null;
            }
            if (this.micStream) {
                this.micStream.getTracks().forEach(track => track.stop());
                this.micStream = null;
            }
            this.isRecording = false;
            this.isRecordingEnded = false;
            this.isPaused = false;
            this.isCancelled = false;
            this.isUploadingVoice = false;
            this.shouldSendImmediately = false;
            this.preparedVoiceFile = null;
            this.audioChunks = [];
            this.recordingTime = 0;
            this.mediaRecorder = null;
        },

        togglePauseRecording() {
            if (!this.mediaRecorder || !this.isRecording || this.isRecordingEnded) return;
            if (this.isPaused) {
                this.mediaRecorder.resume();
                this.isPaused = false;
                if (this.audioCtx && this.audioCtx.state === 'suspended') {
                    this.audioCtx.resume();
                }
            } else {
                this.mediaRecorder.pause();
                this.isPaused = true;
            }
        },

        stopAndSendRecording() {
            if (this.isUploadingVoice) return;

            if (this.isRecordingEnded && this.preparedVoiceFile) {
                this.sendPreparedVoiceFile();
                return;
            }

            if (this.mediaRecorder && this.isRecording) {
                this.shouldSendImmediately = true;
                this.isCancelled = false;
                this.mediaRecorder.stop();
            }
        },

        cancelRecording() {
            this.isCancelled = true;
            if (this.mediaRecorder && this.mediaRecorder.state !== 'inactive') {
                this.mediaRecorder.stop();
            }
            this.resetRecordingState();
        },

        formatTime(seconds) {
            const mins = Math.floor(seconds / 60);
            const secs = seconds % 60;
            return String(mins).padStart(2, '0') + ':' + String(secs).padStart(2, '0');
        }
    }"
    class="fixed bottom-5 right-5 z-40 flex flex-col items-end font-sans"
>
    <!-- Floating Window Popover -->
    <div
        x-show="isOpen"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 translate-y-4 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 scale-95"
        style="display: none;"
        class="mb-3 flex h-[540px] max-h-[84vh] w-[370px] sm:w-[410px] flex-col overflow-hidden rounded-2xl border border-gray-200/90 bg-white shadow-2xl backdrop-blur-xl dark:border-gray-800 dark:bg-gray-900"
    >
        <!-- Window Top Bar -->
        <div class="flex items-center justify-between border-b border-gray-100 bg-gray-50/90 px-3.5 py-2.5 dark:border-gray-800/80 dark:bg-gray-800/80">
            <div class="flex items-center gap-2 min-w-0">
                @if($this->activeConversation)
                    <button
                        type="button"
                        wire:click="$set('activeConversationId', null)"
                        class="rounded-lg p-1 text-gray-500 hover:bg-gray-200 dark:text-gray-400 dark:hover:bg-gray-700"
                        title="Back to conversations"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </button>
                    <div class="min-w-0">
                        <h4 class="truncate text-xs font-bold text-gray-900 dark:text-white flex items-center gap-1.5">
                            {{ $this->activeConversation->getDisplayName(auth()->id()) }}
                            @if($this->activeConversation->isGroup())
                                @php $myFloatingRole = $this->activeConversation->getUserRole(auth()->id()); @endphp
                                @if($myFloatingRole === 'owner')
                                    <span class="rounded-full bg-amber-500/20 px-1.5 py-0.2 text-[9px] font-bold text-amber-800 dark:bg-amber-400/20 dark:text-amber-300 ring-1 ring-amber-400/30">
                                        👑 Owner
                                    </span>
                                @elseif($myFloatingRole === 'admin')
                                    <span class="rounded-full bg-indigo-500/20 px-1.5 py-0.2 text-[9px] font-bold text-indigo-700 dark:bg-indigo-400/20 dark:text-indigo-300 ring-1 ring-indigo-400/30">
                                        🛡️ Admin
                                    </span>
                                @else
                                    <span class="rounded bg-gray-100 px-1 py-0.2 text-[9px] font-semibold text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                                        Group
                                    </span>
                                @endif
                            @endif
                        </h4>
                    </div>
                @else
                    <h4 class="text-xs font-bold text-gray-900 dark:text-white flex items-center gap-1.5">
                        <span class="h-2 w-2 rounded-full bg-amber-500 animate-pulse"></span>
                        Quick Chat
                    </h4>
                @endif
            </div>
            <div class="flex items-center gap-1">
                @if($this->activeConversation)
                    @if($this->activeConversation->isGroup())
                        <!-- Group Settings & Management -->
                        <button
                            type="button"
                            wire:click="openGroupSettings"
                            class="rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-amber-600 dark:hover:bg-gray-700 dark:hover:text-amber-400"
                            title="Group Settings & Members"
                        >
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </button>
                    @endif

                    <!-- Search In Chat Toggle -->
                    <button
                        type="button"
                        wire:click="toggleMessageSearch"
                        class="rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-amber-600 dark:hover:bg-gray-700 dark:hover:text-amber-400 {{ $showMessageSearch ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300' : '' }}"
                        title="Search in this chat"
                    >
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </button>
                @endif

                <!-- Open Full Page -->
                <a
                    href="{{ url('/kamkaj/chat' . ($activeConversationId ? '?c=' . $activeConversationId : '')) }}"
                    class="rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-700 dark:hover:text-gray-200"
                    title="Open full chat page"
                >
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                </a>
                <!-- Close Button -->
                <button
                    type="button"
                    wire:click="toggleWidget"
                    class="rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-700 dark:hover:text-gray-200"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        @if($this->activeConversation)
            <!-- In-Chat Search Bar (Expandable) -->
            @if($showMessageSearch)
                <div class="flex items-center gap-1.5 border-b border-gray-100 bg-amber-50/70 p-2 dark:border-gray-800 dark:bg-gray-800/80">
                    <svg class="h-3.5 w-3.5 text-gray-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input
                        type="text"
                        wire:model.live.debounce.200ms="messageSearch"
                        placeholder="Search in this conversation..."
                        class="chat-input-field flex-1 rounded-lg border border-gray-300 bg-white px-2.5 py-1 text-xs text-gray-900 placeholder-gray-400 focus:border-amber-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:placeholder-gray-500"
                    />
                    @if(filled($messageSearch))
                        <button
                            type="button"
                            wire:click="clearMessageSearch"
                            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"
                            title="Clear search"
                        >
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    @endif
                </div>
            @endif

            <!-- Pinned Messages Banner -->
            @php $pinnedMessages = $this->pinnedMessages; @endphp
            @if($pinnedMessages->isNotEmpty())
                <div class="border-b border-amber-200/80 bg-gradient-to-r from-amber-50 to-orange-50 px-3 py-1.5 text-xs dark:border-amber-900/40 dark:from-amber-950/40 dark:to-orange-950/30">
                    @php $firstPinned = $pinnedMessages->first(); @endphp
                    <div class="flex items-center justify-between gap-2">
                        <button
                            type="button"
                            @if($pinnedMessages->count() > 1)
                                wire:click="openPinnedMessagesModal"
                            @else
                                @click="scrollToMessage({{ $firstPinned->id }})"
                            @endif
                            class="flex items-center gap-1.5 min-w-0 text-left hover:opacity-80 transition"
                            title="{{ $pinnedMessages->count() > 1 ? 'Click to view all pinned messages' : 'Click to jump to pinned message' }}"
                        >
                            <span class="flex h-5 w-5 flex-shrink-0 items-center justify-center rounded-md bg-amber-500 text-white text-[11px]">
                                📌
                            </span>
                            <div class="min-w-0">
                                <div class="flex items-center gap-1.5">
                                    <span class="font-bold text-[10px] text-amber-900 dark:text-amber-200 truncate">
                                        Pinned: {{ $firstPinned->sender?->name }}
                                    </span>
                                    <span class="text-[9px] text-amber-700 dark:text-amber-400 font-medium">
                                        ({{ $firstPinned->getPinnedTimeRemaining() }})
                                    </span>
                                </div>
                                <p class="truncate text-[10px] text-gray-600 dark:text-gray-300">
                                    {{ $firstPinned->body ?: ($firstPinned->attachment_name ?: 'Attachment') }}
                                </p>
                            </div>
                        </button>
                        <div class="flex items-center gap-1 flex-shrink-0">
                            @if($pinnedMessages->count() > 1)
                                <button
                                    type="button"
                                    wire:click="openPinnedMessagesModal"
                                    class="rounded-full bg-amber-200 hover:bg-amber-300 dark:bg-amber-900/70 dark:hover:bg-amber-800 px-2 py-0.5 text-[9px] font-bold text-amber-800 dark:text-amber-200 transition shadow-sm cursor-pointer flex items-center gap-1"
                                    title="View all pinned messages"
                                >
                                    <span>{{ $pinnedMessages->count() }} pinned</span>
                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>
                            @endif
                            <button
                                type="button"
                                wire:click="unpinMessage({{ $firstPinned->id }})"
                                class="rounded p-1 text-gray-400 hover:text-red-500 transition"
                                title="Unpin message"
                            >
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Active Chat Feed -->
            <div
                x-ref="floatingFeed"
                class="flex-1 overflow-y-auto p-3 space-y-2.5 bg-gray-50/40 dark:bg-gray-950/40"
            >
                @php $messages = $this->messages; @endphp
                @forelse($messages as $msg)
                    @php
                        $isMe = $msg->sender_id === auth()->id();
                        $canDelete = $msg->canBeDeletedBy(auth()->id());
                        $isPinned = $msg->isCurrentlyPinned();
                    @endphp
                    <div
                        id="float-msg-{{ $msg->id }}"
                        wire:key="float-msg-{{ $msg->id }}"
                        class="group relative flex flex-col {{ $isMe ? 'items-end' : 'items-start' }}"
                    >
                        <div class="flex items-end gap-1.5 max-w-[88%] {{ $isMe ? 'flex-row-reverse' : 'flex-row' }}">
                            @if(!$isMe)
                                @if($msg->sender?->avatar_url)
                                    <img src="{{ $msg->sender->getFilamentAvatarUrl() }}" class="h-6 w-6 rounded-full object-cover flex-shrink-0" />
                                @else
                                    <div class="flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full bg-amber-500/20 text-[10px] font-bold text-amber-700 dark:text-amber-300">
                                        {{ strtoupper(substr($msg->sender?->name ?? 'U', 0, 1)) }}
                                    </div>
                                @endif
                            @endif

                            <div class="relative rounded-2xl px-3 py-2 text-xs shadow-sm transition-all {{ $isMe ? 'bg-amber-600 text-white rounded-br-sm' : 'bg-white text-gray-900 border border-gray-200 dark:border-gray-800 dark:bg-gray-800 dark:text-gray-100 rounded-bl-sm' }} {{ !$isMe && $msg->mentionsUser(auth()->id()) ? 'ring-2 ring-amber-500/80 dark:ring-amber-400/80 border-amber-400/60 dark:border-amber-500/60 bg-amber-50/50 dark:bg-amber-950/20' : '' }}">
                                @if($this->activeConversation->isGroup() && !$isMe)
                                    <div class="flex items-center justify-between gap-1 mb-0.5">
                                        <span class="block text-[10px] font-semibold text-amber-600 dark:text-amber-400">
                                            {{ $msg->sender?->name }}
                                        </span>
                                        @if($msg->mentionsUser(auth()->id()))
                                            <span class="inline-flex items-center gap-0.5 rounded bg-amber-500/20 px-1 py-0.2 text-[8px] font-bold text-amber-800 dark:bg-amber-400/20 dark:text-amber-300 ring-1 ring-amber-400/30">
                                                @ Tagged you
                                            </span>
                                        @endif
                                    </div>
                                @elseif(!$isMe && $msg->mentionsUser(auth()->id()))
                                    <div class="mb-0.5">
                                        <span class="inline-flex items-center gap-0.5 rounded bg-amber-500/20 px-1 py-0.2 text-[8px] font-bold text-amber-800 dark:bg-amber-400/20 dark:text-amber-300 ring-1 ring-amber-400/30">
                                            @ Tagged you
                                        </span>
                                    </div>
                                @endif

                                @if($isPinned)
                                    <div class="mb-1 flex items-center gap-1 text-[9px] font-bold {{ $isMe ? 'text-amber-200' : 'text-amber-600 dark:text-amber-400' }}">
                                        <span>📌 Pinned</span>
                                        @if($msg->pinned_until)
                                            <span class="opacity-75">({{ $msg->getPinnedTimeRemaining() }})</span>
                                        @endif
                                    </div>
                                @endif

                                <!-- Message Body / Attachments or Deleted State -->
                                @if($msg->is_deleted)
                                    <div class="flex items-center gap-1.5 py-0.5 italic {{ $isMe ? 'text-amber-100/90' : 'text-gray-500 dark:text-gray-400' }}">
                                        <svg class="h-3.5 w-3.5 opacity-70 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                        </svg>
                                        <span class="text-xs">This message was deleted</span>
                                    </div>
                                @else
                                    @if($msg->isImage())
                                        <div class="relative group/attachment my-1 overflow-hidden rounded-lg">
                                            <a href="{{ $msg->getAttachmentUrl() }}" target="_blank">
                                                <img src="{{ $msg->getAttachmentUrl() }}" class="max-h-48 max-w-full rounded-lg object-cover hover:opacity-95 transition" />
                                            </a>
                                            @if($canDelete)
                                                <button
                                                    type="button"
                                                    wire:confirm="Delete this image attachment? It will be replaced with 'This message was deleted'."
                                                    wire:click="deleteMessage({{ $msg->id }})"
                                                    class="absolute top-1.5 right-1.5 flex items-center gap-1 rounded-md bg-black/60 px-1.5 py-0.5 text-[10px] font-bold text-white shadow backdrop-blur hover:bg-red-600 transition"
                                                    title="Delete image (valid for 15 mins or admin)"
                                                >
                                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                    <span>Delete</span>
                                                </button>
                                            @endif
                                        </div>
                                    @elseif($msg->isAudio())
                                        <!-- Modern Waveform Voice Note Player -->
                                        <div 
                                            x-data="voicePlayer('{{ $msg->getAttachmentUrl() }}', $el)"
                                            data-voice-player
                                            class="my-1 flex items-center gap-2 rounded-xl px-2.5 py-1.5 transition-all {{ $isMe ? 'bg-amber-700/60 text-white' : 'bg-gray-100 dark:bg-gray-700/70 text-gray-900 dark:text-white' }} min-w-[210px] max-w-[250px] select-none"
                                        >
                                            <!-- Play/Pause Button -->
                                            <button
                                                type="button"
                                                @click="togglePlay"
                                                class="flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-full shadow transition-transform active:scale-95 {{ $isMe ? 'bg-white text-amber-700 hover:bg-amber-50' : 'bg-amber-600 text-white hover:bg-amber-500' }}"
                                                title="Play / Pause"
                                            >
                                                <template x-if="!isPlaying">
                                                    <svg class="h-3.5 w-3.5 ml-0.5" fill="currentColor" viewBox="0 0 24 24">
                                                        <path d="M8 5v14l11-7z"/>
                                                    </svg>
                                                </template>
                                                <template x-if="isPlaying">
                                                    <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 24 24">
                                                        <path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/>
                                                    </svg>
                                                </template>
                                            </button>

                                            <!-- Waveform & Info -->
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-center gap-[2px] h-5 cursor-pointer py-0.5" @click="handleBarClick($event)">
                                                    <template x-for="(barHeight, idx) in bars" :key="idx">
                                                        <div 
                                                            class="w-[2.5px] rounded-full transition-all duration-75"
                                                            :style="`height: ${barHeight}px;`"
                                                            :class="(idx / (bars.length - 1)) <= (progress / 100) 
                                                                ? '{{ $isMe ? 'bg-white' : 'bg-amber-600 dark:bg-amber-400' }}' 
                                                                : '{{ $isMe ? 'bg-amber-300/40' : 'bg-gray-300 dark:bg-gray-500' }}'"
                                                        ></div>
                                                    </template>
                                                </div>

                                                <div class="flex items-center justify-between text-[9px] {{ $isMe ? 'text-amber-100/90' : 'text-gray-500 dark:text-gray-400' }} font-mono">
                                                    <span x-text="formatSecs(isPlaying ? currentTime : (duration || currentTime))">0:00</span>
                                                    <button 
                                                        type="button" 
                                                        @click="cycleSpeed" 
                                                        class="rounded px-0.5 text-[8px] font-bold uppercase transition hover:bg-black/10 dark:hover:bg-white/10"
                                                        x-text="speed + 'x'"
                                                    >
                                                        1x
                                                    </button>
                                                </div>
                                            </div>

                                            @if($canDelete)
                                                <button
                                                    type="button"
                                                    wire:confirm="Delete this voice message? It will be replaced with 'This message was deleted'."
                                                    wire:click="deleteMessage({{ $msg->id }})"
                                                    class="rounded p-1 transition {{ $isMe ? 'text-amber-200 hover:text-white hover:bg-white/10' : 'text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-950/40' }}"
                                                    title="Delete voice note (valid for 15 mins or admin)"
                                                >
                                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            @endif
                                        </div>
                                    @elseif($msg->isFile())
                                        <div class="my-1 flex items-center justify-between gap-2 rounded-lg p-1.5 {{ $isMe ? 'bg-amber-700/50 text-white' : 'bg-gray-100 dark:bg-gray-700/60 text-gray-900 dark:text-white' }}">
                                            <div class="flex items-center gap-1.5 min-w-0">
                                                <svg class="h-4 w-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                </svg>
                                                <span class="truncate text-[11px] font-medium">{{ $msg->attachment_name }}</span>
                                            </div>
                                            <div class="flex items-center gap-1 flex-shrink-0">
                                                <a href="{{ $msg->getAttachmentUrl() }}" download="{{ $msg->attachment_name }}" class="p-1 hover:opacity-80" title="Download">
                                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                                    </svg>
                                                </a>
                                                @if($canDelete)
                                                    <button
                                                        type="button"
                                                        wire:confirm="Delete this file attachment? It will be replaced with 'This message was deleted'."
                                                        wire:click="deleteMessage({{ $msg->id }})"
                                                        class="p-1 text-red-500 hover:opacity-80"
                                                        title="Delete file (valid for 15 mins or admin)"
                                                    >
                                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                        </svg>
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    @endif

                                    @if($msg->body && !$msg->is_deleted && !($msg->isImage() && $msg->body === $msg->attachment_name) && !($msg->isAudio() && $msg->body === 'Voice message') && !($msg->isFile() && $msg->body === $msg->attachment_name))
                                        <p class="whitespace-pre-wrap break-words leading-relaxed">{!! $msg->getFormattedBodyHtml($isMe) !!}</p>
                                    @endif
                                @endif

                                <div class="mt-0.5 flex items-center justify-end gap-1 text-[9px] {{ $isMe ? 'text-amber-100/75' : 'text-gray-400' }}">
                                    <span>{{ $msg->created_at?->format('h:i A') }}</span>
                                    @if($isMe && !$msg->is_deleted)
                                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    @endif
                                </div>
                            </div>

                            <!-- Message Actions (Pin, Delete) -->
                            @if(!$msg->is_deleted)
                                <div class="opacity-0 group-hover:opacity-100 transition-opacity flex items-center gap-0.5 self-center">
                                    <!-- Pin Message Button -->
                                    @if($isPinned)
                                        <button
                                            type="button"
                                            wire:click="unpinMessage({{ $msg->id }})"
                                            class="rounded p-1 text-amber-600 hover:bg-amber-100 dark:text-amber-400 dark:hover:bg-gray-800"
                                            title="Unpin message"
                                        >
                                            <span class="text-xs">📌</span>
                                        </button>
                                    @else
                                        <button
                                            type="button"
                                            wire:click="openPinMessageModal({{ $msg->id }})"
                                            class="rounded p-1 text-gray-400 hover:text-amber-600 hover:bg-gray-100 dark:hover:bg-gray-800"
                                            title="Pin message on top"
                                        >
                                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                                            </svg>
                                        </button>
                                    @endif

                                    <!-- Delete Message Button (valid only within 15 mins) -->
                                    @if($canDelete)
                                        <button
                                            type="button"
                                            wire:confirm="Delete this message? It will be replaced with 'This message was deleted'."
                                            wire:click="deleteMessage({{ $msg->id }})"
                                            class="rounded p-1 text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-950/40"
                                            title="Delete message (valid for 15 mins or admin)"
                                        >
                                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                            @endif
                        </div>

                        <!-- Floating Quick Reaction Bar (on hover) -->
                        @if(!$msg->is_deleted)
                            <div 
                                x-data="{ showExtraEmojis: false }"
                                class="absolute -top-3 {{ $isMe ? 'right-3' : 'left-8' }} z-20 opacity-0 group-hover:opacity-100 pointer-events-none group-hover:pointer-events-auto transition-opacity duration-150 flex items-center gap-0.5 rounded-full border border-gray-200 bg-white/95 px-1 py-0.5 shadow-md backdrop-blur dark:border-gray-700 dark:bg-gray-800/95"
                            >
                                @php
                                    $quickEmojis = ['👍', '❤️', '😂', '😮', '😢', '🔥'];
                                    $extraEmojis = ['🎉', '👏', '🙏', '💯', '🚀', '👀'];
                                @endphp
                                @foreach($quickEmojis as $qEmoji)
                                    <button
                                        type="button"
                                        wire:click="toggleReaction({{ $msg->id }}, '{{ $qEmoji }}')"
                                        class="rounded-full p-0.5 text-xs transition-transform hover:scale-125 active:scale-95"
                                        title="React {{ $qEmoji }}"
                                    >
                                        {{ $qEmoji }}
                                    </button>
                                @endforeach

                                <div class="relative">
                                    <button
                                        type="button"
                                        @click="showExtraEmojis = !showExtraEmojis"
                                        class="rounded-full p-0.5 text-[10px] text-gray-500 hover:text-amber-600 dark:text-gray-400 dark:hover:text-amber-400 transition"
                                        title="More emojis"
                                    >
                                        ➕
                                    </button>

                                    <div
                                        x-show="showExtraEmojis"
                                        x-transition
                                        @click.outside="showExtraEmojis = false"
                                        class="absolute bottom-full mb-1 {{ $isMe ? 'right-0' : 'left-0' }} flex items-center gap-0.5 rounded-full border border-gray-200 bg-white/95 p-1 shadow-xl backdrop-blur dark:border-gray-700 dark:bg-gray-800/95 z-30"
                                        style="display: none;"
                                    >
                                        @foreach($extraEmojis as $eEmoji)
                                            <button
                                                type="button"
                                                wire:click="toggleReaction({{ $msg->id }}, '{{ $eEmoji }}')"
                                                @click="showExtraEmojis = false"
                                                class="rounded-full p-0.5 text-xs transition-transform hover:scale-125 active:scale-95"
                                                title="React {{ $eEmoji }}"
                                            >
                                                {{ $eEmoji }}
                                            </button>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Reaction Summary Pills -->
                        @if(!$msg->is_deleted)
                            @php $reactionsSummary = $msg->getReactionsSummary(auth()->id()); @endphp
                            @if(!empty($reactionsSummary))
                                <div class="mt-0.5 flex flex-wrap items-center gap-1 {{ $isMe ? 'justify-end pr-1' : 'justify-start pl-7' }}">
                                    @foreach($reactionsSummary as $rec)
                                        <button
                                            type="button"
                                            wire:click="toggleReaction({{ $msg->id }}, '{{ $rec['reaction'] }}')"
                                            class="inline-flex items-center gap-0.5 rounded-full px-1.5 py-0.2 text-[10px] transition-all shadow-xs cursor-pointer {{ $rec['reacted_by_me'] ? 'bg-amber-100 text-amber-900 border border-amber-300 dark:bg-amber-950/60 dark:text-amber-300 dark:border-amber-700/80 font-bold ring-1 ring-amber-400/40' : 'bg-white text-gray-700 border border-gray-200 hover:bg-gray-100 dark:bg-gray-800 dark:text-gray-300 dark:border-gray-700 dark:hover:bg-gray-700' }}"
                                            title="{{ implode(', ', $rec['users']) }} reacted"
                                        >
                                            <span class="text-xs leading-none">{{ $rec['reaction'] }}</span>
                                            <span class="text-[10px] font-semibold">{{ $rec['count'] }}</span>
                                        </button>
                                    @endforeach
                                </div>
                            @endif
                        @endif
                    </div>
                @empty
                    <div class="flex h-full items-center justify-center text-center p-4 text-gray-400 text-xs">
                        @if(filled($messageSearch))
                            No messages matching "{{ $messageSearch }}"
                        @else
                            No messages yet. Say hi!
                        @endif
                    </div>
                @endforelse
            </div>

            @php $isFloatingGroup = $this->activeConversation->isGroup(); @endphp
            <!-- Mini Input Bar Area -->
            <div 
                x-data="{
                    showMentionMenu: false,
                    mentionQuery: '',
                    mentionIndex: 0,
                    mentionStartIndex: 0,
                    mentionCursorPos: 0,
                    members: @js($this->groupMembers),
                    isGroup: {{ $isFloatingGroup ? 'true' : 'false' }},

                    get filteredMembers() {
                        if (!this.isGroup) return [];
                        const q = (this.mentionQuery || '').toLowerCase().trim();
                        const list = [];
                        if (q === '' || 'all'.includes(q) || 'everyone'.includes(q)) {
                            list.push({
                                id: 'all',
                                name: 'Everyone in this group',
                                username: 'all',
                                tag: 'all',
                                isAll: true,
                            });
                        }
                        this.members.forEach(m => {
                            if (
                                q === '' ||
                                (m.name && m.name.toLowerCase().includes(q)) ||
                                (m.username && m.username.toLowerCase().includes(q)) ||
                                (m.tag && m.tag.toLowerCase().includes(q))
                            ) {
                                list.push(m);
                            }
                        });
                        return list;
                    },

                    detectMention() {
                        if (!this.isGroup) {
                            this.showMentionMenu = false;
                            return;
                        }
                        const input = this.$refs.floatMessageInput;
                        if (!input) return;
                        const text = input.value || '';
                        const pos = input.selectionStart ?? text.length;
                        const upToCursor = text.slice(0, pos);
                        const atIndex = upToCursor.lastIndexOf('@');
                        if (atIndex === -1) {
                            this.showMentionMenu = false;
                            return;
                        }
                        if (atIndex > 0 && !/\s/.test(text[atIndex - 1])) {
                            this.showMentionMenu = false;
                            return;
                        }
                        const query = upToCursor.slice(atIndex + 1);
                        if (/\s/.test(query)) {
                            this.showMentionMenu = false;
                            return;
                        }
                        this.mentionQuery = query;
                        this.mentionStartIndex = atIndex;
                        this.mentionCursorPos = pos;
                        this.showMentionMenu = true;
                        this.mentionIndex = 0;
                    },

                    selectMention(item) {
                        const input = this.$refs.floatMessageInput;
                        if (!input) return;
                        const text = input.value || '';
                        const atIndex = this.mentionStartIndex;
                        const cursorPos = this.mentionCursorPos;

                        const before = text.slice(0, atIndex);
                        const after = text.slice(cursorPos);
                        const tag = '@' + item.tag + ' ';
                        const newText = before + tag + after;

                        input.value = newText;
                        input.dispatchEvent(new Event('input', { bubbles: true }));
                        $wire.set('messageText', newText);
                        this.showMentionMenu = false;

                        this.$nextTick(() => {
                            input.focus();
                            const newPos = before.length + tag.length;
                            input.setSelectionRange(newPos, newPos);
                        });
                    },

                    openMentionMenu() {
                        if (!this.isGroup) return;
                        const input = this.$refs.floatMessageInput;
                        if (!input) return;
                        let text = input.value || '';
                        if (text.length > 0 && !text.endsWith(' ')) {
                            text += ' ';
                        }
                        text += '@';
                        input.value = text;
                        input.dispatchEvent(new Event('input', { bubbles: true }));
                        $wire.set('messageText', text);
                        this.$nextTick(() => {
                            input.focus();
                            input.setSelectionRange(text.length, text.length);
                            this.detectMention();
                        });
                    },

                    handleFloatInputKeyDown(e) {
                        if (this.showMentionMenu && this.filteredMembers.length > 0) {
                            if (e.key === 'ArrowDown') {
                                e.preventDefault();
                                this.mentionIndex = (this.mentionIndex + 1) % this.filteredMembers.length;
                                return;
                            }
                            if (e.key === 'ArrowUp') {
                                e.preventDefault();
                                this.mentionIndex = (this.mentionIndex - 1 + this.filteredMembers.length) % this.filteredMembers.length;
                                return;
                            }
                            if (e.key === 'Enter' || e.key === 'Tab') {
                                e.preventDefault();
                                this.selectMention(this.filteredMembers[this.mentionIndex]);
                                return;
                            }
                            if (e.key === 'Escape') {
                                e.preventDefault();
                                this.showMentionMenu = false;
                                return;
                            }
                        }

                        if (e.key === 'Enter' && !e.shiftKey) {
                            e.preventDefault();
                            this.showMentionMenu = false;
                            if ($wire.messageText.trim().length > 0 || $wire.attachment) {
                                $wire.sendMessage();
                            }
                        }
                    }
                }"
                class="relative border-t border-gray-100 bg-white p-2.5 dark:border-gray-800 dark:bg-gray-900"
            >
                <!-- Mention Dropdown Popover in Floating Widget -->
                @if($isFloatingGroup)
                    <div
                        x-show="showMentionMenu && filteredMembers.length > 0"
                        x-transition:enter="transition ease-out duration-150"
                        x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                        x-transition:leave="transition ease-in duration-100"
                        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                        x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                        @click.outside="showMentionMenu = false"
                        class="absolute bottom-full left-2 right-2 mb-2 max-h-52 overflow-y-auto rounded-2xl border border-gray-200 bg-white/95 p-1 shadow-2xl backdrop-blur-md dark:border-gray-700 dark:bg-gray-800/95 z-50 divide-y divide-gray-100 dark:divide-gray-700"
                        style="display: none;"
                    >
                        <div class="px-2 py-1 text-[9px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-400 flex items-center justify-between">
                            <span class="flex items-center gap-1">
                                <span class="text-amber-500 font-bold">@</span> Mention Member
                            </span>
                            <span class="text-[8px] font-normal lowercase opacity-70">↑↓ / ↵</span>
                        </div>

                        <div class="py-0.5 space-y-0.5">
                            <template x-for="(member, idx) in filteredMembers" :key="member.id">
                                <button
                                    type="button"
                                    @mousedown.prevent="selectMention(member)"
                                    @mouseenter="mentionIndex = idx"
                                    class="flex w-full items-center gap-2 rounded-xl px-2 py-1.5 text-left text-xs transition-colors"
                                    :class="mentionIndex === idx 
                                        ? 'bg-amber-500/15 text-amber-900 dark:bg-amber-400/20 dark:text-amber-200 ring-1 ring-amber-400/30' 
                                        : 'text-gray-800 hover:bg-gray-100/80 dark:text-gray-200 dark:hover:bg-gray-700/80'"
                                >
                                    <template x-if="member.isAll">
                                        <div class="flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full bg-amber-500 text-white font-bold text-[10px] shadow-sm">
                                            📢
                                        </div>
                                    </template>
                                    <template x-if="!member.isAll && member.avatar">
                                        <img :src="member.avatar" class="h-6 w-6 flex-shrink-0 rounded-full object-cover border border-gray-200 dark:border-gray-700" />
                                    </template>
                                    <template x-if="!member.isAll && !member.avatar">
                                        <div class="flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full bg-amber-100 text-amber-800 dark:bg-gray-700 dark:text-amber-300 font-bold text-[10px]">
                                            <span x-text="member.initial"></span>
                                        </div>
                                    </template>

                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-1">
                                            <span class="truncate font-semibold text-[11px]" x-text="member.name"></span>
                                            <template x-if="member.isAll">
                                                <span class="rounded bg-amber-500/20 px-1 py-0.2 text-[8px] font-bold text-amber-800 dark:bg-amber-400/20 dark:text-amber-300">
                                                    ALL
                                                </span>
                                            </template>
                                        </div>
                                        <div class="text-[10px] text-gray-500 dark:text-gray-400 truncate">
                                            <span class="text-amber-600 dark:text-amber-400 font-medium">@<span x-text="member.tag"></span></span>
                                        </div>
                                    </div>
                                </button>
                            </template>
                        </div>
                    </div>
                @endif

                <!-- Attachment Preview Chip -->
                @if($attachment)
                    <div class="mb-2 flex items-center justify-between rounded-xl bg-amber-50 p-1.5 text-xs border border-amber-200 dark:bg-gray-800 dark:border-gray-700">
                        <div class="flex items-center gap-1.5 min-w-0">
                            <svg class="h-3.5 w-3.5 text-amber-600 dark:text-amber-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                            </svg>
                            <span class="truncate text-[11px] font-medium text-gray-900 dark:text-white">{{ $attachment->getClientOriginalName() }}</span>
                        </div>
                        <button
                            type="button"
                            wire:click="removeAttachment"
                            class="text-gray-400 hover:text-red-500"
                        >
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                @endif

                @if($isFloatingGroup && !$this->activeConversation->canUserSendMessage(auth()->id()))
                    <div class="flex items-center justify-center gap-1.5 rounded-xl bg-amber-50 p-2.5 text-[11px] text-amber-800 dark:bg-amber-950/40 dark:text-amber-300 border border-amber-200 dark:border-amber-900/40">
                        <svg class="h-3.5 w-3.5 text-amber-600 dark:text-amber-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                        <span class="font-medium">Only group admins can send messages.</span>
                    </div>
                @else
                    <!-- Live Waveform Recording Bar -->
                    <div
                        x-show="isRecording"
                        class="rounded-xl border p-2 transition-all"
                        :class="isRecordingEnded
                            ? 'border-emerald-300 bg-emerald-50/80 dark:border-emerald-800/60 dark:bg-emerald-950/30'
                            : (recordingTime >= maxRecordingSeconds - 10
                                ? 'border-red-300 bg-red-50/80 dark:border-red-800/60 dark:bg-red-950/30'
                                : 'border-red-200/80 bg-gradient-to-r from-red-500/8 via-transparent to-amber-500/8 dark:border-red-900/60 dark:bg-red-950/20')"
                        style="display: none;"
                    >
                        <!-- Top row: dot + live bars + time + countdown -->
                        <div class="flex items-center gap-2">
                            <!-- Pulsing dot (pauses when paused, steady emerald when ended) -->
                            <div class="relative flex h-3 w-3 flex-shrink-0 items-center justify-center">
                                <template x-if="!isRecordingEnded && !isPaused">
                                    <span class="absolute inline-flex h-full w-full rounded-full opacity-75 bg-red-400 animate-ping"></span>
                                </template>
                                <span
                                    class="relative inline-flex h-2 w-2 rounded-full"
                                    :class="isRecordingEnded 
                                        ? 'bg-emerald-500' 
                                        : (isPaused ? 'bg-amber-500' : 'bg-red-600')"
                                ></span>
                            </div>

                            <!-- Live microphone waveform bars -->
                            <div class="flex flex-1 items-center gap-[1.5px] h-7 overflow-hidden">
                                <template x-for="(h, i) in vizBars" :key="i">
                                    <div
                                        class="w-[3px] rounded-full transition-all duration-75"
                                        :style="`height: ${h}px;`"
                                        :class="isRecordingEnded
                                            ? 'bg-emerald-500/80 dark:bg-emerald-400'
                                            : (isPaused
                                                ? 'bg-amber-400/70'
                                                : (recordingTime >= maxRecordingSeconds - 10 ? 'bg-red-500' : 'bg-red-400'))"
                                    ></div>
                                </template>
                            </div>

                            <!-- Timer -->
                            <span
                                class="font-mono text-xs font-bold tabular-nums px-1.5 py-0.5 rounded"
                                :class="isRecordingEnded
                                    ? 'text-emerald-700 bg-emerald-100 dark:text-emerald-300 dark:bg-emerald-900/50'
                                    : (recordingTime >= maxRecordingSeconds - 10
                                        ? 'text-red-700 bg-red-100 dark:text-red-300 dark:bg-red-900/50'
                                        : 'text-red-600 bg-red-100/60 dark:text-red-300 dark:bg-red-900/40')"
                                x-text="isRecordingEnded ? '1:00 (Ready)' : (formatTime(recordingTime) + ' / 1:00')"
                            >00:00 / 1:00</span>
                        </div>

                        <!-- Progress bar for 60s limit -->
                        <div class="mt-1.5 h-0.5 w-full overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                            <div
                                class="h-full rounded-full transition-all duration-1000"
                                :class="isRecordingEnded ? 'bg-emerald-500' : (recordingTime >= maxRecordingSeconds - 10 ? 'bg-red-500' : 'bg-amber-500')"
                                :style="`width: ${isRecordingEnded ? 100 : ((recordingTime / maxRecordingSeconds) * 100)}%`"
                            ></div>
                        </div>

                        <!-- Action buttons row -->
                        <div class="mt-2 flex items-center justify-between gap-1.5">
                            <!-- Discard -->
                            <button
                                type="button"
                                @click="cancelRecording"
                                :disabled="isUploadingVoice"
                                class="flex items-center gap-1 rounded-lg border border-red-200 bg-white/80 px-2 py-1 text-[11px] font-semibold text-red-600 hover:bg-red-50 hover:border-red-300 dark:border-red-900/60 dark:bg-gray-800 dark:text-red-400 transition disabled:opacity-50"
                                title="Discard recording"
                            >
                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                Discard
                            </button>

                            <div class="flex items-center gap-1.5">
                                <!-- Pause / Resume (hidden when recording ended) -->
                                <button
                                    type="button"
                                    x-show="!isRecordingEnded"
                                    @click="togglePauseRecording"
                                    class="flex h-7 w-7 items-center justify-center rounded-full border border-gray-300 bg-white text-gray-600 shadow-sm hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 transition active:scale-95"
                                    :title="isPaused ? 'Resume recording' : 'Pause recording'"
                                >
                                    <template x-if="!isPaused">
                                        <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/>
                                        </svg>
                                    </template>
                                    <template x-if="isPaused">
                                        <svg class="h-3 w-3 ml-0.5" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M8 5v14l11-7z"/>
                                        </svg>
                                    </template>
                                </button>

                                <!-- Send -->
                                <button
                                    type="button"
                                    @click="stopAndSendRecording"
                                    :disabled="isUploadingVoice"
                                    class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1 text-xs font-bold text-white shadow transition-transform active:scale-95 disabled:opacity-75 disabled:cursor-not-allowed"
                                    :class="isRecordingEnded
                                        ? 'bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500'
                                        : 'bg-gradient-to-r from-red-600 to-amber-600 hover:from-red-500 hover:to-amber-500'"
                                    title="Send voice note"
                                >
                                    <template x-if="isUploadingVoice">
                                        <svg class="h-3.5 w-3.5 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                    </template>
                                    <template x-if="!isUploadingVoice">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                        </svg>
                                    </template>
                                    <span x-text="isUploadingVoice ? 'Sending...' : 'Send'">Send</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Normal Input Form -->
                    <form x-show="!isRecording" wire:submit.prevent="sendMessage" class="flex items-center gap-1.5">
                        <!-- File input hidden -->
                        <input
                            type="file"
                            x-ref="floatFileInput"
                            wire:model="attachment"
                            class="hidden"
                        />

                        <!-- Paperclip File Button -->
                        <button
                            type="button"
                            @click="$refs.floatFileInput.click()"
                            class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800 dark:hover:text-gray-200 transition"
                            title="Attach file or photo"
                        >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                            </svg>
                        </button>

                        <!-- Microphone Voice Note Button -->
                        <button
                            type="button"
                            @click="startRecording"
                            class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-amber-600 dark:hover:bg-gray-800 dark:hover:text-amber-400 transition"
                            title="Record voice message"
                        >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>
                            </svg>
                        </button>

                        @if($isFloatingGroup)
                            <!-- Quick Mention Button (@) -->
                            <button
                                type="button"
                                @click="openMentionMenu"
                                class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-amber-600 dark:hover:bg-gray-800 dark:hover:text-amber-400 transition"
                                title="Mention group member (@all or @member)"
                            >
                                <span class="flex h-4 w-4 items-center justify-center text-xs font-black leading-none select-none">@</span>
                            </button>
                        @endif

                        <!-- Input Field (Dark Mode bullet-proof) -->
                        <input
                            type="text"
                            x-ref="floatMessageInput"
                            wire:model="messageText"
                            @input="detectMention"
                            @click="detectMention"
                            @keyup="detectMention"
                            @keydown="handleFloatInputKeyDown"
                            placeholder="{{ $isFloatingGroup ? 'Write a message... (Type @ to mention)' : 'Write a message...' }}"
                            class="chat-input-field w-full rounded-xl border border-gray-300 bg-white px-3 py-1.5 text-xs text-gray-900 placeholder-gray-400 focus:border-amber-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400"
                        />

                        <!-- Send Button -->
                        <button
                            type="submit"
                            class="inline-flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-xl bg-amber-600 text-white shadow transition hover:bg-amber-500"
                        >
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                            </svg>
                        </button>
                    </form>
                @endif
            </div>
        @elseif($showNewChatModal)
            <!-- New Chat View Inside Floating Widget -->
            <div class="flex items-center justify-between border-b border-gray-100 px-3 py-2 dark:border-gray-800">
                <div class="flex items-center gap-2">
                    <button
                        type="button"
                        wire:click="$set('showNewChatModal', false)"
                        class="rounded p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </button>
                    <span class="text-xs font-bold text-gray-900 dark:text-white">Start New Chat</span>
                </div>
                <div class="flex gap-2">
                    <button
                        type="button"
                        wire:click="$set('modalTab', 'direct')"
                        class="text-[11px] font-semibold {{ $modalTab === 'direct' ? 'text-amber-600 dark:text-amber-400' : 'text-gray-400' }}"
                    >
                        Direct
                    </button>
                    <span class="text-gray-300 dark:text-gray-700">|</span>
                    <button
                        type="button"
                        wire:click="$set('modalTab', 'group')"
                        class="text-[11px] font-semibold {{ $modalTab === 'group' ? 'text-amber-600 dark:text-amber-400' : 'text-gray-400' }}"
                    >
                        Group
                    </button>
                </div>
            </div>

            @if($modalTab === 'group')
                <div class="p-2.5 border-b border-gray-100 dark:border-gray-800 bg-gray-50/60 dark:bg-gray-800/40 space-y-2">
                    <input
                        type="text"
                        wire:model="groupTitle"
                        placeholder="Group name..."
                        class="chat-input-field w-full rounded-xl border border-gray-300 bg-white px-3 py-1.5 text-xs text-gray-900 placeholder-gray-400 focus:border-amber-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                    />
                    <div class="flex items-center justify-between text-[11px] text-gray-500">
                        <span>Selected: <strong>{{ count($selectedGroupMembers) }}</strong></span>
                        @if(!empty($selectedGroupMembers) && filled($groupTitle))
                            <button
                                type="button"
                                wire:click="createGroupChat"
                                class="rounded-lg bg-amber-600 px-2.5 py-1 text-xs font-bold text-white shadow hover:bg-amber-500"
                            >
                                Create Group
                            </button>
                        @endif
                    </div>
                </div>
            @endif

            <div class="p-2 border-b border-gray-100 dark:border-gray-800">
                <input
                    type="text"
                    wire:model.live.debounce.250ms="userSearch"
                    placeholder="Search users..."
                    class="chat-input-field w-full rounded-xl border border-gray-300 bg-white px-3 py-1.5 text-xs text-gray-900 placeholder-gray-400 focus:border-amber-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400"
                />
            </div>

            <div class="flex-1 overflow-y-auto divide-y divide-gray-100 dark:divide-gray-800">
                @forelse($this->availableUsers as $user)
                    @php
                        $isSelected = in_array($user->id, $selectedGroupMembers, true);
                        $avatarUrl = $user->getFilamentAvatarUrl();
                    @endphp
                    <div
                        wire:key="float-user-{{ $user->id }}"
                        wire:click="{{ $modalTab === 'group' ? 'toggleGroupMember(' . $user->id . ')' : 'startConversationWith(' . $user->id . ')' }}"
                        class="flex cursor-pointer items-center justify-between p-2.5 transition hover:bg-amber-50/60 dark:hover:bg-gray-800/60 {{ $isSelected ? 'bg-amber-50 dark:bg-amber-950/40' : '' }}"
                    >
                        <div class="flex items-center gap-2 min-w-0">
                            @if($avatarUrl)
                                <img src="{{ $avatarUrl }}" class="h-7 w-7 rounded-full object-cover flex-shrink-0" />
                            @else
                                <div class="flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-full bg-amber-500/20 text-[10px] font-bold text-amber-700 dark:text-amber-300">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                            @endif
                            <div class="min-w-0">
                                <p class="truncate text-xs font-semibold text-gray-900 dark:text-white">{{ $user->name }}</p>
                                <span class="text-[10px] text-gray-400 truncate block">{{ $user->email }}</span>
                            </div>
                        </div>
                        @if($modalTab === 'group')
                            <input
                                type="checkbox"
                                class="rounded border-gray-300 text-amber-600 focus:ring-amber-500 dark:border-gray-700 dark:bg-gray-800"
                                {{ $isSelected ? 'checked' : '' }}
                                wire:click.stop="toggleGroupMember({{ $user->id }})"
                            />
                        @else
                            <span class="text-[11px] font-semibold text-amber-600 dark:text-amber-400">Chat &rarr;</span>
                        @endif
                    </div>
                @empty
                    <div class="p-6 text-center text-xs text-gray-400">
                        No users found
                    </div>
                @endforelse
            </div>
        @else
            <!-- Conversation List View -->
            <div class="p-2 border-b border-gray-100 dark:border-gray-800 flex items-center gap-2">
                <input
                    type="text"
                    wire:model.live.debounce.250ms="search"
                    placeholder="Search chats..."
                    class="chat-input-field flex-1 rounded-xl border border-gray-300 bg-white px-3 py-1.5 text-xs text-gray-900 placeholder-gray-400 focus:border-amber-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400"
                />
                <!-- New Chat Button -->
                <button
                    type="button"
                    wire:click="$set('showNewChatModal', true)"
                    class="inline-flex items-center gap-1 rounded-xl bg-amber-600 px-2.5 py-1.5 text-xs font-bold text-white shadow hover:bg-amber-500 transition"
                    title="Start new chat"
                >
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>New</span>
                </button>
            </div>
            <div class="flex-1 overflow-y-auto divide-y divide-gray-100 dark:divide-gray-800">
                @php $conversations = $this->getConversations(); @endphp
                @forelse($conversations as $conv)
                    @php
                        $title = $conv->getDisplayName(auth()->id());
                        $avatar = $conv->getDisplayAvatar(auth()->id());
                        $unread = $conv->unreadCountFor(auth()->id());
                        $latest = $conv->latestMessage;
                        $isPinned = $conv->isPinnedFor(auth()->id());
                    @endphp
                    <div
                        wire:key="float-conv-{{ $conv->id }}"
                        wire:click="selectConversation({{ $conv->id }})"
                        class="group flex cursor-pointer items-center gap-2.5 p-2.5 transition hover:bg-amber-50/60 dark:hover:bg-gray-800/60 {{ $isPinned ? 'bg-amber-50/40 dark:bg-amber-950/20' : '' }}"
                    >
                        <div class="relative flex-shrink-0">
                            @if($avatar)
                                <img src="{{ $avatar }}" class="h-9 w-9 rounded-full object-cover flex-shrink-0" />
                            @else
                                <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-full bg-gradient-to-tr {{ $conv->isGroup() ? 'from-indigo-600 to-indigo-400' : 'from-amber-600 to-amber-400' }} text-xs font-bold text-white">
                                    {{ strtoupper(substr($title, 0, 1)) }}
                                </div>
                            @endif
                            @if($isPinned)
                                <span class="absolute -top-1 -left-1 flex h-4 w-4 items-center justify-center rounded-full bg-amber-500 text-[9px] text-white shadow">
                                    📌
                                </span>
                            @endif
                        </div>
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center justify-between">
                                <span class="truncate text-xs font-semibold text-gray-900 dark:text-white flex items-center gap-1">
                                    {{ $title }}
                                </span>
                                @if($latest)
                                    <span class="text-[9px] text-gray-400">{{ $latest->created_at?->diffForHumans(null, true, true) }}</span>
                                @endif
                            </div>
                            <p class="truncate text-[11px] text-gray-500 dark:text-gray-400">
                                @if($latest?->is_deleted)
                                    <span class="italic text-gray-400">This message was deleted</span>
                                @else
                                    {{ $latest?->body ?? 'No messages yet' }}
                                @endif
                            </p>
                        </div>
                        <div class="flex items-center gap-1 flex-shrink-0">
                            <!-- Pin Conversation Toggle -->
                            @if($isPinned)
                                <button
                                    type="button"
                                    wire:click.stop="unpinConversation({{ $conv->id }})"
                                    class="rounded p-1 text-amber-600 hover:text-gray-400"
                                    title="Unpin chat"
                                >
                                    <span class="text-xs">📌</span>
                                </button>
                            @else
                                <button
                                    type="button"
                                    wire:click.stop="openPinConversationModal({{ $conv->id }})"
                                    class="rounded p-1 text-gray-400 hover:text-amber-600 opacity-0 group-hover:opacity-100 transition-opacity"
                                    title="Pin chat to top"
                                >
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                                    </svg>
                                </button>
                            @endif

                            @if($unread > 0)
                                <span class="flex h-4 min-w-4 items-center justify-center rounded-full bg-amber-500 px-1 text-[9px] font-bold text-white">
                                    {{ $unread }}
                                </span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="p-6 text-center text-xs text-gray-400">
                        <p>No conversations found.</p>
                        <button
                            type="button"
                            wire:click="$set('showNewChatModal', true)"
                            class="mt-2 text-xs font-semibold text-amber-600 hover:underline dark:text-amber-400"
                        >
                            + Start a new chat
                        </button>
                    </div>
                @endforelse
            </div>
            <div class="border-t border-gray-100 p-2.5 text-center dark:border-gray-800 bg-gray-50/50 dark:bg-gray-900/50 flex items-center justify-between">
                <button
                    type="button"
                    wire:click="$set('showNewChatModal', true)"
                    class="text-xs font-semibold text-amber-600 hover:underline dark:text-amber-400"
                >
                    + New Conversation
                </button>
                <a
                    href="{{ url('/kamkaj/chat') }}"
                    class="inline-flex items-center gap-1 text-xs font-semibold text-gray-500 hover:text-amber-600 dark:text-gray-400 dark:hover:text-amber-400"
                >
                    Full View &rarr;
                </a>
            </div>
        @endif
    </div>

    <!-- Duration Picker Modal for Pinning (Message or Conversation) -->
    @if($pinningMessageId || $pinningConversationId)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 p-4 backdrop-blur-sm">
            <div
                @click.outside="$wire.closePinMessageModal(); $wire.closePinConversationModal();"
                class="relative w-full max-w-xs overflow-hidden rounded-2xl border border-gray-200 bg-white p-4 shadow-2xl dark:border-gray-800 dark:bg-gray-900 animate-in fade-in zoom-in-95 duration-150"
            >
                <div class="flex items-center justify-between border-b border-gray-100 pb-2.5 dark:border-gray-800">
                    <div class="flex items-center gap-1.5">
                        <span class="text-base">📌</span>
                        <h4 class="text-xs font-bold text-gray-900 dark:text-white">
                            {{ $pinningMessageId ? 'Pin Message' : 'Pin Conversation' }}
                        </h4>
                    </div>
                    <button
                        type="button"
                        wire:click="{{ $pinningMessageId ? 'closePinMessageModal' : 'closePinConversationModal' }}"
                        class="rounded-lg p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <p class="py-2 text-[11px] text-gray-500 dark:text-gray-400">
                    Select pinning duration:
                </p>

                <div class="grid grid-cols-2 gap-2 pt-1">
                    @php
                        $durations = [
                            ['label' => '8 Hours', 'val' => '8_hours', 'icon' => '⏱️'],
                            ['label' => '1 Day', 'val' => '1_day', 'icon' => '📅'],
                            ['label' => '1 Week', 'val' => '1_week', 'icon' => '🗓️'],
                            ['label' => '1 Month', 'val' => '1_month', 'icon' => '📆'],
                            ['label' => '1 Year', 'val' => '1_year', 'icon' => '🎂'],
                            ['label' => 'Never Ending', 'val' => 'never_ending', 'icon' => '♾️'],
                        ];
                    @endphp
                    @foreach($durations as $d)
                        <button
                            type="button"
                            wire:click="{{ $pinningMessageId ? 'pinMessage(' . $pinningMessageId . ', \'' . $d['val'] . '\')' : 'pinConversation(' . $pinningConversationId . ', \'' . $d['val'] . '\')' }}"
                            class="flex items-center gap-2 rounded-xl border border-gray-200 p-2 text-left text-xs font-semibold text-gray-700 transition hover:border-amber-500 hover:bg-amber-50 hover:text-amber-700 dark:border-gray-700 dark:text-gray-200 dark:hover:border-amber-500 dark:hover:bg-amber-950/30 dark:hover:text-amber-300"
                        >
                            <span>{{ $d['icon'] }}</span>
                            <span class="truncate">{{ $d['label'] }}</span>
                        </button>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- Group Settings & Management Modal (Floating) -->
    @if($showGroupSettingsModal && $this->activeConversation && $this->activeConversation->isGroup())
        @php
            $isOwner = $this->activeConversation->isOwner(auth()->id());
            $isAdmin = $this->activeConversation->isAdmin(auth()->id());
            $canEditInfo = $this->activeConversation->canUserEditInfo(auth()->id());
            $participants = $this->groupParticipantDetails;
        @endphp
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 p-3 backdrop-blur-sm animate-in fade-in duration-150">
            <div
                @click.outside="$wire.set('showGroupSettingsModal', false)"
                class="relative w-full max-w-sm overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xl dark:border-gray-800 dark:bg-gray-900 flex flex-col max-h-[85vh]"
            >
                <!-- Modal Header -->
                <div class="flex items-center justify-between border-b border-gray-100 px-4 py-3 dark:border-gray-800">
                    <div class="flex items-center gap-2">
                        <span class="text-sm">⚙️</span>
                        <div>
                            <h4 class="text-xs font-bold text-gray-900 dark:text-white">Group Settings</h4>
                            <p class="text-[10px] text-gray-500 dark:text-gray-400 truncate max-w-[180px]">{{ $this->activeConversation->title }}</p>
                        </div>
                    </div>
                    <button
                        type="button"
                        wire:click="$set('showGroupSettingsModal', false)"
                        class="rounded-lg p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <!-- Navigation Tabs -->
                <div class="flex border-b border-gray-100 px-3 gap-2 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-950/40 text-[11px] font-semibold">
                    <button
                        type="button"
                        wire:click="$set('groupSettingsTab', 'general')"
                        class="py-2 border-b-2 transition {{ $groupSettingsTab === 'general' ? 'border-amber-600 text-amber-600 dark:border-amber-400 dark:text-amber-400' : 'border-transparent text-gray-500 hover:text-gray-800 dark:text-gray-400' }}"
                    >
                        General
                    </button>
                    <button
                        type="button"
                        wire:click="$set('groupSettingsTab', 'members')"
                        class="py-2 border-b-2 transition flex items-center gap-1 {{ $groupSettingsTab === 'members' ? 'border-amber-600 text-amber-600 dark:border-amber-400 dark:text-amber-400' : 'border-transparent text-gray-500 hover:text-gray-800 dark:text-gray-400' }}"
                    >
                        <span>Members</span>
                        <span class="rounded bg-gray-200 px-1 text-[9px] dark:bg-gray-800">{{ $participants->count() }}</span>
                    </button>
                    <button
                        type="button"
                        wire:click="$set('groupSettingsTab', 'permissions')"
                        class="py-2 border-b-2 transition {{ $groupSettingsTab === 'permissions' ? 'border-amber-600 text-amber-600 dark:border-amber-400 dark:text-amber-400' : 'border-transparent text-gray-500 hover:text-gray-800 dark:text-gray-400' }}"
                    >
                        Rules
                    </button>
                    <button
                        type="button"
                        wire:click="$set('groupSettingsTab', 'danger')"
                        class="py-2 border-b-2 transition text-red-600 dark:text-red-400 {{ $groupSettingsTab === 'danger' ? 'border-red-600 font-bold' : 'border-transparent' }}"
                    >
                        Actions
                    </button>
                </div>

                <!-- Tab Content Body -->
                <div class="flex-1 overflow-y-auto p-4 space-y-3.5">
                    <!-- GENERAL TAB -->
                    @if($groupSettingsTab === 'general')
                        @if(!$canEditInfo)
                            <div class="rounded-xl bg-amber-50 p-2.5 text-[11px] text-amber-800 dark:bg-amber-950/40 dark:text-amber-300 border border-amber-200 dark:border-amber-900/40">
                                ℹ️ Only group admins can change the group name, icon, and description.
                            </div>
                        @endif

                        <!-- Group Photo -->
                        <div class="flex items-center gap-3">
                            <div class="relative flex-shrink-0">
                                @if($settingsGroupAvatar)
                                    <img src="{{ $settingsGroupAvatar->temporaryUrl() }}" class="h-12 w-12 rounded-full object-cover border-2 border-amber-500 shadow-md" />
                                @elseif($this->activeConversation->avatar_url)
                                    <img src="{{ Storage::url($this->activeConversation->avatar_url) }}" class="h-12 w-12 rounded-full object-cover border-2 border-gray-200 dark:border-gray-700 shadow-md" />
                                @else
                                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gradient-to-tr from-amber-600 to-amber-400 text-base font-bold text-white shadow-md">
                                        {{ strtoupper(substr($this->activeConversation->title, 0, 1)) }}
                                    </div>
                                @endif
                            </div>

                            @if($canEditInfo)
                                <div class="space-y-1">
                                    <div class="flex items-center gap-1.5">
                                        <label class="cursor-pointer rounded-lg bg-gray-100 px-2.5 py-1 text-[11px] font-semibold text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300">
                                            <span>Upload</span>
                                            <input type="file" wire:model="settingsGroupAvatar" accept="image/*" class="hidden" />
                                        </label>
                                        @if($this->activeConversation->avatar_url || $settingsGroupAvatar)
                                            <button
                                                type="button"
                                                wire:click="removeGroupAvatar"
                                                class="rounded-lg px-2 py-1 text-[11px] font-semibold text-red-600 hover:bg-red-50 dark:hover:bg-red-950/40"
                                            >
                                                Remove
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>

                        <!-- Group Title -->
                        <div class="space-y-1">
                            <label class="block text-[11px] font-semibold text-gray-700 dark:text-gray-300">Group Name</label>
                            <input
                                type="text"
                                wire:model="settingsGroupTitle"
                                {{ $canEditInfo ? '' : 'disabled' }}
                                class="chat-input-field w-full rounded-xl border border-gray-300 bg-white px-3 py-1.5 text-xs text-gray-900 placeholder-gray-400 focus:border-amber-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white disabled:opacity-60"
                            />
                            @error('settingsGroupTitle') <span class="text-[10px] text-red-600">{{ $message }}</span> @enderror
                        </div>

                        <!-- Group Description -->
                        <div class="space-y-1">
                            <label class="block text-[11px] font-semibold text-gray-700 dark:text-gray-300">Description</label>
                            <textarea
                                wire:model="settingsGroupDescription"
                                rows="2"
                                {{ $canEditInfo ? '' : 'disabled' }}
                                placeholder="Group description..."
                                class="chat-input-field w-full rounded-xl border border-gray-300 bg-white px-3 py-1.5 text-xs text-gray-900 placeholder-gray-400 focus:border-amber-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white disabled:opacity-60 resize-none"
                            ></textarea>
                            @error('settingsGroupDescription') <span class="text-[10px] text-red-600">{{ $message }}</span> @enderror
                        </div>

                        @if($canEditInfo)
                            <div class="flex justify-end pt-1">
                                <button
                                    type="button"
                                    wire:click="saveGroupSettings"
                                    class="rounded-xl bg-amber-600 px-3.5 py-1.5 text-xs font-bold text-white shadow hover:bg-amber-500"
                                >
                                    Save Changes
                                </button>
                            </div>
                        @endif
                    @endif

                    <!-- MEMBERS TAB -->
                    @if($groupSettingsTab === 'members')
                        <div class="flex items-center justify-between pb-1">
                            <span class="text-[11px] font-bold text-gray-700 dark:text-gray-300">Members ({{ $participants->count() }})</span>
                            @if($isAdmin)
                                <button
                                    type="button"
                                    wire:click="openAddMembersModal"
                                    class="inline-flex items-center gap-1 rounded-lg bg-amber-600 px-2.5 py-1 text-[11px] font-semibold text-white shadow hover:bg-amber-500"
                                >
                                    <span>+ Add</span>
                                </button>
                            @endif
                        </div>

                        <div class="divide-y divide-gray-100 dark:divide-gray-800 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden">
                            @foreach($participants as $p)
                                <div class="flex items-center justify-between p-2.5 bg-white dark:bg-gray-900 hover:bg-gray-50/70 dark:hover:bg-gray-800/50 transition">
                                    <div class="flex items-center gap-2 min-w-0">
                                        @if($p['avatar'])
                                            <img src="{{ $p['avatar'] }}" class="h-7 w-7 rounded-full object-cover border border-gray-200 dark:border-gray-700" />
                                        @else
                                            <div class="flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-full bg-amber-100 text-amber-800 dark:bg-gray-800 dark:text-amber-300 font-bold text-[10px]">
                                                {{ $p['initial'] }}
                                            </div>
                                        @endif
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-1">
                                                <span class="truncate text-[11px] font-bold text-gray-900 dark:text-white">{{ $p['name'] }}</span>
                                                @if($p['is_me'])
                                                    <span class="text-[9px] text-gray-400">(You)</span>
                                                @endif
                                            </div>
                                            <div class="text-[9px] text-gray-400 truncate">
                                                {{ $p['username'] ? '@' . $p['username'] : $p['email'] }}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-1.5 flex-shrink-0">
                                        @if($p['role'] === 'owner')
                                            <span class="rounded bg-amber-500/20 px-1.5 py-0.2 text-[9px] font-bold text-amber-800 dark:text-amber-300">
                                                👑 Owner
                                            </span>
                                        @elseif($p['role'] === 'admin')
                                            <span class="rounded bg-indigo-500/20 px-1.5 py-0.2 text-[9px] font-bold text-indigo-700 dark:text-indigo-300">
                                                🛡️ Admin
                                            </span>
                                        @endif

                                        @if(!$p['is_me'])
                                            @if($isOwner)
                                                <button
                                                    type="button"
                                                    wire:click="toggleAdminRole({{ $p['id'] }})"
                                                    class="rounded p-1 text-[10px] text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400"
                                                    title="{{ $p['is_admin'] ? 'Dismiss Admin' : 'Make Admin' }}"
                                                >
                                                    {{ $p['is_admin'] ? 'Demote' : 'Promote' }}
                                                </button>
                                                <button
                                                    type="button"
                                                    wire:click="openTransferOwnershipModal({{ $p['id'] }})"
                                                    class="rounded p-1 text-[10px] text-gray-400 hover:text-amber-600"
                                                    title="Transfer Ownership"
                                                >
                                                    👑
                                                </button>
                                                <button
                                                    type="button"
                                                    wire:confirm="Remove {{ $p['name'] }} from this group?"
                                                    wire:click="removeMemberFromGroup({{ $p['id'] }})"
                                                    class="rounded p-1 text-gray-400 hover:text-red-600"
                                                    title="Remove"
                                                >
                                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            @elseif($isAdmin && !$p['is_admin'] && !$p['is_owner'])
                                                <button
                                                    type="button"
                                                    wire:confirm="Remove {{ $p['name'] }} from this group?"
                                                    wire:click="removeMemberFromGroup({{ $p['id'] }})"
                                                    class="rounded p-1 text-gray-400 hover:text-red-600"
                                                    title="Remove"
                                                >
                                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <!-- RULES / PERMISSIONS TAB -->
                    @if($groupSettingsTab === 'permissions')
                        <div class="space-y-3">
                            <!-- Rule 1: Send Messages -->
                            <div class="rounded-xl border border-gray-200 dark:border-gray-800 p-3 space-y-1.5 bg-gray-50/50 dark:bg-gray-950/30">
                                <h5 class="text-xs font-bold text-gray-900 dark:text-white">Send Messages</h5>
                                <div class="grid grid-cols-2 gap-2 text-[11px]">
                                    <button
                                        type="button"
                                        @if($isAdmin) wire:click="$set('settingsOnlyAdminsCanMessage', false)" @endif
                                        class="flex items-center gap-1.5 rounded-lg border p-2 text-left transition {{ !$settingsOnlyAdminsCanMessage ? 'border-amber-500 bg-amber-50/60 dark:bg-amber-950/30 font-bold text-amber-900 dark:text-amber-200' : 'border-gray-200 dark:border-gray-800 text-gray-600 dark:text-gray-400' }} {{ $isAdmin ? 'cursor-pointer' : 'cursor-not-allowed opacity-60' }}"
                                    >
                                        <div class="flex h-3.5 w-3.5 flex-shrink-0 items-center justify-center rounded-full border {{ !$settingsOnlyAdminsCanMessage ? 'border-amber-600 bg-amber-600 text-white' : 'border-gray-300 dark:border-gray-600' }}">
                                            @if(!$settingsOnlyAdminsCanMessage)
                                                <div class="h-1 w-1 rounded-full bg-white"></div>
                                            @endif
                                        </div>
                                        <span>All Members</span>
                                    </button>
                                    <button
                                        type="button"
                                        @if($isAdmin) wire:click="$set('settingsOnlyAdminsCanMessage', true)" @endif
                                        class="flex items-center gap-1.5 rounded-lg border p-2 text-left transition {{ $settingsOnlyAdminsCanMessage ? 'border-amber-500 bg-amber-50/60 dark:bg-amber-950/30 font-bold text-amber-900 dark:text-amber-200' : 'border-gray-200 dark:border-gray-800 text-gray-600 dark:text-gray-400' }} {{ $isAdmin ? 'cursor-pointer' : 'cursor-not-allowed opacity-60' }}"
                                    >
                                        <div class="flex h-3.5 w-3.5 flex-shrink-0 items-center justify-center rounded-full border {{ $settingsOnlyAdminsCanMessage ? 'border-amber-600 bg-amber-600 text-white' : 'border-gray-300 dark:border-gray-600' }}">
                                            @if($settingsOnlyAdminsCanMessage)
                                                <div class="h-1 w-1 rounded-full bg-white"></div>
                                            @endif
                                        </div>
                                        <span>Only Admins</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Rule 2: Edit Info -->
                            <div class="rounded-xl border border-gray-200 dark:border-gray-800 p-3 space-y-1.5 bg-gray-50/50 dark:bg-gray-950/30">
                                <h5 class="text-xs font-bold text-gray-900 dark:text-white">Edit Group Info</h5>
                                <div class="grid grid-cols-2 gap-2 text-[11px]">
                                    <button
                                        type="button"
                                        @if($isAdmin) wire:click="$set('settingsOnlyAdminsCanEditInfo', false)" @endif
                                        class="flex items-center gap-1.5 rounded-lg border p-2 text-left transition {{ !$settingsOnlyAdminsCanEditInfo ? 'border-amber-500 bg-amber-50/60 dark:bg-amber-950/30 font-bold text-amber-900 dark:text-amber-200' : 'border-gray-200 dark:border-gray-800 text-gray-600 dark:text-gray-400' }} {{ $isAdmin ? 'cursor-pointer' : 'cursor-not-allowed opacity-60' }}"
                                    >
                                        <div class="flex h-3.5 w-3.5 flex-shrink-0 items-center justify-center rounded-full border {{ !$settingsOnlyAdminsCanEditInfo ? 'border-amber-600 bg-amber-600 text-white' : 'border-gray-300 dark:border-gray-600' }}">
                                            @if(!$settingsOnlyAdminsCanEditInfo)
                                                <div class="h-1 w-1 rounded-full bg-white"></div>
                                            @endif
                                        </div>
                                        <span>All Members</span>
                                    </button>
                                    <button
                                        type="button"
                                        @if($isAdmin) wire:click="$set('settingsOnlyAdminsCanEditInfo', true)" @endif
                                        class="flex items-center gap-1.5 rounded-lg border p-2 text-left transition {{ $settingsOnlyAdminsCanEditInfo ? 'border-amber-500 bg-amber-50/60 dark:bg-amber-950/30 font-bold text-amber-900 dark:text-amber-200' : 'border-gray-200 dark:border-gray-800 text-gray-600 dark:text-gray-400' }} {{ $isAdmin ? 'cursor-pointer' : 'cursor-not-allowed opacity-60' }}"
                                    >
                                        <div class="flex h-3.5 w-3.5 flex-shrink-0 items-center justify-center rounded-full border {{ $settingsOnlyAdminsCanEditInfo ? 'border-amber-600 bg-amber-600 text-white' : 'border-gray-300 dark:border-gray-600' }}">
                                            @if($settingsOnlyAdminsCanEditInfo)
                                                <div class="h-1 w-1 rounded-full bg-white"></div>
                                            @endif
                                        </div>
                                        <span>Only Admins</span>
                                    </button>
                                </div>
                            </div>

                            @if($isAdmin)
                                <div class="flex justify-end pt-1">
                                    <button
                                        type="button"
                                        wire:click="saveGroupSettings"
                                        class="rounded-xl bg-amber-600 px-3.5 py-1.5 text-xs font-bold text-white shadow hover:bg-amber-500"
                                    >
                                        Save Rules
                                    </button>
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- DANGER TAB -->
                    @if($groupSettingsTab === 'danger')
                        <div class="space-y-3">
                            <div class="rounded-xl border border-gray-200 p-3 dark:border-gray-800 flex items-center justify-between">
                                <div>
                                    <h5 class="text-xs font-bold text-gray-900 dark:text-white">Leave Group</h5>
                                    <p class="text-[10px] text-gray-500 dark:text-gray-400">Exit this conversation.</p>
                                </div>
                                <button
                                    type="button"
                                    wire:confirm="Are you sure you want to leave this group?"
                                    wire:click="leaveGroup"
                                    class="rounded-lg border border-red-200 bg-red-50 px-2.5 py-1 text-xs font-bold text-red-600 hover:bg-red-100 dark:border-red-900/60 dark:bg-red-950/40 dark:text-red-400"
                                >
                                    Leave
                                </button>
                            </div>

                            @if($isOwner)
                                <div class="rounded-xl border border-red-200 bg-red-50/40 p-3 dark:border-red-900/60 dark:bg-red-950/20 flex items-center justify-between">
                                    <div>
                                        <h5 class="text-xs font-bold text-red-700 dark:text-red-400">Delete Group</h5>
                                        <p class="text-[10px] text-red-600/80 dark:text-red-400/80">Permanently delete group.</p>
                                    </div>
                                    <button
                                        type="button"
                                        wire:confirm="Are you sure you want to delete this group permanently?"
                                        wire:click="deleteGroup"
                                        class="rounded-lg bg-red-600 px-2.5 py-1 text-xs font-bold text-white shadow hover:bg-red-500"
                                    >
                                        Delete
                                    </button>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <!-- Add Members Modal (Floating) -->
    @if($showAddMembersModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 p-3 backdrop-blur-sm animate-in fade-in duration-150">
            <div
                @click.outside="$wire.set('showAddMembersModal', false)"
                class="relative w-full max-w-sm overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xl dark:border-gray-800 dark:bg-gray-900"
            >
                <div class="flex items-center justify-between border-b border-gray-100 p-3 dark:border-gray-800">
                    <h4 class="text-xs font-bold text-gray-900 dark:text-white">Add Members to Group</h4>
                    <button
                        type="button"
                        wire:click="$set('showAddMembersModal', false)"
                        class="rounded p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="p-2.5 border-b border-gray-100 dark:border-gray-800">
                    <input
                        type="text"
                        wire:model.live.debounce.200ms="memberSearch"
                        placeholder="Search users..."
                        class="chat-input-field w-full rounded-xl border border-gray-300 bg-white px-3 py-1.5 text-xs text-gray-900 placeholder-gray-400 focus:border-amber-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                    />
                </div>

                <div class="max-h-60 overflow-y-auto divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($this->addableUsers as $u)
                        @php $isMarked = in_array($u->id, $newMemberIds, true); @endphp
                        <div
                            wire:click="toggleAddMember({{ $u->id }})"
                            class="flex cursor-pointer items-center justify-between p-2.5 hover:bg-amber-50/60 dark:hover:bg-gray-800/60 transition {{ $isMarked ? 'bg-amber-50 dark:bg-amber-950/40' : '' }}"
                        >
                            <div class="flex items-center gap-2 min-w-0">
                                @if($u->getFilamentAvatarUrl())
                                    <img src="{{ $u->getFilamentAvatarUrl() }}" class="h-7 w-7 rounded-full object-cover border border-gray-200 dark:border-gray-700" />
                                @else
                                    <div class="flex h-7 w-7 items-center justify-center rounded-full bg-amber-100 text-amber-800 dark:bg-gray-800 dark:text-amber-300 font-bold text-xs">
                                        {{ strtoupper(substr($u->name, 0, 1)) }}
                                    </div>
                                @endif
                                <div class="min-w-0">
                                    <p class="truncate text-xs font-semibold text-gray-900 dark:text-white">{{ $u->name }}</p>
                                    <p class="truncate text-[10px] text-gray-400">{{ $u->email }}</p>
                                </div>
                            </div>
                            <input
                                type="checkbox"
                                class="rounded border-gray-300 text-amber-600 focus:ring-amber-500 dark:border-gray-700 dark:bg-gray-800"
                                {{ $isMarked ? 'checked' : '' }}
                                wire:click.stop="toggleAddMember({{ $u->id }})"
                            />
                        </div>
                    @empty
                        <div class="p-4 text-center text-xs text-gray-400">
                            No eligible users found.
                        </div>
                    @endforelse
                </div>

                <div class="flex items-center justify-between border-t border-gray-100 p-2.5 bg-gray-50/50 dark:border-gray-800 dark:bg-gray-950/40">
                    <span class="text-xs text-gray-500">Selected: <strong>{{ count($newMemberIds) }}</strong></span>
                    <button
                        type="button"
                        wire:click="addMembersToGroup"
                        {{ empty($newMemberIds) ? 'disabled' : '' }}
                        class="rounded-xl bg-amber-600 px-3.5 py-1 text-xs font-bold text-white shadow hover:bg-amber-500 disabled:opacity-50"
                    >
                        Add to Group
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Transfer Ownership Modal (Floating) -->
    @if($showTransferOwnershipModal)
        @php $transferUser = \App\Models\User::find($transferOwnershipUserId); @endphp
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 p-3 backdrop-blur-sm animate-in fade-in duration-150">
            <div
                @click.outside="$wire.set('showTransferOwnershipModal', false)"
                class="relative w-full max-w-xs overflow-hidden rounded-2xl border border-gray-200 bg-white p-4 shadow-2xl dark:border-gray-800 dark:bg-gray-900"
            >
                <div class="flex items-center gap-2 text-amber-600 dark:text-amber-400 mb-2">
                    <span class="text-xl">👑</span>
                    <h4 class="text-xs font-bold text-gray-900 dark:text-white">Transfer Ownership</h4>
                </div>

                <p class="text-xs text-gray-600 dark:text-gray-300 leading-relaxed mb-3">
                    Transfer group ownership to <strong>{{ $transferUser?->name }}</strong>?
                </p>

                <div class="rounded-xl bg-amber-50 p-2 text-[10px] text-amber-800 dark:bg-amber-950/40 dark:text-amber-300 border border-amber-200 dark:border-amber-900/40 mb-3">
                    ⚠️ The new owner will have full control and can demote or remove you.
                </div>

                <div class="flex items-center justify-end gap-2">
                    <button
                        type="button"
                        wire:click="$set('showTransferOwnershipModal', false)"
                        class="rounded-lg border border-gray-300 px-2.5 py-1 text-xs font-semibold text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        wire:click="confirmTransferOwnership"
                        class="rounded-lg bg-amber-600 px-3 py-1 text-xs font-bold text-white shadow hover:bg-amber-500"
                    >
                        Confirm
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Pinned Messages List Modal in Floating Widget -->
    @if($showPinnedMessagesModal)
        <div 
            class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 p-4 backdrop-blur-sm"
            @click.self="$wire.set('showPinnedMessagesModal', false)"
        >
            <div
                @click.outside="$wire.set('showPinnedMessagesModal', false)"
                class="relative w-full max-w-sm overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xl dark:border-gray-800 dark:bg-gray-900 flex flex-col max-h-[80vh]"
            >
                <div class="flex items-center justify-between border-b border-gray-100 px-4 py-3 dark:border-gray-800">
                    <div class="flex items-center gap-2">
                        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-amber-500/10 text-amber-600 dark:bg-amber-400/10 dark:text-amber-400 text-xs">
                            📌
                        </span>
                        <div>
                            <h4 class="text-xs font-bold text-gray-900 dark:text-white">Pinned Messages</h4>
                            <p class="text-[10px] text-gray-400">{{ $this->pinnedMessages->count() }} pinned</p>
                        </div>
                    </div>
                    <button
                        type="button"
                        wire:click="closePinnedMessagesModal"
                        class="rounded-lg p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto p-3 divide-y divide-gray-100 dark:divide-gray-800 space-y-2.5">
                    @forelse($this->pinnedMessages as $pm)
                        <div class="pt-2.5 first:pt-0 flex items-start justify-between gap-2 group">
                            <div class="flex items-start gap-2 min-w-0 flex-1">
                                @if($pm->sender?->avatar_url)
                                    <img src="{{ $pm->sender->getFilamentAvatarUrl() }}" class="h-7 w-7 rounded-full object-cover flex-shrink-0" />
                                @else
                                    <div class="flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-full bg-amber-100 text-amber-800 dark:bg-gray-800 dark:text-amber-300 font-bold text-[10px]">
                                        {{ strtoupper(substr($pm->sender?->name ?? 'U', 0, 1)) }}
                                    </div>
                                @endif
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-1.5">
                                        <span class="font-bold text-[11px] text-gray-900 dark:text-white truncate">{{ $pm->sender?->name }}</span>
                                        <span class="rounded bg-amber-100 px-1 py-0.2 text-[8px] font-semibold text-amber-800 dark:bg-amber-900/50 dark:text-amber-300">
                                            {{ $pm->getPinnedTimeRemaining() }}
                                        </span>
                                    </div>
                                    <div class="mt-0.5 text-[11px] text-gray-600 dark:text-gray-300">
                                        @if($pm->isImage())
                                            <div class="flex items-center gap-1 text-amber-600 dark:text-amber-400">
                                                <span>🖼️</span> <span>Photo</span>
                                            </div>
                                        @elseif($pm->isAudio())
                                            <div class="flex items-center gap-1 text-amber-600 dark:text-amber-400">
                                                <span>🎤</span> <span>Voice note</span>
                                            </div>
                                        @elseif($pm->isFile())
                                            <div class="flex items-center gap-1 text-amber-600 dark:text-amber-400">
                                                <span>📎</span> <span class="truncate">{{ $pm->attachment_name }}</span>
                                            </div>
                                        @endif
                                        @if($pm->body)
                                            <p class="truncate line-clamp-2 mt-0.5">{{ $pm->body }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-1 flex-shrink-0">
                                <button
                                    type="button"
                                    wire:click="jumpToPinnedMessage({{ $pm->id }})"
                                    class="rounded-lg bg-gray-100 px-2 py-0.5 text-[10px] font-semibold text-gray-700 hover:bg-amber-100 hover:text-amber-800 dark:bg-gray-800 dark:text-gray-300 transition cursor-pointer"
                                >
                                    Jump
                                </button>
                                <button
                                    type="button"
                                    wire:click="unpinMessage({{ $pm->id }})"
                                    class="rounded p-1 text-gray-400 hover:text-red-500 transition"
                                    title="Unpin"
                                >
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="p-4 text-center text-xs text-gray-400">
                            No pinned messages.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    @endif

    <script>
    window.voicePlayer = function(url, rootEl = null) {
        return {
            url: url,
            rootEl: rootEl,
            audio: null,
            isPlaying: false,
            currentTime: 0,
            duration: 0,
            progress: 0,
            speed: 1,
            speeds: [1, 1.5, 2],
            bars: Array(20).fill(4),
            animRaf: null,

            init() {
                if (!this.rootEl) {
                    this.rootEl = this.$el;
                }
                if (this.rootEl) {
                    this.rootEl._voicePlayer = this;
                    this.rootEl.addEventListener('play-voice-note', () => {
                        this.play();
                    });
                }

                // Global listener: Stop this voice note immediately if any other voice note starts playing
                window.addEventListener('voice-player-stop-all', (e) => {
                    if (e.detail?.except !== this && this.isPlaying) {
                        this.pause();
                    }
                });

                this.audio = new Audio();
                this.audio.preload = 'auto';
                this.audio.src = this.url;

                // Decode real audio waveform and extract exact duration
                this.loadWaveformData();

                this.audio.addEventListener('timeupdate', () => {
                    this.syncProgress();
                });

                this.audio.addEventListener('play', () => {
                    this.isPlaying = true;
                    this.startSmoothProgress();
                });

                this.audio.addEventListener('pause', () => {
                    this.isPlaying = false;
                    this.stopSmoothProgress();
                });

                this.audio.addEventListener('ended', () => {
                    this.isPlaying = false;
                    this.stopSmoothProgress();
                    this.currentTime = 0;
                    this.progress = 0;

                    // Continuous playback: automatically play the next voice note in thread!
                    this.playNextVoiceNote();
                });
            },

            async loadWaveformData() {
                try {
                    const response = await fetch(this.url);
                    if (!response.ok) throw new Error('Fetch audio failed');
                    const arrayBuffer = await response.arrayBuffer();

                    const AudioCtx = window.AudioContext || window.webkitAudioContext;
                    if (!AudioCtx) return;
                    const audioCtx = new AudioCtx();

                    const audioBuffer = await audioCtx.decodeAudioData(arrayBuffer);
                    if (audioBuffer && audioBuffer.duration && isFinite(audioBuffer.duration)) {
                        this.duration = audioBuffer.duration;
                    }

                    // Compute authentic waveform peaks from raw PCM audio channel data
                    const channelData = audioBuffer.getChannelData(0);
                    const numBars = 20;
                    const blockSize = Math.floor(channelData.length / numBars);
                    const rawBars = [];
                    let maxRms = 0;

                    for (let i = 0; i < numBars; i++) {
                        const start = i * blockSize;
                        let sum = 0;
                        let count = 0;
                        const step = Math.max(1, Math.floor(blockSize / 40));
                        for (let j = 0; j < blockSize; j += step) {
                            const val = channelData[start + j] || 0;
                            sum += val * val;
                            count++;
                        }
                        const rms = Math.sqrt(sum / (count || 1));
                        rawBars.push(rms);
                        if (rms > maxRms) maxRms = rms;
                    }

                    // Map RMS energy to heights: silence = 3px, voice = 6px to 22px
                    this.bars = rawBars.map(rms => {
                        if (maxRms <= 0.001 || rms < 0.01) {
                            return 3; // true silence
                        }
                        const ratio = rms / maxRms;
                        return Math.max(3, Math.min(20, Math.round(ratio * 19 + 3)));
                    });

                    audioCtx.close();
                } catch (e) {
                    this.fixAudioDuration();
                }
            },

            fixAudioDuration() {
                if (this.duration > 0) return;
                if (this.audio.duration && isFinite(this.audio.duration) && this.audio.duration > 0) {
                    this.duration = this.audio.duration;
                    return;
                }
                this.audio.addEventListener('loadedmetadata', () => {
                    if (isFinite(this.audio.duration) && this.audio.duration > 0) {
                        this.duration = this.audio.duration;
                    } else {
                        const onTime = () => {
                            this.audio.removeEventListener('timeupdate', onTime);
                            if (isFinite(this.audio.duration)) {
                                this.duration = this.audio.duration;
                            }
                            this.audio.currentTime = 0;
                        };
                        this.audio.addEventListener('timeupdate', onTime);
                        this.audio.currentTime = 1e101;
                    }
                }, { once: true });
            },

            syncProgress() {
                if (!this.audio) return;
                this.currentTime = this.audio.currentTime;
                let dur = this.duration;
                if ((!dur || !isFinite(dur) || dur <= 0) && isFinite(this.audio.duration) && this.audio.duration > 0) {
                    dur = this.audio.duration;
                    this.duration = dur;
                }
                if (!dur || !isFinite(dur) || dur <= 0) {
                    dur = Math.max(this.currentTime + 1, 1);
                }
                this.progress = Math.min(100, Math.max(0, (this.currentTime / dur) * 100));
            },

            startSmoothProgress() {
                this.stopSmoothProgress();
                const step = () => {
                    if (!this.isPlaying) return;
                    this.syncProgress();
                    this.animRaf = requestAnimationFrame(step);
                };
                this.animRaf = requestAnimationFrame(step);
            },

            stopSmoothProgress() {
                if (this.animRaf) {
                    cancelAnimationFrame(this.animRaf);
                    this.animRaf = null;
                }
            },

            play() {
                if (!this.audio) return;
                // Stop any other voice note currently playing across the application
                window.dispatchEvent(new CustomEvent('voice-player-stop-all', { detail: { except: this } }));

                this.audio.playbackRate = this.speed;
                this.audio.play().then(() => {
                    this.isPlaying = true;
                    this.startSmoothProgress();
                }).catch(() => {});
            },

            pause() {
                if (!this.audio) return;
                this.audio.pause();
                this.isPlaying = false;
                this.stopSmoothProgress();
            },

            togglePlay() {
                if (!this.audio) return;
                if (this.isPlaying) {
                    this.pause();
                } else {
                    this.play();
                }
            },

            playNextVoiceNote() {
                const currentEl = this.rootEl || this.$el;
                if (!currentEl) return;

                // Find the enclosing messages feed container
                const feed = currentEl.closest('[x-ref="messagesFeed"]')
                    || currentEl.closest('.overflow-y-auto')
                    || document;

                const players = Array.from(feed.querySelectorAll('[data-voice-player]'));
                const currentIndex = players.indexOf(currentEl);

                // If there is another voice note after this one, automatically trigger play!
                if (currentIndex !== -1 && currentIndex + 1 < players.length) {
                    const nextPlayerEl = players[currentIndex + 1];
                    nextPlayerEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                    setTimeout(() => {
                        if (nextPlayerEl._voicePlayer) {
                            nextPlayerEl._voicePlayer.play();
                        } else {
                            nextPlayerEl.dispatchEvent(new CustomEvent('play-voice-note'));
                        }
                    }, 250);
                }
                // If no next voice note, playback stops in its entirety (no-op)
            },

            handleBarClick(e) {
                if (!this.audio) return;
                const rect = e.currentTarget.getBoundingClientRect();
                const clickX = Math.max(0, Math.min(rect.width, e.clientX - rect.left));
                const percent = (clickX / rect.width) * 100;
                this.seek(percent);
            },

            seek(percent) {
                if (!this.audio) return;
                let dur = this.duration;
                if ((!dur || !isFinite(dur) || dur <= 0) && isFinite(this.audio.duration) && this.audio.duration > 0) {
                    dur = this.audio.duration;
                }
                if (dur && isFinite(dur) && dur > 0) {
                    const targetTime = (percent / 100) * dur;
                    this.audio.currentTime = targetTime;
                    this.currentTime = targetTime;
                    this.progress = percent;
                }
            },

            cycleSpeed() {
                if (!this.audio) return;
                const curIdx = this.speeds.indexOf(this.speed);
                const nextIdx = (curIdx + 1) % this.speeds.length;
                this.speed = this.speeds[nextIdx];
                this.audio.playbackRate = this.speed;
            },

            formatSecs(s) {
                if (!s || isNaN(s) || !isFinite(s) || s < 0) return '0:00';
                const mins = Math.floor(s / 60);
                const secs = Math.floor(s % 60);
                return mins + ':' + String(secs).padStart(2, '0');
            }
        };
    };
</script>

    <!-- Floating Action Button Launcher -->
    @php $unreadTotal = auth()->user()?->unreadMessagesCount() ?? 0; @endphp
    <button
        type="button"
        wire:click="toggleWidget"
        class="group relative flex h-14 w-14 items-center justify-center rounded-full bg-gradient-to-tr from-amber-600 to-amber-500 text-white shadow-xl shadow-amber-500/25 transition-all duration-300 hover:scale-105 active:scale-95 focus:outline-none"
        title="Open Chat"
    >
        <span x-show="!isOpen">
            <svg class="h-6 w-6 transition group-hover:rotate-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
            </svg>
        </span>
        <span x-show="isOpen" style="display: none;">
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </span>

        @if($unreadTotal > 0)
            <span class="absolute -top-1 -right-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-extrabold text-white shadow-md animate-pulse">
                {{ $unreadTotal }}
            </span>
        @endif
    </button>
</div>
@endif
</div>
