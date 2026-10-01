<?php

namespace App\Livewire;

use App\Events\MessageSent;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;

class FloatingChatWidget extends Component
{
    public bool $isOpen = false;

    public ?int $activeConversationId = null;

    public string $messageText = '';

    public string $search = '';

    public function toggleWidget(): void
    {
        $this->isOpen = ! $this->isOpen;

        if ($this->isOpen && ! $this->activeConversationId) {
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

        $this->dispatch('floating-conversation-changed', conversationId: $id);
        $this->dispatch('scroll-floating-chat');
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
            'type' => 'text',
        ]);

        $conversation->update(['last_message_at' => now()]);
        $conversation->markAsReadFor(auth()->id());

        broadcast(new MessageSent($message))->toOthers();

        $this->messageText = '';
        $this->dispatch('scroll-floating-chat');
    }

    public function incomingMessage(array $payload): void
    {
        $convId = (int) ($payload['conversation_id'] ?? 0);

        if ($this->isOpen && $convId === $this->activeConversationId) {
            $conversation = Conversation::find($convId);
            $conversation?->markAsReadFor(auth()->id());
            $this->dispatch('scroll-floating-chat');
        }
    }

    public function getConversations()
    {
        $userId = auth()->id();

        if (! $userId) {
            return collect();
        }

        return Conversation::query()
            ->whereHas('participants', fn ($q) => $q->where('user_id', $userId))
            ->with(['users', 'latestMessage.sender', 'participants'])
            ->orderByDesc('last_message_at')
            ->get()
            ->filter(function ($conv) use ($userId) {
                if (blank($this->search)) {
                    return true;
                }

                $title = $conv->getDisplayName($userId);
                $query = strtolower($this->search);

                return str_contains(strtolower($title), $query);
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

    public function render()
    {
        return view('livewire.floating-chat-widget');
    }
}
