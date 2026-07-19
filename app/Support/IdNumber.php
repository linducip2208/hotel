<?php

declare(strict_types=1);

namespace App\Support;

final readonly class IdNumber
{
    public function __construct(
        public string $type,
        public string $value,
    ) {
    }

    public static function parse(string $raw, string $type = 'ktp'): self
    {
        $value = trim($raw);
        $value = preg_replace('/\s+/', '', $value);

        if ($type === 'npwp') {
            $value = preg_replace('/[^\d.\-]/', '', $value);
        }

        return new self(type: $type, value: $value);
    }

    public function isValid(): bool
    {
        return match ($this->type) {
            'npwp' => $this->validateNpwp(),
            'ktp' => $this->validateKtp(),
            'passport' => $this->validatePassport(),
            'sim' => $this->validateSim(),
            'kitas' => $this->validateKitas(),
            default => false,
        };
    }

    public function masked(): string
    {
        $len = strlen($this->value);

        if ($len <= 4) {
            return str_repeat('*', $len);
        }

        $first = substr($this->value, 0, 2);
        $last = substr($this->value, -3);
        $middle = str_repeat('*', $len - 5);

        return $first . $middle . $last;
    }

    public function isNpwp(): bool
    {
        return $this->type === 'npwp';
    }

    public function isKtp(): bool
    {
        return $this->type === 'ktp';
    }

    public function isPassport(): bool
    {
        return $this->type === 'passport';
    }

    private function validateNpwp(): bool
    {
        $digits = preg_replace('/[^\d]/', '', $this->value);

        return strlen($digits) === 15;
    }

    private function validateKtp(): bool
    {
        $digits = preg_replace('/[^\d]/', '', $this->value);

        return strlen($digits) === 16;
    }

    private function validatePassport(): bool
    {
        return (bool) preg_match('/^[A-Z0-9]{6,12}$/i', $this->value);
    }

    private function validateSim(): bool
    {
        $digits = preg_replace('/[^\d]/', '', $this->value);

        return strlen($digits) >= 12 && strlen($digits) <= 16;
    }

    private function validateKitas(): bool
    {
        return strlen($this->value) >= 8;
    }
}
