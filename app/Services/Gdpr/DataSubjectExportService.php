<?php

declare(strict_types=1);

namespace App\Services\Gdpr;

use App\Contracts\Repositories\ActivityLogRepository;
use App\Contracts\Repositories\CertificateRepository;
use App\Contracts\Repositories\CourseAssignmentRepository;
use App\Contracts\Repositories\NotificationRepository;
use App\Contracts\Repositories\QuizAttemptRepository;
use App\Models\Certificate;
use App\Models\CourseAssignment;
use App\Models\QuizAttempt;
use App\Models\User;
use Spatie\Activitylog\Models\Activity;

/**
 * CLAUDE.md §8 (GDPR): "Data export per data subject: JSON + human
 * readable." Composes every piece of personal data this codebase holds
 * about one user into a single plain array, deliberately not a typed
 * DTO tree -- this shape exists to be serialized once (to JSON, or
 * rendered once into an HTML view) and never read back or passed
 * further through Actions/Repositories, the same "shape data for
 * output" role an Http\Resources class already plays elsewhere in this
 * codebase.
 *
 * Every read here deliberately bypasses the request's ambient tenant
 * (see each repository method's own reasoning) -- a data subject's own
 * export must include everything about them, not just what the current
 * tenant context happens to allow.
 */
final readonly class DataSubjectExportService
{
    public function __construct(
        private CourseAssignmentRepository $assignments,
        private QuizAttemptRepository $quizAttempts,
        private CertificateRepository $certificates,
        private NotificationRepository $notifications,
        private ActivityLogRepository $activity,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function forUser(User $user): array
    {
        return [
            'profile' => $this->profile($user),
            'course_assignments' => $this->assignments->allForUser($user->id)
                ->map($this->assignmentRow(...))
                ->all(),
            'quiz_attempts' => $this->quizAttempts->forUser($user->id)
                ->map($this->quizAttemptRow(...))
                ->all(),
            'certificates' => $this->certificates->forUser($user->id)
                ->map($this->certificateRow(...))
                ->all(),
            'notifications' => $this->notifications->createdSince($user->id, null)
                ->map(fn ($notification): array => [
                    'type' => $notification->data['type'] ?? null,
                    'message' => $notification->data['message'] ?? null,
                    'read_at' => $notification->read_at?->toIso8601String(),
                    'created_at' => $notification->created_at?->toIso8601String(),
                ])
                ->all(),
            'activity' => $this->activity->timelineForUser($user->id)
                ->map(fn (Activity $activity): array => [
                    'description' => $activity->description,
                    'event' => $activity->event,
                    'created_at' => $activity->created_at?->toIso8601String(),
                ])
                ->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function profile(User $user): array
    {
        $reseller = $user->reseller()->first();
        $resellerKlant = $user->resellerKlant()->first();

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'reseller' => $reseller?->name,
            'klant' => $resellerKlant?->name,
            'created_at' => $user->created_at?->toIso8601String(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function assignmentRow(CourseAssignment $assignment): array
    {
        $course = $assignment->course()->first();

        return [
            'course_title' => $course?->title,
            'billing_state' => $assignment->billing_state->value,
            'assigned_at' => $assignment->assigned_at->toIso8601String(),
            'first_opened_at' => $assignment->first_opened_at?->toIso8601String(),
            'completed_at' => $assignment->completed_at?->toIso8601String(),
            'revoked_at' => $assignment->revoked_at?->toIso8601String(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function quizAttemptRow(QuizAttempt $attempt): array
    {
        return [
            'quiz_type' => $attempt->quiz?->type->value,
            'started_at' => $attempt->started_at->toIso8601String(),
            'submitted_at' => $attempt->submitted_at?->toIso8601String(),
            'score' => $attempt->score,
            'passed' => $attempt->passed,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function certificateRow(Certificate $certificate): array
    {
        $course = $certificate->courseAssignment?->course;

        return [
            'course_title' => $course?->title,
            'verification_code' => $certificate->verification_code,
            'issued_at' => $certificate->issued_at->toIso8601String(),
            'expires_at' => $certificate->expires_at?->toIso8601String(),
        ];
    }
}
