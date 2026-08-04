<script setup lang="ts">
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Link, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';

interface UserDetail {
    id: number;
    name: string;
    email: string;
    reseller_name: string | null;
    platform_role: string | null;
    created_at: string;
    erased_at: string | null;
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
    canErase: boolean;
}>();

defineOptions({ layout: AdminLayout });

const impersonateForm = useForm({ reason: '' });
const { t } = useI18n();
const erasing = ref(false);

function impersonate() {
    impersonateForm.post(`/impersonate/${props.user.data.id}`);
}

function erase() {
    if (!window.confirm(t('admin.platform.userDetail.eraseConfirm'))) {
        return;
    }

    erasing.value = true;
    router.post(`/admin/platform/users/${props.user.data.id}/erase`, {}, { onFinish: () => (erasing.value = false) });
}
</script>

<template>
    <Link href="/admin/platform/users" class="d-inline-block mb-3">{{ t('admin.platform.userDetail.back') }}</Link>

    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h1 class="h4 mb-1">{{ user.data.name }}</h1>
            <p class="text-muted mb-0">{{ user.data.email }}</p>
            <span v-if="user.data.erased_at" class="badge text-bg-secondary mt-1">
                {{ t('admin.platform.userDetail.erased', { date: user.data.erased_at }) }}
            </span>
        </div>

        <div class="d-flex gap-2">
            <form v-if="canImpersonate" class="d-flex gap-2" @submit.prevent="impersonate">
                <div>
                    <input
                        v-model="impersonateForm.reason"
                        type="text"
                        class="form-control form-control-sm"
                        :class="{ 'is-invalid': impersonateForm.errors.reason }"
                        :placeholder="t('admin.platform.userDetail.reasonPlaceholder')"
                        :aria-label="t('admin.platform.userDetail.reasonPlaceholder')"
                    />
                    <div v-if="impersonateForm.errors.reason" class="invalid-feedback">
                        {{ impersonateForm.errors.reason }}
                    </div>
                </div>
                <button type="submit" class="btn btn-sm btn-outline-warning" :disabled="impersonateForm.processing">
                    {{ t('admin.platform.userDetail.impersonate') }}
                </button>
            </form>

            <form v-if="canErase && !user.data.erased_at" @submit.prevent="erase">
                <button type="submit" class="btn btn-sm btn-outline-danger" :disabled="erasing">
                    {{ t('admin.platform.userDetail.erase') }}
                </button>
            </form>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase">{{ t('admin.platform.userDetail.reseller') }}</div>
                    <div class="fw-semibold">{{ user.data.reseller_name ?? '—' }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase">{{ t('admin.platform.userDetail.platformRole') }}</div>
                    <div class="fw-semibold">{{ user.data.platform_role ?? '—' }}</div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card h-100">
                <div class="card-body">
                    <div class="text-muted small text-uppercase">{{ t('admin.platform.userDetail.memberSince') }}</div>
                    <div class="fw-semibold">{{ user.data.created_at }}</div>
                </div>
            </div>
        </div>
    </div>

    <h2 class="h5 mb-3">{{ t('admin.platform.userDetail.timeline') }}</h2>

    <table class="table">
        <thead>
            <tr>
                <th scope="col">{{ t('admin.platform.userDetail.when') }}</th>
                <th scope="col">{{ t('admin.platform.userDetail.actor') }}</th>
                <th scope="col">{{ t('admin.platform.userDetail.subject') }}</th>
                <th scope="col">{{ t('admin.platform.userDetail.event') }}</th>
                <th scope="col">{{ t('admin.platform.userDetail.description') }}</th>
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
                <td colspan="5" class="text-muted">{{ t('admin.platform.userDetail.noActivity') }}</td>
            </tr>
        </tbody>
    </table>
</template>
