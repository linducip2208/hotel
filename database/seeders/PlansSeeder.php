<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlansSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            ['name' => 'Starter', 'slug' => 'starter', 'monthly_price_idr' => null, 'per_room_price_idr' => 35000, 'max_rooms' => 20, 'max_users' => 5, 'max_properties' => 1, 'features' => ['ota_basic', 'pos_basic'], 'is_default_signup' => true, 'display_order' => 1],
            ['name' => 'Growth', 'slug' => 'growth', 'monthly_price_idr' => null, 'per_room_price_idr' => 50000, 'max_rooms' => 50, 'max_users' => 15, 'max_properties' => 1, 'features' => ['ota_full', 'channel_manager', 'ai_basic'], 'display_order' => 2],
            ['name' => 'Pro', 'slug' => 'pro', 'monthly_price_idr' => null, 'per_room_price_idr' => 70000, 'max_rooms' => 150, 'max_users' => null, 'max_properties' => 3, 'features' => ['everything', 'priority_support', 'ai_premium'], 'display_order' => 3],
            ['name' => 'Enterprise', 'slug' => 'enterprise', 'monthly_price_idr' => null, 'per_room_price_idr' => null, 'max_rooms' => null, 'max_users' => null, 'max_properties' => null, 'features' => ['custom', 'dedicated_support', 'sla'], 'display_order' => 4],
        ];
        foreach ($plans as $p) {
            Plan::updateOrCreate(['slug' => $p['slug']], array_merge($p, ['is_active' => true]));
        }
    }
}
