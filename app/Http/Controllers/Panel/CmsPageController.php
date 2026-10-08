<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\CmsPage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CmsPageController extends Controller
{
    public function index()
    {
        $pages = CmsPage::where('property_id', app('current_property')->id)
            ->orderBy('sort_order')
            ->orderByDesc('created_at')
            ->get();

        return view('panel.cms.index', compact('pages'));
    }

    public function create()
    {
        return view('panel.cms.edit', ['page' => null]);
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);

        $page = CmsPage::create(array_merge($data, [
            'property_id' => app('current_property')->id,
            'slug' => $this->uniqueSlug($data['slug'] ?? Str::slug($data['title'])),
        ]));

        return redirect()->route('panel.cms.index')->with('success', 'Halaman dibuat.');
    }

    public function edit($id)
    {
        $page = CmsPage::where('property_id', app('current_property')->id)->findOrFail($id);

        return view('panel.cms.edit', compact('page'));
    }

    public function update(Request $request, $id)
    {
        $page = CmsPage::where('property_id', app('current_property')->id)->findOrFail($id);

        $data = $this->validateData($request);

        if (($data['slug'] ?? null) && $data['slug'] !== $page->slug) {
            $data['slug'] = $this->uniqueSlug($data['slug'], $page->id);
        } else {
            unset($data['slug']);
        }

        $page->update($data);

        return redirect()->route('panel.cms.index')->with('success', 'Halaman diperbarui.');
    }

    public function destroy($id)
    {
        $page = CmsPage::where('property_id', app('current_property')->id)->findOrFail($id);
        $page->delete();

        return back()->with('success', 'Halaman dihapus.');
    }

    protected function validateData(Request $request): array
    {
        return $request->validate([
            'title' => 'required|string|max:191',
            'slug' => 'nullable|string|max:191|alpha_dash',
            'meta_title' => 'nullable|string|max:191',
            'meta_description' => 'nullable|string|max:300',
            'excerpt' => 'nullable|string',
            'content' => 'required|string',
            'is_published' => 'boolean',
            'show_in_footer' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);
    }

    protected function uniqueSlug(string $slug, ?int $ignoreId = null): string
    {
        $base = Str::slug($slug) ?: 'page';
        $candidate = $base;
        $i = 1;

        while (CmsPage::where('slug', $candidate)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()) {
            $candidate = $base.'-'.$i++;
        }

        return $candidate;
    }
}
