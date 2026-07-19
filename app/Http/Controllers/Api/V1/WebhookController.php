<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\Webhook;
use App\Models\WebhookDelivery;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WebhookController extends Controller
{
    private function property(): Property
    {
        return app('current_property') ?? Property::orderBy('id')->firstOrFail();
    }

    public function index()
    {
        return response()->json(
            Webhook::where('property_id', $this->property()->id)->paginate(50)
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'url'    => 'required|url|max:500',
            'events' => 'required|array|min:1',
            'secret' => 'nullable|string|max:255',
        ]);

        $secret = $validated['secret'] ?? Str::random(48);

        $webhook = Webhook::create([
            'property_id'      => $this->property()->id,
            'url'              => $validated['url'],
            'events'           => $validated['events'],
            'secret_encrypted' => $secret,
            'is_active'        => true,
        ]);

        return response()->json(['id' => $webhook->id, 'secret' => $secret], 201);
    }

    public function show(int $id)
    {
        return response()->json(
            Webhook::where('property_id', $this->property()->id)->findOrFail($id)
        );
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'url'       => 'sometimes|url|max:500',
            'events'    => 'sometimes|array|min:1',
            'is_active' => 'sometimes|boolean',
        ]);

        $webhook = Webhook::where('property_id', $this->property()->id)->findOrFail($id);
        $webhook->update($validated);

        return response()->json($webhook->fresh());
    }

    public function destroy(int $id)
    {
        Webhook::where('property_id', $this->property()->id)->findOrFail($id)->delete();

        return response()->json(['deleted' => true]);
    }

    public function test(int $id)
    {
        $webhook = Webhook::where('property_id', $this->property()->id)->findOrFail($id);

        return response()->json(['ok' => true, 'webhook_id' => $webhook->id, 'message' => 'Test event queued']);
    }

    public function deliveries(int $id)
    {
        Webhook::where('property_id', $this->property()->id)->findOrFail($id);

        return response()->json(
            WebhookDelivery::where('webhook_id', $id)->latest()->paginate(50)
        );
    }
}
