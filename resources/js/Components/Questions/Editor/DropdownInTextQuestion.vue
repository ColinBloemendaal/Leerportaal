<script setup lang="ts">
import type { DropdownInTextBlank } from '@/types/questions';

const template = defineModel<string>('template', { required: true });
const blanks = defineModel<DropdownInTextBlank[]>('blanks', { required: true });

function addBlank(): void {
    blanks.value = [...blanks.value, { id: crypto.randomUUID(), options: [], correct_option: '' }];
}

function removeBlank(id: string): void {
    blanks.value = blanks.value.filter((blank) => blank.id !== id);
}

function updateOptions(id: string, raw: string): void {
    const options = raw
        .split(',')
        .map((value) => value.trim())
        .filter((value) => value !== '');
    blanks.value = blanks.value.map((blank) => (blank.id === id ? { ...blank, options } : blank));
}

function updateCorrectOption(id: string, correct_option: string): void {
    blanks.value = blanks.value.map((blank) => (blank.id === id ? { ...blank, correct_option } : blank));
}
</script>

<template>
    <div class="mb-3">
        <label for="dropdown-in-text-template" class="form-label">Template (use {{ '{{id}}' }} for each blank)</label>
        <textarea id="dropdown-in-text-template" v-model="template" class="form-control" rows="3"></textarea>
    </div>
    <div class="mb-3">
        <label class="form-label">Blanks</label>
        <div v-for="blank in blanks" :key="blank.id" class="input-group mb-2">
            <span class="input-group-text">{{ blank.id }}</span>
            <input
                type="text"
                class="form-control"
                placeholder="Options, comma-separated"
                :value="blank.options.join(', ')"
                @input="updateOptions(blank.id, ($event.target as HTMLInputElement).value)"
            />
            <select
                class="form-select"
                :value="blank.correct_option"
                @change="updateCorrectOption(blank.id, ($event.target as HTMLSelectElement).value)"
            >
                <option value="" disabled>Correct option</option>
                <option v-for="option in blank.options" :key="option" :value="option">{{ option }}</option>
            </select>
            <button type="button" class="btn btn-outline-danger" @click="removeBlank(blank.id)">Remove</button>
        </div>
        <button type="button" class="btn btn-outline-secondary btn-sm" @click="addBlank">Add blank</button>
    </div>
</template>
