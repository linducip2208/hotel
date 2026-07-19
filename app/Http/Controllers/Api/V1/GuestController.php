<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Folio;
use App\Models\Guest;
use App\Models\Property;
use Illuminate\Http\Request;

class GuestController extends Controller
{
    private function property(): Property
    {
        return app('current_property') ?? Property::orderBy('id')->firstOrFail();
    }

    public function index(Request $request)
    {
        $query = Guest::where('property_id', $this->property()->id)
            ->when($request->query('q'), fn ($q, $s) => $q->where(function ($sub) use ($s) {
                $sub->where('first_name', 'like', "%{$s}%")
                    ->orWhere('last_name', 'like', "%{$s}%")
                    ->orWhere('email', 'like', "%{$s}%")
                    ->orWhere('phone', 'like', "%{$s}%");
            }))
            ->when($request->query('is_vip'), fn ($q) => $q->where('is_vip', true))
            ->latest();

        return response()->json($query->paginate((int) $request->query('per_page', 50)));
    }

    public function show(int $id)
    {
        $guest = Guest::where('property_id', $this->property()->id)->with(['profile', 'loyaltyMember.tier'])->findOrFail($id);
        return response()->json($guest);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name'        => 'required|string|max:100',
            'last_name'         => 'nullable|string|max:100',
            'email'             => 'nullable|email|max:191',
            'phone'             => 'nullable|string|max:30',
            'country'           => 'nullable|string|size:2',
            'nationality'       => 'nullable|string|size:2',
            'date_of_birth'     => 'nullable|date',
            'gender'            => 'nullable|in:male,female,other',
            'id_type'           => 'nullable|in:ktp,passport,sim,kitas',
            'id_number'         => 'nullable|string|max:50',
            'address_line1'     => 'nullable|string|max:191',
            'city'              => 'nullable|string|max:100',
            'province'          => 'nullable|string|max:100',
            'postal_code'       => 'nullable|string|max:12',
            'is_vip'            => 'boolean',
            'marketing_consent' => 'boolean',
            'preferences'       => 'nullable|array',
            'tags'              => 'nullable|array',
            'notes'             => 'nullable|string',
        ]);

        $validated['property_id'] = $this->property()->id;
        $guest = Guest::create($validated);

        return response()->json($guest, 201);
    }

    public function update(Request $request, int $id)
    {
        $guest = Guest::where('property_id', $this->property()->id)->findOrFail($id);

        $validated = $request->validate([
            'first_name'        => 'sometimes|string|max:100',
            'last_name'         => 'nullable|string|max:100',
            'email'             => 'nullable|email|max:191',
            'phone'             => 'nullable|string|max:30',
            'country'           => 'nullable|string|size:2',
            'nationality'       => 'nullable|string|size:2',
            'date_of_birth'     => 'nullable|date',
            'gender'            => 'nullable|in:male,female,other',
            'id_type'           => 'nullable|in:ktp,passport,sim,kitas',
            'id_number'         => 'nullable|string|max:50',
            'address_line1'     => 'nullable|string|max:191',
            'city'              => 'nullable|string|max:100',
            'province'          => 'nullable|string|max:100',
            'postal_code'       => 'nullable|string|max:12',
            'is_vip'            => 'boolean',
            'is_blacklisted'    => 'boolean',
            'blacklist_reason'  => 'nullable|string',
            'marketing_consent' => 'boolean',
            'preferences'       => 'nullable|array',
            'tags'              => 'nullable|array',
            'notes'             => 'nullable|string',
        ]);

        $guest->update($validated);
        return response()->json($guest->fresh());
    }

    public function destroy(int $id)
    {
        Guest::where('property_id', $this->property()->id)->findOrFail($id)->delete();
        return response()->json(['deleted' => true]);
    }

    public function stays(int $id)
    {
        $guest = Guest::where('property_id', $this->property()->id)->findOrFail($id);
        return response()->json($guest->reservations()->latest()->paginate(20));
    }

    public function folios(int $id)
    {
        $guest = Guest::where('property_id', $this->property()->id)->findOrFail($id);
        return response()->json(Folio::where('guest_id', $guest->id)->latest()->paginate(20));
    }

    public function profile(int $id)
    {
        $guest = Guest::where('property_id', $this->property()->id)->with('profile')->findOrFail($id);
        return response()->json($guest->profile);
    }
}
