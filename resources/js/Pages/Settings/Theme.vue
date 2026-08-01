<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { watch } from 'vue';

defineOptions({ layout: AppLayout });

const props = defineProps<{
    theme: {
        data: {
            primary_color: string;
            secondary_color: string | null;
            accent_color: string | null;
            font_family: string | null;
        };
    };
}>();

const form = useForm({
    primary_color: props.theme.data.primary_color,
    secondary_color: props.theme.data.secondary_color ?? '',
    accent_color: props.theme.data.accent_color ?? '',
    font_family: props.theme.data.font_family ?? '',
});

// Live preview: applies straight to the current document, the same
// custom properties App\Services\Theming\ThemeCssGenerator injects
// server-side, so Bootstrap's own components (already wired to these in
// _theme-overrides.scss) re-theme instantly without a save or reload.
function applyPreview(variable: string, value: string) {
    const root = document.documentElement;

    if (value === '') {
        root.style.removeProperty(variable);
    } else {
        root.style.setProperty(variable, value);
    }
}

watch(
    () => form.primary_color,
    (value) => applyPreview('--reseller-primary-color', value),
    { immediate: true },
);
watch(
    () => form.secondary_color,
    (value) => applyPreview('--reseller-secondary-color', value),
    { immediate: true },
);
watch(
    () => form.accent_color,
    (value) => applyPreview('--reseller-accent-color', value),
    { immediate: true },
);
watch(
    () => form.font_family,
    (value) => applyPreview('--reseller-font-family', value),
    { immediate: true },
);

function submit() {
    form.put('/settings/theme');
}
</script>

<template>
    <h1 class="h4 mb-4">Theme</h1>

    <form class="row g-3" style="max-width: 32rem" @submit.prevent="submit">
        <div class="col-12">
            <label for="primary_color" class="form-label">Primary color</label>
            <div class="input-group">
                <input
                    id="primary_color"
                    v-model="form.primary_color"
                    type="color"
                    class="form-control form-control-color"
                    title="Primary color"
                />
                <input
                    v-model="form.primary_color"
                    type="text"
                    class="form-control"
                    :class="{ 'is-invalid': form.errors.primary_color }"
                    aria-label="Primary color hex value"
                />
            </div>
            <div v-if="form.errors.primary_color" class="invalid-feedback d-block">
                {{ form.errors.primary_color }}
            </div>
        </div>

        <div class="col-12">
            <label for="secondary_color" class="form-label">Secondary color</label>
            <div class="input-group">
                <input
                    id="secondary_color"
                    v-model="form.secondary_color"
                    type="color"
                    class="form-control form-control-color"
                    title="Secondary color"
                />
                <input
                    v-model="form.secondary_color"
                    type="text"
                    class="form-control"
                    :class="{ 'is-invalid': form.errors.secondary_color }"
                    aria-label="Secondary color hex value"
                />
            </div>
            <div v-if="form.errors.secondary_color" class="invalid-feedback d-block">
                {{ form.errors.secondary_color }}
            </div>
        </div>

        <div class="col-12">
            <label for="accent_color" class="form-label">Accent color</label>
            <div class="input-group">
                <input
                    id="accent_color"
                    v-model="form.accent_color"
                    type="color"
                    class="form-control form-control-color"
                    title="Accent color"
                />
                <input
                    v-model="form.accent_color"
                    type="text"
                    class="form-control"
                    :class="{ 'is-invalid': form.errors.accent_color }"
                    aria-label="Accent color hex value"
                />
            </div>
            <div v-if="form.errors.accent_color" class="invalid-feedback d-block">{{ form.errors.accent_color }}</div>
        </div>

        <div class="col-12">
            <label for="font_family" class="form-label">Font family</label>
            <input
                id="font_family"
                v-model="form.font_family"
                type="text"
                class="form-control"
                :class="{ 'is-invalid': form.errors.font_family }"
                placeholder="Inter, system-ui, sans-serif"
            />
            <div v-if="form.errors.font_family" class="invalid-feedback">{{ form.errors.font_family }}</div>
        </div>

        <div class="col-12">
            <button type="submit" class="btn btn-primary" :disabled="form.processing">Save theme</button>
        </div>
    </form>

    <div class="mt-4 p-3 border rounded" style="max-width: 32rem">
        <p class="text-muted small mb-2">Live preview</p>
        <button type="button" class="btn btn-primary me-2">Primary button</button>
        <a href="#" class="link-primary">A link</a>
    </div>
</template>
