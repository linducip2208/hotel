<?php

namespace App\Services\Fo;

use App\Models\DigitalRegistration;
use App\Models\Reservation;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class DigitalRegistrationService
{
    public function createForReservation(Reservation $reservation): DigitalRegistration
    {
        $existing = DigitalRegistration::where('reservation_id', $reservation->id)->first();
        if ($existing) {
            return $existing;
        }

        return DigitalRegistration::create([
            'property_id'   => $reservation->property_id,
            'reservation_id'=> $reservation->id,
            'guest_id'      => $reservation->primary_guest_id,
            'status'        => 'pending',
            'token'         => Str::random(64),
        ]);
    }

    public function sendToGuest(DigitalRegistration $reg): void
    {
        $guest = $reg->guest;
        $url = route('registration.form', ['token' => $reg->token]);

        if ($guest->phone) {
            $whatsapp = app(\App\Services\Marketing\WhatsAppBlastService::class);
            $provider = $whatsapp->resolveProvider($reg->property);
            if ($provider) {
                $whatsapp->send($guest->phone, "Selamat datang di {$reg->property->name}! Silakan lengkapi registrasi digital Anda: {$url}", $reg->property);
            }
        }

        $reg->update(['status' => 'sent', 'sent_at' => now()]);
    }

    public function recordView(DigitalRegistration $reg, string $ip, string $ua): void
    {
        if (in_array($reg->status, ['pending', 'sent'])) {
            $reg->update([
                'status'     => 'viewed',
                'viewed_at'  => now(),
                'ip_address' => $ip,
                'user_agent' => $ua,
            ]);
        }
    }

    public function sign(DigitalRegistration $reg, array $formData, string $signatureBase64, ?string $idPhoto = null): void
    {
        $signaturePath = null;
        if ($signatureBase64) {
            $image = str_replace('data:image/png;base64,', '', $signatureBase64);
            $image = str_replace(' ', '+', $image);
            $filename = 'signatures/' . $reg->id . '_' . time() . '.png';
            Storage::disk('public')->put($filename, base64_decode($image));
            $signaturePath = $filename;
        }

        $reg->update([
            'status'           => 'signed',
            'signed_at'        => now(),
            'form_data'        => $formData,
            'signature_path'   => $signaturePath,
            'id_document_path' => $idPhoto,
        ]);
    }

    public function markCompleted(DigitalRegistration $reg): void
    {
        $reg->update(['status' => 'completed']);
    }
}
