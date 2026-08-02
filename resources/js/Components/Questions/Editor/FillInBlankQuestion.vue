<script setup lang="ts">
import type { FillInBlankBlank } from '@/types/questions';

const template = defineModel<string>('template', { required: true });
const blanks = defineModel<FillInBlankBlank[]>('blanks', { required: true });

function addBlank(): void {
    blanks.value = [...blanks.value, { id: crypto.randomUUID(), acceptable_answers: [''], case_sensitive: false }];
}

function removeBlank(id: string): void {
    blanks.value = blanks.value.filter((blank) => blank.id !== id);
}

function updateAnswers(id: string, raw: string): void {
    const acceptable_answers = raw
        .split(',')
        .map((value) => value.trim())
        .filter((value) => value !== '');
    blanks.value = blanks.value.map((blank) => (blank.id === id ? { ...blank, acceptable_answers } : blank));
}

function toggleCaseSensitive(id: string, caseSensitive: boolean): void {
    blanks.value = blanks.value.map((blank) => (blank.id === id ? { ...blank, case_sensitive: caseSensitive } : blank));
}
</script>

<template>
    <div class="mb-3">
        <label for="fill-in-blank-template" class="form-label">Template (use {{ '{{id}}' }} for each blank)</label>
        <textarea id="fill-in-blank-template" v-model="template" class="form-control" rows="3"></textarea>
    </div>
    <div class="mb-3">
        <label class="form-label">Blanks</label>
        <div v-for="blank in blanks" :key="blank.id" class="input-group mb-2">
            <span class="input-group-text">{{ blank.id }}</span>
            <input
                type="text"
                class="form-control"
                placeholder="Acceptable answers, comma-separated"
                :value="blank.acceptable_answers.join(', ')"
                @input="updateAnswers(blank.id, ($event.target as HTMLInputElement).value)"
            />
            <div class="input-group-text">
                <input
                    type="checkbox"
                    class="form-check-input mt-0"
                    :checked="blank.case_sensitive"
                    :aria-label="`Case sensitive for blank ${blank.id}`"
                    @change="toggleCaseSensitive(blank.id, ($event.target as HTMLInputElement).checked)"
                />
            </div>
            <button type="button" class="btn btn-outline-danger" @click="removeBlank(blank.id)">Remove</button>
        </div>
        <button type="button" class="btn btn-outline-secondary btn-sm" @click="addBlank">Add blank</button>
    </div>
</template>
