<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, useForm } from '@inertiajs/vue3';

interface UserDetail {
    id: number;
    name: string;
    email: string;
    reseller_name: string | null;
    platform_role: string | null;
    created_at: string;
}

interface TimelineEntry {
    id: number;
    log_name: string | null;
    description: string;
    event: string | null;
    subject_type: string | null;
    subject_id: number | null;
    causer_id: number | null;
    causer_name: string | null;
    created_at: string;
}

const props = defineProps<{
    user: { data: UserDetail };
    timeline: { data: TimelineEntry[] };
    canImpersonate: boolean;
}>();

defineOptions({ layout: AdminLayout });

const impersonateForm = useForm({ reason: '' });

function impersonate() {
    impersonateForm.post(`/impersonate/${props.user.data.id}`);
}
</script>

<template>
    <Link href="/admin/platform/users" class="d-inline-block mb-3">&laquo; Back to users</Link>

    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h1 class="h4 mb-1">{{ user.data.name }}</h1>
            <p class="text-muted mb-0">{{ user.data.email }}</p>
        </div>

        <form v-if="canImpersonate" class="d-flex gap-2" @submit.prevent="impersonate">
            <div>
                <input
                    v-model="impersonateForm.reason"
                    type="text"
                    class="form-control form-control-sm"
                    :class="{ 'is-invalid': impersonateForm.errors.reason }"
                    placeholder="Reason for impersonating"
                    aria-label="Reason for impersonating"
                />
                <div v-if="impersonateForm.errors.reason" class="invalid-feedback">
                    {{ impersonateForm.errors.reason }}
                </div>
            </div>
            <button type="submit" class="btn btn-sm btn-outline-warning" :disabled="impersonateForm.processing">
                Impersonate
            </button>
        </form>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase">Reseller</div>
                    <div class="fw-semibold">{{ user.data.reseller_name ?? '—' }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase">Platform role</div>
                    <div class="fw-semibold">{{ user.data.platform_role ?? '—' }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase">Member since</div>
                    <div class="fw-semibold">{{ user.data.created_at }}</div>
                </div>
            </div>
        </div>
    </div>

    <h2 class="h5 mb-3">Timeline</h2>

    <table class="table">
        <thead>
            <tr>
                <th scope="col">When</th>
                <th scope="col">Actor</th>
                <th scope="col">Subject</th>
                <th scope="col">Event</th>
                <th scope="col">Description</th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="entry in timeline.data" :key="entry.id">
                <td>{{ entry.created_at }}</td>
                <td>{{ entry.causer_name ?? '—' }}</td>
                <td>
                    {{ entry.subject_type ?? '—' }}<span v-if="entry.subject_id">#{{ entry.subject_id }}</span>
                </td>
                <td>{{ entry.event ?? '—' }}</td>
                <td>{{ entry.description }}</td>
            </tr>
            <tr v-if="timeline.data.length === 0">
                <td colspan="5" class="text-muted">No activity recorded for this user yet.</td>
            </tr>
        </tbody>
    </table>
</template>
