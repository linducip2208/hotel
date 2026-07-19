<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class IdempotencyKey
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! in_array($request->method(), ['POST', 'PUT', 'PATCH'])) {
            return $next($request);
        }
        $key = $request->header('Idempotency-Key');
        if (! $key) return $next($request);

        $hash = hash('sha256', $request->method().'|'.$request->path().'|'.$request->getContent());
        $existing = DB::table('api_idempotency_keys')->where('key', $key)->first();

        if ($existing) {
            if ($existing->hash !== $hash) {
                return response()->json(['error' => 'Idempotency key reused with different payload'], 422);
            }
            if ($existing->response) {
                return response()->json(json_decode($existing->response, true), $existing->http_status ?? 200);
            }
        } else {
            DB::table('api_idempotency_keys')->insert([
                'key' => $key,
                'hash' => $hash,
                'expires_at' => now()->addHours(24),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $response = $next($request);

        DB::table('api_idempotency_keys')->where('key', $key)->update([
            'response' => $response->getContent(),
            'http_status' => $response->getStatusCode(),
            'updated_at' => now(),
        ]);

        return $response;
    }
}
