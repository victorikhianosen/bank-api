<?php

namespace App\Helpers;

class TransactionReference
{


 public static function generate(string $prefix = 'TRN'): string
{
    return strtoupper(substr($prefix, 0, 3))
        . now()->format('ymdHisu')
        . substr((string) hrtime(true), -12);
}


    public static function generateInternal(
        string $prefix
    ): string {

        return strtoupper($prefix) .
            strtoupper(substr(
                bin2hex(random_bytes(6)),
                0,
                12
            ));
    }


    // TRN  - General Transaction
    // BNK  - Bank Transfer
    // WAL  - Wallet
    // ACC  - Customer Account (Savings/Current)
    // GL   - General Ledger
    // FDP  - Fixed Deposit
    // INV  - Investment
    // LON  - Loan
    // CRD  - Card
    // AIR  - Airtime
    // DAT  - Data
    // ELE  - Electricity
    // CAB  - Cable TV
    // BIL  - Bill Payment
    // MER  - Merchant
    // VAN  - Virtual Account
    // SYS  - Internal System
    // DEP  - Deposit
    // WDL  - Withdrawal
    // REV  - Reversal
    // RFD  - Refund
    // FEE  - Fee
    // ADJ  - Adjustment
    // POS  - Point of Sale
    // INT  - Interest
    // CHG  - Charge
    // COM  - Commission
    // TAX  - Tax
    // BON  - Bonus
    // SAL  - Salary
    // PAY  - Payroll




}
