<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import { meetsWcagAA } from '@/lib/contrast';
import { useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
import { useI18n } from 'vue-i18n';

defineOptions({ layout: AppLayout });

const { t } = useI18n();

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
            footer_text: string | null;
            support_email: string | null;
            terms_url: string | null;
            privacy_url: string | null;
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
    footer_text: props.theme.data.footer_text ?? '',
    support_email: props.theme.data.support_email ?? '',
    terms_url: props.theme.data.terms_url ?? '',
    privacy_url: props.theme.data.privacy_url ?? '',
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
    <h1 class="h4 mb-4">{{ t('settings.theme.title') }}</h1>

    <form class="row g-3" style="max-width: 32rem" @submit.prevent="submit">
        <div class="col-12">
            <label for="primary_color" class="form-label">{{ t('settings.theme.primaryColor') }}</label>
            <div class="input-group">
                <input
                    id="primary_color"
                    v-model="form.primary_color"
                    type="color"
                    class="form-control form-control-color"
                    :title="t('settings.theme.primaryColor')"
                />
                <input
                    v-model="form.primary_color"
                    type="text"
                    class="form-control"
                    :class="{ 'is-invalid': form.errors.primary_color }"
                    :aria-label="`${t('settings.theme.primaryColor')} hex value`"
                />
            </div>
            <div v-if="form.errors.primary_color" class="invalid-feedback d-block">
                {{ form.errors.primary_color }}
            </div>
            <p v-if="primaryContrastFailsAA" class="text-warning small mb-0 mt-1">
                {{ t('settings.theme.contrastWarning') }}
            </p>
        </div>

        <div class="col-12">
            <label for="secondary_color" class="form-label">{{ t('settings.theme.secondaryColor') }}</label>
            <div class="input-group">
                <input
                    id="secondary_color"
                    v-model="form.secondary_color"
                    type="color"
                    class="form-control form-control-color"
                    :title="t('settings.theme.secondaryColor')"
                />
                <input
                    v-model="form.secondary_color"
                    type="text"
                    class="form-control"
                    :class="{ 'is-invalid': form.errors.secondary_color }"
                    :aria-label="`${t('settings.theme.secondaryColor')} hex value`"
                />
            </div>
            <div v-if="form.errors.secondary_color" class="invalid-feedback d-block">
                {{ form.errors.secondary_color }}
            </div>
            <p v-if="secondaryContrastFailsAA" class="text-warning small mb-0 mt-1">
                {{ t('settings.theme.contrastWarning') }}
            </p>
        </div>

        <div class="col-12">
            <label for="accent_color" class="form-label">{{ t('settings.theme.accentColor') }}</label>
            <div class="input-group">
                <input
                    id="accent_color"
                    v-model="form.accent_color"
                    type="color"
                    class="form-control form-control-color"
                    :title="t('settings.theme.accentColor')"
                />
                <input
                    v-model="form.accent_color"
                    type="text"
                    class="form-control"
                    :class="{ 'is-invalid': form.errors.accent_color }"
                    :aria-label="`${t('settings.theme.accentColor')} hex value`"
                />
            </div>
            <div v-if="form.errors.accent_color" class="invalid-feedback d-block">{{ form.errors.accent_color }}</div>
        </div>

        <div class="col-12">
            <label for="font_family" class="form-label">{{ t('settings.theme.fontFamily') }}</label>
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
            <label for="logo" class="form-label">{{ t('settings.theme.logo') }}</label>
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
            <label for="favicon" class="form-label">{{ t('settings.theme.favicon') }}</label>
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
            <label for="login_background" class="form-label">{{ t('settings.theme.loginBackground') }}</label>
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
            <label for="custom_css" class="form-label">{{ t('settings.theme.customCss') }}</label>
            <textarea
                id="custom_css"
                v-model="form.custom_css"
                class="form-control font-monospace"
                :class="{ 'is-invalid': form.errors.custom_css }"
                rows="6"
                maxlength="10000"
                placeholder=".btn { border-radius: 0; }"
            ></textarea>
            <div class="form-text">{{ t('settings.theme.customCssHelp') }}</div>
            <div v-if="form.errors.custom_css" class="invalid-feedback d-block">{{ form.errors.custom_css }}</div>
        </div>

        <div class="col-12">
            <label for="sender_name" class="form-label">{{ t('settings.theme.senderName') }}</label>
            <input
                id="sender_name"
                v-model="form.sender_name"
                type="text"
                class="form-control"
                :class="{ 'is-invalid': form.errors.sender_name }"
                placeholder="Your organization name"
            />
            <div class="form-text">{{ t('settings.theme.senderNameHelp') }}</div>
            <div v-if="form.errors.sender_name" class="invalid-feedback">{{ form.errors.sender_name }}</div>
        </div>

        <div class="col-12">
            <label for="reply_to_email" class="form-label">{{ t('settings.theme.replyToEmail') }}</label>
            <input
                id="reply_to_email"
                v-model="form.reply_to_email"
                type="email"
                class="form-control"
                :class="{ 'is-invalid': form.errors.reply_to_email }"
                placeholder="support@yourdomain.example"
            />
            <div class="form-text">{{ t('settings.theme.replyToEmailHelp') }}</div>
            <div v-if="form.errors.reply_to_email" class="invalid-feedback">{{ form.errors.reply_to_email }}</div>
        </div>

        <div class="col-12">
            <label for="footer_text" class="form-label">{{ t('settings.theme.footerText') }}</label>
            <textarea
                id="footer_text"
                v-model="form.footer_text"
                class="form-control"
                :class="{ 'is-invalid': form.errors.footer_text }"
                rows="2"
                maxlength="1000"
                placeholder="© Your organization. All rights reserved."
            ></textarea>
            <div class="form-text">{{ t('settings.theme.footerTextHelp') }}</div>
            <div v-if="form.errors.footer_text" class="invalid-feedback d-block">{{ form.errors.footer_text }}</div>
        </div>

        <div class="col-12">
            <label for="support_email" class="form-label">{{ t('settings.theme.supportEmail') }}</label>
            <input
                id="support_email"
                v-model="form.support_email"
                type="email"
                class="form-control"
                :class="{ 'is-invalid': form.errors.support_email }"
                placeholder="support@yourdomain.example"
            />
            <div v-if="form.errors.support_email" class="invalid-feedback">{{ form.errors.support_email }}</div>
        </div>

        <div class="col-12">
            <label for="terms_url" class="form-label">{{ t('settings.theme.termsUrl') }}</label>
            <input
                id="terms_url"
                v-model="form.terms_url"
                type="url"
                class="form-control"
                :class="{ 'is-invalid': form.errors.terms_url }"
                placeholder="https://yourdomain.example/terms"
            />
            <div v-if="form.errors.terms_url" class="invalid-feedback">{{ form.errors.terms_url }}</div>
        </div>

        <div class="col-12">
            <label for="privacy_url" class="form-label">{{ t('settings.theme.privacyUrl') }}</label>
            <input
                id="privacy_url"
                v-model="form.privacy_url"
                type="url"
                class="form-control"
                :class="{ 'is-invalid': form.errors.privacy_url }"
                placeholder="https://yourdomain.example/privacy"
            />
            <div v-if="form.errors.privacy_url" class="invalid-feedback">{{ form.errors.privacy_url }}</div>
        </div>

        <div class="col-12">
            <button type="submit" class="btn btn-primary" :disabled="form.processing">
                {{ t('settings.theme.save') }}
            </button>
        </div>
    </form>

    <div class="mt-4 p-3 border rounded" style="max-width: 32rem">
        <p class="text-muted small mb-2">{{ t('settings.theme.livePreview') }}</p>
        <button type="button" class="btn btn-primary me-2">{{ t('settings.theme.previewButton') }}</button>
        <a href="#" class="link-primary">{{ t('settings.theme.previewLink') }}</a>
    </div>

    <!--
        Live preview only: the browser applies whatever is typed here to
        the current document immediately, same as the color/font previews
        above. This is not a security boundary -- the server-side
        RejectsUnsafeMarkup rule (and ThemeCssGenerator's defense-in-depth
        re-check) are what actually gate what gets persisted and served
        back to other visitors.
    -->
    <style>
        {{ form.custom_css }}
    </style>
</template>
