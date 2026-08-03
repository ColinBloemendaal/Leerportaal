<script setup lang="ts">
import type { EssayPayload } from '@/types/questions';
import { useI18n } from 'vue-i18n';

defineProps<{ payload: EssayPayload; questionId: string | number }>();

const answer = defineModel<string>({ default: '' });
const { t } = useI18n();
</script>

<template>
    <div>
        <textarea
            :id="`question-${questionId}-answer`"
            v-model="answer"
            class="form-control"
            rows="8"
            :aria-label="t('questions.player.common.answerAria', { id: questionId })"
        ></textarea>
        <div v-if="payload.min_words || payload.max_words" class="form-text">
            <span v-if="payload.min_words"
                >{{ t('questions.player.essay.minWords', { count: payload.min_words }) }}
            </span>
            <span v-if="payload.max_words">{{
                t('questions.player.essay.maxWords', { count: payload.max_words })
            }}</span>
        </div>
    </div>
</template>
