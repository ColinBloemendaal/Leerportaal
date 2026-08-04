<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import type { ExportRow } from '@/types/exports';
import { useI18n } from 'vue-i18n';

defineProps<{
    exports: { data: ExportRow[] };
}>();

// AppLayout, not AdminLayout/ResellerAdminLayout/KlantAdminLayout: this
// page is reachable by any authenticated staff member regardless of
// which admin area they requested an export from, so it has no single
// "right" role-specific sidebar to force.
defineOptions({ layout: AppLayout });

const { t } = useI18n();
</script>

<template>
    <h1 class="h4 mb-4">{{ t('admin.exports.title') }}</h1>

    <table class="table">
        <thead>
            <tr>
                <th scope="col">{{ t('admin.exports.resource') }}</th>
                <th scope="col">{{ t('admin.exports.format') }}</th>
                <th scope="col">{{ t('admin.exports.status') }}</th>
                <th scope="col">{{ t('admin.exports.requested') }}</th>
                <th scope="col">{{ t('admin.exports.expires') }}</th>
                <th scope="col"></th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="row in exports.data" :key="row.id">
                <td>{{ row.resource_type }}</td>
                <td>{{ row.format.toUpperCase() }}</td>
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
                        {{ t('admin.exports.download') }}
                    </a>
                </td>
            </tr>
            <tr v-if="exports.data.length === 0">
                <td colspan="6" class="text-muted">{{ t('admin.exports.noExports') }}</td>
            </tr>
        </tbody>
    </table>
</template>
