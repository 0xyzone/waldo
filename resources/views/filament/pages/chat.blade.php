<x-filament-panels::page class="chat-page-container">
    <div 
        x-data="{
            activeConversationId: @entangle('activeConversationId'),
            onlineUserIds: [],
            isRecording: false,
            isRecordingEnded: false,
            isCancelled: false,
            isPaused: false,
            shouldSendImmediately: false,
            isUploadingVoice: false,
            preparedVoiceFile: null,
            isPlayingPreview: false,
            previewAudio: null,
            previewUrl: null,
            previewCurrentTime: 0,
            previewProgressRatio: 0,
            recordedMimeType: 'audio/webm',
            mediaRecorder: null,
            audioChunks: [],
            recordingTime: 0,
            recordingInterval: null,
            maxRecordingSeconds: 60,
            audioCtx: null,
            analyserNode: null,
            micStream: null,
            vizBars: Array(28).fill(3),
            vizRaf: null,

            init() {
                this.scrollToBottom();
                this.initEcho();

                $wire.on('conversation-changed', (event) => {
                    if (this.isRecording) {
                        this.cancelRecording();
                    }
                    this.$nextTick(() => {
                        this.subscribeToConversation(event.conversationId);
                        this.scrollToBottom();
                    });
                });

                $wire.on('scroll-to-bottom', () => {
                    this.$nextTick(() => this.scrollToBottom());
                });

                $wire.on('scroll-to-message', (event) => {
                    const id = (typeof event === 'object' && event !== null && event.messageId) ? event.messageId : event;
                    this.$nextTick(() => this.scrollToMessage(id));
                });

                $wire.on('focus-message-input', () => {
                    this.$nextTick(() => {
                        this.$refs.messageInput?.focus();
                    });
                });

                window.addEventListener('voice-player-stop-all', () => {
                    if (this.isPlayingPreview) {
                        this.pausePreview();
                    }
                });

                window.addEventListener('EchoLoaded', () => {
                    this.initEcho();
                });
            },

            initEcho() {
                if (!window.Echo) return;

                // Track presence
                window.Echo.join('chat.presence')
                    .here((users) => {
                        this.onlineUserIds = users.map(u => u.id);
                    })
                    .joining((user) => {
                        if (!this.onlineUserIds.includes(user.id)) {
                            this.onlineUserIds.push(user.id);
                        }
                    })
                    .leaving((user) => {
                        this.onlineUserIds = this.onlineUserIds.filter(id => id !== user.id);
                    });

                // User's personal channel
                window.Echo.private(`chat.user.{{ auth()->id() }}`)
                    .listen('.message.sent', (e) => {
                        this.playTing();
                        $wire.$refresh();
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
                const container = this.$refs.messagesFeed;
                if (container) {
                    container.scrollTop = container.scrollHeight;
                }
            },

            scrollToMessage(msgId) {
                const el = document.getElementById('msg-' + msgId);
                if (el) {
                    el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    el.classList.add('ring-2', 'ring-amber-500', 'rounded-2xl', 'transition-all');
                    setTimeout(() => {
                        el.classList.remove('ring-2', 'ring-amber-500');
                    }, 2000);
                }
            },

            handleKeyDown(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    if ($wire.messageText.trim().length > 0 || $wire.attachment) {
                        $wire.sendMessage();
                    }
                }
            },

            isUserOnline(userId) {
                return this.onlineUserIds.includes(userId);
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

                    // Detect best supported MIME type with fallbacks
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

                    // Web Audio API for real-time reactive microphone visualization
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

                    // Timer + 60s max limit auto-stop (stops mic & recording, waits for send click)
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

                    // 200ms timeslice ensures ondataavailable fires periodically
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
                const numBars = 28;
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

                    // Background silence threshold
                    if (volume < 0.02) {
                        // Silent: stay flat at 3px baseline (no ghost motion)
                        this.vizBars = Array(numBars).fill(3);
                    } else {
                        // Voice detected: react dynamically to volume + pitch
                        const amplifiedVol = Math.min(1, volume * 3.8);
                        const bars = [];
                        const step = Math.floor(freqBuf.length / numBars);

                        for (let i = 0; i < numBars; i++) {
                            const freqVal = (freqBuf[i * step] || 0) / 255;
                            const energy = (freqVal * 0.55) + (amplifiedVol * 0.45);
                            const distFromCenter = Math.abs(i - (numBars / 2)) / (numBars / 2);
                            const curveFactor = 1 - (distFromCenter * 0.35);
                            const height = Math.max(3, Math.min(24, Math.round(energy * curveFactor * 24 + 3)));
                            bars.push(height);
                        }
                        this.vizBars = bars;
                    }

                    this.vizRaf = requestAnimationFrame(draw);
                };

                this.vizRaf = requestAnimationFrame(draw);
            },

            stopVizLoop() {
                if (this.vizRaf) {
                    cancelAnimationFrame(this.vizRaf);
                    this.vizRaf = null;
                }
                this.vizBars = Array(28).fill(3);
            },

            setStaticWaveform() {
                const samplePattern = [6, 10, 16, 12, 20, 24, 18, 14, 22, 16, 10, 18, 24, 20, 14, 18, 22, 16, 12, 20, 16, 10, 14, 18, 12, 8, 10, 6];
                this.vizBars = samplePattern.slice(0, 28);
            },

            togglePlayPreview() {
                if (this.isPlayingPreview) {
                    this.pausePreview();
                } else {
                    this.startPlayPreview();
                }
            },

            startPlayPreview() {
                window.dispatchEvent(new CustomEvent('voice-player-stop-all'));

                if (this.previewAudio && this.previewUrl) {
                    this.previewAudio.play().then(() => {
                        this.isPlayingPreview = true;
                    }).catch(err => {
                        console.warn('Resume preview audio failed, rebuilding:', err);
                        this.buildAndPlayPreview();
                    });
                    return;
                }

                this.buildAndPlayPreview();
            },

            buildAndPlayPreview() {
                this.cleanupPreview();

                let blob = null;
                if (this.preparedVoiceFile) {
                    blob = this.preparedVoiceFile;
                } else if (this.audioChunks && this.audioChunks.length > 0) {
                    const mime = this.recordedMimeType || 'audio/webm';
                    blob = new Blob(this.audioChunks, { type: mime });
                }

                if (!blob || blob.size === 0) {
                    console.warn('No recorded voice data to preview.');
                    return;
                }

                try {
                    this.previewUrl = URL.createObjectURL(blob);
                    this.previewAudio = new Audio(this.previewUrl);

                    this.previewAudio.ontimeupdate = () => {
                        if (!this.previewAudio) return;
                        const duration = this.recordingTime || this.previewAudio.duration || 1;
                        this.previewProgressRatio = Math.min(1, this.previewAudio.currentTime / duration);
                        this.previewCurrentTime = Math.floor(this.previewAudio.currentTime);
                    };

                    this.previewAudio.onended = () => {
                        this.isPlayingPreview = false;
                        this.previewCurrentTime = 0;
                        this.previewProgressRatio = 0;
                        if (this.previewAudio) {
                            this.previewAudio.currentTime = 0;
                        }
                    };

                    this.previewAudio.onerror = (e) => {
                        console.error('Preview audio error:', e);
                        this.isPlayingPreview = false;
                    };

                    this.previewAudio.play().then(() => {
                        this.isPlayingPreview = true;
                    }).catch(err => {
                        console.error('Playback error:', err);
                        this.isPlayingPreview = false;
                    });
                } catch(err) {
                    console.error('Error starting preview playback:', err);
                    this.isPlayingPreview = false;
                }
            },

            pausePreview() {
                if (this.previewAudio) {
                    this.previewAudio.pause();
                }
                this.isPlayingPreview = false;
            },

            cleanupPreview() {
                if (this.previewAudio) {
                    this.previewAudio.pause();
                    this.previewAudio.ontimeupdate = null;
                    this.previewAudio.onended = null;
                    this.previewAudio.onerror = null;
                    this.previewAudio.src = '';
                    this.previewAudio = null;
                }
                if (this.previewUrl) {
                    try { URL.revokeObjectURL(this.previewUrl); } catch(e) {}
                    this.previewUrl = null;
                }
                this.isPlayingPreview = false;
                this.previewCurrentTime = 0;
                this.previewProgressRatio = 0;
            },

            handleRecordingTimerEnd() {
                this.cleanupPreview();
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
                this.setStaticWaveform();
                if (this.mediaRecorder && this.mediaRecorder.state !== 'inactive') {
                    this.mediaRecorder.stop();
                }
            },

            sendPreparedVoiceFile() {
                if (!this.preparedVoiceFile || this.isUploadingVoice) return;
                this.cleanupPreview();
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
                this.cleanupPreview();
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
                    this.resumeRecording();
                } else {
                    this.pauseRecording();
                }
            },

            pauseRecording() {
                if (!this.mediaRecorder || !this.isRecording || this.isRecordingEnded || this.isPaused) return;
                try {
                    if (this.mediaRecorder.state === 'recording') {
                        this.mediaRecorder.requestData();
                    }
                } catch(e) {}
                this.mediaRecorder.pause();
                this.isPaused = true;
                this.setStaticWaveform();
            },

            resumeRecording() {
                if (!this.mediaRecorder || !this.isRecording || this.isRecordingEnded || !this.isPaused) return;
                this.cleanupPreview();
                this.mediaRecorder.resume();
                this.isPaused = false;
                if (this.audioCtx && this.audioCtx.state === 'suspended') {
                    this.audioCtx.resume();
                }
            },

            stopAndSendRecording() {
                if (this.isUploadingVoice) return;
                this.cleanupPreview();

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
                this.cleanupPreview();
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
        class="flex h-[calc(100vh-13rem)] min-h-[560px] overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-xl dark:border-slate-800 dark:bg-slate-900"
    >
        <!-- Left: Conversations Sidebar -->
        <div class="flex w-full md:w-80 lg:w-96 flex-col border-r border-gray-200 dark:border-slate-800 {{ $activeConversationId ? 'hidden md:flex' : 'flex' }}">
            <!-- Sidebar Header -->
            <div class="flex items-center justify-between border-b border-gray-100 p-4 dark:border-slate-800/80 bg-white dark:bg-slate-900">
                <div class="flex items-center gap-2">
                    <h2 class="text-base font-bold text-gray-900 dark:text-white">Messages</h2>
                    @php $unreadTotal = auth()->user()?->unreadMessagesCount() ?? 0; @endphp
                    @if($unreadTotal > 0)
                        <span class="inline-flex items-center rounded-full bg-amber-500 px-2 py-0.5 text-xs font-semibold text-white shadow-sm">
                            {{ $unreadTotal }}
                        </span>
                    @endif
                </div>
                <button
                    type="button"
                    wire:click="$set('showNewChatModal', true)"
                    class="inline-flex items-center gap-1.5 rounded-xl bg-amber-600 px-3 py-1.5 text-xs font-semibold text-white shadow-md transition hover:bg-amber-500 active:scale-95"
                    title="Start new chat or group"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>New Chat</span>
                </button>
            </div>

            <!-- Search Bar -->
            <div class="p-3 border-b border-gray-100 dark:border-slate-800/60 bg-gray-50/60 dark:bg-slate-950/40">
                <div class="relative">
                    <input
                        type="text"
                        wire:model.live.debounce.250ms="search"
                        placeholder="Search conversations & messages..."
                        class="chat-input-field w-full rounded-xl border border-gray-300 bg-white py-2 pl-9 pr-3 text-xs text-gray-900 placeholder-gray-400 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500 dark:border-slate-700/80 dark:bg-slate-800 dark:text-gray-100 dark:placeholder-gray-500"
                    />
                    <svg class="absolute left-3 top-2.5 h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
            </div>

            <!-- Conversation List -->
            <div class="flex-1 overflow-y-auto divide-y divide-gray-100 dark:divide-slate-800/50 bg-white dark:bg-slate-900">
                @php $conversations = $this->getConversations(); @endphp
                @forelse($conversations as $conv)
                    @php
                        $isActive = $activeConversationId === $conv->id;
                        $unreadCount = $conv->unreadCountFor(auth()->id());
                        $latest = $conv->latestMessage;
                        $displayName = $conv->getDisplayName(auth()->id());
                        $avatarUrl = $conv->getDisplayAvatar(auth()->id());
                        $isGroup = $conv->isGroup();
                        $recipient = $isGroup ? null : $conv->getRecipientUser(auth()->id());
                        $isPinned = $conv->isPinnedFor(auth()->id());
                    @endphp
                    <div
                        wire:key="conv-{{ $conv->id }}"
                        wire:click="selectConversation({{ $conv->id }})"
                        class="group relative flex cursor-pointer items-center gap-3 p-3.5 transition-all hover:bg-amber-50/60 dark:hover:bg-slate-800/60 {{ $isActive ? 'bg-amber-50/80 dark:bg-amber-500/15 border-l-4 border-amber-500 pl-2.5' : '' }} {{ $isPinned ? 'bg-amber-50/30 dark:bg-amber-950/15' : '' }}"
                    >
                        <!-- Avatar -->
                        <div class="relative flex-shrink-0">
                            @if($avatarUrl)
                                <img src="{{ $avatarUrl }}" class="h-11 w-11 rounded-full object-cover shadow-sm border border-gray-100 dark:border-slate-700" />
                            @else
                                <div class="flex h-11 w-11 items-center justify-center rounded-full bg-gradient-to-tr {{ $isGroup ? 'from-indigo-600 to-indigo-400' : 'from-amber-600 to-amber-400' }} text-sm font-bold text-white shadow-sm">
                                    @if($isGroup)
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                        </svg>
                                    @else
                                        {{ strtoupper(substr($displayName, 0, 1)) }}
                                    @endif
                                </div>
                            @endif

                            @if($isPinned)
                                <span class="absolute -top-1 -left-1 flex h-4 w-4 items-center justify-center rounded-full bg-amber-500 text-[10px] text-white shadow" title="Pinned to top">
                                    📌
                                </span>
                            @endif

                            @if(!$isGroup && $recipient)
                                <span 
                                    x-show="isUserOnline({{ $recipient->id }})"
                                    class="absolute bottom-0 right-0 h-3 w-3 rounded-full border-2 border-white bg-emerald-500 dark:border-slate-900"
                                    title="Online"
                                ></span>
                            @endif
                        </div>

                        <!-- Details -->
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center justify-between gap-1">
                                <span class="truncate text-xs font-semibold text-gray-900 dark:text-white flex items-center gap-1.5">
                                    {{ $displayName }}
                                    @if($isGroup)
                                        <span class="rounded bg-indigo-100 px-1 py-0.2 text-[9px] font-bold text-indigo-700 dark:bg-indigo-900/60 dark:text-indigo-300">
                                            Group
                                        </span>
                                    @endif
                                </span>
                                @if($latest?->created_at)
                                    <span class="text-[10px] text-gray-400 flex-shrink-0">
                                        {{ $latest->created_at->diffForHumans(null, true, true) }}
                                    </span>
                                @endif
                            </div>
                            <div class="flex items-center justify-between mt-0.5">
                                <p class="truncate text-xs text-gray-500 dark:text-gray-400 {{ $unreadCount > 0 ? 'font-semibold text-gray-900 dark:text-gray-100' : '' }}">
                                    @if($latest)
                                        @if($latest->is_deleted)
                                            <span class="italic text-gray-400">This message was deleted</span>
                                        @else
                                            @if($latest->sender_id === auth()->id())
                                                <span class="text-amber-600 dark:text-amber-400">You: </span>
                                            @elseif($isGroup)
                                                <span class="text-gray-700 dark:text-gray-300">{{ $latest->sender?->name }}: </span>
                                            @endif
                                            @if($latest->isAudio())
                                                🎙️ Voice message
                                            @elseif($latest->isImage())
                                                📷 Photo
                                            @elseif($latest->isFile())
                                                📎 Attachment
                                            @else
                                                {{ $latest->body }}
                                            @endif
                                        @endif
                                    @else
                                        <span class="italic text-gray-400">Started a conversation</span>
                                    @endif
                                </p>
                                <div class="flex items-center gap-1 ml-1.5 flex-shrink-0">
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

                                    @if($unreadCount > 0)
                                        <span class="flex h-4 min-w-4 items-center justify-center rounded-full bg-amber-500 px-1 text-[10px] font-bold text-white shadow-sm">
                                            {{ $unreadCount }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-gray-400 dark:text-gray-500">
                        <svg class="mx-auto h-10 w-10 text-gray-300 dark:text-slate-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                        <p class="mt-2 text-xs">No conversations yet</p>
                        <button
                            type="button"
                            wire:click="$set('showNewChatModal', true)"
                            class="mt-3 inline-flex items-center gap-1 text-xs font-semibold text-amber-600 hover:underline dark:text-amber-400"
                        >
                            Start a new chat
                        </button>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Right: Active Chat Window -->
        <div class="flex flex-1 flex-col bg-gray-50/50 dark:bg-slate-950/60 {{ $activeConversationId ? 'flex' : 'hidden md:flex' }}">
            @if($this->activeConversation)
                @php
                    $isGroup = $this->activeConversation->isGroup();
                    $activeName = $this->activeConversation->getDisplayName(auth()->id());
                    $activeAvatar = $this->activeConversation->getDisplayAvatar(auth()->id());
                    $recipient = $isGroup ? null : $this->activeConversation->getRecipientUser(auth()->id());
                @endphp
                <!-- Chat Header -->
                <div class="flex items-center justify-between border-b border-gray-200 bg-white/90 p-3.5 backdrop-blur dark:border-slate-800 dark:bg-slate-900/90">
                    <div class="flex items-center gap-3">
                        <button
                            type="button"
                            wire:click="$set('activeConversationId', null)"
                            class="rounded-lg p-1.5 text-gray-500 hover:bg-gray-100 md:hidden dark:text-gray-400 dark:hover:bg-slate-800"
                        >
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                        </button>

                        <div class="relative flex-shrink-0">
                            @if($activeAvatar)
                                <img src="{{ $activeAvatar }}" class="h-10 w-10 rounded-full object-cover shadow-sm border border-gray-100 dark:border-slate-700" />
                            @else
                                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-tr {{ $isGroup ? 'from-indigo-600 to-indigo-400' : 'from-amber-600 to-amber-400' }} text-sm font-bold text-white shadow-sm">
                                    @if($isGroup)
                                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                        </svg>
                                    @else
                                        {{ strtoupper(substr($activeName, 0, 1)) }}
                                    @endif
                                </div>
                            @endif

                            @if(!$isGroup && $recipient)
                                <span 
                                    x-show="isUserOnline({{ $recipient->id }})"
                                    class="absolute bottom-0 right-0 h-2.5 w-2.5 rounded-full border-2 border-white bg-emerald-500 dark:border-slate-900"
                                ></span>
                            @endif
                        </div>

                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="text-sm font-bold text-gray-900 dark:text-white">
                                    {{ $activeName }}
                                </h3>
                                @if($isGroup)
                                    @php $myRole = $this->activeConversation->getUserRole(auth()->id()); @endphp
                                    @if($myRole === 'owner')
                                        <span class="rounded-full bg-amber-500/20 px-2 py-0.5 text-[9px] font-extrabold text-amber-800 dark:bg-amber-400/20 dark:text-amber-300 ring-1 ring-amber-400/30">👑 Owner</span>
                                    @elseif($myRole === 'admin')
                                        <span class="rounded-full bg-indigo-500/20 px-2 py-0.5 text-[9px] font-extrabold text-indigo-800 dark:bg-indigo-400/20 dark:text-indigo-300 ring-1 ring-indigo-400/30">🛡️ Admin</span>
                                    @endif
                                @endif
                            </div>
                            <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-slate-400">
                                @if($isGroup)
                                    <span>{{ $this->activeConversation->participants->count() }} members</span>
                                    @if($this->activeConversation->description)
                                        <span>•</span>
                                        <span class="truncate max-w-[200px] sm:max-w-[320px] italic">{{ $this->activeConversation->description }}</span>
                                    @endif
                                @else
                                    @if($recipient?->username)
                                        <span>@<span>{{ $recipient->username }}</span></span>
                                        <span>•</span>
                                    @endif
                                    <span x-show="isUserOnline({{ $recipient?->id ?? 0 }})" class="font-medium text-emerald-600 dark:text-emerald-400">Online</span>
                                    <span x-show="!isUserOnline({{ $recipient?->id ?? 0 }})">Offline</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Header Actions (Search & Group Settings) -->
                    <div class="flex items-center gap-1.5">
                        <button
                            type="button"
                            wire:click="toggleMessageSearch"
                            class="rounded-xl p-2 text-gray-400 transition hover:bg-gray-100 hover:text-amber-600 dark:hover:bg-slate-800 dark:hover:text-amber-400 {{ $showMessageSearch ? 'bg-amber-100 text-amber-700 dark:bg-amber-950/60 dark:text-amber-400' : '' }}"
                            title="Search in this conversation"
                        >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </button>

                        @if($isGroup)
                            <button
                                type="button"
                                wire:click="openGroupSettings"
                                class="rounded-xl p-2 text-gray-400 transition hover:bg-gray-100 hover:text-amber-600 dark:hover:bg-slate-800 dark:hover:text-amber-400"
                                title="Group Settings & Members"
                            >
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </button>
                        @endif
                    </div>
                </div>

                <!-- In-Chat Search Bar -->
                @if($showMessageSearch)
                    <div class="flex items-center gap-2 border-b border-gray-100 bg-amber-50/70 p-2.5 dark:border-slate-800 dark:bg-slate-900/60">
                        <svg class="h-4 w-4 text-gray-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <input
                            type="text"
                            wire:model.live.debounce.200ms="messageSearch"
                            placeholder="Search in this conversation..."
                            class="chat-input-field flex-1 rounded-xl border border-gray-300 bg-white px-3 py-1.5 text-xs text-gray-900 placeholder-gray-400 focus:border-amber-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-white dark:placeholder-slate-400"
                        />
                        @if(filled($messageSearch))
                            <button
                                type="button"
                                wire:click="clearMessageSearch"
                                class="rounded-lg p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"
                                title="Clear search"
                            >
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        @endif
                    </div>
                @endif

                <!-- Pinned Messages Banner -->
                @php $pinnedMessages = $this->pinnedMessages; @endphp
                @if($pinnedMessages->isNotEmpty())
                    <div class="border-b border-amber-200/80 bg-gradient-to-r from-amber-50/90 to-orange-50/90 px-4 py-2 text-xs dark:border-amber-900/40 dark:from-amber-950/40 dark:to-orange-950/30">
                        @php $firstPinned = $pinnedMessages->first(); @endphp
                        <div class="flex items-center justify-between gap-3">
                            <button
                                type="button"
                                @if($pinnedMessages->count() > 1)
                                    wire:click="openPinnedMessagesModal"
                                @else
                                    @click="scrollToMessage({{ $firstPinned->id }})"
                                @endif
                                class="flex items-center gap-2 min-w-0 text-left hover:opacity-85 transition"
                                title="{{ $pinnedMessages->count() > 1 ? 'Click to view all pinned messages' : 'Click to jump to pinned message' }}"
                            >
                                <span class="flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-lg bg-amber-500 text-white text-xs shadow-sm">
                                    📌
                                </span>
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2">
                                        <span class="font-bold text-xs text-amber-950 dark:text-amber-200 truncate">
                                            Pinned: {{ $firstPinned->sender?->name }}
                                        </span>
                                        <span class="text-[10px] text-amber-700 dark:text-amber-400 font-semibold">
                                            ({{ $firstPinned->getPinnedTimeRemaining() }})
                                        </span>
                                    </div>
                                    <p class="truncate text-[11px] text-gray-600 dark:text-slate-300">
                                        {{ $firstPinned->body ?: ($firstPinned->attachment_name ?: 'Attachment') }}
                                    </p>
                                </div>
                            </button>
                            <div class="flex items-center gap-1.5 flex-shrink-0">
                                @if($pinnedMessages->count() > 1)
                                    <button
                                        type="button"
                                        wire:click="openPinnedMessagesModal"
                                        class="rounded-full bg-amber-200 hover:bg-amber-300 dark:bg-amber-900/70 dark:hover:bg-amber-800 px-2.5 py-0.5 text-[10px] font-bold text-amber-800 dark:text-amber-200 transition shadow-sm cursor-pointer flex items-center gap-1"
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
                                    class="rounded-lg p-1 text-gray-400 hover:text-red-500 transition"
                                    title="Unpin message"
                                >
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Messages Feed -->
                <div
                    x-ref="messagesFeed"
                    class="flex-1 overflow-y-auto p-4 space-y-3"
                >
                    @php $messages = $this->messages; @endphp
                    @forelse($messages as $msg)
                        @php
                            $isMe = $msg->sender_id === auth()->id();
                            $canDelete = $msg->canBeDeletedBy(auth()->id());
                            $isPinned = $msg->isCurrentlyPinned();
                        @endphp
                        <div
                            id="msg-{{ $msg->id }}"
                            wire:key="msg-{{ $msg->id }}"
                            class="group relative flex flex-col {{ $isMe ? 'items-end' : 'items-start' }}"
                        >
                            <div class="flex items-end gap-2 max-w-[85%] sm:max-w-[70%] {{ $isMe ? 'flex-row-reverse' : 'flex-row' }}">
                                @if(!$isMe)
                                    @if($msg->sender?->avatar_url)
                                        <img src="{{ $msg->sender->getFilamentAvatarUrl() }}" class="h-7 w-7 rounded-full object-cover flex-shrink-0 border border-gray-200 dark:border-slate-700" />
                                    @else
                                        <div class="flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-full bg-slate-200 text-xs font-bold text-slate-700 dark:bg-slate-800 dark:text-slate-300">
                                            {{ strtoupper(substr($msg->sender?->name ?? 'U', 0, 1)) }}
                                        </div>
                                    @endif
                                @endif

                                <div class="relative rounded-2xl px-4 py-2.5 text-xs shadow-sm transition-all {{ $isMe ? 'bg-amber-600 text-white rounded-br-sm' : 'bg-white text-gray-900 border border-gray-200 dark:border-slate-700/80 dark:bg-slate-800 dark:text-slate-100 rounded-bl-sm' }} {{ !$isMe && $msg->mentionsUser(auth()->id()) ? 'ring-2 ring-amber-500/80 dark:ring-amber-400/80 border-amber-400/60 dark:border-amber-500/60 bg-amber-50/50 dark:bg-amber-950/20' : '' }}">
                                    <!-- Sender Name for Groups & Tagged You Badge -->
                                    @if($isGroup && !$isMe)
                                        <div class="flex items-center justify-between gap-2 mb-1">
                                            <span class="block text-[11px] font-bold text-amber-600 dark:text-amber-400">
                                                {{ $msg->sender?->name }}
                                            </span>
                                            @if($msg->mentionsUser(auth()->id()))
                                                <span class="inline-flex items-center gap-1 rounded bg-amber-500/20 px-1.5 py-0.5 text-[9px] font-extrabold text-amber-800 dark:bg-amber-400/20 dark:text-amber-300 ring-1 ring-amber-400/30">
                                                    @ Tagged you
                                                </span>
                                            @endif
                                        </div>
                                    @elseif(!$isGroup && !$isMe && $msg->mentionsUser(auth()->id()))
                                        <div class="mb-1">
                                            <span class="inline-flex items-center gap-1 rounded bg-amber-500/20 px-1.5 py-0.5 text-[9px] font-extrabold text-amber-800 dark:bg-amber-400/20 dark:text-amber-300 ring-1 ring-amber-400/30">
                                                @ Tagged you
                                            </span>
                                        </div>
                                    @endif

                                    @if($msg->is_forwarded)
                                        <div class="mb-1 flex items-center gap-1 text-[10px] italic {{ $isMe ? 'text-amber-200/90' : 'text-gray-500 dark:text-slate-400' }}">
                                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                                            </svg>
                                            <span>Forwarded</span>
                                        </div>
                                    @endif

                                    @if($isPinned)
                                        <div class="mb-1.5 flex items-center gap-1 text-[10px] font-bold {{ $isMe ? 'text-amber-200' : 'text-amber-600 dark:text-amber-400' }}">
                                            <span>📌 Pinned</span>
                                            @if($msg->pinned_until)
                                                <span class="opacity-75 font-normal">({{ $msg->getPinnedTimeRemaining() }})</span>
                                            @endif
                                        </div>
                                    @endif

                                    @if($msg->reply_to_id && $msg->replyTo)
                                        <div 
                                            @click="scrollToMessage({{ $msg->reply_to_id }})"
                                            class="mb-2 cursor-pointer rounded-lg border-l-4 px-2.5 py-1.5 transition text-left select-none {{ $isMe ? 'border-amber-300 bg-black/15 hover:bg-black/25 text-white' : 'border-amber-500 bg-gray-50 hover:bg-gray-100 text-gray-800 dark:border-amber-400 dark:bg-slate-700/50 dark:hover:bg-slate-700/80 dark:text-slate-200' }}"
                                            title="Jump to replied message"
                                        >
                                            <div class="flex items-center justify-between gap-1 text-[11px] font-bold {{ $isMe ? 'text-amber-200' : 'text-amber-600 dark:text-amber-400' }}">
                                                <span>{{ $msg->replyTo->sender?->name ?? 'User' }}</span>
                                            </div>
                                            <p class="truncate text-[11px] opacity-90 font-normal">
                                                {{ $msg->replyTo->getReplySnippet(55) }}
                                            </p>
                                        </div>
                                    @endif

                                    <!-- Message Body or Deleted State -->
                                    @if($msg->is_deleted)
                                        <div class="flex items-center gap-1.5 py-0.5 italic {{ $isMe ? 'text-amber-100/90' : 'text-gray-500 dark:text-gray-400' }}">
                                            <svg class="h-4 w-4 opacity-70 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                            </svg>
                                            <span class="text-xs">This message was deleted</span>
                                        </div>
                                    @else
                                        <!-- Image message -->
                                        @if($msg->isImage())
                                            <div class="relative group/attachment my-1.5 overflow-hidden rounded-xl">
                                                <a href="{{ $msg->getAttachmentUrl() }}" target="_blank">
                                                    <img src="{{ $msg->getAttachmentUrl() }}" class="max-h-64 max-w-full rounded-xl object-cover hover:opacity-95 transition" />
                                                </a>
                                                @if($canDelete)
                                                    <button
                                                        type="button"
                                                        wire:confirm="Delete this image attachment? It will be replaced with 'This message was deleted'."
                                                        wire:click="deleteMessage({{ $msg->id }})"
                                                        class="absolute top-2 right-2 flex items-center gap-1 rounded-lg bg-black/60 px-2 py-1 text-[11px] font-bold text-white shadow backdrop-blur hover:bg-red-600 transition"
                                                        title="Delete image (valid for 15 mins or admin)"
                                                    >
                                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
                                                class="my-1.5 flex items-center gap-2.5 rounded-2xl px-3 py-2 transition-all {{ $isMe ? 'bg-amber-700/60 text-white' : 'bg-gray-100 dark:bg-slate-700/70 text-gray-900 dark:text-white' }} min-w-[240px] max-w-[280px] sm:max-w-[320px] select-none"
                                            >
                                                <!-- Play/Pause Button -->
                                                <button
                                                    type="button"
                                                    @click="togglePlay"
                                                    class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-full shadow-md transition-transform active:scale-95 {{ $isMe ? 'bg-white text-amber-700 hover:bg-amber-50' : 'bg-amber-600 text-white hover:bg-amber-500' }}"
                                                    title="Play / Pause"
                                                >
                                                    <template x-if="!isPlaying">
                                                        <svg class="h-4 w-4 ml-0.5" fill="currentColor" viewBox="0 0 24 24">
                                                            <path d="M8 5v14l11-7z"/>
                                                        </svg>
                                                    </template>
                                                    <template x-if="isPlaying">
                                                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24">
                                                            <path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/>
                                                        </svg>
                                                    </template>
                                                </button>

                                                <!-- Waveform & Info -->
                                                <div class="flex-1 min-w-0">
                                                    <!-- Waveform visualizer bars with scrub -->
                                                    <div class="flex items-center gap-[3px] h-6 cursor-pointer py-1" @click="handleBarClick($event)">
                                                        <template x-for="(barHeight, idx) in bars" :key="idx">
                                                            <div 
                                                                class="w-[3px] rounded-full transition-all duration-150"
                                                                :style="`height: ${barHeight}px;`"
                                                                :class="(idx / bars.length) <= (progress / 100) 
                                                                    ? '{{ $isMe ? 'bg-white' : 'bg-amber-600 dark:bg-amber-400' }}' 
                                                                    : '{{ $isMe ? 'bg-amber-300/40' : 'bg-gray-300 dark:bg-slate-500' }}'"
                                                            ></div>
                                                        </template>
                                                    </div>

                                                    <div class="flex items-center justify-between text-[10px] {{ $isMe ? 'text-amber-100/90' : 'text-gray-500 dark:text-slate-400' }} font-mono mt-0.5">
                                                        <span x-text="formatSecs(isPlaying ? currentTime : (duration || currentTime))">0:00</span>
                                                        <button 
                                                            type="button" 
                                                            @click="cycleSpeed" 
                                                            class="rounded px-1 text-[9px] font-bold uppercase transition hover:bg-black/10 dark:hover:bg-white/10"
                                                            x-text="speed + 'x'"
                                                            title="Playback speed"
                                                        >
                                                            1x
                                                        </button>
                                                    </div>
                                                </div>

                                                <!-- Direct Delete Option for Voice Note Attachment -->
                                                @if($canDelete)
                                                    <button
                                                        type="button"
                                                        wire:confirm="Delete this voice message? It will be replaced with 'This message was deleted'."
                                                        wire:click="deleteMessage({{ $msg->id }})"
                                                        class="rounded-lg p-1.5 transition {{ $isMe ? 'text-amber-200 hover:text-white hover:bg-white/10' : 'text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-950/40' }}"
                                                        title="Delete voice note (valid for 15 mins or admin)"
                                                    >
                                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                        </svg>
                                                    </button>
                                                @endif
                                            </div>
                                        @elseif($msg->isFile())
                                            <!-- File attachment -->
                                            <div class="my-1.5 flex items-center justify-between gap-3 rounded-xl p-2.5 {{ $isMe ? 'bg-amber-700/40 text-white' : 'bg-gray-100 dark:bg-slate-700/60 text-gray-900 dark:text-white' }}">
                                                <div class="flex items-center gap-2 min-w-0">
                                                    <svg class="h-6 w-6 flex-shrink-0 opacity-80" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                    </svg>
                                                    <div class="min-w-0">
                                                        <p class="truncate text-xs font-semibold">{{ $msg->attachment_name }}</p>
                                                        <span class="text-[10px] opacity-75">{{ $msg->getFormattedFileSize() }}</span>
                                                    </div>
                                                </div>
                                                <div class="flex items-center gap-1 flex-shrink-0">
                                                    <a
                                                        href="{{ $msg->getAttachmentUrl() }}"
                                                        download="{{ $msg->attachment_name }}"
                                                        class="rounded-lg p-1 hover:bg-black/10 dark:hover:bg-white/10"
                                                        title="Download file"
                                                    >
                                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                                        </svg>
                                                    </a>
                                                    @if($canDelete)
                                                        <button
                                                            type="button"
                                                            wire:confirm="Delete this file attachment? It will be replaced with 'This message was deleted'."
                                                            wire:click="deleteMessage({{ $msg->id }})"
                                                            class="rounded-lg p-1 text-red-500 hover:bg-red-500/20 transition"
                                                            title="Delete file (valid for 15 mins or admin)"
                                                        >
                                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                            </svg>
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>
                                        @endif

                                        @if($msg->body && (!$msg->isImage() && !$msg->isAudio() && !$msg->isFile() || $msg->body !== $msg->attachment_name))
                                            <p class="whitespace-pre-wrap break-words leading-relaxed">{!! $msg->getFormattedBodyHtml($isMe) !!}</p>
                                        @endif
                                    @endif

                                    <div class="mt-1 flex items-center justify-end gap-1 text-[10px] {{ $isMe ? 'text-amber-100/80' : 'text-gray-400 dark:text-slate-400' }}">
                                        <span>{{ $msg->created_at?->format('h:i A') }}</span>
                                        @if($isMe && !$msg->is_deleted)
                                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        @endif
                                    </div>
                                </div>

                                <!-- Action Buttons on Hover (Reply, Forward, Pin, Delete) -->
                                @if(!$msg->is_deleted)
                                    <div class="opacity-0 group-hover:opacity-100 transition-opacity flex items-center gap-1 self-center">
                                        <!-- Reply Button -->
                                        <button
                                            type="button"
                                            wire:click="setReply({{ $msg->id }})"
                                            class="rounded-lg p-1 text-gray-400 hover:text-amber-600 hover:bg-gray-100 dark:hover:bg-slate-800 transition"
                                            title="Reply to message"
                                        >
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6-6m-6-6l6 6"/>
                                            </svg>
                                        </button>

                                        <!-- Forward Button -->
                                        <button
                                            type="button"
                                            wire:click="openForwardModal({{ $msg->id }})"
                                            class="rounded-lg p-1 text-gray-400 hover:text-amber-600 hover:bg-gray-100 dark:hover:bg-slate-800 transition"
                                            title="Forward message"
                                        >
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 10H11a8 8 0 00-8 8v2m18-10l-6-6m6 6l-6 6"/>
                                            </svg>
                                        </button>
                                        @if($isPinned)
                                            <button
                                                type="button"
                                                wire:click="unpinMessage({{ $msg->id }})"
                                                class="rounded-lg p-1 text-amber-600 hover:bg-amber-100 dark:text-amber-400 dark:hover:bg-slate-800"
                                                title="Unpin message"
                                            >
                                                <span class="text-xs">📌</span>
                                            </button>
                                        @else
                                            <button
                                                type="button"
                                                wire:click="openPinMessageModal({{ $msg->id }})"
                                                class="rounded-lg p-1 text-gray-400 hover:text-amber-600 hover:bg-gray-100 dark:hover:bg-slate-800"
                                                title="Pin message on top"
                                            >
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                                                </svg>
                                            </button>
                                        @endif

                                        @if($canDelete)
                                            <button
                                                type="button"
                                                wire:confirm="Delete this message? It will be replaced with 'This message was deleted'."
                                                wire:click="deleteMessage({{ $msg->id }})"
                                                class="rounded-lg p-1 text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-950/40"
                                                title="Delete message (valid for 15 mins or admin)"
                                            >
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
                                    class="absolute -top-3.5 {{ $isMe ? 'right-4' : 'left-9' }} z-20 hidden group-hover:flex items-center gap-0.5 rounded-full border border-gray-200 bg-white/95 px-1.5 py-0.5 shadow-md backdrop-blur dark:border-slate-700 dark:bg-slate-800/95"
                                >
                                    @php
                                        $quickEmojis = ['👍', '❤️', '😂', '😮', '😢', '🔥'];
                                        $extraEmojis = ['🎉', '👏', '🙏', '💯', '🚀', '👀'];
                                    @endphp
                                    @foreach($quickEmojis as $qEmoji)
                                        <button
                                            type="button"
                                            wire:click="toggleReaction({{ $msg->id }}, '{{ $qEmoji }}')"
                                            class="rounded-full p-0.5 text-sm transition-transform hover:scale-125 active:scale-95"
                                            title="React {{ $qEmoji }}"
                                        >
                                            {{ $qEmoji }}
                                        </button>
                                    @endforeach

                                    <div class="relative">
                                        <button
                                            type="button"
                                            @click="showExtraEmojis = !showExtraEmojis"
                                            class="rounded-full p-0.5 text-xs text-gray-500 hover:text-amber-600 dark:text-slate-400 dark:hover:text-amber-400 transition"
                                            title="More emojis"
                                        >
                                            ➕
                                        </button>

                                        <div
                                            x-show="showExtraEmojis"
                                            x-transition
                                            @click.outside="showExtraEmojis = false"
                                            class="absolute bottom-full mb-1 {{ $isMe ? 'right-0' : 'left-0' }} flex items-center gap-1 rounded-full border border-gray-200 bg-white/95 p-1 shadow-xl backdrop-blur dark:border-slate-700 dark:bg-slate-800/95 z-30"
                                            style="display: none;"
                                        >
                                            @foreach($extraEmojis as $eEmoji)
                                                <button
                                                    type="button"
                                                    wire:click="toggleReaction({{ $msg->id }}, '{{ $eEmoji }}')"
                                                    @click="showExtraEmojis = false"
                                                    class="rounded-full p-1 text-sm transition-transform hover:scale-125 active:scale-95"
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
                                    <div class="mt-1 flex flex-wrap items-center gap-1 {{ $isMe ? 'justify-end pr-2' : 'justify-start pl-9' }}">
                                        @foreach($reactionsSummary as $rec)
                                            <button
                                                type="button"
                                                wire:click="toggleReaction({{ $msg->id }}, '{{ $rec['reaction'] }}')"
                                                class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs transition-all shadow-sm cursor-pointer {{ $rec['reacted_by_me'] ? 'bg-amber-100 text-amber-900 border border-amber-300 dark:bg-amber-950/60 dark:text-amber-300 dark:border-amber-700/80 font-bold ring-1 ring-amber-400/40' : 'bg-white text-gray-700 border border-gray-200 hover:bg-gray-100 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700 dark:hover:bg-slate-700' }}"
                                                title="{{ implode(', ', $rec['users']) }} reacted"
                                            >
                                                <span class="text-sm leading-none">{{ $rec['reaction'] }}</span>
                                                <span class="text-[11px] font-semibold">{{ $rec['count'] }}</span>
                                            </button>
                                        @endforeach
                                    </div>
                                @endif
                            @endif
                        </div>
                    @empty
                        <div class="flex h-full items-center justify-center text-center">
                            <div class="rounded-2xl border border-dashed border-gray-300 p-6 dark:border-slate-800 text-gray-400 dark:text-slate-500 max-w-sm">
                                <p class="text-xs">No messages yet. Send a friendly message or voice note to begin!</p>
                            </div>
                        </div>
                    @endforelse
                </div>

                <!-- Input Footer Area -->
                <div 
                    x-data="{
                        showMentionMenu: false,
                        mentionQuery: '',
                        mentionIndex: 0,
                        mentionStartIndex: 0,
                        mentionCursorPos: 0,
                        members: @js($this->groupMembers),
                        isGroup: {{ $isGroup ? 'true' : 'false' }},

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
                            const input = this.$refs.messageInput;
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
                            const input = this.$refs.messageInput;
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
                            const input = this.$refs.messageInput;
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

                        handleInputKeyDown(e) {
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
                    class="relative border-t border-gray-200 bg-white p-3 dark:border-slate-800 dark:bg-slate-900"
                >
                    <!-- Mention Autocomplete Popover -->
                    @if($isGroup)
                        <div
                            x-show="showMentionMenu && filteredMembers.length > 0"
                            x-transition:enter="transition ease-out duration-150"
                            x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                            x-transition:leave="transition ease-in duration-100"
                            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                            x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                            @click.outside="showMentionMenu = false"
                            class="absolute bottom-full left-4 mb-2 w-72 sm:w-80 max-h-64 overflow-y-auto rounded-2xl border border-gray-200 bg-white/95 p-1.5 shadow-2xl backdrop-blur-md dark:border-slate-700/80 dark:bg-slate-900/95 z-50 divide-y divide-gray-100 dark:divide-slate-800"
                            style="display: none;"
                        >
                            <div class="px-2.5 py-1.5 text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-slate-400 flex items-center justify-between">
                                <span class="flex items-center gap-1">
                                    <svg class="h-3 w-3 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                                    </svg>
                                    Mention Member
                                </span>
                                <span class="text-[9px] font-normal lowercase opacity-70">↑↓ / ↵</span>
                            </div>

                            <div class="py-1 space-y-0.5">
                                <template x-for="(member, idx) in filteredMembers" :key="member.id">
                                    <button
                                        type="button"
                                        @mousedown.prevent="selectMention(member)"
                                        @mouseenter="mentionIndex = idx"
                                        class="flex w-full items-center gap-2.5 rounded-xl px-2.5 py-2 text-left text-xs transition-colors"
                                        :class="mentionIndex === idx 
                                            ? 'bg-amber-500/15 text-amber-900 dark:bg-amber-400/20 dark:text-amber-200 ring-1 ring-amber-400/30' 
                                            : 'text-gray-800 hover:bg-gray-100/80 dark:text-slate-200 dark:hover:bg-slate-800/80'"
                                    >
                                        <!-- Avatar / Icon -->
                                        <template x-if="member.isAll">
                                            <div class="flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-full bg-amber-500 text-white font-bold text-xs shadow-sm">
                                                📢
                                            </div>
                                        </template>
                                        <template x-if="!member.isAll && member.avatar">
                                            <img :src="member.avatar" class="h-7 w-7 flex-shrink-0 rounded-full object-cover border border-gray-200 dark:border-slate-700" />
                                        </template>
                                        <template x-if="!member.isAll && !member.avatar">
                                            <div class="flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-full bg-amber-100 text-amber-800 dark:bg-slate-800 dark:text-amber-300 font-bold text-xs">
                                                <span x-text="member.initial"></span>
                                            </div>
                                        </template>

                                        <!-- Name & Tag -->
                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-center gap-1.5">
                                                <span class="truncate font-semibold text-xs" x-text="member.name"></span>
                                                <template x-if="member.isAll">
                                                    <span class="rounded bg-amber-500/20 px-1 py-0.2 text-[9px] font-bold text-amber-800 dark:bg-amber-400/20 dark:text-amber-300">
                                                        ALL
                                                    </span>
                                                </template>
                                            </div>
                                            <div class="text-[11px] text-gray-500 dark:text-slate-400 truncate">
                                                <span class="text-amber-600 dark:text-amber-400 font-medium">@<span x-text="member.tag"></span></span>
                                                <template x-if="member.isAll">
                                                    <span class="text-[10px] ml-1 opacity-80">(Notify everyone)</span>
                                                </template>
                                            </div>
                                        </div>
                                    </button>
                                </template>
                            </div>
                        </div>
                    @endif

                    <!-- Reply Preview Chip -->
                    @if($replyingToMessageId && $this->replyingToMessage)
                        @php $repMsg = $this->replyingToMessage; @endphp
                        <div class="mb-2 flex items-center justify-between rounded-xl bg-amber-500/10 border-l-4 border-amber-500 px-3 py-2 text-xs dark:bg-amber-950/30 dark:border-amber-400">
                            <div class="flex items-center gap-2 min-w-0">
                                <svg class="h-4 w-4 text-amber-600 dark:text-amber-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6-6m-6-6l6 6"/>
                                </svg>
                                <div class="min-w-0">
                                    <span class="block font-bold text-amber-700 dark:text-amber-300 text-[11px]">
                                        Replying to {{ $repMsg->sender?->name ?? 'User' }}
                                    </span>
                                    <p class="truncate text-gray-600 dark:text-slate-300 text-[11px]">
                                        {{ $repMsg->getReplySnippet(80) }}
                                    </p>
                                </div>
                            </div>
                            <button
                                type="button"
                                wire:click="cancelReply"
                                class="text-gray-400 hover:text-red-500 p-1 rounded-lg transition"
                                title="Cancel reply"
                            >
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    @endif

                    <!-- Attachment Preview Chip -->
                    @if($attachment)
                        <div class="mb-2 flex items-center justify-between rounded-xl bg-amber-50 p-2 text-xs dark:bg-slate-800/80 border border-amber-200 dark:border-slate-700">
                            <div class="flex items-center gap-2 min-w-0">
                                <svg class="h-4 w-4 text-amber-600 dark:text-amber-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                </svg>
                                <span class="truncate font-medium text-gray-900 dark:text-white">{{ $attachment->getClientOriginalName() }}</span>
                            </div>
                            <button
                                type="button"
                                wire:click="removeAttachment"
                                class="text-gray-400 hover:text-red-500"
                            >
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    @endif

                    @if($isGroup && !$this->activeConversation->canUserSendMessage(auth()->id()))
                        <div class="flex items-center justify-center gap-2 rounded-xl bg-amber-50/70 p-3 text-xs text-amber-800 dark:bg-amber-950/40 dark:text-amber-300 border border-amber-200/70 dark:border-amber-900/50">
                            <svg class="h-4 w-4 text-amber-600 dark:text-amber-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                            <span class="font-medium">Only group admins can send messages to this group.</span>
                        </div>
                    @else
                        <!-- Modern Live Microphone Waveform Recording View -->
                        <div 
                            x-show="isRecording" 
                            class="flex flex-col gap-2 rounded-2xl p-3 border shadow-inner transition-all"
                            :class="isRecordingEnded
                                ? 'border-emerald-300 bg-emerald-50/80 dark:border-emerald-800/60 dark:bg-emerald-950/30'
                                : (isPaused 
                                    ? 'border-amber-300 bg-amber-50/80 dark:border-amber-800/60 dark:bg-amber-950/30'
                                    : (recordingTime >= maxRecordingSeconds - 10
                                        ? 'border-red-300 bg-red-50/80 dark:border-red-800/60 dark:bg-red-950/30'
                                        : 'border-red-200/80 bg-gradient-to-r from-red-500/10 via-red-500/5 to-amber-500/10 dark:border-red-900/60 dark:bg-red-950/20'))"
                            style="display: none;"
                        >
                            <div class="flex items-center justify-between gap-3 min-w-0">
                                <div class="flex items-center gap-3 min-w-0 flex-1">
                                    <!-- Pulsing Recording Indicator -->
                                    <div class="relative flex h-3.5 w-3.5 items-center justify-center flex-shrink-0">
                                        <template x-if="!isRecordingEnded && !isPaused">
                                            <span class="absolute inline-flex h-full w-full rounded-full opacity-75 bg-red-400 animate-ping"></span>
                                        </template>
                                        <span 
                                            class="relative inline-flex h-2.5 w-2.5 rounded-full"
                                            :class="isPlayingPreview
                                                ? 'bg-indigo-500 animate-pulse'
                                                : (isRecordingEnded 
                                                    ? 'bg-emerald-500' 
                                                    : (isPaused ? 'bg-amber-500' : 'bg-red-600'))"
                                        ></span>
                                    </div>

                                    <!-- Waveform Bars: Live mic when recording, audio progress when previewing -->
                                    <div class="flex flex-1 items-center gap-[2.5px] h-7 overflow-hidden">
                                        <template x-for="(h, i) in vizBars" :key="i">
                                            <div 
                                                class="w-[3px] rounded-full transition-all duration-75"
                                                :style="`height: ${h}px;`"
                                                :class="isPlayingPreview
                                                    ? (i <= Math.floor(previewProgressRatio * vizBars.length)
                                                        ? 'bg-indigo-600 dark:bg-indigo-400'
                                                        : 'bg-indigo-200 dark:bg-indigo-950')
                                                    : (isRecordingEnded
                                                        ? 'bg-emerald-500/80 dark:bg-emerald-400'
                                                        : (isPaused 
                                                            ? 'bg-amber-500/80 dark:bg-amber-400' 
                                                            : (recordingTime >= maxRecordingSeconds - 10 ? 'bg-red-500' : 'bg-red-500/90 dark:bg-red-400')))"
                                            ></div>
                                        </template>
                                    </div>

                                    <!-- Timer + Status indicator -->
                                    <div class="flex items-center gap-2 flex-shrink-0">
                                        <span 
                                            class="font-mono text-xs font-bold tabular-nums px-2 py-0.5 rounded-md"
                                            :class="isPlayingPreview
                                                ? 'text-indigo-700 bg-indigo-100 dark:text-indigo-300 dark:bg-indigo-900/50'
                                                : (isRecordingEnded
                                                    ? 'text-emerald-700 bg-emerald-100 dark:text-emerald-300 dark:bg-emerald-900/50'
                                                    : (isPaused
                                                        ? 'text-amber-700 bg-amber-100 dark:text-amber-300 dark:bg-amber-900/50'
                                                        : (recordingTime >= maxRecordingSeconds - 10
                                                            ? 'text-red-700 bg-red-100 dark:text-red-300 dark:bg-red-900/50'
                                                            : 'text-red-600 bg-red-100/60 dark:text-red-300 dark:bg-red-900/40')))"
                                            x-text="isPlayingPreview
                                                ? (formatTime(previewCurrentTime) + ' / ' + formatTime(recordingTime))
                                                : (isRecordingEnded
                                                    ? '01:00 (Ready)'
                                                    : (isPaused
                                                        ? (formatTime(recordingTime) + ' (Paused)')
                                                        : (formatTime(recordingTime) + ' / 1:00')))"
                                        >00:00 / 1:00</span>
                                    </div>
                                </div>

                                <div class="flex items-center gap-2 flex-shrink-0">
                                    <!-- Discard Recording Button -->
                                    <button
                                        type="button"
                                        @click="cancelRecording"
                                        :disabled="isUploadingVoice"
                                        class="flex items-center gap-1.5 rounded-xl border border-red-200 bg-white/90 px-2.5 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-50 hover:border-red-300 dark:border-red-900/60 dark:bg-slate-800 dark:text-red-400 dark:hover:bg-red-950/60 transition shadow-sm disabled:opacity-50"
                                        title="Discard recording"
                                    >
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                        <span class="text-xs">Discard</span>
                                    </button>

                                    <!-- Play / Pause Preview Button (when paused or recording ended) -->
                                    <template x-if="isPaused || isRecordingEnded">
                                        <button
                                            type="button"
                                            @click="togglePlayPreview"
                                            class="flex h-8 w-8 items-center justify-center rounded-full border shadow-sm transition active:scale-95"
                                            :class="isPlayingPreview 
                                                ? 'bg-indigo-600 text-white border-indigo-600 hover:bg-indigo-700' 
                                                : 'bg-white text-gray-700 hover:bg-gray-50 border-gray-300 dark:border-gray-600 dark:bg-slate-800 dark:text-gray-200'"
                                            :title="isPlayingPreview ? 'Pause preview' : 'Play preview'"
                                        >
                                            <template x-if="!isPlayingPreview">
                                                <svg class="h-3.5 w-3.5 ml-0.5" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M8 5v14l11-7z"/>
                                                </svg>
                                            </template>
                                            <template x-if="isPlayingPreview">
                                                <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/>
                                                </svg>
                                            </template>
                                        </button>
                                    </template>

                                    <!-- Resume Recording Button (only when paused before timer ended) -->
                                    <template x-if="isPaused && !isRecordingEnded">
                                        <button
                                            type="button"
                                            @click="resumeRecording"
                                            class="inline-flex items-center gap-1.5 h-8 px-2.5 rounded-xl border border-amber-300 bg-amber-50 text-amber-800 hover:bg-amber-100 dark:border-amber-700 dark:bg-amber-950/60 dark:text-amber-200 text-xs font-semibold shadow-sm transition active:scale-95"
                                            title="Resume recording"
                                        >
                                            <svg class="h-3.5 w-3.5 text-red-600" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M12 14c1.66 0 3-1.34 3-3V5c0-1.66-1.34-3-3-3S9 3.34 9 5v6c0 1.66 1.34 3 3 3z"/>
                                                <path d="M17 11c0 2.76-2.24 5-5 5s-5-2.24-5-5H5c0 3.53 2.61 6.43 6 6.92V21h2v-3.08c3.39-.49 6-3.39 6-6.92h-2z"/>
                                            </svg>
                                            <span>Resume</span>
                                        </button>
                                    </template>

                                    <!-- Pause Button (only while actively recording) -->
                                    <template x-if="!isPaused && !isRecordingEnded">
                                        <button
                                            type="button"
                                            @click="pauseRecording"
                                            class="flex h-8 w-8 items-center justify-center rounded-full border border-gray-300 bg-white text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-600 dark:bg-slate-800 dark:text-gray-300 transition active:scale-95"
                                            title="Pause recording"
                                        >
                                            <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 24 24">
                                                <path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/>
                                            </svg>
                                        </button>
                                    </template>

                                    <!-- Stop and Send Voice Recording Button -->
                                    <button
                                        type="button"
                                        @click="stopAndSendRecording"
                                        :disabled="isUploadingVoice"
                                        class="inline-flex items-center gap-1.5 rounded-xl px-3.5 py-1.5 text-xs font-bold text-white shadow-md transition-transform active:scale-95 disabled:opacity-75 disabled:cursor-not-allowed"
                                        :class="isRecordingEnded 
                                            ? 'bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500' 
                                            : 'bg-gradient-to-r from-red-600 to-amber-600 hover:from-red-500 hover:to-amber-500'"
                                        title="Send voice message"
                                    >
                                        <template x-if="isUploadingVoice">
                                            <svg class="h-4 w-4 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                        </template>
                                        <template x-if="!isUploadingVoice">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                            </svg>
                                        </template>
                                        <span x-text="isUploadingVoice ? 'Sending...' : 'Send Voice'">Send Voice</span>
                                    </button>
                                </div>
                            </div>

                            <!-- 60-Second Limit Progress Bar / Preview Progress Bar -->
                            <div class="h-1 w-full overflow-hidden rounded-full bg-gray-200 dark:bg-slate-700">
                                <div 
                                    class="h-full rounded-full transition-all duration-300"
                                    :class="isPlayingPreview 
                                        ? 'bg-indigo-500' 
                                        : (isRecordingEnded 
                                            ? 'bg-emerald-500' 
                                            : (recordingTime >= maxRecordingSeconds - 10 ? 'bg-red-500' : 'bg-gradient-to-r from-amber-500 to-red-500'))"
                                    :style="isPlayingPreview 
                                        ? `width: ${previewProgressRatio * 100}%` 
                                        : `width: ${isRecordingEnded ? 100 : ((recordingTime / maxRecordingSeconds) * 100)}%`"
                                ></div>
                            </div>
                        </div>

                        <form x-show="!isRecording" wire:submit.prevent="sendMessage" class="flex items-center gap-2">
                            <!-- Attachment File Input (Hidden) -->
                            <input
                                type="file"
                                x-ref="fileInput"
                                wire:model="attachment"
                                class="hidden"
                            />

                            <!-- Attach Button (Paperclip) -->
                            <button
                                type="button"
                                @click="$refs.fileInput.click()"
                                class="rounded-xl p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-slate-200 transition"
                                title="Attach image or file"
                            >
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                </svg>
                            </button>

                            <!-- Voice Note Button (Microphone) -->
                            <button
                                type="button"
                                @click="startRecording"
                                class="rounded-xl p-2 text-gray-400 hover:bg-gray-100 hover:text-amber-600 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-amber-400 transition"
                                title="Record voice message"
                            >
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"/>
                                </svg>
                            </button>

                            @if($isGroup)
                                <!-- Quick Mention Button (@) -->
                                <button
                                    type="button"
                                    @click="openMentionMenu"
                                    class="rounded-xl p-2 text-gray-400 hover:bg-gray-100 hover:text-amber-600 dark:text-slate-400 dark:hover:bg-slate-800 dark:hover:text-amber-400 transition"
                                    title="Mention group member (@all or @member)"
                                >
                                    <span class="flex h-5 w-5 items-center justify-center text-sm font-black leading-none select-none">@</span>
                                </button>
                            @endif

                            <!-- Text Input -->
                            <div class="relative flex-1">
                                <input
                                    type="text"
                                    x-ref="messageInput"
                                    wire:model="messageText"
                                    @input="detectMention"
                                    @click="detectMention"
                                    @keyup="detectMention"
                                    @keydown="handleInputKeyDown"
                                    placeholder="{{ $isGroup ? 'Write a message... (Type @ to mention, Enter to send)' : 'Write a message... (Press Enter to send)' }}"
                                    autocomplete="off"
                                    class="chat-input-field w-full rounded-xl border border-gray-300 bg-white px-4 py-2.5 text-xs text-gray-900 placeholder-gray-400 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500 dark:border-slate-700/80 dark:bg-slate-800 dark:text-gray-100 dark:placeholder-slate-400"
                                />
                            </div>

                            <!-- Send Button -->
                            <button
                                type="submit"
                                wire:loading.attr="disabled"
                                class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-amber-600 text-white shadow-md transition hover:bg-amber-500 active:scale-95 disabled:opacity-50"
                            >
                                <svg wire:loading.remove wire:target="sendMessage,attachment" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                </svg>
                                <svg wire:loading wire:target="sendMessage,attachment" class="h-4 w-4 animate-spin text-white" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                                </svg>
                            </button>
                        </form>
                    @endif
                </div>
            @else
                <!-- No Conversation Selected -->
                <div class="flex flex-1 flex-col items-center justify-center p-8 text-center">
                    <div class="flex h-16 w-16 items-center justify-center rounded-2xl bg-amber-50 text-amber-600 dark:bg-amber-950/40 dark:text-amber-400 shadow-inner">
                        <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                    </div>
                    <h3 class="mt-4 text-sm font-bold text-gray-900 dark:text-white">Your Chat Hub</h3>
                    <p class="mt-1 max-w-xs text-xs text-gray-500 dark:text-slate-400">
                        Select a conversation, start a direct message, or create a group chat.
                    </p>
                    <button
                        type="button"
                        wire:click="$set('showNewChatModal', true)"
                        class="mt-4 inline-flex items-center gap-1.5 rounded-xl bg-amber-600 px-4 py-2 text-xs font-semibold text-white shadow-md transition hover:bg-amber-500 active:scale-95"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span>Start New Conversation</span>
                    </button>
                </div>
            @endif
        </div>
    </div>

    <!-- New Chat & Create Group Modal -->
    @if($showNewChatModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 p-4 backdrop-blur-sm">
            <div 
                @click.outside="$wire.set('showNewChatModal', false)"
                class="relative w-full max-w-md overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xl dark:border-slate-800 dark:bg-slate-900"
            >
                <!-- Modal Tabs -->
                <div class="flex items-center justify-between border-b border-gray-100 dark:border-slate-800 px-4 pt-3">
                    <div class="flex gap-4">
                        <button
                            type="button"
                            wire:click="$set('modalTab', 'direct')"
                            class="pb-3 text-xs font-bold transition border-b-2 {{ $modalTab === 'direct' ? 'border-amber-600 text-amber-600 dark:border-amber-400 dark:text-amber-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-slate-400' }}"
                        >
                            Direct Chat
                        </button>
                        <button
                            type="button"
                            wire:click="$set('modalTab', 'group')"
                            class="pb-3 text-xs font-bold transition border-b-2 {{ $modalTab === 'group' ? 'border-amber-600 text-amber-600 dark:border-amber-400 dark:text-amber-400' : 'border-transparent text-gray-500 hover:text-gray-700 dark:text-slate-400' }}"
                        >
                            Create Group
                        </button>
                    </div>
                    <button 
                        type="button"
                        wire:click="$set('showNewChatModal', false)"
                        class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-slate-800"
                    >
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                @if($modalTab === 'group')
                    <!-- Group Name Input -->
                    <div class="p-3 border-b border-gray-100 dark:border-slate-800 space-y-2 bg-gray-50/50 dark:bg-slate-950/40">
                        <label class="block text-xs font-semibold text-gray-700 dark:text-slate-300">Group Name</label>
                        <input
                            type="text"
                            wire:model="groupTitle"
                            placeholder="e.g. Management Team, Project Alpha..."
                            class="chat-input-field w-full rounded-xl border border-gray-300 bg-white px-3.5 py-2 text-xs text-gray-900 placeholder-gray-400 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500 dark:border-slate-700 dark:bg-slate-800 dark:text-gray-100"
                        />
                        <div class="flex items-center justify-between text-[11px] text-gray-500 dark:text-slate-400 pt-1">
                            <span>Selected Members: <strong>{{ count($selectedGroupMembers) }}</strong></span>
                            @if(!empty($selectedGroupMembers) && filled($groupTitle))
                                <button
                                    type="button"
                                    wire:click="createGroupChat"
                                    class="rounded-lg bg-amber-600 px-3 py-1 text-xs font-bold text-white shadow hover:bg-amber-500"
                                >
                                    Create Group &rarr;
                                </button>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Search Users -->
                <div class="p-3 border-b border-gray-100 dark:border-slate-800">
                    <input
                        type="text"
                        wire:model.live.debounce.250ms="userSearch"
                        placeholder="Search by name, username, or email..."
                        class="chat-input-field w-full rounded-xl border border-gray-300 bg-white px-3.5 py-2 text-xs text-gray-900 placeholder-gray-400 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500 dark:border-slate-700 dark:bg-slate-800 dark:text-gray-100"
                    />
                </div>

                <!-- Users List -->
                <div class="max-h-72 overflow-y-auto divide-y divide-gray-100 dark:divide-slate-800">
                    @forelse($this->availableUsers as $user)
                        @php
                            $isSelected = in_array($user->id, $selectedGroupMembers, true);
                            $avatarUrl = $user->getFilamentAvatarUrl();
                        @endphp
                        <div
                            wire:key="modal-user-{{ $user->id }}"
                            wire:click="{{ $modalTab === 'group' ? 'toggleGroupMember(' . $user->id . ')' : 'startConversationWith(' . $user->id . ')' }}"
                            class="flex cursor-pointer items-center justify-between p-3 transition hover:bg-amber-50/70 dark:hover:bg-slate-800/70 {{ $isSelected ? 'bg-amber-50 dark:bg-amber-950/40' : '' }}"
                        >
                            <div class="flex items-center gap-3 min-w-0">
                                @if($avatarUrl)
                                    <img src="{{ $avatarUrl }}" class="h-9 w-9 rounded-full object-cover shadow-sm border border-gray-100 dark:border-slate-700" />
                                @else
                                    <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-full bg-gradient-to-tr from-amber-600 to-amber-400 text-xs font-bold text-white shadow-sm">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                @endif
                                <div class="min-w-0">
                                    <p class="truncate text-xs font-semibold text-gray-900 dark:text-white">{{ $user->name }}</p>
                                    <div class="flex items-center gap-2 text-[10px] text-gray-500 dark:text-slate-400">
                                        @if($user->username)
                                            <span class="font-medium text-amber-600 dark:text-amber-400">@<span>{{ $user->username }}</span></span>
                                        @endif
                                        <span class="truncate">{{ $user->email }}</span>
                                    </div>
                                </div>
                            </div>
                            @if($modalTab === 'group')
                                <input
                                    type="checkbox"
                                    class="rounded border-gray-300 text-amber-600 focus:ring-amber-500 dark:border-slate-700 dark:bg-slate-800"
                                    {{ $isSelected ? 'checked' : '' }}
                                    wire:click.stop="toggleGroupMember({{ $user->id }})"
                                />
                            @else
                                <span class="flex-shrink-0 text-xs font-semibold text-amber-600 dark:text-amber-400">Chat &rarr;</span>
                            @endif
                        </div>
                    @empty
                        <div class="p-6 text-center text-xs text-gray-400 dark:text-slate-500">
                            No registered users found matching your search.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    @endif

    <!-- Duration Picker Modal for Pinning (Message or Conversation) -->
    @if($pinningMessageId || $pinningConversationId)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 p-4 backdrop-blur-sm">
            <div
                @click.outside="$wire.closePinMessageModal(); $wire.closePinConversationModal();"
                class="relative w-full max-w-sm overflow-hidden rounded-2xl border border-gray-200 bg-white p-5 shadow-2xl dark:border-slate-800 dark:bg-slate-900"
            >
                <div class="flex items-center justify-between border-b border-gray-100 pb-3 dark:border-slate-800">
                    <div class="flex items-center gap-2">
                        <span class="text-xl">📌</span>
                        <h4 class="text-sm font-bold text-gray-900 dark:text-white">
                            {{ $pinningMessageId ? 'Pin Message on Top' : 'Pin Conversation on Top' }}
                        </h4>
                    </div>
                    <button
                        type="button"
                        wire:click="{{ $pinningMessageId ? 'closePinMessageModal' : 'closePinConversationModal' }}"
                        class="rounded-lg p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"
                    >
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <p class="py-3 text-xs text-gray-500 dark:text-slate-400">
                    Choose how long you want this pin to remain active on top:
                </p>

                <div class="grid grid-cols-2 gap-2.5 pt-1">
                    @php
                        $durations = [
                            ['label' => '8 Hours', 'val' => '8_hours', 'icon' => '⏱️', 'desc' => 'Until 8 hours pass'],
                            ['label' => '1 Day', 'val' => '1_day', 'icon' => '📅', 'desc' => '24 hours'],
                            ['label' => '1 Week', 'val' => '1_week', 'icon' => '🗓️', 'desc' => '7 days'],
                            ['label' => '1 Month', 'val' => '1_month', 'icon' => '📆', 'desc' => '30 days'],
                            ['label' => '1 Year', 'val' => '1_year', 'icon' => '🎂', 'desc' => '365 days'],
                            ['label' => 'Never Ending', 'val' => 'never_ending', 'icon' => '♾️', 'desc' => 'Until unpinned'],
                        ];
                    @endphp
                    @foreach($durations as $d)
                        <button
                            type="button"
                            wire:click="{{ $pinningMessageId ? 'pinMessage(' . $pinningMessageId . ', \'' . $d['val'] . '\')' : 'pinConversation(' . $pinningConversationId . ', \'' . $d['val'] . '\')' }}"
                            class="flex flex-col rounded-xl border border-gray-200 p-2.5 text-left transition hover:border-amber-500 hover:bg-amber-50 dark:border-slate-700 dark:hover:border-amber-500 dark:hover:bg-amber-950/30"
                        >
                            <div class="flex items-center gap-1.5 font-bold text-xs text-gray-900 dark:text-white">
                                <span>{{ $d['icon'] }}</span>
                                <span>{{ $d['label'] }}</span>
                            </div>
                            <span class="text-[10px] text-gray-400 dark:text-slate-500 mt-0.5">{{ $d['desc'] }}</span>
                        </button>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- Group Settings & Management Modal -->
    @if($showGroupSettingsModal && $this->activeConversation && $this->activeConversation->isGroup())
        @php
            $isOwner = $this->activeConversation->isOwner(auth()->id());
            $isAdmin = $this->activeConversation->isAdmin(auth()->id());
            $canEditInfo = $this->activeConversation->canUserEditInfo(auth()->id());
            $participants = $this->groupParticipantDetails;
        @endphp
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 p-4 backdrop-blur-sm">
            <div
                @click.outside="$wire.set('showGroupSettingsModal', false)"
                class="relative w-full max-w-xl overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xl dark:border-slate-800 dark:bg-slate-900 flex flex-col max-h-[90vh]"
            >
                <!-- Modal Header -->
                <div class="flex items-center justify-between border-b border-gray-100 px-5 py-3.5 dark:border-slate-800">
                    <div class="flex items-center gap-2.5">
                        <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-amber-500/10 text-amber-600 dark:bg-amber-400/10 dark:text-amber-400 text-sm">
                            ⚙️
                        </span>
                        <div>
                            <h3 class="text-sm font-bold text-gray-900 dark:text-white">Group Settings</h3>
                            <p class="text-[11px] text-gray-500 dark:text-slate-400">{{ $this->activeConversation->title }}</p>
                        </div>
                    </div>
                    <button
                        type="button"
                        wire:click="$set('showGroupSettingsModal', false)"
                        class="rounded-lg p-1.5 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-slate-800 dark:hover:text-slate-200"
                    >
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <!-- Navigation Tabs -->
                <div class="flex border-b border-gray-100 px-5 gap-4 dark:border-slate-800 bg-gray-50/50 dark:bg-slate-950/40 text-xs font-semibold">
                    <button
                        type="button"
                        wire:click="$set('groupSettingsTab', 'general')"
                        class="py-2.5 border-b-2 transition {{ $groupSettingsTab === 'general' ? 'border-amber-600 text-amber-600 dark:border-amber-400 dark:text-amber-400' : 'border-transparent text-gray-500 hover:text-gray-800 dark:text-slate-400 dark:hover:text-slate-200' }}"
                    >
                        General
                    </button>
                    <button
                        type="button"
                        wire:click="$set('groupSettingsTab', 'members')"
                        class="py-2.5 border-b-2 transition flex items-center gap-1.5 {{ $groupSettingsTab === 'members' ? 'border-amber-600 text-amber-600 dark:border-amber-400 dark:text-amber-400' : 'border-transparent text-gray-500 hover:text-gray-800 dark:text-slate-400 dark:hover:text-slate-200' }}"
                    >
                        <span>Members</span>
                        <span class="rounded-full bg-gray-200 px-1.5 py-0.2 text-[10px] dark:bg-slate-800">{{ $participants->count() }}</span>
                    </button>
                    <button
                        type="button"
                        wire:click="$set('groupSettingsTab', 'permissions')"
                        class="py-2.5 border-b-2 transition {{ $groupSettingsTab === 'permissions' ? 'border-amber-600 text-amber-600 dark:border-amber-400 dark:text-amber-400' : 'border-transparent text-gray-500 hover:text-gray-800 dark:text-slate-400 dark:hover:text-slate-200' }}"
                    >
                        Permissions
                    </button>
                    <button
                        type="button"
                        wire:click="$set('groupSettingsTab', 'danger')"
                        class="py-2.5 border-b-2 transition text-red-600 hover:text-red-700 dark:text-red-400 {{ $groupSettingsTab === 'danger' ? 'border-red-600 dark:border-red-400 font-bold' : 'border-transparent' }}"
                    >
                        Actions
                    </button>
                </div>

                <!-- Tab Content Body -->
                <div class="flex-1 overflow-y-auto p-5 space-y-4">
                    <!-- GENERAL TAB -->
                    @if($groupSettingsTab === 'general')
                        @if(!$canEditInfo)
                            <div class="rounded-xl bg-amber-50 p-3 text-xs text-amber-800 dark:bg-amber-950/40 dark:text-amber-300 border border-amber-200 dark:border-amber-900/40">
                                ℹ️ Only group admins can change the group name, icon, and description.
                            </div>
                        @endif

                        <!-- Group Photo -->
                        <div class="flex items-center gap-4">
                            <div class="relative">
                                @if($settingsGroupAvatar)
                                    <img src="{{ $settingsGroupAvatar->temporaryUrl() }}" class="h-16 w-16 rounded-full object-cover border-2 border-amber-500 shadow-md" />
                                @elseif($this->activeConversation->avatar_url)
                                    <img src="{{ Storage::url($this->activeConversation->avatar_url) }}" class="h-16 w-16 rounded-full object-cover border-2 border-gray-200 dark:border-slate-700 shadow-md" />
                                @else
                                    <div class="flex h-16 w-16 items-center justify-center rounded-full bg-gradient-to-tr from-amber-600 to-amber-400 text-xl font-bold text-white shadow-md">
                                        {{ strtoupper(substr($this->activeConversation->title, 0, 1)) }}
                                    </div>
                                @endif
                            </div>

                            @if($canEditInfo)
                                <div class="space-y-1.5">
                                    <div class="flex items-center gap-2">
                                        <label class="cursor-pointer rounded-xl bg-gray-100 px-3 py-1.5 text-xs font-semibold text-gray-700 transition hover:bg-gray-200 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700">
                                            <span>Upload Photo</span>
                                            <input type="file" wire:model="settingsGroupAvatar" accept="image/*" class="hidden" />
                                        </label>
                                        @if($this->activeConversation->avatar_url || $settingsGroupAvatar)
                                            <button
                                                type="button"
                                                wire:click="removeGroupAvatar"
                                                class="rounded-xl px-2.5 py-1.5 text-xs font-semibold text-red-600 hover:bg-red-50 dark:hover:bg-red-950/40"
                                            >
                                                Remove
                                            </button>
                                        @endif
                                    </div>
                                    <p class="text-[11px] text-gray-400 dark:text-slate-500">JPG, PNG or GIF up to 2MB</p>
                                </div>
                            @endif
                        </div>

                        <!-- Group Title -->
                        <div class="space-y-1.5">
                            <label class="block text-xs font-semibold text-gray-700 dark:text-slate-300">Group Name</label>
                            <input
                                type="text"
                                wire:model="settingsGroupTitle"
                                {{ $canEditInfo ? '' : 'disabled' }}
                                class="chat-input-field w-full rounded-xl border border-gray-300 bg-white px-3.5 py-2 text-xs text-gray-900 placeholder-gray-400 focus:border-amber-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-white disabled:opacity-60"
                            />
                            @error('settingsGroupTitle') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>

                        <!-- Group Description -->
                        <div class="space-y-1.5">
                            <label class="block text-xs font-semibold text-gray-700 dark:text-slate-300">Group Description</label>
                            <textarea
                                wire:model="settingsGroupDescription"
                                rows="3"
                                {{ $canEditInfo ? '' : 'disabled' }}
                                placeholder="Add a description for group members..."
                                class="chat-input-field w-full rounded-xl border border-gray-300 bg-white px-3.5 py-2 text-xs text-gray-900 placeholder-gray-400 focus:border-amber-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-white disabled:opacity-60 resize-none"
                            ></textarea>
                            @error('settingsGroupDescription') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                        </div>

                        @if($canEditInfo)
                            <div class="flex justify-end pt-2">
                                <button
                                    type="button"
                                    wire:click="saveGroupSettings"
                                    class="rounded-xl bg-amber-600 px-4 py-2 text-xs font-bold text-white shadow-md transition hover:bg-amber-500 active:scale-95"
                                >
                                    Save Changes
                                </button>
                            </div>
                        @endif
                    @endif

                    <!-- MEMBERS TAB -->
                    @if($groupSettingsTab === 'members')
                        <div class="flex items-center justify-between pb-1">
                            <span class="text-xs font-bold text-gray-700 dark:text-slate-300">Participants ({{ $participants->count() }})</span>
                            @if($isAdmin)
                                <button
                                    type="button"
                                    wire:click="openAddMembersModal"
                                    class="inline-flex items-center gap-1 rounded-xl bg-amber-600 px-3 py-1.5 text-xs font-semibold text-white shadow transition hover:bg-amber-500 active:scale-95"
                                >
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    <span>Add Members</span>
                                </button>
                            @endif
                        </div>

                        <div class="divide-y divide-gray-100 dark:divide-slate-800 rounded-xl border border-gray-200 dark:border-slate-800 overflow-hidden">
                            @foreach($participants as $p)
                                <div class="flex items-center justify-between p-3 bg-white dark:bg-slate-900 hover:bg-gray-50/70 dark:hover:bg-slate-800/50 transition">
                                    <div class="flex items-center gap-3 min-w-0">
                                        @if($p['avatar'])
                                            <img src="{{ $p['avatar'] }}" class="h-9 w-9 rounded-full object-cover border border-gray-200 dark:border-slate-700 shadow-sm" />
                                        @else
                                            <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-full bg-amber-100 text-amber-800 dark:bg-slate-800 dark:text-amber-300 font-bold text-xs">
                                                {{ $p['initial'] }}
                                            </div>
                                        @endif
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-1.5">
                                                <span class="truncate text-xs font-bold text-gray-900 dark:text-white">{{ $p['name'] }}</span>
                                                @if($p['is_me'])
                                                    <span class="text-[10px] text-gray-400 dark:text-slate-500">(You)</span>
                                                @endif
                                            </div>
                                            <div class="flex items-center gap-1.5 text-[10px] text-gray-400 dark:text-slate-500">
                                                @if($p['username'])
                                                    <span class="text-amber-600 dark:text-amber-400 font-medium">@<span>{{ $p['username'] }}</span></span>
                                                    <span>•</span>
                                                @endif
                                                <span class="truncate">{{ $p['email'] }}</span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <!-- Role Badge -->
                                        @if($p['role'] === 'owner')
                                            <span class="rounded-full bg-amber-500/20 px-2 py-0.5 text-[10px] font-bold text-amber-800 dark:bg-amber-400/20 dark:text-amber-300 ring-1 ring-amber-400/30">
                                                👑 Owner
                                            </span>
                                        @elseif($p['role'] === 'admin')
                                            <span class="rounded-full bg-indigo-500/20 px-2 py-0.5 text-[10px] font-bold text-indigo-800 dark:bg-indigo-400/20 dark:text-indigo-300 ring-1 ring-indigo-400/30">
                                                🛡️ Admin
                                            </span>
                                        @else
                                            <span class="rounded-full bg-gray-100 px-2 py-0.5 text-[10px] font-semibold text-gray-600 dark:bg-slate-800 dark:text-slate-400">
                                                Member
                                            </span>
                                        @endif

                                        <!-- Contextual Actions -->
                                        @if(!$p['is_me'])
                                            @if($isOwner)
                                                <!-- Owner Controls -->
                                                <div class="flex items-center gap-1">
                                                    <!-- Toggle Admin Role -->
                                                    <button
                                                        type="button"
                                                        wire:click="toggleAdminRole({{ $p['id'] }})"
                                                        class="rounded-lg p-1.5 text-xs text-gray-400 hover:bg-gray-100 hover:text-indigo-600 dark:hover:bg-slate-800 dark:hover:text-indigo-400 font-semibold"
                                                        title="{{ $p['is_admin'] ? 'Dismiss Admin' : 'Make Admin' }}"
                                                    >
                                                        {{ $p['is_admin'] ? 'Dismiss Admin' : 'Make Admin' }}
                                                    </button>

                                                    <!-- Transfer Ownership -->
                                                    <button
                                                        type="button"
                                                        wire:click="openTransferOwnershipModal({{ $p['id'] }})"
                                                        class="rounded-lg p-1.5 text-xs text-gray-400 hover:bg-amber-50 hover:text-amber-600 dark:hover:bg-amber-950/40"
                                                        title="Transfer Group Ownership"
                                                    >
                                                        👑
                                                    </button>

                                                    <!-- Remove Member -->
                                                    <button
                                                        type="button"
                                                        wire:confirm="Remove {{ $p['name'] }} from this group?"
                                                        wire:click="removeMemberFromGroup({{ $p['id'] }})"
                                                        class="rounded-lg p-1.5 text-gray-400 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-950/40"
                                                        title="Remove from group"
                                                    >
                                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                        </svg>
                                                    </button>
                                                </div>
                                            @elseif($isAdmin && !$p['is_admin'] && !$p['is_owner'])
                                                <!-- Admin (non-owner) can remove regular members -->
                                                <button
                                                    type="button"
                                                    wire:confirm="Remove {{ $p['name'] }} from this group?"
                                                    wire:click="removeMemberFromGroup({{ $p['id'] }})"
                                                    class="rounded-lg p-1.5 text-gray-400 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-950/40"
                                                    title="Remove from group"
                                                >
                                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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

                    <!-- PERMISSIONS / ADMIN LOGIC TAB -->
                    @if($groupSettingsTab === 'permissions')
                        <div class="space-y-4">
                            @if(!$isAdmin)
                                <div class="rounded-xl bg-amber-50 p-3 text-xs text-amber-800 dark:bg-amber-950/40 dark:text-amber-300 border border-amber-200 dark:border-amber-900/40">
                                    ℹ️ Only group admins can change permission settings.
                                </div>
                            @endif

                            <!-- Rule 1: Send Messages -->
                            <div class="rounded-xl border border-gray-200 dark:border-slate-800 p-4 space-y-2 bg-gray-50/50 dark:bg-slate-950/30">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h4 class="text-xs font-bold text-gray-900 dark:text-white">Send Messages</h4>
                                        <p class="text-[11px] text-gray-500 dark:text-slate-400">Choose who can send messages to this group.</p>
                                    </div>
                                    <span class="text-base">💬</span>
                                </div>
                                <div class="grid grid-cols-2 gap-2 pt-2">
                                    <button
                                        type="button"
                                        @if($isAdmin) wire:click="$set('settingsOnlyAdminsCanMessage', false)" @endif
                                        class="flex items-center gap-2.5 rounded-xl border p-2.5 text-xs text-left transition {{ !$settingsOnlyAdminsCanMessage ? 'border-amber-500 bg-amber-50/70 dark:bg-amber-950/40 font-bold text-amber-900 dark:text-amber-200 ring-2 ring-amber-500/20' : 'border-gray-200 dark:border-slate-800 text-gray-600 dark:text-slate-400 hover:bg-gray-50 dark:hover:bg-slate-800/50' }} {{ $isAdmin ? 'cursor-pointer' : 'cursor-not-allowed opacity-60' }}"
                                    >
                                        <div class="flex h-4 w-4 flex-shrink-0 items-center justify-center rounded-full border {{ !$settingsOnlyAdminsCanMessage ? 'border-amber-600 bg-amber-600 text-white' : 'border-gray-300 dark:border-slate-600' }}">
                                            @if(!$settingsOnlyAdminsCanMessage)
                                                <div class="h-1.5 w-1.5 rounded-full bg-white"></div>
                                            @endif
                                        </div>
                                        <div>
                                            <div class="font-semibold">All Members</div>
                                            <div class="text-[10px] text-gray-500 dark:text-slate-400">Anyone can send</div>
                                        </div>
                                    </button>
                                    <button
                                        type="button"
                                        @if($isAdmin) wire:click="$set('settingsOnlyAdminsCanMessage', true)" @endif
                                        class="flex items-center gap-2.5 rounded-xl border p-2.5 text-xs text-left transition {{ $settingsOnlyAdminsCanMessage ? 'border-amber-500 bg-amber-50/70 dark:bg-amber-950/40 font-bold text-amber-900 dark:text-amber-200 ring-2 ring-amber-500/20' : 'border-gray-200 dark:border-slate-800 text-gray-600 dark:text-slate-400 hover:bg-gray-50 dark:hover:bg-slate-800/50' }} {{ $isAdmin ? 'cursor-pointer' : 'cursor-not-allowed opacity-60' }}"
                                    >
                                        <div class="flex h-4 w-4 flex-shrink-0 items-center justify-center rounded-full border {{ $settingsOnlyAdminsCanMessage ? 'border-amber-600 bg-amber-600 text-white' : 'border-gray-300 dark:border-slate-600' }}">
                                            @if($settingsOnlyAdminsCanMessage)
                                                <div class="h-1.5 w-1.5 rounded-full bg-white"></div>
                                            @endif
                                        </div>
                                        <div>
                                            <div class="font-semibold">Only Admins</div>
                                            <div class="text-[10px] text-gray-500 dark:text-slate-400">Restricted mode</div>
                                        </div>
                                    </button>
                                </div>
                            </div>

                            <!-- Rule 2: Edit Group Info -->
                            <div class="rounded-xl border border-gray-200 dark:border-slate-800 p-4 space-y-2 bg-gray-50/50 dark:bg-slate-950/30">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h4 class="text-xs font-bold text-gray-900 dark:text-white">Edit Group Info</h4>
                                        <p class="text-[11px] text-gray-500 dark:text-slate-400">Choose who can change group name, icon, and description.</p>
                                    </div>
                                    <span class="text-base">✏️</span>
                                </div>
                                <div class="grid grid-cols-2 gap-2 pt-2">
                                    <button
                                        type="button"
                                        @if($isAdmin) wire:click="$set('settingsOnlyAdminsCanEditInfo', false)" @endif
                                        class="flex items-center gap-2.5 rounded-xl border p-2.5 text-xs text-left transition {{ !$settingsOnlyAdminsCanEditInfo ? 'border-amber-500 bg-amber-50/70 dark:bg-amber-950/40 font-bold text-amber-900 dark:text-amber-200 ring-2 ring-amber-500/20' : 'border-gray-200 dark:border-slate-800 text-gray-600 dark:text-slate-400 hover:bg-gray-50 dark:hover:bg-slate-800/50' }} {{ $isAdmin ? 'cursor-pointer' : 'cursor-not-allowed opacity-60' }}"
                                    >
                                        <div class="flex h-4 w-4 flex-shrink-0 items-center justify-center rounded-full border {{ !$settingsOnlyAdminsCanEditInfo ? 'border-amber-600 bg-amber-600 text-white' : 'border-gray-300 dark:border-slate-600' }}">
                                            @if(!$settingsOnlyAdminsCanEditInfo)
                                                <div class="h-1.5 w-1.5 rounded-full bg-white"></div>
                                            @endif
                                        </div>
                                        <div>
                                            <div class="font-semibold">All Members</div>
                                            <div class="text-[10px] text-gray-500 dark:text-slate-400">Anyone can edit info</div>
                                        </div>
                                    </button>
                                    <button
                                        type="button"
                                        @if($isAdmin) wire:click="$set('settingsOnlyAdminsCanEditInfo', true)" @endif
                                        class="flex items-center gap-2.5 rounded-xl border p-2.5 text-xs text-left transition {{ $settingsOnlyAdminsCanEditInfo ? 'border-amber-500 bg-amber-50/70 dark:bg-amber-950/40 font-bold text-amber-900 dark:text-amber-200 ring-2 ring-amber-500/20' : 'border-gray-200 dark:border-slate-800 text-gray-600 dark:text-slate-400 hover:bg-gray-50 dark:hover:bg-slate-800/50' }} {{ $isAdmin ? 'cursor-pointer' : 'cursor-not-allowed opacity-60' }}"
                                    >
                                        <div class="flex h-4 w-4 flex-shrink-0 items-center justify-center rounded-full border {{ $settingsOnlyAdminsCanEditInfo ? 'border-amber-600 bg-amber-600 text-white' : 'border-gray-300 dark:border-slate-600' }}">
                                            @if($settingsOnlyAdminsCanEditInfo)
                                                <div class="h-1.5 w-1.5 rounded-full bg-white"></div>
                                            @endif
                                        </div>
                                        <div>
                                            <div class="font-semibold">Only Admins</div>
                                            <div class="text-[10px] text-gray-500 dark:text-slate-400">Admins only</div>
                                        </div>
                                    </button>
                                </div>
                            </div>

                            @if($isAdmin)
                                <div class="flex justify-end pt-2">
                                    <button
                                        type="button"
                                        wire:click="saveGroupSettings"
                                        class="rounded-xl bg-amber-600 px-4 py-2 text-xs font-bold text-white shadow-md transition hover:bg-amber-500 active:scale-95"
                                    >
                                        Save Permissions
                                    </button>
                                </div>
                            @endif
                        </div>
                    @endif

                    <!-- DANGER TAB -->
                    @if($groupSettingsTab === 'danger')
                        <div class="space-y-4">
                            <!-- Leave Group -->
                            <div class="rounded-xl border border-gray-200 p-4 dark:border-slate-800 flex items-center justify-between">
                                <div>
                                    <h4 class="text-xs font-bold text-gray-900 dark:text-white">Leave Group</h4>
                                    <p class="text-[11px] text-gray-500 dark:text-slate-400">You will stop receiving messages from this group.</p>
                                </div>
                                <button
                                    type="button"
                                    wire:confirm="Are you sure you want to leave this group?"
                                    wire:click="leaveGroup"
                                    class="rounded-xl border border-red-200 bg-red-50 px-3.5 py-2 text-xs font-bold text-red-600 hover:bg-red-100 dark:border-red-900/60 dark:bg-red-950/40 dark:text-red-400 dark:hover:bg-red-900/40"
                                >
                                    Leave Group
                                </button>
                            </div>

                            @if($isOwner)
                                <!-- Delete Group (Owner only) -->
                                <div class="rounded-xl border border-red-200 bg-red-50/40 p-4 dark:border-red-900/60 dark:bg-red-950/20 flex items-center justify-between">
                                    <div>
                                        <h4 class="text-xs font-bold text-red-700 dark:text-red-400">Delete Group</h4>
                                        <p class="text-[11px] text-red-600/80 dark:text-red-400/80">Permanently delete this group, all messages, and attachments.</p>
                                    </div>
                                    <button
                                        type="button"
                                        wire:confirm="Are you sure you want to delete this group permanently? This action cannot be undone."
                                        wire:click="deleteGroup"
                                        class="rounded-xl bg-red-600 px-3.5 py-2 text-xs font-bold text-white shadow hover:bg-red-500"
                                    >
                                        Delete Group
                                    </button>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <!-- Add Members to Group Modal -->
    @if($showAddMembersModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 p-4 backdrop-blur-sm">
            <div
                @click.outside="$wire.set('showAddMembersModal', false)"
                class="relative w-full max-w-md overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xl dark:border-slate-800 dark:bg-slate-900"
            >
                <div class="flex items-center justify-between border-b border-gray-100 p-4 dark:border-slate-800">
                    <h3 class="text-xs font-bold text-gray-900 dark:text-white">Add Members to Group</h3>
                    <button
                        type="button"
                        wire:click="$set('showAddMembersModal', false)"
                        class="rounded-lg p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"
                    >
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="p-3 border-b border-gray-100 dark:border-slate-800">
                    <input
                        type="text"
                        wire:model.live.debounce.200ms="memberSearch"
                        placeholder="Search users to add..."
                        class="chat-input-field w-full rounded-xl border border-gray-300 bg-white px-3.5 py-2 text-xs text-gray-900 placeholder-gray-400 focus:border-amber-500 focus:outline-none dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                    />
                </div>

                <div class="max-h-72 overflow-y-auto divide-y divide-gray-100 dark:divide-slate-800">
                    @forelse($this->addableUsers as $u)
                        @php $isMarked = in_array($u->id, $newMemberIds, true); @endphp
                        <div
                            wire:click="toggleAddMember({{ $u->id }})"
                            class="flex cursor-pointer items-center justify-between p-3 hover:bg-amber-50/60 dark:hover:bg-slate-800/60 transition {{ $isMarked ? 'bg-amber-50 dark:bg-amber-950/40' : '' }}"
                        >
                            <div class="flex items-center gap-3 min-w-0">
                                @if($u->getFilamentAvatarUrl())
                                    <img src="{{ $u->getFilamentAvatarUrl() }}" class="h-8 w-8 rounded-full object-cover border border-gray-100 dark:border-slate-700" />
                                @else
                                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-amber-100 text-amber-800 dark:bg-slate-800 dark:text-amber-300 font-bold text-xs">
                                        {{ strtoupper(substr($u->name, 0, 1)) }}
                                    </div>
                                @endif
                                <div class="min-w-0">
                                    <p class="truncate text-xs font-semibold text-gray-900 dark:text-white">{{ $u->name }}</p>
                                    <p class="truncate text-[10px] text-gray-400 dark:text-slate-500">{{ $u->email }}</p>
                                </div>
                            </div>
                            <input
                                type="checkbox"
                                class="rounded border-gray-300 text-amber-600 focus:ring-amber-500 dark:border-slate-700 dark:bg-slate-800"
                                {{ $isMarked ? 'checked' : '' }}
                                wire:click.stop="toggleAddMember({{ $u->id }})"
                            />
                        </div>
                    @empty
                        <div class="p-6 text-center text-xs text-gray-400 dark:text-slate-500">
                            No eligible users found to add.
                        </div>
                    @endforelse
                </div>

                <div class="flex items-center justify-between border-t border-gray-100 p-3 bg-gray-50/50 dark:border-slate-800 dark:bg-slate-950/40">
                    <span class="text-xs text-gray-500 dark:text-slate-400">Selected: <strong>{{ count($newMemberIds) }}</strong></span>
                    <button
                        type="button"
                        wire:click="addMembersToGroup"
                        {{ empty($newMemberIds) ? 'disabled' : '' }}
                        class="rounded-xl bg-amber-600 px-4 py-1.5 text-xs font-bold text-white shadow transition hover:bg-amber-500 disabled:opacity-50"
                    >
                        Add to Group
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Transfer Ownership Confirmation Modal -->
    @if($showTransferOwnershipModal)
        @php $transferUser = \App\Models\User::find($transferOwnershipUserId); @endphp
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 p-4 backdrop-blur-sm">
            <div
                @click.outside="$wire.set('showTransferOwnershipModal', false)"
                class="relative w-full max-w-sm overflow-hidden rounded-2xl border border-gray-200 bg-white p-5 shadow-2xl dark:border-slate-800 dark:bg-slate-900"
            >
                <div class="flex items-center gap-2.5 text-amber-600 dark:text-amber-400 mb-3">
                    <span class="text-2xl">👑</span>
                    <h4 class="text-sm font-bold text-gray-900 dark:text-white">Transfer Ownership</h4>
                </div>

                <p class="text-xs text-gray-600 dark:text-slate-300 leading-relaxed mb-4">
                    Are you sure you want to transfer ownership of <strong>{{ $this->activeConversation?->title }}</strong> to
                    <strong>{{ $transferUser?->name }}</strong>?
                </p>

                <div class="rounded-xl bg-amber-50 p-3 text-[11px] text-amber-800 dark:bg-amber-950/40 dark:text-amber-300 border border-amber-200 dark:border-amber-900/40 mb-4">
                    ⚠️ As a former owner, you will be kept as an Admin, but the new owner can remove your admin privileges or delete the group.
                </div>

                <div class="flex items-center justify-end gap-2">
                    <button
                        type="button"
                        wire:click="$set('showTransferOwnershipModal', false)"
                        class="rounded-xl border border-gray-300 px-3.5 py-1.5 text-xs font-semibold text-gray-700 hover:bg-gray-100 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-slate-800"
                    >
                        Cancel
                    </button>
                    <button
                        type="button"
                        wire:click="confirmTransferOwnership"
                        class="rounded-xl bg-amber-600 px-4 py-1.5 text-xs font-bold text-white shadow hover:bg-amber-500"
                    >
                        Confirm Transfer
                    </button>
                </div>
            </div>
        </div>
    @endif

    <!-- Pinned Messages List Modal -->
    @if($showPinnedMessagesModal)
        <div 
            class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 p-4 backdrop-blur-sm"
            @click.self="$wire.set('showPinnedMessagesModal', false)"
        >
            <div
                @click.outside="$wire.set('showPinnedMessagesModal', false)"
                class="relative w-full max-w-lg overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xl dark:border-slate-800 dark:bg-slate-900 flex flex-col max-h-[85vh]"
            >
                <div class="flex items-center justify-between border-b border-gray-100 px-5 py-3.5 dark:border-slate-800">
                    <div class="flex items-center gap-2.5">
                        <span class="flex h-8 w-8 items-center justify-center rounded-xl bg-amber-500/10 text-amber-600 dark:bg-amber-400/10 dark:text-amber-400 text-sm">
                            📌
                        </span>
                        <div>
                            <h3 class="text-sm font-bold text-gray-900 dark:text-white">Pinned Messages</h3>
                            <p class="text-[11px] text-gray-500 dark:text-slate-400">{{ $this->pinnedMessages->count() }} messages pinned</p>
                        </div>
                    </div>
                    <button
                        type="button"
                        wire:click="closePinnedMessagesModal"
                        class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-slate-800 dark:hover:text-slate-200"
                    >
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto p-4 divide-y divide-gray-100 dark:divide-slate-800 space-y-3">
                    @forelse($this->pinnedMessages as $pm)
                        <div class="pt-3 first:pt-0 flex items-start justify-between gap-3 group">
                            <div class="flex items-start gap-3 min-w-0 flex-1">
                                @if($pm->sender?->avatar_url)
                                    <img src="{{ $pm->sender->getFilamentAvatarUrl() }}" class="h-8 w-8 rounded-full object-cover flex-shrink-0 border border-gray-100 dark:border-slate-700" />
                                @else
                                    <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-amber-100 text-amber-800 dark:bg-slate-800 dark:text-amber-300 font-bold text-xs">
                                        {{ strtoupper(substr($pm->sender?->name ?? 'U', 0, 1)) }}
                                    </div>
                                @endif
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2">
                                        <span class="font-bold text-xs text-gray-900 dark:text-white">{{ $pm->sender?->name }}</span>
                                        <span class="text-[10px] text-gray-400">{{ $pm->created_at?->diffForHumans() }}</span>
                                        <span class="rounded bg-amber-100 px-1.5 py-0.5 text-[9px] font-semibold text-amber-800 dark:bg-amber-900/50 dark:text-amber-300">
                                            {{ $pm->getPinnedTimeRemaining() }}
                                        </span>
                                    </div>
                                    <div class="mt-1 text-xs text-gray-700 dark:text-slate-300">
                                        @if($pm->isImage())
                                            <div class="flex items-center gap-2 text-amber-600 dark:text-amber-400">
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                </svg>
                                                <span class="font-medium">Photo</span>
                                            </div>
                                        @elseif($pm->isAudio())
                                            <div class="flex items-center gap-2 text-amber-600 dark:text-amber-400">
                                                <span>🎤</span>
                                                <span class="font-medium">Voice note</span>
                                            </div>
                                        @elseif($pm->isFile())
                                            <div class="flex items-center gap-2 text-amber-600 dark:text-amber-400">
                                                <span>📎</span>
                                                <span class="font-medium truncate">{{ $pm->attachment_name }}</span>
                                            </div>
                                        @endif
                                        @if($pm->body)
                                            <p class="truncate line-clamp-2 mt-0.5 text-gray-800 dark:text-slate-200">{{ $pm->body }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-1.5 flex-shrink-0">
                                <button
                                    type="button"
                                    wire:click="jumpToPinnedMessage({{ $pm->id }})"
                                    class="rounded-xl bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-700 hover:bg-amber-100 hover:text-amber-800 dark:bg-slate-800 dark:text-slate-300 dark:hover:bg-slate-700 transition cursor-pointer"
                                    title="Jump to message"
                                >
                                    Jump
                                </button>
                                <button
                                    type="button"
                                    wire:click="unpinMessage({{ $pm->id }})"
                                    class="rounded-xl p-1 text-gray-400 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-950/40 transition"
                                    title="Unpin message"
                                >
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center text-xs text-gray-400">
                            No pinned messages.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    @endif

    <!-- Forward Message Modal -->
    @if($showForwardModal && $forwardingMessageId)
        @php $fwdMsg = $this->forwardingMessage; @endphp
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 p-4 backdrop-blur-sm">
            <div
                @click.outside="$wire.closeForwardModal()"
                class="relative w-full max-w-md overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xl dark:border-slate-800 dark:bg-slate-900 flex flex-col max-h-[85vh]"
            >
                <!-- Modal Header -->
                <div class="flex items-center justify-between border-b border-gray-100 px-4 py-3 dark:border-slate-800">
                    <div class="flex items-center gap-2">
                        <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-amber-500/10 text-amber-600 dark:bg-amber-400/10 dark:text-amber-400 text-sm">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 10H11a8 8 0 00-8 8v2m18-10l-6-6m6 6l-6 6"/>
                            </svg>
                        </span>
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white">Forward Message</h3>
                    </div>
                    <button
                        type="button"
                        wire:click="closeForwardModal"
                        class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-slate-800"
                    >
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                @if($fwdMsg)
                    <!-- Forwarded Message Summary Card -->
                    <div class="border-b border-gray-100 bg-gray-50/70 p-3 dark:border-slate-800 dark:bg-slate-950/40">
                        <div class="rounded-xl border border-gray-200 bg-white p-2.5 text-xs shadow-sm dark:border-slate-700/80 dark:bg-slate-800">
                            <div class="flex items-center gap-1.5 text-[11px] font-bold text-amber-600 dark:text-amber-400 mb-1">
                                <span>{{ $fwdMsg->sender?->name ?? 'User' }}</span>
                            </div>
                            <p class="truncate text-gray-700 dark:text-slate-200 text-xs">
                                {{ $fwdMsg->getReplySnippet(90) }}
                            </p>
                        </div>
                    </div>
                @endif

                <!-- Search Input -->
                <div class="border-b border-gray-100 p-3 dark:border-slate-800">
                    <input
                        type="text"
                        wire:model.live.debounce.250ms="forwardSearch"
                        placeholder="Search conversations..."
                        class="chat-input-field w-full rounded-xl border border-gray-300 bg-white px-3.5 py-2 text-xs text-gray-900 placeholder-gray-400 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500 dark:border-slate-700 dark:bg-slate-800 dark:text-gray-100"
                    />
                </div>

                <!-- Conversations List -->
                <div class="flex-1 overflow-y-auto divide-y divide-gray-100 dark:divide-slate-800 max-h-64">
                    @forelse($this->forwardableConversations as $fConv)
                        @php
                            $fSelected = in_array($fConv->id, $selectedForwardConversationIds, true);
                            $fTitle = $fConv->getDisplayName(auth()->id());
                            $fAvatar = $fConv->getDisplayAvatar(auth()->id());
                        @endphp
                        <div
                            wire:key="fwd-conv-{{ $fConv->id }}"
                            wire:click="toggleForwardConversation({{ $fConv->id }})"
                            class="flex cursor-pointer items-center justify-between p-3 transition hover:bg-amber-50/60 dark:hover:bg-slate-800/60 {{ $fSelected ? 'bg-amber-50/80 dark:bg-amber-950/40' : '' }}"
                        >
                            <div class="flex items-center gap-2.5 min-w-0">
                                @if($fAvatar)
                                    <img src="{{ $fAvatar }}" class="h-8 w-8 rounded-full object-cover flex-shrink-0" />
                                @else
                                    <div class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-gradient-to-tr {{ $fConv->isGroup() ? 'from-indigo-600 to-indigo-400' : 'from-amber-600 to-amber-400' }} text-xs font-bold text-white shadow-sm">
                                        {{ strtoupper(substr($fTitle, 0, 1)) }}
                                    </div>
                                @endif
                                <div class="min-w-0">
                                    <span class="block truncate text-xs font-semibold text-gray-900 dark:text-white">{{ $fTitle }}</span>
                                    <span class="text-[10px] text-gray-400">{{ $fConv->isGroup() ? 'Group' : 'Direct Message' }}</span>
                                </div>
                            </div>
                            <input
                                type="checkbox"
                                class="rounded border-gray-300 text-amber-600 focus:ring-amber-500 dark:border-slate-700 dark:bg-slate-800"
                                {{ $fSelected ? 'checked' : '' }}
                                wire:click.stop="toggleForwardConversation({{ $fConv->id }})"
                            />
                        </div>
                    @empty
                        <div class="p-6 text-center text-xs text-gray-400 dark:text-slate-500">
                            No conversations found.
                        </div>
                    @endforelse
                </div>

                <!-- Modal Footer -->
                <div class="flex items-center justify-between border-t border-gray-100 bg-gray-50/50 p-3 dark:border-slate-800 dark:bg-slate-900/50">
                    <span class="text-xs text-gray-500 dark:text-slate-400">
                        Selected: <strong>{{ count($selectedForwardConversationIds) }}</strong>
                    </span>
                    <div class="flex items-center gap-2">
                        <button
                            type="button"
                            wire:click="closeForwardModal"
                            class="rounded-xl px-3 py-1.5 text-xs font-medium text-gray-600 hover:bg-gray-100 dark:text-slate-300 dark:hover:bg-slate-800 transition"
                        >
                            Cancel
                        </button>
                        <button
                            type="button"
                            wire:click="forwardMessage"
                            @disabled(empty($selectedForwardConversationIds))
                            class="inline-flex items-center gap-1.5 rounded-xl bg-gradient-to-r from-amber-600 to-orange-600 px-4 py-1.5 text-xs font-bold text-white shadow-md hover:from-amber-500 hover:to-orange-500 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed transition"
                        >
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 10H11a8 8 0 00-8 8v2m18-10l-6-6m6 6l-6 6"/>
                            </svg>
                            <span>Forward ({{ count($selectedForwardConversationIds) }})</span>
                        </button>
                    </div>
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
                        const fetchUrl = this.url + (this.url.includes('?') ? '&' : '?') + '_wf=1';
                        const response = await fetch(fetchUrl, { cache: 'no-store' });
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
                            return Math.max(3, Math.min(22, Math.round(ratio * 19 + 3)));
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
                            // Chrome duration bug workaround for recorded WebM audio
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
</x-filament-panels::page>
