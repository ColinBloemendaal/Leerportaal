<?php

declare(strict_types=1);

namespace App\Actions\Certificates;

use App\Contracts\Repositories\ResellerThemeRepository;
use App\Exceptions\CourseAssignmentNotCompleteException;
use App\Models\Certificate;
use App\Models\CourseAssignment;
use App\Services\Progress\CourseCompletionChecker;
use Barryvdh\DomPDF\PDF;
use Illuminate\Contracts\Filesystem\Factory as FilesystemFactory;
use Illuminate\Support\Str;
use LogicException;
use RuntimeException;

/**
 * "Per-reseller template": rather than a separate editable certificate
 * template (no such editor is asked for, unlike the reseller mail
 * template editor), the one default Blade view is styled with that
 * reseller's own ResellerTheme (logo, primary color) -- the same
 * "one template, reseller branding injected" shape the mail templates
 * already use for their base layout.
 */
final readonly class IssueCertificate
{
    private const DISK = 's3';

    public function __construct(
        private PDF $pdf,
        private FilesystemFactory $filesystem,
        private CourseCompletionChecker $completionChecker,
        private ResellerThemeRepository $themes,
    ) {}

    public function __invoke(CourseAssignment $assignment): Certificate
    {
        $course = $assignment->course;
        $user = $assignment->user;

        if ($course === null || $user === null) {
            throw new LogicException("CourseAssignment #{$assignment->id} has no Course or User.");
        }

        if (! $this->completionChecker->isComplete($assignment, $course)) {
            throw CourseAssignmentNotCompleteException::for($assignment->id);
        }

        $theme = $this->themes->findForReseller($assignment->reseller_id);
        $verificationCode = $this->generateUniqueCode();

        $pdfContents = $this->pdf->loadView('certificates.default', [
            'recipientName' => $user->name,
            'courseTitle' => $course->title,
            'issuedAt' => now()->toFormattedDateString(),
            'verificationCode' => $verificationCode,
            'primaryColor' => $theme === null ? '#0d6efd' : $theme->primary_color,
            'logoUrl' => $theme === null ? null : $theme->logo_path,
        ])->output();

        $path = "certificates/{$verificationCode}.pdf";
        $stored = $this->filesystem->disk(self::DISK)->put($path, $pdfContents);

        if ($stored === false) {
            throw new RuntimeException('Failed to store the generated certificate PDF.');
        }

        $issuedAt = now();

        return Certificate::query()->create([
            'course_assignment_id' => $assignment->id,
            'verification_code' => $verificationCode,
            'issued_at' => $issuedAt,
            'expires_at' => $course->certificate_validity_months === null
                ? null
                : $issuedAt->copy()->addMonths($course->certificate_validity_months),
            'pdf_disk' => self::DISK,
            'pdf_path' => $path,
        ]);
    }

    private function generateUniqueCode(): string
    {
        do {
            $code = Str::upper(Str::random(16));
        } while (Certificate::query()->where('verification_code', $code)->exists());

        return $code;
    }
}
