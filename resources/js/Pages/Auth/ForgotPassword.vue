<script setup lang="ts">
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { useForm, usePage } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

defineOptions({ layout: GuestLayout });

const { t } = useI18n();

const form = useForm({
    email: '',
});

const page = usePage();

function submit() {
    form.post('/forgot-password');
}
</script>

<template>
    <h1 class="h4 mb-4">{{ t('auth.forgotPassword.title') }}</h1>

    <div v-if="page.props.flash.status" class="alert alert-success">{{ page.props.flash.status }}</div>

    <form @submit.prevent="submit">
        <div class="mb-3">
            <label for="email" class="form-label">{{ t('auth.forgotPassword.email') }}</label>
            <input
                id="email"
                v-model="form.email"
                type="email"
                class="form-control"
                :class="{ 'is-invalid': form.errors.email }"
                required
                autofocus
            />
            <div v-if="form.errors.email" class="invalid-feedback">{{ form.errors.email }}</div>
        </div>

        <button type="submit" class="btn btn-primary w-100" :disabled="form.processing">
            {{ t('auth.forgotPassword.submit') }}
        </button>
    </form>
</template>
