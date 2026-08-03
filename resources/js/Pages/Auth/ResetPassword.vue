<script setup lang="ts">
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

defineOptions({ layout: GuestLayout });

const { t } = useI18n();

const props = defineProps<{
    token: string;
    email: string;
}>();

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

function submit() {
    form.post('/reset-password');
}
</script>

<template>
    <h1 class="h4 mb-4">{{ t('auth.resetPassword.title') }}</h1>

    <form @submit.prevent="submit">
        <div class="mb-3">
            <label for="email" class="form-label">{{ t('auth.resetPassword.email') }}</label>
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

        <div class="mb-3">
            <label for="password" class="form-label">{{ t('auth.resetPassword.password') }}</label>
            <input
                id="password"
                v-model="form.password"
                type="password"
                class="form-control"
                :class="{ 'is-invalid': form.errors.password }"
                required
            />
            <div v-if="form.errors.password" class="invalid-feedback">{{ form.errors.password }}</div>
        </div>

        <div class="mb-3">
            <label for="password_confirmation" class="form-label">{{
                t('auth.resetPassword.passwordConfirmation')
            }}</label>
            <input
                id="password_confirmation"
                v-model="form.password_confirmation"
                type="password"
                class="form-control"
                required
            />
        </div>

        <button type="submit" class="btn btn-primary w-100" :disabled="form.processing">
            {{ t('auth.resetPassword.submit') }}
        </button>
    </form>
</template>
