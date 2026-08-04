<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import { router } from '@inertiajs/vue3';
import { reactive } from 'vue';
import { useI18n } from 'vue-i18n';

// Every shape a Questions/Player/*.vue component's v-model can carry
// (see resources/js/Components/Questions/Player/*.vue) -- a plain union
// rather than `unknown`, so it satisfies Inertia's own FormDataConvertible
// when submitted.
type AnswerValue = string | number | boolean | string[] | Record<string, string> | null;

interface AttemptQuestion {
    answerId: number;
    questionId: number;
    prompt: string;
    payload: Record<string, unknown>;
    playerComponent: string;
    submittedAnswer: AnswerValue;
    pointsAwarded: number | null;
    pointsPossible: number;
    isCorrect: boolean | null;
    feedback: string | null;
}

interface AttemptData {
    id: number;
    attemptNumber: number;
    startedAt: string;
    submittedAt: string | null;
    score: number | null;
    maxScore: number | null;
    passed: boolean | null;
    timeLimitMinutes: number | null;
    questions: AttemptQuestion[];
}

const props = defineProps<{
    blockedReason: string | null;
    attempt: AttemptData | null;
    lastResult: AttemptData | null;
}>();

defineOptions({ layout: AppLayout });

const { t } = useI18n();

// Every Questions/Player/*.vue component, resolved by its bare filename
// (matching the "Questions/Player/X" string App\Questions\Contracts\QuestionType::playerComponent()
// returns) -- there is no existing dynamic-component-by-name mechanism
// anywhere else in this codebase to reuse, since no block/question
// player page existed before this one.
const playerComponents = import.meta.glob('@/Components/Questions/Player/*.vue', { eager: true }) as Record<
    string,
    { default: unknown }
>;

function resolvePlayerComponent(playerComponent: string) {
    const name = playerComponent.split('/').pop();
    const match = Object.entries(playerComponents).find(([path]) => path.endsWith(`/${name}.vue`));

    return match?.[1].default;
}

const answers = reactive<Record<number, AnswerValue>>({});

if (props.attempt) {
    for (const question of props.attempt.questions) {
        answers[question.questionId] = question.submittedAnswer ?? null;
    }
}

function submit() {
    if (!props.attempt) {
        return;
    }

    router.post(`/quiz-attempts/${props.attempt.id}/submit`, { answers });
}

function formatResult(result: AttemptData): string {
    if (result.passed === true) {
        return t('quizzes.attempt.passed', { score: result.score ?? 0, max: result.maxScore ?? 0 });
    }

    if (result.passed === false) {
        return t('quizzes.attempt.failed', { score: result.score ?? 0, max: result.maxScore ?? 0 });
    }

    return t('quizzes.attempt.pendingGrading');
}
</script>

<template>
    <div v-if="blockedReason" class="alert alert-warning">
        {{ blockedReason }}
    </div>

    <div v-if="lastResult" class="card mb-4">
        <div class="card-body">
            <h2 class="h5">{{ t('quizzes.attempt.previousResult') }}</h2>
            <p>{{ formatResult(lastResult) }}</p>
        </div>
    </div>

    <template v-if="attempt">
        <h1 class="h4 mb-3">{{ t('quizzes.attempt.title', { number: attempt.attemptNumber }) }}</h1>

        <div v-if="attempt.submittedAt" class="alert alert-success mb-4">
            {{ formatResult(attempt) }}
        </div>

        <form v-else @submit.prevent="submit">
            <div v-for="question in attempt.questions" :key="question.answerId" class="card mb-3">
                <div class="card-body">
                    <p class="fw-semibold">{{ question.prompt }}</p>
                    <component
                        :is="resolvePlayerComponent(question.playerComponent)"
                        v-model="answers[question.questionId]"
                        :payload="question.payload"
                        :question-id="question.questionId"
                    />
                </div>
            </div>

            <button type="submit" class="btn btn-primary">{{ t('quizzes.attempt.submit') }}</button>
        </form>

        <div v-if="attempt.submittedAt">
            <div v-for="question in attempt.questions" :key="question.answerId" class="card mb-3">
                <div class="card-body">
                    <p class="fw-semibold">{{ question.prompt }}</p>
                    <p v-if="question.feedback" class="text-muted">{{ question.feedback }}</p>
                </div>
            </div>
        </div>
    </template>
</template>
