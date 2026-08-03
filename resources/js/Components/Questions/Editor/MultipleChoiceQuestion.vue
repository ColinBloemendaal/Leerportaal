<script setup lang="ts">
import type { MultipleChoiceOption } from '@/types/questions';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const options = defineModel<MultipleChoiceOption[]>('options', { required: true });
const correctOptionId = defineModel<string>('correct_option_id', { required: true });

function addOption(): void {
    options.value = [...options.value, { id: crypto.randomUUID(), text: '' }];
}

function removeOption(id: string): void {
    options.value = options.value.filter((option) => option.id !== id);
    if (correctOptionId.value === id) {
        correctOptionId.value = '';
    }
}

function updateOptionText(id: string, text: string): void {
    options.value = options.value.map((option) => (option.id === id ? { ...option, text } : option));
}
</script>

<template>
    <div class="mb-3">
        <label class="form-label">{{ t('questions.editor.multipleChoice.options') }}</label>
        <div v-for="option in options" :key="option.id" class="input-group mb-2">
            <div class="input-group-text">
                <input
                    type="radio"
                    class="form-check-input mt-0"
                    :checked="correctOptionId === option.id"
                    :aria-label="t('questions.editor.multipleChoice.correctAnswerAria', { text: option.text })"
                    @change="correctOptionId = option.id"
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
            {{ t('questions.editor.multipleChoice.addOption') }}
        </button>
    </div>
</template>
