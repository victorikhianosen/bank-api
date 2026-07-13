<?php

namespace App\Models;

use App\Models\Document;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $guarded = [];

    public function accounts()
    {
        return $this->hasMany(Account::class);
    }

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    public function nextOfKins()
    {
        return $this->hasMany(NextOfKin::class);
    }

       public function documents()
    {
        return $this->hasMany(Document::class);
    }
}
