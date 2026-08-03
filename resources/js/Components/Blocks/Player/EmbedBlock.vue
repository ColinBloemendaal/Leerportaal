<script setup lang="ts">
import type { EmbedContent } from '@/types/blocks';
import { useI18n } from 'vue-i18n';

defineProps<{ content: EmbedContent }>();

const { t } = useI18n();
</script>

<template>
    <div class="ratio ratio-16x9">
        <iframe :src="content.url" :title="t('blocks.player.embed.title')"></iframe>
    </div>
</template>
