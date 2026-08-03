<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, useForm } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';

defineOptions({ layout: AppLayout });

const { t } = useI18n();

const props = defineProps<{
    type: string;
    label: string;
    placeholders: Record<string, string>;
    defaultSubject: string;
    override: { data: { subject: string; body_markdown: string } } | null;
    preview: { subject: string; bodyHtml: string | null };
}>();

const form = useForm({
    subject: props.override?.data.subject ?? '',
    body_markdown: props.override?.data.body_markdown ?? '',
});

function submit() {
    form.put(`/settings/email-templates/${props.type}`);
}

function resetToDefault() {
    form.subject = '';
    form.body_markdown = '';
    submit();
}

function placeholderToken(token: string): string {
    return `{{ ${token} }}`;
}
</script>

<template>
    <p class="mb-1">
        <Link href="/settings/email-templates" class="text-muted small">{{
            t('settings.emailTemplates.edit.back')
        }}</Link>
    </p>
    <h1 class="h4 mb-4">{{ label }}</h1>

    <div class="row g-4">
        <div class="col-12 col-lg-7">
            <form @submit.prevent="submit">
                <div class="mb-3">
                    <label for="subject" class="form-label">{{ t('settings.emailTemplates.edit.subject') }}</label>
                    <input
                        id="subject"
                        v-model="form.subject"
                        type="text"
                        class="form-control"
                        :class="{ 'is-invalid': form.errors.subject }"
                        :placeholder="defaultSubject"
                    />
                    <div v-if="form.errors.subject" class="invalid-feedback">{{ form.errors.subject }}</div>
                </div>

                <div class="mb-3">
                    <label for="body_markdown" class="form-label">{{ t('settings.emailTemplates.edit.body') }}</label>
                    <textarea
                        id="body_markdown"
                        v-model="form.body_markdown"
                        class="form-control font-monospace"
                        :class="{ 'is-invalid': form.errors.body_markdown }"
                        rows="12"
                        maxlength="20000"
                    ></textarea>
                    <div v-if="form.errors.body_markdown" class="invalid-feedback d-block">
                        {{ form.errors.body_markdown }}
                    </div>
                </div>

                <button type="submit" class="btn btn-primary" :disabled="form.processing">
                    {{ t('settings.emailTemplates.edit.save') }}
                </button>
                <button
                    v-if="override !== null"
                    type="button"
                    class="btn btn-outline-secondary ms-2"
                    :disabled="form.processing"
                    @click="resetToDefault"
                >
                    {{ t('settings.emailTemplates.edit.resetToDefault') }}
                </button>
            </form>

            <div class="mt-4">
                <p class="text-muted small mb-2">{{ t('settings.emailTemplates.edit.availablePlaceholders') }}</p>
                <ul class="list-unstyled small">
                    <li v-for="(description, token) in placeholders" :key="token">
                        <code>{{ placeholderToken(token) }}</code> &mdash; {{ description }}
                    </li>
                </ul>
            </div>
        </div>

        <div class="col-12 col-lg-5">
            <p class="text-muted small mb-2">{{ t('settings.emailTemplates.edit.preview') }}</p>
            <div class="border rounded p-3">
                <p class="fw-semibold mb-2">{{ preview.subject }}</p>
                <!-- preview.bodyHtml is server-rendered by App\Services\Mail\SafeMarkdownRenderer
                     (CommonMark with html_input=>escape) -- already-sanitized, not raw user input. -->
                <!-- eslint-disable-next-line vue/no-v-html -->
                <div v-if="preview.bodyHtml" v-html="preview.bodyHtml"></div>
                <p v-else class="text-muted small mb-0">{{ t('settings.emailTemplates.edit.usingDefault') }}</p>
            </div>
        </div>
    </div>
</template>
