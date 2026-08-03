<script setup lang="ts">
import type { DropdownInTextBlank } from '@/types/questions';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

// Computed, not an inline template expression -- see FillInBlankQuestion's
// identical comment: the message's literal "{{id}}" would otherwise sit
// inside the template's own {{ }} mustache delimiters.
const templateLabel = computed(() => t('questions.editor.dropdownInText.template', { placeholder: '{{id}}' }));

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
        <label for="dropdown-in-text-template" class="form-label">{{ templateLabel }}</label>
        <textarea id="dropdown-in-text-template" v-model="template" class="form-control" rows="3"></textarea>
    </div>
    <div class="mb-3">
        <label class="form-label">{{ t('questions.editor.dropdownInText.blanks') }}</label>
        <div v-for="blank in blanks" :key="blank.id" class="input-group mb-2">
            <span class="input-group-text">{{ blank.id }}</span>
            <input
                type="text"
                class="form-control"
                :placeholder="t('questions.editor.dropdownInText.optionsPlaceholder')"
                :value="blank.options.join(', ')"
                @input="updateOptions(blank.id, ($event.target as HTMLInputElement).value)"
            />
            <select
                class="form-select"
                :value="blank.correct_option"
                @change="updateCorrectOption(blank.id, ($event.target as HTMLSelectElement).value)"
            >
                <option value="" disabled>{{ t('questions.editor.dropdownInText.correctOption') }}</option>
                <option v-for="option in blank.options" :key="option" :value="option">{{ option }}</option>
            </select>
            <button type="button" class="btn btn-outline-danger" @click="removeBlank(blank.id)">
                {{ t('questions.common.remove') }}
            </button>
        </div>
        <button type="button" class="btn btn-outline-secondary btn-sm" @click="addBlank">
            {{ t('questions.editor.dropdownInText.addBlank') }}
        </button>
    </div>
</template>
