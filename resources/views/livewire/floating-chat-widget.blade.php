<div>
<script>
    if (!window.voicePlayer) {
        window.voicePlayer = function(url, seedId = 1) {
            const numBars = 26;
            const defaultBars = [];
            let s = Number(seedId) || 123;
            for (let i = 0; i < numBars; i++) {
                s = (s * 9301 + 49297) % 233280;
                const rnd = s / 233280;
                const envelope = Math.sin((i / (numBars - 1)) * Math.PI);
                const h = Math.round(4 + (rnd * 14 + 4) * (0.35 + 0.65 * envelope));
                defaultBars.push(Math.max(4, Math.min(22, h)));
            }

            return {
                url: url,
                seedId: seedId,
                audio: null,
                isPlaying: false,
                currentTime: 0,
                duration: 0,
                progress: 0,
                speed: 1,
                speeds: [1, 1.5, 2],
                bars: defaultBars,
                animRaf: null,

                init() {
                    if (this.$el) {
                        this.$el._voicePlayer = this;
                        this.$el.addEventListener('play-voice-note', () => this.play());
                    }

                    window.addEventListener('voice-player-stop-all', (e) => {
                        if (e.detail?.except !== this && this.isPlaying) {
                            this.pause();
                        }
                    });

                    this.audio = new Audio();
                    this.audio.preload = 'metadata';
                    this.audio.src = this.url;

                    this.audio.addEventListener('loadedmetadata', () => {
                        if (isFinite(this.audio.duration) && this.audio.duration > 0) {
                            this.duration = this.audio.duration;
                        }
                    });

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
                        this.playNextVoiceNote();
                    });

                    this.loadWaveformData();
                },

                async loadWaveformData() {
                    try {
                        const AudioCtx = window.AudioContext || window.webkitAudioContext;
                        if (!AudioCtx) return;
                        const response = await fetch(this.url, { cache: 'force-cache' });
                        if (!response.ok) return;
                        const arrayBuffer = await response.arrayBuffer();

                        const audioCtx = new AudioCtx();
                        const audioBuffer = await audioCtx.decodeAudioData(arrayBuffer);

                        if (audioBuffer && audioBuffer.duration && isFinite(audioBuffer.duration)) {
                            this.duration = audioBuffer.duration;
                        }

                        const channelData = audioBuffer.getChannelData(0);
                        const blockSize = Math.floor(channelData.length / numBars);
                        const rawBars = [];
                        let maxRms = 0;

                        for (let i = 0; i < numBars; i++) {
                            const start = i * blockSize;
                            let sum = 0;
                            let count = 0;
                            const step = Math.max(1, Math.floor(blockSize / 30));
                            for (let j = 0; j < blockSize; j += step) {
                                const val = channelData[start + j] || 0;
                                sum += val * val;
                                count++;
                            }
                            const rms = Math.sqrt(sum / (count || 1));
                            rawBars.push(rms);
                            if (rms > maxRms) maxRms = rms;
                        }

                        if (maxRms > 0.001) {
                            this.bars = rawBars.map(rms => {
                                const ratio = rms / maxRms;
                                return Math.max(4, Math.min(22, Math.round(ratio * 18 + 4)));
                            });
                        }
                        audioCtx.close();
                    } catch (err) {
                        // Keeps natural deterministic bars
                    }
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
                },

                playNextVoiceNote() {
                    const currentEl = this.$el;
                    if (!currentEl) return;
                    const feed = currentEl.closest('[x-ref="floatingFeed"]') || document;
                    const players = Array.from(feed.querySelectorAll('[data-voice-player]'));
                    const currentIndex = players.indexOf(currentEl);
                    if (currentIndex !== -1 && currentIndex + 1 < players.length) {
                        const nextPlayerEl = players[currentIndex + 1];
                        nextPlayerEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                        setTimeout(() => {
                            if (nextPlayerEl._voicePlayer) {
                                nextPlayerEl._voicePlayer.play();
                            }
                        }, 250);
                    }
                }
            };
        };
    }
