<?php

namespace App\Http\Controllers\Panel\Ai;

use App\Http\Controllers\Controller;
use App\Models\Integration;
use App\Models\Inventory;
use App\Models\Provider;
use App\Models\Review;
use App\Services\Ai\ConciergeService;
use App\Services\Ai\DemandForecastAi;
use App\Services\Ai\ReviewReplyGenerator;
use App\Services\Ai\TranslationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class AiController extends Controller
{
    public function hub()
    {
        $property = app('current_property');

        $aiProviders = collect();
        if (Schema::hasTable('integrations')) {
            $aiProviders = Integration::where('property_id', $property?->id)
                ->where('category', 'ai')
                ->get(['id','name','api_format','is_active','default_model']);
        }

        $tools = [
            ['key' => 'concierge',     'label' => 'AI Concierge',          'desc' => 'Chatbot multi-bahasa untuk tamu',     'icon' => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z', 'color' => 'indigo'],
            ['key' => 'translate',     'label' => 'Auto-Translate',        'desc' => 'Terjemahkan deskripsi & konten',      'icon' => 'M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129', 'color' => 'sky'],
            ['key' => 'forecast',      'label' => 'Demand Forecast',       'desc' => 'Prediksi okupansi 30 hari',           'icon' => 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6', 'color' => 'emerald'],
            ['key' => 'review-reply',  'label' => 'Review Reply',          'desc' => 'Generate balasan review otomatis',    'icon' => 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z', 'color' => 'amber'],
            ['key' => 'sentiment',     'label' => 'Sentiment Analysis',    'desc' => 'Analisa perasaan tamu',                'icon' => 'M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'color' => 'rose'],
            ['key' => 'pseo-content',  'label' => 'pSEO Content Generator', 'desc' => 'FAQ + deskripsi otomatis',            'icon' => 'M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z', 'color' => 'violet'],
            ['key' => 'ocr',           'label' => 'KTP/Paspor OCR',        'desc' => 'Vision LLM scan identitas',            'icon' => 'M15 7a2 2 0 11-4 0 2 2 0 014 0zm6 8a2 2 0 11-4 0 2 2 0 014 0zM5 13a2 2 0 11-4 0 2 2 0 014 0z M9 17.5L4 22.5M14 14L9 19l5-5z', 'color' => 'cyan'],
        ];

        return view('panel.ai.hub', compact('property', 'aiProviders', 'tools'));
    }

    public function concierge()
    {
        return view('panel.ai.concierge');
    }

    public function translate()
    {
        return view('panel.ai.translate');
    }

    public function forecast(DemandForecastAi $svc)
    {
        $property = app('current_property');
        $from = Carbon::today();
        $to = Carbon::today()->addDays(30);

        $forecast = ['days' => [], 'note' => null];
        if ($property) {
            try {
                $forecast = $svc->forecast($property, $from, $to);
            } catch (\Throwable $e) {
                $forecast['note'] = 'AI provider belum dikonfigurasi atau error: ' . $e->getMessage();
            }
        }

        $totalRooms = $property?->total_rooms ?: 1;
        $historical = collect();
        for ($d = 0; $d < 14; $d++) {
            $date = Carbon::today()->subDays(13 - $d)->toDateString();
            $sold = (int) Inventory::where('property_id', $property?->id)->whereDate('date', $date)->sum('sold');
            $historical->push([
                'date' => $date,
                'occ'  => round(($sold / $totalRooms) * 100, 1),
            ]);
        }

        return view('panel.ai.forecast', compact('forecast', 'historical', 'totalRooms'));
    }

    public function reviewReplies()
    {
        $property = app('current_property');
        $reviews = collect();
        if (Schema::hasTable('reviews')) {
            $cols = Schema::getColumnListing('reviews');
            $q = Review::where('property_id', $property?->id);
            // reply column varies between schema iterations
            if (in_array('reply_text', $cols, true)) $q->whereNull('reply_text');
            elseif (in_array('reply', $cols, true)) $q->whereNull('reply');
            elseif (in_array('reply_at', $cols, true)) $q->whereNull('reply_at');
            $reviews = $q->orderByDesc('created_at')->take(20)->get();
        }
        return view('panel.ai.review-replies', compact('reviews'));
    }

    public function providers()
    {
        $property = app('current_property');
        $providers = Provider::where('property_id', $property?->id)
            ->where('integration_type', 'ai')
            ->orderBy('display_order')
            ->get();

        return view('panel.ai.providers', compact('providers', 'property'));
    }
}
