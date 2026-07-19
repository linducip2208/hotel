<?php

declare(strict_types=1);

namespace App\Support;

use NumberFormatter;

final readonly class Money
{
    public function __construct(
        public int $amount,
        public string $currency = 'IDR',
    ) {
    }

    public static function fromFloat(float $amount, string $currency = 'IDR'): self
    {
        return new self((int) round($amount), $currency);
    }

    public static function fromInt(int $amount, string $currency = 'IDR'): self
    {
        return new self($amount, $currency);
    }

    public function toFloat(): float
    {
        return $this->amount / 100;
    }

    public function format(string $locale = 'id_ID'): string
    {
        $formatter = new NumberFormatter($locale, NumberFormatter::CURRENCY);

        return $formatter->formatCurrency($this->amount / 100, $this->currency);
    }

    public function add(Money $other): self
    {
        $this->guardSameCurrency($other);

        return new self($this->amount + $other->amount, $this->currency);
    }

    public function subtract(Money $other): self
    {
        $this->guardSameCurrency($other);

        return new self($this->amount - $other->amount, $this->currency);
    }

    public function multiply(float $factor): self
    {
        return new self((int) round($this->amount * $factor), $this->currency);
    }

    public function equals(Money $other): bool
    {
        return $this->amount === $other->amount && $this->currency === $other->currency;
    }

    public function isGreaterThan(Money $other): bool
    {
        $this->guardSameCurrency($other);

        return $this->amount > $other->amount;
    }

    public function isLessThan(Money $other): bool
    {
        $this->guardSameCurrency($other);

        return $this->amount < $other->amount;
    }

    public function isZero(): bool
    {
        return $this->amount === 0;
    }

    public function __toString(): string
    {
        return $this->format();
    }

    private function guardSameCurrency(Money $other): void
    {
        if ($this->currency !== $other->currency) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Currency mismatch: cannot operate on %s and %s.',
                    $this->currency,
                    $other->currency,
                ),
            );
        }
    }
}
