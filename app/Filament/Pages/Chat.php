<?php

namespace App\Filament\Pages;

use App\Events\MessageDeleted;
use App\Events\MessagePinned;
use App\Events\MessageReacted;
use App\Events\MessageSent;
use App\Events\MessageStatusUpdated;
use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\Message;
use App\Models\MessageReaction;
use App\Models\User;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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

    public function getHeading(): string
    {
        return '';
    }

    public function getBreadcrumbs(): array
    {
        return [];
    }

    #[Url(as: 'c')]
    public ?int $activeConversationId = null;

    public string $messageText = '';

    public string $search = '';

    public string $userSearch = '';

    public string $messageSearch = '';

    public bool $showMessageSearch = false;

    public ?int $pinningMessageId = null;

    public ?int $pinningConversationId = null;

    public bool $showPinnedMessagesModal = false;

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

    public bool $showGroupSettingsModal = false;

    public string $groupSettingsTab = 'general'; // 'general', 'members', 'permissions'

    public string $settingsGroupTitle = '';

    public string $settingsGroupDescription = '';

    /**
     * @var mixed
     */
    public $settingsGroupAvatar = null;

    public bool $settingsOnlyAdminsCanMessage = false;

    public bool $settingsOnlyAdminsCanEditInfo = true;

    public bool $showAddMembersModal = false;

    public string $memberSearch = '';

    /**
     * @var array<int>
     */
    public array $newMemberIds = [];

    public ?int $transferOwnershipUserId = null;

    public bool $showTransferOwnershipModal = false;

    public ?int $replyingToMessageId = null;

    public ?int $forwardingMessageId = null;

    public bool $showForwardModal = false;

    public string $forwardSearch = '';

    /**
     * @var array<int>
     */
    public array $selectedForwardConversationIds = [];

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
        $this->markPendingConversationsDelivered();

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
        $this->replyingToMessageId = null;
        $conversation->markAsReadFor(auth()->id());
        $this->safeBroadcast(new MessageStatusUpdated($id, auth()->id(), 'seen'));

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

        if (! $conversation->canUserSendMessage(auth()->id())) {
            Notification::make()
                ->danger()
                ->title('Only admins can send messages in this group.')
                ->send();

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
            $actualMime = $this->voiceNote->getMimeType() ?? 'audio/webm';
            $ext = str_contains($actualMime, 'ogg') ? 'ogg' : (str_contains($actualMime, 'mp4') ? 'mp4' : 'webm');
            $attachmentName = 'Voice message.'.$ext;
            $fileType = $actualMime;
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
            'reply_to_id' => $this->replyingToMessageId,
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

        $mentionedUserIds = [];
        if ($conversation->isGroup()) {
            $mentionedUserIds = $message->parseMentionedUserIds();
            foreach ($mentionedUserIds as $uId) {
                $targetUser = User::find($uId);
                if ($targetUser) {
                    Notification::make()
                        ->title("Mentioned in {$conversation->title}")
                        ->body(auth()->user()->name.': '.Str::limit($text, 80))
                        ->icon('heroicon-o-at-symbol')
                        ->iconColor('warning')
                        ->actions([
                            Action::make('view')
                                ->label('Open Chat')
                                ->url(url('/kamkaj/chat?c='.$conversation->id))
                                ->markAsRead(),
                        ])
                        ->sendToDatabase($targetUser);
                }
            }
        }

        // Broadcast over WebSockets via Reverb
        $this->safeBroadcast(new MessageSent($message, $mentionedUserIds));

        $this->messageText = '';
        $this->attachment = null;
        $this->voiceNote = null;
        $this->replyingToMessageId = null;

        $this->dispatch('message-sent', messageId: $message->id);
        $this->dispatch('scroll-to-bottom');
    }

    public function incomingMessage(array $payload): void
    {
        $convId = (int) ($payload['conversation_id'] ?? 0);

        if ($convId === $this->activeConversationId) {
            $conversation = Conversation::find($convId);
            $conversation?->markAsReadFor(auth()->id());
            $this->safeBroadcast(new MessageStatusUpdated($convId, auth()->id(), 'seen'));
            $this->dispatch('scroll-to-bottom');
        } else {
            $conversation = Conversation::find($convId);
            $conversation?->markAsDeliveredFor(auth()->id());
            $this->safeBroadcast(new MessageStatusUpdated($convId, auth()->id(), 'delivered'));
        }
    }

    public function markConversationDelivered(int $convId): void
    {
        $userId = auth()->id();
        if (! $userId) {
            return;
        }

        $conversation = Conversation::find($convId);
        if ($conversation) {
            $conversation->markAsDeliveredFor($userId);
            $this->safeBroadcast(new MessageStatusUpdated($convId, $userId, 'delivered'));
        }
    }

    public function markPendingConversationsDelivered(): void
    {
        $userId = auth()->id();
        if (! $userId) {
            return;
        }

        $conversations = Conversation::whereHas('participants', fn ($q) => $q->where('user_id', $userId))->get();
        foreach ($conversations as $conv) {
            $conv->markAsDeliveredFor($userId);
            $this->safeBroadcast(new MessageStatusUpdated($conv->id, $userId, 'delivered'));
        }
    }

    public function toggleReadReceipts(): void
    {
        $user = auth()->user();
        if (! $user) {
            return;
        }

        $newState = ! (bool) ($user->read_receipts_enabled ?? true);
        $user->update(['read_receipts_enabled' => $newState]);

        Notification::make()
            ->success()
            ->title($newState ? 'Read receipts enabled' : 'Read receipts disabled')
            ->body($newState ? 'Others can now see your seen status (blue ticks), and you can see theirs.' : "You won't send seen status, and you won't see other users' seen status.")
            ->send();
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

        $this->safeBroadcast(new MessageDeleted($message));

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

        $this->safeBroadcast(new MessagePinned($message));

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

        $this->safeBroadcast(new MessagePinned($message));

        Notification::make()
            ->info()
            ->title('Message unpinned')
            ->send();
    }

    public function openPinnedMessagesModal(): void
    {
        $this->showPinnedMessagesModal = true;
    }

    public function closePinnedMessagesModal(): void
    {
        $this->showPinnedMessagesModal = false;
    }

    public function jumpToPinnedMessage(int $messageId): void
    {
        $this->showPinnedMessagesModal = false;
        $this->dispatch('scroll-to-message', messageId: $messageId);
    }

    public function toggleReaction(int $messageId, string $emoji): void
    {
        $message = Message::with('conversation.participants')->find($messageId);
        if (! $message || ! $message->conversation?->participants->contains('user_id', auth()->id())) {
            return;
        }

        $userId = auth()->id();
        $userReactions = MessageReaction::where('message_id', $messageId)
            ->where('user_id', $userId)
            ->get();

        $sameReaction = $userReactions->firstWhere('reaction', $emoji);

        if ($sameReaction) {
            $userReactions->each->delete();
        } else {
            if ($userReactions->isNotEmpty()) {
                $first = $userReactions->first();
                $first->update(['reaction' => $emoji]);
                $userReactions->slice(1)->each->delete();
            } else {
                MessageReaction::create([
                    'message_id' => $messageId,
                    'user_id' => $userId,
                    'reaction' => $emoji,
                ]);
            }
        }

        $this->safeBroadcast(new MessageReacted($message, $userId, $emoji));
    }

    protected function safeBroadcast(mixed $event): void
    {
        try {
            $socketId = request()->header('X-Socket-ID');
            if ($socketId !== null && preg_match('/^\d+\.\d+$/', (string) $socketId)) {
                broadcast($event)->toOthers();
            } else {
                broadcast($event);
            }
        } catch (\Throwable $e) {
            report($e);
        }
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
            ->with(['sender', 'pinnedBy', 'reactions.user', 'replyTo.sender', 'conversation.participants.user'])
            ->orderBy('created_at', 'asc');

        if (filled($this->messageSearch)) {
            $term = '%'.strtolower($this->messageSearch).'%';
            $query->whereRaw('LOWER(body) LIKE ?', [$term]);
        }

        return $query->get();
    }

    public function setReply(int $messageId): void
    {
        $message = Message::find($messageId);
        if (! $message || $message->conversation_id !== $this->activeConversationId || $message->is_deleted) {
            return;
        }

        $this->replyingToMessageId = $messageId;
        $this->dispatch('focus-message-input');
    }

    public function cancelReply(): void
    {
        $this->replyingToMessageId = null;
    }

    public function getReplyingToMessageProperty(): ?Message
    {
        if (! $this->replyingToMessageId) {
            return null;
        }

        return Message::with('sender')->find($this->replyingToMessageId);
    }

    public function openForwardModal(int $messageId): void
    {
        $message = Message::find($messageId);
        if (! $message || $message->is_deleted) {
            return;
        }

        $this->forwardingMessageId = $messageId;
        $this->selectedForwardConversationIds = [];
        $this->forwardSearch = '';
        $this->showForwardModal = true;
    }

    public function closeForwardModal(): void
    {
        $this->showForwardModal = false;
        $this->forwardingMessageId = null;
        $this->selectedForwardConversationIds = [];
        $this->forwardSearch = '';
    }

    public function toggleForwardConversation(int $conversationId): void
    {
        if (in_array($conversationId, $this->selectedForwardConversationIds, true)) {
            $this->selectedForwardConversationIds = array_values(array_diff($this->selectedForwardConversationIds, [$conversationId]));
        } else {
            $this->selectedForwardConversationIds[] = $conversationId;
        }
    }

    public function forwardMessage(): void
    {
        if (! $this->forwardingMessageId || empty($this->selectedForwardConversationIds)) {
            Notification::make()
                ->warning()
                ->title('Please select at least one chat to forward to.')
                ->send();

            return;
        }

        $sourceMessage = Message::with('sender')->find($this->forwardingMessageId);
        if (! $sourceMessage || $sourceMessage->is_deleted) {
            Notification::make()
                ->danger()
                ->title('Message is no longer available.')
                ->send();
            $this->closeForwardModal();

            return;
        }

        $currentUserId = auth()->id();
        $forwardedCount = 0;

        foreach ($this->selectedForwardConversationIds as $convId) {
            $targetConv = Conversation::with('participants')->find($convId);
            if (! $targetConv || ! $targetConv->participants->contains('user_id', $currentUserId)) {
                continue;
            }

            if (! $targetConv->canUserSendMessage($currentUserId)) {
                continue;
            }

            $newMsg = Message::create([
                'conversation_id' => $targetConv->id,
                'sender_id' => $currentUserId,
                'body' => $sourceMessage->body,
                'type' => $sourceMessage->type,
                'is_forwarded' => true,
                'attachment_path' => $sourceMessage->attachment_path,
                'attachment_name' => $sourceMessage->attachment_name,
                'file_type' => $sourceMessage->file_type,
                'file_size' => $sourceMessage->file_size,
            ]);

            $targetConv->update(['last_message_at' => now()]);
            $targetConv->markAsReadFor($currentUserId);

            $this->safeBroadcast(new MessageSent($newMsg));
            $forwardedCount++;
        }

        $this->closeForwardModal();

        Notification::make()
            ->success()
            ->title("Message forwarded to {$forwardedCount} ".Str::plural('chat', $forwardedCount))
            ->send();

        $this->dispatch('scroll-to-bottom');
    }

    public function getForwardingMessageProperty(): ?Message
    {
        if (! $this->forwardingMessageId) {
            return null;
        }

        return Message::with('sender')->find($this->forwardingMessageId);
    }

    public function getForwardableConversationsProperty()
    {
        $userId = auth()->id();
        if (! $userId) {
            return collect();
        }

        $conversations = Conversation::query()
            ->whereHas('participants', fn ($q) => $q->where('user_id', $userId))
            ->with(['users', 'latestMessage'])
            ->orderByDesc('last_message_at')
            ->get();

        if (filled($this->forwardSearch)) {
            $term = strtolower($this->forwardSearch);
            $conversations = $conversations->filter(function ($conv) use ($userId, $term) {
                return str_contains(strtolower($conv->getDisplayName($userId)), $term);
            });
        }

        return $conversations->values();
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

    public function getGroupMembersProperty()
    {
        if (! $this->activeConversation || ! $this->activeConversation->isGroup()) {
            return collect();
        }

        return $this->activeConversation->users
            ->where('id', '!=', auth()->id())
            ->map(fn ($u) => [
                'id' => $u->id,
                'name' => $u->name,
                'username' => $u->username ?: Str::slug($u->name, ''),
                'tag' => $u->username ?: Str::slug($u->name, ''),
                'avatar' => $u->getFilamentAvatarUrl(),
                'initial' => strtoupper(substr($u->name, 0, 1)),
            ])
            ->values();
    }

    public function openGroupSettings(): void
    {
        if (! $this->activeConversation || ! $this->activeConversation->isGroup()) {
            return;
        }

        $this->settingsGroupTitle = $this->activeConversation->title ?? '';
        $this->settingsGroupDescription = $this->activeConversation->description ?? '';
        $this->settingsOnlyAdminsCanMessage = (bool) $this->activeConversation->only_admins_can_message;
        $this->settingsOnlyAdminsCanEditInfo = (bool) $this->activeConversation->only_admins_can_edit_info;
        $this->settingsGroupAvatar = null;
        $this->groupSettingsTab = 'general';
        $this->showGroupSettingsModal = true;
    }

    public function saveGroupSettings(): void
    {
        if (! $this->activeConversation || ! $this->activeConversation->isGroup()) {
            return;
        }

        if (! $this->activeConversation->canUserEditInfo(auth()->id())) {
            Notification::make()
                ->danger()
                ->title('You do not have permission to edit group details.')
                ->send();

            return;
        }

        $this->validate([
            'settingsGroupTitle' => 'required|string|max:100',
            'settingsGroupDescription' => 'nullable|string|max:500',
        ]);

        $data = [
            'title' => trim($this->settingsGroupTitle),
            'description' => trim($this->settingsGroupDescription) ?: null,
        ];

        if ($this->settingsGroupAvatar) {
            $data['avatar_url'] = $this->settingsGroupAvatar->store('chat-avatars', 'public');
        }

        if ($this->activeConversation->isAdmin(auth()->id())) {
            $data['only_admins_can_message'] = (bool) $this->settingsOnlyAdminsCanMessage;
            $data['only_admins_can_edit_info'] = (bool) $this->settingsOnlyAdminsCanEditInfo;
        }

        $this->activeConversation->update($data);
        $this->activeConversation->refresh();
        $this->settingsOnlyAdminsCanMessage = (bool) $this->activeConversation->only_admins_can_message;
        $this->settingsOnlyAdminsCanEditInfo = (bool) $this->activeConversation->only_admins_can_edit_info;
        $this->settingsGroupAvatar = null;

        Notification::make()
            ->success()
            ->title('Group settings updated')
            ->send();
    }

    public function removeGroupAvatar(): void
    {
        if (! $this->activeConversation || ! $this->activeConversation->isGroup()) {
            return;
        }

        if (! $this->activeConversation->canUserEditInfo(auth()->id())) {
            Notification::make()
                ->danger()
                ->title('You do not have permission to edit group details.')
                ->send();

            return;
        }

        if ($this->activeConversation->avatar_url) {
            Storage::disk('public')->delete($this->activeConversation->avatar_url);
            $this->activeConversation->update(['avatar_url' => null]);
        }

        $this->settingsGroupAvatar = null;

        Notification::make()
            ->success()
            ->title('Group photo removed')
            ->send();
    }

    public function openAddMembersModal(): void
    {
        $this->newMemberIds = [];
        $this->memberSearch = '';
        $this->showAddMembersModal = true;
    }

    public function toggleAddMember(int $userId): void
    {
        if (in_array($userId, $this->newMemberIds, true)) {
            $this->newMemberIds = array_values(array_diff($this->newMemberIds, [$userId]));
        } else {
            $this->newMemberIds[] = $userId;
        }
    }

    public function addMembersToGroup(): void
    {
        if (! $this->activeConversation || ! $this->activeConversation->isGroup()) {
            return;
        }

        if (! $this->activeConversation->isAdmin(auth()->id())) {
            Notification::make()
                ->danger()
                ->title('Only admins can add members to this group.')
                ->send();

            return;
        }

        if (empty($this->newMemberIds)) {
            return;
        }

        foreach ($this->newMemberIds as $userId) {
            $this->activeConversation->addMember($userId, 'member');
        }

        $count = count($this->newMemberIds);
        $this->newMemberIds = [];
        $this->showAddMembersModal = false;

        Notification::make()
            ->success()
            ->title("Added {$count} member(s) to the group")
            ->send();
    }

    public function removeMemberFromGroup(int $userId): void
    {
        if (! $this->activeConversation || ! $this->activeConversation->isGroup()) {
            return;
        }

        if (! $this->activeConversation->isAdmin(auth()->id())) {
            Notification::make()
                ->danger()
                ->title('Only admins can remove members.')
                ->send();

            return;
        }

        if ($this->activeConversation->created_by === $userId) {
            Notification::make()
                ->danger()
                ->title('Cannot remove the group owner.')
                ->send();

            return;
        }

        if (! $this->activeConversation->isOwner(auth()->id()) && $this->activeConversation->isAdmin($userId)) {
            Notification::make()
                ->danger()
                ->title('Only the group owner can remove an admin.')
                ->send();

            return;
        }

        $this->activeConversation->removeMember($userId);

        Notification::make()
            ->success()
            ->title('Member removed from group')
            ->send();
    }

    public function toggleAdminRole(int $userId): void
    {
        if (! $this->activeConversation || ! $this->activeConversation->isGroup()) {
            return;
        }

        if (! $this->activeConversation->isOwner(auth()->id())) {
            Notification::make()
                ->danger()
                ->title('Only the group owner can assign or dismiss admins.')
                ->send();

            return;
        }

        if ($this->activeConversation->created_by === $userId) {
            return;
        }

        $isCurrentlyAdmin = $this->activeConversation->isAdmin($userId);
        $this->activeConversation->setAdmin($userId, ! $isCurrentlyAdmin);

        Notification::make()
            ->success()
            ->title($isCurrentlyAdmin ? 'Admin role removed' : 'Member promoted to Admin')
            ->send();
    }

    public function openTransferOwnershipModal(int $userId): void
    {
        if (! $this->activeConversation || ! $this->activeConversation->isOwner(auth()->id())) {
            Notification::make()
                ->danger()
                ->title('Only the owner can transfer ownership.')
                ->send();

            return;
        }

        $this->transferOwnershipUserId = $userId;
        $this->showTransferOwnershipModal = true;
    }

    public function confirmTransferOwnership(): void
    {
        if (! $this->activeConversation || ! $this->activeConversation->isOwner(auth()->id())) {
            return;
        }

        if (! $this->transferOwnershipUserId) {
            return;
        }

        $this->activeConversation->transferOwnership($this->transferOwnershipUserId);
        $newOwner = User::find($this->transferOwnershipUserId);
        $this->transferOwnershipUserId = null;
        $this->showTransferOwnershipModal = false;

        Notification::make()
            ->success()
            ->title('Ownership transferred'.($newOwner ? " to {$newOwner->name}" : ''))
            ->send();
    }

    public function leaveGroup(): void
    {
        if (! $this->activeConversation || ! $this->activeConversation->isGroup()) {
            return;
        }

        $currentUserId = auth()->id();

        if ($this->activeConversation->isOwner($currentUserId)) {
            $otherMembersCount = $this->activeConversation->participants()->where('user_id', '!=', $currentUserId)->count();
            if ($otherMembersCount > 0) {
                Notification::make()
                    ->danger()
                    ->title('Please transfer group ownership before leaving.')
                    ->send();

                return;
            }
        }

        $this->activeConversation->removeMember($currentUserId);
        $this->showGroupSettingsModal = false;
        $this->activeConversationId = null;

        Notification::make()
            ->info()
            ->title('You have left the group')
            ->send();
    }

    public function deleteGroup(): void
    {
        if (! $this->activeConversation || ! $this->activeConversation->isGroup()) {
            return;
        }

        if (! $this->activeConversation->isOwner(auth()->id())) {
            Notification::make()
                ->danger()
                ->title('Only the group owner can delete this group.')
                ->send();

            return;
        }

        $conversation = $this->activeConversation;
        $this->showGroupSettingsModal = false;
        $this->activeConversationId = null;

        $conversation->messages()->delete();
        $conversation->participants()->delete();
        $conversation->delete();

        Notification::make()
            ->success()
            ->title('Group chat deleted')
            ->send();
    }

    public function getGroupParticipantDetailsProperty()
    {
        if (! $this->activeConversation || ! $this->activeConversation->isGroup()) {
            return collect();
        }

        return $this->activeConversation->users()
            ->withPivot(['role', 'last_read_at'])
            ->get()
            ->map(function ($u) {
                $isOwner = $this->activeConversation->created_by === $u->id;
                $isAdmin = $isOwner || $u->pivot?->role === 'admin';

                return [
                    'id' => $u->id,
                    'name' => $u->name,
                    'username' => $u->username,
                    'email' => $u->email,
                    'avatar' => $u->getFilamentAvatarUrl(),
                    'initial' => strtoupper(substr($u->name, 0, 1)),
                    'role' => $isOwner ? 'owner' : ($isAdmin ? 'admin' : 'member'),
                    'is_owner' => $isOwner,
                    'is_admin' => $isAdmin,
                    'is_me' => $u->id === auth()->id(),
                ];
            })
            ->sortBy(function ($m) {
                if ($m['is_owner']) {
                    return 0;
                }
                if ($m['is_admin']) {
                    return 1;
                }

                return 2;
            })
            ->values();
    }

    public function getAddableUsersProperty()
    {
        if (! $this->activeConversation || ! $this->activeConversation->isGroup()) {
            return collect();
        }

        $existingIds = $this->activeConversation->participants()->pluck('user_id')->all();

        $query = User::query()->whereNotIn('id', $existingIds);

        if (filled($this->memberSearch)) {
            $term = '%'.strtolower($this->memberSearch).'%';
            $query->where(function ($q) use ($term) {
                $q->whereRaw('LOWER(name) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(username) LIKE ?', [$term])
                    ->orWhereRaw('LOWER(email) LIKE ?', [$term]);
            });
        }

        return $query->orderBy('name')->limit(25)->get();
    }
}
