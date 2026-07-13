<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GeneralLedger extends Model
{
    protected $guarded = [];

    /*
    |--------------------------------------------------------------------------
    | RELATIONSHIPS
    |--------------------------------------------------------------------------
    */

    public function accountProductMappings(): HasMany
    {
        return $this->hasMany(
            AccountProductGlMapping::class,
            'general_ledger_id'
        );
    }
}