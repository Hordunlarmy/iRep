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
        $accountId = $this->data['account_id'] ?? null;
        $title = $this->data['title'] ?? '';
        $body = $this->data['body'] ?? '';
        $entityId = $this->data['entity_id'] ?? null;

        if (!$accountId) {
            return;
        }

        $extraFields = collect($this->data)->except([
        'account_id', 'title', 'body',
        ])->toArray();

        $deviceToken = DB::table('device_tokens')
            ->where('account_id', $accountId)
            ->value('device_token');

        $notificationId = DB::table('account_notifications')->insertGetId([
            'account_id' => $accountId,
            'entity_id' => $entityId,
            'type' => $this->notificationType,
            'title' => $title,
            'body' => $body,
            'read_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $notification = array_merge(
            [
            'id' => $notificationId,
            'account_id' => $accountId,
            'type' => $this->notificationType,
            'title' => $title,
            'body' => $body,
            'read_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ],
            $extraFields
        );

        Notification::dispatch($notification);

        if ($deviceToken) {
            app('pushNotify')->sendPushNotification($deviceToken, $title, $body);
        }
    }
}
