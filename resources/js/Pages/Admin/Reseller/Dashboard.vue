<script setup lang="ts">
import ResellerAdminLayout from '@/Layouts/ResellerAdminLayout.vue';
import type { ResellerDashboardStats } from '@/types/admin';
import { useI18n } from 'vue-i18n';

defineOptions({ layout: ResellerAdminLayout });

defineProps<{ stats: ResellerDashboardStats }>();

const { t } = useI18n();

function formatMoney(cents: number): string {
    return new Intl.NumberFormat('nl-NL', { style: 'currency', currency: 'EUR' }).format(cents / 100);
}
</script>

<template>
    <h1 class="h4 mb-4">{{ t('admin.reseller.dashboard.title') }}</h1>

    <div class="row g-3">
        <div class="col-sm-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase">{{ t('admin.reseller.dashboard.klanten') }}</div>
                    <div class="fs-3 fw-semibold">{{ stats.klantCount }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase">{{ t('admin.reseller.dashboard.cursisten') }}</div>
                    <div class="fs-3 fw-semibold">{{ stats.cursistCount }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase">{{ t('admin.reseller.dashboard.assignments') }}</div>
                    <div class="fs-3 fw-semibold">{{ stats.assignmentCount }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-sm-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase">{{ t('admin.reseller.dashboard.billedSpend') }}</div>
                    <div class="fs-3 fw-semibold">{{ formatMoney(stats.billedSpend.cents) }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase">{{ t('admin.reseller.dashboard.pendingSpend') }}</div>
                    <div class="fs-3 fw-semibold">{{ formatMoney(stats.pendingSpend.cents) }}</div>
                </div>
            </div>
        </div>
    </div>
</template>
