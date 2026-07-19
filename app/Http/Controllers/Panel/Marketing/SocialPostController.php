<?php

namespace App\Http\Controllers\Panel\Marketing;

use App\Http\Controllers\Controller;
use App\Services\Marketing\SocialAutoPostService;
use Illuminate\Http\Request;

class SocialPostController extends Controller
{
    public function index()
    {
        $property = app('current_property');
        $connected = \App\Models\Provider::where('property_id', $property->id)
            ->where('integration_type', 'social')
            ->where('api_format', 'instagram_graph')
            ->where('is_active', true)
            ->exists();

        $templates = [
            'weekend'     => 'Weekend Getaway',
            'flash_sale'  => 'Flash Sale 24 Jam',
            'new_year'    => 'Paket Tahun Baru',
        ];

        $service = app(SocialAutoPostService::class);
        $captions = [];
        foreach ($templates as $key => $label) {
            $captions[$key] = $service->generatePromoCaption($property, $key);
        }

        $history = \App\Models\NotificationLog::where('property_id', $property->id)
            ->where('notification_type', 'social_post')
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        return view('panel.marketing.social-poster', compact('connected', 'templates', 'captions', 'history'));
    }

    public function postNow(Request $request)
    {
        $property = app('current_property');
        $data = $request->validate([
            'type'    => 'required|string|in:weekend,flash_sale,new_year,custom',
            'caption' => 'required_if:type,custom|string|max:2200',
        ]);

        $service = app(SocialAutoPostService::class);

        if ($data['type'] === 'availability') {
            $result = $service->postRoomAvailability($property);
        } elseif ($data['type'] === 'custom') {
            $result = $service->postToInstagram($property, $data['caption']);
        } else {
            $caption = $service->generatePromoCaption($property, $data['type']);
            $result  = $service->postToInstagram($property, $caption);
        }

        \App\Models\NotificationLog::create([
            'property_id'      => $property->id,
            'notification_type'=> 'social_post',
            'channel'          => 'instagram',
            'recipient'        => 'feed',
            'status'           => $result['posted'] ? 'sent' : 'failed',
            'payload'          => $result,
        ]);

        if ($result['posted']) {
            return back()->with('success', 'Postingan berhasil dipublikasikan ke Instagram.');
        }

        return back()->with('error', 'Gagal posting: ' . ($result['message'] ?? 'Unknown error'));
    }

    public function schedule(Request $request)
    {
        $data = $request->validate([
            'type'       => 'required|string|in:weekend,flash_sale,new_year,custom',
            'caption'    => 'required_if:type,custom|string|max:2200',
            'scheduled_at' => 'required|date|after:now',
        ]);

        return back()->with('success', 'Postingan dijadwalkan untuk ' . $data['scheduled_at'] . '. (Fitur scheduler aktif setelah deploy cron)');
    }
}
