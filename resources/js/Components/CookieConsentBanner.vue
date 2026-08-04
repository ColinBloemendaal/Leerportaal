<script setup lang="ts">
import { useCookieConsent } from '@/Composables/useCookieConsent';
import { useI18n } from 'vue-i18n';

const { hasAnswered, acceptAll, acceptEssentialOnly } = useCookieConsent();
const { t } = useI18n();
</script>

<template>
    <div v-if="!hasAnswered()" class="fixed-bottom border-top bg-body shadow-sm p-3">
        <div class="container-fluid d-flex flex-wrap align-items-center justify-content-between gap-3">
            <p class="mb-0 small">{{ t('cookieConsent.message') }}</p>
            <div class="d-flex gap-2 flex-shrink-0">
                <button type="button" class="btn btn-sm btn-outline-secondary" @click="acceptEssentialOnly">
                    {{ t('cookieConsent.essentialOnly') }}
                </button>
                <button type="button" class="btn btn-sm btn-primary" @click="acceptAll">
                    {{ t('cookieConsent.acceptAll') }}
                </button>
            </div>
        </div>
    </div>
</template>
