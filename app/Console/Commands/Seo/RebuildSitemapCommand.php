<?php

namespace App\Console\Commands\Seo;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class RebuildSitemapCommand extends Command
{
    protected $signature = 'seo:rebuild-sitemap';
    protected $description = 'Invalidate sitemap cache so it rebuilds on next access';

    public function handle(): int
    {
        foreach (['pages', 'rooms', 'pseo-best', 'pseo-compare', 'pseo-location', 'pseo-landmark'] as $g) {
            Cache::forget("sitemap:$g");
        }
        $this->info('Sitemap cache cleared.');
        return self::SUCCESS;
    }
}
