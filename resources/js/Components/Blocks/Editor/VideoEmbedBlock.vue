<script setup lang="ts">
import { useI18n } from 'vue-i18n';

const url = defineModel<string>('url', { required: true });
const provider = defineModel<string | null>('provider', { required: true });
const { t } = useI18n();
</script>

<template>
    <div class="mb-3">
        <label for="video-url" class="form-label">{{ t('blocks.editor.videoEmbed.url') }}</label>
        <input id="video-url" v-model="url" type="url" class="form-control" placeholder="https://vimeo.com/..." />
        <div class="form-text">{{ t('blocks.editor.videoEmbed.urlHelp') }}</div>
    </div>
    <div class="mb-3">
        <label for="video-provider" class="form-label">{{ t('blocks.editor.videoEmbed.provider') }}</label>
        <input id="video-provider" v-model="provider" type="text" class="form-control" placeholder="vimeo" />
    </div>
</template>
