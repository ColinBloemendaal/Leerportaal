<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import { useI18n } from 'vue-i18n';

defineOptions({ layout: AppLayout });

const { t } = useI18n();
</script>

<template>
    <h1 class="h4 mb-4">{{ t('settings.dataExport.title') }}</h1>

    <p>{{ t('settings.dataExport.description') }}</p>

    <div class="d-flex gap-2">
        <a href="/settings/data-export/json" class="btn btn-outline-secondary">
            {{ t('settings.dataExport.downloadJson') }}
        </a>
        <a href="/settings/data-export/html" class="btn btn-outline-secondary">
            {{ t('settings.dataExport.downloadHtml') }}
        </a>
    </div>
</template>
