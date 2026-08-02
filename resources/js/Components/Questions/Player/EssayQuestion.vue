<script setup lang="ts">
import type { EssayPayload } from '@/types/questions';

defineProps<{ payload: EssayPayload; questionId: string | number }>();

const answer = defineModel<string>({ default: '' });
</script>

<template>
    <div>
        <textarea
            :id="`question-${questionId}-answer`"
            v-model="answer"
            class="form-control"
            rows="8"
            :aria-label="`Answer for question ${questionId}`"
        ></textarea>
        <div v-if="payload.min_words || payload.max_words" class="form-text">
            <span v-if="payload.min_words">Minimum {{ payload.min_words }} words. </span>
            <span v-if="payload.max_words">Maximum {{ payload.max_words }} words.</span>
        </div>
    </div>
</template>
