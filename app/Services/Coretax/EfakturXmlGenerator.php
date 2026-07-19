<?php

declare(strict_types=1);

namespace App\Services\Coretax;

use App\Models\ArInvoice;
use App\Models\EFakturRecord;
use App\Models\Folio;
use App\Models\Property;
use Carbon\Carbon;

class EfakturXmlGenerator
{
    public function generateForInvoice(ArInvoice $invoice, ?string $nsfp = null): array
    {
        $property = $invoice->property ?? Property::find($invoice->property_id);
        $arAccount = $invoice->arAccount;

        $nsfp = $nsfp ?? $this->getNextNsfp($property->id);
        $tglFaktur = $invoice->issued_at?->format('Y-m-d') ?? now()->format('Y-m-d');
        $kodeTransaksi = $this->determineKode($arAccount->npwp ?? null);

        $xml = $this->buildXml([
            'kdJenisTransaksi' => $kodeTransaksi,
            'fgPengganti' => '0',
            'nomorFaktur' => $nsfp,
            'tanggalFaktur' => $tglFaktur,
            'npwpPenjual' => $this->cleanNpwp($property->npwp ?? config('coretax.npwp_penjual')),
            'namaPenjual' => $property->legal_name ?? $property->name ?? config('app.name'),
            'alamatPenjual' => $property->address_line1 ?? '',
            'npwpPembeli' => $this->cleanNpwp($arAccount->npwp ?? null) ?: '000000000000000',
            'namaPembeli' => $arAccount->name ?? 'UMUM',
            'alamatPembeli' => '',
            'details' => $this->buildInvoiceLines($invoice),
            'totalDpp' => (float) $invoice->subtotal,
            'totalPpn' => (float) $invoice->tax_total,
            'totalPpnBm' => 0,
            'status' => 'normal',
        ]);

        $efaktur = EFakturRecord::updateOrCreate(
            ['invoice_id' => $invoice->id],
            [
                'property_id' => $invoice->property_id,
                'invoice_no' => $invoice->invoice_no,
                'nomor_faktur' => $nsfp,
                'kode_transaksi' => $kodeTransaksi,
                'kode_status' => '01',
                'npwp_penjual' => $property->npwp ?? config('coretax.npwp_penjual'),
                'npwp_pembeli' => $arAccount->npwp ?? '000000000000000',
                'dpp' => $invoice->subtotal,
                'ppn' => $invoice->tax_total,
                'status' => 'normal',
                'request_payload' => ['invoice_id' => $invoice->id, 'invoice_no' => $invoice->invoice_no],
                'response_payload' => ['xml' => $xml],
            ]
        );

        return [
            'success' => true,
            'nsfp' => $nsfp,
            'nomor_faktur' => $nsfp,
            'xml' => $xml,
            'efaktur_id' => $efaktur->id,
        ];
    }

    public function generateForFolio(Folio $folio, ?string $nsfp = null): array
    {
        $property = Property::find($folio->property_id) ?? app('current_property');
        $reservation = $folio->reservation;
        $guest = $reservation?->primaryGuest;
        $company = $reservation?->company;

        $nsfp = $nsfp ?? $this->getNextNsfp($folio->property_id);
        $fakturNo = str_pad((string) $folio->id, 8, '0', STR_PAD_LEFT);
        $tglFaktur = $folio->closed_at?->format('Y-m-d') ?? now()->format('Y-m-d');

        $npwpPembeli = $company->npwp ?? $guest?->id_number ?? null;
        $isNpwp = $npwpPembeli && $this->isValidNpwp($npwpPembeli);
        $kodeTransaksi = $isNpwp ? '01' : '04';

        $namaPembeli = $company?->name ?? $guest?->full_name ?? 'Tamu Hotel';
        $alamatPembeli = $company?->address_line1 ?? $guest?->address_line1 ?? '';

        $details = $this->buildFolioDetails($folio);

        $totalDpp = array_sum(array_column($details, 'dpp'));
        $totalPpn = array_sum(array_column($details, 'ppn'));

        $xml = $this->buildXml([
            'kdJenisTransaksi' => $kodeTransaksi,
            'fgPengganti' => '0',
            'nomorFaktur' => $nsfp,
            'tanggalFaktur' => $tglFaktur,
            'npwpPenjual' => $this->cleanNpwp($property->npwp ?? config('coretax.npwp_penjual')),
            'namaPenjual' => $property->legal_name ?? $property->name ?? config('app.name'),
            'alamatPenjual' => $property->address_line1 ?? '',
            'npwpPembeli' => $this->cleanNpwp($npwpPembeli) ?: '000000000000000',
            'namaPembeli' => $namaPembeli,
            'alamatPembeli' => $alamatPembeli,
            'details' => $details,
            'totalDpp' => round($totalDpp, 2),
            'totalPpn' => round($totalPpn, 2),
            'totalPpnBm' => 0,
            'status' => 'normal',
        ]);

        $efaktur = EFakturRecord::create([
            'property_id' => $folio->property_id,
            'invoice_id' => null,
            'invoice_no' => 'FOL-' . $folio->id,
            'nomor_faktur' => $nsfp,
            'kode_transaksi' => $kodeTransaksi,
            'kode_status' => '01',
            'npwp_penjual' => $property->npwp ?? config('coretax.npwp_penjual'),
            'npwp_pembeli' => $this->cleanNpwp($npwpPembeli) ?: '000000000000000',
            'dpp' => $totalDpp,
            'ppn' => $totalPpn,
            'status' => 'normal',
            'request_payload' => ['folio_id' => $folio->id, 'source' => 'folio'],
            'response_payload' => ['xml' => $xml],
            'source_type' => Folio::class,
            'source_id' => $folio->id,
        ]);

        return [
            'success' => true,
            'nsfp' => $nsfp,
            'nomor_faktur' => $nsfp,
            'xml' => $xml,
            'efaktur_id' => $efaktur->id,
        ];
    }

