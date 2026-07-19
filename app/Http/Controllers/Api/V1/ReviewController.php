<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Services\Ai\ReviewReplyGenerator;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function __construct(private ReviewReplyGenerator $generator) {}

    public function index(Request $request)
    {
        $property = $request->user()->property;
        $query = Review::where('property_id', $property->id)
            ->with(['guest', 'reservation'])
            ->orderByDesc('created_at');

        if ($request->published !== null) {
            $query->where('is_published', (bool) $request->published);
        }

        return response()->json($query->paginate(20));
    }

    public function publish(Request $request, int $id)
    {
        $property = $request->user()->property;
        $review = Review::where('property_id', $property->id)->findOrFail($id);
        $review->update(['is_published' => true]);
        return response()->json($review);
    }

    public function unpublish(Request $request, int $id)
    {
        $property = $request->user()->property;
        $review = Review::where('property_id', $property->id)->findOrFail($id);
        $review->update(['is_published' => false]);
        return response()->json($review);
    }

    public function replyDraft(Request $request, int $id)
    {
        $property = $request->user()->property;
        $review = Review::where('property_id', $property->id)->findOrFail($id);
        $draft = $this->generator->generateReply($review, $property);
        return response()->json(['draft' => $draft]);
    }
}
