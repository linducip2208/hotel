<?php

declare(strict_types=1);

namespace App\Support;

use Carbon\Carbon;

final readonly class DateRange
{
    public function __construct(
        public Carbon $start,
        public Carbon $end,
    ) {
    }

    public static function of(Carbon|string $start, Carbon|string $end): self
    {
        $start = $start instanceof Carbon ? $start : Carbon::parse($start);
        $end = $end instanceof Carbon ? $end : Carbon::parse($end);

        if ($start->greaterThan($end)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Start date (%s) must be before or equal to end date (%s).',
                    $start->toDateString(),
                    $end->toDateString(),
                ),
            );
        }

        return new self($start, $end);
    }

    public function nights(): int
    {
        return (int) $this->start->diffInDays($this->end);
    }

    public function overlaps(DateRange $other): bool
    {
        return $this->start->lessThan($other->end)
            && $this->end->greaterThan($other->start);
    }

    public function contains(Carbon $date): bool
    {
        return $date->greaterThanOrEqualTo($this->start)
            && $date->lessThanOrEqualTo($this->end);
    }

    public function toArray(): array
    {
        return [
            'start' => $this->start->toDateString(),
            'end' => $this->end->toDateString(),
            'nights' => $this->nights(),
        ];
    }

    public function toString(): string
    {
        return sprintf('%s ~ %s', $this->start->toDateString(), $this->end->toDateString());
    }

    /**
     * @return Carbon[]
     */
    public function eachDay(): array
    {
        $days = [];
        $cursor = $this->start->copy();

        while ($cursor->lessThanOrEqualTo($this->end)) {
            $days[] = $cursor->copy();
            $cursor->addDay();
        }

        return $days;
    }
}
