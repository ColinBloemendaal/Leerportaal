<script setup lang="ts">
import { useI18n } from 'vue-i18n';

defineProps<{ questionId: string | number }>();

const answer = defineModel<boolean | null>({ default: null });
const { t } = useI18n();
</script>

<template>
    <fieldset>
        <div class="form-check">
            <input
                :id="`question-${questionId}-true`"
                v-model="answer"
                class="form-check-input"
                type="radio"
                :name="`question-${questionId}`"
                :value="true"
            />
            <label class="form-check-label" :for="`question-${questionId}-true`">{{
                t('questions.player.trueFalse.true')
            }}</label>
        </div>
        <div class="form-check">
            <input
                :id="`question-${questionId}-false`"
                v-model="answer"
                class="form-check-input"
                type="radio"
                :name="`question-${questionId}`"
                :value="false"
            />
            <label class="form-check-label" :for="`question-${questionId}-false`">{{
                t('questions.player.trueFalse.false')
            }}</label>
        </div>
    </fieldset>
</template>
