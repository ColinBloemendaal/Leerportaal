<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import { useI18n } from 'vue-i18n';

defineOptions({ layout: AppLayout });

const props = defineProps<{
    document: string;
}>();

const { t } = useI18n();
</script>

<template>
    <h1 class="h4 mb-3">{{ t('settings.subprocessors.title') }}</h1>

    <pre class="subprocessors-document border rounded p-3 bg-body-tertiary">{{ props.document }}</pre>
</template>

<style scoped>
/* Same reasoning as Settings/Dpa.vue: preserve the markdown table's
   alignment while still wrapping the prose paragraphs. */
.subprocessors-document {
    white-space: pre-wrap;
}
</style>
