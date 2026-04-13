<?php

namespace App\Enums;

class AuditChannel
{

    public static function resolve(?string $value): string
    {
        $allowed = ['web', 'api', 'mobile', 'cron'];

        return in_array($value, $allowed, true) ? $value : 'web';
    }
}
