<script setup lang="ts">
const correctAnswer = defineModel<boolean>('correct_answer', { required: true });
</script>

<template>
    <div class="mb-3">
        <label class="form-label">Correct answer</label>
        <div class="form-check">
            <input
                id="true-false-correct-true"
                class="form-check-input"
                type="radio"
                :checked="correctAnswer === true"
                @change="correctAnswer = true"
            />
            <label class="form-check-label" for="true-false-correct-true">True</label>
        </div>
        <div class="form-check">
            <input
                id="true-false-correct-false"
                class="form-check-input"
                type="radio"
                :checked="correctAnswer === false"
                @change="correctAnswer = false"
            />
            <label class="form-check-label" for="true-false-correct-false">False</label>
        </div>
    </div>
</template>
