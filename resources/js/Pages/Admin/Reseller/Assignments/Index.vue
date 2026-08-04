<script setup lang="ts">
import { useExportRequest } from '@/Composables/useExportRequest';
import { useIndexFilters } from '@/Composables/useIndexFilters';
import ResellerAdminLayout from '@/Layouts/ResellerAdminLayout.vue';
import type { FilterQuery, PaginatedCollection } from '@/types/filtering';
import { Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

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
const { t } = useI18n();

function formatMoney(cents: number): string {
    return new Intl.NumberFormat('nl-NL', { style: 'currency', currency: 'EUR' }).format(cents / 100);
}
</script>

<template>
    <div class="d-flex justify-content-between align-items-start mb-4">
        <h1 class="h4 mb-0">{{ t('admin.reseller.assignments.title') }}</h1>
        <Link href="/admin/reseller/assignments/create" class="btn btn-primary btn-sm">
            {{ t('admin.reseller.assignments.assignCourse') }}
        </Link>
    </div>

    <div class="row g-2 mb-3 align-items-center">
        <div class="col-auto">
            <select v-model="filters.billing_state" class="form-select">
                <option value="">{{ t('admin.reseller.assignments.allBillingStates') }}</option>
                <option value="pending">{{ t('admin.reseller.assignments.pending') }}</option>
                <option value="billed">{{ t('admin.reseller.assignments.billed') }}</option>
                <option value="waived">{{ t('admin.reseller.assignments.waived') }}</option>
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
                <th scope="col">{{ t('admin.reseller.assignments.cursist') }}</th>
                <th scope="col">{{ t('admin.reseller.assignments.course') }}</th>
                <th scope="col" role="button" @click="sortBy('billing_state')">
                    {{ t('admin.reseller.assignments.billingState') }}
                    <span v-if="sort === 'billing_state'">{{ direction === 'asc' ? '▲' : '▼' }}</span>
                </th>
                <th scope="col">{{ t('admin.reseller.assignments.price') }}</th>
                <th scope="col" role="button" @click="sortBy('assigned_at')">
                    {{ t('admin.reseller.assignments.assigned') }}
                    <span v-if="sort === 'assigned_at'">{{ direction === 'asc' ? '▲' : '▼' }}</span>
                </th>
                <th scope="col" role="button" @click="sortBy('deadline_at')">
                    {{ t('admin.reseller.assignments.deadline') }}
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
                <td colspan="6" class="text-muted">{{ t('common.noResultsFiltered') }}</td>
            </tr>
        </tbody>
    </table>

    <div v-if="assignments.meta.last_page > 1" class="text-muted small">
        {{
            t('common.pagination', {
                current: assignments.meta.current_page,
                last: assignments.meta.last_page,
                total: assignments.meta.total,
            })
        }}
    </div>
</template>
