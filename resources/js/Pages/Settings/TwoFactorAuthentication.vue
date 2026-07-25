<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import { router, useForm } from '@inertiajs/vue3';

defineOptions({ layout: AppLayout });

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
    <h1 class="h4 mb-4">Two-factor authentication</h1>

    <div v-if="!enabled && !pending">
        <p class="text-muted">Add an extra layer of security to your account using an authenticator app.</p>
        <button type="button" class="btn btn-primary" @click="enable">Enable two-factor authentication</button>
    </div>

    <div v-else-if="pending">
        <p class="text-muted">
            Scan this QR code with your authenticator app, then enter the code it shows to finish setting up.
        </p>
        <!-- eslint-disable-next-line vue/no-v-html -- server-generated SVG (BaconQrCode) from our own TwoFactorAuthenticator, never user input -->
        <div v-if="props.qrCodeSvg" class="mb-3" v-html="props.qrCodeSvg" />

        <form class="row g-2 align-items-end" @submit.prevent="confirm">
            <div class="col-auto">
                <label for="code" class="form-label">Code</label>
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
                <button type="submit" class="btn btn-primary" :disabled="confirmForm.processing">Confirm</button>
            </div>
        </form>
    </div>

    <div v-else>
        <p class="text-success">Two-factor authentication is enabled.</p>

        <div class="d-flex gap-2 mb-3">
            <button type="button" class="btn btn-outline-secondary" @click="regenerateRecoveryCodes">
                Regenerate recovery codes
            </button>
            <button type="button" class="btn btn-outline-danger" @click="disable">Disable</button>
        </div>
    </div>

    <div v-if="props.recoveryCodes" class="alert alert-warning mt-3">
        <p class="fw-semibold">Save these recovery codes somewhere safe. Each can only be used once.</p>
        <ul class="mb-0 font-monospace">
            <li v-for="code in props.recoveryCodes" :key="code">{{ code }}</li>
        </ul>
    </div>
</template>
