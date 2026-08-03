<script setup lang="ts">
import { useExportRequest } from '@/Composables/useExportRequest';
import { useIndexFilters } from '@/Composables/useIndexFilters';
import ResellerAdminLayout from '@/Layouts/ResellerAdminLayout.vue';
import type { FilterQuery, PaginatedCollection } from '@/types/filtering';
import { useI18n } from 'vue-i18n';

interface CourseRow {
    id: number;
    title: string;
    status: string;
    is_catalog: boolean;
    created_at: string;
}

defineOptions({ layout: ResellerAdminLayout });

const props = defineProps<{
    courses: PaginatedCollection<CourseRow>;
    query: FilterQuery;
}>();

const { search, sort, direction, filters, sortBy } = useIndexFilters('/admin/reseller/courses', props.query);
const { requestExport } = useExportRequest('courses', props.query);
const { t } = useI18n();
</script>

<template>
    <h1 class="h4 mb-4">{{ t('admin.reseller.courses.title') }}</h1>

    <div class="row g-2 mb-3 align-items-center">
        <div class="col-auto">
            <input
                v-model="search"
                type="search"
                class="form-control"
                :placeholder="t('admin.reseller.courses.searchPlaceholder')"
            />
        </div>
        <div class="col-auto">
            <select v-model="filters.status" class="form-select">
                <option value="">{{ t('admin.reseller.courses.allStatuses') }}</option>
                <option value="draft">{{ t('admin.reseller.courses.draft') }}</option>
                <option value="in_review">{{ t('admin.reseller.courses.inReview') }}</option>
                <option value="published">{{ t('admin.reseller.courses.published') }}</option>
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
                <th scope="col">{{ t('admin.reseller.courses.titleColumn') }}</th>
                <th scope="col">{{ t('admin.reseller.courses.source') }}</th>
                <th scope="col" role="button" @click="sortBy('status')">
                    {{ t('admin.reseller.courses.status') }}
                    <span v-if="sort === 'status'">{{ direction === 'asc' ? '▲' : '▼' }}</span>
                </th>
                <th scope="col" role="button" @click="sortBy('created_at')">
                    {{ t('admin.reseller.courses.created') }}
                    <span v-if="sort === 'created_at'">{{ direction === 'asc' ? '▲' : '▼' }}</span>
                </th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="course in courses.data" :key="course.id">
                <td>{{ course.title }}</td>
                <td>{{ course.is_catalog ? t('admin.reseller.courses.catalog') : t('admin.reseller.courses.own') }}</td>
                <td>{{ course.status }}</td>
                <td>{{ course.created_at }}</td>
            </tr>
            <tr v-if="courses.data.length === 0">
                <td colspan="4" class="text-muted">{{ t('common.noResultsFiltered') }}</td>
            </tr>
        </tbody>
    </table>

    <div v-if="courses.meta.last_page > 1" class="text-muted small">
        {{
            t('common.pagination', {
                current: courses.meta.current_page,
                last: courses.meta.last_page,
                total: courses.meta.total,
            })
        }}
    </div>
</template>
