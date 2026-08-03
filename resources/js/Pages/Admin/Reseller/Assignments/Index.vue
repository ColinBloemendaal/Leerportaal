<script setup lang="ts">
import { useExportRequest } from '@/Composables/useExportRequest';
import { useIndexFilters } from '@/Composables/useIndexFilters';
import ResellerAdminLayout from '@/Layouts/ResellerAdminLayout.vue';
import type { FilterQuery, PaginatedCollection } from '@/types/filtering';

interface AssignmentRow {
    id: number;
    cursist_name: string | null;
    course_title: string | null;
    billing_state: string;
    price_cents: number;
    assigned_at: string;
    deadline_at: string | null;
}

defineOptions({ layout: ResellerAdminLayout });

const props = defineProps<{
    assignments: PaginatedCollection<AssignmentRow>;
    query: FilterQuery;
}>();

const { sort, direction, filters, sortBy } = useIndexFilters('/admin/reseller/assignments', props.query);
const { requestExport } = useExportRequest('assignments', props.query);

function formatMoney(cents: number): string {
    return new Intl.NumberFormat('nl-NL', { style: 'currency', currency: 'EUR' }).format(cents / 100);
}
</script>

<template>
    <h1 class="h4 mb-4">Assignments</h1>

    <div class="row g-2 mb-3 align-items-center">
        <div class="col-auto">
            <select v-model="filters.billing_state" class="form-select">
                <option value="">All billing states</option>
                <option value="pending">Pending</option>
                <option value="billed">Billed</option>
                <option value="waived">Waived</option>
            </select>
        </div>
        <div class="col-auto ms-auto">
            <button type="button" class="btn btn-sm btn-outline-secondary" @click="requestExport">Export as CSV</button>
        </div>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th scope="col">Cursist</th>
                <th scope="col">Course</th>
                <th scope="col" role="button" @click="sortBy('billing_state')">
                    Billing state
                    <span v-if="sort === 'billing_state'">{{ direction === 'asc' ? '▲' : '▼' }}</span>
                </th>
                <th scope="col">Price</th>
                <th scope="col" role="button" @click="sortBy('assigned_at')">
                    Assigned
                    <span v-if="sort === 'assigned_at'">{{ direction === 'asc' ? '▲' : '▼' }}</span>
                </th>
                <th scope="col" role="button" @click="sortBy('deadline_at')">
                    Deadline
                    <span v-if="sort === 'deadline_at'">{{ direction === 'asc' ? '▲' : '▼' }}</span>
                </th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="assignment in assignments.data" :key="assignment.id">
                <td>{{ assignment.cursist_name ?? '—' }}</td>
                <td>{{ assignment.course_title ?? '—' }}</td>
                <td>{{ assignment.billing_state }}</td>
                <td>{{ formatMoney(assignment.price_cents) }}</td>
                <td>{{ assignment.assigned_at }}</td>
                <td>{{ assignment.deadline_at ?? '—' }}</td>
            </tr>
            <tr v-if="assignments.data.length === 0">
                <td colspan="6" class="text-muted">No assignments match these filters.</td>
            </tr>
        </tbody>
    </table>

    <div v-if="assignments.meta.last_page > 1" class="text-muted small">
        Page {{ assignments.meta.current_page }} of {{ assignments.meta.last_page }} ({{ assignments.meta.total }}
        total)
    </div>
</template>
