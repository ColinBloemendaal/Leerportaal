<script setup lang="ts">
import type { DraggableItem, DropZone } from '@/types/questions';

const imageUrl = defineModel<string>('image_url', { required: true });
const dropZones = defineModel<DropZone[]>('drop_zones', { required: true });
const draggableItems = defineModel<DraggableItem[]>('draggable_items', { required: true });
const correctPlacements = defineModel<Record<string, string>>('correct_placements', { required: true });

function addZone(): void {
    dropZones.value = [...dropZones.value, { id: crypto.randomUUID(), x: 0, y: 0, width: 10, height: 10, label: '' }];
}

function removeZone(id: string): void {
    dropZones.value = dropZones.value.filter((zone) => zone.id !== id);
    const { [id]: _removed, ...rest } = correctPlacements.value;
    correctPlacements.value = rest;
}

function updateZone(id: string, field: keyof DropZone, value: string | number): void {
    dropZones.value = dropZones.value.map((zone) => (zone.id === id ? { ...zone, [field]: value } : zone));
}

function addItem(): void {
    draggableItems.value = [...draggableItems.value, { id: crypto.randomUUID(), text: '' }];
}

function removeItem(id: string): void {
    draggableItems.value = draggableItems.value.filter((item) => item.id !== id);
}

function updateItemText(id: string, text: string): void {
    draggableItems.value = draggableItems.value.map((item) => (item.id === id ? { ...item, text } : item));
}

function setCorrectItem(zoneId: string, itemId: string): void {
    correctPlacements.value = { ...correctPlacements.value, [zoneId]: itemId };
}
</script>

<template>
    <div class="mb-3">
        <label for="drag-drop-image-url" class="form-label">Image URL</label>
        <input id="drag-drop-image-url" v-model="imageUrl" type="text" class="form-control" />
    </div>
    <div class="mb-3">
        <label class="form-label">Draggable items</label>
        <div v-for="item in draggableItems" :key="item.id" class="input-group mb-2">
            <input
                type="text"
                class="form-control"
                :value="item.text"
                @input="updateItemText(item.id, ($event.target as HTMLInputElement).value)"
            />
            <button type="button" class="btn btn-outline-danger" @click="removeItem(item.id)">Remove</button>
        </div>
        <button type="button" class="btn btn-outline-secondary btn-sm" @click="addItem">Add item</button>
    </div>
    <div class="mb-3">
        <label class="form-label">Drop zones (percent of image)</label>
        <div v-for="zone in dropZones" :key="zone.id" class="row g-2 align-items-center mb-2">
            <div class="col">
                <input
                    type="text"
                    class="form-control"
                    placeholder="Label"
                    :value="zone.label"
                    @input="updateZone(zone.id, 'label', ($event.target as HTMLInputElement).value)"
                />
            </div>
            <div v-for="field in (['x', 'y', 'width', 'height'] as const)" :key="field" class="col">
                <input
                    type="number"
                    class="form-control"
                    :placeholder="field"
                    min="0"
                    max="100"
                    :value="zone[field]"
                    @input="updateZone(zone.id, field, Number(($event.target as HTMLInputElement).value))"
                />
            </div>
            <div class="col">
                <select
                    class="form-select"
                    :value="correctPlacements[zone.id] ?? ''"
                    @change="setCorrectItem(zone.id, ($event.target as HTMLSelectElement).value)"
                >
                    <option value="" disabled>Correct item</option>
                    <option v-for="item in draggableItems" :key="item.id" :value="item.id">{{ item.text }}</option>
                </select>
            </div>
            <div class="col-auto">
                <button type="button" class="btn btn-outline-danger" @click="removeZone(zone.id)">Remove</button>
            </div>
        </div>
        <button type="button" class="btn btn-outline-secondary btn-sm" @click="addZone">Add zone</button>
    </div>
</template>
