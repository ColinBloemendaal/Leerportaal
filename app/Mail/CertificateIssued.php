<?php

declare(strict_types=1);

namespace App\Mail;

use App\Contracts\Repositories\ResellerThemeRepository;
use App\Mail\Concerns\HasResellerBranding;
use App\Models\Certificate;
use App\Models\Course;
use App\Models\CourseAssignment;
use App\Models\Reseller;
use App\Models\ResellerTheme;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;
use RuntimeException;

final class CertificateIssued extends Mailable implements ShouldQueue
{
    use HasResellerBranding;
    use Queueable;
    use SerializesModels;

    private readonly Reseller $reseller;

    private readonly Course $course;

    private readonly ?ResellerTheme $resellerTheme;

    public function __construct(public readonly Certificate $certificate)
    {
        // Untyped assignment, not a re-declared property: Mailable's own
        // $theme is intentionally untyped, and Larastan flags overriding it
        // with a native type (property.extraNativeType).
        $this->theme = 'reseller';

        // withoutTenantScope(): CourseAssignment is TenantScoped, but this
        // Mailable is queued and runs on a worker with no ambient tenant
        // to satisfy it -- without this, the query silently returns null
        // (the scope fails closed) and the constructor always throws.
        $assignment = $this->certificate->courseAssignment()->withoutTenantScope()->first();
        $reseller = $assignment?->reseller()->first();
        $course = $assignment?->course()->first();

        if ($assignment === null || $reseller === null || $course === null) {
            throw new RuntimeException("Certificate #{$this->certificate->id} has no CourseAssignment, Reseller, or Course.");
        }

        $this->reseller = $reseller;
        $this->course = $course;

        // Fetched eagerly, not lazily via TenantContext: this Mailable is
        // queued and runs on a worker with no ambient tenant.
        $this->resellerTheme = app(ResellerThemeRepository::class)->findForReseller($reseller->id);
    }

    public function envelope(): Envelope
    {
        return $this->brandedEnvelope(
            $this->reseller,
            $this->resellerTheme,
            trans('Certificate issued: :course', ['course' => $this->course->title]),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.certificates.issued',
            with: [
                'courseTitle' => $this->course->title,
                'verificationCode' => $this->certificate->verification_code,
                'verificationUrl' => URL::route('certificates.verify', ['code' => $this->certificate->verification_code]),
                ...$this->brandingData($this->reseller, $this->resellerTheme),
            ],
        );
    }
}
