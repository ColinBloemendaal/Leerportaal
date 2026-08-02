<script setup lang="ts">
defineProps<{ questionId: string | number }>();

const answer = defineModel<number | null>({ default: null });
</script>

<template>
    <input
        :id="`question-${questionId}-answer`"
        v-model.number="answer"
        type="number"
        step="any"
        class="form-control"
        :aria-label="`Numeric answer for question ${questionId}`"
    />
</template>
