<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Services\PublicPropertyResolver;

class HomeController extends Controller
{
    public function index()
    {
        $property = Property::orderBy('id')->first();

        return view('public.home', compact('property'));
    }

    public function about()
    {
        return view('public.about', ['property' => app(PublicPropertyResolver::class)->resolve()]);
    }

    public function contact()
    {
        return view('public.contact', ['property' => app(PublicPropertyResolver::class)->resolve()]);
    }

    public function privacy()
    {
        return view('public.privacy');
    }

    public function terms()
    {
        return view('public.terms');
    }
}
