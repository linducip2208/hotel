<?php

namespace App\Services\Pdf;

use App\Models\Folio;
use Dompdf\Dompdf;
use Dompdf\Options;

class InvoicePdfGenerator
{
    public function generate(Folio $folio): string
    {
        $folio->loadMissing(['charges', 'payments', 'reservation.primaryGuest', 'property']);
        $html = view('panel.fo.folios.invoice', compact('folio'))->render();

        $options = new Options();
        $options->set('isRemoteEnabled', false);
        $options->set('defaultFont', 'Helvetica');
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4');
        $dompdf->render();
        return $dompdf->output();
    }
}
