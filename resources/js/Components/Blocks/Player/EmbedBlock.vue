<script setup lang="ts">
import type { EmbedContent } from '@/types/blocks';

defineProps<{ content: EmbedContent }>();
</script>

<template>
    <div class="ratio ratio-16x9">
        <iframe :src="content.url" title="Embedded content"></iframe>
    </div>
</template>
