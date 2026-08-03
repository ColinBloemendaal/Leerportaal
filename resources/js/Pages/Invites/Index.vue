<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import type { UserInvite } from '@/types/invites';
import type { PaginatedKlanten } from '@/types/klanten';
import { router, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

defineOptions({ layout: AppLayout });

const props = defineProps<{
    invites: { data: UserInvite[] };
    revoked: { data: UserInvite[] };
    klanten: PaginatedKlanten;
    klantRoles: { value: string; label: string }[];
    resellerRoles: { value: string; label: string }[];
}>();

const form = useForm({
    name: '',
    email: '',
    resellerklant_id: '' as number | '',
    role: '',
});

const availableRoles = computed(() => (form.resellerklant_id === '' ? props.resellerRoles : props.klantRoles));
const { t } = useI18n();

function submit() {
    form.post('/invites', {
        onSuccess: () => form.reset(),
    });
}

function revoke(invite: UserInvite) {
    router.delete(`/invites/${invite.id}`);
}

function restore(invite: UserInvite) {
    router.post(`/invites/${invite.id}/restore`);
}
</script>

<template>
    <h1 class="h4 mb-4">{{ t('invites.title') }}</h1>

    <form class="row g-2 mb-4 align-items-end" @submit.prevent="submit">
        <div class="col-auto">
            <label for="name" class="form-label">{{ t('invites.name') }}</label>
            <input
                id="name"
                v-model="form.name"
                type="text"
                class="form-control"
                :class="{ 'is-invalid': form.errors.name }"
            />
            <div v-if="form.errors.name" class="invalid-feedback">{{ form.errors.name }}</div>
        </div>
        <div class="col-auto">
            <label for="email" class="form-label">{{ t('invites.email') }}</label>
            <input
                id="email"
                v-model="form.email"
                type="email"
                class="form-control"
                :class="{ 'is-invalid': form.errors.email }"
            />
            <div v-if="form.errors.email" class="invalid-feedback">{{ form.errors.email }}</div>
        </div>
        <div class="col-auto">
            <label for="resellerklant_id" class="form-label">{{ t('invites.klant') }}</label>
            <select
                id="resellerklant_id"
                v-model="form.resellerklant_id"
                class="form-select"
                :class="{ 'is-invalid': form.errors.resellerklant_id }"
            >
                <option value="">{{ t('invites.resellerStaff') }}</option>
                <option v-for="klant in klanten.data" :key="klant.id" :value="klant.id">{{ klant.name }}</option>
            </select>
            <div v-if="form.errors.resellerklant_id" class="invalid-feedback">{{ form.errors.resellerklant_id }}</div>
        </div>
        <div class="col-auto">
            <label for="role" class="form-label">{{ t('invites.role') }}</label>
            <select id="role" v-model="form.role" class="form-select" :class="{ 'is-invalid': form.errors.role }">
                <option value="" disabled>{{ t('invites.chooseRole') }}</option>
                <option v-for="role in availableRoles" :key="role.value" :value="role.value">
                    {{ role.label }}
                </option>
            </select>
            <div v-if="form.errors.role" class="invalid-feedback">{{ form.errors.role }}</div>
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-primary" :disabled="form.processing">{{ t('invites.send') }}</button>
        </div>
    </form>

    <table class="table">
        <thead>
            <tr>
                <th scope="col">{{ t('invites.name') }}</th>
                <th scope="col">{{ t('invites.email') }}</th>
                <th scope="col">{{ t('invites.roleColumn') }}</th>
                <th scope="col">{{ t('invites.type') }}</th>
                <th scope="col"></th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="invite in invites.data" :key="invite.id">
                <td>{{ invite.name }}</td>
                <td>{{ invite.email }}</td>
                <td>{{ invite.roleLabel }}</td>
                <td>{{ invite.isKlantInvite ? t('invites.klant') : t('invites.resellerStaff') }}</td>
                <td>
                    <button type="button" class="btn btn-sm btn-outline-danger" @click="revoke(invite)">
                        {{ t('invites.revoke') }}
                    </button>
                </td>
            </tr>
            <tr v-if="invites.data.length === 0">
                <td colspan="5" class="text-muted">{{ t('invites.empty') }}</td>
            </tr>
        </tbody>
    </table>

    <template v-if="revoked.data.length > 0">
        <h2 class="h6 mt-4 mb-2">{{ t('invites.revokedTitle') }}</h2>
        <table class="table">
            <tbody>
                <tr v-for="invite in revoked.data" :key="invite.id">
                    <td>{{ invite.name }}</td>
                    <td>{{ invite.email }}</td>
                    <td>
                        <button type="button" class="btn btn-sm btn-outline-secondary" @click="restore(invite)">
                            {{ t('invites.restore') }}
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
    </template>
</template>
