<div>
@if(!request()->is('*kamkaj/chat*'))
<div
    x-data="{
        isOpen: @entangle('isOpen'),
        activeConversationId: @entangle('activeConversationId'),
        isRecording: false,
        mediaRecorder: null,
        audioChunks: [],
        recordingTime: 0,
        recordingInterval: null,

        init() {
            this.initEcho();

            $wire.on('floating-conversation-changed', (event) => {
                this.$nextTick(() => {
                    this.subscribeToConversation(event.conversationId);
                    this.scrollToBottom();
                });
            });

            $wire.on('scroll-floating-chat', () => {
                this.$nextTick(() => this.scrollToBottom());
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
                alert('Microphone access is required to record voice messages.');
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
    class="fixed bottom-5 right-5 z-40 font-sans"
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
                                <span class="rounded bg-indigo-100 px-1 py-0.2 text-[9px] font-bold text-indigo-700 dark:bg-indigo-900/60 dark:text-indigo-300">
                                    Group
                                </span>
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
                            @click="scrollToMessage({{ $firstPinned->id }})"
                            class="flex items-center gap-1.5 min-w-0 text-left hover:opacity-80 transition"
                            title="Click to jump to pinned message"
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
                                <span class="rounded-full bg-amber-200 px-1.5 py-0.2 text-[9px] font-bold text-amber-800 dark:bg-amber-900 dark:text-amber-200">
                                    {{ $pinnedMessages->count() }} pinned
                                </span>
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

                            <div class="relative rounded-2xl px-3 py-2 text-xs shadow-sm transition-all {{ $isMe ? 'bg-amber-600 text-white rounded-br-sm' : 'bg-white text-gray-900 border border-gray-200 dark:border-gray-800 dark:bg-gray-800 dark:text-gray-100 rounded-bl-sm' }}">
                                @if($this->activeConversation->isGroup() && !$isMe)
                                    <span class="block text-[10px] font-semibold text-amber-600 dark:text-amber-400 mb-0.5">
                                        {{ $msg->sender?->name }}
                                    </span>
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
                                        <a href="{{ $msg->getAttachmentUrl() }}" target="_blank">
                                            <img src="{{ $msg->getAttachmentUrl() }}" class="max-h-48 max-w-full rounded-lg object-cover my-1" />
                                        </a>
                                    @elseif($msg->isAudio())
                                        <audio controls class="max-w-[210px] my-1 h-8">
                                            <source src="{{ $msg->getAttachmentUrl() }}" type="{{ $msg->file_type ?? 'audio/webm' }}">
                                        </audio>
                                    @elseif($msg->isFile())
                                        <div class="my-1 flex items-center justify-between gap-2 rounded-lg p-1.5 {{ $isMe ? 'bg-amber-700/50' : 'bg-gray-100 dark:bg-gray-700/60' }}">
                                            <div class="flex items-center gap-1.5 min-w-0">
                                                <svg class="h-4 w-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                </svg>
                                                <span class="truncate text-[11px] font-medium">{{ $msg->attachment_name }}</span>
                                            </div>
                                            <a href="{{ $msg->getAttachmentUrl() }}" download="{{ $msg->attachment_name }}" class="p-1 hover:opacity-80">
                                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                                </svg>
                                            </a>
                                        </div>
                                    @endif

                                    @if($msg->body && (!$msg->isImage() && !$msg->isAudio() && !$msg->isFile() || $msg->body !== $msg->attachment_name))
                                        <p class="whitespace-pre-wrap break-words leading-relaxed">{{ $msg->body }}</p>
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
                                            title="Delete message (valid for 15 mins)"
                                        >
                                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    @endif
                                </div>
                            @endif
                        </div>
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

            <!-- Mini Input Bar Area -->
            <div class="border-t border-gray-100 bg-white p-2.5 dark:border-gray-800 dark:bg-gray-900">
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

                <!-- Recording Bar Active -->
                <div x-show="isRecording" class="flex items-center justify-between rounded-xl bg-red-50/80 p-2 dark:bg-red-950/40 border border-red-200 dark:border-red-900" style="display: none;">
                    <div class="flex items-center gap-2">
                        <span class="h-2.5 w-2.5 rounded-full bg-red-500 animate-pulse"></span>
                        <span class="text-xs font-semibold text-red-700 dark:text-red-400">Recording</span>
                        <span x-text="formatTime(recordingTime)" class="font-mono text-xs text-red-600 dark:text-red-300">00:00</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <button
                            type="button"
                            @click="cancelRecording"
                            class="rounded-lg p-1 text-gray-500 hover:text-red-600 dark:text-gray-400"
                            title="Cancel"
                        >
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                        <button
                            type="button"
                            @click="stopAndSendRecording"
                            class="rounded-lg bg-red-600 px-2.5 py-1 text-xs font-bold text-white shadow hover:bg-red-500"
                        >
                            Send Voice
                        </button>
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

                    <!-- Input Field (Dark Mode bullet-proof) -->
                    <input
                        type="text"
                        wire:model="messageText"
                        placeholder="Write a message..."
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
