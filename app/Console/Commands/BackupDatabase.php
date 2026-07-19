<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class BackupDatabase extends Command
{
    protected $signature = 'hotel:backup-database';
    protected $description = 'Backup database to storage/backups';

    public function handle()
    {
        $filename = 'backup-' . now()->format('Y-m-d-H-i-s') . '.sql';
        $path = storage_path('backups/' . $filename);

        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        $db = config('database.connections.' . config('database.default'));
        $command = sprintf(
            'mysqldump -h%s -u%s -p%s %s > %s',
            escapeshellarg($db['host']),
            escapeshellarg($db['username']),
            escapeshellarg($db['password']),
            escapeshellarg($db['database']),
            escapeshellarg($path)
        );

        exec($command, $output, $exitCode);

        if ($exitCode === 0) {
            $this->cleanOldBackups(14);
            $this->info('Backup created: ' . $filename);
        } else {
            $this->error('Backup failed');
        }
    }

    private function cleanOldBackups(int $keep): void
    {
        $files = glob(storage_path('backups/backup-*.sql'));
        if (!$files) return;

        rsort($files);
        foreach (array_slice($files, $keep) as $file) {
            unlink($file);
        }
    }
}
