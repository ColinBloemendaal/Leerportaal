<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import type { ExportRow } from '@/types/exports';

defineProps<{
    exports: { data: ExportRow[] };
}>();

// AppLayout, not AdminLayout/ResellerAdminLayout/KlantAdminLayout: this
// page is reachable by any authenticated staff member regardless of
// which admin area they requested an export from, so it has no single
// "right" role-specific sidebar to force.
defineOptions({ layout: AppLayout });
</script>

<template>
    <h1 class="h4 mb-4">My exports</h1>

    <table class="table">
        <thead>
            <tr>
                <th scope="col">Resource</th>
                <th scope="col">Status</th>
                <th scope="col">Requested</th>
                <th scope="col">Expires</th>
                <th scope="col"></th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="row in exports.data" :key="row.id">
                <td>{{ row.resource_type }}</td>
                <td>
                    {{ row.status }}
                    <span v-if="row.status === 'failed' && row.failure_reason" class="text-danger small">
                        ({{ row.failure_reason }})
                    </span>
                </td>
                <td>{{ row.created_at }}</td>
                <td>{{ row.expires_at ?? '—' }}</td>
                <td>
                    <a v-if="row.download_url" :href="row.download_url" class="btn btn-sm btn-outline-primary">
                        Download
                    </a>
                </td>
            </tr>
            <tr v-if="exports.data.length === 0">
                <td colspan="5" class="text-muted">No exports requested yet.</td>
            </tr>
        </tbody>
    </table>
</template>
