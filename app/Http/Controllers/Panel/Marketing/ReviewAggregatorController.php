<?php

namespace App\Http\Controllers\Panel\Marketing;

use App\Http\Controllers\Controller;
use App\Services\Marketing\ReviewAggregatorService;
use Illuminate\Http\Request;

class ReviewAggregatorController extends Controller
{
    public function __construct(protected ReviewAggregatorService $reviewService) {}

    public function index()
    {
        $property = app('current_property');
        $stats = $this->reviewService->getReviewStats($property);

        return view('panel.marketing.review-aggregator', array_merge($stats, ['property' => $property]));
    }

    public function pull(Request $request)
    {
        $property = app('current_property');
        $result = $this->reviewService->pullGoogleReviews($property);

        if (isset($result['error'])) {
            return back()->with('error', 'Gagal menarik ulasan: '.$result['error']);
        }

        return back()->with('success', "Berhasil menarik {$result['imported']} ulasan baru dari {$result['total']} ulasan Google.");
    }

    public function stats()
    {
        $property = app('current_property');

        return response()->json($this->reviewService->getReviewStats($property));
    }
}
