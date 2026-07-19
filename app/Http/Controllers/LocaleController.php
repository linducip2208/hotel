<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class LocaleController extends Controller
{
    public function switch(Request $request, string $locale)
    {
        if (!in_array($locale, ['id', 'en'])) {
            $locale = 'id';
        }

        if ($request->user()) {
            $request->user()->update(['locale' => $locale]);
        }

        return redirect()->back()->withCookie(cookie()->forever('app_locale', $locale));
    }
}
