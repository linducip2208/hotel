<?php

namespace App\Console\Commands;

use App\Models\Guest;
use App\Models\NotificationLog;
use Illuminate\Console\Command;

class SendBirthdayGreetings extends Command
{
    protected $signature = 'hotel:send-birthday-greetings';
    protected $description = 'Kirim ucapan selamat ulang tahun kepada tamu yang berulang tahun hari ini';

    public function handle(): int
    {
        $today = now()->format('m-d');

        $guests = Guest::whereNotNull('date_of_birth')
            ->whereNotNull('email')
            ->where('marketing_consent', true)
            ->get()
            ->filter(fn ($g) => $g->date_of_birth->format('m-d') === $today);

        if ($guests->isEmpty()) {
            $this->info('Tidak ada tamu yang berulang tahun hari ini.');
            return self::SUCCESS;
        }

        $count = 0;
        foreach ($guests as $guest) {
            $message = $this->buildGreeting($guest);

            NotificationLog::create([
                'property_id' => $guest->property_id ?? 1,
                'notifiable_type' => Guest::class,
                'notifiable_id' => $guest->id,
                'channel' => 'email',
                'type' => 'birthday_greeting',
                'subject' => 'Selamat Ulang Tahun, ' . $guest->full_name . '!',
                'body' => $message,
                'status' => 'queued',
                'metadata' => [
                    'guest_name' => $guest->full_name,
                    'birthday' => $guest->date_of_birth->toDateString(),
                    'trigger' => 'scheduler:' . now()->toDateTimeString(),
                ],
            ]);

            $count++;
        }

        $this->info("{$count} ucapan ulang tahun telah diantrekan.");

        return self::SUCCESS;
    }

    protected function buildGreeting(Guest $guest): string
    {
        $age = $guest->date_of_birth->age;

        return <<<MSG
Halo {$guest->first_name},

Selamat ulang tahun yang ke-{$age}! 🎂

Dari seluruh tim di hotel kami, kami mengucapkan semoga Anda sehat selalu, sukses, dan penuh kebahagiaan.

Sebagai apresiasi untuk tamu setia kami, kami ingin memberikan penawaran spesial ulang tahun untuk kunjungan Anda berikutnya. Gunakan kode BDAY{$age} untuk mendapatkan diskon 15% pada reservasi berikutnya.

Sampai jumpa lagi!

Salam hangat,
Tim Hotel
MSG;
    }
}
