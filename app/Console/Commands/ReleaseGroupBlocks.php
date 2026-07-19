<?php

namespace App\Console\Commands;

use App\Models\GroupBlock;
use App\Services\Fo\GroupBlockService;
use Illuminate\Console\Command;

class ReleaseGroupBlocks extends Command
{
    protected $signature = 'groups:release-expired';
    protected $description = 'Release unpicked rooms from expired group blocks';

    public function handle(GroupBlockService $service): void
    {
        $blocks = GroupBlock::where('status', 'definite')
            ->whereNotNull('cutoff_date')
            ->where('cutoff_date', '<', now())
            ->get();

        if ($blocks->isEmpty()) {
            $this->info('No expired group blocks to release.');
            return;
        }

        foreach ($blocks as $block) {
            $service->releaseUnpickedRooms($block);
            $this->info("Released group block: {$block->block_code}");
        }

        $this->info("Done. {$blocks->count()} group block(s) released.");
    }
}
