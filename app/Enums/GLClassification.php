<?php

namespace App\Enums;

enum GLClassification: int
{
    case ASSET = 1;

    case LIABILITY = 2;

    case EQUITY = 3;

    case INCOME = 4;

    case EXPENSE = 5;

    public function label(): string
    {
        return match ($this) {
            self::ASSET => 'Asset',
            self::LIABILITY => 'Liability',
            self::EQUITY => 'Equity',
            self::INCOME => 'Income',
            self::EXPENSE => 'Expense',
        };
    }
}


