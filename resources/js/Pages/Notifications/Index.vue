<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import type { PaginatedCollection } from '@/types/filtering';
import type { NotificationRow } from '@/types/notifications';
import { router } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

// AppLayout, not a role-specific layout: reachable by any authenticated
// user regardless of area, same reasoning as Admin/Exports/Index.vue.
defineOptions({ layout: AppLayout });

defineProps<{
    notifications: PaginatedCollection<NotificationRow>;
}>();

const { t } = useI18n();

function markRead(notification: NotificationRow) {
    router.post(`/notifications/${notification.id}/read`, {}, { preserveScroll: true });
}

function markAllRead() {
    router.post('/notifications/read-all', {}, { preserveScroll: true });
}
</script>

<template>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h4 mb-0">{{ t('notifications.title') }}</h1>
        <button type="button" class="btn btn-sm btn-outline-secondary" @click="markAllRead">
            {{ t('notifications.markAllRead') }}
        </button>
    </div>

    <div class="list-group">
        <div
            v-for="notification in notifications.data"
            :key="notification.id"
            class="list-group-item d-flex justify-content-between align-items-center"
            :class="{ 'bg-body-tertiary': notification.read_at === null }"
        >
            <div>
                <p class="mb-1" :class="{ 'fw-semibold': notification.read_at === null }">
                    {{ notification.message }}
                </p>
                <p class="text-muted small mb-0">{{ notification.created_at }}</p>
            </div>
            <button
                v-if="notification.read_at === null"
                type="button"
                class="btn btn-sm btn-outline-primary"
                @click="markRead(notification)"
            >
                {{ t('notifications.markRead') }}
            </button>
        </div>
        <div v-if="notifications.data.length === 0" class="list-group-item text-muted">
            {{ t('notifications.empty') }}
        </div>
    </div>

    <div v-if="notifications.meta.last_page > 1" class="text-muted small mt-3">
        {{
            t('common.pagination', {
                current: notifications.meta.current_page,
                last: notifications.meta.last_page,
                total: notifications.meta.total,
            })
        }}
    </div>
</template>
