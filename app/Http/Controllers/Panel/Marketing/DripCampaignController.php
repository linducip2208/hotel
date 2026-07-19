<?php

namespace App\Http\Controllers\Panel\Marketing;

use App\Http\Controllers\Controller;
use App\Models\DripCampaign;
use App\Models\DripQueue;
use App\Services\Marketing\DripCampaignService;
use Illuminate\Http\Request;

class DripCampaignController extends Controller
{
    public function index()
    {
        $propertyId = app('current_property')->id;
        $campaigns = DripCampaign::where('property_id', $propertyId)
            ->withCount(['steps', 'queueItems'])
            ->orderByDesc('created_at')
            ->get();

        $queueStats = app(DripCampaignService::class)->getQueueStats($propertyId);

        $recentQueue = DripQueue::where('property_id', $propertyId)
            ->with(['dripStep.campaign', 'guest'])
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        return view('panel.marketing.drip-campaigns', compact('campaigns', 'queueStats', 'recentQueue'));
    }

    public function create()
    {
        return view('panel.marketing.drip-campaigns-edit', [
            'campaign' => null,
        ]);
    }

    public function store(Request $request, DripCampaignService $service)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'trigger_event' => 'required|string|in:booking_confirmed,checkin,checkout,post_stay,birthday,inactive',
            'is_active' => 'boolean',
            'steps' => 'required|array|min:1',
            'steps.*.delay_hours' => 'required|integer|min:0',
            'steps.*.channel' => 'required|string|in:whatsapp,email,both',
            'steps.*.message' => 'required|string',
            'steps.*.subject' => 'nullable|string|max:255',
            'steps.*.template_key' => 'nullable|string|max:100',
        ]);

        $data['property_id'] = app('current_property')->id;
        $service->createCampaign($data, $data['steps']);

        return redirect()->route('panel.marketing.drip-campaigns.index')
            ->with('success', 'Drip campaign created successfully.');
    }

    public function edit($id)
    {
        $campaign = DripCampaign::where('property_id', app('current_property')->id)
            ->with('steps')
            ->findOrFail($id);

        return view('panel.marketing.drip-campaigns-edit', compact('campaign'));
    }

    public function update(Request $request, $id)
    {
        $campaign = DripCampaign::where('property_id', app('current_property')->id)
            ->findOrFail($id);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'trigger_event' => 'required|string|in:booking_confirmed,checkin,checkout,post_stay,birthday,inactive',
            'is_active' => 'boolean',
            'steps' => 'required|array|min:1',
            'steps.*.id' => 'nullable|integer',
            'steps.*.delay_hours' => 'required|integer|min:0',
            'steps.*.channel' => 'required|string|in:whatsapp,email,both',
            'steps.*.message' => 'required|string',
            'steps.*.subject' => 'nullable|string|max:255',
            'steps.*.template_key' => 'nullable|string|max:100',
        ]);

        $campaign->update([
            'name' => $data['name'],
            'trigger_event' => $data['trigger_event'],
            'is_active' => $data['is_active'] ?? true,
        ]);

        $campaign->steps()->delete();

        foreach ($data['steps'] as $i => $step) {
            $campaign->steps()->create([
                'delay_hours' => $step['delay_hours'],
                'channel' => $step['channel'] ?? 'whatsapp',
                'template_key' => $step['template_key'] ?? 'drip_' . ($i + 1),
                'subject' => $step['subject'] ?? null,
                'message' => $step['message'],
                'sort_order' => $i + 1,
            ]);
        }

        return redirect()->route('panel.marketing.drip-campaigns.index')
            ->with('success', 'Drip campaign updated successfully.');
    }

    public function destroy($id)
    {
        $campaign = DripCampaign::where('property_id', app('current_property')->id)
            ->findOrFail($id);

        $campaign->steps()->delete();
        $campaign->delete();

        return redirect()->route('panel.marketing.drip-campaigns.index')
            ->with('success', 'Drip campaign deleted.');
    }
}
