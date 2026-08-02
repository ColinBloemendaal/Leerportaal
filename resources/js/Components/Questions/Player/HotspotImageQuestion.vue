<script setup lang="ts">
import type { HotspotImagePayload } from '@/types/questions';

defineProps<{ payload: HotspotImagePayload; questionId: string | number }>();

const answer = defineModel<string[]>({ default: () => [] });

function toggle(id: string, checked: boolean): void {
    answer.value = checked ? [...answer.value, id] : answer.value.filter((regionId) => regionId !== id);
}
</script>

<template>
    <div>
        <!--
            The image + overlay is a visual convenience for mouse/touch
            users. It is not the answer-submission mechanism -- the
            labeled checkbox list below is, so every region is fully
            reachable and operable by keyboard alone regardless of
            whether the image loads or the overlay is used at all.
        -->
        <div class="position-relative mb-3" style="max-width: 40rem">
            <img :src="payload.image_url" alt="" class="img-fluid" />
            <button
                v-for="region in payload.regions"
                :key="region.id"
                type="button"
                class="position-absolute border border-2"
                :class="answer.includes(region.id) ? 'border-primary' : 'border-secondary'"
                :style="{
                    left: `${region.x}%`,
                    top: `${region.y}%`,
                    width: `${region.width}%`,
                    height: `${region.height}%`,
                    background: 'transparent',
                }"
                :aria-hidden="true"
                tabindex="-1"
                @click="toggle(region.id, !answer.includes(region.id))"
            ></button>
        </div>
        <fieldset>
            <legend class="form-label h6">Select the correct area(s)</legend>
            <div v-for="region in payload.regions" :key="region.id" class="form-check">
                <input
                    :id="`question-${questionId}-region-${region.id}`"
                    class="form-check-input"
                    type="checkbox"
                    :checked="answer.includes(region.id)"
                    @change="toggle(region.id, ($event.target as HTMLInputElement).checked)"
                />
                <label class="form-check-label" :for="`question-${questionId}-region-${region.id}`">
                    {{ region.label }}
                </label>
            </div>
        </fieldset>
    </div>
</template>
