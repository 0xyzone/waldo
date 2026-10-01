<div>
@if(!request()->is('*kamkaj/chat*'))
<div
    x-data="{
        isOpen: @entangle('isOpen'),
        activeConversationId: @entangle('activeConversationId'),

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
                });

            if (this.activeConversationId) {
                this.subscribeToConversation(this.activeConversationId);
            }
        },

        subscribeToConversation(conversationId) {
            if (!window.Echo || !conversationId) return;

            window.Echo.private(`chat.conversation.${conversationId}`)
                .stopListening('.message.sent')
                .listen('.message.sent', (payload) => {
                    if (payload.sender_id !== {{ auth()->id() }}) {
                        this.playTing();
                    }
                    $wire.incomingMessage(payload);
                    $wire.$refresh();
                    this.$nextTick(() => this.scrollToBottom());
                });
        },

        scrollToBottom() {
            const feed = this.$refs.floatingFeed;
            if (feed) {
                feed.scrollTop = feed.scrollHeight;
            }
        },

        playTing() {
            try {
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                const now = ctx.currentTime;
                
                // First chime tone
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

                // Second higher chime tone
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
        class="mb-3 flex h-[520px] max-h-[82vh] w-[360px] sm:w-[390px] flex-col overflow-hidden rounded-2xl border border-gray-200/90 bg-white shadow-2xl backdrop-blur-xl dark:border-gray-800 dark:bg-gray-900"
    >
        <!-- Window Top Bar -->
        <div class="flex items-center justify-between border-b border-gray-100 bg-gray-50/80 px-4 py-3 dark:border-gray-800/80 dark:bg-gray-800/60">
            <div class="flex items-center gap-2 min-w-0">
                @if($this->activeConversation)
                    <button
                        type="button"
                        wire:click="$set('activeConversationId', null)"
                        class="rounded-lg p-1 text-gray-500 hover:bg-gray-200 dark:text-gray-400 dark:hover:bg-gray-700"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </button>
                    <div class="min-w-0">
                        <h4 class="truncate text-xs font-bold text-gray-900 dark:text-white">
                            {{ $this->activeConversation->getDisplayName(auth()->id()) }}
                        </h4>
                    </div>
                @else
                    <h4 class="text-xs font-bold text-gray-900 dark:text-white flex items-center gap-1.5">
                        <span class="h-2 w-2 rounded-full bg-amber-500"></span>
                        Quick Chat
                    </h4>
                @endif
            </div>
            <div class="flex items-center gap-1">
                <!-- Open Full Page -->
                <a
                    href="{{ url('/kamkaj/chat' . ($activeConversationId ? '?c=' . $activeConversationId : '')) }}"
                    class="rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800 dark:hover:text-gray-200"
                    title="Open full page"
                >
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                </a>
                <!-- Close Button -->
                <button
                    type="button"
                    wire:click="toggleWidget"
                    class="rounded-lg p-1.5 text-gray-400 transition hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800 dark:hover:text-gray-200"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        @if($this->activeConversation)
            <!-- Active Chat Feed -->
            <div
                x-ref="floatingFeed"
                class="flex-1 overflow-y-auto p-3.5 space-y-2.5 bg-gray-50/30 dark:bg-gray-950/40"
            >
                @php $messages = $this->messages; @endphp
                @forelse($messages as $msg)
                    @php $isMe = $msg->sender_id === auth()->id(); @endphp
                    <div class="flex flex-col {{ $isMe ? 'items-end' : 'items-start' }}">
                        <div class="flex items-end gap-1.5 max-w-[85%] {{ $isMe ? 'flex-row-reverse' : 'flex-row' }}">
                            @if(!$isMe)
                                @if($msg->sender?->avatar_url)
                                    <img src="{{ $msg->sender->getFilamentAvatarUrl() }}" class="h-6 w-6 rounded-full object-cover flex-shrink-0" />
                                @else
                                    <div class="flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full bg-amber-500/20 text-[10px] font-bold text-amber-700 dark:text-amber-300">
                                        {{ strtoupper(substr($msg->sender?->name ?? 'U', 0, 1)) }}
                                    </div>
                                @endif
                            @endif
                            <div class="rounded-2xl px-3 py-2 text-xs shadow-sm {{ $isMe ? 'bg-amber-600 text-white rounded-br-sm' : 'bg-white text-gray-900 border border-gray-200 dark:border-gray-800 dark:bg-gray-800 dark:text-gray-100 rounded-bl-sm' }}">
                                @if($this->activeConversation->isGroup() && !$isMe)
                                    <span class="block text-[10px] font-semibold text-amber-600 dark:text-amber-400 mb-0.5">
                                        {{ $msg->sender?->name }}
                                    </span>
                                @endif
                                
                                @if($msg->isImage())
                                    <img src="{{ $msg->getAttachmentUrl() }}" class="max-h-48 max-w-full rounded-lg object-cover my-1" />
                                @elseif($msg->isAudio())
                                    <audio controls class="max-w-[220px] my-1 h-8">
                                        <source src="{{ $msg->getAttachmentUrl() }}" type="{{ $msg->file_type ?? 'audio/webm' }}">
                                    </audio>
                                @endif

                                @if($msg->body)
                                    <p class="whitespace-pre-wrap break-words leading-relaxed">{{ $msg->body }}</p>
                                @endif

                                <div class="mt-0.5 flex items-center justify-end text-[9px] {{ $isMe ? 'text-amber-100/75' : 'text-gray-400' }}">
                                    <span>{{ $msg->created_at?->format('h:i A') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="flex h-full items-center justify-center text-center p-4 text-gray-400 text-xs">
                        No messages yet. Say hi!
                    </div>
                @endforelse
            </div>

            <!-- Mini Input Bar -->
            <div class="border-t border-gray-100 bg-white p-2.5 dark:border-gray-800 dark:bg-gray-900">
                <form wire:submit.prevent="sendMessage" class="flex items-center gap-1.5">
                    <input
                        type="text"
                        wire:model="messageText"
                        placeholder="Write a message..."
                        class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-1.5 text-xs text-gray-900 focus:border-amber-500 focus:bg-white focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"
                    />
                    <button
                        type="submit"
                        class="inline-flex h-8 w-8 items-center justify-center rounded-xl bg-amber-600 text-white shadow transition hover:bg-amber-500"
                    >
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                    </button>
                </form>
            </div>
        @else
            <!-- Conversation List View -->
            <div class="p-2 border-b border-gray-100 dark:border-gray-800">
                <input
                    type="text"
                    wire:model.live.debounce.250ms="search"
                    placeholder="Search chats..."
                    class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-1.5 text-xs text-gray-900 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"
                />
            </div>
            <div class="flex-1 overflow-y-auto divide-y divide-gray-100 dark:divide-gray-800">
                @php $conversations = $this->getConversations(); @endphp
                @forelse($conversations as $conv)
                    @php
                        $title = $conv->getDisplayName(auth()->id());
                        $avatar = $conv->getDisplayAvatar(auth()->id());
                        $unread = $conv->unreadCountFor(auth()->id());
                        $latest = $conv->latestMessage;
                    @endphp
                    <div
                        wire:key="float-conv-{{ $conv->id }}"
                        wire:click="selectConversation({{ $conv->id }})"
                        class="flex cursor-pointer items-center gap-2.5 p-2.5 transition hover:bg-amber-50/60 dark:hover:bg-gray-800/60"
                    >
                        @if($avatar)
                            <img src="{{ $avatar }}" class="h-9 w-9 rounded-full object-cover flex-shrink-0" />
                        @else
                            <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-full bg-gradient-to-tr from-amber-600 to-amber-400 text-xs font-bold text-white">
                                {{ strtoupper(substr($title, 0, 1)) }}
                            </div>
                        @endif
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center justify-between">
                                <span class="truncate text-xs font-semibold text-gray-900 dark:text-white">{{ $title }}</span>
                                @if($latest)
                                    <span class="text-[9px] text-gray-400">{{ $latest->created_at?->diffForHumans(null, true, true) }}</span>
                                @endif
                            </div>
                            <p class="truncate text-[11px] text-gray-500 dark:text-gray-400">
                                {{ $latest?->body ?? 'No messages yet' }}
                            </p>
                        </div>
                        @if($unread > 0)
                            <span class="flex h-4 min-w-4 items-center justify-center rounded-full bg-amber-500 px-1 text-[9px] font-bold text-white">
                                {{ $unread }}
                            </span>
                        @endif
                    </div>
                @empty
                    <div class="p-6 text-center text-xs text-gray-400">
                        No conversations yet. Open full chat to start one!
                    </div>
                @endforelse
            </div>
            <div class="border-t border-gray-100 p-2.5 text-center dark:border-gray-800 bg-gray-50/50 dark:bg-gray-900/50">
                <a
                    href="{{ url('/kamkaj/chat') }}"
                    class="inline-flex items-center gap-1 text-xs font-semibold text-amber-600 hover:underline dark:text-amber-400"
                >
                    Open Full Chat Page &rarr;
                </a>
            </div>
        @endif
    </div>

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
