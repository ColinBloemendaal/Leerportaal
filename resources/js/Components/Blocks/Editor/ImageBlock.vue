<script setup lang="ts">
const url = defineModel<string>('url', { required: true });
const alt = defineModel<string>('alt', { required: true });
const caption = defineModel<string | null>('caption', { required: true });
</script>

<template>
    <div class="mb-3">
        <label for="image-url" class="form-label">Image URL</label>
        <input id="image-url" v-model="url" type="url" class="form-control" placeholder="https://..." />
    </div>
    <div class="mb-3">
        <label for="image-alt" class="form-label">Alt text</label>
        <input id="image-alt" v-model="alt" type="text" class="form-control" />
    </div>
    <div class="mb-3">
        <label for="image-caption" class="form-label">Caption</label>
        <input id="image-caption" v-model="caption" type="text" class="form-control" />
    </div>
</template>
