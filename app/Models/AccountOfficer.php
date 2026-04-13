<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class AccountOfficer extends Model
{

use HasUlids;
    protected $guarded = [];
        
        public function branch()
        {
                return $this->belongsTo(Branch::class);
        }

        public function user()
        {
                return $this->belongsTo(User::class);
        }
}
