<?php

declare(strict_types=1);

namespace App\Http\Requests\Quizzes;

use App\Contracts\Repositories\QuizAttemptRepository;
use App\DataTransferObjects\Quizzes\SubmitQuizAttemptData;
use Illuminate\Foundation\Http\FormRequest;

final class SubmitQuizAttemptRequest extends FormRequest
{
    /**
     * Method-injected (Laravel resolves FormRequest hooks via the
     * container) rather than a constructor, since a FormRequest is
     * itself resolved before the route's own {attempt} value exists to
     * inject anything against.
     */
    public function authorize(QuizAttemptRepository $attempts): bool
    {
        $attempt = $attempts->findById((int) $this->route('attempt'));

        return $attempt !== null && ($this->user()?->can('submit', $attempt) ?? false);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'answers' => ['required', 'array'],
        ];
    }

    public function toDto(): SubmitQuizAttemptData
    {
        return SubmitQuizAttemptData::fromArray($this->validated());
    }
}
