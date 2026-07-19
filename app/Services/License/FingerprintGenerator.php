<?php

namespace App\Services\License;

use Illuminate\Support\Str;

class FingerprintGenerator
{
    public function generate(?string $installId = null): string
    {
        $parts = [
            $this->machineId(),
            $this->primaryMacAddress(),
            php_uname('s').'-'.php_uname('r'),
            hash('sha256', base_path()),
            $installId ?? Str::uuid()->toString(),
        ];

        return 'sha256:'.hash('sha256', implode('|', array_filter($parts)));
    }

    public function newInstallId(): string
    {
        return (string) Str::uuid();
    }

    protected function machineId(): string
    {
        $candidates = [
            '/etc/machine-id',
            '/var/lib/dbus/machine-id',
        ];

        foreach ($candidates as $path) {
            if (is_readable($path)) {
                $id = trim((string) @file_get_contents($path));
                if ($id !== '') return $id;
            }
        }

        // Windows fallback — pakai computer name + system root
        return strtolower(gethostname() ?: 'unknown').'@'.($_SERVER['SystemRoot'] ?? 'unknown');
    }

    protected function primaryMacAddress(): string
    {
        $output = [];
        if (PHP_OS_FAMILY === 'Windows') {
            @exec('getmac /fo csv /nh', $output);
        } else {
            @exec("ip link show 2>/dev/null | awk '/link\\/ether/ {print $2; exit}'", $output);
        }

        foreach ($output as $line) {
            if (preg_match('/([0-9A-Fa-f]{2}[:-]){5}[0-9A-Fa-f]{2}/', $line, $m)) {
                return strtolower(str_replace('-', ':', $m[0]));
            }
        }

        return 'mac-unknown';
    }
}
