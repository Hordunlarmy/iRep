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
        $this->notificationType = $notificationType;
        $this->data = $data;
    }

    public function handle(): void
    {
        $deviceToken = $this->data['device_token'] ?? null;
        $title = $this->data['title'] ?? '';
        $accountId = $this->data['account_id'] ?? null;
        $body = $this->data['body'] ?? '';

        if (!$accountId) {
            return;
        }

        $notificationId = DB::table('account_notifications')->insertGetId([
            'account_id' => $accountId,
            'type' => $this->notificationType,
            'title' => $title,
            'body' => $body,
            'read_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $notification = [
            'id' => $notificationId,
            'account_id' => $accountId,
            'type' => $this->notificationType,
            'title' => $title,
            'body' => $body,
            'read_at' => null,
        ];

        Notification::dispatch($notification);

        if ($deviceToken) {
            app('pushNotify')->sendPushNotification($deviceToken, $title, $body);
        }
    }
}
