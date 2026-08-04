<script setup lang="ts">
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';

defineOptions({ layout: AppLayout });

const props = defineProps<{
    document: string;
    currentVersion: string;
    acceptedVersion: string | null;
    acceptedAt: string | null;
    needsAcceptance: boolean;
}>();

const { t } = useI18n();
const accepting = ref(false);

function accept() {
    accepting.value = true;
    router.post('/settings/dpa/accept', {}, { onFinish: () => (accepting.value = false) });
}
</script>

<template>
    <h1 class="h4 mb-3">{{ t('settings.dpa.title') }}</h1>

    <div v-if="needsAcceptance" class="alert alert-warning">
        {{
            acceptedVersion
                ? t('settings.dpa.staleNotice', { version: currentVersion })
                : t('settings.dpa.neverAccepted')
        }}
    </div>
    <div v-else class="alert alert-success">
        {{ t('settings.dpa.acceptedNotice', { date: acceptedAt }) }}
    </div>

    <pre class="dpa-document border rounded p-3 bg-body-tertiary">{{ props.document }}</pre>

    <button v-if="needsAcceptance" type="button" class="btn btn-primary mb-3" :disabled="accepting" @click="accept">
        {{ t('settings.dpa.accept') }}
    </button>

    <p>
        <Link href="/settings/subprocessors">{{ t('settings.dpa.viewSubprocessors') }}</Link>
    </p>
</template>

<style scoped>
/* Bootstrap has no utility for preserving newlines while still wrapping
   long lines -- the plain <pre> default (white-space: pre) would
   otherwise force horizontal scrolling on this document's long lines. */
.dpa-document {
    white-space: pre-wrap;
}
</style>
