<script setup lang="ts">
import { computed } from 'vue';
import type { DropdownInTextPayload } from '@/types/questions';

const props = defineProps<{ payload: DropdownInTextPayload; questionId: string | number }>();

const answer = defineModel<Record<string, string>>({ default: () => ({}) });

// Same text/blank segment split as fill_in_blank's player -- see that
// component for why this is done client-side rather than via v-html.
const segments = computed(() => {
    const parts: Array<{ type: 'text'; value: string } | { type: 'blank'; id: string }> = [];
    const pattern = /\{\{(.+?)\}\}/g;
    let lastIndex = 0;
    let match: RegExpExecArray | null;

    while ((match = pattern.exec(props.payload.template)) !== null) {
        if (match.index > lastIndex) {
            parts.push({ type: 'text', value: props.payload.template.slice(lastIndex, match.index) });
        }
        parts.push({ type: 'blank', id: match[1] });
        lastIndex = pattern.lastIndex;
    }

    if (lastIndex < props.payload.template.length) {
        parts.push({ type: 'text', value: props.payload.template.slice(lastIndex) });
    }

    return parts;
});

function optionsFor(blankId: string): string[] {
    return props.payload.blanks.find((blank) => blank.id === blankId)?.options ?? [];
}

function update(blankId: string, value: string): void {
    answer.value = { ...answer.value, [blankId]: value };
}
</script>

<template>
    <p>
        <template v-for="(segment, index) in segments" :key="index">
            <span v-if="segment.type === 'text'">{{ segment.value }}</span>
            <select
                v-else
                class="d-inline-block w-auto mx-1"
                :aria-label="`Blank ${segment.id} for question ${questionId}`"
                :value="answer[segment.id] ?? ''"
                @change="update(segment.id, ($event.target as HTMLSelectElement).value)"
            >
                <option value="" disabled>Choose</option>
                <option v-for="option in optionsFor(segment.id)" :key="option" :value="option">{{ option }}</option>
            </select>
        </template>
    </p>
</template>
