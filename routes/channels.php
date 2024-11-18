<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('chat.{chatId}', function ($user, $chatId) {
    // Check if the authenticated user is part of the chat
    $userIds = explode('.', $chatId);
    return in_array($user->id, $userIds);
});

Broadcast::channel('user.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
