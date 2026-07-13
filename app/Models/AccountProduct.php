<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AccountProduct extends Model
{
    protected $guarded = [];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    public function generalLedgerMappings(): HasMany
    {
        return $this->hasMany(
            AccountProductGlMapping::class,
            'account_product_id'
        );
    }
}