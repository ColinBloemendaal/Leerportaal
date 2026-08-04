<script setup lang="ts">
import { useExportRequest } from '@/Composables/useExportRequest';
import { useIndexFilters } from '@/Composables/useIndexFilters';
import ResellerAdminLayout from '@/Layouts/ResellerAdminLayout.vue';
import type { FilterQuery, PaginatedCollection } from '@/types/filtering';
import { useI18n } from 'vue-i18n';

interface InvoiceRow {
    id: number;
    period_start: string;
    period_end: string;
    status: string;
    subtotal_cents: number;
    vat_cents: number;
    reverse_charge: boolean;
    total_cents: number;
    issued_at: string | null;
    paid_at: string | null;
}

defineOptions({ layout: ResellerAdminLayout });

const props = defineProps<{
    invoices: PaginatedCollection<InvoiceRow>;
    query: FilterQuery;
}>();

const { sort, direction, filters, sortBy } = useIndexFilters('/admin/reseller/invoices', props.query);
const { requestExport } = useExportRequest('invoices', props.query);
const { t } = useI18n();

function formatMoney(cents: number): string {
    return new Intl.NumberFormat('nl-NL', { style: 'currency', currency: 'EUR' }).format(cents / 100);
}
</script>

<template>
    <h1 class="h4 mb-4">{{ t('admin.reseller.invoices.title') }}</h1>

    <div class="row g-2 mb-3 align-items-center">
        <div class="col-auto">
            <select v-model="filters.status" class="form-select">
                <option value="">{{ t('admin.reseller.invoices.allStatuses') }}</option>
                <option value="draft">{{ t('admin.reseller.invoices.draft') }}</option>
                <option value="issued">{{ t('admin.reseller.invoices.issued') }}</option>
                <option value="paid">{{ t('admin.reseller.invoices.paid') }}</option>
                <option value="overdue">{{ t('admin.reseller.invoices.overdue') }}</option>
                <option value="void">{{ t('admin.reseller.invoices.void') }}</option>
            </select>
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
                <th scope="col" role="button" @click="sortBy('period_start')">
                    {{ t('admin.reseller.invoices.period') }}
                    <span v-if="sort === 'period_start'">{{ direction === 'asc' ? '▲' : '▼' }}</span>
                </th>
                <th scope="col" role="button" @click="sortBy('status')">
                    {{ t('admin.reseller.invoices.status') }}
                    <span v-if="sort === 'status'">{{ direction === 'asc' ? '▲' : '▼' }}</span>
                </th>
                <th scope="col">{{ t('admin.reseller.invoices.subtotal') }}</th>
                <th scope="col">{{ t('admin.reseller.invoices.vat') }}</th>
                <th scope="col" role="button" @click="sortBy('total_cents')">
                    {{ t('admin.reseller.invoices.total') }}
                    <span v-if="sort === 'total_cents'">{{ direction === 'asc' ? '▲' : '▼' }}</span>
                </th>
                <th scope="col">{{ t('admin.reseller.invoices.issued') }}</th>
                <th scope="col">{{ t('admin.reseller.invoices.paid') }}</th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="invoice in invoices.data" :key="invoice.id">
                <td>{{ invoice.period_start }} &ndash; {{ invoice.period_end }}</td>
                <td>{{ invoice.status }}</td>
                <td>{{ formatMoney(invoice.subtotal_cents) }}</td>
                <td>
                    {{
                        invoice.reverse_charge
                            ? t('admin.reseller.invoices.reverseCharge')
                            : formatMoney(invoice.vat_cents)
                    }}
                </td>
                <td>{{ formatMoney(invoice.total_cents) }}</td>
                <td>{{ invoice.issued_at ?? '—' }}</td>
                <td>{{ invoice.paid_at ?? '—' }}</td>
            </tr>
            <tr v-if="invoices.data.length === 0">
                <td colspan="7" class="text-muted">{{ t('common.noResultsFiltered') }}</td>
            </tr>
        </tbody>
    </table>

    <div v-if="invoices.meta.last_page > 1" class="text-muted small">
        {{
            t('common.pagination', {
                current: invoices.meta.current_page,
                last: invoices.meta.last_page,
                total: invoices.meta.total,
            })
        }}
    </div>
</template>
