<?php

namespace Database\Seeders;

use App\Models\LoyaltyTier;
use App\Models\Property;
use Illuminate\Database\Seeder;

class LoyaltyTiersSeeder extends Seeder
{
    public function run(): void
    {
        Property::each(function (Property $p) {
            $tiers = [
                ['name' => 'Silver', 'slug' => 'silver', 'points_threshold' => 0, 'rate_discount_pct' => 0, 'benefits' => ['member_rate', 'birthday_email'], 'display_order' => 1],
                ['name' => 'Gold', 'slug' => 'gold', 'points_threshold' => 5000, 'rate_discount_pct' => 5, 'benefits' => ['member_rate', 'birthday_email', 'late_checkout', 'welcome_drink'], 'display_order' => 2],
                ['name' => 'Platinum', 'slug' => 'platinum', 'points_threshold' => 25000, 'rate_discount_pct' => 10, 'benefits' => ['member_rate', 'birthday_email', 'late_checkout', 'welcome_drink', 'room_upgrade', 'breakfast_free'], 'display_order' => 3],
            ];
            foreach ($tiers as $t) {
                LoyaltyTier::updateOrCreate(
                    ['property_id' => $p->id, 'slug' => $t['slug']],
                    $t
                );
            }
        });
    }
}
