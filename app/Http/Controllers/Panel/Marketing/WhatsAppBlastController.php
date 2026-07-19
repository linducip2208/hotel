<?php

namespace App\Http\Controllers\Panel\Marketing;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Services\Marketing\WhatsAppBlastService;
use Illuminate\Http\Request;

class WhatsAppBlastController extends Controller
{
    public function __construct(protected WhatsAppBlastService $blast) {}

    public function index()
    {
        $property = app('current_property');
        $provider = $this->blast->resolveProvider($property);
        $segments = [
            'vip' => 'VIP (High Value)',
            'at_risk' => 'Berisiko Churn',
            'loyal' => 'Loyal',
            'inactive' => 'Non-Aktif > 6 Bulan',
        ];
        $templates = [
            'Promo Spesial untuk {name}! Dapatkan diskon 20% untuk menginap berikutnya. Booking sekarang.',
            'Halo {name}, kami rindu Anda! Sudah {stays} kali menginap bersama kami. Ada penawaran spesial untuk tamu setia.',
            'Selamat {name}! Anda terpilih mendapat voucher spa gratis saat menginap berikutnya. Info lengkap hubungi kami.',
        ];
        return view('panel.marketing.whatsapp-blast', compact('property', 'provider', 'segments', 'templates'));
    }

    public function previewRecipients(Request $request)
    {
        $property = app('current_property');
        $guests = $this->blast->getTargetedGuests($property, $request->only(['segment', 'min_stays', 'min_ltv']));
        return response()->json(['count' => count($guests), 'guests' => $guests]);
    }

    public function send(Request $request)
    {
        $request->validate([
            'guest_ids' => 'required|array|min:1',
            'message' => 'required|string|max:4096',
            'delay' => 'nullable|integer|min:1|max:60',
        ]);

        $property = app('current_property');
        $result = $this->blast->sendBlast($request->guest_ids, $request->message, $property, $request->integer('delay', 5));

        return back()->with('success', "WhatsApp blast selesai. Terkirim: {$result['sent']}, Gagal: {$result['failed']} dari {$result['total']} tamu.");
    }

    public function testSend(Request $request)
    {
        $request->validate(['phone' => 'required|string', 'message' => 'required|string']);
        $result = $this->blast->send($request->phone, $request->message, app('current_property'));
        return back()->with($result['status'] === 'ok' ? 'success' : 'error', $result['message'] ?? 'Terjadi kesalahan.');
    }
}
