<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useI18n } from 'vue-i18n';

interface QueueWorkload {
    name: string;
    length: number;
    waitSeconds: number;
    processes: number;
}

interface PlatformHealth {
    queues: QueueWorkload[];
    failedJobCount: number;
    failedJobCountLast24Hours: number;
    storageUsedBytes: number;
}

defineProps<{ health: PlatformHealth }>();

defineOptions({ layout: AdminLayout });

const { t } = useI18n();

function formatBytes(bytes: number): string {
    if (bytes === 0) return '0 B';

    const units = ['B', 'KB', 'MB', 'GB', 'TB'];
    const exponent = Math.min(Math.floor(Math.log(bytes) / Math.log(1024)), units.length - 1);

    return `${(bytes / 1024 ** exponent).toFixed(1)} ${units[exponent]}`;
}
</script>

<template>
    <h1 class="h4 mb-4">{{ t('admin.platform.health.title') }}</h1>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase">{{ t('admin.platform.health.failedJobs') }}</div>
                    <div class="fs-3 fw-semibold">{{ health.failedJobCount }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase">
                        {{ t('admin.platform.health.failedJobsLast24h') }}
                    </div>
                    <div class="fs-3 fw-semibold">{{ health.failedJobCountLast24Hours }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase">{{ t('admin.platform.health.storageUsed') }}</div>
                    <div class="fs-3 fw-semibold">{{ formatBytes(health.storageUsedBytes) }}</div>
                </div>
            </div>
        </div>
    </div>

    <h2 class="h5 mb-3">{{ t('admin.platform.health.queueDepth') }}</h2>

    <table class="table">
        <thead>
            <tr>
                <th scope="col">{{ t('admin.platform.health.queue') }}</th>
                <th scope="col">{{ t('admin.platform.health.pendingJobs') }}</th>
                <th scope="col">{{ t('admin.platform.health.waitTime') }}</th>
                <th scope="col">{{ t('admin.platform.health.workers') }}</th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="queue in health.queues" :key="queue.name">
                <td>{{ queue.name }}</td>
                <td>{{ queue.length }}</td>
                <td>{{ queue.waitSeconds }}s</td>
                <td>{{ queue.processes }}</td>
            </tr>
            <tr v-if="health.queues.length === 0">
                <td colspan="4" class="text-muted">{{ t('admin.platform.health.noQueues') }}</td>
            </tr>
        </tbody>
    </table>
</template>
