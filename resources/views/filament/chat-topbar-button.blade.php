<div
    x-data="{
        unreadCount: {{ auth()->user()?->unreadMessagesCount() ?? 0 }},
        init() {
            if (window.Echo) {
                window.Echo.private('chat.user.{{ auth()->id() }}')
                    .listen('.chat.message.sent', (e) => {
                        if (e.message && e.message.sender_id !== {{ auth()->id() }}) {
                            this.unreadCount++;
                        }
                    });
            }
            window.addEventListener('chat-unread-updated', (e) => {
                if (typeof e.detail?.count !== 'undefined') {
                    this.unreadCount = e.detail.count;
                }
            });
        }
    }"
    class="relative inline-flex items-center"
>
    <button
        type="button"
        @click="window.dispatchEvent(new CustomEvent('toggle-floating-chat'))"
        class="relative inline-flex items-center justify-center w-9 h-9 rounded-lg text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-100 hover:bg-slate-100 dark:hover:bg-slate-800/60 transition-colors focus:outline-hidden cursor-pointer"
        title="Open Chat (Slide-over)"
    >
        <!-- Chat Bubble Icon -->
        <svg class="w-5 h-5 text-slate-500 dark:text-slate-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a.75.75 0 0 1-.84-.84c.148-.795.34-1.58.57-2.34A8.096 8.096 0 0 1 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z" />
        </svg>

        <!-- Unread Badge -->
        <template x-if="unreadCount > 0">
            <span
                class="absolute -top-1 -right-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-amber-500 px-1 text-[10px] font-extrabold text-white shadow-xs animate-pulse"
                x-text="unreadCount"
            ></span>
        </template>
    </button>
</div>
