<?php

namespace App\Http\Controllers\Panel\Marketing;

use App\Http\Controllers\Controller;
use App\Services\Marketing\MetasearchService;
use Illuminate\Http\Request;

class MetasearchController extends Controller
{
    public function __construct(protected MetasearchService $service) {}

    public function index()
    {
        $property = app('current_property');
        $channels = $this->service->getChannels();
        $performance = $this->service->getPerformance($property);

        return view('panel.marketing.metasearch', compact('channels', 'performance'));
    }

    public function feed(Request $request)
    {
        $property = app('current_property');
        $channel = $request->get('channel', 'google');
        $format = $request->get('format', 'csv');

        $feedContent = $this->service->generateFeed($property, $channel, $format);
        return response($feedContent, 200, ['Content-Type' => $this->contentType($format)]);
    }

    public function download(Request $request)
    {
        $property = app('current_property');
        $channel = $request->get('channel', 'google');
        $format = $request->get('format', 'csv');

        $feedContent = $this->service->generateFeed($property, $channel, $format);
        $filename = "metasearch-{$property->id}-{$channel}-" . now()->format('Ymd') . '.' . $format;

        return response($feedContent, 200, [
            'Content-Type' => $this->contentType($format),
            'Content-Disposition' => "attachment; filename={$filename}",
        ]);
    }

    public function performance(Request $request)
    {
        $property = app('current_property');
        $channel = $request->get('channel');
        $performance = $this->service->getPerformance($property);

        if ($channel && isset($performance[$channel])) {
            return response()->json($performance[$channel]);
        }
        return response()->json($performance);
    }

    protected function contentType(string $format): string
    {
        return match($format) {
            'xml' => 'application/xml',
            'json' => 'application/json',
            default => 'text/csv',
        };
    }
}
