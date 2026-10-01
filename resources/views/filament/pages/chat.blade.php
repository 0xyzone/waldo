<x-filament-panels::page class="chat-page-container">
    <div 
        x-data="{
            activeConversationId: @entangle('activeConversationId'),
            onlineUserIds: [],
            isAtBottom: true,
            
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
                if (!window.Echo) {
                    return;
                }

                // Presence channel to track who is currently online
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

                // Personal private channel to receive real-time notifications from any conversation
                window.Echo.private(`chat.user.{{ auth()->id() }}`)
                    .listen('.message.sent', (e) => {
                        if (this.activeConversationId !== e.conversation_id) {
                            $wire.$refresh();
                        }
                    });

                // Subscribe to currently active conversation if set
                if (this.activeConversationId) {
                    this.subscribeToConversation(this.activeConversationId);
                }
            },

            subscribeToConversation(conversationId) {
                if (!window.Echo || !conversationId) return;

                // Stop listening to previous conversation channels
                window.Echo.private(`chat.conversation.${conversationId}`)
                    .stopListening('.message.sent')
                    .listen('.message.sent', (payload) => {
                        $wire.incomingMessage(payload);
                        $wire.$refresh();
                        this.$nextTick(() => this.scrollToBottom());
                    });
            },

            scrollToBottom() {
                const container = this.$refs.messagesFeed;
                if (container) {
                    container.scrollTop = container.scrollHeight;
                }
            },

            handleKeyDown(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    if ($wire.messageText.trim().length > 0) {
                        $wire.sendMessage();
                    }
                }
            },

            isUserOnline(userId) {
                return this.onlineUserIds.includes(userId);
            }
        }"
        class="flex h-[calc(100vh-13.5rem)] min-h-[520px] overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-xl dark:border-gray-800 dark:bg-gray-900"
    >
        <!-- Left: Conversations Sidebar -->
        <div class="flex w-full md:w-80 lg:w-96 flex-col border-r border-gray-200 dark:border-gray-800 {{ $activeConversationId ? 'hidden md:flex' : 'flex' }}">
            <!-- Sidebar Header -->
            <div class="flex items-center justify-between border-b border-gray-100 p-4 dark:border-gray-800">
                <div class="flex items-center gap-2">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">Messages</h2>
                    @php $unreadTotal = auth()->user()?->unreadMessagesCount() ?? 0; @endphp
                    @if($unreadTotal > 0)
                        <span class="inline-flex items-center rounded-full bg-amber-500 px-2 py-0.5 text-xs font-semibold text-white">
                            {{ $unreadTotal }}
                        </span>
                    @endif
                </div>
                <button
                    type="button"
                    wire:click="$set('showNewChatModal', true)"
                    class="inline-flex items-center gap-1.5 rounded-xl bg-amber-600 px-3 py-1.5 text-xs font-medium text-white shadow-sm transition hover:bg-amber-500 active:scale-95"
                    title="Start new chat"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>New Chat</span>
                </button>
            </div>

            <!-- Search Bar -->
            <div class="p-3 border-b border-gray-100 dark:border-gray-800/80 bg-gray-50/50 dark:bg-gray-900/50">
                <div class="relative">
                    <input
                        type="text"
                        wire:model.live.debounce.250ms="search"
                        placeholder="Search conversations..."
                        class="w-full rounded-xl border border-gray-200 bg-white py-2 pl-9 pr-3 text-xs text-gray-900 placeholder-gray-400 focus:border-amber-500 focus:outline-none focus:ring-1 focus:ring-amber-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100 dark:placeholder-gray-500"
                    />
                    <svg class="absolute left-3 top-2.5 h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
            </div>

            <!-- Conversation List -->
            <div class="flex-1 overflow-y-auto divide-y divide-gray-100 dark:divide-gray-800/60">
                @php $conversations = $this->getConversations(); @endphp
                @forelse($conversations as $conv)
                    @php
                        $recipient = $conv->getRecipientUser(auth()->id());
                        $isActive = $activeConversationId === $conv->id;
                        $unreadCount = $conv->unreadCountFor(auth()->id());
                        $latest = $conv->latestMessage;
                    @endphp
                    <div
                        wire:key="conv-{{ $conv->id }}"
                        wire:click="selectConversation({{ $conv->id }})"
                        class="group relative flex cursor-pointer items-center gap-3 p-3.5 transition-all hover:bg-amber-50/60 dark:hover:bg-gray-800/60 {{ $isActive ? 'bg-amber-500/10 dark:bg-amber-500/15 border-l-4 border-amber-500 pl-2.5' : '' }}"
                    >
                        <!-- Avatar with presence indicator -->
                        <div class="relative flex-shrink-0">
                            <div class="flex h-11 w-11 items-center justify-center rounded-full bg-gradient-to-tr from-amber-600 to-amber-400 text-sm font-bold text-white shadow-sm">
                                {{ strtoupper(substr($recipient?->name ?? 'U', 0, 1)) }}
                            </div>
                            <!-- Online status dot -->
                            <span 
                                x-show="isUserOnline({{ $recipient?->id ?? 0 }})"
                                class="absolute bottom-0 right-0 h-3 w-3 rounded-full border-2 border-white bg-emerald-500 dark:border-gray-900"
                                title="Online"
                            ></span>
                        </div>

                        <!-- Details -->
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center justify-between gap-1">
                                <span class="truncate text-xs font-semibold text-gray-900 dark:text-white">
                                    {{ $recipient?->name ?? 'User' }}
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
                                        @if($latest->sender_id === auth()->id())
                                            <span class="text-amber-600 dark:text-amber-400">You: </span>
                                        @endif
                                        {{ $latest->body }}
                                    @else
                                        <span class="italic text-gray-400">Started a chat</span>
                                    @endif
                                </p>
                                @if($unreadCount > 0)
                                    <span class="ml-1.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-amber-500 px-1 text-[10px] font-bold text-white">
                                        {{ $unreadCount }}
                                    </span>
                                @endif
                            </div>
                            @if($recipient?->username)
                                <span class="text-[10px] text-gray-400">@<span>{{ $recipient->username }}</span></span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="p-8 text-center text-gray-400">
                        <svg class="mx-auto h-10 w-10 text-gray-300 dark:text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                        <p class="mt-2 text-xs">No conversations yet</p>
                        <button
                            type="button"
                            wire:click="$set('showNewChatModal', true)"
                            class="mt-3 inline-flex items-center gap-1 text-xs font-semibold text-amber-600 hover:underline dark:text-amber-400"
                        >
                            Start a chat with a user
                        </button>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Right: Active Chat Window -->
        <div class="flex flex-1 flex-col bg-gray-50/40 dark:bg-gray-950/40 {{ $activeConversationId ? 'flex' : 'hidden md:flex' }}">
            @if($this->activeConversation)
                @php
                    $activeRecipient = $this->activeConversation->getRecipientUser(auth()->id());
                @endphp
                <!-- Chat Header -->
                <div class="flex items-center justify-between border-b border-gray-200 bg-white/80 p-3.5 backdrop-blur dark:border-gray-800 dark:bg-gray-900/80">
                    <div class="flex items-center gap-3">
                        <!-- Back button for mobile -->
                        <button
                            type="button"
                            wire:click="$set('activeConversationId', null)"
                            class="rounded-lg p-1.5 text-gray-500 hover:bg-gray-100 md:hidden dark:text-gray-400 dark:hover:bg-gray-800"
                        >
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                            </svg>
                        </button>

                        <div class="relative flex-shrink-0">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-tr from-amber-600 to-amber-400 text-sm font-bold text-white shadow-sm">
                                {{ strtoupper(substr($activeRecipient?->name ?? 'U', 0, 1)) }}
                            </div>
                            <span 
                                x-show="isUserOnline({{ $activeRecipient?->id ?? 0 }})"
                                class="absolute bottom-0 right-0 h-2.5 w-2.5 rounded-full border-2 border-white bg-emerald-500 dark:border-gray-900"
                            ></span>
                        </div>

                        <div>
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
                                {{ $activeRecipient?->name ?? 'User' }}
                            </h3>
                            <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                                @if($activeRecipient?->username)
                                    <span>@<span>{{ $activeRecipient->username }}</span></span>
                                    <span>•</span>
                                @endif
                                <span x-show="isUserOnline({{ $activeRecipient?->id ?? 0 }})" class="text-emerald-600 dark:text-emerald-400 font-medium">Online</span>
                                <span x-show="!isUserOnline({{ $activeRecipient?->id ?? 0 }})">Offline</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Messages Feed -->
                <div
                    x-ref="messagesFeed"
                    class="flex-1 overflow-y-auto p-4 space-y-3"
                >
                    @php $messages = $this->messages; @endphp
                    @forelse($messages as $msg)
                        @php $isMe = $msg->sender_id === auth()->id(); @endphp
                        <div
                            wire:key="msg-{{ $msg->id }}"
                            class="flex flex-col {{ $isMe ? 'items-end' : 'items-start' }}"
                        >
                            <div class="flex items-end gap-2 max-w-[85%] sm:max-w-[70%] {{ $isMe ? 'flex-row-reverse' : 'flex-row' }}">
                                @if(!$isMe)
                                    <div class="flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-full bg-gray-200 text-xs font-bold text-gray-600 dark:bg-gray-700 dark:text-gray-200">
                                        {{ strtoupper(substr($msg->sender?->name ?? 'U', 0, 1)) }}
                                    </div>
                                @endif
                                <div class="group relative rounded-2xl px-4 py-2.5 text-xs shadow-sm {{ $isMe ? 'bg-amber-600 text-white rounded-br-sm' : 'bg-white text-gray-900 border border-gray-200 dark:border-gray-700/80 dark:bg-gray-800 dark:text-gray-100 rounded-bl-sm' }}">
                                    <p class="whitespace-pre-wrap break-words leading-relaxed">{{ $msg->body }}</p>
                                    <div class="mt-1 flex items-center justify-end gap-1 text-[10px] {{ $isMe ? 'text-amber-100/80' : 'text-gray-400' }}">
                                        <span>{{ $msg->created_at?->format('h:i A') }}</span>
                                        @if($isMe)
                                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="flex h-full items-center justify-center text-center">
                            <div class="rounded-2xl border border-dashed border-gray-300 p-6 dark:border-gray-700 text-gray-400 max-w-sm">
                                <p class="text-xs">No messages yet. Send a friendly hello to begin the conversation!</p>
                            </div>
                        </div>
                    @endforelse
                </div>

                <!-- Input Footer -->
                <div class="border-t border-gray-200 bg-white p-3 dark:border-gray-800 dark:bg-gray-900">
                    <form wire:submit.prevent="sendMessage" class="flex items-center gap-2">
                        <div class="relative flex-1">
                            <input
                                type="text"
                                wire:model="messageText"
                                @keydown="handleKeyDown"
                                placeholder="Write a message... (Press Enter to send)"
                                autocomplete="off"
                                autofocus
                                class="w-full rounded-xl border border-gray-200 bg-gray-50/70 px-4 py-2.5 text-xs text-gray-900 placeholder-gray-400 focus:border-amber-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-amber-500 dark:border-gray-700 dark:bg-gray-800/70 dark:text-gray-100 dark:placeholder-gray-500 dark:focus:bg-gray-800"
                            />
                        </div>
                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-amber-600 text-white shadow-md transition hover:bg-amber-500 active:scale-95 disabled:opacity-50"
                        >
                            <svg wire:loading.remove wire:target="sendMessage" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                            </svg>
                            <svg wire:loading wire:target="sendMessage" class="h-4 w-4 animate-spin text-white" fill="none" viewBox="0 0 24 24">
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
                    <p class="mt-1 max-w-xs text-xs text-gray-500 dark:text-gray-400">
                        Select a conversation from the left or connect with any registered colleague.
                    </p>
                    <button
                        type="button"
                        wire:click="$set('showNewChatModal', true)"
                        class="mt-4 inline-flex items-center gap-1.5 rounded-xl bg-amber-600 px-4 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-amber-500 active:scale-95"
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

    <!-- New Chat Modal -->
    @if($showNewChatModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 p-4 backdrop-blur-sm">
            <div 
                @click.outside="$wire.set('showNewChatModal', false)"
                class="relative w-full max-w-md overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-2xl dark:border-gray-800 dark:bg-gray-900"
            >
                <div class="flex items-center justify-between border-b border-gray-100 p-4 dark:border-gray-800">
                    <h3 class="text-sm font-bold text-gray-900 dark:text-white">Start New Chat</h3>
                    <button 
                        type="button"
                        wire:click="$set('showNewChatModal', false)"
                        class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-800"
                    >
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <div class="p-3 border-b border-gray-100 dark:border-gray-800">
                    <input
                        type="text"
                        wire:model.live.debounce.250ms="userSearch"
                        placeholder="Search by name, username, or email..."
                        autofocus
                        class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2 text-xs text-gray-900 placeholder-gray-400 focus:border-amber-500 focus:bg-white focus:outline-none focus:ring-1 focus:ring-amber-500 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-100"
                    />
                </div>

                <div class="max-h-72 overflow-y-auto divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($this->availableUsers as $user)
                        <div
                            wire:key="user-{{ $user->id }}"
                            wire:click="startConversationWith({{ $user->id }})"
                            class="flex cursor-pointer items-center justify-between p-3 transition hover:bg-amber-50/70 dark:hover:bg-gray-800/70"
                        >
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-full bg-gradient-to-tr from-amber-600 to-amber-400 text-xs font-bold text-white shadow-sm">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="truncate text-xs font-semibold text-gray-900 dark:text-white">{{ $user->name }}</p>
                                    <div class="flex items-center gap-2 text-[10px] text-gray-500 dark:text-gray-400">
                                        @if($user->username)
                                            <span class="font-medium text-amber-600 dark:text-amber-400">@<span>{{ $user->username }}</span></span>
                                        @endif
                                        <span class="truncate">{{ $user->email }}</span>
                                    </div>
                                </div>
                            </div>
                            <span class="flex-shrink-0 text-xs text-amber-600 font-semibold dark:text-amber-400">Chat &rarr;</span>
                        </div>
                    @empty
                        <div class="p-6 text-center text-xs text-gray-400">
                            No registered users found matching your search.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    @endif
</x-filament-panels::page>