</script>
    <div x-data="{
        isOpen: @entangle('isOpen'),
        activeConversationId: @entangle('activeConversationId'),
        onlineUserIds: [],
        viewerOnlineStatusEnabled: {{ auth()->user()?->online_status_enabled ?? true ? 'true' : 'false' }},
        userOnlinePrivacyMap: {},
        userLastActiveMap: {},
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
        vizBars: Array(32).fill(3),
        vizRaf: null,

        // Context Menu & 2s Mobile Long-Press State
        contextMenu: {
            show: false,
            x: 0,
            y: 0,
            messageId: null,
            isMe: false,
            canDelete: false,
            isPinned: false,
            hasText: false,
            showExtraEmojis: false,
            copied: false,
        },
        longPressTimer: null,
        longPressMessageId: null,
        longPressStartX: 0,
        longPressStartY: 0,
        isLongPressing: false,
        activeHoldMsgId: null,

        startLongPress(e, msg) {
            this.cancelLongPress();
            if (!e.touches || !e.touches[0]) return;
            const touch = e.touches[0];
            this.longPressStartX = touch.clientX;
            this.longPressStartY = touch.clientY;
            this.longPressMessageId = msg.id;
            this.isLongPressing = true;
            this.activeHoldMsgId = msg.id;

            this.longPressTimer = setTimeout(() => {
                if (this.isLongPressing && this.longPressMessageId === msg.id) {
                    if (navigator.vibrate) {
                        try { navigator.vibrate(35); } catch (err) {}
                    }
                    this.openContextMenuAt(this.longPressStartX, this.longPressStartY, msg);
                    this.cancelLongPress();
                }
            }, 2000);
        },

        moveLongPress(e) {
            if (!this.isLongPressing || !e.touches || !e.touches[0]) return;
            const touch = e.touches[0];
            const dx = Math.abs(touch.clientX - this.longPressStartX);
            const dy = Math.abs(touch.clientY - this.longPressStartY);
            if (dx > 8 || dy > 8) {
                this.cancelLongPress();
            }
        },

        cancelLongPress() {
            if (this.longPressTimer) {
                clearTimeout(this.longPressTimer);
                this.longPressTimer = null;
            }
            this.isLongPressing = false;
            this.longPressMessageId = null;
            this.activeHoldMsgId = null;
        },

        openContextMenu(e, msg) {
            this.cancelLongPress();
            if (e) {
                e.preventDefault();
                e.stopPropagation();
            }
            const clientX = e ? e.clientX : this.longPressStartX;
            const clientY = e ? e.clientY : this.longPressStartY;
            this.openContextMenuAt(clientX, clientY, msg);
        },

        openContextMenuAt(clientX, clientY, msg) {
            this.contextMenu.show = false;
            this.contextMenu.showExtraEmojis = false;
            this.contextMenu.copied = false;
            this.contextMenu.messageId = msg.id;
            this.contextMenu.isMe = msg.isMe;
            this.contextMenu.canDelete = msg.canDelete;
            this.contextMenu.isPinned = msg.isPinned;
            this.contextMenu.hasText = !!document.getElementById('float-msg-text-' + msg.id);

            this.$nextTick(() => {
                const panel = this.$refs.drawerPanel || (this.$refs.floatingFeed ? this.$refs.floatingFeed.closest('.pointer-events-auto') : null);
                const panelRect = panel ? panel.getBoundingClientRect() : { left: 0, top: 0, width: window.innerWidth, height: window.innerHeight };

                const menuWidth = 265;
                const menuHeight = this.contextMenu.canDelete ? 260 : 210;

                let posX = clientX - panelRect.left;
                let posY = clientY - panelRect.top;

                if (posX + menuWidth > panelRect.width - 12) {
                    posX = panelRect.width - menuWidth - 12;
                }
                if (posX < 12) {
                    posX = 12;
                }

                if (posY + menuHeight > panelRect.height - 12) {
                    posY = Math.max(12, posY - menuHeight);
                }
                if (posY < 12) {
                    posY = 12;
                }

                this.contextMenu.x = Math.round(posX);
                this.contextMenu.y = Math.round(posY);
                this.contextMenu.show = true;
            });
        },

        closeContextMenu() {
            this.contextMenu.show = false;
            this.contextMenu.showExtraEmojis = false;
        },

        copyMessageText() {
            const el = document.getElementById('float-msg-text-' + this.contextMenu.messageId);
            const text = el ? (el.innerText || el.textContent || '').trim() : '';
            if (!text) return;
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text);
            } else {
                const ta = document.createElement('textarea');
                ta.value = text;
                ta.style.position = 'fixed';
                ta.style.opacity = '0';
                document.body.appendChild(ta);
                ta.select();
                document.execCommand('copy');
                document.body.removeChild(ta);
            }
            this.contextMenu.copied = true;
            setTimeout(() => {
                this.closeContextMenu();
            }, 600);
        },
    
        canViewUserOnline(userId) {
            if (!this.viewerOnlineStatusEnabled) return false;
            if (!userId) return false;
            if (this.userOnlinePrivacyMap[userId] === false) return false;
            return true;
        },
    
        isUserOnline(userId) {
            if (!this.canViewUserOnline(userId)) return false;
            return this.onlineUserIds.includes(userId);
        },
    
        getUserLastActiveText(userId, initialFallback) {
            if (!this.canViewUserOnline(userId)) return '';
            if (this.isUserOnline(userId)) return 'Online';
            if (this.userLastActiveMap[userId]) {
                return 'Last seen ' + this.userLastActiveMap[userId];
            }
            if (initialFallback) {
                return 'Last seen ' + initialFallback;
            }
            return 'Offline';
        },
    
        init() {
            this.initEcho();
    
            $wire.on('floating-conversation-changed', (event) => {
                if (this.isRecording) {
                    this.cancelRecording();
                }
                this.$nextTick(() => {
                    this.subscribeToConversation(event.conversationId);
                    this.scrollToBottom(false);
                    requestAnimationFrame(() => this.scrollToBottom(false));
                });
            });
    
            $wire.on('scroll-floating-chat', () => {
                this.$nextTick(() => {
                    this.scrollToBottom(false);
                    requestAnimationFrame(() => this.scrollToBottom(false));
                });
            });
    
            $wire.on('scroll-to-message', (event) => {
                const id = (typeof event === 'object' && event !== null && event.messageId) ? event.messageId : event;
                this.$nextTick(() => this.scrollToMessage(id));
            });
    
            $wire.on('focus-floating-input', () => {
                this.$nextTick(() => {
                    this.$refs.floatMessageInput?.focus();
                });
            });

            this.$watch('activeConversationId', (val) => {
                if (val) {
                    this.$nextTick(() => {
                        this.scrollToBottom(false);
                        requestAnimationFrame(() => this.scrollToBottom(false));
                    });
                }
            });

            this.$watch('isOpen', (val) => {
                if (val && this.activeConversationId) {
                    this.$nextTick(() => {
                        this.scrollToBottom(false);
                        requestAnimationFrame(() => this.scrollToBottom(false));
                    });
                }
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
    
        _refreshTimer: null,
        debouncedRefresh(delay = 180) {
            if (this._refreshTimer) {
                clearTimeout(this._refreshTimer);
            }
            this._refreshTimer = setTimeout(() => {
                $wire.$refresh();
            }, delay);
        },

        initEcho() {
            if (!window.Echo) return;
    
            // Track presence with WhatsApp-style privacy controls
            window.Echo.join('chat.presence')
                .here((users) => {
                    this.onlineUserIds = [];
                    users.forEach(u => {
                        const enabled = (u.online_status_enabled !== false);
                        this.userOnlinePrivacyMap[u.id] = enabled;
                        if (enabled && !this.onlineUserIds.includes(u.id)) {
                            this.onlineUserIds.push(u.id);
                        }
                    });
                })
                .joining((user) => {
                    const enabled = (user.online_status_enabled !== false);
                    this.userOnlinePrivacyMap[user.id] = enabled;
                    if (enabled && !this.onlineUserIds.includes(user.id)) {
                        this.onlineUserIds.push(user.id);
                    }
                })
                .leaving((user) => {
                    this.onlineUserIds = this.onlineUserIds.filter(id => id !== user.id);
                    this.userLastActiveMap[user.id] = 'Just now';
                });
    
            window.Echo.private(`chat.user.{{ auth()->id() }}`)
                .listen('.message.sent', (e) => {
                    if (this.isOpen && this.activeConversationId === e.conversation_id) {
                        return;
                    }
                    if (this.isSoundNotificationEnabled()) {
                        this.playTing();
                    }
                    this.debouncedRefresh(200);
                })
                .listen('.message.deleted', (e) => {
                    if (e && e.deletedBy === {{ auth()->id() }}) return;
                    this.debouncedRefresh(180);
                })
                .listen('.message.pinned', (e) => {
                    if (e && e.pinnedBy === {{ auth()->id() }}) return;
                    this.debouncedRefresh(180);
                })
                .listen('.message.reacted', (e) => {
                    if (e && e.userId === {{ auth()->id() }}) return;
                    this.debouncedRefresh(180);
                })
                .listen('.message.status', () => this.debouncedRefresh(180));
    
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
                .stopListening('.message.status')
                .listen('.message.sent', (payload) => {
                    if (payload.sender_id !== {{ auth()->id() }} && this.isSoundNotificationEnabled()) {
                        this.playTing();
                    }
                    $wire.incomingMessage(payload);
                    this.$nextTick(() => this.scrollToBottom());
                })
                .listen('.message.deleted', (e) => {
                    if (e && e.deletedBy === {{ auth()->id() }}) return;
                    this.debouncedRefresh(180);
                })
                .listen('.message.pinned', (e) => {
                    if (e && e.pinnedBy === {{ auth()->id() }}) return;
                    this.debouncedRefresh(180);
                })
                .listen('.message.reacted', (e) => {
                    if (e && e.userId === {{ auth()->id() }}) return;
                    this.debouncedRefresh(180);
                })
                .listen('.message.status', () => this.debouncedRefresh(180));
        },
    
        scrollToBottom(smooth = false) {
            const feed = this.$refs.floatingFeed;
            if (feed) {
                if (smooth) {
                    feed.scrollTo({ top: feed.scrollHeight, behavior: 'smooth' });
                } else {
                    feed.scrollTop = feed.scrollHeight;
                }
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
    
        isSoundNotificationEnabled() {
            return localStorage.getItem('kamkaj_push_sound') === '1';
        },
    
        playTing() {
            if (!this.isSoundNotificationEnabled()) {
                return;
            }
            try {
                const ctx = new(window.AudioContext || window.webkitAudioContext)();
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
            } catch (e) {}
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
                        try { this.audioCtx.close(); } catch (e) {}
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
            if (this.vizRaf) { cancelAnimationFrame(this.vizRaf);
                this.vizRaf = null; }
            this.vizBars = Array(32).fill(3);
        },
    
        setStaticWaveform() {
            const samplePattern = [6, 10, 16, 12, 20, 24, 18, 14, 22, 16, 10, 18, 24, 20, 14, 18, 22, 16, 12, 20, 16, 10, 14, 18, 12, 8, 10, 6, 12, 16, 10, 6];
            this.vizBars = samplePattern.slice(0, 32);
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
            } catch (err) {
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
                try { URL.revokeObjectURL(this.previewUrl); } catch (e) {}
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
                try { this.audioCtx.close(); } catch (e) {}
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
                try { this.audioCtx.close(); } catch (e) {}
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
            } catch (e) {}
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
    }" x-on:toggle-floating-chat.window="isOpen = !isOpen; if (isOpen) { $wire.openWidget(); } else { $wire.closeWidget(); }" x-on:open-floating-chat.window="isOpen = true; $wire.openWidget()" x-on:close-floating-chat.window="isOpen = false; $wire.closeWidget()" class="font-sans">
        <!-- Slide-over Drawer (Backdrop & Panel) -->
        <div x-show="isOpen" x-cloak class="fixed inset-0 z-50 overflow-hidden"
            @keydown.escape.window="if (isOpen) { isOpen = false; $wire.closeWidget(); }">
            <!-- Backdrop Overlay -->
            <div x-show="isOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                @click="isOpen = false; $wire.closeWidget()"
                class="fixed inset-0 bg-slate-950/60 backdrop-blur-xs transition-opacity"></div>

            <!-- Slide-over Drawer Panel -->
            <div class="fixed inset-y-0 right-0 flex max-w-full pl-6 sm:pl-10 pointer-events-none">
                <div x-ref="drawerPanel" x-show="isOpen" x-transition:enter="transition-all ease-out duration-300"
                    x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
                    x-transition:leave="transition-all ease-in duration-200" x-transition:leave-start="translate-x-0"
                    x-transition:leave-end="translate-x-full"
                    class="relative pointer-events-auto flex h-full w-screen max-w-md sm:max-w-lg flex-col overflow-hidden border-l border-gray-200/90 bg-white shadow-2xl backdrop-blur-xl dark:border-gray-800 dark:bg-gray-900">
                    <!-- Window Top Bar -->
                    <div
                        class="flex items-center justify-between border-b border-gray-100 bg-gray-50/90 px-3.5 py-2.5 dark:border-gray-800/80 dark:bg-gray-800/80">
                        <div class="flex items-center gap-2 min-w-0 flex-1">
                            @if ($this->activeConversation)
                                @php
                                    $floatIsGroup = $this->activeConversation->isGroup();
                                    $floatRecipient = $floatIsGroup
                                        ? null
                                        : $this->activeConversation->getRecipient(auth()->id());
                                    $floatAvatar = $this->activeConversation->getDisplayAvatar(auth()->id());
                                    $floatDisplayName = $this->activeConversation->getDisplayName(auth()->id());
                                @endphp
                                @if (!$floatIsGroup && $floatRecipient)
                                    <span x-init="userOnlinePrivacyMap[{{ $floatRecipient->id }}] = {{ $floatRecipient->online_status_enabled ?? true ? 'true' : 'false' }};
                                    @if ($floatRecipient->last_active_at) userLastActiveMap[{{ $floatRecipient->id }}] = '{{ $floatRecipient->getLastActiveFormatted() }}'; @endif" class="hidden"></span>
                                @endif
                                <button type="button" wire:click="$set('activeConversationId', null)"
                                    class="rounded-lg p-1 text-gray-500 hover:bg-gray-200 dark:text-gray-400 dark:hover:bg-gray-700 flex-shrink-0"
                                    title="Back to conversations">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 19l-7-7 7-7" />
                                    </svg>
                                </button>

                                <!-- Avatar in Floating Chat Heading -->
                                <div class="relative flex-shrink-0">
                                    @if ($floatAvatar)
                                        <img src="{{ $floatAvatar }}"
                                            class="h-7 w-7 rounded-full object-cover border border-gray-200 dark:border-gray-700 shadow-xs" />
                                    @else
                                        <div
                                            class="flex h-7 w-7 items-center justify-center rounded-full bg-gradient-to-tr {{ $floatIsGroup ? 'from-indigo-600 to-indigo-400' : 'from-amber-600 to-amber-400' }} text-[11px] font-bold text-white shadow-xs">
                                            @if ($floatIsGroup)
                                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                                </svg>
                                            @else
                                                {{ strtoupper(substr($floatDisplayName, 0, 1)) }}
                                            @endif
                                        </div>
                                    @endif

                                    @if (!$floatIsGroup && $floatRecipient)
                                        <span x-show="isUserOnline({{ $floatRecipient->id }})"
                                            class="absolute bottom-0 right-0 h-2 w-2 rounded-full border border-white bg-emerald-500 dark:border-gray-900"
                                            title="Online"></span>
                                    @endif
                                </div>

                                <div class="min-w-0 flex-1">
                                    <h4
                                        class="truncate text-xs font-bold text-gray-900 dark:text-white flex items-center gap-1.5">
                                        {{ $floatDisplayName }}
                                        @if ($floatIsGroup)
                                            @php $myFloatingRole = $this->activeConversation->getUserRole(auth()->id()); @endphp
                                            @if ($myFloatingRole === 'owner')
                                                <span
                                                    class="rounded-full bg-amber-500/20 px-1.5 py-0.2 text-[9px] font-bold text-amber-800 dark:bg-amber-400/20 dark:text-amber-300 ring-1 ring-amber-400/30">
                                                    👑 Owner
                                                </span>
                                            @elseif($myFloatingRole === 'admin')
                                                <span
                                                    class="rounded-full bg-indigo-500/20 px-1.5 py-0.2 text-[9px] font-bold text-indigo-700 dark:bg-indigo-400/20 dark:text-indigo-300 ring-1 ring-indigo-400/30">
                                                    🛡️ Admin
                                                </span>
                                            @else
                                                <span
                                                    class="rounded bg-gray-100 px-1 py-0.2 text-[9px] font-semibold text-gray-600 dark:bg-gray-800 dark:text-gray-300">
                                                    Group
                                                </span>
                                            @endif
                                        @endif
                                    </h4>
                                    <div
                                        class="text-[10px] text-gray-500 dark:text-gray-400 truncate flex items-center gap-1">
                                        @if ($floatIsGroup)
                                            <span>{{ $this->activeConversation->participants->count() }} members</span>
                                        @else
                                            <template x-if="canViewUserOnline({{ $floatRecipient?->id ?? 0 }})">
                                                <span class="flex items-center gap-1">
                                                    <span x-show="isUserOnline({{ $floatRecipient?->id ?? 0 }})"
                                                        class="text-emerald-600 dark:text-emerald-400 font-semibold flex items-center gap-1">
                                                        <span
                                                            class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                                        Online
                                                    </span>
                                                    <span x-show="!isUserOnline({{ $floatRecipient?->id ?? 0 }})"
                                                        class="text-gray-400 dark:text-gray-400"
                                                        x-text="getUserLastActiveText({{ $floatRecipient?->id ?? 0 }}, '{{ $floatRecipient?->getLastActiveFormatted() ?? '' }}')">
                                                        {{ $floatRecipient?->getLastActiveFormatted() ? 'Last seen ' . $floatRecipient->getLastActiveFormatted() : 'Offline' }}
                                                    </span>
                                                </span>
                                            </template>
                                        @endif
                                    </div>
                                </div>
                            @else
                                <h4 class="text-xs font-bold text-gray-900 dark:text-white flex items-center gap-1.5">
                                    <span class="h-2 w-2 rounded-full bg-amber-500 animate-pulse"></span>
                                    Quick Chat
                                </h4>
                            @endif
                        </div>
                        <div class="flex items-center gap-0.5 sm:gap-1 flex-shrink-0">
                            @if ($this->activeConversation)
                                @if ($this->activeConversation->isGroup())
                                    <!-- Group Settings & Management -->
                                    <button type="button" wire:click="openGroupSettings"
                                        class="rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-amber-600 dark:hover:bg-gray-700 dark:hover:text-amber-400"
                                        title="Group Settings & Members">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                    </button>
                                @endif

                                <!-- Online Status Toggle -->
                                <button type="button" wire:click="toggleOnlineStatus"
                                    class="rounded-lg p-1.5 transition {{ auth()->user()?->online_status_enabled ?? true ? 'text-emerald-500 hover:bg-emerald-50 dark:hover:bg-gray-700' : 'text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}"
                                    title="{{ auth()->user()?->online_status_enabled ?? true ? 'Online status: VISIBLE (Online dot & Last active shared). Click to hide.' : 'Online status: HIDDEN (Hidden for both). Click to make visible.' }}">
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        @if (auth()->user()?->online_status_enabled ?? true)
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5.636 18.364a9 9 0 010-12.728m12.728 0a9 9 0 010 12.728m-9.9-2.829a5 5 0 010-7.07m7.072 0a5 5 0 010 7.07M13 12a1 1 0 11-2 0 1 1 0 012 0z" />
                                        @else
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M18.364 5.636a9 9 0 01.263 12.465M5.636 5.636a9 9 0 00-.263 12.465M8.464 8.464a5 5 0 000 7.072m7.072-7.072a5 5 0 010 7.072M3 3l18 18" />
                                        @endif
                                    </svg>
                                </button>

                                <!-- Read Receipts Toggle -->
                                <button type="button" wire:click="toggleReadReceipts"
                                    class="rounded-lg p-1.5 transition {{ auth()->user()?->read_receipts_enabled ? 'text-sky-500 hover:bg-sky-50 dark:hover:bg-gray-700' : 'text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700' }}"
                                    title="{{ auth()->user()?->read_receipts_enabled ? 'Read receipts: ON (Blue ticks shared). Click to toggle off.' : 'Read receipts: OFF (Blue ticks hidden for both). Click to toggle on.' }}">
                                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2"
                                            d="M2 13l4 4L14 7" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2"
                                            d="M8 13l4 4L20 7" />
                                    </svg>
                                </button>

                                <!-- Search In Chat Toggle -->
                                <button type="button" wire:click="toggleMessageSearch"
                                    class="rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-amber-600 dark:hover:bg-gray-700 dark:hover:text-amber-400 {{ $showMessageSearch ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300' : '' }}"
                                    title="Search in this chat">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                    </svg>
                                </button>
                            @endif
                            <!-- Close Button -->
                            <button type="button" @click="isOpen = false; $wire.closeWidget()"
                                wire:click="closeWidget"
                                class="rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-700 dark:hover:text-gray-200 cursor-pointer"
                                title="Close drawer (Esc)">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    @if ($this->activeConversation)
                        <!-- In-Chat Search Bar (Expandable) -->
                        @if ($showMessageSearch)
                            <div
                                class="flex items-center gap-1.5 border-b border-gray-100 bg-amber-50/70 p-2 dark:border-gray-800 dark:bg-gray-800/80">
                                <svg class="h-3.5 w-3.5 text-gray-400 flex-shrink-0" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                                <input type="text" wire:model.live.debounce.200ms="messageSearch"
                                    placeholder="Search in this conversation..."
                                    class="chat-input-field flex-1 rounded-lg border border-gray-300 bg-white px-2.5 py-1 text-xs text-gray-900 placeholder-gray-400 focus:border-amber-500 focus:outline-none dark:border-gray-700 dark:bg-gray-900 dark:text-white dark:placeholder-gray-500" />
                                @if (filled($messageSearch))
                                    <button type="button" wire:click="clearMessageSearch"
                                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"
                                        title="Clear search">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                @endif
                            </div>
                        @endif

                        <!-- Pinned Messages Banner -->
                        @php $pinnedMessages = $this->pinnedMessages; @endphp
                        @if ($pinnedMessages->isNotEmpty())
                            <div
                                class="border-b border-amber-200/80 bg-gradient-to-r from-amber-50 to-orange-50 px-3 py-1.5 text-xs dark:border-amber-900/40 dark:from-amber-950/40 dark:to-orange-950/30">
                                @php $firstPinned = $pinnedMessages->first(); @endphp
                                <div class="flex items-center justify-between gap-2">
                                    <button type="button"
                                        @if ($pinnedMessages->count() > 1) wire:click="openPinnedMessagesModal"
                            @else
                                @click="scrollToMessage({{ $firstPinned->id }})" @endif
                                        class="flex items-center gap-1.5 min-w-0 text-left hover:opacity-80 transition"
                                        title="{{ $pinnedMessages->count() > 1 ? 'Click to view all pinned messages' : 'Click to jump to pinned message' }}">
                                        <span
                                            class="flex h-5 w-5 flex-shrink-0 items-center justify-center rounded-md bg-amber-500 text-white text-[11px]">
                                            📌
                                        </span>
                                        <div class="min-w-0">
                                            <div class="flex items-center gap-1.5">
                                                <span
                                                    class="font-bold text-[10px] text-amber-900 dark:text-amber-200 truncate">
                                                    Pinned: {{ $firstPinned->sender?->name }}
                                                </span>
                                                <span
                                                    class="text-[9px] text-amber-700 dark:text-amber-400 font-medium">
                                                    ({{ $firstPinned->getPinnedTimeRemaining() }})
                                                </span>
                                            </div>
                                            <p class="truncate text-[10px] text-gray-600 dark:text-gray-300">
                                                {{ $firstPinned->body ?: ($firstPinned->attachment_name ?: 'Attachment') }}
                                            </p>
                                        </div>
                                    </button>
                                    <div class="flex items-center gap-1 flex-shrink-0">
                                        @if ($pinnedMessages->count() > 1)
                                            <button type="button" wire:click="openPinnedMessagesModal"
                                                class="rounded-full bg-amber-200 hover:bg-amber-300 dark:bg-amber-900/70 dark:hover:bg-amber-800 px-2 py-0.5 text-[9px] font-bold text-amber-800 dark:text-amber-200 transition shadow-sm cursor-pointer flex items-center gap-1"
                                                title="View all pinned messages">
                                                <span>{{ $pinnedMessages->count() }} pinned</span>
                                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M19 9l-7 7-7-7" />
                                                </svg>
                                            </button>
                                        @endif
                                        <button type="button" wire:click="unpinMessage({{ $firstPinned->id }})"
                                            class="rounded p-1 text-gray-400 hover:text-red-500 transition"
                                            title="Unpin message">
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M6 18L18 6M6 6l12 12" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Active Chat Feed -->
                        <div x-ref="floatingFeed"
                            @scroll.passive="closeContextMenu()"
                            class="flex-1 overflow-y-auto px-3.5 py-3 bg-slate-50/70 dark:bg-slate-950/50">
                            @php
                                $messages = $this->messages;
                                $totalMessagesCount = $this->totalMessagesCount;
                                $isChatGroup = $this->activeConversation->isGroup();
                                $dateGroups = $messages->groupBy(function ($m) {
                                    return $m->created_at ? $m->created_at->format('Y-m-d') : 'unknown';
                                });
                            @endphp
                            @if ($totalMessagesCount > $this->messagesLimit && blank($messageSearch))
                                <div class="flex justify-center my-1.5">
                                    <button type="button" wire:click="loadOlderMessages"
                                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-[11px] font-semibold text-amber-700 bg-amber-50 hover:bg-amber-100 dark:text-amber-300 dark:bg-amber-950/60 dark:hover:bg-amber-900/60 border border-amber-200 dark:border-amber-800 transition shadow-2xs cursor-pointer">
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                        </svg>
                                        <span>Load earlier messages ({{ $totalMessagesCount - $this->messagesLimit }} more)</span>
                                    </button>
                                </div>
                            @endif
                            @forelse($dateGroups as $dateKey => $groupMessages)
                                @php
                                    $firstGroupMsg = $groupMessages->first();
                                    $dateLabel = 'Unknown';
                                    if ($firstGroupMsg && $firstGroupMsg->created_at) {
                                        if ($firstGroupMsg->created_at->isToday()) {
                                            $dateLabel = 'Today';
                                        } elseif ($firstGroupMsg->created_at->isYesterday()) {
                                            $dateLabel = 'Yesterday';
                                        } else {
                                            $dateLabel = $firstGroupMsg->created_at->format('M j, Y');
                                        }
                                    }
                                @endphp
                                <div class="relative date-group mb-2">
                                    <!-- Sticky Date Pill (sticks to top while scrolling this date range) -->
                                    <div class="sticky top-0 z-20 flex items-center justify-center py-1 select-none pointer-events-none">
                                        <span class="px-3 py-0.5 text-[10px] font-semibold tracking-wide text-slate-600 dark:text-slate-300 bg-white/95 dark:bg-slate-800/95 border border-slate-200/90 dark:border-slate-700/80 rounded-full shadow-xs backdrop-blur-md">
                                            {{ $dateLabel }}
                                        </span>
                                    </div>
                                    <div class="space-y-1.5">
                                        @foreach($groupMessages as $msg)
                                            @php
                                                $isMe = $msg->sender_id === auth()->id();
                                                $canDelete = $msg->canBeDeletedBy(auth()->id());
                                                $isPinned = $msg->isCurrentlyPinned();
                                            @endphp

                                <div id="float-msg-{{ $msg->id }}" wire:key="float-msg-{{ $msg->id }}"
                                    @if (!$msg->is_deleted)
                                        @contextmenu.prevent.stop="openContextMenu($event, { id: {{ $msg->id }}, isMe: {{ $isMe ? 'true' : 'false' }}, canDelete: {{ $canDelete ? 'true' : 'false' }}, isPinned: {{ $isPinned ? 'true' : 'false' }} })"
                                    @endif
                                    class="group relative flex w-full {{ $isMe ? 'justify-end' : 'justify-start' }} py-0.5">
                                    <div
                                        class="flex items-end gap-1.5 max-w-[84%] sm:max-w-[76%] {{ $isMe ? 'flex-row-reverse' : 'flex-row' }}">
                                        @if (!$isMe)
                                            <div class="flex-shrink-0 self-end mb-0.5">
                                                @if ($msg->sender?->avatar_url)
                                                    <img src="{{ $msg->sender->getFilamentAvatarUrl() }}"
                                                        class="h-6 w-6 rounded-full object-cover ring-1 ring-slate-200 dark:ring-slate-700" />
                                                @else
                                                    <div
                                                        class="flex h-6 w-6 items-center justify-center rounded-full bg-gradient-to-tr from-amber-500/20 to-orange-500/20 text-[10px] font-bold text-amber-700 dark:text-amber-300 ring-1 ring-amber-500/30">
                                                        {{ strtoupper(substr($msg->sender?->name ?? 'U', 0, 1)) }}
                                                    </div>
                                                @endif
                                            </div>
                                        @endif

                                        <div class="relative w-fit"
                                            @if (!$msg->is_deleted)
                                                @contextmenu.prevent.stop="openContextMenu($event, { id: {{ $msg->id }}, isMe: {{ $isMe ? 'true' : 'false' }}, canDelete: {{ $canDelete ? 'true' : 'false' }}, isPinned: {{ $isPinned ? 'true' : 'false' }} })"
                                                @touchstart="startLongPress($event, { id: {{ $msg->id }}, isMe: {{ $isMe ? 'true' : 'false' }}, canDelete: {{ $canDelete ? 'true' : 'false' }}, isPinned: {{ $isPinned ? 'true' : 'false' }} })"
                                                @touchmove="moveLongPress($event)"
                                                @touchend="cancelLongPress()"
                                                @touchcancel="cancelLongPress()"
                                            @endif
                                        >
                                            <div
                                                :class="activeHoldMsgId === {{ $msg->id }} ? 'scale-[0.98] ring-2 ring-amber-400 opacity-90' : ''"
                                                class="relative rounded-2xl px-2.5 py-1.5 text-xs shadow-xs transition-all select-text cursor-default {{ $msg->is_deleted ? 'bg-slate-100/70 dark:bg-slate-800/50 border border-dashed border-slate-300 dark:border-slate-700 text-slate-500 dark:text-slate-400 rounded-2xl' : ($isMe ? 'bg-gradient-to-br from-amber-500 via-orange-500 to-amber-600 text-white rounded-br-xs shadow-orange-500/15' : 'bg-white text-slate-900 border border-slate-200/80 dark:border-slate-700/60 dark:bg-slate-800 dark:text-slate-100 rounded-bl-xs shadow-slate-900/5') }} {{ !$isMe && !$msg->is_deleted && $msg->mentionsUser(auth()->id()) ? 'ring-2 ring-amber-400 dark:ring-amber-400/80 bg-amber-50/70 dark:bg-amber-950/30 border-amber-300 dark:border-amber-700' : '' }}">
                                                @if ($isChatGroup && !$isMe && !$msg->is_deleted)
                                                    <div class="flex items-center justify-between gap-2 mb-1 leading-none">
                                                        <span
                                                            class="text-[11px] font-bold text-amber-600 dark:text-amber-400">
                                                            {{ $msg->sender?->name }}
                                                        </span>
                                                        @if ($msg->mentionsUser(auth()->id()))
                                                            <span
                                                                class="inline-flex items-center gap-0.5 rounded-full bg-amber-500/20 px-1.5 py-0.2 text-[8px] font-bold text-amber-800 dark:bg-amber-400/20 dark:text-amber-300 ring-1 ring-amber-400/30">
                                                                @ Tagged you
                                                            </span>
                                                        @endif
                                                    </div>
                                                @elseif(!$isMe && !$msg->is_deleted && $msg->mentionsUser(auth()->id()))
                                                    <div class="mb-1 leading-none">
                                                        <span
                                                            class="inline-flex items-center gap-0.5 rounded-full bg-amber-500/20 px-1.5 py-0.2 text-[8px] font-bold text-amber-800 dark:bg-amber-400/20 dark:text-amber-300 ring-1 ring-amber-400/30">
                                                            @ Tagged you
                                                        </span>
                                                    </div>
                                                @endif

                                                @if ($msg->is_forwarded && !$msg->is_deleted)
                                                    <div class="mb-1 flex items-center gap-1 text-[9px] italic {{ $isMe ? 'text-amber-100/90' : 'text-slate-500 dark:text-slate-400' }}">
                                                        <svg class="h-2.5 w-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l6-6m0 0l-6-6m6 6H9a6 6 0 00-6 6v3" />
                                                        </svg>
                                                        <span>Forwarded</span>
                                                    </div>
                                                @endif

                                                @if ($isPinned && !$msg->is_deleted)
                                                    <div class="mb-1 flex items-center gap-1 text-[9px] font-bold {{ $isMe ? 'text-amber-100' : 'text-amber-600 dark:text-amber-400' }}">
                                                        <span>📌 Pinned</span>
                                                        @if ($msg->pinned_until)
                                                            <span class="opacity-75">({{ $msg->getPinnedTimeRemaining() }})</span>
                                                        @endif
                                                    </div>
                                                @endif

                                                @if ($msg->reply_to_id && $msg->replyTo && !$msg->is_deleted)
                                                    <div @click="scrollToMessage({{ $msg->reply_to_id }})"
                                                        class="mb-1.5 cursor-pointer rounded-lg border-l-3 px-2 py-1 transition text-left select-none {{ $isMe ? 'border-amber-300 bg-black/15 hover:bg-black/25 text-white' : 'border-amber-500 bg-slate-100 hover:bg-slate-200/70 text-slate-800 dark:border-amber-400 dark:bg-slate-700/60 dark:hover:bg-slate-700/90 dark:text-slate-200' }}"
                                                        title="Jump to replied message">
                                                        <div class="text-[10px] font-bold {{ $isMe ? 'text-amber-200' : 'text-amber-600 dark:text-amber-400' }}">
                                                            {{ $msg->replyTo->sender?->name ?? 'User' }}
                                                        </div>
                                                        <p class="truncate text-[10px] opacity-90 font-normal">
                                                            {{ $msg->replyTo->getReplySnippet(45) }}
                                                        </p>
                                                    </div>
                                                @endif

                                            <!-- Message Body / Attachments or Deleted State -->
                                            @if ($msg->is_deleted)
                                                <div
                                                    class="flex items-center gap-1.5 py-0.5 italic text-slate-500 dark:text-slate-400 text-[11px]">
                                                    <svg class="h-3.5 w-3.5 opacity-70 flex-shrink-0" fill="none"
                                                        viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                                    </svg>
                                                    <span>This message was deleted</span>
                                                </div>
                                            @else
                                                @if ($msg->isImage())
                                                    <div
                                                        class="relative group/attachment my-1 overflow-hidden rounded-lg">
                                                        <a href="{{ $msg->getAttachmentUrl() }}" target="_blank">
                                                            <img src="{{ $msg->getAttachmentUrl() }}"
                                                                class="max-h-48 max-w-full rounded-lg object-cover hover:opacity-95 transition" />
                                                        </a>
                                                                                             @elseif($msg->isAudio())
                                                    <!-- Modern Waveform Voice Note Player -->
                                                    <div x-data="voicePlayer('{{ $msg->getAttachmentUrl() }}', {{ $msg->id }})" data-voice-player
                                                        class="my-1 flex items-center gap-2.5 rounded-2xl px-3 py-2 transition-all {{ $isMe ? 'bg-black/20 border border-white/15 text-white' : 'bg-slate-100/90 dark:bg-slate-700/60 border border-slate-200/80 dark:border-slate-600/50 text-slate-800 dark:text-slate-100' }} min-w-[220px] max-w-[260px] select-none shadow-xs">
                                                        <!-- Play/Pause Button -->
                                                        <button type="button" @click.stop="togglePlay()"
                                                            class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full shadow-md transition-all active:scale-90 hover:scale-105 cursor-pointer {{ $isMe ? 'bg-white text-orange-600 hover:bg-orange-50' : 'bg-gradient-to-tr from-amber-600 to-orange-500 text-white hover:from-amber-500 hover:to-orange-400' }}"
                                                            title="Play / Pause">
                                                            <svg x-show="!isPlaying" class="h-4 w-4 ml-0.5" fill="currentColor"
                                                                viewBox="0 0 24 24">
                                                                <path d="M8 5v14l11-7z" />
                                                            </svg>
                                                            <svg x-show="isPlaying" x-cloak class="h-4 w-4" fill="currentColor"
                                                                viewBox="0 0 24 24">
                                                                <path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z" />
                                                            </svg>
                                                        </button>

                                                        <!-- Waveform & Info -->
                                                        <div class="flex-1 min-w-0 flex flex-col justify-center gap-1">
                                                            <div class="flex items-center gap-[2px] h-6 cursor-pointer py-0.5 group/wave relative"
                                                                @click.stop="handleBarClick($event)"
                                                                title="Click to seek">
                                                                <template x-for="(barHeight, idx) in bars"
                                                                    :key="idx">
                                                                    <div class="w-[3px] rounded-full transition-all duration-75 group-hover/wave:opacity-90"
                                                                        :style="`height: ${barHeight}px;`"
                                                                        :class="(idx / (bars.length - 1)) <= (progress / 100) ?
                                                                        '{{ $isMe ? 'bg-white font-bold shadow-xs' : 'bg-gradient-to-t from-amber-600 to-orange-500 dark:from-amber-400 dark:to-orange-400' }}' :
                                                                        '{{ $isMe ? 'bg-white/35' : 'bg-slate-300 dark:bg-slate-500/60' }}'">
                                                                    </div>
                                                                </template>
                                                            </div>

                                                            <div
                                                                class="flex items-center justify-between text-[10px] {{ $isMe ? 'text-amber-100/90' : 'text-slate-500 dark:text-slate-400' }} font-mono leading-none">
                                                                <span
                                                                    x-text="formatSecs(isPlaying ? currentTime : (duration || currentTime))">0:00</span>
                                                                <button type="button" @click.stop="cycleSpeed()"
                                                                    class="rounded px-1 py-0.2 text-[9px] font-bold uppercase transition hover:bg-black/10 dark:hover:bg-white/10 cursor-pointer {{ $isMe ? 'text-white' : 'text-amber-700 dark:text-amber-300' }}"
                                                                    x-text="speed + 'x'">
                                                                    1x
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @elseif($msg->isFile())
                                                    <div class="my-1 flex items-center justify-between gap-2 rounded-xl p-2 {{ $isMe ? 'bg-black/15 text-white border border-white/10' : 'bg-slate-100 dark:bg-slate-700/60 text-slate-900 dark:text-white border border-slate-200/60 dark:border-slate-600/40' }}">
                                                        <div class="flex items-center gap-1.5 min-w-0">
                                                            <svg class="h-4 w-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                            </svg>
                                                            <span class="truncate text-[11px] font-medium">{{ $msg->attachment_name }}</span>
                                                        </div>
                                                        <div class="flex items-center gap-1 flex-shrink-0">
                                                            <a href="{{ $msg->getAttachmentUrl() }}" download="{{ $msg->attachment_name }}"
                                                                class="p-1 hover:opacity-80" title="Download">
                                                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                                                </svg>
                                                            </a>
                                                        </div>
                                                    </div>
                                                @endif

                                                @php
                                                    $hasTextBody = $msg->body &&
                                                        !$msg->is_deleted &&
                                                        !($msg->isImage() && $msg->body === $msg->attachment_name) &&
                                                        !($msg->isAudio() && $msg->body === 'Voice message') &&
                                                        !($msg->isFile() && $msg->body === $msg->attachment_name);
                                                @endphp

                                                @if ($hasTextBody)
                                                    <div class="flex flex-wrap items-baseline gap-x-2 gap-y-0.5 max-w-full select-text leading-snug">
                                                        <span id="float-msg-text-{{ $msg->id }}" class="break-words select-text">{!! $msg->getFormattedBodyHtml($isMe) !!}</span>
                                                        <span class="inline-flex items-center gap-1 text-[9px] font-medium leading-none select-none opacity-80 whitespace-nowrap self-end pb-0.5 ml-auto {{ $isMe ? 'text-amber-100/90' : 'text-slate-400 dark:text-slate-500' }}">
                                                            <span>{{ $msg->created_at?->format('h:i A') }}</span>
                                                            @if ($isMe)
                                                                @php $deliveryStatus = $msg->getDeliveryStatus(auth()->id()); @endphp
                                                                @if ($deliveryStatus === 'seen')
                                                                    <span title="Read" class="inline-flex items-center text-sky-200 drop-shadow-[0_0_2px_rgba(56,189,248,0.6)]">
                                                                        <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.3" d="M2 13l4 4L14 7" />
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.3" d="M8 13l4 4L20 7" />
                                                                        </svg>
                                                                    </span>
                                                                @elseif($deliveryStatus === 'delivered')
                                                                    <span title="Delivered" class="inline-flex items-center text-amber-200/90">
                                                                        <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2 13l4 4L14 7" />
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 13l4 4L20 7" />
                                                                        </svg>
                                                                    </span>
                                                                @else
                                                                    <span title="Sent" class="inline-flex items-center text-amber-200/70">
                                                                        <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                                        </svg>
                                                                    </span>
                                                                @endif
                                                            @endif
                                                        </span>
                                                    </div>
                                                @elseif(!$msg->is_deleted)
                                                    <div class="mt-1 flex items-center justify-end gap-1 text-[9px] font-medium leading-none select-none {{ $isMe ? 'text-amber-100/80' : 'text-slate-400 dark:text-slate-500' }}">
                                                        <span>{{ $msg->created_at?->format('h:i A') }}</span>
                                                        @if ($isMe)
                                                            @php $deliveryStatus = $msg->getDeliveryStatus(auth()->id()); @endphp
                                                            @if ($deliveryStatus === 'seen')
                                                                <span title="Read" class="inline-flex items-center text-sky-200 drop-shadow-[0_0_2px_rgba(56,189,248,0.6)]">
                                                                    <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.3" d="M2 13l4 4L14 7" />
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.3" d="M8 13l4 4L20 7" />
                                                                    </svg>
                                                                </span>
                                                            @elseif($deliveryStatus === 'delivered')
                                                                <span title="Delivered" class="inline-flex items-center text-amber-200/90">
                                                                    <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2 13l4 4L14 7" />
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 13l4 4L20 7" />
                                                                    </svg>
                                                                </span>
                                                            @else
                                                                <span title="Sent" class="inline-flex items-center text-amber-200/70">
                                                                    <svg class="h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                                                    </svg>
                                                                </span>
                                                            @endif
                                                        @endif
                                                    </div>
                                                @endif
                                            @endif
                                        </div>

                                        <!-- Reaction Summary Pills -->
                                        @if (!$msg->is_deleted)
                                            @php $reactionsSummary = $msg->getReactionsSummary(auth()->id()); @endphp
                                            @if (!empty($reactionsSummary))
                                                <div class="mt-1 flex flex-wrap items-center gap-1 {{ $isMe ? 'justify-end pr-0.5' : 'justify-start pl-0.5' }}">
                                                    @foreach ($reactionsSummary as $rec)
                                                        <button type="button"
                                                            wire:click="toggleReaction({{ $msg->id }}, '{{ $rec['reaction'] }}')"
                                                            class="inline-flex items-center gap-1 rounded-full px-1.5 py-0.5 text-[10px] transition-all shadow-2xs cursor-pointer {{ $rec['reacted_by_me'] ? 'bg-amber-100 text-amber-900 border border-amber-300 dark:bg-amber-950/60 dark:text-amber-300 dark:border-amber-700/80 font-bold ring-1 ring-amber-400/40' : 'bg-white text-slate-700 border border-slate-200/90 hover:bg-slate-100 dark:bg-slate-800 dark:text-slate-300 dark:border-slate-700 dark:hover:bg-slate-700' }}"
                                                            title="{{ implode(', ', $rec['users']) }} reacted">
                                                            <span class="text-xs leading-none">{{ $rec['reaction'] }}</span>
                                                            <span class="text-[10px] font-semibold">{{ $rec['count'] }}</span>
                                                        </button>
                                                    @endforeach
                                                </div>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @empty
                                <div
                                    class="flex h-full items-center justify-center text-center p-4 text-gray-400 text-xs">
                                    @if (filled($messageSearch))
                                        No messages matching "{{ $messageSearch }}"
                                    @else
                                        No messages yet. Say hi!
                                    @endif
                                </div>
                            @endforelse
                        </div>

                        <!-- Floating Context Menu (Secondary Click on Desktop, 2s Hold on Mobile) -->
                        <div x-show="contextMenu.show" x-cloak
                            @click.outside="closeContextMenu()"
                            @contextmenu.outside="closeContextMenu()"
                            @keydown.escape.window="closeContextMenu()"
                            class="absolute z-50 animate-in fade-in zoom-in-95 duration-150 origin-top select-none"
                            :style="`top: ${contextMenu.y}px; left: ${contextMenu.x}px;`"
                            style="display: none;">
                            <div class="w-64 overflow-hidden rounded-2xl border border-slate-200/90 bg-white/95 p-1.5 shadow-2xl backdrop-blur-xl dark:border-slate-700/80 dark:bg-slate-900/95">
                                <!-- Quick Reaction Bar (Top Row) -->
                                <div class="relative mb-1 flex items-center justify-between gap-1 rounded-xl bg-slate-50/90 dark:bg-slate-800/70 p-1 border border-slate-100 dark:border-slate-800">
                                    @php
                                        $quickEmojis = ['👍', '❤️', '😂', '😮', '😢', '🔥'];
                                        $extraEmojis = ['🎉', '👏', '🙏', '💯', '🚀', '👀', '✨', '⚡', '💡', '😍', '🤝', '🙌'];
                                    @endphp
                                    @foreach ($quickEmojis as $qEmoji)
                                        <button type="button"
                                            @click="$wire.toggleReaction(contextMenu.messageId, '{{ $qEmoji }}'); closeContextMenu();"
                                            class="flex h-7 w-7 items-center justify-center rounded-lg text-base transition-transform hover:scale-130 active:scale-90 hover:bg-white dark:hover:bg-slate-700 cursor-pointer"
                                            title="React {{ $qEmoji }}">
                                            {{ $qEmoji }}
                                        </button>
                                    @endforeach

                                    <!-- Expand More Emojis -->
                                    <button type="button" @click="contextMenu.showExtraEmojis = !contextMenu.showExtraEmojis"
                                        class="flex h-7 w-7 items-center justify-center rounded-lg text-xs font-bold text-slate-400 hover:text-amber-600 hover:bg-white dark:hover:bg-slate-700 transition cursor-pointer"
                                        title="More reactions">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
                                        </svg>
                                    </button>
                                </div>

                                <!-- Extra Emojis Popover / Expansion -->
                                <div x-show="contextMenu.showExtraEmojis" x-transition
                                    class="mb-1.5 grid grid-cols-6 gap-1 rounded-xl bg-slate-100/90 dark:bg-slate-800/90 p-1.5 border border-slate-200/60 dark:border-slate-700/60">
                                    @foreach ($extraEmojis as $eEmoji)
                                        <button type="button"
                                            @click="$wire.toggleReaction(contextMenu.messageId, '{{ $eEmoji }}'); closeContextMenu();"
                                            class="flex h-7 w-7 items-center justify-center rounded-lg text-sm transition-transform hover:scale-125 active:scale-90 hover:bg-white dark:hover:bg-slate-700 cursor-pointer"
                                            title="React {{ $eEmoji }}">
                                            {{ $eEmoji }}
                                        </button>
                                    @endforeach
                                </div>

                                <!-- Action Items List -->
                                <div class="space-y-0.5 text-xs font-medium text-slate-700 dark:text-slate-200">
                                    <!-- Reply -->
                                    <button type="button"
                                        @click="$wire.setReply(contextMenu.messageId); closeContextMenu();"
                                        class="flex w-full items-center gap-2.5 rounded-xl px-2.5 py-1.5 text-left transition hover:bg-amber-50 hover:text-amber-700 dark:hover:bg-amber-950/40 dark:hover:text-amber-300 cursor-pointer">
                                        <svg class="h-4 w-4 text-slate-400 dark:text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 016 6v3" />
                                        </svg>
                                        <span>Reply</span>
                                    </button>

                                    <!-- Forward -->
                                    <button type="button"
                                        @click="$wire.openForwardModal(contextMenu.messageId); closeContextMenu();"
                                        class="flex w-full items-center gap-2.5 rounded-xl px-2.5 py-1.5 text-left transition hover:bg-amber-50 hover:text-amber-700 dark:hover:bg-amber-950/40 dark:hover:text-amber-300 cursor-pointer">
                                        <svg class="h-4 w-4 text-slate-400 dark:text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l6-6m0 0l-6-6m6 6H9a6 6 0 00-6 6v3" />
                                        </svg>
                                        <span>Forward</span>
                                    </button>

                                    <!-- Copy Text (when message has text) -->
                                    <template x-if="contextMenu.hasText">
                                        <button type="button"
                                            @click="copyMessageText()"
                                            class="flex w-full items-center gap-2.5 rounded-xl px-2.5 py-1.5 text-left transition hover:bg-amber-50 hover:text-amber-700 dark:hover:bg-amber-950/40 dark:hover:text-amber-300 cursor-pointer">
                                            <svg class="h-4 w-4 text-slate-400 dark:text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                            </svg>
                                            <span x-text="contextMenu.copied ? 'Copied to Clipboard!' : 'Copy Text'"></span>
                                        </button>
                                    </template>

                                    <!-- Pin / Unpin -->
                                    <button type="button"
                                        @click="if (contextMenu.isPinned) { $wire.unpinMessage(contextMenu.messageId); } else { $wire.openPinMessageModal(contextMenu.messageId); } closeContextMenu();"
                                        class="flex w-full items-center gap-2.5 rounded-xl px-2.5 py-1.5 text-left transition hover:bg-amber-50 hover:text-amber-700 dark:hover:bg-amber-950/40 dark:hover:text-amber-300 cursor-pointer">
                                        <span class="text-sm leading-none">📌</span>
                                        <span x-text="contextMenu.isPinned ? 'Unpin Message' : 'Pin Message'"></span>
                                    </button>

                                    <!-- Delete (when permitted) -->
                                    <template x-if="contextMenu.canDelete">
                                        <div>
                                            <div class="my-1 border-t border-slate-100 dark:border-slate-800"></div>
                                            <button type="button"
                                                @click="if (confirm('Delete this message? It will be replaced with \'This message was deleted\'.')) { $wire.deleteMessage(contextMenu.messageId); } closeContextMenu();"
                                                class="flex w-full items-center gap-2.5 rounded-xl px-2.5 py-1.5 text-left text-red-600 dark:text-red-400 transition hover:bg-red-50 dark:hover:bg-red-950/40 cursor-pointer font-semibold">
                                                <svg class="h-4 w-4 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                                <span>Delete Message</span>
                                            </button>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        @php $isFloatingGroup = $this->activeConversation->isGroup(); @endphp
                        <!-- Mini Input Bar Area -->
                        <div x-data="{
                            showMentionMenu: false,
                            mentionQuery: '',
                            mentionIndex: 0,
                            mentionStartIndex: 0,
                            mentionCursorPos: 0,
                            members: @js($this->groupMembers),
                            isGroup: {{ $isFloatingGroup ? 'true' : 'false' }},
                            hasText: false,
                            enterIsNewLine: (function() {
                                try {
                                    return localStorage.getItem('waldo_chat_enter_mode') === 'newline';
                                } catch (e) {
                                    return false;
                                }
                            })(),
                        
                            updateHasText() {
                                const val = this.$refs.floatMessageInput?.value?.trim() || '';
                                this.hasText = val.length > 0;
                            },

                            init() {
                                this.updateHasText();
                                this.$nextTick(() => {
                                    this.resizeInput();
                                    this.updateHasText();
                                });
                                $wire.on('floating-conversation-changed', () => {
                                    this.$nextTick(() => {
                                        this.resizeInput();
                                        this.updateHasText();
                                    });
                                });
                                $wire.on('focus-floating-input', () => {
                                    this.$nextTick(() => {
                                        this.$refs.floatMessageInput?.focus();
                                        this.resizeInput();
                                        this.updateHasText();
                                    });
                                });
                            },
                        
                            toggleEnterMode() {
                                this.enterIsNewLine = !this.enterIsNewLine;
                                try {
                                    localStorage.setItem('waldo_chat_enter_mode', this.enterIsNewLine ? 'newline' : 'send');
                                } catch (e) {}
                            },
                        
                            resizeInput() {
                                const el = this.$refs.floatMessageInput;
                                if (!el) return;
                                el.style.height = 'auto';
                                let scrollH = el.scrollHeight;
                                const trailingNewlines = (el.value.match(/\n+$/) || [''])[0].length;
                                if (trailingNewlines > 0) {
                                    scrollH += (trailingNewlines * 20);
                                }
                                const minHeight = 30;
                                const maxHeight = 110;
                                const targetHeight = Math.min(Math.max(scrollH, minHeight), maxHeight);
                                el.style.height = targetHeight + 'px';
                                el.style.overflowY = scrollH > maxHeight ? 'auto' : 'hidden';
                                this.updateHasText();
                            },
                        
                            insertNewline() {
                                const el = this.$refs.floatMessageInput;
                                if (!el) return;
                                const start = el.selectionStart ?? el.value.length;
                                const end = el.selectionEnd ?? el.value.length;
                                const val = el.value || '';
                                const newVal = val.substring(0, start) + '\n' + val.substring(end);
                                el.value = newVal;
                                const nextPos = start + 1;
                                el.selectionStart = el.selectionEnd = nextPos;
                                el.dispatchEvent(new Event('input', { bubbles: true }));
                                this.resizeInput();
                                this.$nextTick(() => {
                                    el.focus();
                                    this.resizeInput();
                                    this.updateHasText();
                                });
                            },
                        
                            submitMessage() {
                                this.showMentionMenu = false;
                                const input = this.$refs.floatMessageInput;
                                const text = input ? input.value : ($wire.messageText || '');
                                if (!text.trim() && !$wire.attachment) {
                                    return;
                                }

                                if (input) {
                                    input.value = '';
                                }
                                this.hasText = false;
                                this.$nextTick(() => {
                                    this.resizeInput();
                                    this.updateHasText();
                                });

                                $wire.sendMessage(text);
                                this.$nextTick(() => {
                                    this.scrollToBottom();
                                });
                            },
                        
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
                                    this.resizeInput();
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
                                    this.resizeInput();
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
                        
                                if (e.key === 'Enter') {
                                    if (e.shiftKey || e.altKey) {
                                        e.preventDefault();
                                        e.stopPropagation();
                                        this.insertNewline();
                                        return;
                                    }
                        
                                    if (e.ctrlKey || e.metaKey) {
                                        e.preventDefault();
                                        e.stopPropagation();
                                        this.submitMessage();
                                        return;
                                    }
                        
                                    if (this.enterIsNewLine) {
                                        e.preventDefault();
                                        e.stopPropagation();
                                        this.insertNewline();
                                    } else {
                                        e.preventDefault();
                                        e.stopPropagation();
                                        this.submitMessage();
                                    }
                                }
                            }
                        }"
                            class="relative border-t border-gray-100 bg-white p-2.5 dark:border-gray-800 dark:bg-gray-900 flex-shrink-0">
                            <!-- Mention Dropdown Popover in Floating Widget -->
                            @if ($isFloatingGroup)
                                <div x-show="showMentionMenu && filteredMembers.length > 0"
                                    x-transition:enter="transition ease-out duration-150"
                                    x-transition:enter-start="opacity-0 translate-y-2 scale-95"
                                    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                                    x-transition:leave="transition ease-in duration-100"
                                    x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                                    x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                                    @click.outside="showMentionMenu = false"
                                    class="absolute bottom-full left-2 right-2 mb-2 max-h-52 overflow-y-auto rounded-2xl border border-gray-200 bg-white/95 p-1 shadow-2xl backdrop-blur-md dark:border-gray-700 dark:bg-gray-800/95 z-50 divide-y divide-gray-100 dark:divide-gray-700"
                                    style="display: none;">
                                    <div
                                        class="px-2 py-1 text-[9px] font-bold uppercase tracking-wider text-gray-400 dark:text-gray-400 flex items-center justify-between">
                                        <span class="flex items-center gap-1">
                                            <span class="text-amber-500 font-bold">@</span> Mention Member
                                        </span>
                                        <span class="text-[8px] font-normal lowercase opacity-70">↑↓ / ↵</span>
                                    </div>

                                    <div class="py-0.5 space-y-0.5">
                                        <template x-for="(member, idx) in filteredMembers" :key="member.id">
                                            <button type="button" @mousedown.prevent="selectMention(member)"
                                                @mouseenter="mentionIndex = idx"
                                                class="flex w-full items-center gap-2 rounded-xl px-2 py-1.5 text-left text-xs transition-colors"
                                                :class="mentionIndex === idx ?
                                                    'bg-amber-500/15 text-amber-900 dark:bg-amber-400/20 dark:text-amber-200 ring-1 ring-amber-400/30' :
                                                    'text-gray-800 hover:bg-gray-100/80 dark:text-gray-200 dark:hover:bg-gray-700/80'">
                                                <template x-if="member.isAll">
                                                    <div
                                                        class="flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full bg-amber-500 text-white font-bold text-[10px] shadow-sm">
                                                        📢
                                                    </div>
                                                </template>
                                                <template x-if="!member.isAll && member.avatar">
                                                    <img :src="member.avatar"
                                                        class="h-6 w-6 flex-shrink-0 rounded-full object-cover border border-gray-200 dark:border-gray-700" />
                                                </template>
                                                <template x-if="!member.isAll && !member.avatar">
                                                    <div
                                                        class="flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full bg-amber-100 text-amber-800 dark:bg-gray-700 dark:text-amber-300 font-bold text-[10px]">
                                                        <span x-text="member.initial"></span>
                                                    </div>
                                                </template>

                                                <div class="min-w-0 flex-1">
                                                    <div class="flex items-center gap-1">
                                                        <span class="truncate font-semibold text-[11px]"
                                                            x-text="member.name"></span>
                                                        <template x-if="member.isAll">
                                                            <span
                                                                class="rounded bg-amber-500/20 px-1 py-0.2 text-[8px] font-bold text-amber-800 dark:bg-amber-400/20 dark:text-amber-300">
                                                                ALL
                                                            </span>
                                                        </template>
                                                    </div>
                                                    <div class="text-[10px] text-gray-500 dark:text-gray-400 truncate">
                                                        <span
                                                            class="text-amber-600 dark:text-amber-400 font-medium">@<span
                                                                x-text="member.tag"></span></span>
                                                    </div>
                                                </div>
                                            </button>
                                        </template>
                                    </div>
                                </div>
                            @endif

                            <!-- Reply Preview Chip -->
                            @if ($replyingToMessageId && $this->replyingToMessage)
                                @php $repMsg = $this->replyingToMessage; @endphp
                                <div
                                    class="mb-2 flex items-center justify-between rounded-xl bg-amber-500/10 border-l-4 border-amber-500 px-2.5 py-1.5 text-xs dark:bg-amber-950/30 dark:border-amber-400">
                                    <div class="flex items-center gap-1.5 min-w-0">
                                        <svg class="h-3.5 w-3.5 text-amber-600 dark:text-amber-400 flex-shrink-0"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 016 6v3" />
                                        </svg>
                                        <div class="min-w-0">
                                            <span
                                                class="block font-bold text-amber-700 dark:text-amber-300 text-[10px]">
                                                Replying to {{ $repMsg->sender?->name ?? 'User' }}
                                            </span>
                                            <p class="truncate text-gray-600 dark:text-gray-300 text-[10px]">
                                                {{ $repMsg->getReplySnippet(60) }}
                                            </p>
                                        </div>
                                    </div>
                                    <button type="button" wire:click="cancelReply"
                                        class="text-gray-400 hover:text-red-500 p-0.5 rounded transition"
                                        title="Cancel reply">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                            @endif

                            <!-- Attachment Preview Chip -->
                            @if ($attachment)
                                <div
                                    class="mb-2 flex items-center justify-between rounded-xl bg-amber-50 p-1.5 text-xs border border-amber-200 dark:bg-gray-800 dark:border-gray-700">
                                    <div class="flex items-center gap-1.5 min-w-0">
                                        <svg class="h-3.5 w-3.5 text-amber-600 dark:text-amber-400 flex-shrink-0"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                        </svg>
                                        <span
                                            class="truncate text-[11px] font-medium text-gray-900 dark:text-white">{{ $attachment->getClientOriginalName() }}</span>
                                    </div>
                                    <button type="button" wire:click="removeAttachment"
                                        class="text-gray-400 hover:text-red-500">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                            @endif

                            @if ($isFloatingGroup && !$this->activeConversation->canUserSendMessage(auth()->id()))
                                <div
                                    class="flex items-center justify-center gap-1.5 rounded-xl bg-amber-50 p-2.5 text-[11px] text-amber-800 dark:bg-amber-950/40 dark:text-amber-300 border border-amber-200 dark:border-amber-900/40">
                                    <svg class="h-3.5 w-3.5 text-amber-600 dark:text-amber-400 flex-shrink-0"
                                        fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                    <span class="font-medium">Only group admins can send messages.</span>
                                </div>
                            @else
                                <!-- Live Waveform Recording Bar -->
                                <div x-show="isRecording" class="rounded-xl border p-2 transition-all"
                                    :class="isRecordingEnded
                                        ?
                                        'border-emerald-300 bg-emerald-50/80 dark:border-emerald-800/60 dark:bg-emerald-950/30' :
                                        (recordingTime >= maxRecordingSeconds - 10 ?
                                            'border-red-300 bg-red-50/80 dark:border-red-800/60 dark:bg-red-950/30' :
                                            'border-red-200/80 bg-gradient-to-r from-red-500/8 via-transparent to-amber-500/8 dark:border-red-900/60 dark:bg-red-950/20'
                                            )"
                                    style="display: none;">
                                    <!-- Top row: dot + live bars + time + countdown -->
                                    <div class="flex items-center gap-2">
                                        <!-- Pulsing dot (pauses when paused, steady emerald when ended) -->
                                        <div class="relative flex h-3 w-3 flex-shrink-0 items-center justify-center">
                                            <template x-if="!isRecordingEnded && !isPaused">
                                                <span
                                                    class="absolute inline-flex h-full w-full rounded-full opacity-75 bg-red-400 animate-ping"></span>
                                            </template>
                                            <span class="relative inline-flex h-2 w-2 rounded-full"
                                                :class="isPlayingPreview
                                                    ?
                                                    'bg-indigo-500 animate-pulse' :
                                                    (isRecordingEnded ?
                                                        'bg-emerald-500' :
                                                        (isPaused ? 'bg-amber-500' : 'bg-red-600'))"></span>
                                        </div>

                                        <!-- Live microphone waveform bars / Preview playback progress -->
                                        <div class="flex flex-1 items-center gap-[1.5px] h-7 overflow-hidden">
                                            <template x-for="(h, i) in vizBars" :key="i">
                                                <div class="w-[3px] rounded-full transition-all duration-75"
                                                    :style="`height: ${h}px;`"
                                                    :class="isPlayingPreview
                                                        ?
                                                        (i <= Math.floor(previewProgressRatio * vizBars.length) ?
                                                            'bg-indigo-600 dark:bg-indigo-400' :
                                                            'bg-indigo-200 dark:bg-indigo-950') :
                                                        (isRecordingEnded ?
                                                            'bg-emerald-500/80 dark:bg-emerald-400' :
                                                            (isPaused ?
                                                                'bg-amber-500/80 dark:bg-amber-400' :
                                                                (recordingTime >= maxRecordingSeconds - 10 ?
                                                                    'bg-red-500' : 'bg-red-400')))">
                                                </div>
                                            </template>
                                        </div>

                                        <!-- Timer -->
                                        <span class="font-mono text-xs font-bold tabular-nums px-1.5 py-0.5 rounded"
                                            :class="isPlayingPreview
                                                ?
                                                'text-indigo-700 bg-indigo-100 dark:text-indigo-300 dark:bg-indigo-900/50' :
                                                (isRecordingEnded ?
                                                    'text-emerald-700 bg-emerald-100 dark:text-emerald-300 dark:bg-emerald-900/50' :
                                                    (isPaused ?
                                                        'text-amber-700 bg-amber-100 dark:text-amber-300 dark:bg-amber-900/50' :
                                                        (recordingTime >= maxRecordingSeconds - 10 ?
                                                            'text-red-700 bg-red-100 dark:text-red-300 dark:bg-red-900/50' :
                                                            'text-red-600 bg-red-100/60 dark:text-red-300 dark:bg-red-900/40'
                                                            )))"
                                            x-text="isPlayingPreview
                                    ? (formatTime(previewCurrentTime) + ' / ' + formatTime(recordingTime))
                                    : (isRecordingEnded
                                        ? '1:00 (Ready)'
                                        : (isPaused
                                            ? (formatTime(recordingTime) + ' (Paused)')
                                            : (formatTime(recordingTime) + ' / 1:00')))">00:00
                                            / 1:00</span>
                                    </div>

                                    <!-- Progress bar for 60s limit / preview progress -->
                                    <div
                                        class="mt-1.5 h-0.5 w-full overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                                        <div class="h-full rounded-full transition-all duration-300"
                                            :class="isPlayingPreview
                                                ?
                                                'bg-indigo-500' :
                                                (isRecordingEnded ? 'bg-emerald-500' : (recordingTime >=
                                                    maxRecordingSeconds - 10 ? 'bg-red-500' : 'bg-amber-500'))"
                                            :style="isPlayingPreview
                                                ?
                                                `width: ${previewProgressRatio * 100}%` :
                                                `width: ${isRecordingEnded ? 100 : ((recordingTime / maxRecordingSeconds) * 100)}%`">
                                        </div>
                                    </div>

                                    <!-- Action buttons row -->
                                    <div class="mt-2 flex items-center justify-between gap-1.5">
                                        <!-- Discard -->
                                        <button type="button" @click="cancelRecording" :disabled="isUploadingVoice"
                                            class="flex items-center gap-1 rounded-lg border border-red-200 bg-white/80 px-2 py-1 text-[11px] font-semibold text-red-600 hover:bg-red-50 hover:border-red-300 dark:border-red-900/60 dark:bg-gray-800 dark:text-red-400 transition disabled:opacity-50"
                                            title="Discard recording">
                                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24"
                                                stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                            Discard
                                        </button>

                                        <div class="flex items-center gap-1.5">
                                            <!-- Play / Pause Preview Button (when paused or recording ended) -->
                                            <template x-if="isPaused || isRecordingEnded">
                                                <button type="button" @click="togglePlayPreview"
                                                    class="flex h-7 w-7 items-center justify-center rounded-full border shadow-sm transition active:scale-95"
                                                    :class="isPlayingPreview
                                                        ?
                                                        'bg-indigo-600 text-white border-indigo-600 hover:bg-indigo-700' :
                                                        'bg-white text-gray-700 hover:bg-gray-50 border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200'"
                                                    :title="isPlayingPreview ? 'Pause preview' : 'Play preview'">
                                                    <template x-if="!isPlayingPreview">
                                                        <svg class="h-3 w-3 ml-0.5" fill="currentColor"
                                                            viewBox="0 0 24 24">
                                                            <path d="M8 5v14l11-7z" />
                                                        </svg>
                                                    </template>
                                                    <template x-if="isPlayingPreview">
                                                        <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 24 24">
                                                            <path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z" />
                                                        </svg>
                                                    </template>
                                                </button>
                                            </template>

                                            <!-- Resume Recording Button (only when paused before timer ended) -->
                                            <template x-if="isPaused && !isRecordingEnded">
                                                <button type="button" @click="resumeRecording"
                                                    class="inline-flex items-center gap-1 h-7 px-2 rounded-lg border border-amber-300 bg-amber-50 text-amber-800 hover:bg-amber-100 dark:border-amber-700 dark:bg-amber-950/60 dark:text-amber-200 text-[11px] font-semibold shadow-sm transition active:scale-95"
                                                    title="Resume recording">
                                                    <svg class="h-3 w-3 text-red-600" fill="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path
                                                            d="M12 14c1.66 0 3-1.34 3-3V5c0-1.66-1.34-3-3-3S9 3.34 9 5v6c0 1.66 1.34 3 3 3z" />
                                                        <path
                                                            d="M17 11c0 2.76-2.24 5-5 5s-5-2.24-5-5H5c0 3.53 2.61 6.43 6 6.92V21h2v-3.08c3.39-.49 6-3.39 6-6.92h-2z" />
                                                    </svg>
                                                    <span>Resume</span>
                                                </button>
                                            </template>

                                            <!-- Pause Button (only while actively recording) -->
                                            <template x-if="!isPaused && !isRecordingEnded">
                                                <button type="button" @click="pauseRecording"
                                                    class="flex h-7 w-7 items-center justify-center rounded-full border border-gray-300 bg-white text-gray-600 shadow-sm hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 transition active:scale-95"
                                                    title="Pause recording">
                                                    <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 24 24">
                                                        <path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z" />
                                                    </svg>
                                                </button>
                                            </template>

                                            <!-- Send -->
                                            <button type="button" @click="stopAndSendRecording"
                                                :disabled="isUploadingVoice"
                                                class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1 text-xs font-bold text-white shadow transition-transform active:scale-95 disabled:opacity-75 disabled:cursor-not-allowed"
                                                :class="isRecordingEnded
                                                    ?
                                                    'bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500' :
                                                    'bg-gradient-to-r from-red-600 to-amber-600 hover:from-red-500 hover:to-amber-500'"
                                                title="Send voice note">
                                                <template x-if="isUploadingVoice">
                                                    <svg class="h-3.5 w-3.5 animate-spin text-white" fill="none"
                                                        viewBox="0 0 24 24">
                                                        <circle class="opacity-25" cx="12" cy="12"
                                                            r="10" stroke="currentColor" stroke-width="4"></circle>
                                                        <path class="opacity-75" fill="currentColor"
                                                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                                        </path>
                                                    </svg>
                                                </template>
                                                <template x-if="!isUploadingVoice">
                                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24"
                                                        stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                                    </svg>
                                                </template>
                                                <span x-text="isUploadingVoice ? 'Sending...' : 'Send'">Send</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Modern Clean Capsule Composer Form -->
                                <form x-show="!isRecording" @submit.prevent="submitMessage()"
                                    class="flex items-center gap-1.5 bg-slate-100/90 dark:bg-slate-800/80 px-2 py-1.5 rounded-full border border-slate-200/80 dark:border-slate-700/60 focus-within:border-amber-500/60 focus-within:ring-2 focus-within:ring-amber-500/20 focus-within:bg-white dark:focus-within:bg-slate-800 transition-all duration-150">
                                    <!-- File input hidden -->
                                    <input type="file" x-ref="floatFileInput" wire:model="attachment"
                                        class="hidden" />

                                    <!-- Paperclip File Button -->
                                    <button type="button" @click="$refs.floatFileInput.click()"
                                        class="flex-shrink-0 rounded-full p-1.5 text-slate-400 hover:text-amber-600 hover:bg-slate-200/60 dark:hover:bg-slate-700/60 transition cursor-pointer"
                                        title="Attach file or photo">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                        </svg>
                                    </button>

                                    @if ($isFloatingGroup)
                                        <!-- Quick Mention Button (@) -->
                                        <button type="button" @click="openMentionMenu"
                                            class="flex-shrink-0 rounded-full p-1.5 text-slate-400 hover:text-amber-600 hover:bg-slate-200/60 dark:hover:bg-slate-700/60 transition cursor-pointer"
                                            title="Mention group member (@all or @member)">
                                            <span class="flex h-4 w-4 items-center justify-center text-xs font-black leading-none select-none">@</span>
                                        </button>
                                    @endif

                                    <!-- Input Field (Autosizing Textarea) -->
                                    <div class="relative flex-1 min-w-0" wire:ignore>
                                        <textarea x-ref="floatMessageInput" wire:model="messageText" rows="1"
                                            @input="updateHasText(); detectMention(); resizeInput();"
                                            @click="detectMention" @keyup="updateHasText(); detectMention($event);" @keydown="handleFloatInputKeyDown"
                                            @paste="$nextTick(() => { resizeInput(); updateHasText(); })"
                                            @cut="$nextTick(() => { resizeInput(); updateHasText(); })"
                                            placeholder="Type a message..."
                                            class="chat-input-field block w-full bg-transparent px-1.5 py-1 text-xs leading-normal text-slate-900 placeholder-slate-400 focus:outline-none dark:text-slate-100 dark:placeholder-slate-500 resize-none border-0 focus:ring-0"
                                            style="min-height: 24px; max-height: 100px; overflow-y: hidden;"></textarea>
                                    </div>

                                    <!-- Morphing Action Button (Record vs Send) -->
                                    <div class="flex-shrink-0 flex items-center">
                                        <!-- Mic Record Button (when no message typed) -->
                                        <button type="button" x-show="!hasText && !@js(!empty($attachment))" @click="startRecording"
                                            x-transition:enter="transition ease-out duration-150"
                                            x-transition:enter-start="opacity-0 scale-75"
                                            x-transition:enter-end="opacity-100 scale-100"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-slate-200/80 hover:bg-amber-500/20 text-slate-600 hover:text-amber-600 dark:bg-slate-700/80 dark:text-slate-300 dark:hover:text-amber-400 transition-all active:scale-90 cursor-pointer shadow-2xs"
                                            title="Record voice message">
                                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z" />
                                            </svg>
                                        </button>

                                        <!-- Send Button (when text typed or attachment added) -->
                                        <button type="submit" x-show="hasText || @js(!empty($attachment))"
                                            x-transition:enter="transition ease-out duration-150"
                                            x-transition:enter-start="opacity-0 scale-75"
                                            x-transition:enter-end="opacity-100 scale-100"
                                            class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-gradient-to-tr from-amber-500 to-orange-500 text-white shadow-sm hover:from-amber-600 hover:to-orange-600 active:scale-95 transition-all cursor-pointer"
                                            :title="enterIsNewLine ? 'Send message (Ctrl+Enter)' : 'Send message (Enter)'">
                                            <svg class="h-4 w-4 translate-x-px" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                                            </svg>
                                        </button>
                                    </div>
                                </form>
                            @endif
                        </div>
                    @elseif($showNewChatModal)
                        <!-- New Chat View Inside Floating Widget -->
                        <div
                            class="flex items-center justify-between border-b border-gray-100 px-3 py-2 dark:border-gray-800">
                            <div class="flex items-center gap-2">
                                <button type="button" wire:click="$set('showNewChatModal', false)"
                                    class="rounded p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 19l-7-7 7-7" />
                                    </svg>
                                </button>
                                <span class="text-xs font-bold text-gray-900 dark:text-white">Start New Chat</span>
                            </div>
                            <div class="flex gap-2">
                                <button type="button" wire:click="$set('modalTab', 'direct')"
                                    class="text-[11px] font-semibold {{ $modalTab === 'direct' ? 'text-amber-600 dark:text-amber-400' : 'text-gray-400' }}">
                                    Direct
                                </button>
                                <span class="text-gray-300 dark:text-gray-700">|</span>
                                <button type="button" wire:click="$set('modalTab', 'group')"
                                    class="text-[11px] font-semibold {{ $modalTab === 'group' ? 'text-amber-600 dark:text-amber-400' : 'text-gray-400' }}">
                                    Group
                                </button>
                            </div>
                        </div>

                        @if ($modalTab === 'group')
                            <div
                                class="p-2.5 border-b border-gray-100 dark:border-gray-800 bg-gray-50/60 dark:bg-gray-800/40 space-y-2">
                                <input type="text" wire:model="groupTitle" placeholder="Group name..."
                                    class="chat-input-field w-full rounded-xl border border-gray-300 bg-white px-3 py-1.5 text-xs text-gray-900 placeholder-gray-400 focus:border-amber-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                                <div class="flex items-center justify-between text-[11px] text-gray-500">
                                    <span>Selected: <strong>{{ count($selectedGroupMembers) }}</strong></span>
                                    @if (!empty($selectedGroupMembers) && filled($groupTitle))
                                        <button type="button" wire:click="createGroupChat"
                                            class="rounded-lg bg-amber-600 px-2.5 py-1 text-xs font-bold text-white shadow hover:bg-amber-500">
                                            Create Group
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @endif

                        <div class="p-2 border-b border-gray-100 dark:border-gray-800">
                            <input type="text" wire:model.live.debounce.250ms="userSearch"
                                placeholder="Search users..."
                                class="chat-input-field w-full rounded-xl border border-gray-300 bg-white px-3 py-1.5 text-xs text-gray-900 placeholder-gray-400 focus:border-amber-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400" />
                        </div>

                        <div class="flex-1 overflow-y-auto divide-y divide-gray-100 dark:divide-gray-800">
                            @forelse($this->availableUsers as $user)
                                @php
                                    $isSelected = in_array($user->id, $selectedGroupMembers, true);
                                    $avatarUrl = $user->getFilamentAvatarUrl();
                                @endphp
                                <div wire:key="float-user-{{ $user->id }}"
                                    wire:click="{{ $modalTab === 'group' ? 'toggleGroupMember(' . $user->id . ')' : 'startConversationWith(' . $user->id . ')' }}"
                                    class="flex cursor-pointer items-center justify-between p-2.5 transition hover:bg-amber-50/60 dark:hover:bg-gray-800/60 {{ $isSelected ? 'bg-amber-50 dark:bg-amber-950/40' : '' }}">
                                    <div class="flex items-center gap-2 min-w-0">
                                        @if ($avatarUrl)
                                            <img src="{{ $avatarUrl }}"
                                                class="h-7 w-7 rounded-full object-cover flex-shrink-0" />
                                        @else
                                            <div
                                                class="flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-full bg-amber-500/20 text-[10px] font-bold text-amber-700 dark:text-amber-300">
                                                {{ strtoupper(substr($user->name, 0, 1)) }}
                                            </div>
                                        @endif
                                        <div class="min-w-0">
                                            <p class="truncate text-xs font-semibold text-gray-900 dark:text-white">
                                                {{ $user->name }}</p>
                                            <span
                                                class="text-[10px] text-gray-400 truncate block">{{ $user->email }}</span>
                                        </div>
                                    </div>
                                    @if ($modalTab === 'group')
                                        <input type="checkbox"
                                            class="rounded border-gray-300 text-amber-600 focus:ring-amber-500 dark:border-gray-700 dark:bg-gray-800"
                                            {{ $isSelected ? 'checked' : '' }}
                                            wire:click.stop="toggleGroupMember({{ $user->id }})" />
                                    @else
                                        <span class="text-[11px] font-semibold text-amber-600 dark:text-amber-400">Chat
                                            &rarr;</span>
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
                            <input type="text" wire:model.live.debounce.250ms="search"
                                placeholder="Search chats..."
                                class="chat-input-field flex-1 rounded-xl border border-gray-300 bg-white px-3 py-1.5 text-xs text-gray-900 placeholder-gray-400 focus:border-amber-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400" />
                            <!-- New Chat Button -->
                            <button type="button" wire:click="$set('showNewChatModal', true)"
                                class="inline-flex items-center gap-1 rounded-xl bg-amber-600 px-2.5 py-1.5 text-xs font-bold text-white shadow hover:bg-amber-500 transition"
                                title="Start new chat">
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4v16m8-8H4" />
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
                                    $unread = $conv->unread_count ?? $conv->unreadCountFor(auth()->id());
                                    $latest = $conv->latestMessage;
                                    $isPinned = $conv->isPinnedFor(auth()->id());
                                    $convRecipient = $conv->isGroup() ? null : $conv->getRecipient(auth()->id());
                                @endphp
                                <div wire:key="float-conv-{{ $conv->id }}"
                                    wire:click="selectConversation({{ $conv->id }})"
                                    class="group flex cursor-pointer items-center gap-2.5 p-2.5 transition hover:bg-amber-50/60 dark:hover:bg-gray-800/60 {{ $isPinned ? 'bg-amber-50/40 dark:bg-amber-950/20' : '' }}">
                                    <div class="relative flex-shrink-0">
                                        @if ($avatar)
                                            <img src="{{ $avatar }}"
                                                class="h-9 w-9 rounded-full object-cover flex-shrink-0" />
                                        @else
                                            <div
                                                class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-full bg-gradient-to-tr {{ $conv->isGroup() ? 'from-indigo-600 to-indigo-400' : 'from-amber-600 to-amber-400' }} text-xs font-bold text-white">
                                                {{ strtoupper(substr($title, 0, 1)) }}
                                            </div>
                                        @endif
                                        @if ($isPinned)
                                            <span
                                                class="absolute -top-1 -left-1 flex h-4 w-4 items-center justify-center rounded-full bg-amber-500 text-[9px] text-white shadow">
                                                📌
                                            </span>
                                        @endif
                                        @if (!$conv->isGroup() && $convRecipient)
                                            <span x-init="userOnlinePrivacyMap[{{ $convRecipient->id }}] = {{ $convRecipient->online_status_enabled ?? true ? 'true' : 'false' }};
                                            @if ($convRecipient->last_active_at) userLastActiveMap[{{ $convRecipient->id }}] = '{{ $convRecipient->getLastActiveFormatted() }}'; @endif" class="hidden"></span>
                                            <span x-show="isUserOnline({{ $convRecipient->id }})"
                                                class="absolute bottom-0 right-0 h-2.5 w-2.5 rounded-full border-2 border-white bg-emerald-500 dark:border-gray-900"
                                                title="Online"></span>
                                        @endif
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center justify-between">
                                            <span
                                                class="truncate text-xs font-semibold text-gray-900 dark:text-white flex items-center gap-1">
                                                {{ $title }}
                                            </span>
                                            @if ($latest)
                                                <span
                                                    class="text-[9px] text-gray-400">{{ $latest->created_at?->diffForHumans(null, true, true) }}</span>
                                            @endif
                                        </div>
                                        <p class="truncate text-[11px] text-gray-500 dark:text-gray-400">
                                            @if ($latest?->is_deleted)
                                                <span class="italic text-gray-400">This message was deleted</span>
                                            @else
                                                {{ $latest?->body ?? 'No messages yet' }}
                                            @endif
                                        </p>
                                    </div>
                                    <div class="flex items-center gap-1 flex-shrink-0">
                                        <!-- Pin Conversation Toggle -->
                                        @if ($isPinned)
                                            <button type="button"
                                                wire:click.stop="unpinConversation({{ $conv->id }})"
                                                class="rounded p-1 text-amber-600 hover:text-gray-400"
                                                title="Unpin chat">
                                                <span class="text-xs">📌</span>
                                            </button>
                                        @else
                                            <button type="button"
                                                wire:click.stop="openPinConversationModal({{ $conv->id }})"
                                                class="rounded p-1 text-gray-400 hover:text-amber-600 opacity-0 group-hover:opacity-100 transition-opacity"
                                                title="Pin chat to top">
                                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z" />
                                                </svg>
                                            </button>
                                        @endif

                                        @if ($unread > 0)
                                            <span
                                                class="flex h-4 min-w-4 items-center justify-center rounded-full bg-amber-500 px-1 text-[9px] font-bold text-white">
                                                {{ $unread }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="p-6 text-center text-xs text-gray-400">
                                    <p>No conversations found.</p>
                                    <button type="button" wire:click="$set('showNewChatModal', true)"
                                        class="mt-2 text-xs font-semibold text-amber-600 hover:underline dark:text-amber-400">
                                        + Start a new chat
                                    </button>
                                </div>
                            @endforelse
                        </div>
                        <div
                            class="border-t border-gray-100 p-2.5 text-center dark:border-gray-800 bg-gray-50/50 dark:bg-gray-900/50 flex items-center justify-between">
                            <button type="button" wire:click="$set('showNewChatModal', true)"
                                class="text-xs font-semibold text-amber-600 hover:underline dark:text-amber-400">
                                + New Conversation
                            </button>
                        </div>
                    @endif
                </div>

                <!-- Duration Picker Modal for Pinning (Message or Conversation) -->
                @if ($pinningMessageId || $pinningConversationId)
                    <div
                        class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 p-4 backdrop-blur-sm">
                        <div @click.outside="$wire.closePinMessageModal(); $wire.closePinConversationModal();"
                            class="relative w-full max-w-xs overflow-hidden rounded-2xl border border-gray-200 bg-white p-4 shadow-2xl dark:border-gray-800 dark:bg-gray-900 animate-in fade-in zoom-in-95 duration-150">
                            <div
                                class="flex items-center justify-between border-b border-gray-100 pb-2.5 dark:border-gray-800">
                                <div class="flex items-center gap-1.5">
                                    <span class="text-base">📌</span>
                                    <h4 class="text-xs font-bold text-gray-900 dark:text-white">
                                        {{ $pinningMessageId ? 'Pin Message' : 'Pin Conversation' }}
                                    </h4>
                                </div>
                                <button type="button"
                                    wire:click="{{ $pinningMessageId ? 'closePinMessageModal' : 'closePinConversationModal' }}"
                                    class="rounded-lg p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
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
                                @foreach ($durations as $d)
                                    <button type="button"
                                        wire:click="{{ $pinningMessageId ? 'pinMessage(' . $pinningMessageId . ', \'' . $d['val'] . '\')' : 'pinConversation(' . $pinningConversationId . ', \'' . $d['val'] . '\')' }}"
                                        class="flex items-center gap-2 rounded-xl border border-gray-200 p-2 text-left text-xs font-semibold text-gray-700 transition hover:border-amber-500 hover:bg-amber-50 hover:text-amber-700 dark:border-gray-700 dark:text-gray-200 dark:hover:border-amber-500 dark:hover:bg-amber-950/30 dark:hover:text-amber-300">
                                        <span>{{ $d['icon'] }}</span>
                                        <span class="truncate">{{ $d['label'] }}</span>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Group Settings & Management Modal (Floating) -->
                @if ($showGroupSettingsModal && $this->activeConversation && $this->activeConversation->isGroup())
                    @php
                        $isOwner = $this->activeConversation->isOwner(auth()->id());
                        $isAdmin = $this->activeConversation->isAdmin(auth()->id());
                        $canEditInfo = $this->activeConversation->canUserEditInfo(auth()->id());
                        $participants = $this->groupParticipantDetails;
                    @endphp
                    <div
                        class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 p-3 backdrop-blur-sm animate-in fade-in duration-150">
                        <div @click.outside="$wire.set('showGroupSettingsModal', false)"
                            class="relative w-full max-w-sm overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xl dark:border-gray-800 dark:bg-gray-900 flex flex-col max-h-[85vh]">
                            <!-- Modal Header -->
                            <div
                                class="flex items-center justify-between border-b border-gray-100 px-4 py-3 dark:border-gray-800">
                                <div class="flex items-center gap-2">
                                    <span class="text-sm">⚙️</span>
                                    <div>
                                        <h4 class="text-xs font-bold text-gray-900 dark:text-white">Group Settings</h4>
                                        <p class="text-[10px] text-gray-500 dark:text-gray-400 truncate max-w-[180px]">
                                            {{ $this->activeConversation->title }}</p>
                                    </div>
                                </div>
                                <button type="button" wire:click="$set('showGroupSettingsModal', false)"
                                    class="rounded-lg p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            <!-- Navigation Tabs -->
                            <div
                                class="flex border-b border-gray-100 px-3 gap-2 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-950/40 text-[11px] font-semibold">
                                <button type="button" wire:click="$set('groupSettingsTab', 'general')"
                                    class="py-2 border-b-2 transition {{ $groupSettingsTab === 'general' ? 'border-amber-600 text-amber-600 dark:border-amber-400 dark:text-amber-400' : 'border-transparent text-gray-500 hover:text-gray-800 dark:text-gray-400' }}">
                                    General
                                </button>
                                <button type="button" wire:click="$set('groupSettingsTab', 'members')"
                                    class="py-2 border-b-2 transition flex items-center gap-1 {{ $groupSettingsTab === 'members' ? 'border-amber-600 text-amber-600 dark:border-amber-400 dark:text-amber-400' : 'border-transparent text-gray-500 hover:text-gray-800 dark:text-gray-400' }}">
                                    <span>Members</span>
                                    <span
                                        class="rounded bg-gray-200 px-1 text-[9px] dark:bg-gray-800">{{ $participants->count() }}</span>
                                </button>
                                <button type="button" wire:click="$set('groupSettingsTab', 'permissions')"
                                    class="py-2 border-b-2 transition {{ $groupSettingsTab === 'permissions' ? 'border-amber-600 text-amber-600 dark:border-amber-400 dark:text-amber-400' : 'border-transparent text-gray-500 hover:text-gray-800 dark:text-gray-400' }}">
                                    Rules
                                </button>
                                <button type="button" wire:click="$set('groupSettingsTab', 'danger')"
                                    class="py-2 border-b-2 transition text-red-600 dark:text-red-400 {{ $groupSettingsTab === 'danger' ? 'border-red-600 font-bold' : 'border-transparent' }}">
                                    Actions
                                </button>
                            </div>

                            <!-- Tab Content Body -->
                            <div class="flex-1 overflow-y-auto p-4 space-y-3.5">
                                <!-- GENERAL TAB -->
                                @if ($groupSettingsTab === 'general')
                                    @if (!$canEditInfo)
                                        <div
                                            class="rounded-xl bg-amber-50 p-2.5 text-[11px] text-amber-800 dark:bg-amber-950/40 dark:text-amber-300 border border-amber-200 dark:border-amber-900/40">
                                            ℹ️ Only group admins can change the group name, icon, and description.
                                        </div>
                                    @endif

                                    <!-- Group Photo -->
                                    <div class="flex items-center gap-3">
                                        <div class="relative flex-shrink-0">
                                            @if ($settingsGroupAvatar)
                                                <img src="{{ $settingsGroupAvatar->temporaryUrl() }}"
                                                    class="h-12 w-12 rounded-full object-cover border-2 border-amber-500 shadow-md" />
                                            @elseif($this->activeConversation->avatar_url)
                                                <img src="{{ Storage::url($this->activeConversation->avatar_url) }}"
                                                    class="h-12 w-12 rounded-full object-cover border-2 border-gray-200 dark:border-gray-700 shadow-md" />
                                            @else
                                                <div
                                                    class="flex h-12 w-12 items-center justify-center rounded-full bg-gradient-to-tr from-amber-600 to-amber-400 text-base font-bold text-white shadow-md">
                                                    {{ strtoupper(substr($this->activeConversation->title, 0, 1)) }}
                                                </div>
                                            @endif
                                        </div>

                                        @if ($canEditInfo)
                                            <div class="space-y-1">
                                                <div class="flex items-center gap-1.5">
                                                    <label
                                                        class="cursor-pointer rounded-lg bg-gray-100 px-2.5 py-1 text-[11px] font-semibold text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-300">
                                                        <span>Upload</span>
                                                        <input type="file" wire:model="settingsGroupAvatar"
                                                            accept="image/*" class="hidden" />
                                                    </label>
                                                    @if ($this->activeConversation->avatar_url || $settingsGroupAvatar)
                                                        <button type="button" wire:click="removeGroupAvatar"
                                                            class="rounded-lg px-2 py-1 text-[11px] font-semibold text-red-600 hover:bg-red-50 dark:hover:bg-red-950/40">
                                                            Remove
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Group Title -->
                                    <div class="space-y-1">
                                        <label
                                            class="block text-[11px] font-semibold text-gray-700 dark:text-gray-300">Group
                                            Name</label>
                                        <input type="text" wire:model="settingsGroupTitle"
                                            {{ $canEditInfo ? '' : 'disabled' }}
                                            class="chat-input-field w-full rounded-xl border border-gray-300 bg-white px-3 py-1.5 text-xs text-gray-900 placeholder-gray-400 focus:border-amber-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white disabled:opacity-60" />
                                        @error('settingsGroupTitle')
                                            <span class="text-[10px] text-red-600">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <!-- Group Description -->
                                    <div class="space-y-1">
                                        <label
                                            class="block text-[11px] font-semibold text-gray-700 dark:text-gray-300">Description</label>
                                        <textarea wire:model="settingsGroupDescription" rows="2" {{ $canEditInfo ? '' : 'disabled' }}
                                            placeholder="Group description..."
                                            class="chat-input-field w-full rounded-xl border border-gray-300 bg-white px-3 py-1.5 text-xs text-gray-900 placeholder-gray-400 focus:border-amber-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white disabled:opacity-60 resize-none"></textarea>
                                        @error('settingsGroupDescription')
                                            <span class="text-[10px] text-red-600">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    @if ($canEditInfo)
                                        <div class="flex justify-end pt-1">
                                            <button type="button" wire:click="saveGroupSettings"
                                                class="rounded-xl bg-amber-600 px-3.5 py-1.5 text-xs font-bold text-white shadow hover:bg-amber-500">
                                                Save Changes
                                            </button>
                                        </div>
                                    @endif
                                @endif

                                <!-- MEMBERS TAB -->
                                @if ($groupSettingsTab === 'members')
                                    <div class="flex items-center justify-between pb-1">
                                        <span class="text-[11px] font-bold text-gray-700 dark:text-gray-300">Members
                                            ({{ $participants->count() }})</span>
                                        @if ($isAdmin)
                                            <button type="button" wire:click="openAddMembersModal"
                                                class="inline-flex items-center gap-1 rounded-lg bg-amber-600 px-2.5 py-1 text-[11px] font-semibold text-white shadow hover:bg-amber-500">
                                                <span>+ Add</span>
                                            </button>
                                        @endif
                                    </div>

                                    <div
                                        class="divide-y divide-gray-100 dark:divide-gray-800 rounded-xl border border-gray-200 dark:border-gray-800 overflow-hidden">
                                        @foreach ($participants as $p)
                                            <div
                                                class="flex items-center justify-between p-2.5 bg-white dark:bg-gray-900 hover:bg-gray-50/70 dark:hover:bg-gray-800/50 transition">
                                                <div class="flex items-center gap-2 min-w-0">
                                                    @if ($p['avatar'])
                                                        <img src="{{ $p['avatar'] }}"
                                                            class="h-7 w-7 rounded-full object-cover border border-gray-200 dark:border-gray-700" />
                                                    @else
                                                        <div
                                                            class="flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-full bg-amber-100 text-amber-800 dark:bg-gray-800 dark:text-amber-300 font-bold text-[10px]">
                                                            {{ $p['initial'] }}
                                                        </div>
                                                    @endif
                                                    <div class="min-w-0">
                                                        <div class="flex items-center gap-1">
                                                            <span
                                                                class="truncate text-[11px] font-bold text-gray-900 dark:text-white">{{ $p['name'] }}</span>
                                                            @if ($p['is_me'])
                                                                <span class="text-[9px] text-gray-400">(You)</span>
                                                            @endif
                                                        </div>
                                                        <div class="text-[9px] text-gray-400 truncate">
                                                            {{ $p['username'] ? '@' . $p['username'] : $p['email'] }}
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="flex items-center gap-1.5 flex-shrink-0">
                                                    @if ($p['role'] === 'owner')
                                                        <span
                                                            class="rounded bg-amber-500/20 px-1.5 py-0.2 text-[9px] font-bold text-amber-800 dark:text-amber-300">
                                                            👑 Owner
                                                        </span>
                                                    @elseif($p['role'] === 'admin')
                                                        <span
                                                            class="rounded bg-indigo-500/20 px-1.5 py-0.2 text-[9px] font-bold text-indigo-700 dark:text-indigo-300">
                                                            🛡️ Admin
                                                        </span>
                                                    @endif

                                                    @if (!$p['is_me'])
                                                        @if ($isOwner)
                                                            <button type="button"
                                                                wire:click="toggleAdminRole({{ $p['id'] }})"
                                                                class="rounded p-1 text-[10px] text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400"
                                                                title="{{ $p['is_admin'] ? 'Dismiss Admin' : 'Make Admin' }}">
                                                                {{ $p['is_admin'] ? 'Demote' : 'Promote' }}
                                                            </button>
                                                            <button type="button"
                                                                wire:click="openTransferOwnershipModal({{ $p['id'] }})"
                                                                class="rounded p-1 text-[10px] text-gray-400 hover:text-amber-600"
                                                                title="Transfer Ownership">
                                                                👑
                                                            </button>
                                                            <button type="button"
                                                                wire:confirm="Remove {{ $p['name'] }} from this group?"
                                                                wire:click="removeMemberFromGroup({{ $p['id'] }})"
                                                                class="rounded p-1 text-gray-400 hover:text-red-600"
                                                                title="Remove">
                                                                <svg class="h-3.5 w-3.5" fill="none"
                                                                    viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round"
                                                                        stroke-linejoin="round" stroke-width="2"
                                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                                </svg>
                                                            </button>
                                                        @elseif($isAdmin && !$p['is_admin'] && !$p['is_owner'])
                                                            <button type="button"
                                                                wire:confirm="Remove {{ $p['name'] }} from this group?"
                                                                wire:click="removeMemberFromGroup({{ $p['id'] }})"
                                                                class="rounded p-1 text-gray-400 hover:text-red-600"
                                                                title="Remove">
                                                                <svg class="h-3.5 w-3.5" fill="none"
                                                                    viewBox="0 0 24 24" stroke="currentColor">
                                                                    <path stroke-linecap="round"
                                                                        stroke-linejoin="round" stroke-width="2"
                                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
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
                                @if ($groupSettingsTab === 'permissions')
                                    <div class="space-y-3">
                                        <!-- Rule 1: Send Messages -->
                                        <div
                                            class="rounded-xl border border-gray-200 dark:border-gray-800 p-3 space-y-1.5 bg-gray-50/50 dark:bg-gray-950/30">
                                            <h5 class="text-xs font-bold text-gray-900 dark:text-white">Send Messages
                                            </h5>
                                            <div class="grid grid-cols-2 gap-2 text-[11px]">
                                                <button type="button"
                                                    @if ($isAdmin) wire:click="$set('settingsOnlyAdminsCanMessage', false)" @endif
                                                    class="flex items-center gap-1.5 rounded-lg border p-2 text-left transition {{ !$settingsOnlyAdminsCanMessage ? 'border-amber-500 bg-amber-50/60 dark:bg-amber-950/30 font-bold text-amber-900 dark:text-amber-200' : 'border-gray-200 dark:border-gray-800 text-gray-600 dark:text-gray-400' }} {{ $isAdmin ? 'cursor-pointer' : 'cursor-not-allowed opacity-60' }}">
                                                    <div
                                                        class="flex h-3.5 w-3.5 flex-shrink-0 items-center justify-center rounded-full border {{ !$settingsOnlyAdminsCanMessage ? 'border-amber-600 bg-amber-600 text-white' : 'border-gray-300 dark:border-gray-600' }}">
                                                        @if (!$settingsOnlyAdminsCanMessage)
                                                            <div class="h-1 w-1 rounded-full bg-white"></div>
                                                        @endif
                                                    </div>
                                                    <span>All Members</span>
                                                </button>
                                                <button type="button"
                                                    @if ($isAdmin) wire:click="$set('settingsOnlyAdminsCanMessage', true)" @endif
                                                    class="flex items-center gap-1.5 rounded-lg border p-2 text-left transition {{ $settingsOnlyAdminsCanMessage ? 'border-amber-500 bg-amber-50/60 dark:bg-amber-950/30 font-bold text-amber-900 dark:text-amber-200' : 'border-gray-200 dark:border-gray-800 text-gray-600 dark:text-gray-400' }} {{ $isAdmin ? 'cursor-pointer' : 'cursor-not-allowed opacity-60' }}">
                                                    <div
                                                        class="flex h-3.5 w-3.5 flex-shrink-0 items-center justify-center rounded-full border {{ $settingsOnlyAdminsCanMessage ? 'border-amber-600 bg-amber-600 text-white' : 'border-gray-300 dark:border-gray-600' }}">
                                                        @if ($settingsOnlyAdminsCanMessage)
                                                            <div class="h-1 w-1 rounded-full bg-white"></div>
                                                        @endif
                                                    </div>
                                                    <span>Only Admins</span>
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Rule 2: Edit Info -->
                                        <div
                                            class="rounded-xl border border-gray-200 dark:border-gray-800 p-3 space-y-1.5 bg-gray-50/50 dark:bg-gray-950/30">
                                            <h5 class="text-xs font-bold text-gray-900 dark:text-white">Edit Group Info
                                            </h5>
                                            <div class="grid grid-cols-2 gap-2 text-[11px]">
                                                <button type="button"
                                                    @if ($isAdmin) wire:click="$set('settingsOnlyAdminsCanEditInfo', false)" @endif
                                                    class="flex items-center gap-1.5 rounded-lg border p-2 text-left transition {{ !$settingsOnlyAdminsCanEditInfo ? 'border-amber-500 bg-amber-50/60 dark:bg-amber-950/30 font-bold text-amber-900 dark:text-amber-200' : 'border-gray-200 dark:border-gray-800 text-gray-600 dark:text-gray-400' }} {{ $isAdmin ? 'cursor-pointer' : 'cursor-not-allowed opacity-60' }}">
                                                    <div
                                                        class="flex h-3.5 w-3.5 flex-shrink-0 items-center justify-center rounded-full border {{ !$settingsOnlyAdminsCanEditInfo ? 'border-amber-600 bg-amber-600 text-white' : 'border-gray-300 dark:border-gray-600' }}">
                                                        @if (!$settingsOnlyAdminsCanEditInfo)
                                                            <div class="h-1 w-1 rounded-full bg-white"></div>
                                                        @endif
                                                    </div>
                                                    <span>All Members</span>
                                                </button>
                                                <button type="button"
                                                    @if ($isAdmin) wire:click="$set('settingsOnlyAdminsCanEditInfo', true)" @endif
                                                    class="flex items-center gap-1.5 rounded-lg border p-2 text-left transition {{ $settingsOnlyAdminsCanEditInfo ? 'border-amber-500 bg-amber-50/60 dark:bg-amber-950/30 font-bold text-amber-900 dark:text-amber-200' : 'border-gray-200 dark:border-gray-800 text-gray-600 dark:text-gray-400' }} {{ $isAdmin ? 'cursor-pointer' : 'cursor-not-allowed opacity-60' }}">
                                                    <div
                                                        class="flex h-3.5 w-3.5 flex-shrink-0 items-center justify-center rounded-full border {{ $settingsOnlyAdminsCanEditInfo ? 'border-amber-600 bg-amber-600 text-white' : 'border-gray-300 dark:border-gray-600' }}">
                                                        @if ($settingsOnlyAdminsCanEditInfo)
                                                            <div class="h-1 w-1 rounded-full bg-white"></div>
                                                        @endif
                                                    </div>
                                                    <span>Only Admins</span>
                                                </button>
                                            </div>
                                        </div>

                                        @if ($isAdmin)
                                            <div class="flex justify-end pt-1">
                                                <button type="button" wire:click="saveGroupSettings"
                                                    class="rounded-xl bg-amber-600 px-3.5 py-1.5 text-xs font-bold text-white shadow hover:bg-amber-500">
                                                    Save Rules
                                                </button>
                                            </div>
                                        @endif
                                    </div>
                                @endif

                                <!-- DANGER TAB -->
                                @if ($groupSettingsTab === 'danger')
                                    <div class="space-y-3">
                                        <div
                                            class="rounded-xl border border-gray-200 p-3 dark:border-gray-800 flex items-center justify-between">
                                            <div>
                                                <h5 class="text-xs font-bold text-gray-900 dark:text-white">Leave Group
                                                </h5>
                                                <p class="text-[10px] text-gray-500 dark:text-gray-400">Exit this
                                                    conversation.</p>
                                            </div>
                                            <button type="button"
                                                wire:confirm="Are you sure you want to leave this group?"
                                                wire:click="leaveGroup"
                                                class="rounded-lg border border-red-200 bg-red-50 px-2.5 py-1 text-xs font-bold text-red-600 hover:bg-red-100 dark:border-red-900/60 dark:bg-red-950/40 dark:text-red-400">
                                                Leave
                                            </button>
                                        </div>

                                        @if ($isOwner)
                                            <div
                                                class="rounded-xl border border-red-200 bg-red-50/40 p-3 dark:border-red-900/60 dark:bg-red-950/20 flex items-center justify-between">
                                                <div>
                                                    <h5 class="text-xs font-bold text-red-700 dark:text-red-400">Delete
                                                        Group</h5>
                                                    <p class="text-[10px] text-red-600/80 dark:text-red-400/80">
                                                        Permanently delete group.</p>
                                                </div>
                                                <button type="button"
                                                    wire:confirm="Are you sure you want to delete this group permanently?"
                                                    wire:click="deleteGroup"
                                                    class="rounded-lg bg-red-600 px-2.5 py-1 text-xs font-bold text-white shadow hover:bg-red-500">
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
                @if ($showAddMembersModal)
                    <div
                        class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 p-3 backdrop-blur-sm animate-in fade-in duration-150">
                        <div @click.outside="$wire.set('showAddMembersModal', false)"
                            class="relative w-full max-w-sm overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xl dark:border-gray-800 dark:bg-gray-900">
                            <div
                                class="flex items-center justify-between border-b border-gray-100 p-3 dark:border-gray-800">
                                <h4 class="text-xs font-bold text-gray-900 dark:text-white">Add Members to Group</h4>
                                <button type="button" wire:click="$set('showAddMembersModal', false)"
                                    class="rounded p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            <div class="p-2.5 border-b border-gray-100 dark:border-gray-800">
                                <input type="text" wire:model.live.debounce.200ms="memberSearch"
                                    placeholder="Search users..."
                                    class="chat-input-field w-full rounded-xl border border-gray-300 bg-white px-3 py-1.5 text-xs text-gray-900 placeholder-gray-400 focus:border-amber-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                            </div>

                            <div class="max-h-60 overflow-y-auto divide-y divide-gray-100 dark:divide-gray-800">
                                @forelse($this->addableUsers as $u)
                                    @php $isMarked = in_array($u->id, $newMemberIds, true); @endphp
                                    <div wire:click="toggleAddMember({{ $u->id }})"
                                        class="flex cursor-pointer items-center justify-between p-2.5 hover:bg-amber-50/60 dark:hover:bg-gray-800/60 transition {{ $isMarked ? 'bg-amber-50 dark:bg-amber-950/40' : '' }}">
                                        <div class="flex items-center gap-2 min-w-0">
                                            @if ($u->getFilamentAvatarUrl())
                                                <img src="{{ $u->getFilamentAvatarUrl() }}"
                                                    class="h-7 w-7 rounded-full object-cover border border-gray-200 dark:border-gray-700" />
                                            @else
                                                <div
                                                    class="flex h-7 w-7 items-center justify-center rounded-full bg-amber-100 text-amber-800 dark:bg-gray-800 dark:text-amber-300 font-bold text-xs">
                                                    {{ strtoupper(substr($u->name, 0, 1)) }}
                                                </div>
                                            @endif
                                            <div class="min-w-0">
                                                <p
                                                    class="truncate text-xs font-semibold text-gray-900 dark:text-white">
                                                    {{ $u->name }}</p>
                                                <p class="truncate text-[10px] text-gray-400">{{ $u->email }}</p>
                                            </div>
                                        </div>
                                        <input type="checkbox"
                                            class="rounded border-gray-300 text-amber-600 focus:ring-amber-500 dark:border-gray-700 dark:bg-gray-800"
                                            {{ $isMarked ? 'checked' : '' }}
                                            wire:click.stop="toggleAddMember({{ $u->id }})" />
                                    </div>
                                @empty
                                    <div class="p-4 text-center text-xs text-gray-400">
                                        No eligible users found.
                                    </div>
                                @endforelse
                            </div>

                            <div
                                class="flex items-center justify-between border-t border-gray-100 p-2.5 bg-gray-50/50 dark:border-gray-800 dark:bg-gray-950/40">
                                <span class="text-xs text-gray-500">Selected:
                                    <strong>{{ count($newMemberIds) }}</strong></span>
                                <button type="button" wire:click="addMembersToGroup"
                                    {{ empty($newMemberIds) ? 'disabled' : '' }}
                                    class="rounded-xl bg-amber-600 px-3.5 py-1 text-xs font-bold text-white shadow hover:bg-amber-500 disabled:opacity-50">
                                    Add to Group
                                </button>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Transfer Ownership Modal (Floating) -->
                @if ($showTransferOwnershipModal)
                    @php $transferUser = \App\Models\User::find($transferOwnershipUserId); @endphp
                    <div
                        class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 p-3 backdrop-blur-sm animate-in fade-in duration-150">
                        <div @click.outside="$wire.set('showTransferOwnershipModal', false)"
                            class="relative w-full max-w-xs overflow-hidden rounded-2xl border border-gray-200 bg-white p-4 shadow-2xl dark:border-gray-800 dark:bg-gray-900">
                            <div class="flex items-center gap-2 text-amber-600 dark:text-amber-400 mb-2">
                                <span class="text-xl">👑</span>
                                <h4 class="text-xs font-bold text-gray-900 dark:text-white">Transfer Ownership</h4>
                            </div>

                            <p class="text-xs text-gray-600 dark:text-gray-300 leading-relaxed mb-3">
                                Transfer group ownership to <strong>{{ $transferUser?->name }}</strong>?
                            </p>

                            <div
                                class="rounded-xl bg-amber-50 p-2 text-[10px] text-amber-800 dark:bg-amber-950/40 dark:text-amber-300 border border-amber-200 dark:border-amber-900/40 mb-3">
                                ⚠️ The new owner will have full control and can demote or remove you.
                            </div>

                            <div class="flex items-center justify-end gap-2">
                                <button type="button" wire:click="$set('showTransferOwnershipModal', false)"
                                    class="rounded-lg border border-gray-300 px-2.5 py-1 text-xs font-semibold text-gray-700 hover:bg-gray-100 dark:border-gray-700 dark:text-gray-300">
                                    Cancel
                                </button>
                                <button type="button" wire:click="confirmTransferOwnership"
                                    class="rounded-lg bg-amber-600 px-3 py-1 text-xs font-bold text-white shadow hover:bg-amber-500">
                                    Confirm
                                </button>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Pinned Messages List Modal in Floating Widget -->
                @if ($showPinnedMessagesModal)
                    <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 p-4 backdrop-blur-sm"
                        @click.self="$wire.set('showPinnedMessagesModal', false)">
                        <div @click.outside="$wire.set('showPinnedMessagesModal', false)"
                            class="relative w-full max-w-sm overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xl dark:border-gray-800 dark:bg-gray-900 flex flex-col max-h-[80vh]">
                            <div
                                class="flex items-center justify-between border-b border-gray-100 px-4 py-3 dark:border-gray-800">
                                <div class="flex items-center gap-2">
                                    <span
                                        class="flex h-7 w-7 items-center justify-center rounded-lg bg-amber-500/10 text-amber-600 dark:bg-amber-400/10 dark:text-amber-400 text-xs">
                                        📌
                                    </span>
                                    <div>
                                        <h4 class="text-xs font-bold text-gray-900 dark:text-white">Pinned Messages
                                        </h4>
                                        <p class="text-[10px] text-gray-400">{{ $this->pinnedMessages->count() }}
                                            pinned</p>
                                    </div>
                                </div>
                                <button type="button" wire:click="closePinnedMessagesModal"
                                    class="rounded-lg p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            <div
                                class="flex-1 overflow-y-auto p-3 divide-y divide-gray-100 dark:divide-gray-800 space-y-2.5">
                                @forelse($this->pinnedMessages as $pm)
                                    <div class="pt-2.5 first:pt-0 flex items-start justify-between gap-2 group">
                                        <div class="flex items-start gap-2 min-w-0 flex-1">
                                            @if ($pm->sender?->avatar_url)
                                                <img src="{{ $pm->sender->getFilamentAvatarUrl() }}"
                                                    class="h-7 w-7 rounded-full object-cover flex-shrink-0" />
                                            @else
                                                <div
                                                    class="flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-full bg-amber-100 text-amber-800 dark:bg-gray-800 dark:text-amber-300 font-bold text-[10px]">
                                                    {{ strtoupper(substr($pm->sender?->name ?? 'U', 0, 1)) }}
                                                </div>
                                            @endif
                                            <div class="min-w-0 flex-1">
                                                <div class="flex items-center gap-1.5">
                                                    <span
                                                        class="font-bold text-[11px] text-gray-900 dark:text-white truncate">{{ $pm->sender?->name }}</span>
                                                    <span
                                                        class="rounded bg-amber-100 px-1 py-0.2 text-[8px] font-semibold text-amber-800 dark:bg-amber-900/50 dark:text-amber-300">
                                                        {{ $pm->getPinnedTimeRemaining() }}
                                                    </span>
                                                </div>
                                                <div class="mt-0.5 text-[11px] text-gray-600 dark:text-gray-300">
                                                    @if ($pm->isImage())
                                                        <div
                                                            class="flex items-center gap-1 text-amber-600 dark:text-amber-400">
                                                            <span>🖼️</span> <span>Photo</span>
                                                        </div>
                                                    @elseif($pm->isAudio())
                                                        <div
                                                            class="flex items-center gap-1 text-amber-600 dark:text-amber-400">
                                                            <span>🎤</span> <span>Voice note</span>
                                                        </div>
                                                    @elseif($pm->isFile())
                                                        <div
                                                            class="flex items-center gap-1 text-amber-600 dark:text-amber-400">
                                                            <span>📎</span> <span
                                                                class="truncate">{{ $pm->attachment_name }}</span>
                                                        </div>
                                                    @endif
                                                    @if ($pm->body)
                                                        <p class="truncate line-clamp-2 mt-0.5">{{ $pm->body }}
                                                        </p>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-1 flex-shrink-0">
                                            <button type="button"
                                                wire:click="jumpToPinnedMessage({{ $pm->id }})"
                                                class="rounded-lg bg-gray-100 px-2 py-0.5 text-[10px] font-semibold text-gray-700 hover:bg-amber-100 hover:text-amber-800 dark:bg-gray-800 dark:text-gray-300 transition cursor-pointer">
                                                Jump
                                            </button>
                                            <button type="button" wire:click="unpinMessage({{ $pm->id }})"
                                                class="rounded p-1 text-gray-400 hover:text-red-500 transition"
                                                title="Unpin">
                                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2" d="M6 18L18 6M6 6l12 12" />
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

                <!-- Forward Message Modal inside Floating Widget -->
                @if ($showForwardModal && $forwardingMessageId)
                    @php $fwdMsg = $this->forwardingMessage; @endphp
                    <div
                        class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 p-4 backdrop-blur-sm">
                        <div @click.outside="$wire.closeForwardModal()"
                            class="relative w-full max-w-sm overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xl dark:border-gray-800 dark:bg-gray-900 flex flex-col max-h-[80vh] animate-in fade-in zoom-in-95 duration-150">
                            <!-- Modal Header -->
                            <div
                                class="flex items-center justify-between border-b border-gray-100 px-3.5 py-2.5 dark:border-gray-800">
                                <div class="flex items-center gap-1.5">
                                    <span
                                        class="flex h-6 w-6 items-center justify-center rounded-lg bg-amber-500/10 text-amber-600 dark:bg-amber-400/10 dark:text-amber-400 text-xs">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M21 10H11a8 8 0 00-8 8v2m18-10l-6-6m6 6l-6 6" />
                                        </svg>
                                    </span>
                                    <h4 class="text-xs font-bold text-gray-900 dark:text-white">Forward Message</h4>
                                </div>
                                <button type="button" wire:click="closeForwardModal"
                                    class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                        stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            @if ($fwdMsg)
                                <!-- Forwarded Message Summary Card -->
                                <div
                                    class="border-b border-gray-100 bg-gray-50/70 p-2.5 dark:border-gray-800 dark:bg-gray-950/40">
                                    <div
                                        class="rounded-xl border border-gray-200 bg-white p-2 text-xs shadow-sm dark:border-gray-700/80 dark:bg-gray-800">
                                        <div
                                            class="flex items-center gap-1 text-[10px] font-bold text-amber-600 dark:text-amber-400 mb-0.5">
                                            <span>{{ $fwdMsg->sender?->name ?? 'User' }}</span>
                                        </div>
                                        <p class="truncate text-gray-700 dark:text-gray-200 text-[11px]">
                                            {{ $fwdMsg->getReplySnippet(75) }}
                                        </p>
                                    </div>
                                </div>
                            @endif

                            <!-- Search Input -->
                            <div class="border-b border-gray-100 p-2 dark:border-gray-800">
                                <input type="text" wire:model.live.debounce.250ms="forwardSearch"
                                    placeholder="Search chats..."
                                    class="chat-input-field w-full rounded-xl border border-gray-300 bg-white px-3 py-1.5 text-xs text-gray-900 placeholder-gray-400 focus:border-amber-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100" />
                            </div>

                            <!-- Conversations List -->
                            <div
                                class="flex-1 overflow-y-auto divide-y divide-gray-100 dark:divide-gray-800 max-h-56">
                                @forelse($this->forwardableConversations as $fConv)
                                    @php
                                        $fSelected = in_array($fConv->id, $selectedForwardConversationIds, true);
                                        $fTitle = $fConv->getDisplayName(auth()->id());
                                        $fAvatar = $fConv->getDisplayAvatar(auth()->id());
                                    @endphp
                                    <div wire:key="float-fwd-conv-{{ $fConv->id }}"
                                        wire:click="toggleForwardConversation({{ $fConv->id }})"
                                        class="flex cursor-pointer items-center justify-between p-2.5 transition hover:bg-amber-50/60 dark:hover:bg-gray-800/60 {{ $fSelected ? 'bg-amber-50/80 dark:bg-amber-950/40' : '' }}">
                                        <div class="flex items-center gap-2 min-w-0">
                                            @if ($fAvatar)
                                                <img src="{{ $fAvatar }}"
                                                    class="h-7 w-7 rounded-full object-cover flex-shrink-0" />
                                            @else
                                                <div
                                                    class="flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-full bg-gradient-to-tr {{ $fConv->isGroup() ? 'from-indigo-600 to-indigo-400' : 'from-amber-600 to-amber-400' }} text-[10px] font-bold text-white shadow-sm">
                                                    {{ strtoupper(substr($fTitle, 0, 1)) }}
                                                </div>
                                            @endif
                                            <div class="min-w-0">
                                                <span
                                                    class="block truncate text-xs font-semibold text-gray-900 dark:text-white">{{ $fTitle }}</span>
                                                <span
                                                    class="text-[9px] text-gray-400">{{ $fConv->isGroup() ? 'Group' : 'Direct' }}</span>
                                            </div>
                                        </div>
                                        <input type="checkbox"
                                            class="rounded border-gray-300 text-amber-600 focus:ring-amber-500 dark:border-gray-700 dark:bg-gray-800"
                                            {{ $fSelected ? 'checked' : '' }}
                                            wire:click.stop="toggleForwardConversation({{ $fConv->id }})" />
                                    </div>
                                @empty
                                    <div class="p-4 text-center text-xs text-gray-400 dark:text-gray-500">
                                        No conversations found.
                                    </div>
                                @endforelse
                            </div>

                            <!-- Modal Footer -->
                            <div
                                class="flex items-center justify-between border-t border-gray-100 bg-gray-50/50 p-2.5 dark:border-gray-800 dark:bg-gray-900/50">
                                <span class="text-[11px] text-gray-500 dark:text-gray-400">
                                    Selected: <strong>{{ count($selectedForwardConversationIds) }}</strong>
                                </span>
                                <div class="flex items-center gap-1.5">
                                    <button type="button" wire:click="closeForwardModal"
                                        class="rounded-xl px-2.5 py-1 text-xs font-medium text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800 transition">
                                        Cancel
                                    </button>
                                    <button type="button" wire:click="forwardMessage" @disabled(empty($selectedForwardConversationIds))
                                        class="inline-flex items-center gap-1 rounded-xl bg-gradient-to-r from-amber-600 to-orange-600 px-3 py-1 text-xs font-bold text-white shadow hover:from-amber-500 hover:to-orange-500 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed transition">
                                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24"
                                            stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M21 10H11a8 8 0 00-8 8v2m18-10l-6-6m6 6l-6 6" />
                                        </svg>
                                        <span>Forward ({{ count($selectedForwardConversationIds) }})</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
</div>
