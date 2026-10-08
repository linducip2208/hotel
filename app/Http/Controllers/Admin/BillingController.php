<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TenantInvoice;
use App\Models\TenantSubscription;
use Illuminate\Http\Request;

class BillingController extends Controller
{
    public function index()
    {
        return view('admin.billing.index', [
            'activeCount' => TenantSubscription::where('status', 'active')->count(),
            'trialingCount' => TenantSubscription::where('status', 'trialing')->count(),
            'pastDueCount' => TenantSubscription::where('status', 'past_due')->count(),
            'mrr' => TenantSubscription::where('status', 'active')->sum('price_paid_idr'),
            'outstanding' => (float) TenantInvoice::whereIn('status', ['issued', 'past_due'])->sum('balance'),
        ]);
    }

    public function subscriptions()
    {
        return view('admin.billing.subscriptions', [
            'subscriptions' => TenantSubscription::with(['tenant', 'plan'])
                ->latest()->paginate(30),
        ]);
    }

    public function invoices()
    {
        return view('admin.billing.invoices', [
            'invoices' => TenantInvoice::with('tenant')
                ->orderByDesc('issued_at')
                ->paginate(30),
        ]);
    }

    public function coupons()
    {
        // No coupon storage exists yet — honest empty state, no fake form.
        return view('admin.billing.coupons');
    }

    public function storeCoupon(Request $request)
    {
        return back()->withErrors(['coupon' => 'Sistem kupon belum tersedia pada instalasi ini.']);
    }

    public function failedPayments()
    {
        return view('admin.billing.failed', [
            'failed' => TenantSubscription::with(['tenant', 'plan'])
                ->where('status', 'past_due')
                ->latest()->paginate(30),
            'unpaidInvoices' => TenantInvoice::with('tenant')
                ->whereIn('status', ['issued', 'past_due'])
                ->where('balance', '>', 0)
                ->orderByDesc('due_at')
                ->paginate(30),
        ]);
    }
}
