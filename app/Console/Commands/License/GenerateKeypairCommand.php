<?php

namespace App\Console\Commands\License;

use Illuminate\Console\Command;

class GenerateKeypairCommand extends Command
{
    protected $signature = 'license:generate-keypair
                            {--bits=2048 : Key size in bits (2048 or 4096)}
                            {--force : Overwrite existing keypair}
                            {--no-hash : Skip updating .env with public key hash}';

    protected $description = 'Generate RSA keypair for license signing (JWT RS256)';

    public function handle(): int
    {
        $bits = (int) ($this->option('bits') ?: 2048);
        if (! in_array($bits, [2048, 4096])) {
            $this->error('Key size must be 2048 or 4096 bits.');
            return self::FAILURE;
        }

        $storagePath = storage_path('app');
        $privateKeyPath = "{$storagePath}/vendor-private.pem";
        $publicKeyPath = "{$storagePath}/vendor-public.pem";

        if (! is_dir($storagePath)) {
            mkdir($storagePath, 0700, true);
        }

        if ((file_exists($privateKeyPath) || file_exists($publicKeyPath)) && ! $this->option('force')) {
            $this->warn('Keypair already exists. Use --force to overwrite.');
            $this->line("  Private: {$privateKeyPath}");
            $this->line("  Public:  {$publicKeyPath}");

            if (file_exists($publicKeyPath)) {
                $existingHash = hash('sha256', file_get_contents($publicKeyPath));
                $this->line("  SHA256:  {$existingHash}");
            }

            return self::SUCCESS;
        }

        if ($this->option('force') && file_exists($privateKeyPath)) {
            $backup = "{$privateKeyPath}.bak.".time();
            copy($privateKeyPath, $backup);
            $this->line("Backed up old private key to: {$backup}");
        }

        if ($this->option('force') && file_exists($publicKeyPath)) {
            $backup = "{$publicKeyPath}.bak.".time();
            copy($publicKeyPath, $backup);
            $this->line("Backed up old public key to: {$backup}");
        }

        $this->info("Generating {$bits}-bit RSA keypair...");

        $config = [
            'private_key_bits' => $bits,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
            'digest_alg'       => 'sha256',
        ];

        $key = openssl_pkey_new($config);

        if ($key === false) {
            $this->error('OpenSSL key generation failed: '.openssl_error_string());
            return self::FAILURE;
        }

        $exported = openssl_pkey_export($key, $privateKeyOut);
        if (! $exported) {
            $this->error('Failed to export private key: '.openssl_error_string());
            return self::FAILURE;
        }

        $details = openssl_pkey_get_details($key);
        if ($details === false) {
            $this->error('Failed to get key details: '.openssl_error_string());
            return self::FAILURE;
        }

        $publicKeyOut = $details['key'];

        file_put_contents($privateKeyPath, $privateKeyOut);
        file_put_contents($publicKeyPath, $publicKeyOut);

        chmod($privateKeyPath, 0600);
        chmod($publicKeyPath, 0644);

        $publicKeyHash = hash('sha256', $publicKeyOut);

        $this->newLine();
        $this->info('RSA keypair generated successfully.');

        $this->line('');
        $this->line("  <comment>Private key:</comment> {$privateKeyPath}");
        $this->line("  <comment>Permission:</comment>   600 (owner read/write only)");
        $this->line('');
        $this->line("  <comment>Public key:</comment>  {$publicKeyPath}");
        $this->line("  <comment>Permission:</comment>   644 (world readable)");
        $this->line('');
        $this->line("  <comment>Key size:</comment>     {$details['bits']} bits");
        $this->line("  <comment>Type:</comment>         {$details['type']}");
        $this->line("  <comment>SHA256 hash:</comment>  {$publicKeyHash}");

        if (! $this->option('no-hash')) {
            $this->updateEnvHash($publicKeyHash);
        }

        $this->newLine();
        $this->warn('IMPORTANT: Store the private key securely. If lost, ALL issued licenses become invalid.');
        $this->warn('Distribute the public key to hotel property installations for token verification.');

        return self::SUCCESS;
    }

    protected function updateEnvHash(string $hash): void
    {
        $envPath = base_path('.env');

        if (! file_exists($envPath)) {
            $this->warn('.env file not found. Skipping hash update.');
            $this->line("Add this to your .env: LICENSE_PUBLIC_KEY_HASH={$hash}");
            return;
        }

        $content = file_get_contents($envPath);

        if (str_contains($content, 'LICENSE_PUBLIC_KEY_HASH=')) {
            $content = preg_replace(
                '/^LICENSE_PUBLIC_KEY_HASH=.*/m',
                "LICENSE_PUBLIC_KEY_HASH={$hash}",
                $content
            );
        } else {
            $content .= "\nLICENSE_PUBLIC_KEY_HASH={$hash}\n";
        }

        file_put_contents($envPath, $content);
        $this->line('');
        $this->info('.env updated with LICENSE_PUBLIC_KEY_HASH.');
    }
}
