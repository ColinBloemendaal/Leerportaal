<script setup lang="ts">
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { useForm, usePage } from '@inertiajs/vue3';

defineOptions({ layout: GuestLayout });

const form = useForm({
    email: '',
});

const page = usePage();

function submit() {
    form.post('/forgot-password');
}
</script>

<template>
    <h1 class="h4 mb-4">Forgot your password?</h1>

    <div v-if="page.props.flash.status" class="alert alert-success">{{ page.props.flash.status }}</div>

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

        <button type="submit" class="btn btn-primary w-100" :disabled="form.processing">Send reset link</button>
    </form>
</template>
