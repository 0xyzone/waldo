<?php

namespace App\Filament\Pages;

use App\Events\MessageSent;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Url;

class Chat extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static string|BackedEnum|null $activeNavigationIcon = Heroicon::ChatBubbleLeftRight;

    protected static ?string $navigationLabel = 'Chat';

    protected static ?string $title = 'Messages';

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.pages.chat';

    #[Url(as: 'c')]
    public ?int $activeConversationId = null;

    public string $messageText = '';

    public string $search = '';

    public string $userSearch = '';

    public bool $showNewChatModal = false;

    public static function getNavigationBadge(): ?string
    {
        $count = auth()->user()?->unreadMessagesCount() ?? 0;

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'primary';
    }

    public function mount(?int $c = null): void
    {
        if ($c) {
            $this->selectConversation($c);
        } else {
            $first = $this->getConversations()->first();
            if ($first) {
                $this->selectConversation($first->id);
            }
        }
    }

    public function selectConversation(int $id): void
    {
        $conversation = Conversation::with(['users', 'participants'])->find($id);

        if (! $conversation || ! $conversation->users->contains('id', auth()->id())) {
            return;
        }

        $this->activeConversationId = $id;
        $conversation->markAsReadFor(auth()->id());

        $this->dispatch('conversation-changed', conversationId: $id);
        $this->dispatch('scroll-to-bottom');
    }

    public function startConversationWith(int $userId): void
    {
        if ($userId === auth()->id()) {
            Notification::make()
                ->warning()
                ->title('Cannot chat with yourself')
                ->send();

            return;
        }

        $targetUser = User::find($userId);
        if (! $targetUser) {
            return;
        }

        $conversation = Conversation::findOrCreateDirect(auth()->id(), $userId);
        $this->showNewChatModal = false;
        $this->userSearch = '';
        $this->selectConversation($conversation->id);
    }

    public function sendMessage(): void
    {
        $text = trim($this->messageText);

        if (blank($text) || ! $this->activeConversationId) {
            return;
        }

        $conversation = Conversation::with('participants')->find($this->activeConversationId);
        if (! $conversation || ! $conversation->participants->contains('user_id', auth()->id())) {
            return;
        }

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => auth()->id(),
            'body' => $text,
        ]);

        $conversation->update(['last_message_at' => now()]);
        $conversation->markAsReadFor(auth()->id());

        // Broadcast to others over WebSockets via Reverb
        broadcast(new MessageSent($message))->toOthers();

        $this->messageText = '';

        $this->dispatch('message-sent', messageId: $message->id);
        $this->dispatch('scroll-to-bottom');
    }

    public function incomingMessage(array $payload): void
    {
        $convId = (int) ($payload['conversation_id'] ?? 0);

        if ($convId === $this->activeConversationId) {
            $conversation = Conversation::find($convId);
            $conversation?->markAsReadFor(auth()->id());
            $this->dispatch('scroll-to-bottom');
        }
    }

    public function getConversations()
    {
        $userId = auth()->id();

        return Conversation::query()
            ->whereHas('participants', fn ($q) => $q->where('user_id', $userId))
            ->with(['users', 'latestMessage.sender', 'participants'])
            ->orderByDesc('last_message_at')
            ->get()
            ->filter(function ($conv) use ($userId) {
                if (blank($this->search)) {
                    return true;
                }

                $recipient = $conv->getRecipientUser($userId);
                $query = strtolower($this->search);

                return str_contains(strtolower($recipient?->name ?? ''), $query)
                    || str_contains(strtolower($recipient?->username ?? ''), $query);
            });
    }

    public function getActiveConversationProperty(): ?Conversation
    {
        if (! $this->activeConversationId) {
            return null;
        }

        return Conversation::with(['users', 'participants'])->find($this->activeConversationId);
    }

    public function getMessagesProperty(): Collection
    {
        if (! $this->activeConversationId) {
            return new Collection;
        }

        return Message::where('conversation_id', $this->activeConversationId)
            ->with('sender')
            ->orderBy('created_at', 'asc')
            ->get();
    }

    public function getAvailableUsersProperty()
    {
        $query = User::query()
            ->where('id', '!=', auth()->id());

        if (filled($this->userSearch)) {
            $term = '%'.strtolower($this->userSearch).'%';
            $query->where(function ($q) use ($term) {
                $q->whereRaw('LOWER(name) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(username) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(email) LIKE ?', [$term])
                    ->orWhere('phone', 'LIKE', $term);
            });
        }

        return $query->orderBy('name')->limit(20)->get();
    }
}
