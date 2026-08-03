<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Billing\RecordInvoicePaymentStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Public, unauthenticated (Mollie can't log in) -- verified instead by
 * always re-fetching the payment's real status from Mollie itself, see
 * RecordInvoicePaymentStatus. CSRF-exempted, see bootstrap/app.php.
 */
final class MollieWebhookController extends Controller
{
    public function __invoke(Request $request, RecordInvoicePaymentStatus $recordStatus): JsonResponse
    {
        $paymentId = $request->input('id');

        if (! is_string($paymentId) || $paymentId === '') {
            return response()->json(['message' => 'Missing payment id.'], 422);
        }

        $recordStatus($paymentId);

        return response()->json(['message' => 'ok']);
    }
}
