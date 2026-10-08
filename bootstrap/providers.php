<?php

use App\Providers\AccountingServiceProvider;
use App\Providers\AppServiceProvider;
use App\Providers\IntegrationServiceProvider;
use App\Providers\LicenseServiceProvider;
use App\Providers\PseoServiceProvider;

return [
    AppServiceProvider::class,
    LicenseServiceProvider::class,
    IntegrationServiceProvider::class,
    AccountingServiceProvider::class,
    PseoServiceProvider::class,
];
