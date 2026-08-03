<script setup lang="ts">
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

defineOptions({ layout: GuestLayout });

const { t } = useI18n();

const props = defineProps<{
    acceptUrl: string;
    name: string;
    email: string;
}>();

const form = useForm({
    password: '',
    password_confirmation: '',
});

function submit() {
    form.post(props.acceptUrl);
}
</script>

<template>
    <h1 class="h4 mb-1">{{ t('auth.acceptInvite.title', { name }) }}</h1>
    <p class="text-muted mb-4">{{ t('auth.acceptInvite.description', { email }) }}</p>

    <form @submit.prevent="submit">
        <div class="mb-3">
            <label for="password" class="form-label">{{ t('auth.acceptInvite.password') }}</label>
            <input
                id="password"
                v-model="form.password"
                type="password"
                class="form-control"
                :class="{ 'is-invalid': form.errors.password }"
                required
                autofocus
            />
            <div v-if="form.errors.password" class="invalid-feedback">{{ form.errors.password }}</div>
        </div>

        <div class="mb-3">
            <label for="password_confirmation" class="form-label">{{
                t('auth.acceptInvite.passwordConfirmation')
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
            {{ t('auth.acceptInvite.submit') }}
        </button>
    </form>
</template>
