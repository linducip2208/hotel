<?php

namespace App\Http\Controllers\Panel\Survey;

use App\Http\Controllers\Controller;
use App\Models\Survey;
use App\Models\SurveyResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SurveyController extends Controller
{
    public function index()
    {
        $surveys = Survey::where('property_id', app('current_property')->id)->paginate(50);
        return view('panel.survey.index', compact('surveys'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'trigger' => 'required|in:post_stay,post_event,in_stay,on_demand',
            'questions' => 'required|array|min:1',
        ]);
        Survey::create($data + [
            'property_id' => app('current_property')->id,
            'slug' => Str::slug($data['name']).'-'.Str::random(4),
            'is_active' => true,
        ]);
        return back();
    }

    public function responses(int $id)
    {
        $survey = Survey::with('responses.guest')->findOrFail($id);
        $npsAvg = $survey->responses->whereNotNull('nps_score')->avg('nps_score');
        $promoters = $survey->responses->where('nps_score', '>=', 9)->count();
        $detractors = $survey->responses->where('nps_score', '<=', 6)->whereNotNull('nps_score')->count();
        $total = max(1, $survey->responses->whereNotNull('nps_score')->count());
        $nps = round((($promoters - $detractors) / $total) * 100, 1);
        return view('panel.survey.responses', compact('survey', 'npsAvg', 'nps', 'promoters', 'detractors'));
    }
}
