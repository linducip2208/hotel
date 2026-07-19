<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\BookingAccessToken;
use App\Models\Reservation;
use Illuminate\Http\Request;

class GuestPortalController extends Controller
{
    public function manageBooking(Request $request, string $ref)
    {
        $reservation = $this->resolve($ref, $request->query('token'));
        return view('portal.manage', compact('reservation'));
    }

    public function preCheckin(Request $request, string $ref)
    {
        $reservation = $this->resolve($ref, $request->query('token'));
        return view('portal.pre-checkin', compact('reservation'));
    }

    public function submitPreCheckin(Request $request, string $ref)
    {
        $reservation = $this->resolve($ref, $request->query('token'));
        $reservation->update(['pre_checkin_complete' => true, 'arrival_time' => $request->input('arrival_time')]);
        return back()->with('status', 'Pre check-in tersimpan.');
    }

    public function selfCheckin(Request $request, string $ref)
    {
        $reservation = $this->resolve($ref, $request->query('token'));
        return view('portal.self-checkin', compact('reservation'));
    }

    public function inStay(string $token)
    {
        $access = BookingAccessToken::where('token_hashed', hash('sha256', $token))->firstOrFail();
        if (! $access->isValid()) abort(403);
        return view('portal.in-stay', ['reservation' => $access->reservation]);
    }

    public function folio(Request $request, string $ref)
    {
        $reservation = $this->resolve($ref, $request->query('token'));
        return view('portal.folio', ['reservation' => $reservation->load('folios.charges', 'folios.payments')]);
    }

    public function review(Request $request, string $ref)
    {
        $reservation = $this->resolve($ref, $request->query('token'));
        return view('portal.review', compact('reservation'));
    }

    public function submitReview(Request $request, string $ref)
    {
        $data = $request->validate([
            'rating' => 'required|integer|between:1,5',
            'comment' => 'nullable|string|max:2000',
            'category_ratings' => 'nullable|array',
        ]);
        $reservation = $this->resolve($ref, $request->query('token'));
        \App\Models\Review::create([
            'property_id' => $reservation->property_id,
            'reservation_id' => $reservation->id,
            'guest_id' => $reservation->primary_guest_id,
            'rating' => $data['rating'],
            'comment' => $data['comment'] ?? null,
            'category_ratings' => $data['category_ratings'] ?? null,
            'is_public' => true,
        ]);
        return back()->with('status', 'Terima kasih atas reviewnya!');
    }

    protected function resolve(string $ref, ?string $token): Reservation
    {
        $reservation = Reservation::where('ref', $ref)->firstOrFail();
        if ($token) {
            $access = BookingAccessToken::where('token_hashed', hash('sha256', $token))->where('reservation_id', $reservation->id)->first();
            if (! $access || ! $access->isValid()) abort(403);
        }
        return $reservation;
    }
}
