<?php

namespace App\Http\Controllers\Panel\Comm;

use App\Http\Controllers\Controller;
use App\Models\MarketingCampaign;
use App\Models\MessageTemplate;
use App\Models\MessageThread;
use App\Services\Communication\MessagingService;
use Illuminate\Http\Request;

class CommController extends Controller
{
    public function inbox(Request $request)
    {
        $threads = MessageThread::where('property_id', app('current_property')->id)
            ->with('guest')->orderByDesc('last_message_at')->paginate(50);
        return view('panel.comm.inbox', compact('threads'));
    }

    public function thread(int $id)
    {
        $thread = MessageThread::with('messages', 'guest', 'reservation')->findOrFail($id);
        $thread->update(['unread_count' => 0]);
        return view('panel.comm.thread', compact('thread'));
    }

    public function reply(Request $request, int $id, MessagingService $svc)
    {
        $thread = MessageThread::where('property_id', app('current_property')->id)->findOrFail($id);
        $svc->reply($thread, $request->input('body'), $request->user()?->id);
        return back();
    }

    public function templates()
    {
        $templates = MessageTemplate::where('property_id', app('current_property')->id)->paginate(50);
        return view('panel.comm.templates', compact('templates'));
    }

    public function storeTemplate(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'channel' => 'required|in:email,whatsapp,sms',
            'subject' => 'nullable|string',
            'body' => 'required|string',
            'locale' => 'nullable|string',
        ]);
        MessageTemplate::create($data + ['property_id' => app('current_property')->id]);
        return back();
    }

    public function campaigns()
    {
        $campaigns = MarketingCampaign::where('property_id', app('current_property')->id)
            ->with('template')->orderByDesc('id')->paginate(50);
        $templates = MessageTemplate::where('property_id', app('current_property')->id)->where('is_active', true)->get();
        return view('panel.comm.campaigns', compact('campaigns', 'templates'));
    }

    public function storeCampaign(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'channel' => 'required|in:email,whatsapp,sms',
            'template_id' => 'nullable|integer',
            'scheduled_at' => 'nullable|date',
        ]);
        MarketingCampaign::create($data + ['property_id' => app('current_property')->id, 'status' => 'draft']);
        return back();
    }
}
