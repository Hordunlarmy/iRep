<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('chat.{id}', function ($user, $id) {
    return true;
});

Broadcast::channel('presence.chat', function ($user) {
    return ['id' => $user->id, 'name' => $user->name];
});
