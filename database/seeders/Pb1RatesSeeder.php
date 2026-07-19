<?php

namespace Database\Seeders;

use App\Models\Pb1Rate;
use Illuminate\Database\Seeder;

class Pb1RatesSeeder extends Seeder
{
    public function run(): void
    {
        $regions = [
            ['ID-JK', 'DKI Jakarta', 10.000],
            ['ID-BA-BD', 'Kab. Badung (Bali)', 10.000],
            ['ID-BA-DPS', 'Kota Denpasar', 10.000],
            ['ID-BA-GIA', 'Kab. Gianyar (Ubud)', 10.000],
            ['ID-YO', 'D.I. Yogyakarta', 10.000],
            ['ID-JB-BDG', 'Kota Bandung', 10.000],
            ['ID-JT-SLO', 'Kota Surakarta (Solo)', 10.000],
            ['ID-JI-SBY', 'Kota Surabaya', 10.000],
            ['ID-JI-MLG', 'Kota Malang', 10.000],
            ['ID-SU-MDN', 'Kota Medan', 10.000],
            ['ID-SS-PLB', 'Kota Palembang', 10.000],
            ['ID-KS-BPN', 'Kota Balikpapan', 10.000],
            ['ID-KS-SMD', 'Kota Samarinda', 10.000],
            ['ID-NT-LMK', 'Kota Mataram (Lombok)', 10.000],
            ['ID-MA-MKS', 'Kota Makassar', 10.000],
        ];

        foreach ($regions as [$code, $name, $rate]) {
            Pb1Rate::updateOrCreate(
                ['region_code' => $code, 'effective_from' => '2025-01-01'],
                [
                    'region_name' => $name,
                    'rate' => $rate,
                    'effective_until' => null,
                    'source_law' => 'UU 1/2022 + Perda terkait',
                    'is_active' => true,
                ]
            );
        }
    }
}
