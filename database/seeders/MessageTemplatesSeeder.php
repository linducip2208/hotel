<?php

namespace Database\Seeders;

use App\Models\MessageTemplate;
use App\Models\Property;
use Illuminate\Database\Seeder;

class MessageTemplatesSeeder extends Seeder
{
    public function run(): void
    {
        Property::each(function (Property $p) {
            $items = [
                ['name' => 'Booking Confirmation Email', 'channel' => 'email', 'locale' => 'id',
                    'subject' => 'Booking Anda Terkonfirmasi — {{ref}}',
                    'body' => "Halo {{guest_name}},\n\nTerima kasih telah memilih {{property_name}}.\n\nDetail booking:\n- Ref: {{ref}}\n- Check-in: {{check_in}}\n- Check-out: {{check_out}}\n- Total: Rp {{total}}\n\nManage booking: {{manage_url}}\n\nSampai jumpa!",
                    'variables' => ['guest_name', 'ref', 'check_in', 'check_out', 'total', 'property_name', 'manage_url']],
                ['name' => 'Booking Cancelled Email', 'channel' => 'email', 'locale' => 'id',
                    'subject' => 'Booking {{ref}} Dibatalkan',
                    'body' => "Halo {{guest_name}},\n\nBooking Anda telah dibatalkan.\n- Ref: {{ref}}\n- Penalty: Rp {{penalty}}\n- Refund: Rp {{refund}}\n\nKami harap dapat melayani Anda di lain waktu.",
                    'variables' => ['guest_name', 'ref', 'penalty', 'refund']],
                ['name' => 'Pre Check-in Reminder WA', 'channel' => 'whatsapp', 'locale' => 'id',
                    'body' => "Halo {{guest_name}}! Besok Anda check-in di {{property_name}}. Untuk speed-up, silakan pre check-in di {{precheckin_url}} 🙏",
                    'variables' => ['guest_name', 'property_name', 'precheckin_url']],
                ['name' => 'Review Request Email', 'channel' => 'email', 'locale' => 'id',
                    'subject' => 'Bagaimana pengalaman Anda di {{property_name}}?',
                    'body' => "Halo {{guest_name}},\n\nTerima kasih atas kunjungan Anda. Mohon luangkan 1 menit untuk review:\n{{review_url}}\n\nSebagai apresiasi, dapatkan diskon 10% untuk stay berikutnya dengan kode REVIEW10.",
                    'variables' => ['guest_name', 'property_name', 'review_url']],
                ['name' => 'Booking Confirmation Email (EN)', 'channel' => 'email', 'locale' => 'en',
                    'subject' => 'Booking Confirmed — {{ref}}',
                    'body' => "Dear {{guest_name}},\n\nThank you for choosing {{property_name}}.\n\nBooking details:\n- Ref: {{ref}}\n- Check-in: {{check_in}}\n- Check-out: {{check_out}}\n- Total: Rp {{total}}\n\nManage booking: {{manage_url}}\n\nSee you soon!",
                    'variables' => ['guest_name', 'ref', 'check_in', 'check_out', 'total', 'property_name', 'manage_url']],
            ];
            foreach ($items as $i) {
                MessageTemplate::updateOrCreate(
                    ['property_id' => $p->id, 'name' => $i['name']],
                    $i + ['is_active' => true]
                );
            }
        });
    }
}
