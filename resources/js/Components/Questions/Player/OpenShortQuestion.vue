<script setup lang="ts">
defineProps<{ questionId: string | number }>();

const answer = defineModel<string>({ default: '' });
</script>

<template>
    <input
        :id="`question-${questionId}-answer`"
        v-model="answer"
        type="text"
        class="form-control"
        :aria-label="`Answer for question ${questionId}`"
    />
</template>
