<script setup lang="ts">
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Link, useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

defineOptions({ layout: GuestLayout });

const { t } = useI18n();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

function submit() {
    form.post('/login', {
        onFinish: () => form.reset('password'),
    });
}
</script>

<template>
    <h1 class="h4 mb-4">{{ t('auth.login.title') }}</h1>

    <form @submit.prevent="submit">
        <div class="mb-3">
            <label for="email" class="form-label">{{ t('auth.login.email') }}</label>
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
            <label for="password" class="form-label">{{ t('auth.login.password') }}</label>
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

        <div class="mb-3 form-check">
            <input id="remember" v-model="form.remember" type="checkbox" class="form-check-input" />
            <label for="remember" class="form-check-label">{{ t('auth.login.rememberMe') }}</label>
        </div>

        <button type="submit" class="btn btn-primary w-100" :disabled="form.processing">
            {{ t('auth.login.submit') }}
        </button>

        <div class="text-center mt-3">
            <Link href="/forgot-password" class="small">{{ t('auth.login.forgotPassword') }}</Link>
        </div>
    </form>
</template>
