<?php

declare(strict_types=1);

namespace App\Support;

final readonly class PhoneNumber
{
    public function __construct(
        public string $countryCode,
        public string $number,
        public string $full,
    ) {
    }

    public static function parse(string $raw): self
    {
        $raw = trim($raw);
        $raw = preg_replace('/[^\d+]/', '', $raw);

        if (str_starts_with($raw, '+')) {
            $raw = substr($raw, 1);
        }

        // Detect Indonesian numbers
        if (str_starts_with($raw, '62')) {
            $countryCode = '62';
            $number = substr($raw, 2);
        } elseif (str_starts_with($raw, '0')) {
            $countryCode = '62';
            $number = substr($raw, 1);
        } else {
            $countryCode = '';
            $number = $raw;
        }

        $full = $countryCode ? '+' . $countryCode . $number : $number;

        return new self(
            countryCode: $countryCode,
            number: $number,
            full: $full,
        );
    }

    public function toE164(): string
    {
        return '+' . $this->countryCode . $this->number;
    }

    public function toReadable(): string
    {
        $digits = $this->number;
        $len = strlen($digits);

        if ($len < 7) {
            return '+' . $this->countryCode . ' ' . $digits;
        }

        $parts = [
            substr($digits, 0, 3),
            substr($digits, 3, 4),
            substr($digits, 7),
        ];

        $readable = implode('-', array_filter($parts));

        return '+' . $this->countryCode . ' ' . $readable;
    }

    public function isIndonesian(): bool
    {
        return $this->countryCode === '62';
    }

    public function masked(): string
    {
        $len = strlen($this->number);

        if ($len <= 4) {
            return str_repeat('*', $len);
        }

        $visible = substr($this->number, -4);
        $masked = str_repeat('*', $len - 4);

        return '+' . $this->countryCode . ' ' . $masked . $visible;
    }
}
