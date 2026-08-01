<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';

defineOptions({ layout: AppLayout });

defineProps<{
    types: { type: string; label: string; overridden: boolean }[];
}>();
</script>

<template>
    <h1 class="h4 mb-4">Email templates</h1>

    <p class="text-muted">
        Override the subject and body of these system emails. Anything not overridden uses the default text.
    </p>

    <table class="table">
        <thead>
            <tr>
                <th scope="col">Notification</th>
                <th scope="col">Status</th>
                <th scope="col"></th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="type in types" :key="type.type">
                <td>{{ type.label }}</td>
                <td>
                    <span :class="type.overridden ? 'badge text-bg-primary' : 'badge text-bg-secondary'">
                        {{ type.overridden ? 'Customized' : 'Default' }}
                    </span>
                </td>
                <td>
                    <Link :href="`/settings/email-templates/${type.type}`" class="btn btn-sm btn-outline-primary">
                        Edit
                    </Link>
                </td>
            </tr>
        </tbody>
    </table>
</template>
