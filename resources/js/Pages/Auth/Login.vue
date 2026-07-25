<script setup lang="ts">
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Link, useForm } from '@inertiajs/vue3';

defineOptions({ layout: GuestLayout });

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
    <h1 class="h4 mb-4">Sign in</h1>

    <form @submit.prevent="submit">
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
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
            <label for="password" class="form-label">Password</label>
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
            <label for="remember" class="form-check-label">Remember me</label>
        </div>

        <button type="submit" class="btn btn-primary w-100" :disabled="form.processing">Sign in</button>

        <div class="text-center mt-3">
            <Link href="/forgot-password" class="small">Forgot your password?</Link>
        </div>
    </form>
</template>
