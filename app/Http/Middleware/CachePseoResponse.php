<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CachePseoResponse
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($request->method() !== 'GET' || ! $response->isSuccessful()) {
            return $response;
        }

        // Cache headers untuk edge (Cloudflare Workers / standard CDN)
        $response->headers->set('Cache-Control', 'public, max-age=3600, s-maxage=86400, stale-while-revalidate=604800');
        $response->headers->set('Vary', 'Accept-Encoding, Accept-Language');

        // ETag untuk conditional requests
        $etag = '"'.md5($response->getContent()).'"';
        $response->headers->set('ETag', $etag);

        if ($request->getETags() && in_array($etag, $request->getETags())) {
            return response('', 304, ['ETag' => $etag, 'Cache-Control' => 'public, max-age=3600']);
        }

        return $response;
    }
}
