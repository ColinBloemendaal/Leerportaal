<script setup lang="ts">
import type { MultipleResponsePayload } from '@/types/questions';

defineProps<{ payload: MultipleResponsePayload; questionId: string | number }>();

const answer = defineModel<string[]>({ default: () => [] });

function toggle(id: string, checked: boolean): void {
    answer.value = checked ? [...answer.value, id] : answer.value.filter((optionId) => optionId !== id);
}
</script>

<template>
    <fieldset>
        <div v-for="option in payload.options" :key="option.id" class="form-check">
            <input
                :id="`question-${questionId}-option-${option.id}`"
                class="form-check-input"
                type="checkbox"
                :checked="answer.includes(option.id)"
                @change="toggle(option.id, ($event.target as HTMLInputElement).checked)"
            />
            <label class="form-check-label" :for="`question-${questionId}-option-${option.id}`">
                {{ option.text }}
            </label>
        </div>
    </fieldset>
</template>
