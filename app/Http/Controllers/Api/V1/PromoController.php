<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\PromoCode;
use App\Services\Promo\PromoService;
use Illuminate\Http\Request;

class PromoController extends Controller
{
    public function __construct(private PromoService $svc) {}

    public function index(Request $request)
    {
        $property = $request->user()->property;
        $promos = PromoCode::where('property_id', $property->id)
            ->withCount('usages')
            ->orderByDesc('created_at')
            ->paginate(20);
        return response()->json($promos);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code'           => 'required|string|max:50',
            'discount_type'  => 'required|in:pct,fixed',
            'discount_value' => 'required|numeric|min:0',
            'valid_from'     => 'required|date',
            'valid_until'    => 'required|date|after_or_equal:valid_from',
            'max_uses'       => 'nullable|integer|min:1',
            'min_nights'     => 'nullable|integer|min:1',
            'rules'          => 'nullable|array',
        ]);

        $promo = PromoCode::create([
            ...$data,
            'property_id' => $request->user()->property->id,
            'used_count'  => 0,
            'is_active'   => true,
        ]);

        return response()->json($promo, 201);
    }

    public function update(Request $request, int $id)
    {
        $property = $request->user()->property;
        $promo = PromoCode::where('property_id', $property->id)->findOrFail($id);
        $data = $request->validate([
            'is_active'    => 'boolean',
            'valid_until'  => 'date',
            'max_uses'     => 'nullable|integer|min:1',
        ]);
        $promo->update($data);
        return response()->json($promo);
    }

    public function destroy(Request $request, int $id)
    {
        $property = $request->user()->property;
        $promo = PromoCode::where('property_id', $property->id)->findOrFail($id);
        $promo->delete();
        return response()->noContent();
    }

    public function validate(Request $request)
    {
        $data = $request->validate([
            'code'        => 'required|string',
            'check_in'    => 'required|date',
            'check_out'   => 'required|date|after:check_in',
            'total_amount'=> 'required|numeric|min:0',
        ]);

        $property = $request->user()->property;
        $result = $this->svc->validate($property, $data['code'], $data);
        return response()->json($result);
    }
}
