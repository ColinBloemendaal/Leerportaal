<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const page = usePage();
const footer = computed(() => page.props.footer);

const hasContent = computed(
    () =>
        footer.value.text !== null ||
        footer.value.supportEmail !== null ||
        footer.value.termsUrl !== null ||
        footer.value.privacyUrl !== null,
);
</script>

<template>
    <footer v-if="hasContent" class="text-center text-muted small py-3">
        <p v-if="footer.text" class="mb-1">{{ footer.text }}</p>
        <p class="mb-0">
            <a v-if="footer.supportEmail" :href="`mailto:${footer.supportEmail}`" class="text-muted me-3">
                {{ footer.supportEmail }}
            </a>
            <a v-if="footer.termsUrl" :href="footer.termsUrl" class="text-muted me-3" target="_blank" rel="noopener">
                {{ t('components.appFooter.terms') }}
            </a>
            <a v-if="footer.privacyUrl" :href="footer.privacyUrl" class="text-muted" target="_blank" rel="noopener">
                {{ t('components.appFooter.privacy') }}
            </a>
        </p>
    </footer>
</template>
