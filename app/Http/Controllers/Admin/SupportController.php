<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class SupportController extends Controller
{
    public function tickets() { return view('admin.support.tickets'); }
    public function showTicket(int $id) { return view('admin.support.show'); }
    public function reply(Request $request, int $id) { return back(); }
    public function kb() { return view('admin.support.kb'); }
}
