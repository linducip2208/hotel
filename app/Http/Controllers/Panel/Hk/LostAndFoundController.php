<?php

namespace App\Http\Controllers\Panel\Hk;

use App\Http\Controllers\Controller;
use App\Models\LostAndFound;
use App\Models\Room;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LostAndFoundController extends Controller
{
    public function index(Request $request)
    {
        $propertyId = app('current_property')->id;

        $items = LostAndFound::where('property_id', $propertyId)
            ->with(['room', 'foundByUser', 'claimedByGuest'])
            ->when($request->query('status'), fn ($q, $s) => $q->where('status', $s))
            ->when($request->query('location'), fn ($q, $l) => $q->where('found_location', 'like', "%{$l}%"))
            ->when($request->query('date_from'), fn ($q, $d) => $q->whereDate('found_date', '>=', $d))
            ->when($request->query('date_to'), fn ($q, $d) => $q->whereDate('found_date', '<=', $d))
            ->orderByDesc('found_date')
            ->paginate(25);

        $statusCounts = LostAndFound::where('property_id', $propertyId)
            ->selectRaw("status, count(*) as total")
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('panel.hk.lost-found.index', compact('items', 'statusCounts'));
    }

    public function create()
    {
        $rooms = Room::where('property_id', app('current_property')->id)
            ->orderBy('floor')->orderBy('number')->get();

        return view('panel.hk.lost-found.create', compact('rooms'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'description'    => 'required|string|max:500',
            'found_location' => 'nullable|string|max:200',
            'found_date'     => 'required|date',
            'found_by'       => 'nullable|string|max:100',
            'room_id'        => 'nullable|exists:rooms,id',
            'photo'          => 'nullable|image|max:5120',
            'notes'          => 'nullable|string|max:1000',
        ]);

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('lost-found', 'public');
        }

        $data['property_id'] = app('current_property')->id;
        $data['found_by_user_id'] = $request->user()?->id;
        $data['status'] = 'found';

        LostAndFound::create($data);

        return redirect()->route('panel.hk.lost-found.index')
            ->with('success', 'Lost & Found item recorded.');
    }

    public function show(int $id)
    {
        $item = LostAndFound::where('property_id', app('current_property')->id)
            ->with(['room', 'foundByUser', 'claimedByGuest'])
            ->findOrFail($id);

        return view('panel.hk.lost-found.show', compact('item'));
    }

    public function edit(int $id)
    {
        $item = LostAndFound::where('property_id', app('current_property')->id)->findOrFail($id);
        $rooms = Room::where('property_id', app('current_property')->id)
            ->orderBy('floor')->orderBy('number')->get();

        return view('panel.hk.lost-found.edit', compact('item', 'rooms'));
    }

    public function update(Request $request, int $id)
    {
        $item = LostAndFound::where('property_id', app('current_property')->id)->findOrFail($id);

        $data = $request->validate([
            'description'    => 'required|string|max:500',
            'found_location' => 'nullable|string|max:200',
            'found_date'     => 'required|date',
            'room_id'        => 'nullable|exists:rooms,id',
            'photo'          => 'nullable|image|max:5120',
            'notes'          => 'nullable|string|max:1000',
        ]);

        if ($request->hasFile('photo')) {
            if ($item->photo_path) {
                Storage::disk('public')->delete($item->photo_path);
            }
            $data['photo_path'] = $request->file('photo')->store('lost-found', 'public');
        }

        $item->update($data);

        return redirect()->route('panel.hk.lost-found.show', $id)
            ->with('success', 'Item updated.');
    }

    public function claim(int $id, Request $request)
    {
        $item = LostAndFound::where('property_id', app('current_property')->id)->findOrFail($id);

        $data = $request->validate([
            'claimed_by_guest_id' => 'nullable|exists:guests,id',
            'notes'               => 'nullable|string|max:500',
        ]);

        $item->update([
            'status'              => 'claimed',
            'claimed_by_guest_id' => $data['claimed_by_guest_id'] ?? null,
            'claimed_date'        => now(),
            'notes'               => $data['notes'] ?? $item->notes,
        ]);

        return back()->with('success', 'Item marked as claimed.');
    }

    public function returnToOwner(int $id)
    {
        $item = LostAndFound::where('property_id', app('current_property')->id)->findOrFail($id);

        $item->update([
            'status'       => 'returned',
            'claimed_date' => now(),
        ]);

        return back()->with('success', 'Item marked as returned to owner.');
    }

    public function dispose(int $id)
    {
        $item = LostAndFound::where('property_id', app('current_property')->id)->findOrFail($id);

        $item->update(['status' => 'disposed']);

        return back()->with('success', 'Item marked as disposed.');
    }

    public function exportCsv()
    {
        $items = LostAndFound::where('property_id', app('current_property')->id)
            ->with(['room', 'foundByUser', 'claimedByGuest'])
            ->orderByDesc('found_date')
            ->get();

        $filename = 'lost-found-export-'.Carbon::now()->format('YmdHis').'.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($items) {
            $fh = fopen('php://output', 'w');
            fputcsv($fh, ['ID', 'Description', 'Location Found', 'Date Found', 'Room', 'Found By', 'Status', 'Claimed By', 'Claimed Date', 'Notes']);

            foreach ($items as $item) {
                fputcsv($fh, [
                    $item->id,
                    $item->description,
                    $item->found_location,
                    $item->found_date?->toDateString(),
                    $item->room?->number,
                    $item->foundByUser?->name,
                    $item->status,
                    $item->claimedByGuest?->full_name,
                    $item->claimed_date?->toDateString(),
                    $item->notes,
                ]);
            }
            fclose($fh);
        };

        return response()->stream($callback, 200, $headers);
    }
}
