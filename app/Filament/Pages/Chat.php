<?php

namespace App\Filament\Pages;

use App\Events\MessageDeleted;
use App\Events\MessagePinned;
use App\Events\MessageSent;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Message;
use App\Models\User;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Url;
use Livewire\WithFileUploads;

class Chat extends Page
{
    use WithFileUploads;

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

    public string $messageSearch = '';

    public bool $showMessageSearch = false;

    public ?int $pinningMessageId = null;

    public ?int $pinningConversationId = null;

    public bool $showNewChatModal = false;

    public string $modalTab = 'direct'; // 'direct' or 'group'

    public string $groupTitle = '';

    /**
     * @var array<int>
     */
    public array $selectedGroupMembers = [];

    /**
     * @var mixed
     */
    public $attachment = null;

    /**
     * @var mixed
     */
    public $voiceNote = null;

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
        $this->attachment = null;
        $this->voiceNote = null;
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

    public function toggleGroupMember(int $userId): void
    {
        if (in_array($userId, $this->selectedGroupMembers, true)) {
            $this->selectedGroupMembers = array_values(array_diff($this->selectedGroupMembers, [$userId]));
        } else {
            $this->selectedGroupMembers[] = $userId;
        }
    }

    public function createGroupChat(): void
    {
        $title = trim($this->groupTitle);

        if (blank($title)) {
            Notification::make()
                ->warning()
                ->title('Please enter a group name')
                ->send();

            return;
        }

        if (empty($this->selectedGroupMembers)) {
            Notification::make()
                ->warning()
                ->title('Please select at least one member')
                ->send();

            return;
        }

        $conversation = Conversation::createGroup(
            creatorId: auth()->id(),
            title: $title,
            participantUserIds: $this->selectedGroupMembers,
        );

        $this->showNewChatModal = false;
        $this->groupTitle = '';
        $this->selectedGroupMembers = [];
        $this->modalTab = 'direct';

        Notification::make()
            ->success()
            ->title('Group created!')
            ->send();

        $this->selectConversation($conversation->id);
    }

    public function removeAttachment(): void
    {
        $this->attachment = null;
    }

