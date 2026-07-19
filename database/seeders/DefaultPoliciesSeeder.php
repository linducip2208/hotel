<?php

namespace Database\Seeders;

use App\Models\CancellationPolicy;
use App\Models\Property;
use Illuminate\Database\Seeder;

class DefaultPoliciesSeeder extends Seeder
{
    public function run(): void
    {
        Property::each(function (Property $p) {
            $defaults = [
                ['name' => 'Flexible (Free Cancellation)', 'code' => 'flex', 'is_refundable' => true, 'is_default' => true,
                    'rules' => [['days_before' => 1, 'penalty_pct' => 100], ['days_before' => 999, 'penalty_pct' => 0]],
                    'display_text' => 'Free cancellation up to 24 hours before check-in. After that, 100% penalty.'],
                ['name' => 'Moderate', 'code' => 'mod', 'is_refundable' => true, 'is_default' => false,
                    'rules' => [['days_before' => 2, 'penalty_pct' => 100], ['days_before' => 7, 'penalty_pct' => 50], ['days_before' => 999, 'penalty_pct' => 0]],
                    'display_text' => 'Free cancellation up to 7 days before. 50% penalty 2-7 days before. 100% within 48 hours.'],
                ['name' => 'Non-Refundable', 'code' => 'nrr', 'is_refundable' => false, 'is_default' => false,
                    'rules' => [['days_before' => 999, 'penalty_pct' => 100]],
                    'display_text' => 'No refunds. Saved 15% upfront.'],
            ];
            foreach ($defaults as $d) {
                CancellationPolicy::updateOrCreate(
                    ['property_id' => $p->id, 'code' => $d['code']],
                    $d + ['is_active' => true]
                );
            }
        });
    }
}
