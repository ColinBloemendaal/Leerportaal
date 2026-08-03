<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

defineOptions({ layout: AppLayout });

defineProps<{
    types: { type: string; label: string; overridden: boolean }[];
}>();

const { t } = useI18n();
</script>

<template>
    <h1 class="h4 mb-4">{{ t('settings.emailTemplates.index.title') }}</h1>

    <p class="text-muted">
        {{ t('settings.emailTemplates.index.description') }}
    </p>

    <table class="table">
        <thead>
            <tr>
                <th scope="col">{{ t('settings.emailTemplates.index.notification') }}</th>
                <th scope="col">{{ t('settings.emailTemplates.index.status') }}</th>
                <th scope="col"></th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="type in types" :key="type.type">
                <td>{{ type.label }}</td>
                <td>
                    <span :class="type.overridden ? 'badge text-bg-primary' : 'badge text-bg-secondary'">
                        {{
                            type.overridden
                                ? t('settings.emailTemplates.index.customized')
                                : t('settings.emailTemplates.index.default')
                        }}
                    </span>
                </td>
                <td>
                    <Link :href="`/settings/email-templates/${type.type}`" class="btn btn-sm btn-outline-primary">
                        {{ t('settings.emailTemplates.index.edit') }}
                    </Link>
                </td>
            </tr>
        </tbody>
    </table>
</template>
