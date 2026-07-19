<?php

namespace Database\Seeders;

use App\Models\Inventory;
use App\Models\Property;
use App\Models\RatePlan;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    private Property $property;
    private RoomType $superior;
    private RoomType $deluxe;
    private RoomType $suite;
    private RatePlan $bar;
    private RatePlan $nrr;

    private array $firstNames = [
        'Budi', 'Agus', 'Dewi', 'Sari', 'Rini', 'Andi', 'Rudi', 'Eko', 'Wati', 'Tono',
        'Siti', 'Ahmad', 'Rina', 'Hendra', 'Ani', 'Bambang', 'Dian', 'Fajar', 'Citra', 'Dedi',
        'Nina', 'Hadi', 'Putri', 'Anton', 'Maya', 'Riko', 'Lina', 'Teguh', 'Yuni', 'Irfan',
        'Fitri', 'Bagus', 'Sinta', 'Wawan', 'Lia', 'Rama', 'Nur', 'Ari', 'Dina', 'Yoga',
        'Rani', 'Reza', 'Tari', 'Adit', 'Vina', 'Galih', 'Nia', 'Dimas', 'Wulan', 'Bayu',
    ];

    private array $lastNames = [
        'Santoso', 'Wijaya', 'Pratama', 'Setiawan', 'Kusuma', 'Hartono', 'Susanto', 'Mahendra',
        'Gunawan', 'Nugroho', 'Purnama', 'Saputra', 'Anggraini', 'Lestari', 'Utami', 'Hidayat',
        'Permata', 'Ramadan', 'Prasetyo', 'Kurniawan', 'Susilowati', 'Wahyuni', 'Handayani',
        'Sudrajat', 'Hermawan', 'Iskandar',
    ];

    private array $domains = [
        'gmail.com', 'yahoo.co.id', 'outlook.com', 'mail.com', 'hotmail.com',
        'gmail.com', 'gmail.com', 'gmail.com', 'yahoo.com', 'gmail.com',
    ];

    private array $cities = [
        'Jakarta', 'Surabaya', 'Bandung', 'Yogyakarta', 'Semarang', 'Medan', 'Bali',
        'Makassar', 'Palembang', 'Malang', 'Solo', 'Bogor', 'Depok', 'Tangerang', 'Bekasi',
    ];

    private array $addressPrefixes = [
        'Jl. Merdeka', 'Jl. Sudirman', 'Jl. Thamrin', 'Jl. Diponegoro', 'Jl. Gatot Subroto',
        'Jl. Pahlawan', 'Jl. Veteran', 'Jl. Ahmad Yani', 'Jl. Kartini', 'Jl. Pemuda',
        'Gg. Mawar', 'Komplek Permata', 'Perum Griya', 'Jl. Mangga', 'Jl. Melati',
    ];

    private array $specialRequests = [
        null, null, null,
        'Extra pillow please', 'Twin bed preferred', 'Non-smoking room', 'High floor',
        'Quiet room away from elevator', 'Early check-in if possible', 'Late check-out 2pm',
        'Honeymoon decoration', 'Birthday surprise', 'Airport pickup needed',
        'Halal food only', 'Connecting rooms', 'Wheelchair accessible',
    ];

    private array $sources = [
        'direct', 'direct', 'direct', 'direct', 'direct',
        'walk_in', 'walk_in', 'ota:booking', 'ota:agoda', 'ota:traveloka',
        'corporate', 'travel_agent',
    ];

    private array $addonOptions = [
        ['code' => 'BKFST', 'name' => 'Breakfast Buffet', 'price' => 85000],
        ['code' => 'XFER', 'name' => 'Airport Transfer', 'price' => 150000],
        ['code' => 'LNDRY', 'name' => 'Laundry Service', 'price' => 50000],
        ['code' => 'MINI', 'name' => 'Minibar Package', 'price' => 75000],
        ['code' => 'SPA60', 'name' => 'Spa 60min', 'price' => 200000],
        ['code' => 'DINNER', 'name' => 'Romantic Dinner', 'price' => 250000],
    ];

    public function run(): void
    {
        $this->command?->info('Seeding 1 year of business-flow demo data (1000 reservations)...');

        $this->command?->info('Clearing existing demo data...');
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('folio_payments')->truncate();
        DB::table('folio_charges')->truncate();
        DB::table('folios')->truncate();
        DB::table('reservation_rooms')->truncate();
        DB::table('reservation_addons')->truncate();
        DB::table('booking_access_tokens')->truncate();
        DB::table('reservations')->truncate();
        DB::table('guests')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        $this->createPropertyAndRooms();
        $this->createRatePlansAndRates();
        $this->createInventory();
        $this->createGuestsAndReservations();

        $this->command?->info('Done. Login at /login with superadmin@demohotel.id / password123');
    }

    private function createPropertyAndRooms(): void
    {
        $this->property = Property::firstOrCreate(
            ['name' => 'Demo Hotel Mandala'],
            [
                'slug' => 'demo-hotel-mandala',
                'region_code' => 'ID-YO',
                'province' => 'D.I. Yogyakarta',
                'city' => 'Yogyakarta',
                'address_line1' => 'Jl. Malioboro 100',
                'star_rating' => 4,
                'total_rooms' => 100,
                'is_active' => true,
            ]
        );

        $superiorPhotos = [
            'https://images.unsplash.com/photo-1611892440504-42a792e24d32?auto=format&fit=crop&w=1600&q=80',
            'https://images.unsplash.com/photo-1505693416388-ac5ce068fe85?auto=format&fit=crop&w=1600&q=80',
            'https://images.unsplash.com/photo-1566665797739-1674de7a421a?auto=format&fit=crop&w=1600&q=80',
            'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=1600&q=80',
        ];
        $deluxePhotos = [
            'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?auto=format&fit=crop&w=1600&q=80',
            'https://images.unsplash.com/photo-1590490360182-c33d57733427?auto=format&fit=crop&w=1600&q=80',
            'https://images.unsplash.com/photo-1591088398332-8a7791972843?auto=format&fit=crop&w=1600&q=80',
            'https://images.unsplash.com/photo-1578683010236-d716f9a3f461?auto=format&fit=crop&w=1600&q=80',
        ];
        $suitePhotos = [
            'https://images.unsplash.com/photo-1618773928121-c32242e63f39?auto=format&fit=crop&w=1600&q=80',
            'https://images.unsplash.com/photo-1582719508461-905c673771fd?auto=format&fit=crop&w=1600&q=80',
            'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?auto=format&fit=crop&w=1600&q=80',
            'https://images.unsplash.com/photo-1602002418816-5c0aeef426aa?auto=format&fit=crop&w=1600&q=80',
        ];

        $this->superior = RoomType::updateOrCreate(
            ['property_id' => $this->property->id, 'code' => 'SUP'],
            [
                'name' => 'Superior Room', 'slug' => 'superior',
                'description' => 'Cozy 24m² room with twin or queen bed, modern furnishings, and city ambience.',
                'max_occupancy' => 2, 'max_adults' => 2, 'base_rate' => 450000,
                'amenities' => ['wifi', 'ac', 'tv', 'minibar'],
                'photos' => $superiorPhotos,
                'size_sqm' => 24, 'bed_config' => 'Queen / Twin',
                'is_active' => true,
            ]
        );

        $this->deluxe = RoomType::updateOrCreate(
            ['property_id' => $this->property->id, 'code' => 'DLX'],
            [
                'name' => 'Deluxe Room', 'slug' => 'deluxe',
                'description' => 'Spacious 32m² with king bed, city view balcony, and elegant interior.',
                'max_occupancy' => 3, 'max_adults' => 2, 'extra_bed_capacity' => 1,
                'base_rate' => 650000,
                'amenities' => ['wifi', 'ac', 'tv', 'minibar', 'safe', 'balcony'],
                'photos' => $deluxePhotos,
                'size_sqm' => 32, 'bed_config' => 'King', 'view' => 'City',
                'is_active' => true,
            ]
        );

        $this->suite = RoomType::updateOrCreate(
            ['property_id' => $this->property->id, 'code' => 'STE'],
            [
                'name' => 'Junior Suite', 'slug' => 'junior-suite',
                'description' => 'Premium 48m² suite with separate living area, bathtub, and panoramic city view.',
                'max_occupancy' => 4, 'max_adults' => 2, 'extra_bed_capacity' => 2,
                'base_rate' => 1200000,
                'amenities' => ['wifi', 'ac', 'tv', 'minibar', 'safe', 'balcony', 'living_room', 'bathtub'],
                'photos' => $suitePhotos,
                'size_sqm' => 48, 'bed_config' => 'King', 'view' => 'City',
                'is_active' => true,
            ]
        );

        // 10 lantai × 10 kamar = 100 total
        // Per lantai: 6 Superior (n=1..6), 3 Deluxe (n=7..9), 1 Junior Suite (n=10)
        for ($floor = 1; $floor <= 10; $floor++) {
            for ($n = 1; $n <= 10; $n++) {
                $type = $n <= 6 ? $this->superior : ($n <= 9 ? $this->deluxe : $this->suite);
                Room::firstOrCreate(
                    ['property_id' => $this->property->id, 'number' => sprintf('%d%02d', $floor, $n)],
                    [
                        'room_type_id' => $type->id,
                        'floor' => $floor,
                        'view' => $type->view,
                        'hk_status' => 'clean',
                        'fo_status' => 'vacant',
                        'is_active' => true,
                    ]
                );
            }
        }

        $this->command?->info('Property + 100 rooms ready.');
    }

    private function createRatePlansAndRates(): void
    {
        $this->bar = RatePlan::firstOrCreate(
            ['property_id' => $this->property->id, 'code' => 'BAR'],
            ['name' => 'Best Available Rate', 'is_refundable' => true, 'is_active' => true]
        );
        $this->nrr = RatePlan::firstOrCreate(
            ['property_id' => $this->property->id, 'code' => 'NRR'],
            ['name' => 'Non-Refundable', 'is_refundable' => false, 'is_active' => true]
        );

        $days = range(-370, 100);
        $chunks = array_chunk($days, 60);

        foreach ([$this->superior, $this->deluxe, $this->suite] as $rt) {
            foreach ($chunks as $chunk) {
                $records = [];
                foreach ($chunk as $d) {
                    $date = now()->addDays($d)->toDateString();
                    $dt = Carbon::parse($date);
                    $weekend = in_array($dt->dayOfWeek, [5, 6]);
                    $month = (int) $dt->month;
                    $multiplier = $weekend ? 1.15 : 1.0;
                    if (in_array($month, [6, 7, 12, 1])) {
                        $multiplier *= 1.08;
                    }

                    $records[] = [
                        'property_id' => $this->property->id,
                        'room_type_id' => $rt->id,
                        'rate_plan_id' => $this->bar->id,
                        'date' => $date,
                        'amount' => (int) round($rt->base_rate * $multiplier),
                        'currency' => 'IDR',
                    ];
                    $records[] = [
                        'property_id' => $this->property->id,
                        'room_type_id' => $rt->id,
                        'rate_plan_id' => $this->nrr->id,
                        'date' => $date,
                        'amount' => (int) round($rt->base_rate * 0.85 * $multiplier),
                        'currency' => 'IDR',
                    ];
                }
                DB::table('rates')->upsert($records, ['property_id', 'room_type_id', 'rate_plan_id', 'date'], ['amount']);
            }
        }

        $this->command?->info('1-year rate calendar ready (-370 to +100 days).');
    }

    private function createInventory(): void
    {
        $now = now();
        // Hitung total real per room type dari tabel rooms — tidak hardcode.
        $countsByType = Room::where('property_id', $this->property->id)
            ->where('is_active', true)
            ->selectRaw('room_type_id, COUNT(*) as c')
            ->groupBy('room_type_id')
            ->pluck('c', 'room_type_id');

        foreach ([$this->superior, $this->deluxe, $this->suite] as $rt) {
            $total = (int) ($countsByType[$rt->id] ?? 0);
            if ($total === 0) continue;
            $records = [];
            for ($d = -370; $d <= 100; $d++) {
                $records[] = [
                    'property_id' => $this->property->id,
                    'room_type_id' => $rt->id,
                    'date' => $now->copy()->addDays($d)->toDateString(),
                    'total' => $total,
                    'sold' => 0,
                    'blocked' => 0,
                    'out_of_order' => 0,
                    'overbooking_allowance' => 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            foreach (array_chunk($records, 200) as $chunk) {
                DB::table('inventory')->upsert($chunk, ['property_id', 'room_type_id', 'date'], ['total']);
            }
        }
    }

    private function createGuestsAndReservations(): void
    {
        $propertyId = $this->property->id;
        $rooms = Room::where('property_id', $propertyId)->get();
        $defaultUserId = $this->seedDemoUsers($propertyId);

        $roomTypeRooms = [
            'SUP' => $rooms->where('room_type_id', $this->superior->id)->values(),
            'DLX' => $rooms->where('room_type_id', $this->deluxe->id)->values(),
            'STE' => $rooms->where('room_type_id', $this->suite->id)->values(),
        ];

        $today = now()->startOfDay();
        $totalReservations = 1000;

        // === Step 1: Pre-compute reservation params and sort chronologically by booked_at ===
        $params = [];
        for ($i = 0; $i < $totalReservations; $i++) {
            // Distribution across the year:
            //   ~75% past stays (-365..-1), ~5% in-house (-3..+3), ~20% future (+4..+95)
            $r = rand(1, 100);
            $offset = match (true) {
                $r <= 75  => -rand(1, 365),
                $r <= 80  =>  rand(-3, 3),
                default   =>  rand(4, 95),
            };
            $checkIn = $today->copy()->addDays($offset);

            // Length-of-stay distribution: most stays are 1-3 nights, long stays rare
            $rn = rand(1, 100);
            $nights = match (true) {
                $rn <= 35 => 1,
                $rn <= 65 => 2,
                $rn <= 82 => 3,
                $rn <= 92 => 4,
                $rn <= 97 => 5,
                $rn <= 99 => 7,
                default   => rand(8, 14),
            };
            $checkOut = $checkIn->copy()->addDays($nights);

            // Lead time: when the booking was made
            $leadDays = rand(1, 90);
            $bookedAt = $checkIn->copy()->subDays($leadDays)->setTime(rand(8, 22), rand(0, 59));
            // Bookings can never be made in the future (relative to today)
            if ($bookedAt->gt($today->copy()->endOfDay())) {
                $bookedAt = $today->copy()->subDays(rand(0, 30))->setTime(rand(8, 22), rand(0, 59));
            }

            // Room type: 60% SUP, 30% DLX, 10% STE
            $rrt = rand(1, 100);
            $rt = $rrt <= 60 ? $this->superior : ($rrt <= 90 ? $this->deluxe : $this->suite);

            $params[] = [
                'checkIn' => $checkIn, 'checkOut' => $checkOut, 'nights' => $nights,
                'bookedAt' => $bookedAt, 'leadDays' => $leadDays, 'rt' => $rt,
            ];
        }

        usort($params, fn ($a, $b) => $a['bookedAt']->timestamp <=> $b['bookedAt']->timestamp);

        // === Step 2: Build guests + reservations chronologically ===
        $guestBatch = $reservationBatch = $resRoomBatch = $folioBatch = $chargeBatch = $paymentBatch = [];
        $repeatPool = [];     // existing guest IDs available for reuse
        $guestId = $reservationId = $folioId = 0;
        $yearCounter = [];    // ref counter scoped per booking year

        $this->command?->info('Generating 1000 reservations across 1 year...');
        $bar = $this->command?->getOutput()?->createProgressBar($totalReservations);

        foreach ($params as $p) {
            $reservationId++;
            $bar?->advance();

            $checkIn   = $p['checkIn'];
            $checkOut  = $p['checkOut'];
            $nights    = $p['nights'];
            $bookedAt  = $p['bookedAt'];
            $leadDays  = $p['leadDays'];
            $rt        = $p['rt'];

            // ----- Guest: ~25% repeat once pool is warm, otherwise create a new one -----
            if (count($repeatPool) > 30 && rand(1, 100) <= 25) {
                $useGuestId = $repeatPool[array_rand($repeatPool)];
            } else {
                $guestId++;
                $useGuestId = $guestId;
                $fn = $this->firstNames[array_rand($this->firstNames)];
                $ln = $this->lastNames[array_rand($this->lastNames)];
                $email = strtolower($fn . '.' . $ln . $guestId . '@' . $this->domains[array_rand($this->domains)]);
                $isVip = rand(1, 100) <= 5;
                $city = $this->cities[array_rand($this->cities)];

                $guestBatch[] = [
                    'property_id' => $propertyId,
                    'first_name' => $fn,
                    'last_name' => $ln,
                    'email' => $email,
                    'phone' => '+628' . rand(10, 99) . '-' . rand(1000, 9999) . '-' . rand(1000, 9999),
                    'country' => 'ID',
                    'nationality' => 'ID',
                    'gender' => ['Male', 'Female'][rand(0, 1)],
                    'address_line1' => $this->addressPrefixes[array_rand($this->addressPrefixes)] . ' No. ' . rand(1, 200),
                    'city' => $city,
                    'province' => $city,
                    'postal_code' => (string) rand(10000, 99999),
                    'is_vip' => $isVip,
                    'preferences' => json_encode($isVip ? ['high_floor', 'late_checkout', 'extra_pillow'] : []),
                    'marketing_consent' => rand(1, 100) <= 60,
                    'created_at' => $bookedAt,
                    'updated_at' => $bookedAt,
                ];
                $repeatPool[] = $guestId;
            }

            // ----- Status by date relative to today -----
            if ($checkOut->lt($today)) {
                $rr = rand(1, 100);
                $status = $rr <= 88 ? 'checked_out' : ($rr <= 95 ? 'cancelled' : 'no_show');
            } elseif ($checkIn->lt($today) && $checkOut->gte($today)) {
                $status = 'checked_in';
            } elseif ($checkIn->equalTo($today)) {
                $status = rand(1, 100) <= 70 ? 'checked_in' : 'confirmed';
            } else {
                $rr = rand(1, 100);
                $status = $rr <= 80 ? 'confirmed' : ($rr <= 92 ? 'tentative' : 'cancelled');
            }

            // ----- Pricing with weekend + season bump -----
            $weekend = in_array($checkIn->dayOfWeek, [5, 6]);
            $month = (int) $checkIn->month;
            $multiplier = $weekend ? 1.15 : 1.0;
            if (in_array($month, [6, 7, 12, 1])) {
                $multiplier *= 1.08;
            }
            $ratePerNight   = (int) round($rt->base_rate * $multiplier);
            $totalRoom      = $ratePerNight * $nights;
            $serviceCharge  = (int) round($totalRoom * 0.05);
            $taxTotal       = (int) round(($totalRoom + $serviceCharge) * 0.11);

            $addonTotal = 0;
            $addonPicked = null;
            if (rand(1, 100) <= 35 && !in_array($status, ['cancelled', 'no_show'])) {
                $addonPicked = $this->addonOptions[array_rand($this->addonOptions)];
                $addonTotal = $addonPicked['price'];
            }

            $grandTotal = $totalRoom + $addonTotal + $serviceCharge + $taxTotal;
            $discount = rand(1, 100) <= 8 ? (int) round($grandTotal * rand(5, 15) / 100) : 0;
            $grandTotal -= $discount;

            // ----- Lifecycle timestamps -----
            $checkedInAt  = in_array($status, ['checked_in', 'checked_out'])
                ? $checkIn->copy()->setTime(rand(13, 20), rand(0, 59)) : null;
            $checkedOutAt = $status === 'checked_out'
                ? $checkOut->copy()->setTime(rand(7, 12), rand(0, 59)) : null;
            $cancelledAt  = $status === 'cancelled'
                ? $bookedAt->copy()->addDays(rand(0, max(0, $leadDays - 1)))->setTime(rand(8, 21), rand(0, 59))
                : null;
            $source = $this->sources[array_rand($this->sources)];

            // Deposit for in-house guest (paid at check-in)
            $deposit = $status === 'checked_in' ? (int) round($grandTotal * rand(30, 70) / 100) : 0;

            $balance = match ($status) {
                'checked_out', 'cancelled', 'no_show' => 0,
                'checked_in' => $grandTotal - $deposit,
                default      => $grandTotal,
            };

            // Per-year ref counter for realistic numbering across the historical span
            $year = $bookedAt->year;
            $yearCounter[$year] = ($yearCounter[$year] ?? 0) + 1;
            $ref = 'HMS-' . $year . '-' . str_pad((string) $yearCounter[$year], 6, '0', STR_PAD_LEFT);

            $reservationUpdatedAt = $cancelledAt ?? $checkedOutAt ?? $checkedInAt ?? $bookedAt;

            $reservationBatch[] = [
                'property_id' => $propertyId,
                'ref' => $ref,
                'primary_guest_id' => $useGuestId,
                'source' => $source,
                'check_in' => $checkIn->toDateString(),
                'check_out' => $checkOut->toDateString(),
                'nights' => $nights,
                'adults' => rand(1, 2),
                'children' => rand(0, 2),
                'status' => $status,
                'total_room' => $totalRoom,
                'total_addons' => $addonTotal,
                'service_charge' => $serviceCharge,
                'tax_total' => $taxTotal,
                'grand_total' => $grandTotal,
                'balance' => $balance,
                'discount_amount' => $discount,
                'special_requests' => $this->specialRequests[array_rand($this->specialRequests)],
                'checked_in_at' => $checkedInAt,
                'checked_out_at' => $checkedOutAt,
                'cancelled_at' => $cancelledAt,
                'cancellation_reason' => $status === 'cancelled' ? 'Guest cancelled' : null,
                'cancellation_penalty' => $status === 'cancelled' && rand(1, 100) <= 30 ? (int) round($ratePerNight * 0.5) : 0,
                'created_by_user_id' => $defaultUserId,
                'created_at' => $bookedAt,
                'updated_at' => $reservationUpdatedAt,
            ];

            $roomPool = $roomTypeRooms[$rt->code];
            $assignedRoom = $roomPool->random();

            $resRoomBatch[] = [
                'reservation_id' => $reservationId,
                'room_type_id' => $rt->id,
                'rate_plan_id' => rand(1, 100) <= 75 ? $this->bar->id : $this->nrr->id,
                'room_id' => $assignedRoom->id,
                'check_in' => $checkIn->toDateString(),
                'check_out' => $checkOut->toDateString(),
                'adults' => rand(1, 2),
                'children' => rand(0, 2),
                'subtotal' => $totalRoom,
                'status' => in_array($status, ['checked_in', 'checked_out']) ? 'occupied' : 'booked',
                'created_at' => $bookedAt,
                'updated_at' => $reservationUpdatedAt,
            ];

            // Folios + charges + payments only exist for stays that actually opened
            if (in_array($status, ['checked_in', 'checked_out'])) {
                $folioId++;
                $folioStatus    = $status === 'checked_out' ? 'closed' : 'open';
                $totalPayments  = $status === 'checked_out' ? $grandTotal : $deposit;
                $folioBalance   = $grandTotal - $totalPayments;

                $folioBatch[] = [
                    'property_id' => $propertyId,
                    'reservation_id' => $reservationId,
                    'guest_id' => $useGuestId,
                    'folio_no' => 'FOL-' . $checkIn->format('Ymd') . '-' . str_pad((string) $folioId, 5, '0', STR_PAD_LEFT),
                    'type' => 'guest',
                    'status' => $folioStatus,
                    'total_charges' => $grandTotal,
                    'total_payments' => $totalPayments,
                    'balance' => $folioBalance,
                    'opened_at' => $checkedInAt,
                    'closed_at' => $checkedOutAt,
                    'cashier_id' => $defaultUserId,
                    'created_at' => $checkedInAt,
                    'updated_at' => $checkedOutAt ?? $checkedInAt,
                ];

                $chargeStamp = $checkIn->copy()->setTime(15, rand(0, 59));

                $chargeBatch[] = [
                    'folio_id' => $folioId,
                    'property_id' => $propertyId,
                    'charge_date' => $checkIn->toDateString(),
                    'description' => 'Room Charge - ' . $rt->name . ' (' . $checkIn->format('d M') . ' - ' . $checkOut->format('d M Y') . ')',
                    'category' => 'room',
                    'qty' => $nights,
                    'unit_price' => $ratePerNight,
                    'amount' => $totalRoom,
                    'is_taxable' => true,
                    'tax_code' => null,
                    'tax_amount' => 0,
                    'is_void' => false,
                    'void_reason' => null,
                    'source_type' => 'night_audit',
                    'source_ref' => null,
                    'source_type_id' => null,
                    'posted_by_user_id' => $defaultUserId,
                    'created_at' => $chargeStamp,
                    'updated_at' => $chargeStamp,
                ];

                $chargeBatch[] = [
                    'folio_id' => $folioId,
                    'property_id' => $propertyId,
                    'charge_date' => $checkIn->toDateString(),
                    'description' => 'Service Charge 5%',
                    'category' => 'service_charge',
                    'qty' => 1,
                    'unit_price' => $serviceCharge,
                    'amount' => $serviceCharge,
                    'is_taxable' => false,
                    'tax_code' => null,
                    'tax_amount' => 0,
                    'is_void' => false,
                    'void_reason' => null,
                    'source_type' => 'night_audit',
                    'source_ref' => null,
                    'source_type_id' => null,
                    'posted_by_user_id' => $defaultUserId,
                    'created_at' => $chargeStamp,
                    'updated_at' => $chargeStamp,
                ];

                $chargeBatch[] = [
                    'folio_id' => $folioId,
                    'property_id' => $propertyId,
                    'charge_date' => $checkIn->toDateString(),
                    'description' => 'PPN 11%',
                    'category' => 'ppn',
                    'qty' => 1,
                    'unit_price' => $taxTotal,
                    'amount' => $taxTotal,
                    'is_taxable' => false,
                    'tax_code' => 'PPN_OUT',
                    'tax_amount' => $taxTotal,
                    'is_void' => false,
                    'void_reason' => null,
                    'source_type' => 'night_audit',
                    'source_ref' => null,
                    'source_type_id' => null,
                    'posted_by_user_id' => $defaultUserId,
                    'created_at' => $chargeStamp,
                    'updated_at' => $chargeStamp,
                ];

                if ($addonPicked && $addonTotal > 0) {
                    $addonOffset = rand(0, max(0, $nights - 1));
                    $addonStamp = $checkIn->copy()->addDays($addonOffset)->setTime(rand(7, 21), rand(0, 59));
                    $chargeBatch[] = [
                        'folio_id' => $folioId,
                        'property_id' => $propertyId,
                        'charge_date' => $addonStamp->toDateString(),
                        'description' => $addonPicked['name'],
                        'category' => 'fnb',
                        'qty' => 1,
                        'unit_price' => $addonPicked['price'],
                        'amount' => $addonPicked['price'],
                        'is_taxable' => true,
                        'tax_code' => null,
                        'tax_amount' => 0,
                        'is_void' => false,
                        'void_reason' => null,
                        'source_type' => 'pos_order',
                        'source_ref' => null,
                        'source_type_id' => null,
                        'posted_by_user_id' => $defaultUserId,
                        'created_at' => $addonStamp,
                        'updated_at' => $addonStamp,
                    ];
                }

                if ($status === 'checked_out') {
                    $paymentBatch[] = [
                        'folio_id' => $folioId,
                        'property_id' => $propertyId,
                        'payment_date' => $checkOut->toDateString(),
                        'amount' => $grandTotal,
                        'method' => ['cash', 'card', 'transfer', 'qris'][rand(0, 3)],
                        'provider_id' => null,
                        'reference_no' => 'PAY-' . Str::random(8),
                        'mdr_amount' => 0,
                        'gateway_payload' => null,
                        'is_void' => false,
                        'void_reason' => null,
                        'cashier_id' => $defaultUserId,
                        'shift_id' => null,
                        'created_at' => $checkedOutAt,
                        'updated_at' => $checkedOutAt,
                    ];
                } else { // checked_in
                    $paymentBatch[] = [
                        'folio_id' => $folioId,
                        'property_id' => $propertyId,
                        'payment_date' => $checkIn->toDateString(),
                        'amount' => $deposit,
                        'method' => ['cash', 'card', 'qris'][rand(0, 2)],
                        'provider_id' => null,
                        'reference_no' => 'DEP-' . Str::random(8),
                        'mdr_amount' => 0,
                        'gateway_payload' => null,
                        'is_void' => false,
                        'void_reason' => null,
                        'cashier_id' => $defaultUserId,
                        'shift_id' => null,
                        'created_at' => $checkedInAt,
                        'updated_at' => $checkedInAt,
                    ];
                }
            }

            // Flush batches every 100 reservations to keep memory bounded and FK order intact
            if ($reservationId % 100 === 0) {
                $this->flushBatches($guestBatch, $reservationBatch, $resRoomBatch, $folioBatch, $chargeBatch, $paymentBatch);
                $guestBatch = $reservationBatch = $resRoomBatch = $folioBatch = $chargeBatch = $paymentBatch = [];
            }
        }

        $bar?->finish();
        $this->command?->newLine();

        $this->flushBatches($guestBatch, $reservationBatch, $resRoomBatch, $folioBatch, $chargeBatch, $paymentBatch);

        $occupiedRoomIds = DB::table('reservation_rooms')
            ->join('reservations', 'reservation_rooms.reservation_id', '=', 'reservations.id')
            ->where('reservations.property_id', $this->property->id)
            ->where('reservations.status', 'checked_in')
            ->pluck('reservation_rooms.room_id');
        DB::table('rooms')->whereIn('id', $occupiedRoomIds)->update(['fo_status' => 'occupied']);

        $this->command?->info(sprintf(
            'Done: %d unique guests, %d reservations, %d folios across %d booking years.',
            $guestId,
            $reservationId,
            $folioId,
            count($yearCounter),
        ));
    }

    /**
     * Seed one demo user per role with predictable credentials.
     * Returns the super_owner user id (used as default cashier/created_by FK).
     */
    private function seedDemoUsers(int $propertyId): int
    {
        $demoUsers = [
            ['email' => 'superadmin@demohotel.id', 'name' => 'Super Admin',     'role' => 'super_owner'],
            ['email' => 'manager@demohotel.id',    'name' => 'Hotel Manager',   'role' => 'manager'],
            ['email' => 'fo@demohotel.id',         'name' => 'Front Office',    'role' => 'front_office'],
            ['email' => 'cashier@demohotel.id',    'name' => 'FO Cashier',      'role' => 'cashier'],
            ['email' => 'housekeeping@demohotel.id','name' => 'Housekeeping',   'role' => 'housekeeping'],
            ['email' => 'pos@demohotel.id',        'name' => 'POS Cashier',     'role' => 'pos_cashier'],
            ['email' => 'accountant@demohotel.id', 'name' => 'Accountant',      'role' => 'accountant'],
            ['email' => 'auditor@demohotel.id',    'name' => 'Auditor',         'role' => 'auditor'],
            ['email' => 'sales@demohotel.id',      'name' => 'Sales & Marketing','role' => 'sales_marketing'],
            ['email' => 'it@demohotel.id',         'name' => 'IT Admin',        'role' => 'it_admin'],
        ];

        $superOwnerId = null;
        foreach ($demoUsers as $u) {
            $user = User::updateOrCreate(
                ['email' => $u['email']],
                [
                    'name' => $u['name'],
                    'password' => bcrypt('password123'),
                    'property_id' => $propertyId,
                    'is_active' => true,
                ]
            );
            try {
                $user->syncRoles([$u['role']]);
            } catch (\Throwable $e) {
                // Role may not exist yet if RolesAndPermissionsSeeder hasn't run — non-fatal.
            }
            if ($u['role'] === 'super_owner') {
                $superOwnerId = $user->id;
            }
        }

        // Backwards-compat alias used by older docs and prior seeds.
        $legacy = User::updateOrCreate(
            ['email' => 'admin@demohotel.id'],
            [
                'name' => 'Admin Hotel',
                'password' => bcrypt('password123'),
                'property_id' => $propertyId,
                'is_active' => true,
            ]
        );
        try { $legacy->syncRoles(['super_owner']); } catch (\Throwable $e) {}

        return $superOwnerId ?? $legacy->id;
    }

    private function flushBatches(array &$guests, array &$reservations, array &$resRooms, array &$folios, array &$charges, array &$payments): void
    {
        if (!empty($guests)) {
            DB::table('guests')->insert($guests);
        }
        if (!empty($reservations)) {
            DB::table('reservations')->insert($reservations);
        }
        if (!empty($resRooms)) {
            DB::table('reservation_rooms')->insert($resRooms);
        }
        if (!empty($folios)) {
            DB::table('folios')->insert($folios);
        }
        if (!empty($charges)) {
            DB::table('folio_charges')->insert($charges);
        }
        if (!empty($payments)) {
            DB::table('folio_payments')->insert($payments);
        }
    }
}
