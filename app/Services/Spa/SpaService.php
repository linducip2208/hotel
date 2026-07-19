<?php

namespace App\Services\Spa;

use App\Models\Folio;
use App\Models\SpaAppointment;
use App\Models\SpaTreatment;
use App\Services\Fo\FolioService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SpaService
{
    public function __construct(protected FolioService $folioSvc) {}

    public function book(array $data): SpaAppointment
    {
        return DB::transaction(function () use ($data) {
            $treatment = SpaTreatment::findOrFail($data['treatment_id']);
            $start = Carbon::parse($data['start_at']);
            $end = $start->copy()->addMinutes($treatment->duration_minutes);

            return SpaAppointment::create([
                'property_id' => $treatment->property_id,
                'treatment_id' => $treatment->id,
                'therapist_id' => $data['therapist_id'] ?? null,
                'cabin_id' => $data['cabin_id'] ?? null,
                'guest_id' => $data['guest_id'] ?? null,
                'reservation_id' => $data['reservation_id'] ?? null,
                'folio_id' => $data['folio_id'] ?? null,
                'start_at' => $start,
                'end_at' => $end,
                'status' => 'booked',
                'price' => $data['price'] ?? $treatment->price,
                'notes' => $data['notes'] ?? null,
            ]);
        });
    }

    public function complete(SpaAppointment $a): void
    {
        $a->update(['status' => 'completed']);

        if ($a->folio_id) {
            $folio = Folio::find($a->folio_id);
            if ($folio) {
                $this->folioSvc->postCharge($folio, [
                    'description' => 'Spa: '.$a->treatment?->name,
                    'category' => 'spa',
                    'amount' => $a->price,
                    'tax_code' => 'PPN_OUT',
                    'is_taxable' => true,
                    'source_type' => 'spa_appointment',
                    'source_ref' => (string) $a->id,
                ]);
            }
        }
    }
}
