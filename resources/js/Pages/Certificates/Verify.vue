<script setup lang="ts">
import GuestLayout from '@/Layouts/GuestLayout.vue';
import type { CertificateVerification } from '@/types/certificates';
import { useI18n } from 'vue-i18n';

defineOptions({ layout: GuestLayout });

defineProps<{ certificate: CertificateVerification | null }>();

const { t } = useI18n();
</script>

<template>
    <div v-if="certificate" class="text-center">
        <div class="text-success mb-3">
            <svg
                xmlns="http://www.w3.org/2000/svg"
                width="48"
                height="48"
                fill="currentColor"
                viewBox="0 0 16 16"
                aria-hidden="true"
            >
                <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16" />
                <path
                    d="M10.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425a.267.267 0 0 1 .02-.022"
                />
            </svg>
        </div>
        <h1 class="h4 mb-3">{{ t('certificates.verified') }}</h1>
        <p class="mb-1">
            <strong>{{ certificate.data.recipient_name }}</strong>
        </p>
        <p class="mb-1">{{ certificate.data.course_title }}</p>
        <p class="text-muted small mb-3">{{ t('certificates.issuedOn', { date: certificate.data.issued_at }) }}</p>
        <p class="text-muted small font-monospace">{{ certificate.data.verification_code }}</p>
    </div>

    <div v-else class="text-center">
        <div class="text-danger mb-3">
            <svg
                xmlns="http://www.w3.org/2000/svg"
                width="48"
                height="48"
                fill="currentColor"
                viewBox="0 0 16 16"
                aria-hidden="true"
            >
                <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14m0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16" />
                <path
                    d="M5.354 4.646a.5.5 0 1 0-.708.708L7.293 8l-2.647 2.646a.5.5 0 0 0 .708.708L8 8.707l2.646 2.647a.5.5 0 0 0 .708-.708L8.707 8l2.647-2.646a.5.5 0 0 0-.708-.708L8 7.293z"
                />
            </svg>
        </div>
        <h1 class="h4 mb-3">{{ t('certificates.notFoundTitle') }}</h1>
        <p class="text-muted">{{ t('certificates.notFoundDescription') }}</p>
    </div>
</template>
