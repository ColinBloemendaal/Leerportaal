<script setup lang="ts">
import { useExportRequest } from '@/Composables/useExportRequest';
import { useIndexFilters } from '@/Composables/useIndexFilters';
import ResellerAdminLayout from '@/Layouts/ResellerAdminLayout.vue';
import type { FilterQuery, PaginatedCollection } from '@/types/filtering';

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
</script>

<template>
    <h1 class="h4 mb-4">Courses</h1>

    <div class="row g-2 mb-3 align-items-center">
        <div class="col-auto">
            <input v-model="search" type="search" class="form-control" placeholder="Search title" />
        </div>
        <div class="col-auto">
            <select v-model="filters.status" class="form-select">
                <option value="">All statuses</option>
                <option value="draft">Draft</option>
                <option value="in_review">In review</option>
                <option value="published">Published</option>
            </select>
        </div>
        <div class="col-auto ms-auto">
            <button type="button" class="btn btn-sm btn-outline-secondary" @click="requestExport">Export as CSV</button>
        </div>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th scope="col">Title</th>
                <th scope="col">Source</th>
                <th scope="col" role="button" @click="sortBy('status')">
                    Status
                    <span v-if="sort === 'status'">{{ direction === 'asc' ? '▲' : '▼' }}</span>
                </th>
                <th scope="col" role="button" @click="sortBy('created_at')">
                    Created
                    <span v-if="sort === 'created_at'">{{ direction === 'asc' ? '▲' : '▼' }}</span>
                </th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="course in courses.data" :key="course.id">
                <td>{{ course.title }}</td>
                <td>{{ course.is_catalog ? 'Catalog' : 'Own' }}</td>
                <td>{{ course.status }}</td>
                <td>{{ course.created_at }}</td>
            </tr>
            <tr v-if="courses.data.length === 0">
                <td colspan="4" class="text-muted">No courses match these filters.</td>
            </tr>
        </tbody>
    </table>

    <div v-if="courses.meta.last_page > 1" class="text-muted small">
        Page {{ courses.meta.current_page }} of {{ courses.meta.last_page }} ({{ courses.meta.total }} total)
    </div>
</template>
