<script setup lang="ts">
import type { CalloutVariant } from '@/types/blocks';

const text = defineModel<string>('text', { required: true });
const variant = defineModel<CalloutVariant>('variant', { required: true });

const variants: CalloutVariant[] = ['info', 'warning', 'danger', 'success'];
</script>

<template>
    <div class="mb-3">
        <label for="callout-text" class="form-label">Text</label>
        <textarea id="callout-text" v-model="text" class="form-control" rows="3"></textarea>
    </div>
    <div class="mb-3">
        <label for="callout-variant" class="form-label">Style</label>
        <select id="callout-variant" v-model="variant" class="form-select">
            <option v-for="option in variants" :key="option" :value="option">{{ option }}</option>
        </select>
    </div>
</template>
