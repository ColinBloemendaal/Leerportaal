<script setup lang="ts">
import type { LikertScalePoint } from '@/types/questions';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const scale = defineModel<LikertScalePoint[]>('scale', { required: true });

function addPoint(): void {
    scale.value = [...scale.value, { value: String(scale.value.length + 1), label: '' }];
}

function removePoint(index: number): void {
    scale.value = scale.value.filter((_, i) => i !== index);
}

function updatePoint(index: number, field: keyof LikertScalePoint, value: string): void {
    scale.value = scale.value.map((point, i) => (i === index ? { ...point, [field]: value } : point));
}
</script>

<template>
    <div class="mb-3">
        <label class="form-label">{{ t('questions.editor.likert.scalePoints') }}</label>
        <div v-for="(point, index) in scale" :key="index" class="input-group mb-2">
            <input
                type="text"
                class="form-control"
                style="max-width: 6rem"
                :placeholder="t('questions.editor.likert.valuePlaceholder')"
                :value="point.value"
                @input="updatePoint(index, 'value', ($event.target as HTMLInputElement).value)"
            />
            <input
                type="text"
                class="form-control"
                :placeholder="t('questions.editor.likert.labelPlaceholder')"
                :value="point.label"
                @input="updatePoint(index, 'label', ($event.target as HTMLInputElement).value)"
            />
            <button type="button" class="btn btn-outline-danger" @click="removePoint(index)">
                {{ t('questions.common.remove') }}
            </button>
        </div>
        <button type="button" class="btn btn-outline-secondary btn-sm" @click="addPoint">
            {{ t('questions.editor.likert.addScalePoint') }}
        </button>
    </div>
</template>
