<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\MessageThread;
use App\Models\Property;
use App\Services\Communication\MessagingService;
use Illuminate\Http\Request;

class CommController extends Controller
{
    private function property(): Property
    {
        return app('current_property') ?? Property::orderBy('id')->firstOrFail();
    }

    public function threads(Request $request)
    {
        $query = MessageThread::where('property_id', $this->property()->id)
            ->with('guest')
            ->when($request->query('status'), fn ($q, $s) => $q->where('status', $s))
            ->when($request->query('channel'), fn ($q, $c) => $q->where('channel', $c))
            ->latest('last_message_at');

        return response()->json($query->paginate(50));
    }

    public function show(int $id)
    {
        $thread = MessageThread::where('property_id', $this->property()->id)
            ->with('messages', 'guest')
            ->findOrFail($id);
        return response()->json($thread);
    }

    public function reply(Request $request, int $id, MessagingService $svc)
    {
        $validated = $request->validate([
            'body' => 'required|string|max:5000',
        ]);

        $thread = MessageThread::where('property_id', $this->property()->id)->findOrFail($id);
        return response()->json($svc->reply($thread, $validated['body'], $request->user()?->id), 201);
    }

    public function inbound(Request $request, MessagingService $svc)
    {
        $validated = $request->validate([
            'channel' => 'required|in:email,whatsapp,sms,web_chat,ota_message',
            'from'    => 'required|string|max:100',
            'body'    => 'required|string',
            'context' => 'nullable|array',
        ]);

        return response()->json($svc->recordInbound(
            $validated['channel'],
            $validated['from'],
            $validated['body'],
            $validated['context'] ?? []
        ), 201);
    }
}
