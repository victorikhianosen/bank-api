<?php

namespace App\Enums;

enum AccountCategoryPrefix: string
{
    case SAV = '1';
    case CUR = '2';
    case COR = '3';
    case OTHER = '5';

    public static function fromCode(string $code): string
    {
        return match (strtoupper($code)) {
            'SAV' => self::SAV->value,
            'CUR' => self::CUR->value,
            'COR' => self::COR->value,
            default => self::OTHER->value,
        };
    }
}
