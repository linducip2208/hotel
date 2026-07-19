<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\Guest;
use App\Models\Reservation;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function global(Request $request)
    {
        $q = (string) $request->query('q', '');
        if (strlen($q) < 2) {
            return response()->json(['guests' => [], 'reservations' => []]);
        }

        // Use simple DB search if Scout/Meilisearch not configured
        $guests = Guest::query()
            ->where(fn ($qq) => $qq->where('first_name', 'like', "%$q%")
                ->orWhere('last_name', 'like', "%$q%")
                ->orWhere('email', 'like', "%$q%")
                ->orWhere('phone', 'like', "%$q%"))
            ->limit(10)->get();

        $reservations = Reservation::query()
            ->where('ref', 'like', "%$q%")->limit(10)->get();

        return response()->json([
            'guests' => $guests->map(fn ($g) => ['id' => $g->id, 'name' => $g->full_name, 'email' => $g->email]),
            'reservations' => $reservations->map(fn ($r) => ['id' => $r->id, 'ref' => $r->ref, 'check_in' => $r->check_in?->toDateString()]),
        ]);
    }
}