    /**
     * Build e-Faktur XML in DJP 4.0 format.
     */
    private function buildXml(array $data): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<Faktur xmlns="http://factur.pajak.go.id/4.0">' . "\n";
        $xml .= '  <KeteranganTambahan>Hotel Accommodation — ' . e($data['namaPenjual']) . '</KeteranganTambahan>' . "\n";
        $xml .= '  <KodeTransaksi>' . $data['kdJenisTransaksi'] . '</KodeTransaksi>' . "\n";
        $xml .= '  <FgPengganti>' . $data['fgPengganti'] . '</FgPengganti>' . "\n";
        $xml .= '  <NomorFaktur>' . $data['nomorFaktur'] . '</NomorFaktur>' . "\n";
        $xml .= '  <TanggalFaktur>' . $data['tanggalFaktur'] . '</TanggalFaktur>' . "\n";
        $xml .= '  <Penjual>' . "\n";
        $xml .= '    <NPWP>' . $data['npwpPenjual'] . '</NPWP>' . "\n";
        $xml .= '    <Nama>' . e($data['namaPenjual']) . '</Nama>' . "\n";
        $xml .= '    <Alamat>' . e($data['alamatPenjual']) . '</Alamat>' . "\n";
        $xml .= '  </Penjual>' . "\n";
        $xml .= '  <Pembeli>' . "\n";
        $xml .= '    <NPWP>' . $data['npwpPembeli'] . '</NPWP>' . "\n";
        $xml .= '    <Nama>' . e($data['namaPembeli']) . '</Nama>' . "\n";
        $xml .= '    <Alamat>' . e($data['alamatPembeli']) . '</Alamat>' . "\n";
        $xml .= '  </Pembeli>' . "\n";
        $xml .= '  <DetailTransaksi>' . "\n";

        foreach ($data['details'] as $detail) {
            $xml .= '    <Item>' . "\n";
            $xml .= '      <KodeBarang>' . $detail['kode'] . '</KodeBarang>' . "\n";
            $xml .= '      <NamaBarang>' . e($detail['nama']) . '</NamaBarang>' . "\n";
            $xml .= '      <HargaSatuan>' . number_format($detail['harga'], 2, '.', '') . '</HargaSatuan>' . "\n";
            $xml .= '      <Jumlah>' . $detail['jumlah'] . '</Jumlah>' . "\n";
            $xml .= '      <Total>' . number_format($detail['total'], 2, '.', '') . '</Total>' . "\n";
            $xml .= '      <Diskon>0.00</Diskon>' . "\n";
            $xml .= '      <Dpp>' . number_format($detail['dpp'], 2, '.', '') . '</Dpp>' . "\n";
            $xml .= '      <Ppn>' . number_format($detail['ppn'], 2, '.', '') . '</Ppn>' . "\n";
            $xml .= '    </Item>' . "\n";
        }

