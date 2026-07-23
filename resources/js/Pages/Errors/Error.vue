<script setup lang="ts">
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { computed } from 'vue';

defineOptions({ layout: GuestLayout });

const props = defineProps<{
    status: number;
}>();

const messages: Record<number, string> = {
    403: 'You do not have permission to access this page.',
    404: 'The page you are looking for could not be found.',
    419: 'The page expired, please try again.',
    500: 'Something went wrong on our end.',
    503: 'The application is currently unavailable.',
};

const message = computed(() => messages[props.status] ?? 'An unexpected error occurred.');
</script>

<template>
    <div class="text-center">
        <p class="display-4 fw-bold mb-2">{{ status }}</p>
        <p class="text-muted mb-0">{{ message }}</p>
    </div>
</template>
