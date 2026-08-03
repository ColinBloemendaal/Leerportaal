<script setup lang="ts">
import { useI18n } from 'vue-i18n';

const url = defineModel<string>('url', { required: true });
const { t } = useI18n();
</script>

<template>
    <div class="mb-3">
        <label for="embed-url" class="form-label">{{ t('blocks.editor.embed.url') }}</label>
        <input id="embed-url" v-model="url" type="url" class="form-control" placeholder="https://..." />
    </div>
</template>