        $xml .= '  </DetailTransaksi>' . "\n";
        $xml .= '  <TotalDPP>' . number_format($data['totalDpp'], 2, '.', '') . '</TotalDPP>' . "\n";
        $xml .= '  <TotalPPN>' . number_format($data['totalPpn'], 2, '.', '') . '</TotalPPN>' . "\n";
        $xml .= '  <TotalPPnBM>0.00</TotalPPnBM>' . "\n";
        $xml .= '  <IsDigunggung>0</IsDigunggung>' . "\n";
        $xml .= '  <Status>' . $data['status'] . '</Status>' . "\n";
        $xml .= '</Faktur>';

        return $xml;
    }

    private function buildInvoiceLines(ArInvoice $invoice): array
    {
        $details = [];

        foreach ($invoice->lines as $line) {
            $amount = (float) ($line->amount ?? 0);
            $taxAmount = (float) ($line->tax_amount ?? 0);
            $dpp = $amount > 0 ? round($amount - $taxAmount, 2) : 0;
            $ppn = $taxAmount;

            $details[] = [
                'kode' => 'HOTEL001',
                'nama' => $line->description ?? 'Hotel Accommodation',
                'harga' => $dpp,
                'jumlah' => (int) ($line->qty ?? 1),
                'total' => $dpp,
                'dpp' => $dpp,
                'ppn' => $ppn,
            ];
        }

        if (empty($details)) {
            $dpp = round((float) $invoice->subtotal, 2);
            $ppn = round((float) $invoice->tax_total, 2);
            $details[] = [
                'kode' => 'HOTEL001',
                'nama' => 'Hotel Accommodation',
                'harga' => $dpp,
                'jumlah' => 1,
                'total' => $dpp,
                'dpp' => $dpp,
                'ppn' => $ppn,
            ];
        }

        return $details;
    }

    private function buildFolioDetails(Folio $folio): array
    {
        $details = [];
        $charges = $folio->charges()->where('is_void', false)->get();

        foreach ($charges as $charge) {
            $amount = (float) ($charge->amount ?? 0);
            $taxAmount = (float) ($charge->tax_amount ?? 0);
            $dpp = round($amount, 2);
            $ppn = round($taxAmount, 2);

            $kode = match ($charge->category) {
                'room' => 'HOTEL001',
                'fnb', 'restaurant' => 'HOTEL002',
                'laundry' => 'HOTEL003',
                'spa' => 'HOTEL004',
                'minibar' => 'HOTEL005',
                'telephone' => 'HOTEL006',
                'transport' => 'HOTEL007',
                default => 'HOTEL099',
            };

            $details[] = [
                'kode' => $kode,
                'nama' => $charge->description ?? ucfirst((string) $charge->category),
                'harga' => $dpp,
                'jumlah' => (int) ($charge->qty ?? 1),
                'total' => $dpp,
                'dpp' => $dpp,
                'ppn' => $ppn,
            ];
        }

        if (empty($details)) {
            $dpp = round((float) ($folio->total_charges ?? 0) / 1.11, 2);
            $ppn = round((float) ($folio->total_charges ?? 0) - $dpp, 2);
            $details[] = [
                'kode' => 'HOTEL001',
                'nama' => 'Hotel Accommodation',
                'harga' => $dpp,
                'jumlah' => 1,
                'total' => $dpp,
                'dpp' => $dpp,
                'ppn' => $ppn,
            ];
        }

        return $details;
    }

    private function getNextNsfp(int $propertyId): string
    {
        $last = EFakturRecord::where('property_id', $propertyId)
            ->whereNotNull('nomor_faktur')
            ->orderBy('id', 'desc')
            ->first();

        if ($last && preg_match('/^\d{13}$/', $last->nomor_faktur)) {
            return str_pad((string) ((int) $last->nomor_faktur + 1), 13, '0', STR_PAD_LEFT);
        }

        $base = '000' . now()->format('ymd') . '00001';
        return substr($base, 0, 13);
    }

    private function cleanNpwp(?string $npwp): string
    {
        if (empty($npwp)) {
            return '';
        }
        $cleaned = preg_replace('/[^0-9]/', '', $npwp);
        return $cleaned;
    }

    private function isValidNpwp(?string $npwp): bool
    {
        $cleaned = $this->cleanNpwp($npwp);
        return strlen($cleaned) === 15 && $cleaned !== '000000000000000';
    }

    private function determineKode(?string $npwp): string
    {
        return $this->isValidNpwp($npwp) ? '01' : '04';
    }
}
