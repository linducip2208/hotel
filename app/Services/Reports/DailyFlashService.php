<?php

namespace App\Services\Reports;

use App\Models\DailyFlashReport;
use App\Models\FolioCharge;
use App\Models\FolioPayment;
use App\Models\Inventory;
use App\Models\Property;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DailyFlashService
{
    public function build(Property $property, ?Carbon $date = null): DailyFlashReport
    {
        $date ??= now();
        $dateStr = $date->toDateString();

        $sold = (int) Inventory::where('property_id', $property->id)->whereDate('date', $dateStr)->sum('sold');
        $oo = (int) Inventory::where('property_id', $property->id)->whereDate('date', $dateStr)->sum('out_of_order');
        $totalRooms = $property->total_rooms ?: 1;
        $occPct = round(($sold / $totalRooms) * 100, 2);

        $roomRev = (float) FolioCharge::where('property_id', $property->id)
            ->whereDate('charge_date', $dateStr)
            ->where('category', 'room')->where('is_void', false)->sum('amount');
        $fnbRev = (float) FolioCharge::where('property_id', $property->id)
            ->whereDate('charge_date', $dateStr)->where('category', 'fnb')->where('is_void', false)->sum('amount');
        $miniBar = (float) FolioCharge::where('property_id', $property->id)
            ->whereDate('charge_date', $dateStr)->where('category', 'minibar')->where('is_void', false)->sum('amount');
        $laundry = (float) FolioCharge::where('property_id', $property->id)
            ->whereDate('charge_date', $dateStr)->where('category', 'laundry')->where('is_void', false)->sum('amount');
        $spa = (float) FolioCharge::where('property_id', $property->id)
            ->whereDate('charge_date', $dateStr)->where('category', 'spa')->where('is_void', false)->sum('amount');
        $other = (float) FolioCharge::where('property_id', $property->id)
            ->whereDate('charge_date', $dateStr)->whereIn('category', ['other', 'addon'])->where('is_void', false)->sum('amount');

        $pb1 = (float) FolioCharge::where('property_id', $property->id)
            ->whereDate('charge_date', $dateStr)->where('tax_code', 'PB1')->where('is_void', false)->sum('tax_amount');
        $ppn = (float) FolioCharge::where('property_id', $property->id)
            ->whereDate('charge_date', $dateStr)->where('tax_code', 'PPN_OUT')->where('is_void', false)->sum('tax_amount');

        $pmtBreakdown = FolioPayment::where('property_id', $property->id)
            ->whereDate('payment_date', $dateStr)->where('is_void', false)
            ->selectRaw('method, sum(amount) as total')->groupBy('method')->pluck('total', 'method')->toArray();

        $sourceMix = Reservation::where('property_id', $property->id)
            ->whereDate('check_in', $dateStr)
            ->selectRaw('source, count(*) as count, sum(grand_total) as revenue')
            ->groupBy('source')->get()->map(fn ($r) => ['count' => $r->count, 'revenue' => (float) $r->revenue])->toArray();

        $totalRev = $roomRev + $fnbRev + $miniBar + $laundry + $spa + $other;
        $adr = $sold > 0 ? round($roomRev / $sold, 2) : 0;
        $revpar = round($roomRev / max(1, $totalRooms), 2);

        return DailyFlashReport::updateOrCreate(
            ['property_id' => $property->id, 'report_date' => $dateStr],
            [
                'rooms_kpi' => [
                    'available' => $totalRooms - $oo,
                    'sold' => $sold,
                    'out_of_order' => $oo,
                    'occupancy_pct' => $occPct,
                    'adr' => $adr,
                    'revpar' => $revpar,
                ],
                'revenue_breakdown' => compact('roomRev', 'fnbRev', 'miniBar', 'laundry', 'spa', 'other'),
                'tax_breakdown' => ['pb1' => $pb1, 'ppn' => $ppn],
                'payment_breakdown' => $pmtBreakdown,
                'source_mix' => $sourceMix,
                'total_revenue' => $totalRev,
            ]
        );
    }
}
