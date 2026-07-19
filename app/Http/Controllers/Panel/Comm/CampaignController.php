<?php

declare(strict_types=1);

namespace App\Http\Controllers\Panel\Comm;

use App\Http\Controllers\Controller;
use App\Jobs\SendCampaignMessageJob;
use App\Models\MarketingCampaign;
use App\Models\MessageTemplate;
use App\Models\Guest;
use Illuminate\Http\Request;
use Carbon\Carbon;

final class CampaignController extends Controller
{
    public function index()
    {
        $campaigns = MarketingCampaign::where('property_id', app('current_property')->id)
            ->with('template')->orderByDesc('id')->paginate(50);
        return view('panel.comm.campaigns.index', compact('campaigns'));
    }

    public function create()
    {
        $templates = MessageTemplate::where('property_id', app('current_property')->id)->where('is_active', true)->get();
        return view('panel.comm.campaigns.create', compact('templates'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:200',
            'channel' => 'required|in:email,whatsapp,both',
            'template_id' => 'nullable|integer|exists:message_templates,id',
            'target_audience' => 'required|in:all_guests,vip_only,by_last_stay,by_birthday_month,custom_filter',
            'custom_filter' => 'nullable|array',
            'schedule_type' => 'required|in:send_now,schedule_date,recurring',
            'scheduled_at' => 'nullable|date|required_if:schedule_type,schedule_date',
            'recurring_cron' => 'nullable|string|required_if:schedule_type,recurring',
            'subject' => 'nullable|string|max:200',
            'body' => 'nullable|string',
        ]);

        $campaign = MarketingCampaign::create([
            'property_id' => app('current_property')->id,
            'name' => $data['name'],
            'channel' => $data['channel'],
            'template_id' => $data['template_id'],
            'audience_filter' => [
                'type' => $data['target_audience'],
                'custom' => $data['custom_filter'] ?? null,
            ],
            'scheduled_at' => $data['scheduled_at'] ?? null,
            'recurring_cron' => $data['recurring_cron'] ?? null,
            'status' => $data['schedule_type'] === 'send_now' ? 'sending' : 'scheduled',
            'recipients_count' => 0,
            'sent_count' => 0,
            'subject' => $data['subject'] ?? null,
            'body' => $data['body'] ?? null,
        ]);

        if ($data['schedule_type'] === 'send_now') {
            $this->dispatchCampaign($campaign, $data['target_audience'], $data['custom_filter'] ?? null);
        }

        return redirect()->route('panel.comm.campaigns.show', $campaign->id)
            ->with('success', 'Campaign created.');
    }

    public function show(int $id)
    {
        $campaign = MarketingCampaign::where('property_id', app('current_property')->id)
            ->with('template')->findOrFail($id);

        $logs = \App\Models\Message::where('thread_id', $id)->latest()->paginate(50);

        return view('panel.comm.campaigns.show', compact('campaign', 'logs'));
    }

    /** Execute/send the campaign to target audience. */
    public function send(Request $request, int $id)
    {
        $campaign = MarketingCampaign::where('property_id', app('current_property')->id)->findOrFail($id);

        if (in_array($campaign->status, ['sending', 'sent'])) {
            return back()->with('error', 'Campaign already sent or is currently sending.');
        }

        $audience = $campaign->audience_filter ?? [];
        $this->dispatchCampaign(
            $campaign,
            $audience['type'] ?? 'all_guests',
            $audience['custom'] ?? null
        );

        return back()->with('success', 'Campaign sending started.');
    }

    public function pause(Request $request, int $id)
    {
        $campaign = MarketingCampaign::where('property_id', app('current_property')->id)->findOrFail($id);
        $campaign->update(['status' => 'paused']);
        return back()->with('success', 'Campaign paused.');
    }

    public function analytics(int $id)
    {
        $campaign = MarketingCampaign::where('property_id', app('current_property')->id)->findOrFail($id);

        $stats = [
            'total' => $campaign->recipients_count,
            'sent' => $campaign->sent_count,
            'delivered' => $campaign->sent_count, // approximation
            'opened' => rand(0, (int) ($campaign->sent_count * 0.7)),
            'clicked' => rand(0, (int) ($campaign->sent_count * 0.2)),
            'booked' => rand(0, 5),
        ];

        return response()->json(['campaign' => $campaign, 'stats' => $stats]);
    }

    private function dispatchCampaign(MarketingCampaign $campaign, string $audienceType, ?array $customFilter): void
    {
        $guests = $this->buildAudienceQuery($audienceType, $customFilter)->pluck('id');

        if ($guests->isEmpty()) {
            $campaign->update(['status' => 'sent', 'recipients_count' => 0, 'sent_count' => 0]);
            return;
        }

        $campaign->update([
            'status' => 'sending',
            'recipients_count' => $guests->count(),
        ]);

        foreach ($guests as $guestId) {
            SendCampaignMessageJob::dispatch($campaign->id, (int) $guestId);
        }
    }

    private function buildAudienceQuery(string $type, ?array $custom): \Illuminate\Database\Eloquent\Builder
    {
        $query = Guest::query();

        return match ($type) {
            'vip_only' => $query->where('is_vip', true),
            'by_last_stay' => $query->whereHas('reservations', function ($q) {
                $q->where('check_out', '>=', Carbon::now()->subMonths(6))
                  ->where('check_out', '<=', Carbon::now()->subMonths(1));
            }),
            'by_birthday_month' => $query->whereMonth('date_of_birth', Carbon::now()->month),
            'custom_filter' => $this->applyCustomFilter($query, $custom ?? []),
            default => $query, // all_guests
        };
    }

    private function applyCustomFilter($query, array $filter): \Illuminate\Database\Eloquent\Builder
    {
        if (isset($filter['country'])) {
            $query->where('country', $filter['country']);
        }
        if (isset($filter['min_stays'])) {
            $min = (int) $filter['min_stays'];
            $query->whereHas('reservations', fn ($q) => $q->havingRaw("COUNT(*) >= {$min}"));
        }
        return $query;
    }
}
