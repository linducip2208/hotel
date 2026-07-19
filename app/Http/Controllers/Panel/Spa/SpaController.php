<?php

namespace App\Http\Controllers\Panel\Spa;

use App\Http\Controllers\Controller;
use App\Models\SpaAppointment;
use App\Models\SpaCabin;
use App\Models\SpaMembership;
use App\Models\SpaTherapist;
use App\Models\SpaTreatment;
use App\Services\Spa\SpaService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SpaController extends Controller
{
    public function __construct(protected SpaService $svc) {}

    public function index() { return $this->appointments(request()); }

    public function appointments(Request $request)
    {
        $today = $request->query('date', now()->toDateString());
        $q = SpaAppointment::where('property_id', app('current_property')->id)
            ->whereDate('start_at', $today)
            ->with('treatment', 'therapist', 'cabin', 'guest')
            ->orderBy('start_at');
        if ($search = $request->query('search')) {
            $q->whereHas('guest', fn ($sub) => $sub->where('name', 'like', "%{$search}%"));
        }
        $apps = $q->get();
        $treatments = SpaTreatment::where('property_id', app('current_property')->id)->where('is_active', true)->get();
        $therapists = SpaTherapist::where('property_id', app('current_property')->id)->where('is_active', true)->get();
        $cabins = SpaCabin::where('property_id', app('current_property')->id)->where('is_active', true)->get();
        return view('panel.spa.appointments', compact('apps', 'treatments', 'therapists', 'cabins', 'today'));
    }

    public function book(Request $request)
    {
        $data = $request->validate([
            'treatment_id' => 'required|integer',
            'therapist_id' => 'nullable|integer',
            'cabin_id' => 'nullable|integer',
            'guest_id' => 'nullable|integer',
            'reservation_id' => 'nullable|integer',
            'folio_id' => 'nullable|integer',
            'start_at' => 'required|date',
            'price' => 'nullable|numeric',
        ]);
        $this->svc->book($data);
        return back();
    }

    public function complete(Request $request, int $id)
    {
        $a = SpaAppointment::where('property_id', app('current_property')->id)->findOrFail($id);
        $this->svc->complete($a);
        return back();
    }

    public function cancelAppointment($id)
    {
        $a = SpaAppointment::where('property_id', app('current_property')->id)->findOrFail($id);
        $a->update(['status' => 'cancelled']);
        return back();
    }

    public function destroyAppointment($id)
    {
        $a = SpaAppointment::where('property_id', app('current_property')->id)->findOrFail($id);
        $a->delete();
        return back();
    }

    // ─── Treatments ───────────────────────────────────────────────

    public function treatments(Request $request)
    {
        $q = SpaTreatment::where('property_id', app('current_property')->id);
        if ($search = $request->query('search')) {
            $q->where(fn ($sub) => $sub->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"));
        }
        $treatments = $q->paginate(50);
        return view('panel.spa.treatments', compact('treatments'));
    }

    public function storeTreatment(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'code' => 'required|string',
            'duration_minutes' => 'required|integer|min:15',
            'price' => 'required|numeric',
            'description' => 'nullable|string',
        ]);
        SpaTreatment::create($data + ['property_id' => app('current_property')->id]);
        return back();
    }

    public function updateTreatment(Request $request, $id)
    {
        $t = SpaTreatment::where('property_id', app('current_property')->id)->findOrFail($id);
        $data = $request->validate([
            'name' => 'required|string',
            'code' => 'required|string',
            'duration_minutes' => 'required|integer|min:15',
            'price' => 'required|numeric',
            'description' => 'nullable|string',
        ]);
        $t->update($data);
        return back();
    }

    public function destroyTreatment($id)
    {
        $t = SpaTreatment::where('property_id', app('current_property')->id)->findOrFail($id);
        $t->delete();
        return back();
    }

    // ─── Therapists ───────────────────────────────────────────────

    public function therapists(Request $request)
    {
        $q = SpaTherapist::where('property_id', app('current_property')->id);
        if ($search = $request->query('search')) {
            $q->where('name', 'like', "%{$search}%");
        }
        $therapists = $q->paginate(50);
        return view('panel.spa.therapists', compact('therapists'));
    }

    public function storeTherapist(Request $request)
    {
        $data = $request->validate(['name' => 'required|string', 'gender' => 'nullable|in:M,F']);
        SpaTherapist::create($data + ['property_id' => app('current_property')->id]);
        return back();
    }

    public function updateTherapist(Request $request, $id)
    {
        $tp = SpaTherapist::where('property_id', app('current_property')->id)->findOrFail($id);
        $data = $request->validate(['name' => 'required|string', 'gender' => 'nullable|in:M,F']);
        $tp->update($data);
        return back();
    }

    public function destroyTherapist($id)
    {
        $tp = SpaTherapist::where('property_id', app('current_property')->id)->findOrFail($id);
        $tp->delete();
        return back();
    }

    // ─── Cabins ───────────────────────────────────────────────────

    public function cabins()
    {
        $cabins = SpaCabin::where('property_id', app('current_property')->id)->paginate(50);
        return view('panel.spa.cabins', compact('cabins'));
    }

    public function storeCabin(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'type' => 'required|in:single,couple,vip',
        ]);
        SpaCabin::create($data + ['property_id' => app('current_property')->id]);
        return back();
    }

    public function updateCabin(Request $request, $id)
    {
        $cabin = SpaCabin::where('property_id', app('current_property')->id)->findOrFail($id);
        $data = $request->validate([
            'name' => 'required|string',
            'type' => 'required|in:single,couple,vip',
        ]);
        $data['is_active'] = $request->boolean('is_active');
        $cabin->update($data);
        return back();
    }

    public function destroyCabin($id)
    {
        $cabin = SpaCabin::where('property_id', app('current_property')->id)->findOrFail($id);
        $cabin->delete();
        return back();
    }

    // ─── Memberships ──────────────────────────────────────────────

    public function memberships()
    {
        $memberships = SpaMembership::where('property_id', app('current_property')->id)->with('guest')->paginate(50);
        return view('panel.spa.memberships', compact('memberships'));
    }

    public function storeMembership(Request $request)
    {
        $data = $request->validate([
            'guest_id' => 'required|integer|exists:guests,id',
            'plan_type' => 'required|in:monthly,quarterly,annual',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'price' => 'required|numeric',
            'payment_method' => 'nullable|string',
        ]);
        $data['membership_number'] = 'SPA-' . strtoupper(Str::random(8));
        $data['property_id'] = app('current_property')->id;
        SpaMembership::create($data);
        return back();
    }

    public function updateMembership(Request $request, $id)
    {
        $m = SpaMembership::where('property_id', app('current_property')->id)->findOrFail($id);
        $data = $request->validate([
            'plan_type' => 'required|in:monthly,quarterly,annual',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'price' => 'required|numeric',
            'payment_method' => 'nullable|string',
            'status' => 'nullable|in:active,expired,cancelled',
        ]);
        $data['auto_renew'] = $request->boolean('auto_renew');
        $m->update($data);
        return back();
    }
}
