<?php

namespace App\Enums;

enum FinancialAccountType: int
{
    case SAVINGS_CONTROL = 1;

    case CASH = 2;

    case INTEREST_EXPENSE = 3;

    case FEE_INCOME = 4;

    case PENALTY_INCOME = 5;
}

