<script setup lang="ts">
import { useI18n } from 'vue-i18n';

defineProps<{ questionId: string | number }>();

const answer = defineModel<string>({ default: '' });
const { t } = useI18n();
</script>

<template>
    <input
        :id="`question-${questionId}-answer`"
        v-model="answer"
        type="text"
        class="form-control"
        :aria-label="t('questions.player.common.answerAria', { id: questionId })"
    />
</template>
