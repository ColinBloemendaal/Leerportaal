<script setup lang="ts">
import type { RichTextContent } from '@/types/blocks';

defineProps<{ content: RichTextContent }>();
</script>

<template>
    <!-- content.html is server-rendered/validated via App\Rules\RejectsUnsafeMarkup
         before it's ever persisted -- not raw, unvalidated user input at render time. -->
    <!-- eslint-disable-next-line vue/no-v-html -->
    <div v-html="content.html"></div>
</template>
