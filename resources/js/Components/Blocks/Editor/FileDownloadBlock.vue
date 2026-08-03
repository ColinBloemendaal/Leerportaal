<script setup lang="ts">
import { useI18n } from 'vue-i18n';

const url = defineModel<string>('url', { required: true });
const label = defineModel<string>('label', { required: true });
const { t } = useI18n();
</script>

<template>
    <div class="mb-3">
        <label for="file-url" class="form-label">{{ t('blocks.editor.fileDownload.url') }}</label>
        <input id="file-url" v-model="url" type="url" class="form-control" placeholder="https://..." />
    </div>
    <div class="mb-3">
        <label for="file-label" class="form-label">{{ t('blocks.editor.fileDownload.linkText') }}</label>
        <input
            id="file-label"
            v-model="label"
            type="text"
            class="form-control"
            :placeholder="t('blocks.editor.fileDownload.linkTextPlaceholder')"
        />
    </div>
</template>
