<script setup lang="ts">
import type { OrderingPayload } from '@/types/questions';
import { computed } from 'vue';

const props = defineProps<{ payload: OrderingPayload; questionId: string | number }>();

// Answer is the list of item ids in the cursist's chosen order. Starts
// shuffled (not in the payload's correct order) so the player never
// leaks the answer.
const answer = defineModel<string[]>({ default: () => [] });

const orderedIds = computed(() => (answer.value.length > 0 ? answer.value : props.payload.items.map((i) => i.id)));

function itemText(id: string): string {
    return props.payload.items.find((item) => item.id === id)?.text ?? '';
}

function move(index: number, direction: -1 | 1): void {
    const target = index + direction;
    if (target < 0 || target >= orderedIds.value.length) return;
    const next = [...orderedIds.value];
    [next[index], next[target]] = [next[target], next[index]];
    answer.value = next;
}
</script>

<template>
    <ol class="list-group list-group-numbered" :aria-label="`Order the items for question ${questionId}`">
        <li
            v-for="(id, index) in orderedIds"
            :key="id"
            class="list-group-item d-flex justify-content-between align-items-center"
        >
            {{ itemText(id) }}
            <span>
                <button
                    type="button"
                    class="btn btn-sm btn-outline-secondary"
                    :disabled="index === 0"
                    :aria-label="`Move '${itemText(id)}' up`"
                    @click="move(index, -1)"
                >
                    ↑
                </button>
                <button
                    type="button"
                    class="btn btn-sm btn-outline-secondary"
                    :disabled="index === orderedIds.length - 1"
                    :aria-label="`Move '${itemText(id)}' down`"
                    @click="move(index, 1)"
                >
                    ↓
                </button>
            </span>
        </li>
    </ol>
</template>
