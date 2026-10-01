<?php

use App\Models\ConversationParticipant;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('chat.conversation.{conversationId}', function ($user, $conversationId) {
    return ConversationParticipant::where('conversation_id', $conversationId)
        ->where('user_id', $user->id)
        ->exists();
});

Broadcast::channel('chat.user.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});

Broadcast::channel('chat.presence', function ($user) {
    return [
        'id' => $user->id,
        'name' => $user->name,
        'username' => $user->username,
    ];
});