    public function sendMessage(): void
    {
        $text = trim($this->messageText);

        if (blank($text) && ! $this->attachment && ! $this->voiceNote) {
            return;
        }

        if (! $this->activeConversationId) {
            return;
        }

        $conversation = Conversation::with('participants')->find($this->activeConversationId);
        if (! $conversation || ! $conversation->participants->contains('user_id', auth()->id())) {
            return;
        }

        $type = 'text';
        $attachmentPath = null;
        $attachmentName = null;
        $fileType = null;
        $fileSize = null;

        // Handle voice note
        if ($this->voiceNote) {
            $type = 'audio';
            $attachmentPath = $this->voiceNote->store('chat-voice', 'public');
            $attachmentName = 'Voice message.webm';
            $fileType = 'audio/webm';
            $fileSize = $this->voiceNote->getSize();
            if (blank($text)) {
                $text = 'Voice message';
            }
        } elseif ($this->attachment) {
            $mime = $this->attachment->getMimeType();
            $attachmentName = $this->attachment->getClientOriginalName();
            $fileType = $mime;
            $fileSize = $this->attachment->getSize();
            $attachmentPath = $this->attachment->store('chat-attachments', 'public');

            if (str_starts_with($mime, 'image/')) {
                $type = 'image';
            } elseif (str_starts_with($mime, 'audio/')) {
                $type = 'audio';
            } else {
                $type = 'file';
            }

            if (blank($text)) {
                $text = $attachmentName;
            }
        }

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'sender_id' => auth()->id(),
            'body' => $text,
            'type' => $type,
            'attachment_path' => $attachmentPath,
            'attachment_name' => $attachmentName,
            'file_type' => $fileType,
            'file_size' => $fileSize,
        ]);

        $conversation->update(['last_message_at' => now()]);
        $conversation->markAsReadFor(auth()->id());

        // Broadcast over WebSockets via Reverb
        broadcast(new MessageSent($message))->toOthers();

        $this->messageText = '';
        $this->attachment = null;
        $this->voiceNote = null;

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

    public function calculatePinnedUntil(string $duration): ?Carbon
    {
        return match ($duration) {
            '8_hours' => now()->addHours(8),
            '1_day' => now()->addDay(),
            '1_week' => now()->addWeek(),
            '1_month' => now()->addMonth(),
            '1_year' => now()->addYear(),
            default => null, // 'never_ending'
        };
    }

    public function deleteMessage(int $messageId): void
    {
        $message = Message::find($messageId);

        if (! $message || ! $message->canBeDeletedBy(auth()->id())) {
            Notification::make()
                ->danger()
                ->title('Cannot delete message')
                ->body('Messages can only be deleted within 15 minutes of sending.')
                ->send();

            return;
        }

        if ($message->attachment_path) {
            Storage::disk('public')->delete($message->attachment_path);
        }

        $message->update([
            'is_deleted' => true,
            'deleted_at' => now(),
            'body' => 'This message was deleted',
            'attachment_path' => null,
            'attachment_name' => null,
            'file_type' => null,
            'file_size' => null,
            'type' => 'text',
        ]);

        broadcast(new MessageDeleted($message))->toOthers();

        Notification::make()
            ->success()
            ->title('Message deleted')
            ->send();
    }

    public function openPinMessageModal(int $messageId): void
    {
        $this->pinningMessageId = $messageId;
    }

    public function closePinMessageModal(): void
    {
        $this->pinningMessageId = null;
    }

    public function pinMessage(int $messageId, string $duration = 'never_ending'): void
    {
        $message = Message::with('conversation.participants')->find($messageId);

        if (! $message || ! $message->conversation?->participants->contains('user_id', auth()->id())) {
            return;
        }

        $pinnedUntil = $this->calculatePinnedUntil($duration);

        $message->update([
            'is_pinned' => true,
            'pinned_at' => now(),
            'pinned_until' => $pinnedUntil,
            'pinned_by' => auth()->id(),
        ]);

        $this->pinningMessageId = null;

        broadcast(new MessagePinned($message))->toOthers();

        Notification::make()
            ->success()
            ->title('Message pinned successfully')
            ->send();
    }

    public function unpinMessage(int $messageId): void
    {
        $message = Message::with('conversation.participants')->find($messageId);

        if (! $message || ! $message->conversation?->participants->contains('user_id', auth()->id())) {
            return;
        }

        $message->update([
            'is_pinned' => false,
            'pinned_at' => null,
            'pinned_until' => null,
            'pinned_by' => null,
        ]);

        broadcast(new MessagePinned($message))->toOthers();

        Notification::make()
            ->info()
            ->title('Message unpinned')
            ->send();
    }

    public function openPinConversationModal(int $conversationId): void
    {
        $this->pinningConversationId = $conversationId;
    }

    public function closePinConversationModal(): void
    {
        $this->pinningConversationId = null;
    }

    public function pinConversation(int $conversationId, string $duration = 'never_ending'): void
    {
        $participant = ConversationParticipant::where('conversation_id', $conversationId)
            ->where('user_id', auth()->id())
            ->first();

        if (! $participant) {
            return;
        }

        $pinnedUntil = $this->calculatePinnedUntil($duration);

        $participant->update([
            'is_pinned' => true,
            'pinned_until' => $pinnedUntil,
        ]);

        $this->pinningConversationId = null;

        Notification::make()
            ->success()
            ->title('Chat pinned to top')
            ->send();
    }

    public function unpinConversation(int $conversationId): void
    {
        $participant = ConversationParticipant::where('conversation_id', $conversationId)
            ->where('user_id', auth()->id())
            ->first();

        if (! $participant) {
            return;
        }

        $participant->update([
            'is_pinned' => false,
            'pinned_until' => null,
        ]);

        $this->pinningConversationId = null;

        Notification::make()
            ->info()
            ->title('Chat unpinned')
            ->send();
    }

    public function toggleMessageSearch(): void
    {
        $this->showMessageSearch = ! $this->showMessageSearch;
        if (! $this->showMessageSearch) {
            $this->messageSearch = '';
        }
    }

    public function clearMessageSearch(): void
    {
        $this->messageSearch = '';
    }

    public function getConversations()
    {
        $userId = auth()->id();

        if (! $userId) {
            return collect();
        }

        $conversations = Conversation::query()
            ->whereHas('participants', fn ($q) => $q->where('user_id', $userId))
            ->with(['users', 'latestMessage.sender', 'participants'])
            ->orderByDesc('last_message_at')
            ->get();

        if (filled($this->search)) {
            $term = strtolower($this->search);
            $conversations = $conversations->filter(function ($conv) use ($userId, $term) {
                $title = strtolower($conv->getDisplayName($userId));
                if (str_contains($title, $term)) {
                    return true;
                }

                if ($conv->latestMessage && str_contains(strtolower($conv->latestMessage->body ?? ''), $term)) {
                    return true;
                }

                return $conv->messages()->whereRaw('LOWER(body) LIKE ?', ['%'.$term.'%'])->exists();
            });
        }

        // Sort pinned conversations to the very top
        return $conversations->sortByDesc(fn ($conv) => $conv->isPinnedFor($userId))->values();
    }

    public function getActiveConversationProperty(): ?Conversation
    {
        if (! $this->activeConversationId) {
            return null;
        }

        return Conversation::with(['users', 'participants'])->find($this->activeConversationId);
    }

    public function getPinnedMessagesProperty(): Collection
    {
        if (! $this->activeConversationId) {
            return new Collection;
        }

        return Message::where('conversation_id', $this->activeConversationId)
            ->where('is_pinned', true)
            ->where(function ($q) {
                $q->whereNull('pinned_until')
                    ->orWhere('pinned_until', '>', now());
            })
            ->with(['sender', 'pinnedBy'])
            ->orderByDesc('pinned_at')
            ->get();
    }

    public function getMessagesProperty(): Collection
    {
        if (! $this->activeConversationId) {
            return new Collection;
        }

        $query = Message::where('conversation_id', $this->activeConversationId)
            ->with(['sender', 'pinnedBy'])
            ->orderBy('created_at', 'asc');

        if (filled($this->messageSearch)) {
            $term = '%'.strtolower($this->messageSearch).'%';
            $query->whereRaw('LOWER(body) LIKE ?', [$term]);
        }

        return $query->get();
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

        return $query->orderBy('name')->limit(30)->get();
    }
}
