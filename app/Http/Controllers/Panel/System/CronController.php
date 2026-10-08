<?php

namespace App\Http\Controllers\Panel\System;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\DeploymentHeartbeat;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Http\Request;

class CronController extends Controller
{
    /**
     * List every task registered in the Laravel scheduler with its schedule
     * expression, human-readable description and next run time.
     */
    public function index()
    {
        $schedule = app(Schedule::class);

        $tasks = collect($schedule->events())->map(function ($event) {
            return [
                'signature' => $this->signature($event),
                'command' => (string) $event->command,
                'description' => $event->description ?: $this->signature($event),
                'expression' => $event->expression,
                'human' => $this->humanizeCron($event->expression),
                'next_run' => $event->nextRunDate()->format('d M Y H:i'),
                'timezone' => $event->timezone,
            ];
        })->values();

        $lastHeartbeat = DeploymentHeartbeat::query()
            ->orderByDesc('created_at')
            ->first();

        return view('panel.system.cron', compact('tasks', 'lastHeartbeat'));
    }

    public function run(Request $request)
    {
        $command = (string) $request->input('command');

        abort_unless($command !== '' && in_array($command, $this->runnableSignatures(), true), 404);

        $artisan = base_path('artisan');

        if (strncasecmp(PHP_OS, 'WIN', 3) === 0) {
            $cmd = sprintf('start /B php "%s" %s > NUL 2>&1', $artisan, escapeshellarg($command));
        } else {
            $cmd = sprintf('nohup php "%s" %s > /dev/null 2>&1 &', $artisan, escapeshellarg($command));
        }

        exec($cmd);

        AuditLog::query()->create([
            'property_id' => app('current_property')->id,
            'user_id' => auth()->id(),
            'user_type' => 'staff',
            'action' => 'cron.run',
            'metadata' => json_encode(['command' => $command]),
        ]);

        return back()->with('success', "Command \"{$command}\" dijalankan di background.");
    }

    /**
     * Extract the artisan signature (e.g. "license:heartbeat") from a raw
     * scheduled command string like `"/path/php.exe" "artisan" license:heartbeat`.
     */
    protected function signature($event): ?string
    {
        $cmd = (string) $event->command;

        if (preg_match('/artisan"?\s+"?([a-zA-Z0-9:_\-]+)/', $cmd, $m)) {
            return $m[1];
        }

        return null;
    }

    protected function runnableSignatures(): array
    {
        return collect(app(Schedule::class)->events())
            ->map(fn ($e) => $this->signature($e))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Convert a 5-part cron expression into a short human description.
     */
    protected function humanizeCron(string $expression): string
    {
        $parts = preg_split('/\s+/', trim($expression));

        if (count($parts) !== 5) {
            return $expression;
        }

        [$min, $hour, $day, $month, $weekday] = $parts;

        if ($min === '0' && $hour === '0' && $day === '*' && $month === '*') {
            return 'Setiap hari jam 00:00';
        }

        if (str_contains($min, '*/') && $hour === '*' && $day === '*' && $month === '*' && $weekday === '*') {
            $n = str_replace('*/', '', $min);

            return "Setiap {$n} menit";
        }

        if ($min === '0' && $hour === '*' && $day === '*' && $month === '*' && $weekday === '*') {
            return 'Setiap jam';
        }

        if (preg_match('/^\d+$/', $min) && preg_match('/^\d+$/', $hour)) {
            return sprintf('Setiap hari jam %02d:%02d', (int) $hour, (int) $min);
        }

        return $expression;
    }
}
