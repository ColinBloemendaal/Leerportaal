<script setup lang="ts">
import { useExportRequest } from '@/Composables/useExportRequest';
import { useIndexFilters } from '@/Composables/useIndexFilters';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import type { FilterQuery, PaginatedCollection } from '@/types/filtering';

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
</script>

<template>
    <h1 class="h4 mb-4">Activity log</h1>

    <div class="row g-2 mb-3 align-items-center">
        <div class="col-auto">
            <input v-model="search" type="search" class="form-control" placeholder="Search description" />
        </div>
        <div class="col-auto">
            <input v-model="filters.causer_id" type="number" class="form-control" placeholder="Actor (user id)" />
        </div>
        <div class="col-auto">
            <input
                v-model="filters.subject_type"
                class="form-control"
                placeholder="Subject type (e.g. App\Models\Course)"
            />
        </div>
        <div class="col-auto">
            <input v-model="filters.event" class="form-control" placeholder="Action (created/updated/deleted)" />
        </div>
        <div class="col-auto">
            <input v-model="filters.reseller_id" type="number" class="form-control" placeholder="Reseller id" />
        </div>
        <div class="col-auto">
            <input v-model="filters.date_from" type="date" class="form-control" aria-label="From date" />
        </div>
        <div class="col-auto">
            <input v-model="filters.date_to" type="date" class="form-control" aria-label="To date" />
        </div>
        <div class="col-auto ms-auto">
            <button type="button" class="btn btn-sm btn-outline-secondary" @click="requestExport">Export as CSV</button>
        </div>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th scope="col" role="button" @click="sortBy('created_at')">
                    When
                    <span v-if="sort === 'created_at'">{{ direction === 'asc' ? '▲' : '▼' }}</span>
                </th>
                <th scope="col">Causer</th>
                <th scope="col">Subject</th>
                <th scope="col" role="button" @click="sortBy('event')">
                    Event
                    <span v-if="sort === 'event'">{{ direction === 'asc' ? '▲' : '▼' }}</span>
                </th>
                <th scope="col">Description</th>
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
                <td colspan="5" class="text-muted">No activity matches these filters.</td>
            </tr>
        </tbody>
    </table>

    <div v-if="activity.meta.last_page > 1" class="text-muted small">
        Page {{ activity.meta.current_page }} of {{ activity.meta.last_page }} ({{ activity.meta.total }} total)
    </div>
</template>
