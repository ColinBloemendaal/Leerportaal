<script setup lang="ts">
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { router, usePage } from '@inertiajs/vue3';

defineOptions({ layout: GuestLayout });

const page = usePage();

function resend() {
    router.post('/email/verification-notification');
}
</script>

<template>
    <h1 class="h4 mb-3">Verify your email</h1>
    <p class="text-muted">
        Thanks for signing up! Before getting started, please verify your email address by clicking the link we emailed
        you.
    </p>

    <div v-if="page.props.flash.status" class="alert alert-success">{{ page.props.flash.status }}</div>

    <button type="button" class="btn btn-primary" @click="resend">Resend verification email</button>
</template>
