<?php

namespace App\Services\Notifications;

use App\Adapters\Contracts\MailAdapterInterface;
use App\Adapters\Contracts\WhatsappAdapterInterface;
use App\Models\NotificationLog;
use App\Models\Reservation;
use App\Services\Integrations\ProviderRegistry;
use Illuminate\Support\Facades\Log;

class NotificationDispatcher
{
    public function __construct(protected ProviderRegistry $registry) {}

    public function bookingConfirmed(Reservation $reservation): void
    {
        $this->sendMail($reservation, 'Booking Confirmation — '.$reservation->ref, $this->bookingConfirmedHtml($reservation));
        $this->sendWhatsapp($reservation, 'booking_confirmation', [
            'parameters' => [
                ['type' => 'text', 'text' => $reservation->primaryGuest?->first_name],
                ['type' => 'text', 'text' => $reservation->ref],
            ],
        ]);
    }

    public function reservationCancelled(Reservation $reservation): void
    {
        $this->sendMail($reservation, 'Booking Cancelled — '.$reservation->ref, '<p>Your booking has been cancelled.</p>');
    }

    public function reviewRequest(Reservation $reservation): void
    {
        $url = url("/portal/review/{$reservation->ref}");
        $this->sendMail($reservation, 'Bagaimana pengalaman Anda?', "<p>Halo {$reservation->primaryGuest?->first_name},</p><p>Mohon luangkan waktu untuk review: <a href=\"$url\">$url</a></p>");
    }

    public function checkinReminder(Reservation $reservation): void
    {
        $checkIn = $reservation->check_in->format('d M Y');
        $this->sendMail(
            $reservation,
            "Reminder: Check-in besok — {$reservation->property->name}",
            "<p>Halo {$reservation->primaryGuest?->first_name},</p><p>Check-in Anda dijadwalkan besok, <strong>{$checkIn}</strong>.</p><p>Ref: <strong>{$reservation->ref}</strong></p>"
        );
        $this->sendWhatsapp($reservation, 'checkin_reminder', [
            'parameters' => [
                ['type' => 'text', 'text' => $reservation->primaryGuest?->first_name],
                ['type' => 'text', 'text' => $checkIn],
                ['type' => 'text', 'text' => $reservation->ref],
            ],
        ]);
    }

    protected function sendMail(Reservation $r, string $subject, string $html): void
    {
        $email = $r->primaryGuest?->email;
        if (! $email) return;

        try {
            /** @var MailAdapterInterface|null $adapter */
            $adapter = $this->registry->forFeature($r->property_id, 'mail_transactional');
            if ($adapter) {
                $adapter->send($email, $subject, $html);
            } else {
                Log::info("Mail (no provider): to={$email} subject={$subject}");
            }
        } catch (\Throwable $e) {
            Log::warning("Mail send failed: {$e->getMessage()}");
        }
    }

    protected function sendWhatsapp(Reservation $r, string $template, array $vars): void
    {
        $phone = $r->primaryGuest?->phone;
        if (! $phone) return;

        try {
            /** @var WhatsappAdapterInterface|null $adapter */
            $adapter = $this->registry->forFeature($r->property_id, 'wa_transactional');
            if ($adapter) {
                $adapter->send($phone, $template, $vars);
            }
        } catch (\Throwable $e) {
            Log::warning("WA send failed: {$e->getMessage()}");
        }
    }

    protected function bookingConfirmedHtml(Reservation $r): string
    {
        $name = $r->primaryGuest?->first_name;
        $url = url('/portal/booking/'.$r->ref);
        return <<<HTML
<p>Halo {$name},</p>
<p>Booking Anda terkonfirmasi.</p>
<table>
<tr><td>Ref:</td><td><strong>{$r->ref}</strong></td></tr>
<tr><td>Check-in:</td><td>{$r->check_in->format('d M Y')}</td></tr>
<tr><td>Check-out:</td><td>{$r->check_out->format('d M Y')}</td></tr>
<tr><td>Total:</td><td>Rp {$r->grand_total}</td></tr>
</table>
<p>Manage booking: <a href="{$url}">{$url}</a></p>
HTML;
    }

    public function dispatchFromLog(NotificationLog $log): void
    {
        try {
            match ($log->channel) {
                'email' => $this->dispatchMail($log),
                'whatsapp' => $this->dispatchWhatsapp($log),
                default => null,
            };

            $log->update(['status' => 'sent', 'sent_at' => now()]);
        } catch (\Throwable $e) {
            $log->update(['status' => 'failed', 'error' => $e->getMessage()]);
            Log::warning("Notification dispatch failed: {$e->getMessage()}");
        }
    }

    protected function dispatchMail(NotificationLog $log): void
    {
        /** @var MailAdapterInterface|null $adapter */
        $adapter = $this->registry->forFeature($log->property_id, 'mail_transactional');
        if ($adapter) {
            $metadata = $log->metadata ?? [];
            $subject = $metadata['subject'] ?? $log->event;
            $body = $metadata['body'] ?? '';
            $adapter->send($log->recipient, $subject, $body);
        } else {
            Log::info("Mail (no provider): to={$log->recipient} event={$log->event}");
        }
    }

    protected function dispatchWhatsapp(NotificationLog $log): void
    {
        /** @var WhatsappAdapterInterface|null $adapter */
        $adapter = $this->registry->forFeature($log->property_id, 'wa_transactional');
        if ($adapter) {
            $adapter->send($log->recipient, $log->event, $log->metadata ?? []);
        }
    }
}
