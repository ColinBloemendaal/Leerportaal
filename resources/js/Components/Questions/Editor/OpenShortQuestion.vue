<script setup lang="ts">
import type { OpenShortMatchMode } from '@/types/questions';

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
        <label for="open-short-match-mode" class="form-label">Match mode</label>
        <select id="open-short-match-mode" v-model="matchMode" class="form-select">
            <option value="exact">Exact match</option>
            <option value="contains">Contains</option>
            <option value="regex">Regular expression</option>
        </select>
    </div>
    <div class="form-check mb-3">
        <input id="open-short-case-sensitive" v-model="caseSensitive" class="form-check-input" type="checkbox" />
        <label class="form-check-label" for="open-short-case-sensitive">Case sensitive</label>
    </div>
    <div class="mb-3">
        <label class="form-label">Acceptable answers</label>
        <div v-for="(answer, index) in acceptableAnswers" :key="index" class="input-group mb-2">
            <input
                type="text"
                class="form-control"
                :value="answer"
                @input="updateAnswer(index, ($event.target as HTMLInputElement).value)"
            />
            <button type="button" class="btn btn-outline-danger" @click="removeAnswer(index)">Remove</button>
        </div>
        <button type="button" class="btn btn-outline-secondary btn-sm" @click="addAnswer">Add answer</button>
    </div>
</template>
