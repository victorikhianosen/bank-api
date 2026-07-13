<?php

namespace App\Models;

use App\Models\AccountProduct;
use App\Models\Customer;
use App\Models\Tier;
use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    protected $guarded = [];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function product()
    {
        return $this->belongsTo(
            AccountProduct::class,
            'account_product_id'
        );
    }

    public function tier()
    {
        return $this->belongsTo(
            Tier::class,
            'account_product_id'
        );
    }

    public function transactions()
    {
        return $this->hasMany(
            Transaction::class
        );
    }


    
 


}
