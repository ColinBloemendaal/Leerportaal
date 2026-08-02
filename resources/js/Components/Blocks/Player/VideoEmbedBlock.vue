<script setup lang="ts">
import type { VideoEmbedContent } from '@/types/blocks';

defineProps<{ content: VideoEmbedContent }>();
</script>

<template>
    <div class="ratio ratio-16x9">
        <iframe :src="content.url" allowfullscreen title="Video"></iframe>
    </div>
</template>
