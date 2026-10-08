<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\NewsletterSubscriber;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class NewsletterController extends Controller
{
    public function subscribe(Request $request)
    {
        $property = app()->bound('current_property') ? app('current_property') : Property::orderBy('id')->first();

        $data = $request->validate([
            'email' => 'required|email|max:191',
            'name' => 'nullable|string|max:191',
        ]);

        $subscriber = NewsletterSubscriber::updateOrCreate(
            ['property_id' => $property->id, 'email' => $data['email']],
            [
                'name' => $data['name'] ?? null,
                'status' => 'subscribed',
                'source' => 'website',
                'unsubscribe_token' => Str::random(40),
                'subscribed_at' => now(),
                'unsubscribed_at' => null,
            ]
        );

        if ($request->expectsJson()) {
            return response()->json(['ok' => true, 'message' => 'Subscribed.']);
        }

        return back()->with('success', 'Terima kasih! Email Anda berhasil didaftarkan.');
    }

    public function unsubscribe(string $token)
    {
        $subscriber = NewsletterSubscriber::where('unsubscribe_token', $token)->first();

        if ($subscriber) {
            $subscriber->update([
                'status' => 'unsubscribed',
                'unsubscribed_at' => now(),
            ]);
        }

        return view('public.newsletter-unsubscribed');
    }
}
