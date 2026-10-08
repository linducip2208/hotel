<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => ':memory:']);
DB::purge();
\Artisan::call('migrate', ['--force' => true]);

$property = App\Models\Property::create(['name' => 'W', 'slug' => 'w-'.uniqid(), 'region_code' => 'ID-JK', 'is_active' => true]);
$rt = App\Models\RoomType::create(['property_id' => $property->id, 'code' => 'D', 'name' => 'D', 'slug' => 'd', 'max_occupancy' => 2, 'base_rate' => 500000, 'is_active' => true]);
$ra = App\Models\Room::create(['property_id' => $property->id, 'room_type_id' => $rt->id, 'number' => 'A', 'hk_status' => 'clean', 'fo_status' => 'vacant', 'is_active' => true]);
$rb = App\Models\Room::create(['property_id' => $property->id, 'room_type_id' => $rt->id, 'number' => 'B', 'hk_status' => 'clean', 'fo_status' => 'vacant', 'is_active' => true]);
$rp = App\Models\RatePlan::create(['property_id' => $property->id, 'code' => 'BAR', 'name' => 'BAR', 'is_active' => true]);

$svc = app(App\Services\Fo\ReservationService::class);
try {
    $r = $svc->createWalkIn($property, [$ra, $rb], ['first_name' => 'X', 'email' => 'x@x.com'], now()->addDay()->endOfDay(), null);
    echo 'OK rooms='.$r->rooms()->count().' total='.$r->total_room.PHP_EOL;
} catch (Throwable $e) {
    echo get_class($e).': '.$e->getMessage().PHP_EOL;
    echo $e->getTraceAsString().PHP_EOL;
}
