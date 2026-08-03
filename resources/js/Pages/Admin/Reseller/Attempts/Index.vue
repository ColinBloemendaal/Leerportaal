<script setup lang="ts">
import { useExportRequest } from '@/Composables/useExportRequest';
import { useIndexFilters } from '@/Composables/useIndexFilters';
import ResellerAdminLayout from '@/Layouts/ResellerAdminLayout.vue';
import type { FilterQuery, PaginatedCollection } from '@/types/filtering';
import { useI18n } from 'vue-i18n';

interface AttemptRow {
    id: number;
    cursist_name: string | null;
    quiz_id: number | null;
    quiz_type: string | null;
    attempt_number: number;
    score: number | null;
    max_score: number | null;
    passed: boolean | null;
    started_at: string;
    submitted_at: string | null;
}

defineOptions({ layout: ResellerAdminLayout });

const props = defineProps<{
    attempts: PaginatedCollection<AttemptRow>;
    query: FilterQuery;
}>();

const { sort, direction, filters, sortBy } = useIndexFilters('/admin/reseller/attempts', props.query);
const { requestExport } = useExportRequest('attempts', props.query);
const { t } = useI18n();

function passedLabel(passed: boolean | null): string {
    if (passed === null) return t('admin.reseller.attempts.pending');
    return passed ? t('admin.reseller.attempts.passed') : t('admin.reseller.attempts.failed');
}
</script>

<template>
    <h1 class="h4 mb-4">{{ t('admin.reseller.attempts.title') }}</h1>

    <div class="row g-2 mb-3 align-items-center">
        <div class="col-auto">
            <select v-model="filters.passed" class="form-select">
                <option value="">{{ t('admin.reseller.attempts.allResults') }}</option>
                <option value="1">{{ t('admin.reseller.attempts.passed') }}</option>
                <option value="0">{{ t('admin.reseller.attempts.failed') }}</option>
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
                <th scope="col">{{ t('admin.reseller.attempts.cursist') }}</th>
                <th scope="col">{{ t('admin.reseller.attempts.quiz') }}</th>
                <th scope="col">{{ t('admin.reseller.attempts.attemptNumber') }}</th>
                <th scope="col" role="button" @click="sortBy('score')">
                    {{ t('admin.reseller.attempts.score') }}
                    <span v-if="sort === 'score'">{{ direction === 'asc' ? '▲' : '▼' }}</span>
                </th>
                <th scope="col">{{ t('admin.reseller.attempts.result') }}</th>
                <th scope="col" role="button" @click="sortBy('started_at')">
                    {{ t('admin.reseller.attempts.started') }}
                    <span v-if="sort === 'started_at'">{{ direction === 'asc' ? '▲' : '▼' }}</span>
                </th>
                <th scope="col" role="button" @click="sortBy('submitted_at')">
                    {{ t('admin.reseller.attempts.submitted') }}
                    <span v-if="sort === 'submitted_at'">{{ direction === 'asc' ? '▲' : '▼' }}</span>
                </th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="attempt in attempts.data" :key="attempt.id">
                <td>{{ attempt.cursist_name ?? '—' }}</td>
                <td>{{ attempt.quiz_type ?? '—' }}</td>
                <td>{{ attempt.attempt_number }}</td>
                <td>{{ attempt.score ?? '—' }} / {{ attempt.max_score ?? '—' }}</td>
                <td>{{ passedLabel(attempt.passed) }}</td>
                <td>{{ attempt.started_at }}</td>
                <td>{{ attempt.submitted_at ?? '—' }}</td>
            </tr>
            <tr v-if="attempts.data.length === 0">
                <td colspan="7" class="text-muted">{{ t('common.noResultsFiltered') }}</td>
            </tr>
        </tbody>
    </table>

    <div v-if="attempts.meta.last_page > 1" class="text-muted small">
        {{
            t('common.pagination', {
                current: attempts.meta.current_page,
                last: attempts.meta.last_page,
                total: attempts.meta.total,
            })
        }}
    </div>
</template>
