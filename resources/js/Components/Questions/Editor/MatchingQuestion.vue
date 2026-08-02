<script setup lang="ts">
import type { MatchingPair } from '@/types/questions';

const pairs = defineModel<MatchingPair[]>('pairs', { required: true });

function addPair(): void {
    pairs.value = [...pairs.value, { id: crypto.randomUUID(), left: '', right: '' }];
}

function removePair(id: string): void {
    pairs.value = pairs.value.filter((pair) => pair.id !== id);
}

function updatePair(id: string, field: 'left' | 'right', value: string): void {
    pairs.value = pairs.value.map((pair) => (pair.id === id ? { ...pair, [field]: value } : pair));
}
</script>

<template>
    <div class="mb-3">
        <label class="form-label">Pairs</label>
        <div v-for="pair in pairs" :key="pair.id" class="input-group mb-2">
            <input
                type="text"
                class="form-control"
                placeholder="Left"
                :value="pair.left"
                @input="updatePair(pair.id, 'left', ($event.target as HTMLInputElement).value)"
            />
            <input
                type="text"
                class="form-control"
                placeholder="Right"
                :value="pair.right"
                @input="updatePair(pair.id, 'right', ($event.target as HTMLInputElement).value)"
            />
            <button type="button" class="btn btn-outline-danger" @click="removePair(pair.id)">Remove</button>
        </div>
        <button type="button" class="btn btn-outline-secondary btn-sm" @click="addPair">Add pair</button>
    </div>
</template>
