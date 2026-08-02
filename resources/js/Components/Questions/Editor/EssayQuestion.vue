<script setup lang="ts">
import type { EssayRubricCriterion } from '@/types/questions';

const rubric = defineModel<EssayRubricCriterion[]>('rubric', { required: true });
const minWords = defineModel<number | null>('min_words', { required: true });
const maxWords = defineModel<number | null>('max_words', { required: true });

function addCriterion(): void {
    rubric.value = [...rubric.value, { criterion: '', points: 0 }];
}

function removeCriterion(index: number): void {
    rubric.value = rubric.value.filter((_, i) => i !== index);
}

function updateCriterion(index: number, field: keyof EssayRubricCriterion, value: string | number): void {
    rubric.value = rubric.value.map((entry, i) => (i === index ? { ...entry, [field]: value } : entry));
}
</script>

<template>
    <div class="row mb-3">
        <div class="col">
            <label for="essay-min-words" class="form-label">Minimum words</label>
            <input id="essay-min-words" v-model.number="minWords" type="number" class="form-control" min="0" />
        </div>
        <div class="col">
            <label for="essay-max-words" class="form-label">Maximum words</label>
            <input id="essay-max-words" v-model.number="maxWords" type="number" class="form-control" min="0" />
        </div>
    </div>
    <div class="mb-3">
        <label class="form-label">Grading rubric</label>
        <div v-for="(entry, index) in rubric" :key="index" class="input-group mb-2">
            <input
                type="text"
                class="form-control"
                placeholder="Criterion"
                :value="entry.criterion"
                @input="updateCriterion(index, 'criterion', ($event.target as HTMLInputElement).value)"
            />
            <input
                type="number"
                class="form-control"
                placeholder="Points"
                min="0"
                :value="entry.points"
                @input="updateCriterion(index, 'points', Number(($event.target as HTMLInputElement).value))"
            />
            <button type="button" class="btn btn-outline-danger" @click="removeCriterion(index)">Remove</button>
        </div>
        <button type="button" class="btn btn-outline-secondary btn-sm" @click="addCriterion">Add criterion</button>
    </div>
</template>
