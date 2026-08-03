<script setup lang="ts">
import { useI18n } from 'vue-i18n';

const html = defineModel<string>('html', { required: true });
const { t } = useI18n();
</script>

<template>
    <div class="mb-3">
        <label for="rich-text-html" class="form-label">{{ t('blocks.editor.richText.text') }}</label>
        <textarea id="rich-text-html" v-model="html" class="form-control" rows="6"></textarea>
    </div>
</template>
