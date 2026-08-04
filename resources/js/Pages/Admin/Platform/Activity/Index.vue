<script setup lang="ts">
import { useExportRequest } from '@/Composables/useExportRequest';
import { useIndexFilters } from '@/Composables/useIndexFilters';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import type { FilterQuery, PaginatedCollection } from '@/types/filtering';
import { useI18n } from 'vue-i18n';

interface ActivityRow {
    id: number;
    log_name: string | null;
    description: string;
    event: string | null;
    subject_type: string | null;
    subject_id: number | null;
    causer_name: string | null;
    created_at: string;
}

defineOptions({ layout: AdminLayout });

const props = defineProps<{
    activity: PaginatedCollection<ActivityRow>;
    query: FilterQuery;
}>();

const { search, sort, direction, filters, sortBy } = useIndexFilters('/admin/platform/activity', props.query);
const { requestExport } = useExportRequest('activity', props.query);
const { t } = useI18n();
</script>

<template>
    <h1 class="h4 mb-4">{{ t('admin.platform.activity.title') }}</h1>

    <div class="row g-2 mb-3 align-items-center">
        <div class="col-auto">
            <input
                v-model="search"
                type="search"
                class="form-control"
                :placeholder="t('admin.platform.activity.searchPlaceholder')"
            />
        </div>
        <div class="col-auto">
            <input
                v-model="filters.causer_id"
                type="number"
                class="form-control"
                :placeholder="t('admin.platform.activity.actorPlaceholder')"
            />
        </div>
        <div class="col-auto">
            <input
                v-model="filters.subject_type"
                class="form-control"
                :placeholder="t('admin.platform.activity.subjectTypePlaceholder')"
            />
        </div>
        <div class="col-auto">
            <input
                v-model="filters.event"
                class="form-control"
                :placeholder="t('admin.platform.activity.actionPlaceholder')"
            />
        </div>
        <div class="col-auto">
            <input
                v-model="filters.reseller_id"
                type="number"
                class="form-control"
                :placeholder="t('admin.platform.activity.resellerPlaceholder')"
            />
        </div>
        <div class="col-auto">
            <input
                v-model="filters.date_from"
                type="date"
                class="form-control"
                :aria-label="t('admin.platform.activity.fromDate')"
            />
        </div>
        <div class="col-auto">
            <input
                v-model="filters.date_to"
                type="date"
                class="form-control"
                :aria-label="t('admin.platform.activity.toDate')"
            />
        </div>
        <div class="col-auto ms-auto">
            <button type="button" class="btn btn-sm btn-outline-secondary" @click="requestExport('csv')">
                {{ t('common.exportCsv') }}
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary" @click="requestExport('xlsx')">
                {{ t('common.exportXlsx') }}
            </button>
        </div>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th scope="col" role="button" @click="sortBy('created_at')">
                    {{ t('admin.platform.activity.when') }}
                    <span v-if="sort === 'created_at'">{{ direction === 'asc' ? '▲' : '▼' }}</span>
                </th>
                <th scope="col">{{ t('admin.platform.activity.causer') }}</th>
                <th scope="col">{{ t('admin.platform.activity.subject') }}</th>
                <th scope="col" role="button" @click="sortBy('event')">
                    {{ t('admin.platform.activity.event') }}
                    <span v-if="sort === 'event'">{{ direction === 'asc' ? '▲' : '▼' }}</span>
                </th>
                <th scope="col">{{ t('admin.platform.activity.description') }}</th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="entry in activity.data" :key="entry.id">
                <td>{{ entry.created_at }}</td>
                <td>{{ entry.causer_name ?? '—' }}</td>
                <td>
                    {{ entry.subject_type ?? '—' }}<span v-if="entry.subject_id">#{{ entry.subject_id }}</span>
                </td>
                <td>{{ entry.event ?? '—' }}</td>
                <td>{{ entry.description }}</td>
            </tr>
            <tr v-if="activity.data.length === 0">
                <td colspan="5" class="text-muted">{{ t('common.noResultsFiltered') }}</td>
            </tr>
        </tbody>
    </table>

    <div v-if="activity.meta.last_page > 1" class="text-muted small">
        {{
            t('common.pagination', {
                current: activity.meta.current_page,
                last: activity.meta.last_page,
                total: activity.meta.total,
            })
        }}
    </div>
</template>
