<script setup lang="ts">
import type { LikertPayload } from '@/types/questions';

defineProps<{ payload: LikertPayload; questionId: string | number }>();

const answer = defineModel<string | null>({ default: null });
</script>

<template>
    <fieldset>
        <div v-for="point in payload.scale" :key="point.value" class="form-check form-check-inline">
            <input
                :id="`question-${questionId}-${point.value}`"
                v-model="answer"
                class="form-check-input"
                type="radio"
                :name="`question-${questionId}`"
                :value="point.value"
            />
            <label class="form-check-label" :for="`question-${questionId}-${point.value}`">{{ point.label }}</label>
        </div>
    </fieldset>
</template>
