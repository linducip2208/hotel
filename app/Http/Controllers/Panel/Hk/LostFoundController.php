<?php

namespace App\Http\Controllers\Panel\Hk;

use App\Http\Controllers\Controller;
use App\Models\Guest;
use App\Models\Room;
use App\Services\Hk\LostFoundService;
use Illuminate\Http\Request;

class LostFoundController extends Controller
{
    public function __construct(protected LostFoundService $service) {}

    public function index(Request $request)
    {
        $propertyId = app('current_property')->id;

        $items = $this->service->list($propertyId, $request->only([
            'status', 'category', 'search', 'date_from', 'date_to',
        ]));

        $statusCounts = $this->service->statusCounts($propertyId);

        $rooms = Room::where('property_id', $propertyId)
            ->orderBy('floor')->orderBy('number')->get();

        return view('panel.hk.lost-found', compact('items', 'statusCounts', 'rooms'));
    }

    public function store(Request $request)
    {
        $propertyId = app('current_property')->id;

        $data = $request->validate([
            'name'           => 'required|string|max:200',
            'category'       => 'required|string|in:electronics,clothing,jewelry,documents,toys,keys,other',
            'description'    => 'nullable|string|max:1000',
            'location_found' => 'nullable|string|max:200',
            'room_id'        => 'nullable|exists:rooms,id',
            'found_at'       => 'nullable|date',
            'storage_location' => 'nullable|string|max:200',
            'disposal_days'  => 'nullable|integer|min:1|max:365',
            'photos_upload'  => 'nullable|array',
            'photos_upload.*'=> 'image|max:5120',
        ]);

        $this->service->store($propertyId, $data, $request->user()->id);

        return back()->with('success', 'Barang hilang berhasil dicatat.');
    }

    public function show(int $id)
    {
        $propertyId = app('current_property')->id;
        $item = $this->service->find($id, $propertyId);
        $guests = Guest::where('property_id', $propertyId)->orderBy('first_name')->get();

        return view('panel.hk.lost-found-show', compact('item', 'guests'));
    }

    public function update(Request $request, int $id)
    {
        $propertyId = app('current_property')->id;

        $data = $request->validate([
            'name'           => 'required|string|max:200',
            'category'       => 'required|string|in:electronics,clothing,jewelry,documents,toys,keys,other',
            'description'    => 'nullable|string|max:1000',
            'location_found' => 'nullable|string|max:200',
            'room_id'        => 'nullable|exists:rooms,id',
            'found_at'       => 'nullable|date',
            'storage_location' => 'nullable|string|max:200',
            'disposal_days'  => 'nullable|integer|min:1|max:365',
            'photos_upload'  => 'nullable|array',
            'photos_upload.*'=> 'image|max:5120',
        ]);

        $this->service->update($id, $propertyId, $data);

        return back()->with('success', 'Barang hilang berhasil diperbarui.');
    }

    public function claim(int $id, Request $request)
    {
        $propertyId = app('current_property')->id;

        $data = $request->validate([
            'claimed_by_guest_id' => 'nullable|exists:guests,id',
            'claim_verified_by'   => 'nullable|string|max:100',
        ]);

        $this->service->claim($id, $propertyId, $data);

        return back()->with('success', 'Barang ditandai sebagai diklaim.');
    }

    public function dispose(int $id)
    {
        $this->service->dispose($id, app('current_property')->id);
        return back()->with('success', 'Barang ditandai sebagai dibuang.');
    }

    public function donate(int $id)
    {
        $this->service->donate($id, app('current_property')->id);
        return back()->with('success', 'Barang ditandai sebagai disumbangkan.');
    }

    public function returnToOwner(int $id)
    {
        $this->service->returnToOwner($id, app('current_property')->id);
        return back()->with('success', 'Barang ditandai sebagai dikembalikan.');
    }

    public function quickAction(int $id, Request $request)
    {
        $action = $request->input('action');
        $propertyId = app('current_property')->id;

        match ($action) {
            'claim'   => $this->service->claim($id, $propertyId, $request->only(['claimed_by_guest_id', 'claim_verified_by'])),
            'dispose' => $this->service->dispose($id, $propertyId),
            'donate'  => $this->service->donate($id, $propertyId),
            'return'  => $this->service->returnToOwner($id, $propertyId),
            default   => null,
        };

        return back()->with('success', 'Aksi berhasil dilakukan.');
    }
}
