<script setup lang="ts">
defineProps<{
    locales: { locale: string; complete: boolean }[];
}>();
</script>

<template>
    <div class="d-flex gap-2 flex-wrap">
        <span
            v-for="item in locales"
            :key="item.locale"
            class="badge"
            :class="item.complete ? 'text-bg-success' : 'text-bg-warning'"
        >
            {{ item.locale.toUpperCase() }}: {{ item.complete ? 'Complete' : 'Incomplete' }}
        </span>
    </div>
</template>
