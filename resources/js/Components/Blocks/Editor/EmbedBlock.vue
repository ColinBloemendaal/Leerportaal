<script setup lang="ts">
const url = defineModel<string>('url', { required: true });
</script>

<template>
    <div class="mb-3">
        <label for="embed-url" class="form-label">Embed URL</label>
        <input id="embed-url" v-model="url" type="url" class="form-control" placeholder="https://..." />
    </div>
</template>
