<script setup lang="ts">
const url = defineModel<string>('url', { required: true });
const provider = defineModel<string | null>('provider', { required: true });
</script>

<template>
    <div class="mb-3">
        <label for="video-url" class="form-label">Video URL</label>
        <input id="video-url" v-model="url" type="url" class="form-control" placeholder="https://vimeo.com/..." />
        <div class="form-text">External hosting only (Vimeo, Mux, unlisted YouTube) -- no self-hosted video.</div>
    </div>
    <div class="mb-3">
        <label for="video-provider" class="form-label">Provider (optional)</label>
        <input id="video-provider" v-model="provider" type="text" class="form-control" placeholder="vimeo" />
    </div>
</template>
