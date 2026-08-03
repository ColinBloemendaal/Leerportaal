<script setup lang="ts">
// No content fields -- a divider has nothing to configure.
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
</script>

<template>
    <p class="text-muted small mb-0">{{ t('blocks.editor.divider.description') }}</p>
</template>
