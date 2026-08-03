<script setup lang="ts">
import KlantAdminLayout from '@/Layouts/KlantAdminLayout.vue';
import type { KlantDashboardStats } from '@/types/admin';
import { useI18n } from 'vue-i18n';

defineOptions({ layout: KlantAdminLayout });

defineProps<{ stats: KlantDashboardStats }>();

const { t } = useI18n();
</script>

<template>
    <h1 class="h4 mb-4">{{ t('admin.klant.dashboard.title') }}</h1>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase">{{ t('admin.klant.dashboard.cursisten') }}</div>
                    <div class="fs-3 fw-semibold">{{ stats.cursistCount }}</div>
                </div>
            </div>
        </div>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th scope="col">{{ t('admin.klant.dashboard.cursist') }}</th>
                <th scope="col">{{ t('admin.klant.dashboard.assigned') }}</th>
                <th scope="col">{{ t('admin.klant.dashboard.inProgress') }}</th>
                <th scope="col">{{ t('admin.klant.dashboard.completed') }}</th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="cursist in stats.cursisten" :key="cursist.userId">
                <td>{{ cursist.name }}</td>
                <td>{{ cursist.assignedCount }}</td>
                <td>{{ cursist.inProgressCount }}</td>
                <td>{{ cursist.completedCount }}</td>
            </tr>
            <tr v-if="stats.cursisten.length === 0">
                <td colspan="4" class="text-muted">{{ t('admin.klant.dashboard.noCursisten') }}</td>
            </tr>
        </tbody>
    </table>
</template>
