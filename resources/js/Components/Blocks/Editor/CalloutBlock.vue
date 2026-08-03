<script setup lang="ts">
import type { CalloutVariant } from '@/types/blocks';
import { useI18n } from 'vue-i18n';

const text = defineModel<string>('text', { required: true });
const variant = defineModel<CalloutVariant>('variant', { required: true });

const variants: CalloutVariant[] = ['info', 'warning', 'danger', 'success'];
const { t } = useI18n();
</script>

<template>
    <div class="mb-3">
        <label for="callout-text" class="form-label">{{ t('blocks.editor.callout.text') }}</label>
        <textarea id="callout-text" v-model="text" class="form-control" rows="3"></textarea>
    </div>
    <div class="mb-3">
        <label for="callout-variant" class="form-label">{{ t('blocks.editor.callout.style') }}</label>
        <select id="callout-variant" v-model="variant" class="form-select">
            <option v-for="option in variants" :key="option" :value="option">
                {{ t(`blocks.editor.callout.variants.${option}`) }}
            </option>
        </select>
    </div>
</template>
