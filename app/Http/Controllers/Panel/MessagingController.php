<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Services\Comm\GuestMessagingService;
use Illuminate\Http\Request;

class MessagingController extends Controller
{
    public function index(GuestMessagingService $service)
    {
        $property = app('current_property');
        $threads = $service->getActiveThreads($property->id);
        $unreadCount = $service->getUnreadCount($property->id);
        return view('panel.messaging.index', compact('property', 'threads', 'unreadCount'));
    }

    public function thread(GuestMessagingService $service, $id)
    {
        $property = app('current_property');
        $messages = $service->getThreadMessages($id);
        $quickReplies = $service->getQuickReplies($property->id);
        $thread = \App\Models\MessageThread::with('guest')->findOrFail($id);
        $service->markThreadRead($id);

        return view('panel.messaging.thread', compact('property', 'thread', 'messages', 'quickReplies'));
    }

    public function send(Request $request, GuestMessagingService $service, $id)
    {
        $request->validate(['body' => 'required|string|max:5000']);
        $property = app('current_property');
        $user = auth()->user();
        $from = $property->name ?? 'Front Desk';

        $msg = $service->sendMessage(
            (int) $id,
            'outbound',
            $from,
            'guest',
            $request->body,
            $request->input('type', 'text'),
            $request->input('attachments')
        );

        return back()->with('success', 'Pesan terkirim.');
    }

    public function markRead(GuestMessagingService $service, $id)
    {
        $service->markThreadRead($id);
        return response()->json(['ok' => true]);
    }

    public function closeThread(GuestMessagingService $service, $id)
    {
        $service->closeThread($id);
        return back()->with('success', 'Thread closed.');
    }

    public function poll(GuestMessagingService $service, $id)
    {
        $afterId = request('after_id');
        $messages = $service->getThreadMessages($id, 50, $afterId ? (int) $afterId : null);
        $service->markThreadRead($id);
        return response()->json($messages);
    }

    public function quickReplies(GuestMessagingService $service)
    {
        $property = app('current_property');
        return response()->json($service->getQuickReplies($property->id));
    }

    public function storeQuickReply(Request $request)
    {
        $property = app('current_property');
        \App\Models\QuickReply::create([
            'property_id' => $property->id,
            'label' => $request->label,
            'reply_text' => $request->reply_text,
            'display_order' => $request->input('display_order', 0),
        ]);
        return back()->with('success', 'Quick reply ditambahkan.');
    }
}
