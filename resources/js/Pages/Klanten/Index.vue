<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import type { PaginatedKlanten } from '@/types/klanten';
import { useForm } from '@inertiajs/vue3';

defineOptions({ layout: AppLayout });

defineProps<{
    klanten: PaginatedKlanten;
}>();

const form = useForm({
    name: '',
});

function submit() {
    form.post('/klanten', {
        onSuccess: () => form.reset(),
    });
}
</script>

<template>
    <h1 class="h4 mb-4">Klanten</h1>

    <form class="row g-2 mb-4" @submit.prevent="submit">
        <div class="col-auto">
            <input
                v-model="form.name"
                type="text"
                class="form-control"
                :class="{ 'is-invalid': form.errors.name }"
                placeholder="Naam"
                aria-label="Naam"
            />
            <div v-if="form.errors.name" class="invalid-feedback">{{ form.errors.name }}</div>
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-primary" :disabled="form.processing">Toevoegen</button>
        </div>
    </form>

    <table class="table">
        <thead>
            <tr>
                <th scope="col">Naam</th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="klant in klanten.data" :key="klant.id">
                <td>{{ klant.name }}</td>
            </tr>
            <tr v-if="klanten.data.length === 0">
                <td class="text-muted">Nog geen klanten.</td>
            </tr>
        </tbody>
    </table>
</template>
