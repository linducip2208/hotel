<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

class OpenApiController extends Controller
{
    public function spec()
    {
        return response()->json([
            'openapi' => '3.1.0',
            'info' => [
                'title' => config('app.name').' API',
                'version' => '1.0.0',
                'description' => 'Hotel Management System REST API. See docs/14-API_SPEC.md.',
            ],
            'servers' => [['url' => config('app.url').'/api/v1']],
            'paths' => [
                '/availability' => ['get' => ['summary' => 'Search availability']],
                '/reservations' => ['get' => ['summary' => 'List reservations'], 'post' => ['summary' => 'Create reservation']],
                '/folios/{id}' => ['get' => ['summary' => 'Get folio']],
                // ... actual auto-generation via Scribe in real impl
            ],
        ], 200, [], JSON_UNESCAPED_SLASHES);
    }
}
