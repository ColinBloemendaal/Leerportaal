<script setup lang="ts">
import { useI18n } from 'vue-i18n';

defineProps<{ questionId: string | number }>();

const answer = defineModel<number | null>({ default: null });
const { t } = useI18n();
</script>

<template>
    <input
        :id="`question-${questionId}-answer`"
        v-model.number="answer"
        type="number"
        step="any"
        class="form-control"
        :aria-label="t('questions.player.numeric.answerAria', { id: questionId })"
    />
</template>
