<script setup lang="ts">
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { useI18n } from 'vue-i18n';

defineOptions({ layout: GuestLayout });

const { t } = useI18n();
</script>

<template>
    <h1 class="h4 mb-3">{{ t('welcome.title') }}</h1>
    <p class="text-muted mb-0">
        {{ t('welcome.description') }}
    </p>
</template>
