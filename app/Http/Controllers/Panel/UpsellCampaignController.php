<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\UpsellCampaign;
use App\Models\UpsellOffer;
use App\Services\Revenue\UpsellPreArrivalService;
use Illuminate\Http\Request;

class UpsellCampaignController extends Controller
{
    public function index()
    {
        $property = app('current_property');
        $campaigns = UpsellCampaign::where('property_id', $property->id)
            ->orderBy('created_at', 'desc')
            ->get();
        $offers = UpsellOffer::where('property_id', $property->id)
            ->where('is_active', true)
            ->get();
        return view('panel.upsell.campaigns.index', compact('property', 'campaigns', 'offers'));
    }

    public function create()
    {
        $property = app('current_property');
        $offers = UpsellOffer::where('property_id', $property->id)
            ->where('is_active', true)
            ->get();
        return view('panel.upsell.campaigns.create', compact('property', 'offers'));
    }

    public function store(Request $request)
    {
        $property = app('current_property');
        $validated = $request->validate([
            'name' => 'required|string|max:200',
            'offer_ids' => 'required|array',
            'days_before_arrival' => 'required|integer|min:1|max:30',
            'channel' => 'required|in:whatsapp,email,both',
            'guest_filters' => 'nullable|json',
        ]);

        UpsellCampaign::create(array_merge($validated, [
            'property_id' => $property->id,
            'guest_filters' => json_decode($validated['guest_filters'] ?? '{}', true),
        ]));

        return redirect()->route('panel.upsell.campaigns.index')
            ->with('success', 'Campaign berhasil dibuat.');
    }

    public function show($id)
    {
        $property = app('current_property');
        $campaign = UpsellCampaign::with('logs')->findOrFail($id);
        return view('panel.upsell.campaigns.show', compact('property', 'campaign'));
    }

    public function run(UpsellPreArrivalService $service, $id)
    {
        $result = $service->runCampaign((int) $id);
        return back()->with('success', "Campaign selesai. {$result['sent']} upsell terkirim.");
    }

    public function toggle(Request $request, $id)
    {
        $campaign = UpsellCampaign::findOrFail($id);
        $newStatus = $campaign->status === 'active' ? 'paused' : 'active';
        $campaign->update(['status' => $newStatus]);
        return back()->with('success', "Campaign di-{$newStatus}.");
    }

    public function acceptPresentation(UpsellPreArrivalService $service, $id)
    {
        $service->recordAcceptance((int) $id);
        return back()->with('success', 'Upsell diterima! Revenue bertambah.');
    }
}
