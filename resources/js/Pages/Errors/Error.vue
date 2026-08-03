<script setup lang="ts">
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

defineOptions({ layout: GuestLayout });

const props = defineProps<{
    status: number;
}>();

const { t, te } = useI18n();

const message = computed(() => {
    const key = `errors.${props.status}`;
    return te(key) ? t(key) : t('errors.unknown');
});
</script>

<template>
    <div class="text-center">
        <p class="display-4 fw-bold mb-2">{{ status }}</p>
        <p class="text-muted mb-0">{{ message }}</p>
    </div>
</template>
