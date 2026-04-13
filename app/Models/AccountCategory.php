<?php

namespace App\Models;

use App\Models\AccountProduct;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class AccountCategory extends Model
{
    use HasUlids;
    protected $guarded = [];

    public function accountProducts()
    {
        return $this->hasMany(AccountProduct::class);
    }
}


