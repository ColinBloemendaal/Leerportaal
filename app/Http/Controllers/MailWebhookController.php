<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Mail\RecordMailSuppression;
use App\Contracts\Mail\MailSuppressionWebhookParser;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Public, unauthenticated (the provider can't log in), signature-verified
 * instead -- same "unauthenticated but verified" shape the Billing
 * section's Mollie webhook will need. CSRF-exempted, see bootstrap/app.php.
 */
final class MailWebhookController extends Controller
{
    public function mailgun(Request $request, MailSuppressionWebhookParser $parser, RecordMailSuppression $recordSuppression): JsonResponse
    {
        $payload = $request->all();

        if (! $parser->verifySignature($payload)) {
            return response()->json(['message' => 'Invalid signature.'], 401);
        }

        $event = $parser->parse($payload);

        if ($event !== null) {
            $recordSuppression($event);
        }

        return response()->json(['message' => 'ok']);
    }
}
