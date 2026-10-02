<x-filament-panels::page class="chat-page-container">
    <div 
        x-data="{
            activeConversationId: @entangle('activeConversationId'),
            onlineUserIds: [],
            isRecording: false,
            mediaRecorder: null,
            audioChunks: [],
            recordingTime: 0,
            recordingInterval: null,

            init() {
                this.scrollToBottom();
                this.initEcho();

                $wire.on('conversation-changed', (event) => {
                    this.$nextTick(() => {
                        this.subscribeToConversation(event.conversationId);
                        this.scrollToBottom();
                    });
                });

                $wire.on('scroll-to-bottom', () => {
                    this.$nextTick(() => this.scrollToBottom());
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
                try {
                    const stream = await navigator.mediaDevices.getUserMedia({ audio: true });
                    this.mediaRecorder = new MediaRecorder(stream);
                    this.audioChunks = [];
                    this.recordingTime = 0;
                    this.isRecording = true;

                    this.recordingInterval = setInterval(() => {
                        this.recordingTime++;
                    }, 1000);

                    this.mediaRecorder.ondataavailable = (e) => {
                        if (e.data.size > 0) {
                            this.audioChunks.push(e.data);
                        }
                    };

                    this.mediaRecorder.onstop = () => {
                        clearInterval(this.recordingInterval);
                        stream.getTracks().forEach(track => track.stop());

                        if (this.audioChunks.length > 0) {
                            const audioBlob = new Blob(this.audioChunks, { type: 'audio/webm' });
                            const audioFile = new File([audioBlob], 'voice_note_' + Date.now() + '.webm', { type: 'audio/webm' });
                            
                            @this.upload('voiceNote', audioFile, () => {
                                $wire.sendMessage();
                            }, () => {}, () => {});
                        }
                    };

                    this.mediaRecorder.start();
                } catch (err) {
                    alert('Microphone access is required to send voice messages.');
                }
            },

            stopAndSendRecording() {
                if (this.mediaRecorder && this.isRecording) {
                    this.isRecording = false;
                    this.mediaRecorder.stop();
                }
            },

            cancelRecording() {
                if (this.mediaRecorder && this.isRecording) {
                    this.audioChunks = [];
                    this.isRecording = false;
                    clearInterval(this.recordingInterval);
                    this.mediaRecorder.stop();
                }
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
                            <h3 class="text-sm font-bold text-gray-900 dark:text-white">
                                {{ $activeName }}
                            </h3>
                            <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-slate-400">
                                @if($isGroup)
                                    <span>{{ $this->activeConversation->participants->count() }} members</span>
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

                    <!-- Header Actions (Search) -->
                    <div class="flex items-center gap-2">
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
                                @click="scrollToMessage({{ $firstPinned->id }})"
                                class="flex items-center gap-2 min-w-0 text-left hover:opacity-85 transition"
                                title="Click to jump to pinned message"
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
                                    <span class="rounded-full bg-amber-200 px-2 py-0.5 text-[10px] font-bold text-amber-800 dark:bg-amber-900 dark:text-amber-200">
                                        {{ $pinnedMessages->count() }} pinned
                                    </span>
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

                                <div class="relative rounded-2xl px-4 py-2.5 text-xs shadow-sm transition-all {{ $isMe ? 'bg-amber-600 text-white rounded-br-sm' : 'bg-white text-gray-900 border border-gray-200 dark:border-slate-700/80 dark:bg-slate-800 dark:text-slate-100 rounded-bl-sm' }}">
                                    <!-- Sender Name for Groups -->
                                    @if($isGroup && !$isMe)
                                        <span class="block text-[11px] font-bold text-amber-600 dark:text-amber-400 mb-1">
                                            {{ $msg->sender?->name }}
                                        </span>
                                    @endif

                                    @if($isPinned)
                                        <div class="mb-1.5 flex items-center gap-1 text-[10px] font-bold {{ $isMe ? 'text-amber-200' : 'text-amber-600 dark:text-amber-400' }}">
                                            <span>📌 Pinned</span>
                                            @if($msg->pinned_until)
                                                <span class="opacity-75 font-normal">({{ $msg->getPinnedTimeRemaining() }})</span>
                                            @endif
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
                                            <div class="my-1.5 overflow-hidden rounded-xl">
                                                <a href="{{ $msg->getAttachmentUrl() }}" target="_blank">
                                                    <img src="{{ $msg->getAttachmentUrl() }}" class="max-h-64 max-w-full rounded-xl object-cover hover:opacity-95 transition" />
                                                </a>
                                            </div>
                                        @elseif($msg->isAudio())
                                            <!-- Voice note / Audio -->
                                            <div class="my-1.5 flex items-center gap-2 rounded-xl p-1.5 {{ $isMe ? 'bg-amber-700/40' : 'bg-gray-100 dark:bg-slate-700/60' }}">
                                                <audio controls class="max-w-[240px] sm:max-w-[280px] h-8">
                                                    <source src="{{ $msg->getAttachmentUrl() }}" type="{{ $msg->file_type ?? 'audio/webm' }}">
                                                </audio>
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
                                                <a
                                                    href="{{ $msg->getAttachmentUrl() }}"
                                                    download="{{ $msg->attachment_name }}"
                                                    class="rounded-lg p-1 hover:bg-black/10 dark:hover:bg-white/10"
                                                >
                                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                                    </svg>
                                                </a>
                                            </div>
                                        @endif

                                        @if($msg->body && (!$msg->isImage() && !$msg->isAudio() && !$msg->isFile() || $msg->body !== $msg->attachment_name))
                                            <p class="whitespace-pre-wrap break-words leading-relaxed">{{ $msg->body }}</p>
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

                                <!-- Action Buttons on Hover (Pin, Delete) -->
                                @if(!$msg->is_deleted)
                                    <div class="opacity-0 group-hover:opacity-100 transition-opacity flex items-center gap-1 self-center">
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
                                                title="Delete message (valid for 15 mins)"
                                            >
                                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        @endif
                                    </div>
                                @endif
                            </div>
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
                <div class="border-t border-gray-200 bg-white p-3 dark:border-slate-800 dark:bg-slate-900">
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

                    <!-- Recording View or Normal Input View -->
                    <div x-show="isRecording" class="flex items-center justify-between rounded-xl bg-red-50/70 p-2.5 dark:bg-red-950/30 border border-red-200 dark:border-red-900/60" style="display: none;">
                        <div class="flex items-center gap-2">
                            <span class="h-3 w-3 rounded-full bg-red-500 animate-pulse"></span>
                            <span class="text-xs font-semibold text-red-700 dark:text-red-400">Recording...</span>
                            <span x-text="formatTime(recordingTime)" class="font-mono text-xs text-red-600 dark:text-red-300">00:00</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <!-- Cancel Recording -->
                            <button
                                type="button"
                                @click="cancelRecording"
                                class="rounded-lg p-1.5 text-gray-500 hover:bg-red-100 hover:text-red-600 dark:text-gray-400 dark:hover:bg-red-900/50"
                                title="Cancel recording"
                            >
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                            <!-- Send Recording -->
                            <button
                                type="button"
                                @click="stopAndSendRecording"
                                class="inline-flex items-center gap-1 rounded-xl bg-red-600 px-3 py-1.5 text-xs font-bold text-white shadow hover:bg-red-500"
                            >
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span>Send Voice</span>
                            </button>
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

                        <!-- Text Input -->
                        <div class="relative flex-1">
                            <input
                                type="text"
                                wire:model="messageText"
                                @keydown="handleKeyDown"
                                placeholder="Write a message... (Press Enter to send)"
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
</x-filament-panels::page>
