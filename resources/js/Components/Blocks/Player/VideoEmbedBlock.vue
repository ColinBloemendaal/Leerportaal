<script setup lang="ts">
import type { VideoEmbedContent } from '@/types/blocks';
import { useI18n } from 'vue-i18n';

defineProps<{ content: VideoEmbedContent }>();

const { t } = useI18n();
</script>

<template>
    <div class="ratio ratio-16x9">
        <iframe :src="content.url" allowfullscreen :title="t('blocks.player.videoEmbed.title')"></iframe>
    </div>
</template>
