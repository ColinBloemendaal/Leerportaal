<script setup lang="ts">
import { useExportRequest } from '@/Composables/useExportRequest';
import { useIndexFilters } from '@/Composables/useIndexFilters';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import type { FilterQuery, PaginatedCollection } from '@/types/filtering';
import { Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

interface UserRow {
    id: number;
    name: string;
    email: string;
    reseller_name: string | null;
    platform_role: string | null;
    created_at: string;
}

defineOptions({ layout: AdminLayout });

const props = defineProps<{
    users: PaginatedCollection<UserRow>;
    query: FilterQuery;
}>();

const { search, sort, direction, sortBy } = useIndexFilters('/admin/platform/users', props.query);
const { requestExport } = useExportRequest('users', props.query);
const { t } = useI18n();
</script>

<template>
    <h1 class="h4 mb-4">{{ t('admin.platform.users.title') }}</h1>

    <div class="row g-2 mb-3 align-items-center">
        <div class="col-auto">
            <input
                v-model="search"
                type="search"
                class="form-control"
                :placeholder="t('admin.platform.users.searchPlaceholder')"
            />
        </div>
        <div class="col-auto ms-auto">
            <button type="button" class="btn btn-sm btn-outline-secondary" @click="requestExport">
                {{ t('common.exportCsv') }}
            </button>
        </div>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th scope="col" role="button" @click="sortBy('name')">
                    {{ t('admin.platform.users.name') }}
                    <span v-if="sort === 'name'">{{ direction === 'asc' ? '▲' : '▼' }}</span>
                </th>
                <th scope="col" role="button" @click="sortBy('email')">
                    {{ t('admin.platform.users.email') }}
                    <span v-if="sort === 'email'">{{ direction === 'asc' ? '▲' : '▼' }}</span>
                </th>
                <th scope="col">{{ t('admin.platform.users.reseller') }}</th>
                <th scope="col">{{ t('admin.platform.users.platformRole') }}</th>
                <th scope="col" role="button" @click="sortBy('created_at')">
                    {{ t('admin.platform.users.created') }}
                    <span v-if="sort === 'created_at'">{{ direction === 'asc' ? '▲' : '▼' }}</span>
                </th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="user in users.data" :key="user.id">
                <td>
                    <Link :href="`/admin/platform/users/${user.id}`">{{ user.name }}</Link>
                </td>
                <td>{{ user.email }}</td>
                <td>{{ user.reseller_name ?? '—' }}</td>
                <td>{{ user.platform_role ?? '—' }}</td>
                <td>{{ user.created_at }}</td>
            </tr>
            <tr v-if="users.data.length === 0">
                <td colspan="5" class="text-muted">{{ t('common.noResultsFiltered') }}</td>
            </tr>
        </tbody>
    </table>

    <div v-if="users.meta.last_page > 1" class="text-muted small">
        {{
            t('common.pagination', {
                current: users.meta.current_page,
                last: users.meta.last_page,
                total: users.meta.total,
            })
        }}
    </div>
</template>
