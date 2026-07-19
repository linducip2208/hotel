<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Property;

class HomeController extends Controller
{
    public function index()
    {
        $property = Property::orderBy('id')->first();
        return view('public.home', compact('property'));
    }

    public function about()   { return view('public.about', ['property' => Property::first()]); }
    public function contact() { return view('public.contact', ['property' => Property::first()]); }
    public function privacy() { return view('public.privacy'); }
    public function terms()   { return view('public.terms'); }
}
