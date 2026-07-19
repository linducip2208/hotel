<?php

namespace App\Http\Controllers\Panel\Revenue;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\UpsellOffer;
use App\Models\UpsellPresentation;
use App\Services\Revenue\UpsellService;
use Illuminate\Http\Request;

class UpsellController extends Controller
{
    protected UpsellService $service;

    public function __construct(UpsellService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $property = app('current_property');

        $offers = UpsellOffer::where('property_id', $property->id)
            ->orderBy('type')->orderBy('name')
            ->get();

        $recentActivity = UpsellPresentation::where('property_id', $property->id)
            ->with(['reservation.primaryGuest', 'offer'])
            ->orderByDesc('offered_at')
            ->limit(50)
            ->get();

        return view('panel.revenue.upsells', compact('offers', 'recentActivity'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'in:room_upgrade,late_checkout,spa,dinner,airport_transfer,package'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'min_stay_nights' => ['required', 'integer', 'min:1'],
            'target_guest_tier' => ['nullable', 'string', 'in:hot,warm,cold,all'],
            'timing' => ['required', 'string', 'in:pre_arrival,during_stay,checkin,anytime'],
            'days_before_arrival' => ['nullable', 'integer', 'min:0'],
            'upgrade_to_room_type_id' => ['nullable', 'integer', 'exists:room_types,id'],
            'is_active' => ['boolean'],
        ]);

        $data['property_id'] = app('current_property')->id;

        UpsellOffer::create($data);

        return back()->with('success', 'Penawaran upsell berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $offer = UpsellOffer::where('property_id', app('current_property')->id)->findOrFail($id);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'in:room_upgrade,late_checkout,spa,dinner,airport_transfer,package'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'min_stay_nights' => ['required', 'integer', 'min:1'],
            'target_guest_tier' => ['nullable', 'string', 'in:hot,warm,cold,all'],
            'timing' => ['required', 'string', 'in:pre_arrival,during_stay,checkin,anytime'],
            'days_before_arrival' => ['nullable', 'integer', 'min:0'],
            'upgrade_to_room_type_id' => ['nullable', 'integer', 'exists:room_types,id'],
            'is_active' => ['boolean'],
        ]);

        $offer->update($data);

        return back()->with('success', 'Penawaran upsell berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $offer = UpsellOffer::where('property_id', app('current_property')->id)->findOrFail($id);
        $offer->delete();

        return back()->with('success', 'Penawaran upsell berhasil dihapus.');
    }

    public function reservationUpsells($reservationId)
    {
        $property = app('current_property');
        $reservation = Reservation::where('property_id', $property->id)->with('primaryGuest')->findOrFail($reservationId);

        $eligible = $this->service->getEligibleOffers($reservation);
        $allOffers = UpsellOffer::where('property_id', $property->id)->where('is_active', true)->orderBy('name')->get();

        $presentations = UpsellPresentation::where('reservation_id', $reservation->id)
            ->with(['offer', 'acceptedByUser'])
            ->orderByDesc('offered_at')
            ->get();

        return view('panel.revenue.upsells-reservation', compact('reservation', 'eligible', 'allOffers', 'presentations'));
    }

    public function presentToReservation(Request $request, $reservationId)
    {
        $property = app('current_property');
        $reservation = Reservation::where('property_id', $property->id)->findOrFail($reservationId);

        $data = $request->validate([
            'upsell_offer_ids' => ['required', 'array'],
            'upsell_offer_ids.*' => ['integer', 'exists:upsell_offers,id'],
        ]);

        $presented = [];

        foreach ($data['upsell_offer_ids'] as $offerId) {
            $offer = UpsellOffer::where('property_id', $property->id)->findOrFail($offerId);
            $presented[] = $this->service->presentOffer($reservation, $offer);
        }

        return back()->with('success', count($presented) . ' penawaran telah disajikan ke reservasi.');
    }

    public function accept(Request $request, $id)
    {
        $property = app('current_property');
        $presentation = UpsellPresentation::where('property_id', $property->id)->findOrFail($id);

        $negotiatedPrice = $request->input('negotiated_price');

        $this->service->acceptOffer($presentation, auth()->id(), $negotiatedPrice ? (float) $negotiatedPrice : null);

        return back()->with('success', 'Penawaran diterima. ' . ($negotiatedPrice ? 'Harga negosiasi: Rp ' . number_format($negotiatedPrice, 0, ',', '.') : ''));
    }

    public function decline($id)
    {
        $property = app('current_property');
        $presentation = UpsellPresentation::where('property_id', $property->id)->findOrFail($id);

        $this->service->declineOffer($presentation);

        return back()->with('success', 'Penawaran ditolak.');
    }
}
