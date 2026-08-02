<script setup lang="ts">
const html = defineModel<string>('html', { required: true });
</script>

<template>
    <div class="mb-3">
        <label for="rich-text-html" class="form-label">Text</label>
        <textarea id="rich-text-html" v-model="html" class="form-control" rows="6"></textarea>
    </div>
</template>
