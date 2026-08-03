<script setup lang="ts">
import { useIndexFilters } from '@/Composables/useIndexFilters';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import type { FilterQuery, PaginatedCollection } from '@/types/filtering';

interface ResellerRow {
    id: number;
    name: string;
    slug: string;
    status: string;
    created_at: string;
}

defineOptions({ layout: AdminLayout });

const props = defineProps<{
    resellers: PaginatedCollection<ResellerRow>;
    query: FilterQuery;
}>();

const { search, sort, direction, filters, sortBy } = useIndexFilters('/admin/platform/resellers', props.query);
</script>

<template>
    <h1 class="h4 mb-4">Resellers</h1>

    <div class="row g-2 mb-3">
        <div class="col-auto">
            <input v-model="search" type="search" class="form-control" placeholder="Search name or slug" />
        </div>
        <div class="col-auto">
            <select v-model="filters.status" class="form-select">
                <option value="">All statuses</option>
                <option value="active">Active</option>
                <option value="suspended">Suspended</option>
            </select>
        </div>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th scope="col" role="button" @click="sortBy('name')">
                    Name
                    <span v-if="sort === 'name'">{{ direction === 'asc' ? '▲' : '▼' }}</span>
                </th>
                <th scope="col">Slug</th>
                <th scope="col" role="button" @click="sortBy('status')">
                    Status
                    <span v-if="sort === 'status'">{{ direction === 'asc' ? '▲' : '▼' }}</span>
                </th>
                <th scope="col" role="button" @click="sortBy('created_at')">
                    Created
                    <span v-if="sort === 'created_at'">{{ direction === 'asc' ? '▲' : '▼' }}</span>
                </th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="reseller in resellers.data" :key="reseller.id">
                <td>{{ reseller.name }}</td>
                <td>{{ reseller.slug }}</td>
                <td>{{ reseller.status }}</td>
                <td>{{ reseller.created_at }}</td>
            </tr>
            <tr v-if="resellers.data.length === 0">
                <td colspan="4" class="text-muted">No resellers match these filters.</td>
            </tr>
        </tbody>
    </table>

    <div v-if="resellers.meta.last_page > 1" class="text-muted small">
        Page {{ resellers.meta.current_page }} of {{ resellers.meta.last_page }} ({{ resellers.meta.total }} total)
    </div>
</template>
