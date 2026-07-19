<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Services\Finance\OwnerPortalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OwnerPortalController extends Controller
{
    public function __construct(
        protected OwnerPortalService $ownerService
    ) {
        $this->middleware('auth');
    }

    private function userProperty(): ?Property
    {
        return Property::where('id', Auth::user()->property_id)->first();
    }

    public function dashboard(Request $request)
    {
        $property = $this->userProperty();
        if (!$property) {
            return redirect('/login')->with('error', 'Properti tidak ditemukan.');
        }

        $period = $request->query('period', now()->startOfMonth()->toDateString());
        $data = $this->ownerService->getOwnerDashboard(Auth::user(), $property);

        $summary = $data['summary'];
        $distributions = $data['distributions'];
        $monthlyTrend = $data['monthly_trend'];
        $documents = $data['documents'];
        $ownershipPct = $data['ownership_pct'];

        return view('portal.owner.dashboard', compact(
            'property', 'summary', 'distributions', 'monthlyTrend', 'documents', 'ownershipPct', 'period'
        ));
    }

    public function financials(Request $request)
    {
        $property = $this->userProperty();
        if (!$property) { return redirect('/login'); }

        $period = $request->query('period', now()->startOfMonth()->toDateString());
        $pnl = $this->ownerService->getMonthlyPnl($property, $period);

        $owner = \App\Models\PropertyOwner::where('property_id', $property->id)
            ->where('user_id', Auth::id())
            ->first();
        $ownershipPct = $owner ? (float) $owner->ownership_pct : 0;

        return view('portal.owner.financials', compact('property', 'pnl', 'ownershipPct', 'period'));
    }

    public function distributions(Request $request)
    {
        $property = $this->userProperty();
        if (!$property) { return redirect('/login'); }

        $distributions = \App\Models\OwnerDistribution::where('property_id', $property->id)
            ->where('owner_user_id', Auth::id())
            ->orderByDesc('period_start')
            ->paginate(20);

        return view('portal.owner.distributions', compact('property', 'distributions'));
    }

    public function downloadDocument($id)
    {
        $doc = \App\Models\OwnerDocument::where('id', $id)
            ->where('owner_user_id', Auth::id())
            ->firstOrFail();

        $path = storage_path('app/' . $doc->file_path);
        if (!file_exists($path)) {
            abort(404, 'File tidak ditemukan.');
        }

        return response()->download($path, $doc->title . '.' . pathinfo($path, PATHINFO_EXTENSION));
    }
}
