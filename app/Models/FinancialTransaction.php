<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinancialTransaction extends Model
{
    protected $guarded = [];

    public function sourceAccount()
    {
        return $this->belongsTo(
            Account::class,
            'source_account_id'
        );
    }

    public function destinationAccount()
    {
        return $this->belongsTo(
            Account::class,
            'destination_account_id'
        );
    }

    public function transactions()
    {
        return $this->hasMany(
            Transaction::class,
            'financial_transaction_id'
        );
    }
}