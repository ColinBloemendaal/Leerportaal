<script setup lang="ts">
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const correctAnswer = defineModel<number>('correct_answer', { required: true });
const tolerance = defineModel<number>('tolerance', { required: true });
</script>

<template>
    <div class="row mb-3">
        <div class="col">
            <label for="numeric-correct-answer" class="form-label">{{
                t('questions.editor.numeric.correctAnswer')
            }}</label>
            <input
                id="numeric-correct-answer"
                v-model.number="correctAnswer"
                type="number"
                class="form-control"
                step="any"
            />
        </div>
        <div class="col">
            <label for="numeric-tolerance" class="form-label">{{ t('questions.editor.numeric.tolerance') }}</label>
            <input
                id="numeric-tolerance"
                v-model.number="tolerance"
                type="number"
                class="form-control"
                min="0"
                step="any"
            />
        </div>
    </div>
</template>
