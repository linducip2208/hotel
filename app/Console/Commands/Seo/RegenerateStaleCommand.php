<?php

namespace App\Console\Commands\Seo;

use App\Models\SeoPage;
use Illuminate\Console\Command;

class RegenerateStaleCommand extends Command
{
    protected $signature = 'seo:regenerate-stale';
    protected $description = 'Trigger regeneration for SEO pages past regenerate_after';

    public function handle(): int
    {
        $count = SeoPage::whereNotNull('regenerate_after')
            ->where('regenerate_after', '<=', now())
            ->update(['last_generated_at' => now(), 'regenerate_after' => now()->addDays(90)]);
        $this->info("Marked {$count} pages for regeneration.");
        return self::SUCCESS;
    }
}
