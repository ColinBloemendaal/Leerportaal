<script setup lang="ts">
import type { OpenShortMatchMode } from '@/types/questions';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const matchMode = defineModel<OpenShortMatchMode>('match_mode', { required: true });
const caseSensitive = defineModel<boolean>('case_sensitive', { required: true });
const acceptableAnswers = defineModel<string[]>('acceptable_answers', { required: true });

function addAnswer(): void {
    acceptableAnswers.value = [...acceptableAnswers.value, ''];
}

function removeAnswer(index: number): void {
    acceptableAnswers.value = acceptableAnswers.value.filter((_, i) => i !== index);
}

function updateAnswer(index: number, value: string): void {
    acceptableAnswers.value = acceptableAnswers.value.map((answer, i) => (i === index ? value : answer));
}
</script>

<template>
    <div class="mb-3">
        <label for="open-short-match-mode" class="form-label">{{ t('questions.editor.openShort.matchMode') }}</label>
        <select id="open-short-match-mode" v-model="matchMode" class="form-select">
            <option value="exact">{{ t('questions.editor.openShort.exactMatch') }}</option>
            <option value="contains">{{ t('questions.editor.openShort.contains') }}</option>
            <option value="regex">{{ t('questions.editor.openShort.regex') }}</option>
        </select>
    </div>
    <div class="form-check mb-3">
        <input id="open-short-case-sensitive" v-model="caseSensitive" class="form-check-input" type="checkbox" />
        <label class="form-check-label" for="open-short-case-sensitive">{{
            t('questions.editor.openShort.caseSensitive')
        }}</label>
    </div>
    <div class="mb-3">
        <label class="form-label">{{ t('questions.editor.openShort.acceptableAnswers') }}</label>
        <div v-for="(answer, index) in acceptableAnswers" :key="index" class="input-group mb-2">
            <input
                type="text"
                class="form-control"
                :value="answer"
                @input="updateAnswer(index, ($event.target as HTMLInputElement).value)"
            />
            <button type="button" class="btn btn-outline-danger" @click="removeAnswer(index)">
                {{ t('questions.common.remove') }}
            </button>
        </div>
        <button type="button" class="btn btn-outline-secondary btn-sm" @click="addAnswer">
            {{ t('questions.editor.openShort.addAnswer') }}
        </button>
    </div>
</template>
