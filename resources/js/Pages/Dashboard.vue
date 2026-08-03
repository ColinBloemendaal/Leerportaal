<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import type { AssignmentStatus, CursistAssignmentRow } from '@/types/dashboard';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

defineOptions({ layout: AppLayout });

const props = defineProps<{ assignments: CursistAssignmentRow[] }>();
const { t } = useI18n();

function byStatus(status: AssignmentStatus): CursistAssignmentRow[] {
    return props.assignments.filter((assignment) => assignment.status === status);
}

const notStarted = computed(() => byStatus('not_started'));
const inProgress = computed(() => byStatus('in_progress'));
const completed = computed(() => byStatus('completed'));
</script>

<template>
    <h1 class="h4 mb-4">{{ t('dashboard.title') }}</h1>

    <div v-if="assignments.length === 0" class="text-muted">{{ t('dashboard.empty') }}</div>

    <template v-else>
        <section v-if="inProgress.length > 0" class="mb-4">
            <h2 class="h6 text-uppercase text-muted mb-3">{{ t('dashboard.inProgress') }}</h2>
            <div v-for="row in inProgress" :key="row.assignmentId" class="card mb-2">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-semibold">{{ row.courseTitle }}</div>
                        <div v-if="row.deadlineAt" class="small" :class="row.isOverdue ? 'text-danger' : 'text-muted'">
                            {{ row.isOverdue ? t('dashboard.overdue') : t('dashboard.due') }}: {{ row.deadlineAt }}
                        </div>
                    </div>
                    <div class="text-end" style="min-width: 8rem">
                        <div
                            class="progress"
                            role="progressbar"
                            :aria-valuenow="row.progressPercent"
                            aria-valuemin="0"
                            aria-valuemax="100"
                        >
                            <div class="progress-bar" :style="{ width: `${row.progressPercent}%` }"></div>
                        </div>
                        <div class="small text-muted mt-1">{{ row.progressPercent }}%</div>
                    </div>
                </div>
            </div>
        </section>

        <section v-if="notStarted.length > 0" class="mb-4">
            <h2 class="h6 text-uppercase text-muted mb-3">{{ t('dashboard.assigned') }}</h2>
            <div v-for="row in notStarted" :key="row.assignmentId" class="card mb-2">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div class="fw-semibold">{{ row.courseTitle }}</div>
                    <div v-if="row.deadlineAt" class="small" :class="row.isOverdue ? 'text-danger' : 'text-muted'">
                        {{ row.isOverdue ? t('dashboard.overdue') : t('dashboard.due') }}: {{ row.deadlineAt }}
                    </div>
                </div>
            </div>
        </section>

        <section v-if="completed.length > 0">
            <h2 class="h6 text-uppercase text-muted mb-3">{{ t('dashboard.completed') }}</h2>
            <div v-for="row in completed" :key="row.assignmentId" class="card mb-2">
                <div class="card-body"><span class="text-success me-2">&#10003;</span>{{ row.courseTitle }}</div>
            </div>
        </section>
    </template>
</template>
