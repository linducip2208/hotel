<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\CoreTaxException;
use App\Http\Controllers\Controller;
use App\Models\ArInvoice;
use App\Services\Indonesia\CoretaxService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CoreTaxController extends Controller
{
    public function __construct(protected CoretaxService $coretax) {}

    /**
     * Push an AR Invoice as e-Faktur to DJP.
     */
    public function pushFaktur(Request $request): JsonResponse
    {
        $request->validate([
            'invoice_id' => 'required|exists:ar_invoices,id',
        ]);

        try {
            $invoice = ArInvoice::with(['lines', 'arAccount', 'property'])->findOrFail($request->input('invoice_id'));
            $result = $this->coretax->pushFaktur($invoice);

            return response()->json($result, 201);

        } catch (CoreTaxException $e) {
            Log::channel('coretax')->error('pushFaktur failed: ' . $e->getMessage(), $e->getContext());
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => $e->getErrorCode(),
                    'message' => $e->getMessage(),
                ],
                'context' => $e->getContext(),
            ], $e->getHttpStatusCode());
        } catch (\Throwable $e) {
            Log::channel('coretax')->error('pushFaktur unexpected error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'INTERNAL_ERROR',
                    'message' => $e->getMessage(),
                ],
            ], 500);
        }
    }

    /**
     * Check e-Faktur status on DJP.
     */
    public function checkStatus(string $nomor): JsonResponse
    {
        try {
            $result = $this->coretax->checkFakturStatus($nomor);
            return response()->json($result);
        } catch (CoreTaxException $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => $e->getErrorCode(),
                    'message' => $e->getMessage(),
                ],
            ], $e->getHttpStatusCode());
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => ['code' => 'INTERNAL_ERROR', 'message' => $e->getMessage()],
            ], 500);
        }
    }

    /**
     * Cancel e-Faktur on DJP.
     */
    public function cancelFaktur(Request $request, string $nomor): JsonResponse
    {
        $request->validate([
            'reason' => 'nullable|string|max:1000',
        ]);

        try {
            $userId = $request->user()?->id ?? 1;
            $reason = $request->input('reason', '');
            $result = $this->coretax->cancelFaktur($nomor, $userId, $reason);
            return response()->json($result);
        } catch (CoreTaxException $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => $e->getErrorCode(),
                    'message' => $e->getMessage(),
                ],
            ], $e->getHttpStatusCode());
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => ['code' => 'INTERNAL_ERROR', 'message' => $e->getMessage()],
            ], 500);
        }
    }

    /**
     * Get NSFP allocation from DJP.
     */
    public function getNsfp(Request $request, int $year): JsonResponse
    {
        try {
            $result = $this->coretax->getNsfp($year ?: (int) date('Y'));
            return response()->json($result);
        } catch (CoreTaxException $e) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => $e->getErrorCode(),
                    'message' => $e->getMessage(),
                ],
            ], $e->getHttpStatusCode());
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => ['code' => 'INTERNAL_ERROR', 'message' => $e->getMessage()],
            ], 500);
        }
    }
}
