<script setup lang="ts">
import { useIndexFilters } from '@/Composables/useIndexFilters';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import type { FilterQuery, PaginatedCollection } from '@/types/filtering';
import { Link } from '@inertiajs/vue3';

interface UserRow {
    id: number;
    name: string;
    email: string;
    reseller_name: string | null;
    platform_role: string | null;
    created_at: string;
}

defineOptions({ layout: AdminLayout });

const props = defineProps<{
    users: PaginatedCollection<UserRow>;
    query: FilterQuery;
}>();

const { search, sort, direction, sortBy } = useIndexFilters('/admin/platform/users', props.query);
</script>

<template>
    <h1 class="h4 mb-4">Users</h1>

    <div class="row g-2 mb-3">
        <div class="col-auto">
            <input v-model="search" type="search" class="form-control" placeholder="Search name or email" />
        </div>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th scope="col" role="button" @click="sortBy('name')">
                    Name
                    <span v-if="sort === 'name'">{{ direction === 'asc' ? '▲' : '▼' }}</span>
                </th>
                <th scope="col" role="button" @click="sortBy('email')">
                    Email
                    <span v-if="sort === 'email'">{{ direction === 'asc' ? '▲' : '▼' }}</span>
                </th>
                <th scope="col">Reseller</th>
                <th scope="col">Platform role</th>
                <th scope="col" role="button" @click="sortBy('created_at')">
                    Created
                    <span v-if="sort === 'created_at'">{{ direction === 'asc' ? '▲' : '▼' }}</span>
                </th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="user in users.data" :key="user.id">
                <td>
                    <Link :href="`/admin/platform/users/${user.id}`">{{ user.name }}</Link>
                </td>
                <td>{{ user.email }}</td>
                <td>{{ user.reseller_name ?? '—' }}</td>
                <td>{{ user.platform_role ?? '—' }}</td>
                <td>{{ user.created_at }}</td>
            </tr>
            <tr v-if="users.data.length === 0">
                <td colspan="5" class="text-muted">No users match these filters.</td>
            </tr>
        </tbody>
    </table>

    <div v-if="users.meta.last_page > 1" class="text-muted small">
        Page {{ users.meta.current_page }} of {{ users.meta.last_page }} ({{ users.meta.total }} total)
    </div>
</template>
