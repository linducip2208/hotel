<?php

namespace App\Http\Controllers\Panel\Fo;

use App\Http\Controllers\Controller;
use App\Models\GroupBlock;
use App\Models\GroupBlockRoom;
use App\Models\Guest;
use App\Models\RatePlan;
use App\Models\Room;
use App\Models\RoomType;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class GroupBlockController extends Controller
{
    public function index()
    {
        $property = app('current_property');
        $blocks = GroupBlock::where('property_id', $property->id)
            ->with(['rooms.roomType', 'company'])
            ->orderByDesc('check_in')
            ->paginate(25);

        return view('panel.fo.group-blocks.index', compact('blocks'));
    }

    public function create()
    {
        $property = app('current_property');
        $roomTypes = RoomType::where('property_id', $property->id)->where('is_active', true)->orderBy('name')->get();
        $ratePlans = RatePlan::where('property_id', $property->id)->where('is_active', true)->orderBy('name')->get();
        $guests = Guest::where('property_id', $property->id)->orderBy('first_name')->get();

        return view('panel.fo.group-blocks.create', compact('roomTypes', 'ratePlans', 'guests'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'group_name' => ['required', 'string', 'max:255'],
            'guest_id' => ['required', 'integer', 'exists:guests,id'],
            'check_in' => ['required', 'date', 'after_or_equal:today'],
            'check_out' => ['required', 'date', 'after:check_in'],
            'rooms_count' => ['required', 'integer', 'min:1', 'max:999'],
            'room_type_id' => ['required', 'integer', 'exists:room_types,id'],
            'rate_plan_id' => ['nullable', 'integer', 'exists:rate_plans,id'],
            'negotiated_rate' => ['nullable', 'numeric', 'min:0'],
            'cutoff_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $property = app('current_property');

        $block = GroupBlock::create([
            'property_id' => $property->id,
            'block_code' => strtoupper(Str::random(8)),
            'group_name' => $data['group_name'],
            'company_id' => Guest::find($data['guest_id'])?->company_id,
            'check_in' => $data['check_in'],
            'check_out' => $data['check_out'],
            'rooms_count' => $data['rooms_count'],
            'negotiated_rate' => $data['negotiated_rate'] ?? null,
            'cutoff_date' => $data['cutoff_date'] ?? null,
            'status' => 'tentative',
            'notes' => $data['notes'] ?? null,
        ]);

        GroupBlockRoom::create([
            'group_block_id' => $block->id,
            'room_type_id' => $data['room_type_id'],
            'rooms_count' => $data['rooms_count'],
            'rooms_picked_up' => 0,
            'rate' => $data['negotiated_rate'] ?? null,
        ]);

        return redirect()->route('panel.fo.group-blocks.show', $block->id)
            ->with('success', 'Grup block berhasil dibuat.');
    }

    public function show(int $id)
    {
        $property = app('current_property');
        $block = GroupBlock::where('property_id', $property->id)
            ->with(['rooms.roomType', 'company'])
            ->findOrFail($id);

        $availableRooms = Room::where('property_id', $property->id)
            ->where('is_active', true)
            ->where('hk_status', 'clean')
            ->whereDoesntHave('reservationRooms', fn ($q) =>
                $q->whereHas('reservation', fn ($q) =>
                    $q->whereIn('status', ['confirmed', 'checked_in'])
                        ->where('check_in', '<', $block->check_out)
                        ->where('check_out', '>', $block->check_in)
                )
            )
            ->with('roomType')
            ->orderBy('floor')->orderBy('number')
            ->get();

        $guests = Guest::where('property_id', $property->id)->orderBy('first_name')->get();
        $roomTypes = RoomType::where('property_id', $property->id)->where('is_active', true)->orderBy('name')->get();
        $ratePlans = RatePlan::where('property_id', $property->id)->where('is_active', true)->orderBy('name')->get();

        return view('panel.fo.group-blocks.show', compact('block', 'availableRooms', 'guests', 'roomTypes', 'ratePlans'));
    }

    public function addRoom(Request $request, int $id)
    {
        $block = GroupBlock::where('property_id', app('current_property')->id)->findOrFail($id);

        $data = $request->validate([
            'room_type_id' => ['required', 'integer', 'exists:room_types,id'],
            'rooms_count' => ['required', 'integer', 'min:1'],
            'rate' => ['nullable', 'numeric', 'min:0'],
        ]);

        $existing = GroupBlockRoom::where('group_block_id', $block->id)
            ->where('room_type_id', $data['room_type_id'])
            ->first();

        if ($existing) {
            $existing->increment('rooms_count', $data['rooms_count']);
            if ($data['rate']) {
                $existing->update(['rate' => $data['rate']]);
            }
        } else {
            GroupBlockRoom::create([
                'group_block_id' => $block->id,
                'room_type_id' => $data['room_type_id'],
                'rooms_count' => $data['rooms_count'],
                'rooms_picked_up' => 0,
                'rate' => $data['rate'] ?? null,
            ]);
        }

        $block->increment('rooms_count', $data['rooms_count']);

        return back()->with('success', 'Kamar ditambahkan ke grup block.');
    }

    public function removeRoom(int $blockId, int $roomId)
    {
        $block = GroupBlock::where('property_id', app('current_property')->id)->findOrFail($blockId);
        $room = GroupBlockRoom::where('group_block_id', $block->id)->findOrFail($roomId);

        $block->decrement('rooms_count', $room->rooms_count);
        $room->delete();

        return back()->with('success', 'Kamar dihapus dari grup block.');
    }

    public function confirm(int $id)
    {
        $block = GroupBlock::where('property_id', app('current_property')->id)
            ->with('rooms')
            ->findOrFail($id);

        if ($block->status !== 'tentative') {
            return back()->with('error', 'Hanya block tentative yang bisa dikonfirmasi.');
        }

        $block->update(['status' => 'definite']);

        return back()->with('success', 'Grup block dikonfirmasi. Semua kamar siap dipick-up.');
    }

    public function pickup(int $id)
    {
        $property = app('current_property');
        $block = GroupBlock::where('property_id', $property->id)
            ->with(['rooms.roomType'])
            ->findOrFail($id);

        return view('panel.sales.group-blocks-pickup', compact('block'));
    }

    public function releaseUnpicked(int $id)
    {
        $property = app('current_property');
        $block = GroupBlock::where('property_id', $property->id)
            ->with('rooms')
            ->findOrFail($id);

        app(\App\Services\Fo\GroupBlockService::class)->releaseUnpickedRooms($block);

        return back()->with('success', 'Kamar yang belum di-pickup berhasil direlease ke inventory.');
    }
}
