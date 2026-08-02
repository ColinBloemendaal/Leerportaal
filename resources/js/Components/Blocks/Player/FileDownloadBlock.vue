<script setup lang="ts">
import type { FileDownloadContent } from '@/types/blocks';

defineProps<{ content: FileDownloadContent }>();
</script>

<template>
    <a :href="content.url" class="btn btn-outline-primary" target="_blank" rel="noopener">
        {{ content.label }}
    </a>
</template>
