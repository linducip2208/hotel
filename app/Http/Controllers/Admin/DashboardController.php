<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LicenseEvent;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\TenantInvoice;
use App\Models\TenantSubscription;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index()
    {
        $today = now()->startOfDay();
        $weekStart = now()->startOfWeek();
        $monthStart = now()->startOfMonth();

        $tenantCounts = [
            'active'    => Tenant::where('status', 'active')->count(),
            'trial'     => Tenant::where('status', 'trial')->count(),
            'suspended' => Tenant::where('status', 'suspended')->count(),
            'canceled'  => Tenant::where('status', 'canceled')->count(),
        ];

        $subActive = TenantSubscription::where('status', 'active')->count();
        $mrr = (float) TenantSubscription::where('status', 'active')->sum('price_paid_idr');

        $invThisMonth = TenantInvoice::where('created_at', '>=', $monthStart)->count();
        $invPaid = TenantInvoice::where('created_at', '>=', $monthStart)->where('status', 'paid')->count();
        $invUnpaid = TenantInvoice::whereIn('status', ['unpaid','overdue'])->count();
        $revenueMonth = (float) TenantInvoice::where('created_at', '>=', $monthStart)->where('status', 'paid')->sum('grand_total');

        $events = [
            'today' => LicenseEvent::where('created_at', '>=', $today)->count(),
            'week'  => LicenseEvent::where('created_at', '>=', $weekStart)->count(),
        ];

        $stats = [
            // Legacy keys retained for any view that still references them
            'tenants_active'       => $tenantCounts['active'],
            'tenants_trial'        => $tenantCounts['trial'],
            'license_events_today' => $events['today'],

            'tenants'         => $tenantCounts,
            'tenants_total'   => array_sum($tenantCounts),
            'subscriptions'   => $subActive,
            'mrr'             => $mrr,
            'invoices_month'  => $invThisMonth,
            'invoices_paid'   => $invPaid,
            'invoices_unpaid' => $invUnpaid,
            'revenue_month'   => $revenueMonth,
            'license_events'  => $events,
            'plans_total'     => Plan::count(),
        ];

        $recentTenants = Tenant::orderByDesc('created_at')->take(5)->get();
        $recentEvents  = LicenseEvent::orderByDesc('created_at')->take(8)->get();

        return view('admin.dashboard', compact('stats', 'recentTenants', 'recentEvents'));
    }
}
