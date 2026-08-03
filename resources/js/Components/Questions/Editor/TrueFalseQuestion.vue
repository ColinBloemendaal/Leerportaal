<script setup lang="ts">
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const correctAnswer = defineModel<boolean>('correct_answer', { required: true });
</script>

<template>
    <div class="mb-3">
        <label class="form-label">{{ t('questions.editor.trueFalse.correctAnswer') }}</label>
        <div class="form-check">
            <input
                id="true-false-correct-true"
                class="form-check-input"
                type="radio"
                :checked="correctAnswer === true"
                @change="correctAnswer = true"
            />
            <label class="form-check-label" for="true-false-correct-true">{{
                t('questions.editor.trueFalse.true')
            }}</label>
        </div>
        <div class="form-check">
            <input
                id="true-false-correct-false"
                class="form-check-input"
                type="radio"
                :checked="correctAnswer === false"
                @change="correctAnswer = false"
            />
            <label class="form-check-label" for="true-false-correct-false">{{
                t('questions.editor.trueFalse.false')
            }}</label>
        </div>
    </div>
</template>
