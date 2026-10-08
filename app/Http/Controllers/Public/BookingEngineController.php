<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Inventory;
use App\Models\Property;
use App\Models\Reservation;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\SearchKeyword;
use App\Services\Fo\PricingService;
use App\Services\Fo\ReservationService;
use App\Services\Fo\ReservationValidationException;
use App\Services\Payment\PaymentGatewayService;
use App\Services\Promo\PromoService;
use App\Services\PublicPropertyResolver;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class BookingEngineController extends Controller
{
    public function search()
    {
        return view('public.booking.search', ['property' => app(PublicPropertyResolver::class)->resolve()]);
    }

    public function results(Request $request, PricingService $pricing)
    {
        $data = $request->validate([
            'check_in' => ['required', 'date', 'after_or_equal:today'],
            'check_out' => ['required', 'date', 'after:check_in'],
            'adults' => ['required', 'integer', 'min:1', 'max:10'],
            'children' => ['nullable', 'integer', 'min:0', 'max:10'],
        ]);

        $property = app(PublicPropertyResolver::class)->resolve() ?? abort(404);
        $checkIn = Carbon::parse($data['check_in'])->startOfDay();
        $checkOut = Carbon::parse($data['check_out'])->startOfDay();
        $nights = $checkIn->diffInDays($checkOut);

        if ($request->filled('q')) {
            SearchKeyword::track($property->id, $request->query('q'), 'booking', 0);
        }

        $roomTypes = RoomType::where('property_id', $property->id)
            ->where('is_active', true)
            ->where('max_occupancy', '>=', $data['adults'] + ($data['children'] ?? 0))
            ->orderBy('display_order')
            ->get()
            ->map(function (RoomType $rt) use ($property, $pricing, $checkIn, $checkOut, $nights) {
                // Rate plans are priced by the SAME engine used at commit time.
                $rt->rate_plans = $pricing->ratePlansForStay($property, $rt->id, $checkIn, $checkOut, $nights);
                $rt->available_units = $this->availableUnits($property->id, $rt->id, $checkIn, $checkOut);

                $sellable = $rt->rate_plans->filter(fn ($p) => $p['sellable']);
                $rt->from_price = $sellable->min('total');
                $rt->sellable = $rt->available_units > 0 && $sellable->isNotEmpty();

                return $rt;
            });

        return view('public.booking.results', compact('roomTypes', 'data', 'nights', 'property'));
    }

    public function checkout(Request $request, PricingService $pricing, PaymentGatewayService $gateway)
    {
        $data = $request->validate([
            'check_in' => ['required', 'date', 'after_or_equal:today'],
            'check_out' => ['required', 'date', 'after:check_in'],
            'room_type_id' => ['required', 'integer'],
            'rate_plan_id' => ['required', 'integer'],
            'adults' => ['required', 'integer', 'min:1', 'max:10'],
            'children' => ['nullable', 'integer', 'min:0', 'max:10'],
        ]);

        $property = app(PublicPropertyResolver::class)->resolve() ?? abort(404);

        // Ownership check — a forged ID from another property must never render.
        $roomType = RoomType::where('property_id', $property->id)->where('is_active', true)->find($data['room_type_id']);
        if (! $roomType) {
            return redirect()->route('booking.results', Arr::only($data, ['check_in', 'check_out', 'adults', 'children']))
                ->withErrors(['room_type_id' => 'Tipe kamar tidak tersedia.']);
        }

        // Server-side quote — the browser price is never trusted.
        $checkIn = Carbon::parse($data['check_in'])->startOfDay();
        $checkOut = Carbon::parse($data['check_out'])->startOfDay();

        // Optional promo code (property-scoped, validated server-side).
        $promo = null;
        if (! empty($data['promo_code'])) {
            $promo = app(PromoService::class)->lookup($data['promo_code'], $property->id);
            if (! $promo) {
                return redirect()->route('booking.results', Arr::only($data, ['check_in', 'check_out', 'adults', 'children']))
                    ->withErrors(['promo_code' => 'Kode promo tidak valid atau sudah kedaluwarsa.']);
            }
        }

        $quote = $pricing->quote($property, (int) $roomType->id, (int) $data['rate_plan_id'], $checkIn, $checkOut, (int) $data['adults'], (int) ($data['children'] ?? 0), $promo);

        $plan = collect($pricing->ratePlansForStay($property, $roomType->id, $checkIn, $checkOut, $quote['nights']))
            ->firstWhere('id', (int) $data['rate_plan_id']);
        if (! $plan || ! $plan['sellable']) {
            return redirect()->route('booking.results', Arr::only($data, ['check_in', 'check_out', 'adults', 'children']))
                ->withErrors(['rate_plan_id' => $plan['block_reason'] ?? 'Rate plan tidak dapat dijual untuk tanggal ini.']);
        }

        $available = $this->availableUnits($property->id, $roomType->id, $checkIn, $checkOut);
        if ($available <= 0) {
            return redirect()->route('booking.results', Arr::only($data, ['check_in', 'check_out', 'adults', 'children']))
                ->withErrors(['room_type_id' => 'Kamar untuk tanggal ini sudah penuh.']);
        }

        $paymentMethods = $gateway->availablePaymentMethods($property->id);
        $paymentMethodGroups = collect($paymentMethods)->groupBy('group');

        return view('public.booking.checkout', [
            'property' => $property,
            'data' => $data,
            'roomType' => $roomType,
            'plan' => $plan,
            'promo' => $promo,
            'quote' => $quote,
            'paymentMethodGroups' => $paymentMethodGroups,
        ]);
    }

    public function submit(Request $request, ReservationService $svc, PaymentGatewayService $payment, PricingService $pricing)
    {
        // Public endpoint: throttle per IP to deter abuse.
        $key = 'booking-submit:'.($request->ip() ?? 'unknown');
        if (RateLimiter::tooManyAttempts($key, 10)) {
            return back()->withInput()->withErrors(['email' => 'Terlalu banyak percobaan. Silakan tunggu beberapa menit.']);
        }
        RateLimiter::hit($key, 300);

        $data = $request->validate([
            'check_in' => 'required|date|after_or_equal:today',
            'check_out' => 'required|date|after:check_in',
            'room_type_id' => 'required|integer',
            'rate_plan_id' => 'required|integer',
            'adults' => 'required|integer|min:1|max:10',
            'children' => 'nullable|integer|min:0|max:10',
            'promo_code' => 'nullable|string|max:40',
            'first_name' => 'required|string|max:100',
            'last_name' => 'nullable|string|max:100',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:30',
            'special_requests' => 'nullable|string|max:1000',
            'payment_method' => 'required|string',
            'agree_policy' => 'accepted',
        ]);

        $property = app(PublicPropertyResolver::class)->resolve() ?? abort(404);

        // Ownership checks (anti cross-property / forged input).
        if (! RoomType::where('property_id', $property->id)->whereKey($data['room_type_id'])->where('is_active', true)->exists()) {
            return back()->withInput()->withErrors(['room_type_id' => 'Tipe kamar tidak tersedia.']);
        }

        $checkIn = Carbon::parse($data['check_in'])->startOfDay();
        $checkOut = Carbon::parse($data['check_out'])->startOfDay();

        // Promo resolved server-side (never trust the browser's discount).
        $promo = null;
        if (! empty($data['promo_code'])) {
            $promo = app(PromoService::class)->lookup($data['promo_code'], $property->id);
            if (! $promo) {
                return back()->withInput()->withErrors(['promo_code' => 'Kode promo tidak valid atau sudah kedaluwarsa.']);
            }
        }

        // Re-quote server-side (authoritative price).
        $quote = $pricing->quote($property, (int) $data['room_type_id'], (int) $data['rate_plan_id'], $checkIn, $checkOut, (int) $data['adults'], (int) ($data['children'] ?? 0), $promo);
        if (! empty($quote['restrictions'])) {
            return back()->withInput()->withErrors(['rate_plan_id' => 'Rate plan tidak dapat dijual untuk tanggal ini: '.implode('; ', $quote['restrictions'])]);
        }

        // Availability checked AGAIN immediately before commit (inside create()).
        try {
            $reservation = $svc->create([
                'property_id' => $property->id,
                'check_in' => $data['check_in'],
                'check_out' => $data['check_out'],
                'rooms' => [[
                    'room_type_id' => (int) $data['room_type_id'],
                    'rate_plan_id' => (int) $data['rate_plan_id'],
                    'adults' => (int) $data['adults'],
                    'children' => (int) ($data['children'] ?? 0),
                ]],
                'primary_guest' => [
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name'] ?? null,
                    'email' => $data['email'],
                    'phone' => $data['phone'],
                ],
                'special_requests' => $data['special_requests'] ?? null,
                'promo_code' => $data['promo_code'] ?? null,
                'source' => 'direct',
            ]);
        } catch (ReservationValidationException $e) {
            return back()->withInput()->withErrors(['check_in' => $e->getMessage()]);
        }

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
            ->with(['primaryGuest', 'rooms.roomType', 'rooms.ratePlan', 'property', 'folios.payments'])
            ->firstOrFail();

        $paymentError = session('payment_error');
        $paymentSuccess = session('payment_success');

        return view('public.booking.confirmation', compact('reservation', 'paymentError', 'paymentSuccess'));
    }

    /**
     * Provider webhook. SECURITY: an unverified callback is REJECTED — no
     * payment is updated, a security event is logged, and the provider gets 403.
     */
    public function paymentCallback(Request $request, string $ref, PaymentGatewayService $payment)
    {
        $payload = $request->all();
        $headers = $request->header();

        $verified = $payment->verifyCallback($ref, $payload, $headers);

        if (! $verified) {
            Log::channel('audit')->warning('SECURITY: payment callback rejected — invalid signature', [
                'ref' => $ref,
                'ip' => $request->ip(),
                'has_order_id' => isset($payload['order_id']),
            ]);

            return response()->json(['ok' => false, 'error' => 'Invalid signature'], 403);
        }

        $status = $payload['transaction_status']
            ?? $payload['status']
            ?? $payload['result']['status']
            ?? 'pending';

        // handleCallback is idempotent + state-transition safe.
        $payment->handleCallback($ref, $status, $payload);

        return response()->json(['ok' => true, 'verified' => true]);
    }

    public function paymentReturn(Request $request, string $ref, PaymentGatewayService $payment)
    {
        $reservation = $payment->handlePaymentReturn($ref);

        return redirect()->route('booking.confirmation', $reservation->ref)
            ->with('payment_return', true);
    }

    /**
     * Min available units across the stay (physical inventory; falls back to
     * room count when no inventory rows are configured).
     */
    protected function availableUnits(int $propertyId, int $roomTypeId, Carbon $checkIn, Carbon $checkOut): int
    {
        $rows = Inventory::where('property_id', $propertyId)
            ->where('room_type_id', $roomTypeId)
            ->whereBetween('date', [$checkIn->toDateString(), $checkOut->copy()->subDay()->toDateString()])
            ->get();

        if ($rows->isEmpty()) {
            return (int) Room::where('property_id', $propertyId)
                ->where('room_type_id', $roomTypeId)
                ->where('is_active', true)
                ->where('fo_status', '!=', 'out_of_order')
                ->count();
        }

        return (int) $rows->min(fn ($i) => $i->available);
    }
}
