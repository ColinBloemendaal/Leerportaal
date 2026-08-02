<script setup lang="ts">
import { computed } from 'vue';
import type { FillInBlankPayload } from '@/types/questions';

const props = defineProps<{ payload: FillInBlankPayload; questionId: string | number }>();

const answer = defineModel<Record<string, string>>({ default: () => ({}) });

// Splits "The capital of {{1}} is {{2}}." into alternating text/blank
// segments so the template can render inline inputs without any HTML
// interpolation of user-authored content.
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

function update(blankId: string, value: string): void {
    answer.value = { ...answer.value, [blankId]: value };
}
</script>

<template>
    <p>
        <template v-for="(segment, index) in segments" :key="index">
            <span v-if="segment.type === 'text'">{{ segment.value }}</span>
            <input
                v-else
                type="text"
                class="d-inline-block mx-1"
                style="width: 8rem"
                :aria-label="`Blank ${segment.id} for question ${questionId}`"
                :value="answer[segment.id] ?? ''"
                @input="update(segment.id, ($event.target as HTMLInputElement).value)"
            />
        </template>
    </p>
</template>
