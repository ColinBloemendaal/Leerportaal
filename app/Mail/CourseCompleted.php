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

final class CourseCompleted extends Mailable implements ShouldQueue
{
    use HasResellerBranding;
    use Queueable;
    use SerializesModels;

    private readonly Reseller $reseller;

    private readonly Course $course;

    private readonly ?ResellerTheme $resellerTheme;

    public function __construct(public readonly CourseAssignment $assignment)
    {
        $reseller = $this->assignment->reseller()->first();
        $course = $this->assignment->course()->first();

        if ($reseller === null || $course === null) {
            throw new RuntimeException("CourseAssignment #{$this->assignment->id} has no Reseller or Course.");
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
            trans('Course completed: :course', ['course' => $this->course->title]),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.assignments.completed',
            with: ['courseTitle' => $this->course->title],
        );
    }
}
