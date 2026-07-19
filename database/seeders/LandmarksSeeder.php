<?php

namespace Database\Seeders;

use App\Models\Landmark;
use Illuminate\Database\Seeder;

class LandmarksSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['malioboro', 'Jalan Malioboro', 'Yogyakarta', 'D.I. Yogyakarta', -7.7925, 110.3653],
            ['borobudur', 'Candi Borobudur', 'Magelang', 'Jawa Tengah', -7.6079, 110.2038],
            ['kuta-beach', 'Pantai Kuta', 'Badung', 'Bali', -8.7184, 115.1686],
            ['ubud-monkey-forest', 'Monkey Forest Ubud', 'Gianyar', 'Bali', -8.5184, 115.2589],
            ['monas', 'Monumen Nasional', 'Jakarta', 'DKI Jakarta', -6.1754, 106.8272],
            ['kemang', 'Kemang', 'Jakarta', 'DKI Jakarta', -6.2604, 106.8137],
            ['malioboro-station', 'Stasiun Yogyakarta', 'Yogyakarta', 'D.I. Yogyakarta', -7.7894, 110.3631],
            ['ngurah-rai-airport', 'Bandara Ngurah Rai', 'Badung', 'Bali', -8.7480, 115.1672],
            ['soekarno-hatta-airport', 'Bandara Soekarno-Hatta', 'Tangerang', 'Banten', -6.1256, 106.6558],
        ];

        foreach ($items as [$slug, $name, $city, $province, $lat, $lng]) {
            Landmark::updateOrCreate(
                ['slug' => $slug],
                ['name' => $name, 'city' => $city, 'province' => $province, 'lat' => $lat, 'lng' => $lng, 'country' => 'ID']
            );
        }
    }
}
