<?php

namespace App\Http\Controllers\Panel\Pricing;

use App\Http\Controllers\Controller;
use App\Models\RateGroupDiscount;
use App\Models\RatePlan;
use App\Models\RoomType;
use Illuminate\Http\Request;

class GroupDiscountController extends Controller
{
    public function index()
    {
        $propertyId = app('current_property')->id;

        $discounts = RateGroupDiscount::where('property_id', $propertyId)
            ->with(['roomType', 'ratePlan'])
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->get();

        $roomTypes = RoomType::where('property_id', $propertyId)->orderBy('name')->get();
        $ratePlans = RatePlan::where('property_id', $propertyId)->orderBy('name')->get();

        return view('panel.pricing.group-discounts', compact('discounts', 'roomTypes', 'ratePlans'));
    }

    public function store(Request $request)
    {
        $propertyId = app('current_property')->id;

        $data = $request->validate([
            'name' => 'required|string|max:191',
            'code' => 'nullable|string|max:50',
            'discount_type' => 'required|string|in:percent,fixed',
            'discount_value' => 'required|numeric|min:0',
            'room_type_id' => 'nullable|integer|exists:room_types,id',
            'rate_plan_id' => 'nullable|integer|exists:rate_plans,id',
            'min_nights' => 'nullable|integer|min:1',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'is_active' => 'boolean',
        ]);

        RateGroupDiscount::create(array_merge($data, [
            'property_id' => $propertyId,
            'is_active' => $data['is_active'] ?? true,
            'min_nights' => $data['min_nights'] ?? 1,
        ]));

        return back()->with('success', 'Diskon grup ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $discount = RateGroupDiscount::where('property_id', app('current_property')->id)->findOrFail($id);

        $data = $request->validate([
            'name' => 'required|string|max:191',
            'code' => 'nullable|string|max:50',
            'discount_type' => 'required|string|in:percent,fixed',
            'discount_value' => 'required|numeric|min:0',
            'room_type_id' => 'nullable|integer|exists:room_types,id',
            'rate_plan_id' => 'nullable|integer|exists:rate_plans,id',
            'min_nights' => 'nullable|integer|min:1',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
            'is_active' => 'boolean',
        ]);

        $discount->update(array_merge($data, [
            'is_active' => $data['is_active'] ?? true,
            'min_nights' => $data['min_nights'] ?? 1,
        ]));

        return back()->with('success', 'Diskon grup diperbarui.');
    }

    public function destroy($id)
    {
        RateGroupDiscount::where('property_id', app('current_property')->id)->where('id', $id)->delete();

        return back()->with('success', 'Diskon grup dihapus.');
    }
}
