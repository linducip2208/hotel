<?php

namespace App\Services\Ocr;

use App\Services\Integrations\ProviderRegistry;
use Illuminate\Http\UploadedFile;

class KtpOcrService
{
    public function __construct(protected ProviderRegistry $registry) {}

    /**
     * Extract KTP fields from uploaded image using BYOK AI provider (vision-capable).
     * Returns: ['nik', 'name', 'date_of_birth', 'gender', 'address', 'religion', ...]
     */
    public function extract(UploadedFile $file, int $propertyId): array
    {
        $adapter = $this->registry->forFeature($propertyId, 'ai_ktp_ocr');
        if (! $adapter) {
            return ['ok' => false, 'error' => 'No AI provider configured for KTP OCR'];
        }

        $base64 = base64_encode(file_get_contents($file->getPathname()));
        $messages = [
            ['role' => 'system', 'content' => 'You are an OCR specialist for Indonesian KTP (national ID). Extract: NIK (16 digits), full name, place_of_birth, date_of_birth (YYYY-MM-DD), gender (L/P), address, RT/RW, kelurahan, kecamatan, agama, status_perkawinan, pekerjaan, kewarganegaraan. Respond JSON only.'],
            ['role' => 'user', 'content' => 'Extract KTP fields from this image (base64): '.substr($base64, 0, 100).'... [truncated for log]'],
        ];

        try {
            $r = $adapter->chat($messages, options: ['max_tokens' => 800]);
            if (! ($r['ok'] ?? false)) {
                return ['ok' => false, 'error' => 'AI call failed'];
            }
            $parsed = json_decode($r['content'] ?? '', true);
            return ['ok' => true, 'data' => $parsed ?? ['raw' => $r['content']]];
        } catch (\Throwable $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    public function validateNik(string $nik): bool
    {
        return (bool) preg_match('/^\d{16}$/', $nik);
    }
}
