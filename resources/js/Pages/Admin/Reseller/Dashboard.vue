<script setup lang="ts">
import ResellerAdminLayout from '@/Layouts/ResellerAdminLayout.vue';
import type { ResellerDashboardStats } from '@/types/admin';

defineOptions({ layout: ResellerAdminLayout });

defineProps<{ stats: ResellerDashboardStats }>();

function formatMoney(cents: number): string {
    return new Intl.NumberFormat('nl-NL', { style: 'currency', currency: 'EUR' }).format(cents / 100);
}
</script>

<template>
    <h1 class="h4 mb-4">Reseller dashboard</h1>

    <div class="row g-3">
        <div class="col-sm-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase">Klanten</div>
                    <div class="fs-3 fw-semibold">{{ stats.klantCount }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase">Cursisten</div>
                    <div class="fs-3 fw-semibold">{{ stats.cursistCount }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase">Assignments</div>
                    <div class="fs-3 fw-semibold">{{ stats.assignmentCount }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-sm-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase">Billed spend</div>
                    <div class="fs-3 fw-semibold">{{ formatMoney(stats.billedSpend.cents) }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase">Pending spend</div>
                    <div class="fs-3 fw-semibold">{{ formatMoney(stats.pendingSpend.cents) }}</div>
                </div>
            </div>
        </div>
    </div>
</template>
