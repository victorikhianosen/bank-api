<?php

namespace App\Enums;

enum TransactionType: string 
{
    case WALLET_TRANSFER = 'wallet_transfer';
    case BANK_TRANSFER = 'bank_transfer';
    case AIRTIME = 'airtime';
    case DATA = 'data';
    case INVESTMENT = 'investment';
    case LOAN = 'loan';
    case ELECTRICITY = 'electricity';
    case CABLE = 'cable';



}

