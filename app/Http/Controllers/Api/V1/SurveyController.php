<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Survey;
use App\Models\SurveyResponse;
use Illuminate\Http\Request;

class SurveyController extends Controller
{
    public function index(Request $request)
    {
        $property = $request->user()->property;
        $surveys = Survey::where('property_id', $property->id)
            ->withCount('responses')
            ->orderByDesc('created_at')
            ->get();
        return response()->json($surveys);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'     => 'required|string|max:200',
            'questions' => 'required|array|min:1',
            'trigger'   => 'in:checkout,manual,scheduled',
        ]);

        $survey = Survey::create([
            ...$data,
            'property_id' => $request->user()->property->id,
            'is_active'   => true,
        ]);

        return response()->json($survey, 201);
    }

    public function show(Request $request, int $id)
    {
        $property = $request->user()->property;
        $survey = Survey::where('property_id', $property->id)
            ->withCount('responses')
            ->findOrFail($id);
        return response()->json($survey);
    }

    public function responses(Request $request, int $id)
    {
        $property = $request->user()->property;
        $survey = Survey::where('property_id', $property->id)->findOrFail($id);
        $responses = $survey->responses()
            ->with(['guest', 'reservation'])
            ->orderByDesc('submitted_at')
            ->paginate(20);
        return response()->json($responses);
    }

    public function update(Request $request, int $id)
    {
        $property = $request->user()->property;
        $survey = Survey::where('property_id', $property->id)->findOrFail($id);
        $survey->update($request->only(['title', 'questions', 'is_active', 'trigger']));
        return response()->json($survey);
    }
}
