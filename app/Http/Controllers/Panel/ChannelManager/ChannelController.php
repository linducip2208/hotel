<?php

namespace App\Http\Controllers\Panel\ChannelManager;

use App\Http\Controllers\Controller;
use App\Models\AriSyncLog;
use App\Models\Channel;
use App\Models\ChannelConflict;
use App\Models\ChannelRoomMapping;
use App\Models\Provider;
use App\Models\Rate;
use App\Models\RatePlan;
use App\Models\RoomType;
use Illuminate\Http\Request;

class ChannelController extends Controller
{
    public function index()
    {
        $channels = Channel::where('property_id', app('current_property')->id)->get();
        return view('panel.channel.index', compact('channels'));
    }

    public function mapping()
    {
        $pid = app('current_property')->id;
        $channels = Channel::where('property_id', $pid)->where('is_active', true)->get();
        $roomTypes = RoomType::where('property_id', $pid)->orderBy('name')->get();
        $ratePlans = RatePlan::where('property_id', $pid)->where('is_active', true)->orderBy('name')->get();
        $mappings = ChannelRoomMapping::whereHas('channel', fn ($q) => $q->where('property_id', $pid))
            ->with('channel', 'roomType', 'ratePlan')
            ->get();
        return view('panel.channel.mapping', compact('channels', 'roomTypes', 'ratePlans', 'mappings'));
    }

    public function storeMapping(Request $request)
    {
        $data = $request->validate([
            'channel_id' => 'required|integer|exists:channels,id',
            'room_type_id' => 'required|integer|exists:room_types,id',
            'rate_plan_id' => 'required|integer|exists:rate_plans,id',
            'channel_room_id' => 'required|string',
            'channel_rate_id' => 'required|string',
        ]);
        ChannelRoomMapping::create($data + ['is_active' => true]);
        return back()->with('success', 'Mapping ruangan berhasil disimpan.');
    }

    public function deleteMapping($id)
    {
        $mapping = ChannelRoomMapping::findOrFail($id);
        $mapping->delete();
        return back()->with('success', 'Mapping ruangan telah dihapus.');
    }

    public function rates()
    {
        $pid = app('current_property')->id;
        $mappings = ChannelRoomMapping::whereHas('channel', fn ($q) => $q->where('property_id', $pid))
            ->with('channel', 'roomType', 'ratePlan')
            ->get();
        return view('panel.channel.rates', compact('mappings'));
    }

    public function updateRates(Request $request)
    {
        $data = $request->validate([
            'mapping_id' => 'required|array',
            'mapping_id.*' => 'required|integer|exists:channel_room_mappings,id',
            'base_rate' => 'required|array',
            'base_rate.*' => 'required|numeric|min:0',
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
        ]);

        foreach ($data['mapping_id'] as $i => $mappingId) {
            $mapping = ChannelRoomMapping::findOrFail($mappingId);
            $baseRate = $data['base_rate'][$i];

            $mapping->update([
                'config' => array_merge($mapping->config ?? [], [
                    'base_rate' => (float) $baseRate,
                    'rate_date_from' => $data['date_from'],
                    'rate_date_to' => $data['date_to'],
                    'last_rate_push' => now()->toDateTimeString(),
                ]),
            ]);
        }

        return back()->with('success', 'Tarif channel berhasil diperbarui.');
    }

    public function restrictions()
    {
        $pid = app('current_property')->id;
        $channels = Channel::where('property_id', $pid)->where('is_active', true)->get();
        $mappings = ChannelRoomMapping::whereHas('channel', fn ($q) => $q->where('property_id', $pid))
            ->with('channel', 'roomType', 'ratePlan')
            ->get();
        return view('panel.channel.restrictions', compact('channels', 'mappings'));
    }

    public function storeRestrictions(Request $request)
    {
        $data = $request->validate([
            'mapping_id' => 'required|array',
            'mapping_id.*' => 'required|integer|exists:channel_room_mappings,id',
            'cta_days' => 'nullable|array',
            'cta_days.*' => 'nullable|integer|min:0',
            'ctd_days' => 'nullable|array',
            'ctd_days.*' => 'nullable|integer|min:0',
            'min_los' => 'nullable|array',
            'min_los.*' => 'nullable|integer|min:1',
            'max_los' => 'nullable|array',
            'max_los.*' => 'nullable|integer|min:1',
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
        ]);

        foreach ($data['mapping_id'] as $i => $mappingId) {
            $mapping = ChannelRoomMapping::findOrFail($mappingId);

            $restrictions = [
                'cta_days' => isset($data['cta_days'][$i]) ? (int) $data['cta_days'][$i] : null,
                'ctd_days' => isset($data['ctd_days'][$i]) ? (int) $data['ctd_days'][$i] : null,
                'min_los' => isset($data['min_los'][$i]) ? (int) $data['min_los'][$i] : null,
                'max_los' => isset($data['max_los'][$i]) ? (int) $data['max_los'][$i] : null,
                'date_from' => $data['date_from'],
                'date_to' => $data['date_to'],
                'updated_at' => now()->toDateTimeString(),
            ];

            $mapping->update(['restrictions' => $restrictions]);
        }

        return back()->with('success', 'Restriksi channel berhasil disimpan.');
    }

