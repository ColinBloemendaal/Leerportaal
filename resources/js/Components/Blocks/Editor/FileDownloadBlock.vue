<script setup lang="ts">
const url = defineModel<string>('url', { required: true });
const label = defineModel<string>('label', { required: true });
</script>

<template>
    <div class="mb-3">
        <label for="file-url" class="form-label">File URL</label>
        <input id="file-url" v-model="url" type="url" class="form-control" placeholder="https://..." />
    </div>
    <div class="mb-3">
        <label for="file-label" class="form-label">Link text</label>
        <input id="file-label" v-model="label" type="text" class="form-control" placeholder="Download handout" />
    </div>
</template>
