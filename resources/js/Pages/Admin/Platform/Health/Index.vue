<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';

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

function formatBytes(bytes: number): string {
    if (bytes === 0) return '0 B';

    const units = ['B', 'KB', 'MB', 'GB', 'TB'];
    const exponent = Math.min(Math.floor(Math.log(bytes) / Math.log(1024)), units.length - 1);

    return `${(bytes / 1024 ** exponent).toFixed(1)} ${units[exponent]}`;
}
</script>

<template>
    <h1 class="h4 mb-4">Platform health</h1>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase">Failed jobs</div>
                    <div class="fs-3 fw-semibold">{{ health.failedJobCount }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase">Failed jobs (last 24h)</div>
                    <div class="fs-3 fw-semibold">{{ health.failedJobCountLast24Hours }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase">Storage used</div>
                    <div class="fs-3 fw-semibold">{{ formatBytes(health.storageUsedBytes) }}</div>
                </div>
            </div>
        </div>
    </div>

    <h2 class="h5 mb-3">Queue depth</h2>

    <table class="table">
        <thead>
            <tr>
                <th scope="col">Queue</th>
                <th scope="col">Pending jobs</th>
                <th scope="col">Wait time</th>
                <th scope="col">Workers</th>
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
                <td colspan="4" class="text-muted">No active queues.</td>
            </tr>
        </tbody>
    </table>
</template>
