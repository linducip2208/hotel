<?php

declare(strict_types=1);

namespace App\Services\Compliance;

use App\Models\ArInvoice;
use App\Models\EFakturRecord;
use App\Models\Property;
use App\Services\Indonesia\CoretaxService;
use Illuminate\Support\Facades\App;

class CoretaxClient
{
    public function __construct(protected ?CoretaxService $coretax = null)
    {
        $this->coretax ??= App::make(CoretaxService::class);
    }

    public function pushFaktur(Property $property, EFakturRecord $faktur): array
    {
        $invoice = $faktur->invoice ?? ArInvoice::find($faktur->invoice_id);

        if (! $invoice) {
            $faktur->markFailed('No AR Invoice associated with this e-Faktur record.');
            return ['ok' => false, 'message' => 'No AR Invoice associated.'];
        }

        try {
            $result = $this->coretax->pushFaktur($invoice);
            return ['ok' => $result['success'], 'message' => $result['data']['nomor_faktur'] ?? 'OK'];
        } catch (\Throwable $e) {
            return ['ok' => false, 'message' => $e->getMessage()];
        }
    }

    public function checkStatus(EFakturRecord $faktur): array
    {
        try {
            $result = $this->coretax->checkFakturStatus($faktur->nomor_faktur);
            return ['ok' => $result['success'], 'status' => $result['data']['status'] ?? $faktur->status];
        } catch (\Throwable $e) {
            return ['ok' => false, 'status' => $faktur->status, 'message' => $e->getMessage()];
        }
    }
}
