<script setup lang="ts">
import type { HotspotRegion } from '@/types/questions';

const imageUrl = defineModel<string>('image_url', { required: true });
const regions = defineModel<HotspotRegion[]>('regions', { required: true });
const correctRegionIds = defineModel<string[]>('correct_region_ids', { required: true });

function addRegion(): void {
    regions.value = [...regions.value, { id: crypto.randomUUID(), x: 0, y: 0, width: 10, height: 10, label: '' }];
}

function removeRegion(id: string): void {
    regions.value = regions.value.filter((region) => region.id !== id);
    correctRegionIds.value = correctRegionIds.value.filter((regionId) => regionId !== id);
}

function updateRegion(id: string, field: keyof HotspotRegion, value: string | number): void {
    regions.value = regions.value.map((region) => (region.id === id ? { ...region, [field]: value } : region));
}

function toggleCorrect(id: string, checked: boolean): void {
    correctRegionIds.value = checked
        ? [...correctRegionIds.value, id]
        : correctRegionIds.value.filter((regionId) => regionId !== id);
}
</script>

<template>
    <div class="mb-3">
        <label for="hotspot-image-url" class="form-label">Image URL</label>
        <input id="hotspot-image-url" v-model="imageUrl" type="text" class="form-control" />
    </div>
    <div class="mb-3">
        <label class="form-label">Regions (percent of image)</label>
        <div v-for="region in regions" :key="region.id" class="row g-2 align-items-center mb-2">
            <div class="col-auto">
                <input
                    type="checkbox"
                    class="form-check-input"
                    :checked="correctRegionIds.includes(region.id)"
                    :aria-label="`Mark '${region.label}' as a correct region`"
                    @change="toggleCorrect(region.id, ($event.target as HTMLInputElement).checked)"
                />
            </div>
            <div class="col">
                <input
                    type="text"
                    class="form-control"
                    placeholder="Label"
                    :value="region.label"
                    @input="updateRegion(region.id, 'label', ($event.target as HTMLInputElement).value)"
                />
            </div>
            <div v-for="field in ['x', 'y', 'width', 'height'] as const" :key="field" class="col">
                <input
                    type="number"
                    class="form-control"
                    :placeholder="field"
                    min="0"
                    max="100"
                    :value="region[field]"
                    @input="updateRegion(region.id, field, Number(($event.target as HTMLInputElement).value))"
                />
            </div>
            <div class="col-auto">
                <button type="button" class="btn btn-outline-danger" @click="removeRegion(region.id)">Remove</button>
            </div>
        </div>
        <button type="button" class="btn btn-outline-secondary btn-sm" @click="addRegion">Add region</button>
    </div>
</template>
