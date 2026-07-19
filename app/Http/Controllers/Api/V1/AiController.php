<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\Review;
use App\Services\Ai\ConciergeService;
use App\Services\Ai\DemandForecastAi;
use App\Services\Ai\ReviewReplyGenerator;
use App\Services\Ai\TranslationService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AiController extends Controller
{
    private function property(): Property
    {
        return app('current_property') ?? Property::orderBy('id')->firstOrFail();
    }

    public function translate(Request $request, TranslationService $svc)
    {
        $request->validate([
            'text' => 'required|string',
            'to'   => 'required|string|size:2',
            'from' => 'nullable|string|size:2',
        ]);

        return response()->json($svc->translate(
            $this->property()->id,
            $request->input('text'),
            $request->input('to'),
            $request->input('from')
        ));
    }

    public function concierge(Request $request, ConciergeService $svc)
    {
        $request->validate([
            'message' => 'required|string',
            'history' => 'nullable|array',
            'locale'  => 'nullable|string|max:10',
        ]);

        $property = $this->property();

        return response()->json($svc->chat(
            $property,
            $request->input('message'),
            $request->input('history', []),
            $request->input('locale', 'id')
        ));
    }

    public function reviewReply(Request $request, int $reviewId, ReviewReplyGenerator $svc)
    {
        $request->validate([
            'tone'   => 'nullable|string|max:50',
            'locale' => 'nullable|string|max:10',
        ]);

        $review = Review::where('property_id', $this->property()->id)->findOrFail($reviewId);

        return response()->json($svc->generate(
            $review,
            $request->input('tone', 'friendly_professional'),
            $request->input('locale', 'id')
        ));
    }

    public function demandForecast(Request $request, DemandForecastAi $svc)
    {
        $request->validate([
            'from' => 'nullable|date',
            'to'   => 'nullable|date|after_or_equal:from',
        ]);

        $property = $this->property();
        $from = Carbon::parse($request->query('from', now()->toDateString()));
        $to   = Carbon::parse($request->query('to', now()->addDays(14)->toDateString()));

        return response()->json($svc->refine($property, $from, $to));
    }

    public function chatbot(Request $request, ConciergeService $svc)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
            'history' => 'nullable|array',
            'locale'  => 'nullable|string|max:10',
        ]);

        $property = $this->property();

        $response = $svc->chat(
            $property,
            $request->input('message'),
            $request->input('history', []),
            $request->input('locale', 'en')
        );

        // Format for chatbot UI
        $suggestions = $this->generateSuggestions($request->input('message'));
        $actions = $this->detectActions($request->input('message'));

        return response()->json([
            'reply' => $response['reply'] ?? $response['message'] ?? 'How can I assist you?',
            'suggestions' => $suggestions,
            'actions' => $actions,
        ]);
    }

    private function generateSuggestions(string $message): array
    {
        $msg = strtolower($message);

        if (str_contains($msg, 'room') || str_contains($msg, 'kamar')) {
            return ['Show Available Rooms', 'Room Rates', 'Room Photos'];
        }
        if (str_contains($msg, 'spa') || str_contains($msg, 'massage')) {
            return ['Spa Treatments', 'Book Appointment', 'Spa Packages'];
        }
        if (str_contains($msg, 'restaurant') || str_contains($msg, 'food') || str_contains($msg, 'makan')) {
            return ['Restaurant Menu', 'Room Service', 'Breakfast Hours'];
        }
        if (str_contains($msg, 'check') || str_contains($msg, 'time')) {
            return ['Check-in Time', 'Check-out Time', 'Late Check-out'];
        }

        return ['Room Availability', 'Spa & Wellness', 'Restaurant', 'Hotel Facilities', 'Nearby Attractions'];
    }

    private function detectActions(string $message): array
    {
        $actions = [];
        $msg = strtolower($message);

        if (str_contains($msg, 'book') || str_contains($msg, 'reserve') || str_contains($msg, 'booking')) {
            $actions[] = 'book_now';
        }
        if (str_contains($msg, 'room') || str_contains($msg, 'available') || str_contains($msg, 'kamar')) {
            $actions[] = 'show_rooms';
        }
        if (str_contains($msg, 'contact') || str_contains($msg, 'call') || str_contains($msg, 'phone')) {
            $actions[] = 'contact';
        }
        if (str_contains($msg, 'faq') || str_contains($msg, 'question')) {
            $actions[] = 'faq';
        }

        return $actions;
    }
}
