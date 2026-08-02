<script setup lang="ts">
defineProps<{ questionId: string | number }>();

const answer = defineModel<boolean | null>({ default: null });
</script>

<template>
    <fieldset>
        <div class="form-check">
            <input
                :id="`question-${questionId}-true`"
                v-model="answer"
                class="form-check-input"
                type="radio"
                :name="`question-${questionId}`"
                :value="true"
            />
            <label class="form-check-label" :for="`question-${questionId}-true`">True</label>
        </div>
        <div class="form-check">
            <input
                :id="`question-${questionId}-false`"
                v-model="answer"
                class="form-check-input"
                type="radio"
                :name="`question-${questionId}`"
                :value="false"
            />
            <label class="form-check-label" :for="`question-${questionId}-false`">False</label>
        </div>
    </fieldset>
</template>
