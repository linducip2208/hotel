<?php

namespace App\Http\Controllers\Panel\Marketing;

use App\Http\Controllers\Controller;
use App\Models\RoomType;

class WidgetController extends Controller
{
    public function index()
    {
        $propertyId = app('current_property')->id;
        $baseUrl = rtrim(config('app.url'), '/');

        $roomTypes = RoomType::where('property_id', $propertyId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $availabilityEmbed = sprintf(
            '<iframe src="%s/widget/availability" style="width:100%%;min-height:420px;border:0;border-radius:12px;" loading="lazy" title="Ketersediaan Kamar"></iframe>',
            $baseUrl
        );

        return view('panel.marketing.widgets', compact('roomTypes', 'baseUrl', 'availabilityEmbed'));
    }
}