    public function updateRestrictions(Request $request, $id)
    {
        $mapping = ChannelRoomMapping::findOrFail($id);

        $data = $request->validate([
            'cta_days' => 'nullable|integer|min:0',
            'ctd_days' => 'nullable|integer|min:0',
            'min_los' => 'nullable|integer|min:1',
            'max_los' => 'nullable|integer|min:1',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
        ]);

        $restrictions = array_merge($mapping->restrictions ?? [], array_filter($data, fn ($v) => !is_null($v)));
        $restrictions['updated_at'] = now()->toDateTimeString();

        $mapping->update(['restrictions' => $restrictions]);

        return back()->with('success', 'Restriksi berhasil diperbarui.');
    }

    public function syncLog()
    {
        $logs = AriSyncLog::whereHas('channel', fn ($q) => $q->where('property_id', app('current_property')->id))
            ->latest()->paginate(50);
        return view('panel.channel.sync-log', compact('logs'));
    }

    public function conflicts()
    {
        $conflicts = ChannelConflict::where('property_id', app('current_property')->id)
            ->where('status', 'open')->latest()->paginate(25);
        return view('panel.channel.conflicts', compact('conflicts'));
    }

    public function resolveConflict(Request $request, int $id)
    {
        $c = ChannelConflict::where('property_id', app('current_property')->id)->findOrFail($id);
        $c->update([
            'status' => 'resolved',
            'resolved_at' => now(),
            'resolved_by_user_id' => $request->user()?->id,
            'resolution_notes' => $request->input('notes'),
        ]);
        return back();
    }

    public function providers()
    {
        $property = app('current_property');
        $providers = Provider::where('property_id', $property?->id)
            ->where('integration_type', 'ota')
            ->orderBy('display_order')
            ->get();

        return view('panel.channel.providers', compact('providers', 'property'));
    }

    // ── Virtual Cards ─────────────────────────────────────────────────
    public function virtualCards()
    {
        $cards = \App\Models\OtaVirtualCard::where('property_id', app('current_property')->id)
            ->with('reservation', 'channel')
            ->latest()->paginate(25);
        return view('panel.channel.vcc-index', compact('cards'));
    }

    public function virtualCardDetail($id)
    {
        $card = \App\Models\OtaVirtualCard::where('property_id', app('current_property')->id)
            ->with('reservation.primaryGuest', 'channel')
            ->findOrFail($id);
        return view('panel.channel.vcc-detail', compact('card'));
    }

    // ── GDS Bookings ──────────────────────────────────────────────────
    public function gdsBookings()
    {
        $bookings = \App\Models\GdsBooking::where('property_id', app('current_property')->id)
            ->with('reservation')
            ->latest('received_at')->paginate(25);
        return view('panel.channel.gds-index', compact('bookings'));
    }

    public function gdsBookingDetail($id)
    {
        $booking = \App\Models\GdsBooking::where('property_id', app('current_property')->id)
            ->with('reservation.primaryGuest')
            ->findOrFail($id);
        return view('panel.channel.gds-detail', compact('booking'));
    }

    // ── Channel Dashboard ─────────────────────────────────────────────
    public function dashboard()
    {
        $property = app('current_property');
        $channels = \App\Models\Channel::where('property_id', $property->id)
            ->withCount(['mappings', 'syncLogs', 'virtualCards'])
            ->with(['conflicts' => fn($q) => $q->where('status', 'open')])
            ->get();

        $totalMappings = $channels->sum('mappings_count');
        $totalConflicts = $channels->sum(fn($c) => $c->conflicts->count());
        $totalVcc = $channels->sum('virtual_cards_count');

        $recentLogs = \App\Models\AriSyncLog::whereHas('channel', fn($q) => $q->where('property_id', $property->id))
            ->with('channel')->latest()->limit(10)->get();

        $recentConflicts = \App\Models\ChannelConflict::where('property_id', $property->id)
            ->where('status', 'open')->latest()->limit(5)->get();

        $parityAlerts = \App\Models\ChannelParityAlert::where('property_id', $property->id)
            ->where('status', 'open')->count();

        return view('panel.channel.dashboard', compact(
            'channels', 'totalMappings', 'totalConflicts', 'totalVcc',
            'recentLogs', 'recentConflicts', 'parityAlerts'
        ));
    }

    // ── Per-OTA Detail ────────────────────────────────────────────────
    public function detail($id)
    {
        $channel = \App\Models\Channel::where('property_id', app('current_property')->id)
            ->with(['mappings.roomType', 'mappings.ratePlan', 'provider'])
            ->findOrFail($id);

        $syncLogs = \App\Models\AriSyncLog::where('channel_id', $channel->id)
            ->latest()->limit(20)->get();

        $conflicts = \App\Models\ChannelConflict::where('property_id', app('current_property')->id)
            ->where('channel_id', $channel->id)->latest()->limit(10)->get();

        $vccs = \App\Models\OtaVirtualCard::where('channel_id', $channel->id)
            ->with('reservation')->latest()->limit(10)->get();

        $parityAlerts = \App\Models\ChannelParityAlert::where('channel_id', $channel->id)
            ->where('status', 'open')->latest()->limit(5)->get();

        $otaBookings = \App\Models\Reservation::where('property_id', app('current_property')->id)
            ->where('source', 'LIKE', 'ota:'.$channel->code.'%')
            ->latest()->limit(10)->get();

        return view('panel.channel.detail', compact(
            'channel', 'syncLogs', 'conflicts', 'vccs', 'parityAlerts', 'otaBookings'
        ));
    }
}
