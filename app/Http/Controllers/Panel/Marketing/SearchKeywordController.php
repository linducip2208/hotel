<?php

namespace App\Http\Controllers\Panel\Marketing;

use App\Http\Controllers\Controller;
use App\Models\SearchKeyword;
use Illuminate\Http\Request;

class SearchKeywordController extends Controller
{
    public function index(Request $request)
    {
        $propertyId = app('current_property')->id;

        $source = $request->query('source', 'booking');

        $keywords = SearchKeyword::where('property_id', $propertyId)
            ->when($source !== 'all', fn ($q) => $q->where('source', $source))
            ->orderByDesc('hits')
            ->paginate(50)
            ->withQueryString();

        $top = SearchKeyword::where('property_id', $propertyId)
            ->orderByDesc('hits')
            ->limit(15)
            ->get();

        return view('panel.marketing.search-keywords', compact('keywords', 'top', 'source'));
    }

    public function destroy($id)
    {
        SearchKeyword::where('property_id', app('current_property')->id)->where('id', $id)->delete();

        return back()->with('success', 'Kata kunci dihapus.');
    }
}
