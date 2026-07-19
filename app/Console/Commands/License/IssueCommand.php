<?php

namespace App\Console\Commands\License;

use Carbon\Carbon;
use Firebase\JWT\JWT;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class IssueCommand extends Command
{
    protected $signature = 'license:issue
                            {--domain= : Domain name tied to this license}
                            {--email= : Licensee email address}
                            {--plan=premium : Plan key (premium, enterprise, basic)}
                            {--expires= : Expiry date (YYYY-MM-DD). Default: 1 year from now}
                            {--properties=1 : Maximum number of properties}
                            {--rooms= : Maximum rooms (unlimited if not set)}
                            {--users= : Maximum users (unlimited if not set)}
                            {--features= : Comma-separated extra feature flags}
                            {--json : Output as JSON}';

    protected $description = 'Issue a new license key for a hotel property';

    public function handle(): int
    {
        $domain = $this->option('domain') ?: $this->ask('License domain (e.g. hotel-customer.com)');
        $email = $this->option('email') ?: $this->ask('Licensee email address');
        $plan = $this->option('plan') ?: 'premium';
        $properties = (int) ($this->option('properties') ?: 1);

        $expiresInput = $this->option('expires');
        if ($expiresInput) {
            $expiresAt = Carbon::parse($expiresInput)->startOfDay();
        } else {
            $expiresAt = Carbon::now()->addYear()->startOfDay();
        }

        $rooms = $this->option('rooms') ? (int) $this->option('rooms') : null;
        $users = $this->option('users') ? (int) $this->option('users') : null;

        $featuresInput = $this->option('features') ?: '';
        $features = array_filter(explode(',', $featuresInput));

        $privateKey = $this->loadPrivateKey();
        if ($privateKey === null) {
            $this->error('Vendor private key not found. Run license:generate-keypair first.');
            $this->line('Expected at: storage/app/vendor-private.pem');
            return self::FAILURE;
        }

        $now = Carbon::now();
        $licenseId = $this->generateLicenseId();

        $payload = [
            'iss' => config('app.url', 'https://license.hotelhub.id'),
            'sub' => $licenseId,
            'iat' => $now->timestamp,
            'nbf' => $now->timestamp,
            'exp' => $expiresAt->timestamp,
            'jti' => Str::uuid()->toString(),
            'license' => [
                'id'         => $licenseId,
                'domain'     => $domain,
                'email'      => $email,
                'plan'       => $plan,
                'issued_at'  => $now->toIso8601String(),
                'expires_at' => $expiresAt->toIso8601String(),
                'properties' => $properties,
                'max_rooms'  => $rooms,
                'max_users'  => $users,
                'features'   => $features,
            ],
        ];

        $token = JWT::encode($payload, $privateKey, 'RS256');

        $activationCode = $this->generateActivationCode();

        $this->storeLocalLicense($licenseId, $token, $domain, $email, $plan, $expiresAt, $properties, $rooms, $users, $features);

        if ($this->option('json')) {
            $this->line(json_encode([
                'license_id'      => $licenseId,
                'license_key'     => $token,
                'activation_code' => $activationCode,
                'domain'          => $domain,
                'email'           => $email,
                'plan'            => $plan,
                'expires_at'      => $expiresAt->toIso8601String(),
                'properties'      => $properties,
                'max_rooms'       => $rooms,
                'max_users'       => $users,
                'features'        => $features,
                'issued_at'       => $now->toIso8601String(),
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            return self::SUCCESS;
        }

        $this->newLine();
        $this->info('License issued successfully.');
        $this->newLine();
        $this->line("  <comment>License ID:</comment>      {$licenseId}");
        $this->line("  <comment>Domain:</comment>          {$domain}");
        $this->line("  <comment>Email:</comment>           {$email}");
        $this->line("  <comment>Plan:</comment>            {$plan}");
        $this->line("  <comment>Properties:</comment>      {$properties}");
        $this->line("  <comment>Expires:</comment>         {$expiresAt->toDateString()}");
        $this->line("  <comment>Features:</comment>        ".($features ? implode(', ', $features) : 'none'));
        $this->newLine();
        $this->line("  <comment>License Key (JWT):</comment>");
        $this->line("  {$token}");
        $this->newLine();
        $this->line("  <comment>Activation Code:</comment>");
        $this->line("  {$activationCode}");
        $this->newLine();
        $this->info('Share the License Key and Activation Code with the property owner.');
        $this->info('They will enter both during the initial setup wizard.');

        return self::SUCCESS;
    }

    protected function loadPrivateKey(): ?string
    {
        $path = storage_path('app/vendor-private.pem');

        if (! is_readable($path)) {
            return null;
        }

        $content = file_get_contents($path);
        if (! $content || str_contains($content, 'PLACEHOLDER')) {
            return null;
        }

        return $content;
    }

    protected function generateLicenseId(): string
    {
        return 'LIC-'.strtoupper(Str::random(4)).'-'.strtoupper(Str::random(4)).'-'.strtoupper(Str::random(4));
    }

    protected function generateActivationCode(): string
    {
        $blocks = [];
        for ($i = 0; $i < 4; $i++) {
            $blocks[] = strtoupper(Str::random(4));
        }
        return implode('-', $blocks);
    }

    protected function storeLocalLicense(
        string $licenseId,
        string $token,
        string $domain,
        string $email,
        string $plan,
        Carbon $expiresAt,
        int $properties,
        ?int $rooms,
        ?int $users,
        array $features
    ): void {
        if (! class_exists(\App\Models\LocalLicense::class)) {
            return;
        }

        try {
            \App\Models\LocalLicense::create([
                'install_id'               => $licenseId,
                'license_key_hash'         => hash('sha256', $token),
                'token_encrypted'          => $token,
                'status'                   => 'unpaired',
                'plan'                     => $plan,
                'features'                 => $features,
                'max_rooms'                => $rooms,
                'max_users'                => $users,
                'max_properties'           => $properties,
                'valid_until'              => $expiresAt,
            ]);
        } catch (\Throwable $e) {
            $this->warn('Could not store license in local database: '.$e->getMessage());
        }
    }
}
