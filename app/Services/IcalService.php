<?php

namespace App\Services;

use App\Models\Reservation;

class IcalService
{
    /**
     * Build an iCalendar (RFC 5545) VEVENT for a booking so guests can
     * add the stay to Google Calendar / Outlook / Apple Calendar.
     */
    public function generate(Reservation $reservation): string
    {
        $reservation->loadMissing(['property', 'primaryGuest', 'rooms.room']);

        $now = now()->format('Ymd\THis\Z');
        $uid = sprintf('booking-%s@%s', $reservation->ref, parse_url(config('app.url'), PHP_URL_HOST) ?: 'hotel.local');

        $summary = $this->escape(sprintf(
            '%s — %s',
            $reservation->property?->name ?? config('app.name'),
            $this->escape($reservation->primaryGuest?->full_name ?? 'Guest')
        ));

        $location = $this->escape($this->buildLocation($reservation));
        $description = $this->escape($this->buildDescription($reservation));

        $dtStart = $reservation->check_in?->format('Ymd');
        $dtEnd = $reservation->check_out?->format('Ymd');

        // All-day events use DTEND as exclusive end date (check-out day).
        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//Hotel HMS//Booking Engine//EN',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
            'BEGIN:VEVENT',
            'UID:'.$uid,
            'DTSTAMP:'.$now,
            'DTSTART;VALUE=DATE:'.$dtStart,
            'DTEND;VALUE=DATE:'.$dtEnd,
            'SUMMARY:'.$summary,
            'LOCATION:'.$location,
            'DESCRIPTION:'.$description,
            'STATUS:CONFIRMED',
            'END:VEVENT',
            'END:VCALENDAR',
        ];

        return implode("\r\n", $lines)."\r\n";
    }

    public function filename(Reservation $reservation): string
    {
        return sprintf('%s-%s.ics', $reservation->ref, $reservation->check_in?->format('Ymd'));
    }

    protected function buildLocation(Reservation $reservation): string
    {
        $p = $reservation->property;
        if (! $p) {
            return '';
        }

        return trim(implode(', ', array_filter([$p->name, $p->address_line1, $p->city])));
    }

    protected function buildDescription(Reservation $reservation): string
    {
        $roomNames = $reservation->rooms->map(fn ($r) => $r->room?->name ?? $r->room_type_name ?? '')->filter()->implode(', ');

        return implode(' | ', array_filter([
            'Booking: '.$reservation->ref,
            $roomNames ? 'Room: '.$roomNames : null,
            'Check-in: '.$reservation->check_in?->format('d M Y'),
            'Check-out: '.$reservation->check_out?->format('d M Y'),
        ]));
    }

    protected function escape(string $value): string
    {
        return str_replace(
            ['\\', "\r\n", "\n", "\r", ',', ';'],
            ['\\\\', '\\n', '\\n', '\\n', '\\,', '\\;'],
            $value
        );
    }
}
