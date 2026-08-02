<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import type { PlatformDashboardStats } from '@/types/admin';
import { computed } from 'vue';

defineOptions({ layout: AdminLayout });

const props = defineProps<{ stats: PlatformDashboardStats }>();

function formatMoney(cents: number): string {
    return new Intl.NumberFormat('nl-NL', { style: 'currency', currency: 'EUR' }).format(cents / 100);
}

function formatBytes(bytes: number): string {
    if (bytes === 0) return '0 B';

    const units = ['B', 'KB', 'MB', 'GB', 'TB'];
    const exponent = Math.min(Math.floor(Math.log(bytes) / Math.log(1024)), units.length - 1);

    return `${(bytes / 1024 ** exponent).toFixed(1)} ${units[exponent]}`;
}

const storagePercent = computed(() => {
    if (props.stats.storageIncludedBytes <= 0) return 0;

    return Math.min(100, Math.round((props.stats.storageUsedBytes / props.stats.storageIncludedBytes) * 100));
});
</script>

<template>
    <h1 class="h4 mb-4">Platform dashboard</h1>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase">Resellers</div>
                    <div class="fs-3 fw-semibold">{{ stats.resellerCount }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase">Users</div>
                    <div class="fs-3 fw-semibold">{{ stats.userCount }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase">Courses</div>
                    <div class="fs-3 fw-semibold">{{ stats.courseCount }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase">Storage used</div>
                    <div class="fs-3 fw-semibold">{{ formatBytes(stats.storageUsedBytes) }}</div>
                    <div
                        class="progress mt-2"
                        role="progressbar"
                        :aria-valuenow="storagePercent"
                        aria-valuemin="0"
                        aria-valuemax="100"
                    >
                        <div class="progress-bar" :style="{ width: `${storagePercent}%` }"></div>
                    </div>
                    <div class="small text-muted mt-1">of {{ formatBytes(stats.storageIncludedBytes) }} included</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-sm-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase">Billed revenue</div>
                    <div class="fs-3 fw-semibold">{{ formatMoney(stats.billedRevenue.cents) }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase">Pending revenue</div>
                    <div class="fs-3 fw-semibold">{{ formatMoney(stats.pendingRevenue.cents) }}</div>
                </div>
            </div>
        </div>
    </div>
</template>
