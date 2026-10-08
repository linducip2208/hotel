<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Services\CurrencyService;
use Illuminate\Http\Request;

class CurrencyController extends Controller
{
    public function switch(Request $request, CurrencyService $currency)
    {
        $code = strtoupper($request->query('code', 'IDR'));

        if (in_array($code, $currency->available(), true)) {
            session([CurrencyService::SESSION_KEY => $code]);
        }

        return back();
    }
}
