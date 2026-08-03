<?php

declare(strict_types=1);

namespace App\Mail;

use App\Contracts\Repositories\ResellerThemeRepository;
use App\Mail\Concerns\HasResellerBranding;
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
use RuntimeException;

final class AssignmentDeadlineApproaching extends Mailable implements ShouldQueue
{
    use HasResellerBranding;
    use Queueable;
    use SerializesModels;

    private readonly Reseller $reseller;

    private readonly Course $course;

    private readonly ?ResellerTheme $resellerTheme;

    public function __construct(
        public readonly CourseAssignment $assignment,
        public readonly int $daysBefore,
    ) {
        // Untyped assignment, not a re-declared property: Mailable's own
        // $theme is intentionally untyped, and Larastan flags overriding it
        // with a native type (property.extraNativeType).
        $this->theme = 'reseller';

        $reseller = $this->assignment->reseller()->first();
        $course = $this->assignment->course()->first();

        if ($reseller === null || $course === null || $this->assignment->deadline_at === null) {
            throw new RuntimeException("CourseAssignment #{$this->assignment->id} has no Reseller, Course, or deadline.");
        }

        $this->reseller = $reseller;
        $this->course = $course;

        $this->resellerTheme = app(ResellerThemeRepository::class)->findForReseller($reseller->id);
    }

    public function envelope(): Envelope
    {
        return $this->brandedEnvelope(
            $this->reseller,
            $this->resellerTheme,
            trans('Deadline approaching: :course', ['course' => $this->course->title]),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.assignments.deadline-approaching',
            with: [
                'courseTitle' => $this->course->title,
                'daysBefore' => $this->daysBefore,
                'deadlineAt' => $this->assignment->deadline_at?->toFormattedDateString(),
                ...$this->brandingData($this->reseller, $this->resellerTheme),
            ],
        );
    }
}
