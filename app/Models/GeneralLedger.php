<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GeneralLedger extends Model
{
      protected $fillable = [
    'branch_id',
    'currency_id',
    'currency',
    'name',
    'code',
    'type',
    'parent_id',
    'balance',
    'status',
    'created_by',
    'updated_by'
  ];

  public function currency()
  {
    return $this->belongsTo(Currency::class);
  }
}
