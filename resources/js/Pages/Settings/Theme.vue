<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import { meetsWcagAA } from '@/lib/contrast';
import { useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';

defineOptions({ layout: AppLayout });

const props = defineProps<{
    theme: {
        data: {
            primary_color: string;
            secondary_color: string | null;
            accent_color: string | null;
            font_family: string | null;
            custom_css: string | null;
            sender_name: string | null;
            reply_to_email: string | null;
        };
    };
    assetUrls: {
        logo: string | null;
        favicon: string | null;
        login_background: string | null;
    };
}>();

const form = useForm({
    primary_color: props.theme.data.primary_color,
    secondary_color: props.theme.data.secondary_color ?? '',
    accent_color: props.theme.data.accent_color ?? '',
    font_family: props.theme.data.font_family ?? '',
    logo: null as File | null,
    favicon: null as File | null,
    login_background: null as File | null,
    custom_css: props.theme.data.custom_css ?? '',
    sender_name: props.theme.data.sender_name ?? '',
    reply_to_email: props.theme.data.reply_to_email ?? '',
});

function onFileChange(field: 'logo' | 'favicon' | 'login_background', event: Event) {
    const input = event.target as HTMLInputElement;
    form[field] = input.files?.[0] ?? null;
}

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

// WCAG AA warning, not a blocker: Bootstrap renders white text on
// primary/secondary backgrounds by default (buttons, badges), so that's
// the pairing worth checking. null means "not a valid hex color yet" --
// no warning while the user is still typing.
const primaryContrastFailsAA = computed(() => meetsWcagAA(form.primary_color, '#ffffff') === false);
const secondaryContrastFailsAA = computed(
    () => form.secondary_color !== '' && meetsWcagAA(form.secondary_color, '#ffffff') === false,
);
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
            <p v-if="primaryContrastFailsAA" class="text-warning small mb-0 mt-1">
                Warning: white text on this color fails WCAG AA contrast (4.5:1) -- may be hard to read on buttons.
            </p>
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
            <p v-if="secondaryContrastFailsAA" class="text-warning small mb-0 mt-1">
                Warning: white text on this color fails WCAG AA contrast (4.5:1) -- may be hard to read on buttons.
            </p>
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
            <label for="logo" class="form-label">Logo</label>
            <img v-if="assetUrls.logo" :src="assetUrls.logo" alt="Current logo" class="d-block mb-2" height="48" />
            <input
                id="logo"
                type="file"
                class="form-control"
                accept="image/png,image/jpeg"
                :class="{ 'is-invalid': form.errors.logo }"
                @change="onFileChange('logo', $event)"
            />
            <div v-if="form.errors.logo" class="invalid-feedback">{{ form.errors.logo }}</div>
        </div>

        <div class="col-12">
            <label for="favicon" class="form-label">Favicon</label>
            <img
                v-if="assetUrls.favicon"
                :src="assetUrls.favicon"
                alt="Current favicon"
                class="d-block mb-2"
                height="24"
            />
            <input
                id="favicon"
                type="file"
                class="form-control"
                accept="image/png,image/x-icon"
                :class="{ 'is-invalid': form.errors.favicon }"
                @change="onFileChange('favicon', $event)"
            />
            <div v-if="form.errors.favicon" class="invalid-feedback">{{ form.errors.favicon }}</div>
        </div>

        <div class="col-12">
            <label for="login_background" class="form-label">Login background</label>
            <img
                v-if="assetUrls.login_background"
                :src="assetUrls.login_background"
                alt="Current login background"
                class="d-block mb-2"
                style="max-width: 100%; max-height: 8rem"
            />
            <input
                id="login_background"
                type="file"
                class="form-control"
                accept="image/png,image/jpeg"
                :class="{ 'is-invalid': form.errors.login_background }"
                @change="onFileChange('login_background', $event)"
            />
            <div v-if="form.errors.login_background" class="invalid-feedback">
                {{ form.errors.login_background }}
            </div>
        </div>

        <div class="col-12">
            <label for="custom_css" class="form-label">Custom CSS</label>
            <textarea
                id="custom_css"
                v-model="form.custom_css"
                class="form-control font-monospace"
                :class="{ 'is-invalid': form.errors.custom_css }"
                rows="6"
                maxlength="10000"
                placeholder=".btn { border-radius: 0; }"
            ></textarea>
            <div class="form-text">Advanced. Max 10,000 characters. Applied after all other theme settings.</div>
            <div v-if="form.errors.custom_css" class="invalid-feedback d-block">{{ form.errors.custom_css }}</div>
        </div>

        <div class="col-12">
            <label for="sender_name" class="form-label">Email sender name</label>
            <input
                id="sender_name"
                v-model="form.sender_name"
                type="text"
                class="form-control"
                :class="{ 'is-invalid': form.errors.sender_name }"
                placeholder="Your organization name"
            />
            <div class="form-text">Shown as the sender name on emails. The sending address itself stays fixed.</div>
            <div v-if="form.errors.sender_name" class="invalid-feedback">{{ form.errors.sender_name }}</div>
        </div>

        <div class="col-12">
            <label for="reply_to_email" class="form-label">Reply-to email</label>
            <input
                id="reply_to_email"
                v-model="form.reply_to_email"
                type="email"
                class="form-control"
                :class="{ 'is-invalid': form.errors.reply_to_email }"
                placeholder="support@yourdomain.example"
            />
            <div class="form-text">Where replies to your emails should land.</div>
            <div v-if="form.errors.reply_to_email" class="invalid-feedback">{{ form.errors.reply_to_email }}</div>
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

    <!--
        Live preview only: the browser applies whatever is typed here to
        the current document immediately, same as the color/font previews
        above. This is not a security boundary -- the server-side
        SafeCustomCss rule (and ThemeCssGenerator's defense-in-depth
        re-check) are what actually gate what gets persisted and served
        back to other visitors.
    -->
    <style>
        {{ form.custom_css }}
    </style>
</template>
