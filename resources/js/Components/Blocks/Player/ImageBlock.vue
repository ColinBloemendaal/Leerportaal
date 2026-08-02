<script setup lang="ts">
import type { ImageContent } from '@/types/blocks';

defineProps<{ content: ImageContent }>();
</script>

<template>
    <figure class="mb-0">
        <img :src="content.url" :alt="content.alt" class="img-fluid" />
        <figcaption v-if="content.caption" class="text-muted small mt-1">{{ content.caption }}</figcaption>
    </figure>
</template>
