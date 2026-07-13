<?php

namespace App\Enums;

enum GLType: string
{
    case CASH = 'cash';

    case BANK = 'bank';

    case SAVINGS_CONTROL = 'savings_control';

    case CURRENT_CONTROL = 'current_control';

    case WALLET_CONTROL = 'wallet_control';

    case LOAN_PORTFOLIO = 'loan_portfolio';

    case INTEREST_INCOME = 'interest_income';

    case INTEREST_EXPENSE = 'interest_expense';

    case FEE_INCOME = 'fee_income';

    case PENALTY_INCOME = 'penalty_income';

    case TAX = 'tax';

    case MERCHANT_SETTLEMENT = 'merchant_settlement';

    case SUSPENSE = 'suspense';

    case RECEIVABLE = 'receivable';

    case PAYABLE = 'payable';

    case FIXED_DEPOSIT = 'fixed_deposit';

    case INVESTMENT = 'investment';

    case EQUITY = 'equity';
}