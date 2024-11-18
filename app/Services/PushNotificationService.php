<?php

namespace App\Services;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Illuminate\Support\Facades\Log;

class PushNotificationService
{
    protected $messaging;

    public function __construct()
    {
        $this->messaging = (new Factory())
            ->withServiceAccount(env('FIREBASE_CREDENTIALS_PATH'))
            ->createMessaging();
    }

    public function sendPushNotification(string $deviceToken, string $title, string $body)
    {
        try {
            $notification = Notification::create($title, $body);

            $message = CloudMessage::withTarget('token', $deviceToken)
                ->withNotification($notification);

            $this->messaging->send($message);

            Log::info('Notification sent successfully!');
            return response()->json(['status' => 'Notification sent successfully!']);
        } catch (FirebaseException $e) {
            Log::error('Failed to send notification: ' . $e->getMessage());
            return response()->json(['status' => 'Failed to send notification', 'error' => $e->getMessage()], 500);
        }
    }

    // Method to send push notification to a topic
    public function sendPushNotificationToTopic(string $topic, string $title, string $body)
    {
        try {
            $notification = Notification::create($title, $body);

            // Use the 'topic' target for sending to a topic
            $message = CloudMessage::withTarget('topic', $topic)
                ->withNotification($notification);

            $this->messaging->send($message);

            Log::info('Notification sent to topic successfully!');
            return response()->json(['status' => 'Notification sent to topic successfully!']);
        } catch (FirebaseException $e) {
            Log::error('Failed to send notification to topic: ' . $e->getMessage());
            return response()->json(['status' => 'Failed to send notification to topic', 'error' => $e->getMessage()], 500);
        }
    }

    public function subscribeToTopic(string $deviceToken, string $topic)
    {
        try {
            // Subscribe the device tokens to the specified topic
            $this->messaging->subscribeToTopic($deviceToken, $topic);

            Log::info('Device subscribed to topic successfully!', ['topic' => $topic]);
            return response()->json(['status' => 'Devices subscribed to topic successfully!']);
        } catch (FirebaseException $e) {
            Log::error('Failed to subscribe to topic: ' . $e->getMessage());
            return response()->json(['status' => 'Failed to subscribe to topic', 'error' => $e->getMessage()], 500);
        }
    }
}
