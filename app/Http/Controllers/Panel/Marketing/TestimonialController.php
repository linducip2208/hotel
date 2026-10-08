<?php

namespace App\Http\Controllers\Panel\Marketing;

use App\Http\Controllers\Controller;
use App\Models\Testimonial;
use Illuminate\Http\Request;

class TestimonialController extends Controller
{
    public function index()
    {
        $propertyId = app('current_property')->id;

        $testimonials = Testimonial::where('property_id', $propertyId)
            ->orderBy('sort_order')
            ->orderByDesc('created_at')
            ->get();

        return view('panel.marketing.testimonials', compact('testimonials'));
    }

    public function store(Request $request)
    {
        $propertyId = app('current_property')->id;

        $data = $request->validate([
            'guest_name' => 'required|string|max:191',
            'guest_title' => 'nullable|string|max:191',
            'origin_city' => 'nullable|string|max:191',
            'rating' => 'required|integer|between:1,5',
            'quote' => 'required|string',
            'avatar_url' => 'nullable|string|max:500',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        Testimonial::create(array_merge($data, [
            'property_id' => $propertyId,
            'source' => 'curated',
            'is_active' => $data['is_active'] ?? true,
            'sort_order' => $data['sort_order'] ?? 0,
        ]));

        return back()->with('success', 'Testimoni ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $testimonial = Testimonial::where('property_id', app('current_property')->id)->findOrFail($id);

        $data = $request->validate([
            'guest_name' => 'required|string|max:191',
            'guest_title' => 'nullable|string|max:191',
            'origin_city' => 'nullable|string|max:191',
            'rating' => 'required|integer|between:1,5',
            'quote' => 'required|string',
            'avatar_url' => 'nullable|string|max:500',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $testimonial->update(array_merge($data, [
            'is_active' => $data['is_active'] ?? true,
            'sort_order' => $data['sort_order'] ?? 0,
        ]));

        return back()->with('success', 'Testimoni diperbarui.');
    }

    public function destroy($id)
    {
        $testimonial = Testimonial::where('property_id', app('current_property')->id)->findOrFail($id);
        $testimonial->delete();

        return back()->with('success', 'Testimoni dihapus.');
    }
}
