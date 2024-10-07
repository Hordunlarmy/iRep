<?php

namespace App\Services;

class EmailTemplates
{
    public static $userVerification;
    public static $resetPassword;

    public static function init()
    {
        self::$userVerification = env('EMAIL_VERIFICATION_TEMPLATE_ID');
        self::$resetPassword = env('RESET_PASSWORD_TEMPLATE_ID');
    }
}
