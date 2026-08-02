<script setup lang="ts">
const allowedMimeTypes = defineModel<string[]>('allowed_mime_types', { required: true });
const maxSizeBytes = defineModel<number | null>('max_size_bytes', { required: true });

function updateMimeTypes(raw: string): void {
    allowedMimeTypes.value = raw
        .split(',')
        .map((value) => value.trim())
        .filter((value) => value !== '');
}
</script>

<template>
    <div class="mb-3">
        <label for="file-upload-mime-types" class="form-label">Allowed file types (comma-separated MIME types)</label>
        <input
            id="file-upload-mime-types"
            type="text"
            class="form-control"
            :value="allowedMimeTypes.join(', ')"
            @input="updateMimeTypes(($event.target as HTMLInputElement).value)"
        />
    </div>
    <div class="mb-3">
        <label for="file-upload-max-size" class="form-label">Maximum file size (bytes)</label>
        <input id="file-upload-max-size" v-model.number="maxSizeBytes" type="number" class="form-control" min="1" />
    </div>
</template>
