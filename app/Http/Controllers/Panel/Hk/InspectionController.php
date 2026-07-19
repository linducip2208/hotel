<?php

namespace App\Http\Controllers\Panel\Hk;

use App\Http\Controllers\Controller;
use App\Models\InspectionChecklist;
use App\Models\Room;
use Illuminate\Http\Request;

class InspectionController extends Controller
{
    public function index(Request $request)
    {
        $query = InspectionChecklist::where('property_id', app('current_property')->id)
            ->with('room', 'inspector');

        if ($request->filled('status')) {
            $query->where('overall_status', $request->query('status'));
        }
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->query('date'));
        }

        $inspections = $query->latest()->paginate(25);

        return view('panel.hk.inspection.index', compact('inspections'));
    }

    public function create(int $roomId)
    {
        $room = Room::where('property_id', app('current_property')->id)->findOrFail($roomId);

        $checklistItems = [
            'bed_linen' => 'Bed Linen',
            'towels' => 'Towels',
            'bathroom' => 'Bathroom',
            'floor' => 'Floor',
            'dusting' => 'Dusting',
            'amenities' => 'Amenities',
            'minibar' => 'Minibar',
            'tv_ac' => 'TV & AC',
            'wifi' => 'WiFi',
            'overall_cleanliness' => 'Overall Cleanliness',
        ];

        return view('panel.hk.inspection.create', compact('room', 'checklistItems'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'room_id' => 'required|integer',
            'items' => 'required|array|min:1',
            'items.*.status' => 'required|in:pass,fail',
            'items.*.photo' => 'nullable|string',
            'items.*.comment' => 'nullable|string|max:500',
            'notes' => 'nullable|string',
        ]);

        $items = [];
        $allPassed = true;

        $itemLabels = [
            'bed_linen' => 'Bed Linen', 'towels' => 'Towels', 'bathroom' => 'Bathroom',
            'floor' => 'Floor', 'dusting' => 'Dusting', 'amenities' => 'Amenities',
            'minibar' => 'Minibar', 'tv_ac' => 'TV & AC', 'wifi' => 'WiFi',
            'overall_cleanliness' => 'Overall Cleanliness',
        ];

        foreach ($data['items'] as $key => $itemData) {
            $status = $itemData['status'];
            if ($status === 'fail') $allPassed = false;

            $items[] = [
                'name' => $itemLabels[$key] ?? $key,
                'status' => $status,
                'photo_path' => $itemData['photo'] ?? null,
                'comment' => $itemData['comment'] ?? null,
            ];
        }

        $inspection = InspectionChecklist::create([
            'property_id' => app('current_property')->id,
            'room_id' => $data['room_id'],
            'inspector_id' => auth()->id(),
            'inspected_at' => now(),
            'overall_status' => $allPassed ? 'pass' : 'fail',
            'items' => $items,
            'notes' => $data['notes'] ?? null,
        ]);

        return redirect()->route('panel.hk.inspection.show', $inspection->id)
            ->with('success', 'Inspection completed.');
    }

    public function show(int $id)
    {
        $inspection = InspectionChecklist::where('property_id', app('current_property')->id)
            ->with('room', 'inspector')
            ->findOrFail($id);

        return view('panel.hk.inspection.show', compact('inspection'));
    }

    public function report(Request $request)
    {
        $inspections = InspectionChecklist::where('property_id', app('current_property')->id)
            ->with('room', 'inspector')
            ->latest()
            ->take(100)
            ->get();

        $total = $inspections->count();
        $passed = $inspections->where('overall_status', 'pass')->count();
        $failed = $inspections->where('overall_status', 'fail')->count();
        $passRate = $total > 0 ? round(($passed / $total) * 100) : 0;

        return view('panel.hk.inspection.report', compact('inspections', 'total', 'passed', 'failed', 'passRate'));
    }
}
