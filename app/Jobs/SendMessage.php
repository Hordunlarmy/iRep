<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\Message;
use App\Events\NewMessage;
use Illuminate\Support\Facades\DB;

class SendMessage implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;

    public function __construct(public Message $message)
    {
        //
    }

    public function handle(): void
    {
        NewMessage::dispatch($this->message);

        $notification = [
            'account_id' => $this->message->receiverId,
            'entity_id' => $this->message->id,
            'title' => 'New Message',
            'body' => 'You have a new message ' . $this->message->senderId,
        ];

        SendNotification::dispatch("message", $notification);
    }
}
