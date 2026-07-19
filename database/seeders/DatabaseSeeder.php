<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            Pb1RatesSeeder::class,
            LandmarksSeeder::class,
            PlansSeeder::class,
            KbArticlesSeeder::class,
            \Database\Seeders\BlogSeeder::class,
        ]);

        if (app()->environment('local', 'demo')) {
            $this->call(DemoDataSeeder::class);
        }

        // Property-dependent seeders run after demo (or any property exists)
        if (\App\Models\Property::exists()) {
            $this->call([
                ChartOfAccountsSeeder::class,
                DefaultPoliciesSeeder::class,
                DocumentTemplatesSeeder::class,
                MessageTemplatesSeeder::class,
                LoyaltyTiersSeeder::class,
                PaymentPresetsSeeder::class,
            ]);
        }
    }
}
