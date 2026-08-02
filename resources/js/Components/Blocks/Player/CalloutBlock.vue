<script setup lang="ts">
import type { CalloutContent } from '@/types/blocks';
import { computed } from 'vue';

const props = defineProps<{ content: CalloutContent }>();

const alertClass = computed(() => `alert alert-${props.content.variant}`);
</script>

<template>
    <div :class="alertClass" role="alert">{{ content.text }}</div>
</template>
