<?php

namespace Database\Seeders;

use App\Models\DocumentTemplate;
use App\Models\Property;
use Illuminate\Database\Seeder;

class DocumentTemplatesSeeder extends Seeder
{
    public function run(): void
    {
        Property::each(function (Property $p) {
            $items = [
                [
                    'type' => 'email_confirmation', 'locale' => 'id', 'name' => 'Default Booking Confirmation (ID)',
                    'body_html' => "<h2>Hai {{guest_name}},</h2><p>Booking Anda di <strong>{{property_name}}</strong> telah dikonfirmasi.</p><table><tr><td>Ref</td><td>{{ref}}</td></tr><tr><td>Check-in</td><td>{{check_in}}</td></tr><tr><td>Check-out</td><td>{{check_out}}</td></tr><tr><td>Total</td><td>Rp {{total}}</td></tr></table><p>Manage booking: <a href=\"{{manage_url}}\">{{manage_url}}</a></p>",
                ],
                [
                    'type' => 'email_confirmation', 'locale' => 'en', 'name' => 'Default Booking Confirmation (EN)',
                    'body_html' => "<h2>Dear {{guest_name}},</h2><p>Your booking at <strong>{{property_name}}</strong> is confirmed.</p><table><tr><td>Ref</td><td>{{ref}}</td></tr><tr><td>Check-in</td><td>{{check_in}}</td></tr><tr><td>Check-out</td><td>{{check_out}}</td></tr><tr><td>Total</td><td>Rp {{total}}</td></tr></table><p>Manage booking: <a href=\"{{manage_url}}\">{{manage_url}}</a></p>",
                ],
                [
                    'type' => 'invoice', 'locale' => 'id', 'name' => 'Default Invoice (ID)',
                    'header_html' => "<div style=\"text-align:center\"><h1>{{property_name}}</h1><p>{{property_address}}</p></div>",
                    'body_html' => "<h2>INVOICE {{invoice_no}}</h2><p>Tanggal: {{invoice_date}}</p><p>Untuk: {{guest_name}}</p><table>{{lines_html}}</table><p><strong>Total: Rp {{total}}</strong></p>",
                    'footer_html' => "<p style=\"text-align:center;font-size:11px;color:#888\">Terima kasih atas kunjungan Anda.</p>",
                    'is_default' => true,
                ],
                [
                    'type' => 'registration_card', 'locale' => 'id', 'name' => 'Default Registration Card (ID)',
                    'body_html' => "<h2>KARTU REGISTRASI TAMU</h2><p>Nama: {{guest_name}}</p><p>No KTP/Paspor: {{id_number}}</p><p>Kewarganegaraan: {{nationality}}</p><p>Check-in: {{check_in}} · Check-out: {{check_out}}</p><p>Kamar: {{room_number}}</p><p style=\"margin-top:60px\">Tanda tangan tamu: ____________________</p>",
                    'is_default' => true,
                ],
                [
                    'type' => 'beo', 'locale' => 'id', 'name' => 'Default BEO Sheet',
                    'body_html' => "<h1>BANQUET EVENT ORDER</h1><p>{{event_no}}</p><p>Event: {{event_title}}</p><p>Tanggal: {{event_date}}</p><p>Function Room: {{function_room}}</p><p>Jumlah Tamu: {{attendees}}</p><p>Setup: {{setup}}</p><p>{{menu_html}}</p>",
                    'is_default' => true,
                ],
            ];
            foreach ($items as $i) {
                DocumentTemplate::updateOrCreate(
                    ['property_id' => $p->id, 'type' => $i['type'], 'locale' => $i['locale'], 'name' => $i['name']],
                    $i + ['is_active' => true]
                );
            }
        });
    }
}
