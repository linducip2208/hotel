<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KbArticle;
use Illuminate\Http\Request;

class SupportController extends Controller
{
    public function tickets()
    {
        // No ticket storage on this install — honest empty state.
        return view('admin.support.tickets');
    }

    public function showTicket(int $id)
    {
        abort(404, 'Sistem tiket belum tersedia pada instalasi ini.');
    }

    public function reply(Request $request, int $id)
    {
        abort(404, 'Sistem tiket belum tersedia pada instalasi ini.');
    }

    public function kb()
    {
        return view('admin.support.kb', [
            'articles' => KbArticle::orderBy('category')
                ->orderBy('title')
                ->paginate(30),
        ]);
    }
}
