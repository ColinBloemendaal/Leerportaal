<script setup lang="ts">
import { computed } from 'vue';
import type { MatchingPayload } from '@/types/questions';

const props = defineProps<{ payload: MatchingPayload; questionId: string | number }>();

const answer = defineModel<Record<string, string>>({ default: () => ({}) });

// Shuffled once per render via a stable sort key, not re-shuffled on every
// answer change, so the option order doesn't jump around as the cursist
// picks answers.
const shuffledRightValues = computed(() => [...props.payload.pairs.map((pair) => pair.right)].sort());

function select(pairId: string, value: string): void {
    answer.value = { ...answer.value, [pairId]: value };
}
</script>

<template>
    <div v-for="pair in payload.pairs" :key="pair.id" class="mb-2 row align-items-center">
        <label class="col-auto col-form-label" :for="`question-${questionId}-pair-${pair.id}`">{{ pair.left }}</label>
        <div class="col-auto">
            <select
                :id="`question-${questionId}-pair-${pair.id}`"
                class="form-select"
                :value="answer[pair.id] ?? ''"
                @change="select(pair.id, ($event.target as HTMLSelectElement).value)"
            >
                <option value="" disabled>Choose a match</option>
                <option v-for="value in shuffledRightValues" :key="value" :value="value">{{ value }}</option>
            </select>
        </div>
    </div>
</template>
