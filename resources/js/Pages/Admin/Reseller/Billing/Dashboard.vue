<script setup lang="ts">
import ResellerAdminLayout from '@/Layouts/ResellerAdminLayout.vue';
import type { ResellerBillingDashboardStats } from '@/types/admin';
import { useI18n } from 'vue-i18n';

defineOptions({ layout: ResellerAdminLayout });

defineProps<{ stats: ResellerBillingDashboardStats }>();

const { t } = useI18n();

function formatMoney(cents: number): string {
    return new Intl.NumberFormat('nl-NL', { style: 'currency', currency: 'EUR' }).format(cents / 100);
}
</script>

<template>
    <h1 class="h4 mb-4">{{ t('admin.reseller.billing.title') }}</h1>

    <div class="card mb-4">
        <div class="card-body">
            <div class="text-muted small text-uppercase">{{ t('admin.reseller.billing.currentPeriod') }}</div>
            <div v-if="stats.currentPeriodStart" class="mb-2">
                {{ stats.currentPeriodStart }} &ndash; {{ stats.currentPeriodEnd }}
            </div>
            <div v-else class="text-muted mb-2">{{ t('admin.reseller.billing.noCurrentPeriod') }}</div>
            <div class="fs-3 fw-semibold">{{ formatMoney(stats.currentPeriodSubtotal.cents) }}</div>
        </div>
    </div>

    <h2 class="h5 mb-3">{{ t('admin.reseller.billing.klantBreakdown') }}</h2>
    <div class="table-responsive mb-4">
        <table class="table">
            <thead>
                <tr>
                    <th>{{ t('admin.reseller.billing.klant') }}</th>
                    <th>{{ t('admin.reseller.billing.subtotal') }}</th>
                </tr>
            </thead>
            <tbody>
                <tr v-if="stats.klantBreakdown.length === 0">
                    <td colspan="2" class="text-muted">{{ t('admin.reseller.billing.noBreakdown') }}</td>
                </tr>
                <tr v-for="row in stats.klantBreakdown" :key="row.klantId ?? 'other'">
                    <td>{{ row.klantName }}</td>
                    <td>{{ formatMoney(row.subtotal.cents) }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <h2 class="h5 mb-3">{{ t('admin.reseller.billing.history') }}</h2>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>{{ t('admin.reseller.billing.period') }}</th>
                    <th>{{ t('admin.reseller.billing.status') }}</th>
                    <th>{{ t('admin.reseller.billing.total') }}</th>
                    <th>{{ t('admin.reseller.billing.issued') }}</th>
                    <th>{{ t('admin.reseller.billing.paid') }}</th>
                </tr>
            </thead>
            <tbody>
                <tr v-if="stats.history.length === 0">
                    <td colspan="5" class="text-muted">{{ t('admin.reseller.billing.noHistory') }}</td>
                </tr>
                <tr v-for="invoice in stats.history" :key="invoice.id">
                    <td>{{ invoice.periodStart }} &ndash; {{ invoice.periodEnd }}</td>
                    <td>{{ invoice.status }}</td>
                    <td>{{ formatMoney(invoice.total.cents) }}</td>
                    <td>{{ invoice.issuedAt ?? '—' }}</td>
                    <td>{{ invoice.paidAt ?? '—' }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</template>
