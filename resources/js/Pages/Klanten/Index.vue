<script setup lang="ts">
import { useExportRequest } from '@/Composables/useExportRequest';
import { useIndexFilters } from '@/Composables/useIndexFilters';
import AppLayout from '@/Layouts/AppLayout.vue';
import type { FilterQuery } from '@/types/filtering';
import type { PaginatedKlanten, ResellerKlant } from '@/types/klanten';
import { router, useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

defineOptions({ layout: AppLayout });

const props = defineProps<{
    klanten: PaginatedKlanten;
    trashed: ResellerKlant[];
    query: FilterQuery;
}>();

const { search, sort, direction, sortBy } = useIndexFilters('/klanten', props.query);
const { requestExport } = useExportRequest('klanten', props.query);
const { t } = useI18n();

const form = useForm({
    name: '',
});

function submit() {
    form.post('/klanten', {
        onSuccess: () => form.reset(),
    });
}

function destroy(klant: ResellerKlant) {
    router.delete(`/klanten/${klant.id}`);
}

function restore(klant: ResellerKlant) {
    router.post(`/klanten/${klant.id}/restore`);
}
</script>

<template>
    <h1 class="h4 mb-4">{{ t('klanten.title') }}</h1>

    <form class="row g-2 mb-4" @submit.prevent="submit">
        <div class="col-auto">
            <input
                v-model="form.name"
                type="text"
                class="form-control"
                :class="{ 'is-invalid': form.errors.name }"
                :placeholder="t('klanten.namePlaceholder')"
                :aria-label="t('klanten.namePlaceholder')"
            />
            <div v-if="form.errors.name" class="invalid-feedback">{{ form.errors.name }}</div>
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-primary" :disabled="form.processing">{{ t('klanten.add') }}</button>
        </div>
    </form>

    <div class="row g-2 mb-3 align-items-center">
        <div class="col-auto">
            <input
                v-model="search"
                type="search"
                class="form-control"
                :placeholder="t('klanten.searchPlaceholder')"
                :aria-label="t('klanten.searchPlaceholder')"
            />
        </div>
        <div class="col-auto ms-auto">
            <button type="button" class="btn btn-sm btn-outline-secondary" @click="requestExport('csv')">
                {{ t('common.exportCsv') }}
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary" @click="requestExport('xlsx')">
                {{ t('common.exportXlsx') }}
            </button>
        </div>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th scope="col" role="button" @click="sortBy('name')">
                    {{ t('klanten.name') }}
                    <span v-if="sort === 'name'">{{ direction === 'asc' ? '▲' : '▼' }}</span>
                </th>
                <th scope="col"></th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="klant in klanten.data" :key="klant.id">
                <td>{{ klant.name }}</td>
                <td>
                    <button type="button" class="btn btn-sm btn-outline-danger" @click="destroy(klant)">
                        {{ t('klanten.remove') }}
                    </button>
                </td>
            </tr>
            <tr v-if="klanten.data.length === 0">
                <td colspan="2" class="text-muted">{{ t('klanten.empty') }}</td>
            </tr>
        </tbody>
    </table>

    <template v-if="trashed.length > 0">
        <h2 class="h6 mt-4 mb-2">{{ t('klanten.trashedTitle') }}</h2>
        <table class="table">
            <tbody>
                <tr v-for="klant in trashed" :key="klant.id">
                    <td>{{ klant.name }}</td>
                    <td>
                        <button type="button" class="btn btn-sm btn-outline-secondary" @click="restore(klant)">
                            {{ t('klanten.restore') }}
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
    </template>
</template>
