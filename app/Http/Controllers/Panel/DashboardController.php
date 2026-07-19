<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\FolioCharge;
use App\Models\HkTask;
use App\Models\Inventory;
use App\Models\JournalLine;
use App\Models\PosOrder;
use App\Models\Reservation;
use App\Services\Approvals\ApprovalService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $property   = app('current_property');
        $today      = now()->toDateString();
        $totalRooms = $property->total_rooms ?: 1;

        $soldToday = (int) Inventory::where('property_id', $property->id)
            ->whereDate('date', $today)->sum('sold');

        $roomRevToday = (float) FolioCharge::where('property_id', $property->id)
            ->whereDate('charge_date', $today)->where('category', 'room')->where('is_void', false)->sum('amount');

        $totalRevToday = (float) FolioCharge::where('property_id', $property->id)
            ->whereDate('charge_date', $today)->where('is_void', false)->sum('amount');

        $arrivalsList = Reservation::where('property_id', $property->id)
            ->whereDate('check_in', $today)->whereIn('status', ['confirmed', 'tentative'])
            ->with('primaryGuest')->orderBy('check_in')->get();

        $departuresList = Reservation::where('property_id', $property->id)
            ->whereDate('check_out', $today)->where('status', 'checked_in')
            ->with('primaryGuest')->orderBy('check_out')->get();

        // 7-day occupancy & revenue trend for sparkline chart
        $trend = collect();
        for ($d = 6; $d >= 0; $d--) {
            $date = Carbon::today()->subDays($d)->toDateString();
            $sold = (int) Inventory::where('property_id', $property->id)->whereDate('date', $date)->sum('sold');
            $rev  = (float) FolioCharge::where('property_id', $property->id)
                ->whereDate('charge_date', $date)->where('is_void', false)->sum('amount');
            $trend->push([
                'date'    => Carbon::parse($date)->format('D'),
                'occ'     => round(($sold / $totalRooms) * 100, 1),
                'revenue' => $rev,
            ]);
        }

        $kpi = [
            'arrivals_today'   => $arrivalsList->count(),
            'departures_today' => $departuresList->count(),
            'in_house'         => Reservation::where('property_id', $property->id)->where('status', 'checked_in')->count(),
            'pending_payment'  => Reservation::where('property_id', $property->id)
                ->where('balance', '>', 0)->whereIn('status', ['confirmed', 'checked_in'])->count(),
            'occupancy_pct'    => round(($soldToday / $totalRooms) * 100, 1),
            'total_rooms'      => $totalRooms,
            'occupied_rooms'   => $soldToday,
            'adr'              => $soldToday > 0 ? round($roomRevToday / $soldToday, 0) : 0,
            'revpar'           => round($roomRevToday / $totalRooms, 0),
            'room_rev_today'   => $roomRevToday,
            'total_rev_today'  => $totalRevToday,
            'arrivals_list'    => $arrivalsList,
            'departures_list'  => $departuresList,
        ];

        $user  = $request->user();
        $role  = $user ? optional($user->roles->first())->name : null;
        $roleModules = $this->getVisibleModuleCatalog($role);
        $roleMetrics = $role ? $this->getRoleSpecificMetrics($role) : [];

        return view('panel.dashboard', compact('property', 'kpi', 'trend', 'role', 'roleModules', 'roleMetrics'));
    }

    private function getVisibleModuleCatalog(?string $role): array
    {
        $allModules = [
            // Operations
            ['cluster' => 'Operations', 'label' => 'Reservations',   'desc' => 'Booking, check-in, folios',     'route' => 'panel.fo.reservations.index',     'color' => 'indigo',   'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>'],
            ['cluster' => 'Operations', 'label' => 'Tape Chart',     'desc' => 'Visual room timeline',          'route' => 'panel.fo.calendar',                'color' => 'sky',      'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>'],
            ['cluster' => 'Operations', 'label' => 'Night Audit',    'desc' => 'EOD posting & rollover',        'route' => 'panel.fo.night-audit.index',       'color' => 'slate',    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>'],
            ['cluster' => 'Operations', 'label' => 'E-Registration', 'desc' => 'Online check-in',               'route' => 'panel.fo.e-registration.index',    'color' => 'cyan',     'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7l-2.5 2.5L9 12.5"/>'],
            ['cluster' => 'Operations', 'label' => 'Out of Order',   'desc' => 'OOO room status',               'route' => 'panel.fo.ooo.index',               'color' => 'rose',     'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>'],
            ['cluster' => 'Operations', 'label' => 'Cashier Shifts', 'desc' => 'Open/close shifts',             'route' => 'panel.fo.shifts.index',            'color' => 'amber',    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>'],
            ['cluster' => 'Operations', 'label' => 'Housekeeping',   'desc' => 'HK board & tasks',              'route' => 'panel.hk.board',                   'color' => 'violet',   'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>'],
            ['cluster' => 'Operations', 'label' => 'Lost & Found',   'desc' => 'Items left by guests',          'route' => 'panel.hk.lost-found.index',        'color' => 'fuchsia',  'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>'],

            // F&B
            ['cluster' => 'F&B / POS',  'label' => 'POS',            'desc' => 'Point of Sale',                 'route' => 'panel.pos.index',                  'color' => 'orange',   'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>'],
            ['cluster' => 'F&B / POS',  'label' => 'KDS',            'desc' => 'Kitchen Display',               'route' => 'panel.pos.kds',                    'color' => 'red',      'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082"/>'],
            ['cluster' => 'F&B / POS',  'label' => 'Laundry',        'desc' => 'Laundry POS',                   'route' => 'panel.pos.laundry.index',          'color' => 'sky',      'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>'],

            // Revenue
            ['cluster' => 'Revenue',    'label' => 'Rate Calendar',  'desc' => 'Daily rates',                   'route' => 'panel.pricing.calendar',           'color' => 'emerald',  'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/>'],
            ['cluster' => 'Revenue',    'label' => 'Dynamic Rules',  'desc' => 'Yield automation',              'route' => 'panel.pricing.rules',              'color' => 'teal',     'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>'],
            ['cluster' => 'Revenue',    'label' => 'Parity Alerts',  'desc' => 'OTA price parity',              'route' => 'panel.pricing.parity',             'color' => 'amber',    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>'],
            ['cluster' => 'Revenue',    'label' => 'RMS',            'desc' => 'Revenue management',            'route' => 'panel.rms.dashboard',              'color' => 'cyan',     'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>'],
            ['cluster' => 'Revenue',    'label' => 'Channels',       'desc' => 'OTA distribution',              'route' => 'panel.channel.index',              'color' => 'rose',     'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064"/>'],
            ['cluster' => 'Revenue',    'label' => 'Allotments',     'desc' => 'Group blocks',                  'route' => 'panel.sales.allotments.index',     'color' => 'pink',     'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/>'],

            // AI Tools (BYOK)
            ['cluster' => 'AI Tools (BYOK)', 'label' => 'AI Hub',         'desc' => 'Semua tool AI di satu tempat',         'route' => 'panel.ai.hub',           'color' => 'violet',  'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>'],
            ['cluster' => 'AI Tools (BYOK)', 'label' => 'AI Concierge',   'desc' => 'Chatbot multi-bahasa',                 'route' => 'panel.ai.concierge',     'color' => 'indigo',  'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>'],
            ['cluster' => 'AI Tools (BYOK)', 'label' => 'Auto-Translate', 'desc' => 'Terjemahkan konten',                   'route' => 'panel.ai.translate',     'color' => 'sky',     'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"/>'],
            ['cluster' => 'AI Tools (BYOK)', 'label' => 'Demand Forecast','desc' => 'Prediksi okupansi 30 hari',            'route' => 'panel.ai.forecast',      'color' => 'emerald', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>'],
            ['cluster' => 'AI Tools (BYOK)', 'label' => 'Review Replies', 'desc' => 'AI generate balasan review',           'route' => 'panel.ai.review-replies','color' => 'amber',   'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>'],

            // Guests & CRM
            ['cluster' => 'Guests',     'label' => 'Guests',         'desc' => 'Profiles & history',            'route' => 'panel.guests.index',               'color' => 'indigo',   'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>'],
            ['cluster' => 'Guests',     'label' => 'Loyalty',        'desc' => 'Members &amp; tiers',           'route' => 'panel.loyalty.members',            'color' => 'amber',    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>'],
            ['cluster' => 'Guests',     'label' => 'Communications', 'desc' => 'Inbox &amp; campaigns',         'route' => 'panel.comm.inbox',                 'color' => 'blue',     'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>'],
            ['cluster' => 'Guests',     'label' => 'Concierge',      'desc' => 'Requests &amp; POIs',           'route' => 'panel.concierge.requests.index',   'color' => 'emerald',  'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>'],
            ['cluster' => 'Guests',     'label' => 'Surveys',        'desc' => 'Guest feedback',                'route' => 'panel.survey.index',               'color' => 'teal',     'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>'],
            ['cluster' => 'Guests',     'label' => 'Referrals',      'desc' => 'Affiliate program',             'route' => 'panel.marketing.referrals',        'color' => 'fuchsia',  'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>'],

            // Finance
            ['cluster' => 'Finance',    'label' => 'Accounting',     'desc' => 'COA · Journal · AR/AP',         'route' => 'panel.accounting.dashboard',       'color' => 'emerald',  'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>'],
            ['cluster' => 'Finance',    'label' => 'Bank Recon',     'desc' => 'Match transactions',            'route' => 'panel.finance.bank-recon',         'color' => 'cyan',     'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 10h18M3 6h18m-9 8h9m-3-4l3 4-3 4M3 14h6l3 4-3 4H3"/>'],
            ['cluster' => 'Finance',    'label' => 'Budget',         'desc' => 'Plan vs actual',                'route' => 'panel.finance.budget',             'color' => 'lime',     'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2"/>'],
            ['cluster' => 'Finance',    'label' => 'FX Rates',       'desc' => 'Currency conversion',           'route' => 'panel.finance.fx-rates',           'color' => 'sky',      'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>'],
            ['cluster' => 'Finance',    'label' => 'Owner Stmt',     'desc' => 'Distribution reports',          'route' => 'panel.finance.owner-statements',   'color' => 'indigo',   'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>'],

            // Inventory & People
            ['cluster' => 'Inventory & People', 'label' => 'Inventory',     'desc' => 'Stock items',           'route' => 'panel.inventory.index',            'color' => 'orange',   'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>'],
            ['cluster' => 'Inventory & People', 'label' => 'Purchasing',    'desc' => 'PR · PO · GR',          'route' => 'panel.inventory.po.index',         'color' => 'amber',    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>'],
            ['cluster' => 'Inventory & People', 'label' => 'Asset Mgmt',    'desc' => 'PPM · work orders',     'route' => 'panel.asset.index',                'color' => 'slate',    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z"/>'],
            ['cluster' => 'Inventory & People', 'label' => 'HR & Payroll',  'desc' => 'Staff · attendance',    'route' => 'panel.hr.employees',               'color' => 'violet',   'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>'],
            ['cluster' => 'Inventory & People', 'label' => 'Spa',           'desc' => 'Wellness bookings',     'route' => 'panel.spa.appointments',           'color' => 'pink',     'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>'],
            ['cluster' => 'Inventory & People', 'label' => 'Banquet',       'desc' => 'Events &amp; MICE',     'route' => 'panel.banquet.events.index',       'color' => 'rose',     'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2"/>'],

            // Insights
            ['cluster' => 'Insights',   'label' => 'Reports',        'desc' => 'Occupancy · Cashier · BPS',     'route' => 'panel.reports.occupancy',          'color' => 'blue',     'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"/>'],
            ['cluster' => 'Insights',   'label' => 'Sustainability', 'desc' => 'Eco metrics',                   'route' => 'panel.sustainability.dashboard',   'color' => 'emerald',  'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/>'],
            ['cluster' => 'Insights',   'label' => 'Audit Log',      'desc' => 'Activity trail',                'route' => 'panel.audit.index',                'color' => 'slate',    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/>'],
            ['cluster' => 'Insights',   'label' => 'Knowledge Base', 'desc' => 'Internal docs',                 'route' => 'panel.kb.index',                   'color' => 'cyan',     'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>'],
            ['cluster' => 'Insights',   'label' => 'Settings',       'desc' => 'Property &amp; integrations',   'route' => 'panel.settings.property',          'color' => 'gray',     'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065zM15 12a3 3 0 11-6 0 3 3 0 016 0z"/>'],
        ];

        if (!$role || in_array($role, ['admin', 'manager', 'owner'])) {
            return $allModules;
        }

        return array_values(array_filter($allModules, function ($module) use ($role) {
            return match ($role) {
                'fo' => in_array($module['cluster'], ['Operations', 'Guests', 'AI Tools (BYOK)']),
                'hk' => in_array($module['label'], ['Housekeeping', 'Lost & Found']),
                'kasir' => in_array($module['cluster'], ['F&B / POS']) || $module['label'] === 'Cashier Shifts',
                'acc' => in_array($module['cluster'], ['Finance']) || $module['label'] === 'Reports',
                'channel' => in_array($module['cluster'], ['Revenue']),
                default => false,
            };
        }));
    }

    private function getRoleSpecificMetrics(string $role): array
    {
        $property = app('current_property');
        $today    = now()->toDateString();
        $metrics  = [];

        $includeHk    = in_array($role, ['admin', 'manager', 'owner', 'hk']);
        $includeFo    = in_array($role, ['admin', 'manager', 'owner', 'fo']);
        $includeAcc   = in_array($role, ['admin', 'manager', 'owner', 'acc']);
        $includeKasir = in_array($role, ['admin', 'manager', 'owner', 'kasir']);

        if ($includeHk) {
            $metrics['hk'] = [
                'dirty_rooms'   => HkTask::where('property_id', $property->id)->whereDate('scheduled_date', $today)->where('status', 'pending')->count(),
                'clean_rooms'   => HkTask::where('property_id', $property->id)->whereDate('scheduled_date', $today)->where('status', 'done')->count(),
                'pending_tasks' => HkTask::where('property_id', $property->id)->whereIn('status', ['pending', 'in_progress'])->count(),
            ];
        }

        if ($includeFo) {
            $metrics['fo'] = [
                'pending_check_ins'  => Reservation::where('property_id', $property->id)->whereDate('check_in', $today)->whereIn('status', ['confirmed', 'tentative'])->count(),
                'pending_check_outs' => Reservation::where('property_id', $property->id)->whereDate('check_out', $today)->where('status', 'checked_in')->count(),
                'pending_payments'   => Reservation::where('property_id', $property->id)->where('balance', '>', 0)->whereIn('status', ['confirmed', 'checked_in'])->count(),
            ];
        }

        if ($includeAcc) {
            $monthStart = now()->startOfMonth()->toDateString();
            $revenueMtd = (float) FolioCharge::where('property_id', $property->id)
                ->whereBetween('charge_date', [$monthStart, $today])
                ->where('is_void', false)->sum('amount');

            $expenseMtd = (float) JournalLine::whereHas('entry', function ($q) use ($property) {
                $q->where('property_id', $property->id)
                    ->where('period_year', now()->year)
                    ->where('period_month', now()->month)
                    ->where('status', '!=', 'void');
            })->whereHas('account', function ($q) {
                $q->where('type', 'expense');
            })->sum('debit');

            $metrics['acc'] = [
                'revenue_mtd' => $revenueMtd,
                'expense_mtd' => $expenseMtd,
                'net_income'  => $revenueMtd - $expenseMtd,
            ];
        }

        if ($includeKasir) {
            $metrics['kasir'] = [
                'pos_orders_today'  => PosOrder::where('property_id', $property->id)->whereDate('created_at', $today)->count(),
                'pos_revenue_today' => (float) PosOrder::where('property_id', $property->id)->whereDate('created_at', $today)->sum('grand_total'),
            ];
        }

        return $metrics;
    }

    public function approveItem($id, ApprovalService $approvals)
    {
        $req = \App\Models\ApprovalRequest::where('property_id', app('current_property')->id)
            ->findOrFail($id);

        $approvals->approve($req, auth()->id(), 'Approved from dashboard');

        return back()->with('success', 'Permintaan disetujui.');
    }

    public function rejectItem($id, ApprovalService $approvals)
    {
        $req = \App\Models\ApprovalRequest::where('property_id', app('current_property')->id)
            ->findOrFail($id);

        $approvals->reject($req, auth()->id(), 'Rejected from dashboard');

        return back()->with('success', 'Permintaan ditolak.');
    }
}
