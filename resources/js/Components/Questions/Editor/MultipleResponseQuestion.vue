<script setup lang="ts">
import type { MultipleResponseOption } from '@/types/questions';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const options = defineModel<MultipleResponseOption[]>('options', { required: true });
const correctOptionIds = defineModel<string[]>('correct_option_ids', { required: true });

function addOption(): void {
    options.value = [...options.value, { id: crypto.randomUUID(), text: '' }];
}

function removeOption(id: string): void {
    options.value = options.value.filter((option) => option.id !== id);
    correctOptionIds.value = correctOptionIds.value.filter((optionId) => optionId !== id);
}

function updateOptionText(id: string, text: string): void {
    options.value = options.value.map((option) => (option.id === id ? { ...option, text } : option));
}

function toggleCorrect(id: string, checked: boolean): void {
    correctOptionIds.value = checked
        ? [...correctOptionIds.value, id]
        : correctOptionIds.value.filter((optionId) => optionId !== id);
}
</script>

<template>
    <div class="mb-3">
        <label class="form-label">{{ t('questions.editor.multipleResponse.options') }}</label>
        <div v-for="option in options" :key="option.id" class="input-group mb-2">
            <div class="input-group-text">
                <input
                    type="checkbox"
                    class="form-check-input mt-0"
                    :checked="correctOptionIds.includes(option.id)"
                    :aria-label="t('questions.editor.multipleResponse.correctAnswerAria', { text: option.text })"
                    @change="toggleCorrect(option.id, ($event.target as HTMLInputElement).checked)"
                />
            </div>
            <input
                type="text"
                class="form-control"
                :value="option.text"
                @input="updateOptionText(option.id, ($event.target as HTMLInputElement).value)"
            />
            <button type="button" class="btn btn-outline-danger" @click="removeOption(option.id)">
                {{ t('questions.common.remove') }}
            </button>
        </div>
        <button type="button" class="btn btn-outline-secondary btn-sm" @click="addOption">
            {{ t('questions.editor.multipleResponse.addOption') }}
        </button>
    </div>
</template>
