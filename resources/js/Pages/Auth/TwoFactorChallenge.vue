<script setup lang="ts">
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { useForm } from '@inertiajs/vue3';

defineOptions({ layout: GuestLayout });

const form = useForm({
    code: '',
});

function submit() {
    form.post('/two-factor-challenge');
}
</script>

<template>
    <h1 class="h4 mb-3">Two-factor authentication</h1>
    <p class="text-muted">Enter the code from your authenticator app, or one of your recovery codes.</p>

    <form @submit.prevent="submit">
        <div class="mb-3">
            <label for="code" class="form-label">Code</label>
            <input
                id="code"
                v-model="form.code"
                type="text"
                inputmode="numeric"
                autocomplete="one-time-code"
                class="form-control"
                :class="{ 'is-invalid': form.errors.code }"
                required
                autofocus
            />
            <div v-if="form.errors.code" class="invalid-feedback">{{ form.errors.code }}</div>
        </div>

        <button type="submit" class="btn btn-primary w-100" :disabled="form.processing">Verify</button>
    </form>
</template>
