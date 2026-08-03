<script setup lang="ts">
import { useI18n } from 'vue-i18n';

const url = defineModel<string>('url', { required: true });
const alt = defineModel<string>('alt', { required: true });
const caption = defineModel<string | null>('caption', { required: true });
const { t } = useI18n();
</script>

<template>
    <div class="mb-3">
        <label for="image-url" class="form-label">{{ t('blocks.editor.image.url') }}</label>
        <input id="image-url" v-model="url" type="url" class="form-control" placeholder="https://..." />
    </div>
    <div class="mb-3">
        <label for="image-alt" class="form-label">{{ t('blocks.editor.image.alt') }}</label>
        <input id="image-alt" v-model="alt" type="text" class="form-control" />
    </div>
    <div class="mb-3">
        <label for="image-caption" class="form-label">{{ t('blocks.editor.image.caption') }}</label>
        <input id="image-caption" v-model="caption" type="text" class="form-control" />
    </div>
</template>
