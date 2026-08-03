<script setup lang="ts">
import { router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const page = usePage();

const impersonation = computed(() => page.props.impersonation);

function stop() {
    router.delete('/impersonate');
}
</script>

<template>
    <div
        v-if="impersonation"
        class="alert alert-warning rounded-0 mb-0 d-flex justify-content-between align-items-center"
    >
        <i18n-t keypath="components.impersonationBanner.notice" tag="span">
            <template #impersonator
                ><strong>{{ impersonation.impersonatorName }}</strong></template
            >
            <template #target
                ><strong>{{ impersonation.targetName }}</strong></template
            >
        </i18n-t>
        <button type="button" class="btn btn-sm btn-outline-dark" @click="stop">
            {{ t('components.impersonationBanner.stop') }}
        </button>
    </div>
</template>
