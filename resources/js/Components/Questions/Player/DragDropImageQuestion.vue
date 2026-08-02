<script setup lang="ts">
import type { DragDropImagePayload } from '@/types/questions';

const props = defineProps<{ payload: DragDropImagePayload; questionId: string | number }>();

const answer = defineModel<Record<string, string>>({ default: () => ({}) });

function select(zoneId: string, itemId: string): void {
    answer.value = { ...answer.value, [zoneId]: itemId };
}

function itemText(id: string): string {
    return props.payload.draggable_items.find((item) => item.id === id)?.text ?? '';
}
</script>

<template>
    <div>
        <!--
            As with hotspot_image: the image below is a visual aid for
            mouse/touch users. The labeled <select> per drop zone is the
            actual answer-submission mechanism, fully keyboard-operable
            on its own.
        -->
        <div class="position-relative mb-3" style="max-width: 40rem">
            <img :src="payload.image_url" alt="" class="img-fluid" />
            <span
                v-for="zone in payload.drop_zones"
                :key="zone.id"
                class="position-absolute border border-2 border-secondary d-flex align-items-center justify-content-center bg-white bg-opacity-75"
                :style="{ left: `${zone.x}%`, top: `${zone.y}%`, width: `${zone.width}%`, height: `${zone.height}%` }"
                aria-hidden="true"
            >
                {{ answer[zone.id] ? itemText(answer[zone.id]) : '' }}
            </span>
        </div>
        <div v-for="zone in payload.drop_zones" :key="zone.id" class="mb-2 row align-items-center">
            <label class="col-auto col-form-label" :for="`question-${questionId}-zone-${zone.id}`">{{ zone.label }}</label>
            <div class="col-auto">
                <select
                    :id="`question-${questionId}-zone-${zone.id}`"
                    class="form-select"
                    :value="answer[zone.id] ?? ''"
                    @change="select(zone.id, ($event.target as HTMLSelectElement).value)"
                >
                    <option value="" disabled>Choose an item</option>
                    <option v-for="item in payload.draggable_items" :key="item.id" :value="item.id">
                        {{ item.text }}
                    </option>
                </select>
            </div>
        </div>
    </div>
</template>
