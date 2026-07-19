<?php

namespace App\Console\Commands;

use App\Models\Property;
use App\Services\Compliance\LicenseService;
use Illuminate\Console\Command;

class CheckLicenseExpiry extends Command
{
    protected $signature = 'compliance:check-licenses';
    protected $description = 'Cek izin yang akan kadaluarsa dan update status';

    public function handle(LicenseService $service): int
    {
        $properties = Property::where('is_active', true)->get();
        $totalExpiring = 0;
        $totalExpired = 0;

        foreach ($properties as $property) {
            $result = $service->checkExpiry($property);
            $totalExpiring += $result['expiring'];
            $totalExpired += $result['expired'];
        }

        $this->info("License expiry check complete: {$totalExpiring} expiring soon, {$totalExpired} expired.");
        return self::SUCCESS;
    }
}
