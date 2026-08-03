<script setup lang="ts">
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { router, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

defineOptions({ layout: GuestLayout });

const { t } = useI18n();
const page = usePage();

function resend() {
    router.post('/email/verification-notification');
}
</script>

<template>
    <h1 class="h4 mb-3">{{ t('auth.verifyEmail.title') }}</h1>
    <p class="text-muted">{{ t('auth.verifyEmail.description') }}</p>

    <div v-if="page.props.flash.status" class="alert alert-success">{{ page.props.flash.status }}</div>

    <button type="button" class="btn btn-primary" @click="resend">{{ t('auth.verifyEmail.resend') }}</button>
</template>
