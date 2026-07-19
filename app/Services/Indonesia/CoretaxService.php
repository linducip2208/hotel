<?php

declare(strict_types=1);

namespace App\Services\Indonesia;

use App\Exceptions\CoreTaxException;
use App\Models\ArInvoice;
use App\Models\EFakturRecord;
use App\Models\Property;
use App\Services\Compliance\NsfpService;
use App\Services\Integrations\ProviderRegistry;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CoretaxService
{
    protected Client $http;

    public function __construct(
        protected ProviderRegistry $registry,
        protected NsfpService $nsfpService,
    ) {
        $this->http = new Client([
            'base_uri' => rtrim((string) config('coretax.base_url'), '/') . '/',
            'timeout' => (int) config('coretax.timeout', 30),
            'connect_timeout' => 10,
            'http_errors' => false,
            'headers' => config('coretax.headers', [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ]),
        ]);
    }

    // ─── Public API ───────────────────────────────────────────────

    /**
     * Push e-Faktur to DJP Coretax API.
     */
    public function pushFaktur(ArInvoice $invoice): array
    {
        $this->validateInvoice($invoice);

        $property = $invoice->property ?? Property::find($invoice->property_id);
        $npwpPenjual = config('coretax.npwp_penjual');

        if (empty($npwpPenjual)) {
            throw CoreTaxException::notConfigured();
        }

        $nsfp = $this->nsfpService->next($property);

        $faktur = EFakturRecord::create([
            'property_id' => $property->id,
            'invoice_id' => $invoice->id,
            'invoice_no' => $invoice->invoice_no,
            'npwp_penjual' => $npwpPenjual,
            'npwp_pembeli' => $invoice->arAccount->npwp ?? '000000000000000',
            'dpp' => $invoice->subtotal,
            'ppn' => $invoice->tax_total,
            'kode_transaksi' => $this->determineKodeTransaksi($invoice),
            'kode_status' => '01',
            'status' => 'draft',
            'nomor_faktur' => $nsfp,
        ]);

        $xmlBody = $this->buildFakturXml($invoice, $faktur, $property, $npwpPenjual);
        $signedXml = $this->signXml($xmlBody);

        $faktur->update(['request_payload' => ['xml' => $signedXml]]);

        try {
            $response = $this->callApi('efaktur/submit', [
                'npwp' => $npwpPenjual,
                'xml' => base64_encode($signedXml),
            ]);

            $status = $response['status'] ?? 'error';

            if (($response['success'] ?? false) || in_array($status, ['success', 'approved', 'processed'])) {
                $nomorFaktur = $response['nomor_faktur'] ?? $nsfp;
                $faktur->markSent($nomorFaktur, $response, '05');
                return [
                    'success' => true,
                    'data' => [
                        'id' => $faktur->id,
                        'nomor_faktur' => $nomorFaktur,
                        'status' => 'success',
                        'invoice_no' => $invoice->invoice_no,
                    ],
                ];
            }

            $errorMessage = $response['pesan'] ?? $response['error'] ?? $response['message'] ?? 'Unknown error';
            $faktur->markFailed($errorMessage, $response);

            throw CoreTaxException::apiError('efaktur/submit', $response);

        } catch (CoreTaxException $e) {
            throw $e;
        } catch (\Throwable $e) {
            $faktur->markFailed($e->getMessage());
            throw CoreTaxException::networkError('efaktur/submit', $e->getMessage(), $e);
        }
    }

    /**
     * Cancel faktur pajak.
     */
    public function cancelFaktur(string $nomorFaktur, int $userId, string $reason = ''): array
    {
        $faktur = EFakturRecord::where('nomor_faktur', $nomorFaktur)->first();

        if (! $faktur) {
            throw CoreTaxException::invoiceValidationFailed("Faktur {$nomorFaktur} not found.");
        }

        if ($faktur->status === 'cancelled') {
            throw CoreTaxException::invoiceValidationFailed("Faktur {$nomorFaktur} is already cancelled.");
        }

        try {
            $response = $this->callApi('efaktur/cancel', [
                'npwp' => config('coretax.npwp_penjual'),
                'nomor_faktur' => $nomorFaktur,
                'alasan' => $reason ?: 'Pembatalan oleh PKP',
            ]);

            if ($response['success'] ?? false) {
                $faktur->markCancelled($reason, $userId);
                return [
                    'success' => true,
                    'data' => [
                        'nomor_faktur' => $nomorFaktur,
                        'status' => 'cancelled',
                        'message' => $response['message'] ?? 'Faktur cancelled successfully.',
                    ],
                ];
            }

            throw CoreTaxException::apiError('efaktur/cancel', $response);

        } catch (CoreTaxException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw CoreTaxException::networkError('efaktur/cancel', $e->getMessage(), $e);
        }
    }

    /**
     * Verify NPWP validity via DJP.
     */
    public function checkNpwp(string $npwp): array
    {
        $cleaned = preg_replace('/[^0-9]/', '', $npwp);

        if (strlen($cleaned) !== 15) {
            return [
                'success' => false,
                'data' => [
                    'npwp' => $npwp,
                    'valid' => false,
                    'message' => 'NPWP must be exactly 15 digits.',
                ],
            ];
        }

        try {
            $response = $this->callApi('npwp/validate', ['npwp' => $cleaned]);

            return [
                'success' => $response['success'] ?? false,
                'data' => [
                    'npwp' => $cleaned,
                    'valid' => $response['valid'] ?? ($response['success'] ?? false),
                    'name' => $response['nama'] ?? $response['name'] ?? null,
                    'address' => $response['alamat'] ?? $response['address'] ?? null,
                    'type' => $response['jenis_wp'] ?? $response['type'] ?? null,
                    'status' => $response['status_wp'] ?? $response['status'] ?? null,
                    'raw' => $response,
                ],
            ];
        } catch (CoreTaxException $e) {
            throw $e;
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'data' => [
                    'npwp' => $cleaned,
                    'valid' => false,
                    'message' => $e->getMessage(),
                ],
            ];
        }
    }

    /**
     * Fetch NSFP allocation from DJP for a tax year.
     */
    public function getNsfp(int $year): array
    {
        try {
            $response = $this->callApi('nsfp/allocation', [
                'npwp' => config('coretax.npwp_penjual'),
                'tahun' => $year,
            ]);

            return [
                'success' => $response['success'] ?? false,
                'data' => [
                    'year' => $year,
                    'total_allocated' => $response['total_jatah'] ?? $response['total'] ?? 0,
                    'used' => $response['terpakai'] ?? $response['used'] ?? 0,
                    'remaining' => $response['sisa'] ?? $response['remaining'] ?? 0,
                    'ranges' => $response['range'] ?? $response['ranges'] ?? [],
                    'raw' => $response,
                ],
            ];
        } catch (CoreTaxException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw CoreTaxException::networkError('nsfp/allocation', $e->getMessage(), $e);
        }
    }

    /**
     * Check faktur status on DJP.
     */
    public function checkFakturStatus(string $nomorFaktur): array
    {
        $faktur = EFakturRecord::where('nomor_faktur', $nomorFaktur)->first();

        try {
            $response = $this->callApi('efaktur/status', [
                'npwp' => config('coretax.npwp_penjual'),
                'nomor_faktur' => $nomorFaktur,
            ]);

            $status = $response['status'] ?? ($faktur?->status ?? 'unknown');

            return [
                'success' => true,
                'data' => [
                    'nomor_faktur' => $nomorFaktur,
                    'status' => $status,
                    'invoice_no' => $faktur?->invoice_no ?? null,
                    'sent_at' => $faktur?->sent_at?->toISOString() ?? null,
                    'raw' => $response,
                ],
            ];
        } catch (CoreTaxException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw CoreTaxException::networkError('efaktur/status', $e->getMessage(), $e);
        }
    }

    /**
     * Validate invoice before push to DJP.
     */
    public function validateInvoice(ArInvoice $invoice): array
    {
        $errors = [];

        if (empty($invoice->invoice_no)) {
            $errors[] = 'Invoice number is required.';
        }

        $npwpPembeli = $invoice->arAccount->npwp ?? null;
        if (! empty($npwpPembeli)) {
            $cleaned = preg_replace('/[^0-9]/', '', $npwpPembeli);
            if (strlen($cleaned) !== 15) {
                $errors[] = 'NPWP Pembeli must be exactly 15 digits.';
            }
        }

        $npwpPenjual = config('coretax.npwp_penjual');
        if (empty($npwpPenjual)) {
            $errors[] = 'NPWP Penjual not configured (CORETAX_NPWP).';
        }

        if ((float) $invoice->subtotal <= 0) {
            $errors[] = 'DPP (subtotal) must be greater than 0.';
        }

        if ((float) $invoice->tax_total <= 0) {
            $errors[] = 'PPN (tax total) must be greater than 0.';
        }

        $lines = $invoice->lines ?? collect();
        if ($lines->isEmpty()) {
            $errors[] = 'Invoice must have at least one line item.';
        }

        if (! empty($errors)) {
            throw CoreTaxException::invoiceValidationFailed(
                'Invoice #' . $invoice->invoice_no . ' is invalid for e-Faktur.',
                ['errors' => $errors]
            );
        }

        return ['success' => true, 'data' => ['valid' => true]];
    }

    // ─── Private Helpers ──────────────────────────────────────────

    /**
     * Build XML Faktur Pajak sesuai format DJP.
     */
    protected function buildFakturXml(
        ArInvoice $invoice,
        EFakturRecord $faktur,
        Property $property,
        string $npwpPenjual,
    ): string {
        $issuedAt = $invoice->issued_at?->format('Y-m-d') ?? now()->format('Y-m-d');
        $dueAt = $invoice->due_at?->format('Y-m-d') ?? now()->addDays(30)->format('Y-m-d');
        $npwpPembeli = $invoice->arAccount->npwp ?? '000000000000000';
        $npwpPembeli = preg_replace('/[^0-9]/', '', $npwpPembeli);
        $namaPembeli = $invoice->arAccount->name
            ?? $invoice->arAccount->company?->name
            ?? 'UMUM';

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->formatOutput = true;

        $envelope = $dom->createElement('soapenv:Envelope');
        $envelope->setAttribute('xmlns:soapenv', 'http://schemas.xmlsoap.org/soap/envelope/');
        $envelope->setAttribute('xmlns:cor', 'http://schemas.djp.go.id/coretax/v1');
        $dom->appendChild($envelope);

        $header = $dom->createElement('soapenv:Header');
        $envelope->appendChild($header);

        $body = $dom->createElement('soapenv:Body');
        $envelope->appendChild($body);

        $submitFaktur = $dom->createElement('cor:SubmitFakturRequest');
        $body->appendChild($submitFaktur);

        $this->appendElement($dom, $submitFaktur, 'cor:NPWPPenjual', $npwpPenjual);
        $this->appendElement($dom, $submitFaktur, 'cor:NomorFaktur', $faktur->nomor_faktur);
        $this->appendElement($dom, $submitFaktur, 'cor:KodeTransaksi', $faktur->kode_transaksi);
        $this->appendElement($dom, $submitFaktur, 'cor:KodeStatus', $faktur->kode_status);
        $this->appendElement($dom, $submitFaktur, 'cor:TanggalFaktur', $issuedAt);
        $this->appendElement($dom, $submitFaktur, 'cor:JatuhTempo', $dueAt);

        $pembeli = $dom->createElement('cor:Pembeli');
        $this->appendElement($dom, $pembeli, 'cor:NPWP', $npwpPembeli);
        $this->appendElement($dom, $pembeli, 'cor:Nama', htmlspecialchars($namaPembeli, ENT_XML1, 'UTF-8'));
        $submitFaktur->appendChild($pembeli);

        $detailList = $dom->createElement('cor:DetailBarangJasa');
        $lineNo = 0;
        foreach ($invoice->lines as $line) {
            $lineNo++;
            $item = $dom->createElement('cor:Item');
            $this->appendElement($dom, $item, 'cor:NoUrut', (string) $lineNo);
            $this->appendElement($dom, $item, 'cor:NamaBarang', htmlspecialchars(
                $line->description ?? 'Hotel Accommodation',
                ENT_XML1,
                'UTF-8',
            ));
            $this->appendElement($dom, $item, 'cor:Jumlah', number_format((float) ($line->qty ?? 1), 2, '.', ''));
            $this->appendElement($dom, $item, 'cor:HargaSatuan', number_format((float) ($line->unit_price ?? 0), 2, '.', ''));
            $this->appendElement($dom, $item, 'cor:JumlahHarga', number_format((float) ($line->amount ?? 0), 2, '.', ''));
            $this->appendElement($dom, $item, 'cor:PPN', number_format((float) ($line->tax_amount ?? 0), 2, '.', ''));
            $detailList->appendChild($item);
        }
        $submitFaktur->appendChild($detailList);

        $this->appendElement($dom, $submitFaktur, 'cor:TotalDPP', number_format((float) $invoice->subtotal, 2, '.', ''));
        $this->appendElement($dom, $submitFaktur, 'cor:TotalPPN', number_format((float) $invoice->tax_total, 2, '.', ''));
        $this->appendElement($dom, $submitFaktur, 'cor:TotalFaktur', number_format((float) $invoice->grand_total, 2, '.', ''));

        $referensi = $dom->createElement('cor:Referensi');
        $this->appendElement($dom, $referensi, 'cor:Tahun', $issuedAt ? substr($issuedAt, 0, 4) : date('Y'));
        $this->appendElement($dom, $referensi, 'cor:Bulan', $issuedAt ? substr($issuedAt, 5, 2) : date('m'));
        $this->appendElement($dom, $referensi, 'cor:InvoiceNo', $invoice->invoice_no);
        $submitFaktur->appendChild($referensi);

        return $dom->saveXML();
    }

    /**
     * Sign XML with PKCS12 certificate.
     */
    protected function signXml(string $xml): string
    {
        $certPath = rtrim((string) config('coretax.certificate_path'), '/') . '/npwp.pfx';
        $certPassword = config('coretax.certificate_password');

        if (! file_exists($certPath)) {
            Log::warning('Coretax certificate not found at: ' . $certPath . '. Proceeding unsigned.');
            return $xml;
        }

        if (empty($certPassword)) {
            Log::warning('Coretax certificate password not set (CORETAX_CERT_PASSWORD). Proceeding unsigned.');
            return $xml;
        }

        try {
            $pkcs12 = file_get_contents($certPath);

            if (! function_exists('openssl_pkcs12_read')) {
                Log::warning('OpenSSL extension not available. Cannot sign XML.');
                return $xml;
            }

            $certs = [];
            if (! openssl_pkcs12_read($pkcs12, $certs, $certPassword)) {
                $error = openssl_error_string();
                Log::error('Failed to read PKCS12 certificate: ' . ($error ?: 'unknown error'));
                return $xml;
            }

            $privateKey = $certs['pkey'] ?? null;
            $cert = $certs['cert'] ?? null;

            if (! $privateKey || ! $cert) {
                Log::error('PKCS12 bundle missing private key or certificate.');
                return $xml;
            }

            $dom = new \DOMDocument();
            $dom->loadXML($xml);

            $canonical = $dom->C14N();

            $signature = '';
            if (! openssl_sign($canonical, $signature, $privateKey, OPENSSL_ALGO_SHA256)) {
                Log::error('Failed to sign XML with certificate.');
                return $xml;
            }

            $sigElement = $dom->createElementNS('http://www.w3.org/2000/09/xmldsig#', 'Signature');
            $sigValue = $dom->createElement('SignatureValue', base64_encode($signature));
            $sigElement->appendChild($sigValue);

            $envelope = $dom->getElementsByTagNameNS('http://schemas.xmlsoap.org/soap/envelope/', 'Header')->item(0);
            if ($envelope) {
                $envelope->appendChild($sigElement);
            }

            return $dom->saveXML();

        } catch (\Throwable $e) {
            Log::error('XML signing error: ' . $e->getMessage(), ['exception' => $e]);
            return $xml;
        }
    }

    /**
     * Make HTTP call to Coretax API with retry and timeout.
     */
    protected function callApi(string $endpoint, array $data): array
    {
        $maxRetries = (int) config('coretax.retry', 3);
        $attempt = 0;
        $lastException = null;

        while ($attempt < $maxRetries) {
            $attempt++;
            try {
                $response = $this->http->post($endpoint, ['json' => $data]);

                $statusCode = $response->getStatusCode();
                $body = (string) $response->getBody();
                $result = json_decode($body, true);

                if (! is_array($result)) {
                    $result = ['success' => $statusCode < 400, 'raw' => $body];
                }

                Log::channel('coretax')->info('Coretax API call', [
                    'endpoint' => $endpoint,
                    'attempt' => $attempt,
                    'status_code' => $statusCode,
                    'npwp' => $data['npwp'] ?? null,
                ]);

                return $result;

            } catch (ConnectException $e) {
                $lastException = $e;
                Log::channel('coretax')->warning("Coretax connection attempt {$attempt} failed: " . $e->getMessage());

                if ($attempt < $maxRetries) {
                    usleep($attempt * 500000);
                }
            } catch (RequestException $e) {
                $lastException = $e;
                $response = $e->getResponse();
                $body = $response ? (string) $response->getBody() : '';
                $decoded = json_decode($body, true);

                if (is_array($decoded) && ($decoded['success'] ?? false)) {
                    return $decoded;
                }

                Log::channel('coretax')->error("Coretax request attempt {$attempt} failed: " . $e->getMessage(), [
                    'status' => $response?->getStatusCode(),
                    'body' => Str::limit($body, 500),
                ]);

                if ($attempt < $maxRetries) {
                    usleep($attempt * 500000);
                }
            }
        }

        throw CoreTaxException::networkError(
            $endpoint,
            $lastException?->getMessage() ?? 'Max retries exceeded',
            $lastException,
        );
    }

    // ─── Utility ───────────────────────────────────────────────────

    protected function determineKodeTransaksi(ArInvoice $invoice): string
    {
        $npwp = $invoice->arAccount->npwp ?? '';
        $npwp = preg_replace('/[^0-9]/', '', $npwp);

        if (strlen($npwp) !== 15 || $npwp === '000000000000000') {
            return '04';
        }

        return '01';
    }

    protected function appendElement(\DOMDocument $dom, \DOMElement $parent, string $name, string $value): void
    {
        $element = $dom->createElement($name, $value);
        $parent->appendChild($element);
    }
}
