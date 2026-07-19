<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * HTTP security headers — apply globally to web routes.
 *
 * Headers ditambahkan:
 * - Strict-Transport-Security (HSTS, production only)
 * - X-Frame-Options (anti-clickjacking)
 * - X-Content-Type-Options (anti-MIME-sniffing)
 * - Referrer-Policy
 * - Permissions-Policy (disable kamera/mic/geolocation default)
 * - Content-Security-Policy (allow Unsplash, Picsum, fonts.bunny.net, cdn.tailwindcss.com)
 * - X-XSS-Protection (legacy IE/Edge)
 *
 * Force HTTPS via redirect saat APP_ENV=production dan request HTTP.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        // Force HTTPS di production (kecuali via reverse proxy yang sudah set X-Forwarded-Proto)
        if (app()->isProduction() && ! $request->isSecure() && ! $this->isProxyHttps($request)) {
            return redirect()->secure($request->getRequestUri(), 301);
        }

        $response = $next($request);

        // Skip headers untuk asset / image responses (tidak perlu CSP)
        if ($this->isStaticAsset($request)) {
            return $response;
        }

        $headers = [
            'X-Frame-Options'        => 'SAMEORIGIN',
            'X-Content-Type-Options' => 'nosniff',
            'Referrer-Policy'        => 'strict-origin-when-cross-origin',
            'Permissions-Policy'     => 'camera=(), microphone=(), geolocation=(self), payment=(self)',
            'X-XSS-Protection'       => '1; mode=block',
            'Content-Security-Policy'=> $this->csp($request),
        ];

        if (app()->isProduction()) {
            $headers['Strict-Transport-Security'] = 'max-age=31536000; includeSubDomains; preload';
        }

        // Hide server fingerprint
        $response->headers->remove('X-Powered-By');
        $response->headers->remove('Server');

        foreach ($headers as $key => $value) {
            if (! $response->headers->has($key)) {
                $response->headers->set($key, $value);
            }
        }

        return $response;
    }

    private function csp(Request $request): string
    {
        $directives = [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.tailwindcss.com",
            "style-src 'self' 'unsafe-inline' https://fonts.bunny.net",
            "font-src 'self' https://fonts.bunny.net data:",
            "img-src 'self' data: https://images.unsplash.com https://picsum.photos https://fastly.picsum.photos https://source.unsplash.com",
            "connect-src 'self' https://whitelabel.co.id",
            "frame-ancestors 'self'",
            "base-uri 'self'",
            "form-action 'self'",
            "object-src 'none'",
        ];

        // Only force HTTPS upgrade when actually serving HTTPS — otherwise local
        // http://*.test dev breaks (browser upgrades asset URLs to https that don't exist).
        if (app()->isProduction() || $request->isSecure()) {
            $directives[] = "upgrade-insecure-requests";
        }

        return implode('; ', $directives);
    }

    private function isStaticAsset(Request $request): bool
    {
        $path = $request->path();
        return str_starts_with($path, 'build/')
            || str_starts_with($path, 'storage/')
            || preg_match('/\.(css|js|png|jpg|jpeg|webp|svg|ico|woff2?|ttf|otf)$/i', $path) === 1;
    }

    private function isProxyHttps(Request $request): bool
    {
        return $request->header('X-Forwarded-Proto') === 'https'
            || $request->header('X-Forwarded-Ssl') === 'on';
    }
}
