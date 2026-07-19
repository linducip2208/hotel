<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class TelemetryController extends Controller
{
    public function index() { return view('admin.telemetry.index'); }
    public function errors() { return view('admin.telemetry.errors'); }
    public function health() { return view('admin.telemetry.health'); }
}
