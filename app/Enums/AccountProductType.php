<?php

namespace App\Enums;

class AccountProductType
{
    public const SAVINGS = 'savings';

    public const CURRENT = 'current';

    public const MERCHANT = 'merchant';

    public const CORPORATE = 'corporate';

    public const DOMICILIARY = 'domiciliary';

    public static function values(): array
    {
        return [
            self::SAVINGS,
            self::CURRENT,
            self::MERCHANT,
            self::CORPORATE,
            self::DOMICILIARY,
        ];
    }
}