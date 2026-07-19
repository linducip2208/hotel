<?php

namespace App\Http\Middleware;

use App\Adapters\Captcha\HcaptchaAdapter;
use App\Adapters\Captcha\RecaptchaAdapter;
use App\Adapters\Captcha\TurnstileAdapter;
use App\Models\Provider;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyCaptcha
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->input('captcha_token') ?? $request->header('X-Captcha-Token');

        $provider = Provider::where('integration_type', 'captcha')->where('is_active', true)->where('is_default', true)->first();
        if (! $provider) {
            // No captcha configured — let request through
            return $next($request);
        }

        $adapter = match ($provider->api_format) {
            'turnstile' => new TurnstileAdapter($provider),
            'hcaptcha' => new HcaptchaAdapter($provider),
            'recaptcha' => new RecaptchaAdapter($provider),
            default => null,
        };

        if (! $adapter) return $next($request);

        if (! $token || ! $adapter->verify($token, $request->ip())) {
            return response()->json(['error' => 'Captcha verification failed'], 422);
        }

        return $next($request);
    }
}
