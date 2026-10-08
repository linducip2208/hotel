<?php

namespace App\Services;

use App\Models\Property;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Request;

/**
 * Resolves the property shown on the public website / booking engine.
 *
 * Resolution order:
 *  1. Hostname match against Property slug subdomain or full domain
 *  2. APP_PROPERTY_SLUG env pin (standalone single-property install)
 *  3. First active property (legacy single-property fallback)
 *
 * Never relies on a hardcoded ID.
 */
class PublicPropertyResolver
{
    public function resolve(): ?Property
    {
        // 1. Explicit env pin.
        if ($slug = Config::get('hotel.public_property_slug') ?: env('APP_PROPERTY_SLUG')) {
            $property = Property::where('slug', $slug)->where('is_active', true)->first();
            if ($property) {
                return $property;
            }
        }

        // 2. Hostname: subdomain (demo.hotel.test → slug "demo") or custom domain.
        $host = strtolower(Request::getHost() ?? '');
        if ($host && ! in_array($host, ['localhost', '127.0.0.1'], true) && ! str_starts_with($host, 'www.')) {
            $byDomain = Property::where('is_active', true)
                ->get()
                ->first(fn (Property $p) => $this->matchesHost($p, $host));
            if ($byDomain) {
                return $byDomain;
            }
        }

        // 3. Legacy fallback: first active property.
        return Property::where('is_active', true)->orderBy('id')->first();
    }

    protected function matchesHost(Property $property, string $host): bool
    {
        // Custom domain stored in settings.
        $settings = $property->settings;
        if (is_array($settings) && isset($settings['custom_domain'])
            && strtolower($settings['custom_domain']) === $host) {
            return true;
        }

        // Slug subdomain: {slug}.example.com — only meaningful on a shared root.
        $parts = explode('.', $host);
        if (count($parts) >= 2) {
            $subdomain = $parts[0];
            if ($subdomain === strtolower($property->slug)) {
                return true;
            }
        }

        return false;
    }
}
