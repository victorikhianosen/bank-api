<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;

class CurrencyController extends Controller
{
    use HttpResponses;
    public function index()
    {
        $currencies = Currency::all();
        return $this->success(message: 'Currency fetch successfully', data: $currencies);
    }
}
