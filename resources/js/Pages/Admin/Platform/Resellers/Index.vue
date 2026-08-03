<script setup lang="ts">
import { useExportRequest } from '@/Composables/useExportRequest';
import { useIndexFilters } from '@/Composables/useIndexFilters';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import type { FilterQuery, PaginatedCollection } from '@/types/filtering';
import { useI18n } from 'vue-i18n';

interface ResellerRow {
    id: number;
    name: string;
    slug: string;
    status: string;
    created_at: string;
}

defineOptions({ layout: AdminLayout });

const props = defineProps<{
    resellers: PaginatedCollection<ResellerRow>;
    query: FilterQuery;
}>();

const { search, sort, direction, filters, sortBy } = useIndexFilters('/admin/platform/resellers', props.query);
const { requestExport } = useExportRequest('resellers', props.query);
const { t } = useI18n();
</script>

<template>
    <h1 class="h4 mb-4">{{ t('admin.platform.resellers.title') }}</h1>

    <div class="row g-2 mb-3 align-items-center">
        <div class="col-auto">
            <input
                v-model="search"
                type="search"
                class="form-control"
                :placeholder="t('admin.platform.resellers.searchPlaceholder')"
            />
        </div>
        <div class="col-auto">
            <select v-model="filters.status" class="form-select">
                <option value="">{{ t('admin.platform.resellers.allStatuses') }}</option>
                <option value="active">{{ t('admin.platform.resellers.active') }}</option>
                <option value="suspended">{{ t('admin.platform.resellers.suspended') }}</option>
            </select>
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
                    {{ t('admin.platform.resellers.name') }}
                    <span v-if="sort === 'name'">{{ direction === 'asc' ? '▲' : '▼' }}</span>
                </th>
                <th scope="col">{{ t('admin.platform.resellers.slug') }}</th>
                <th scope="col" role="button" @click="sortBy('status')">
                    {{ t('admin.platform.resellers.status') }}
                    <span v-if="sort === 'status'">{{ direction === 'asc' ? '▲' : '▼' }}</span>
                </th>
                <th scope="col" role="button" @click="sortBy('created_at')">
                    {{ t('admin.platform.resellers.created') }}
                    <span v-if="sort === 'created_at'">{{ direction === 'asc' ? '▲' : '▼' }}</span>
                </th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="reseller in resellers.data" :key="reseller.id">
                <td>{{ reseller.name }}</td>
                <td>{{ reseller.slug }}</td>
                <td>{{ reseller.status }}</td>
                <td>{{ reseller.created_at }}</td>
            </tr>
            <tr v-if="resellers.data.length === 0">
                <td colspan="4" class="text-muted">{{ t('common.noResultsFiltered') }}</td>
            </tr>
        </tbody>
    </table>

    <div v-if="resellers.meta.last_page > 1" class="text-muted small">
        {{
            t('common.pagination', {
                current: resellers.meta.current_page,
                last: resellers.meta.last_page,
                total: resellers.meta.total,
            })
        }}
    </div>
</template>
