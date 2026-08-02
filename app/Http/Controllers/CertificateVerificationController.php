<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Contracts\Repositories\CertificateRepository;
use App\Http\Resources\CertificateVerificationResource;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Public, unauthenticated -- anyone holding a verification code (printed
 * on the certificate itself) can confirm it's genuine. Deliberately does
 * not require login or tenant resolution, since a certificate might be
 * checked by a third party (an employer) with no Leerportaal account.
 */
final class CertificateVerificationController extends Controller
{
    public function show(string $code, CertificateRepository $certificates): Response
    {
        $certificate = $certificates->findByVerificationCode($code);

        return Inertia::render('Certificates/Verify', [
            'certificate' => $certificate === null ? null : new CertificateVerificationResource($certificate),
        ]);
    }
}
