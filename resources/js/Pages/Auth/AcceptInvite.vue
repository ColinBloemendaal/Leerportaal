<script setup lang="ts">
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { useForm } from '@inertiajs/vue3';

defineOptions({ layout: GuestLayout });

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
    <h1 class="h4 mb-1">Welcome, {{ name }}</h1>
    <p class="text-muted mb-4">Set a password for {{ email }} to finish setting up your account.</p>

    <form @submit.prevent="submit">
        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
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
            <label for="password_confirmation" class="form-label">Confirm password</label>
            <input
                id="password_confirmation"
                v-model="form.password_confirmation"
                type="password"
                class="form-control"
                required
            />
        </div>

        <button type="submit" class="btn btn-primary w-100" :disabled="form.processing">Activate account</button>
    </form>
</template>
