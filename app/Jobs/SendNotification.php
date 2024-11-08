<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Events\LikeNotificationEvent;
use App\Events\CommentNotificationEvent;
use App\Events\RepostNotificationEvent;

class SendNotification implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;

    public function __construct(public string $notificationType, public array $data)
    {
        // Accepts the type (e.g., 'like', 'comment', 'repost') and the data payload
    }

    public function handle(): void
    {
        // Handle different types of notifications
        switch ($this->notificationType) {
            case 'like':
                // LikeNotificationEvent::dispatch($this->data);
                break;

            case 'comment':
                // CommentNotificationEvent::dispatch($this->data);
                break;

            case 'repost':
                // RepostNotificationEvent::dispatch($this->data);
                break;

            default:
                throw new \InvalidArgumentException("Unknown notification type: {$this->notificationType}");
        }
    }
}
