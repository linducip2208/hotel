<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LicenseEvent;
use Illuminate\Http\Request;

class LicenseController extends Controller
{
    public function index() { return view('admin.licenses.index', ['events' => LicenseEvent::latest()->paginate(50)]); }
    public function show(int $id) { return view('admin.licenses.show', ['event' => LicenseEvent::findOrFail($id)]); }
    public function create() { return view('admin.licenses.create'); }
    public function store(Request $request) { return back(); }
    public function edit(int $id) { return back(); }
    public function update(Request $request, int $id) { return back(); }
    public function destroy(int $id) { return back(); }
    public function revoke(int $id) { return back(); }
    public function extend(int $id) { return back(); }
    public function regenerate(int $id) { return back(); }
}
