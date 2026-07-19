<?php

namespace App\Http\Controllers\Pseo;

use App\Http\Controllers\Controller;
use App\Services\Seo\SitemapBuilder;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    public function __construct(protected SitemapBuilder $builder) {}

    public function index()
    {
        $cacheKey = 'sitemap:index';
        $xml = Cache::remember($cacheKey, 3600, function () {
            $base = config('app.url');
            $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n"
                .'<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
            foreach ($this->builder->index() as $group) {
                $xml .= '<sitemap><loc>'.htmlspecialchars("$base/sitemap-$group.xml")
                    .'</loc></sitemap>';
            }
            $xml .= '</sitemapindex>';
            return $xml;
        });

        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }

    public function group(string $group)
    {
        $cacheKey = "sitemap:$group";
        $xml = Cache::remember($cacheKey, 3600, function () use ($group) {
            $urls = $this->builder->urlsForGroup($group);
            $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n"
                .'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';
            foreach ($urls as $u) {
                $xml .= '<url><loc>'.htmlspecialchars($u['loc']).'</loc>';
                if (isset($u['priority'])) {
                    $xml .= '<priority>'.$u['priority'].'</priority>';
                }
                $xml .= '<changefreq>weekly</changefreq>';
                $xml .= '</url>';
            }
            $xml .= '</urlset>';
            return $xml;
        });

        return response($xml, 200, ['Content-Type' => 'application/xml']);
    }
}
