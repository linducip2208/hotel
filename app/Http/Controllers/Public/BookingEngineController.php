<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\Rate;
use App\Models\Reservation;
use App\Models\RoomType;
use App\Services\Fo\FolioService;
use App\Services\Fo\ReservationService;
use App\Services\Payment\PaymentGatewayService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class BookingEngineController extends Controller
{
    public function search()
    {
        return view('public.booking.search', ['property' => Property::first()]);
    }

    public function results(Request $request)
    {
        $data = $request->validate([
            'check_in' => ['required', 'date', 'after_or_equal:today'],
            'check_out' => ['required', 'date', 'after:check_in'],
            'adults' => ['required', 'integer', 'min:1'],
            'children' => ['nullable', 'integer', 'min:0'],
        ]);

        $property = Property::firstOrFail();
        $checkIn = Carbon::parse($data['check_in']);
        $checkOut = Carbon::parse($data['check_out']);
        $nights = $checkIn->diffInDays($checkOut);

        $roomTypes = RoomType::where('property_id', $property->id)
            ->where('is_active', true)
            ->where('max_occupancy', '>=', $data['adults'] + ($data['children'] ?? 0))
            ->get()
            ->map(function (RoomType $rt) use ($property, $checkIn, $checkOut, $nights) {
                $sumRate = Rate::where('property_id', $property->id)
                    ->where('room_type_id', $rt->id)
                    ->whereBetween('date', [$checkIn->toDateString(), $checkOut->copy()->subDay()->toDateString()])
                    ->where('closed', false)
                    ->sum('amount');
                $rt->total_price = (float) $sumRate ?: $rt->base_rate * $nights;
                return $rt;
            });

        return view('public.booking.results', compact('roomTypes', 'data', 'nights', 'property'));
    }

    public function checkout(Request $request)
    {
        return view('public.booking.checkout', [
            'property' => Property::first(),
            'data' => $request->all(),
        ]);
    }

    public function submit(Request $request, ReservationService $svc, PaymentGatewayService $payment)
    {
        $data = $request->validate([
            'check_in' => 'required|date',
            'check_out' => 'required|date',
            'room_type_id' => 'required|integer',
            'rate_plan_id' => 'required|integer',
            'adults' => 'required|integer|min:1',
            'children' => 'nullable|integer|min:0',
            'first_name' => 'required|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'email' => 'required|email',
            'phone' => 'required|string',
            'special_requests' => 'nullable|string|max:1000',
            'payment_method' => 'required|string|in:bank_transfer,virtual_account,credit_card,qris,ewallet,gopay,shopee_pay,convenience_store,retail_outlet',
        ]);

        $property = Property::firstOrFail();

        $reservation = $svc->create([
            'property_id' => $property->id,
            'check_in' => $data['check_in'],
            'check_out' => $data['check_out'],
            'rooms' => [[
                'room_type_id' => $data['room_type_id'],
                'rate_plan_id' => $data['rate_plan_id'],
                'adults' => $data['adults'],
                'children' => $data['children'] ?? 0,
            ]],
            'primary_guest' => [
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'] ?? null,
                'email' => $data['email'],
                'phone' => $data['phone'],
            ],
            'special_requests' => $data['special_requests'] ?? null,
            'source' => 'direct',
        ]);

        $paymentResult = $payment->createTransaction($reservation, $data['payment_method']);

        if (! $paymentResult['ok'] && empty($paymentResult['redirect_url'])) {
            return redirect()->route('booking.confirmation', $reservation->ref)
                ->with('payment_error', $paymentResult['error'] ?? 'Gagal memproses pembayaran. Silakan coba lagi.');
        }

        if (! empty($paymentResult['redirect_url'])) {
            return redirect()->away($paymentResult['redirect_url']);
        }

        return redirect()->route('booking.confirmation', $reservation->ref)
            ->with('payment_success', true);
    }

    public function confirmation(string $ref)
    {
        $reservation = Reservation::where('ref', $ref)
            ->with(['primaryGuest', 'rooms.roomType', 'property', 'folios.payments'])
            ->firstOrFail();

        $paymentError = session('payment_error');
        $paymentSuccess = session('payment_success');

        return view('public.booking.confirmation', compact('reservation', 'paymentError', 'paymentSuccess'));
    }

    public function paymentCallback(Request $request, string $ref, PaymentGatewayService $payment)
    {
        $payload = $request->all();
        $headers = $request->header();

        $verified = $payment->verifyCallback($ref, $payload, $headers);

        $status = $payload['transaction_status']
            ?? $payload['status']
            ?? $payload['result']['status']
            ?? 'pending';

        if ($verified) {
            $payment->handleCallback($ref, $status, $payload);
        } else {
            $payment->handleCallback($ref, $status, $payload);
        }

        return response()->json(['ok' => true, 'verified' => $verified]);
    }

    public function paymentReturn(Request $request, string $ref, PaymentGatewayService $payment)
    {
        $reservation = $payment->handlePaymentReturn($ref);

        return redirect()->route('booking.confirmation', $reservation->ref)
            ->with('payment_return', true);
    }
}
