<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import { router, useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

defineOptions({ layout: AppLayout });

const { t } = useI18n();

const props = defineProps<{
    enabled: boolean;
    pending: boolean;
    qrCodeSvg: string | null;
    recoveryCodes: string[] | null;
}>();

const confirmForm = useForm({
    code: '',
});

function enable() {
    router.post('/settings/two-factor');
}

function confirm() {
    confirmForm.post('/settings/two-factor/confirm');
}

function disable() {
    router.delete('/settings/two-factor');
}

function regenerateRecoveryCodes() {
    router.post('/settings/two-factor/recovery-codes');
}
</script>

<template>
    <h1 class="h4 mb-4">{{ t('settings.twoFactor.title') }}</h1>

    <div v-if="!enabled && !pending">
        <p class="text-muted">{{ t('settings.twoFactor.introDescription') }}</p>
        <button type="button" class="btn btn-primary" @click="enable">{{ t('settings.twoFactor.enable') }}</button>
    </div>

    <div v-else-if="pending">
        <p class="text-muted">
            {{ t('settings.twoFactor.pendingDescription') }}
        </p>
        <!-- eslint-disable-next-line vue/no-v-html -- server-generated SVG (BaconQrCode) from our own TwoFactorAuthenticator, never user input -->
        <div v-if="props.qrCodeSvg" class="mb-3" v-html="props.qrCodeSvg" />

        <form class="row g-2 align-items-end" @submit.prevent="confirm">
            <div class="col-auto">
                <label for="code" class="form-label">{{ t('settings.twoFactor.code') }}</label>
                <input
                    id="code"
                    v-model="confirmForm.code"
                    type="text"
                    inputmode="numeric"
                    class="form-control"
                    :class="{ 'is-invalid': confirmForm.errors.code }"
                    required
                />
                <div v-if="confirmForm.errors.code" class="invalid-feedback">{{ confirmForm.errors.code }}</div>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary" :disabled="confirmForm.processing">
                    {{ t('settings.twoFactor.confirm') }}
                </button>
            </div>
        </form>
    </div>

    <div v-else>
        <p class="text-success">{{ t('settings.twoFactor.enabledDescription') }}</p>

        <div class="d-flex gap-2 mb-3">
            <button type="button" class="btn btn-outline-secondary" @click="regenerateRecoveryCodes">
                {{ t('settings.twoFactor.regenerate') }}
            </button>
            <button type="button" class="btn btn-outline-danger" @click="disable">
                {{ t('settings.twoFactor.disable') }}
            </button>
        </div>
    </div>

    <div v-if="props.recoveryCodes" class="alert alert-warning mt-3">
        <p class="fw-semibold">{{ t('settings.twoFactor.recoveryCodesWarning') }}</p>
        <ul class="mb-0 font-monospace">
            <li v-for="code in props.recoveryCodes" :key="code">{{ code }}</li>
        </ul>
    </div>
</template>
