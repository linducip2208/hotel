<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BillingController extends Controller
{
    public function index() { return view('admin.billing.index'); }
    public function subscriptions() { return view('admin.billing.subscriptions'); }
    public function invoices() { return view('admin.billing.invoices'); }
    public function coupons() { return view('admin.billing.coupons'); }
    public function storeCoupon(Request $request) { return back(); }
    public function failedPayments() { return view('admin.billing.failed'); }
}
