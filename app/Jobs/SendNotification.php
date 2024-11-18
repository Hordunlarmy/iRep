<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use App\Events\Notification;

class SendNotification implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;

    protected $service;
    public string $notificationType;
    public array $data;

    public function __construct(string $notificationType, array $data)
    {
        $this->service = app('pushNotify');
        $this->notificationType = $notificationType;
        $this->data = $data;
    }

    public function handle(): void
    {
        $deviceToken = $this->data['device_token'] ?? null;
        $title = $this->data['title'] ?? '';
        $userId = $this->data['user_id'] ?? null;
        $body = $this->data['body'] ?? '';

        if (!$userId) {
            return;
        }

        $notificationId = DB::table('account_notifications')->insertGetId([
            'user_id' => $userId,
            'type' => $this->notificationType,
            'title' => $title,
            'body' => $body,
            'read_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $notification = [
            'id' => $notificationId,
            'user_id' => $userId,
            'type' => $this->notificationType,
            'title' => $title,
            'body' => $body,
            'read_at' => null,
        ];

        Notification::dispatch($notification);

        if ($deviceToken) {
            $this->service->sendPushNotification($deviceToken, $title, $body);
        }
    }
}
