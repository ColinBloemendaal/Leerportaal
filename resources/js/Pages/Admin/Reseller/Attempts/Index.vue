<script setup lang="ts">
import { useExportRequest } from '@/Composables/useExportRequest';
import { useIndexFilters } from '@/Composables/useIndexFilters';
import ResellerAdminLayout from '@/Layouts/ResellerAdminLayout.vue';
import type { FilterQuery, PaginatedCollection } from '@/types/filtering';

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

function passedLabel(passed: boolean | null): string {
    if (passed === null) return 'Pending';
    return passed ? 'Passed' : 'Failed';
}
</script>

<template>
    <h1 class="h4 mb-4">Quiz attempts</h1>

    <div class="row g-2 mb-3 align-items-center">
        <div class="col-auto">
            <select v-model="filters.passed" class="form-select">
                <option value="">All results</option>
                <option value="1">Passed</option>
                <option value="0">Failed</option>
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
                <th scope="col">Quiz</th>
                <th scope="col">Attempt #</th>
                <th scope="col" role="button" @click="sortBy('score')">
                    Score
                    <span v-if="sort === 'score'">{{ direction === 'asc' ? '▲' : '▼' }}</span>
                </th>
                <th scope="col">Result</th>
                <th scope="col" role="button" @click="sortBy('started_at')">
                    Started
                    <span v-if="sort === 'started_at'">{{ direction === 'asc' ? '▲' : '▼' }}</span>
                </th>
                <th scope="col" role="button" @click="sortBy('submitted_at')">
                    Submitted
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
                <td colspan="7" class="text-muted">No attempts match these filters.</td>
            </tr>
        </tbody>
    </table>

    <div v-if="attempts.meta.last_page > 1" class="text-muted small">
        Page {{ attempts.meta.current_page }} of {{ attempts.meta.last_page }} ({{ attempts.meta.total }} total)
    </div>
</template>
