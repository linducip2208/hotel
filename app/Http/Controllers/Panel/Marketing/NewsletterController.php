<?php

namespace App\Http\Controllers\Panel\Marketing;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    public function index(Request $request)
    {
        $propertyId = app('current_property')->id;

        $status = $request->query('status', 'subscribed');

        $subscribers = NewsletterSubscriber::where('property_id', $propertyId)
            ->when($status !== 'all', fn ($q) => $q->where('status', $status))
            ->when($request->filled('q'), fn ($q) => $q->where('email', 'like', '%'.$request->query('q').'%'))
            ->orderByDesc('created_at')
            ->paginate(25)
            ->withQueryString();

        $counts = [
            'subscribed' => NewsletterSubscriber::where('property_id', $propertyId)->subscribed()->count(),
            'unsubscribed' => NewsletterSubscriber::where('property_id', $propertyId)->unsubscribed()->count(),
            'bounced' => NewsletterSubscriber::where('property_id', $propertyId)->where('status', 'bounced')->count(),
            'total' => NewsletterSubscriber::where('property_id', $propertyId)->count(),
        ];

        return view('panel.marketing.newsletter', compact('subscribers', 'counts', 'status'));
    }

    public function store(Request $request)
    {
        $propertyId = app('current_property')->id;

        $data = $request->validate([
            'email' => 'required|email|max:191',
            'name' => 'nullable|string|max:191',
        ]);

        NewsletterSubscriber::updateOrCreate(
            ['property_id' => $propertyId, 'email' => $data['email']],
            [
                'name' => $data['name'] ?? null,
                'status' => 'subscribed',
                'source' => 'manual',
                'subscribed_at' => now(),
                'unsubscribed_at' => null,
            ]
        );

        return back()->with('success', 'Subscriber ditambahkan.');
    }

    public function export(Request $request)
    {
        $propertyId = app('current_property')->id;

        $rows = NewsletterSubscriber::where('property_id', $propertyId)
            ->when($request->query('status', 'subscribed') !== 'all', fn ($q) => $q->where('status', $request->query('status', 'subscribed')))
            ->orderBy('email')
            ->get();

        $csv = fopen('php://temp', 'r+');
        fputcsv($csv, ['email', 'name', 'source', 'status', 'subscribed_at']);

        foreach ($rows as $row) {
            fputcsv($csv, [
                $row->email,
                $row->name,
                $row->source,
                $row->status,
                $row->subscribed_at?->toDateTimeString(),
            ]);
        }
        rewind($csv);
        $content = stream_get_contents($csv);
        fclose($csv);

        return response($content, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="newsletter-subscribers.csv"',
        ]);
    }

    public function destroy($id)
    {
        $subscriber = NewsletterSubscriber::where('property_id', app('current_property')->id)->findOrFail($id);
        $subscriber->delete();

        return back()->with('success', 'Subscriber dihapus.');
    }
}
