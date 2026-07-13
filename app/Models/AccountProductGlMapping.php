<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountProductGlMapping extends Model
{
    protected $table = 'account_product_gl_mappings';

    protected $guarded = [];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    public function accountProduct(): BelongsTo
    {
        return $this->belongsTo(
            AccountProduct::class,
            'account_product_id'
        );
    }

    public function generalLedger(): BelongsTo
    {
        return $this->belongsTo(
            GeneralLedger::class,
            'general_ledger_id'
        );
    }
}