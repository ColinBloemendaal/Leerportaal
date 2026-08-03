<script setup lang="ts">
import type { OrderingItem } from '@/types/questions';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const items = defineModel<OrderingItem[]>('items', { required: true });

function addItem(): void {
    items.value = [...items.value, { id: crypto.randomUUID(), text: '' }];
}

function removeItem(id: string): void {
    items.value = items.value.filter((item) => item.id !== id);
}

function updateText(id: string, text: string): void {
    items.value = items.value.map((item) => (item.id === id ? { ...item, text } : item));
}

function move(index: number, direction: -1 | 1): void {
    const target = index + direction;
    if (target < 0 || target >= items.value.length) return;
    const next = [...items.value];
    [next[index], next[target]] = [next[target], next[index]];
    items.value = next;
}
</script>

<template>
    <div class="mb-3">
        <label class="form-label">{{ t('questions.editor.ordering.itemsLabel') }}</label>
        <div v-for="(item, index) in items" :key="item.id" class="input-group mb-2">
            <button
                type="button"
                class="btn btn-outline-secondary"
                :disabled="index === 0"
                :aria-label="t('questions.editor.ordering.moveUpAria', { text: item.text })"
                @click="move(index, -1)"
            >
                ↑
            </button>
            <button
                type="button"
                class="btn btn-outline-secondary"
                :disabled="index === items.length - 1"
                :aria-label="t('questions.editor.ordering.moveDownAria', { text: item.text })"
                @click="move(index, 1)"
            >
                ↓
            </button>
            <input
                type="text"
                class="form-control"
                :value="item.text"
                @input="updateText(item.id, ($event.target as HTMLInputElement).value)"
            />
            <button type="button" class="btn btn-outline-danger" @click="removeItem(item.id)">
                {{ t('questions.common.remove') }}
            </button>
        </div>
        <button type="button" class="btn btn-outline-secondary btn-sm" @click="addItem">
            {{ t('questions.editor.ordering.addItem') }}
        </button>
    </div>
</template>
