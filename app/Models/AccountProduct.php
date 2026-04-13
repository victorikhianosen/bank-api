<?php

namespace App\Models;

use App\Models\AccountCategory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class AccountProduct extends Model
{

    use HasUlids;

    protected $guarded = [];

    public function accountCategory()
    {
        return $this->belongsTo(AccountCategory::class);
    }
}
