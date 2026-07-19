<?php

namespace App\Http\Controllers\Panel\Security;

use App\Http\Controllers\Controller;
use App\Models\IncidentReport;
use App\Services\Security\IncidentService;
use Illuminate\Http\Request;

class IncidentController extends Controller
{
    public function __construct(protected IncidentService $service) {}

    public function index(Request $request)
    {
        $property = app('current_property');
        $stats = $this->service->getStats($property);

        $query = IncidentReport::where('property_id', $property->id)
            ->with(['guest', 'room', 'reportedBy', 'resolvedBy'])
            ->latest('incident_date');

        if ($request->filled('severity')) {
            $query->where('severity', $request->severity);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('type')) {
            $query->where('incident_type', $request->type);
        }
        if ($request->filled('from') && $request->filled('to')) {
            $query->whereBetween('incident_date', [$request->from, $request->to]);
        }

        $incidents = $query->paginate(20);

        return view('panel.security.incidents', compact('incidents', 'stats'));
    }

    public function create()
    {
        return view('panel.security.incident-detail', [
            'incident' => new IncidentReport(['incident_date' => now()]),
            'edit' => true,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'incident_type' => 'required|in:guest_injury,guest_illness,theft,property_damage,staff_injury,security,fire,flood,complaint,other',
            'severity' => 'required|in:low,medium,high,critical',
            'location' => 'nullable|string|max:255',
            'incident_date' => 'required|date',
            'reported_by' => 'nullable|string|max:255',
            'guest_id' => 'nullable|exists:guests,id',
            'reservation_id' => 'nullable|exists:reservations,id',
            'room_id' => 'nullable|exists:rooms,id',
            'description' => 'required|string',
            'immediate_actions' => 'nullable|string',
            'witness_name' => 'nullable|string|max:255',
            'witness_contact' => 'nullable|string|max:100',
            'police_report_filed' => 'boolean',
            'insurance_claim_filed' => 'boolean',
        ]);

        $incident = $this->service->create(app('current_property'), $data);
        $this->service->notifyManagement($incident);

        return redirect()->route('panel.security.incidents.show', $incident->id)
            ->with('success', 'Laporan insiden berhasil dibuat. Nomor: ' . $incident->report_number);
    }

    public function show($id)
    {
        $incident = IncidentReport::where('property_id', app('current_property')->id)
            ->with(['guest', 'room', 'reservation', 'reportedBy', 'resolvedBy', 'followups.assignedTo'])
            ->findOrFail($id);

        return view('panel.security.incident-detail', compact('incident'));
    }

    public function edit($id)
    {
        $incident = IncidentReport::where('property_id', app('current_property')->id)->findOrFail($id);
        return view('panel.security.incident-detail', ['incident' => $incident, 'edit' => true]);
    }

    public function update(Request $request, $id)
    {
        $incident = IncidentReport::where('property_id', app('current_property')->id)->findOrFail($id);
        $data = $request->validate([
            'incident_type' => 'required|in:guest_injury,guest_illness,theft,property_damage,staff_injury,security,fire,flood,complaint,other',
            'severity' => 'required|in:low,medium,high,critical',
            'location' => 'nullable|string|max:255',
            'incident_date' => 'required|date',
            'reported_by' => 'nullable|string|max:255',
            'description' => 'required|string',
            'immediate_actions' => 'nullable|string',
            'witness_name' => 'nullable|string|max:255',
            'witness_contact' => 'nullable|string|max:100',
            'police_report_filed' => 'boolean',
            'insurance_claim_filed' => 'boolean',
        ]);
        $incident->update($data);
        return back()->with('success', 'Insiden berhasil diperbarui.');
    }

    public function resolve(Request $request, $id)
    {
        $incident = IncidentReport::where('property_id', app('current_property')->id)->findOrFail($id);
        $data = $request->validate([
            'resolution' => 'required|string',
            'close_permanently' => 'boolean',
        ]);
        $this->service->resolve($incident, $data);
        return back()->with('success', 'Insiden berhasil diselesaikan.');
    }

    public function addFollowup(Request $request, $id)
    {
        $incident = IncidentReport::where('property_id', app('current_property')->id)->findOrFail($id);
        $data = $request->validate([
            'action' => 'required|string',
            'assigned_to_user_id' => 'nullable|exists:users,id',
            'due_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);
        $this->service->addFollowup(app('current_property'), $incident, $data);
        return back()->with('success', 'Tindak lanjut berhasil ditambahkan.');
    }

    public function completeFollowup($id, $followupId)
    {
        $followup = \App\Models\IncidentFollowup::whereHas('incidentReport', fn($q) =>
            $q->where('property_id', app('current_property')->id)
        )->findOrFail($followupId);
        $this->service->completeFollowup($followup);
        return back()->with('success', 'Tindak lanjut selesai.');
    }
}
