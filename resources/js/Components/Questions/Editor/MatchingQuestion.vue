<script setup lang="ts">
import type { MatchingPair } from '@/types/questions';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

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
        <label class="form-label">{{ t('questions.editor.matching.pairs') }}</label>
        <div v-for="pair in pairs" :key="pair.id" class="input-group mb-2">
            <input
                type="text"
                class="form-control"
                :placeholder="t('questions.editor.matching.left')"
                :value="pair.left"
                @input="updatePair(pair.id, 'left', ($event.target as HTMLInputElement).value)"
            />
            <input
                type="text"
                class="form-control"
                :placeholder="t('questions.editor.matching.right')"
                :value="pair.right"
                @input="updatePair(pair.id, 'right', ($event.target as HTMLInputElement).value)"
            />
            <button type="button" class="btn btn-outline-danger" @click="removePair(pair.id)">
                {{ t('questions.common.remove') }}
            </button>
        </div>
        <button type="button" class="btn btn-outline-secondary btn-sm" @click="addPair">
            {{ t('questions.editor.matching.addPair') }}
        </button>
    </div>
</template>
