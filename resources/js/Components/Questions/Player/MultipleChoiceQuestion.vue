<script setup lang="ts">
import type { MultipleChoicePayload } from '@/types/questions';

defineProps<{ payload: MultipleChoicePayload; questionId: string | number }>();

const answer = defineModel<string | null>({ default: null });
</script>

<template>
    <fieldset>
        <div v-for="option in payload.options" :key="option.id" class="form-check">
            <input
                :id="`question-${questionId}-option-${option.id}`"
                v-model="answer"
                class="form-check-input"
                type="radio"
                :name="`question-${questionId}`"
                :value="option.id"
            />
            <label class="form-check-label" :for="`question-${questionId}-option-${option.id}`">
                {{ option.text }}
            </label>
        </div>
    </fieldset>
</template>
