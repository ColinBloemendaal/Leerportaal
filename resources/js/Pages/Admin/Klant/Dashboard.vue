<script setup lang="ts">
import KlantAdminLayout from '@/Layouts/KlantAdminLayout.vue';
import type { KlantDashboardStats } from '@/types/admin';

defineOptions({ layout: KlantAdminLayout });

defineProps<{ stats: KlantDashboardStats }>();
</script>

<template>
    <h1 class="h4 mb-4">Klant dashboard</h1>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase">Cursisten</div>
                    <div class="fs-3 fw-semibold">{{ stats.cursistCount }}</div>
                </div>
            </div>
        </div>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th scope="col">Cursist</th>
                <th scope="col">Assigned</th>
                <th scope="col">In progress</th>
                <th scope="col">Completed</th>
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
                <td colspan="4" class="text-muted">No cursisten yet.</td>
            </tr>
        </tbody>
    </table>
</template>
