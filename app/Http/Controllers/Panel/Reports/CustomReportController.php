<?php

namespace App\Http\Controllers\Panel\Reports;

use App\Http\Controllers\Controller;
use App\Models\CustomReport;
use App\Services\Reports\CustomReportService;
use Illuminate\Http\Request;

class CustomReportController extends Controller
{
    public function __construct(
        protected CustomReportService $svc
    ) {}

    public function index()
    {
        $property = app('current_property');
        $reports = CustomReport::where('property_id', $property->id)
            ->with('createdBy')
            ->orderByDesc('updated_at')
            ->paginate(20);
        $widgets = $this->svc->getAvailableWidgets();

        return view('panel.reports.custom-reports', compact('reports', 'widgets'));
    }

    public function create()
    {
        $widgets = $this->svc->getAvailableWidgets();

        return view('panel.reports.custom-reports-create', compact('widgets'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'category' => 'required|string|in:revenue,operations,guests,finance',
            'widgets' => 'required|array|min:1',
            'widgets.*' => 'string',
            'is_public' => 'boolean',
        ]);

        CustomReport::create([
            'property_id' => app('current_property')->id,
            'name' => $data['name'],
            'category' => $data['category'],
            'widgets' => $data['widgets'],
            'created_by_user_id' => $request->user()->id,
            'is_public' => $data['is_public'] ?? false,
        ]);

        return redirect()->route('panel.reports.custom-reports.index')
            ->with('success', 'Laporan kustom berhasil disimpan.');
    }

    public function show(int $id)
    {
        $property = app('current_property');
        $report = CustomReport::where('property_id', $property->id)->findOrFail($id);

        $widgetData = [];
        if ($report->widgets) {
            foreach ($report->widgets as $widgetKey) {
                $widgetData[$widgetKey] = $this->svc->getWidgetData($property, $widgetKey);
            }
        }

        $widgetDefs = $this->svc->getAvailableWidgets();

        return view('panel.reports.custom-reports-show', compact('report', 'widgetData', 'widgetDefs'));
    }

    public function edit(int $id)
    {
        $property = app('current_property');
        $report = CustomReport::where('property_id', $property->id)->findOrFail($id);
        $widgets = $this->svc->getAvailableWidgets();

        return view('panel.reports.custom-reports-create', compact('report', 'widgets'));
    }

    public function update(Request $request, int $id)
    {
        $report = CustomReport::where('property_id', app('current_property')->id)->findOrFail($id);

        $data = $request->validate([
            'name' => 'required|string',
            'category' => 'required|string|in:revenue,operations,guests,finance',
            'widgets' => 'required|array|min:1',
            'widgets.*' => 'string',
            'is_public' => 'boolean',
        ]);

        $report->update($data);

        return redirect()->route('panel.reports.custom-reports.index')
            ->with('success', 'Laporan kustom diperbarui.');
    }

    public function destroy(int $id)
    {
        $report = CustomReport::where('property_id', app('current_property')->id)->findOrFail($id);
        $report->delete();

        return redirect()->route('panel.reports.custom-reports.index')
            ->with('success', 'Laporan kustom dihapus.');
    }

    public function widgetData(Request $request, string $key)
    {
        $property = app('current_property');
        $data = $this->svc->getWidgetData($property, $key);

        return response()->json($data);
    }
}
