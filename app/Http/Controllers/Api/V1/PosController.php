<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\PosOrder;
use Illuminate\Http\Request;

class PosController extends Controller
{
    public function createOrder(Request $request) { return app(\App\Http\Controllers\Panel\Pos\PosController::class)->createOrder($request); }
    public function updateOrder(Request $request, int $id) { return app(\App\Http\Controllers\Panel\Pos\PosController::class)->updateOrder($request, $id); }
    public function settleOrder(Request $request, int $id) { return app(\App\Http\Controllers\Panel\Pos\PosController::class)->settleOrder($request, $id); }
}
