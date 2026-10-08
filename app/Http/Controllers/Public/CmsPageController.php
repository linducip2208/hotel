<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\CmsPage;

class CmsPageController extends Controller
{
    public function show(string $slug)
    {
        $propertyId = app()->bound('current_property') ? app('current_property')->id : null;

        $query = CmsPage::where('slug', $slug)->where('is_published', true);

        if ($propertyId) {
            $query->where('property_id', $propertyId);
        }

        $page = $query->first();

        abort_if(! $page, 404);

        return view('public.cms-page', compact('page'));
    }
}
