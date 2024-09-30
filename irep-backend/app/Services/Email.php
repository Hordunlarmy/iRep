<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Email
{
    protected $templates;
    protected $authorizationKey;
    protected $url;

    public function __construct(EmailTemplates $templates)
    {
        $this->templates = $templates;
        $this->authorizationKey = trim(env('EMAIL_AUTH_KEY', ''));
        $this->url = trim(env('EMAIL_API_URL', ''));

    }

    protected function sendEmail($templateKey, $recipientEmail, $recipientName, $mergeInfo)
    {
        $payload = [
            'mail_template_key' => $templateKey,
            'from' => [
                'address' => 'noreply@hordun.software',
                'name' => 'iREP',
            ],
            'to' => [
                [
                    'email_address' => [
                        'address' => $recipientEmail,
                        'name' => $recipientName,
                    ]
                ]
            ],
            'merge_info' => $mergeInfo,
        ];

        try {
            $response = Http::withHeaders([
                'accept' => 'application/json',
                'content-type' => 'application/json',
                'authorization' => $this->authorizationKey,
            ])->post($this->url, $payload);

            Log::info($response->body());
            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Email exception: ' . $e->getMessage());
            return false;
        }
    }

    public function sendNewUserVerification($recipientEmail, $recipientName, array $templateVariables)
    {
        return $this->sendEmail(
            $this->templates::$userVerification,
            $recipientEmail,
            $recipientName,
            $templateVariables
        );
    }

    public function sendResetPasswordVerification($recipientEmail, $recipientName, array $templateVariables)
    {
        return $this->sendEmail(
            $this->templates::$resetPassword,
            $recipientEmail,
            $recipientName,
            $templateVariables
        );
    }
}
