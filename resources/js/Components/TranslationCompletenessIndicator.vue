<script setup lang="ts">
import { useI18n } from 'vue-i18n';

defineProps<{
    locales: { locale: string; complete: boolean }[];
}>();

const { t } = useI18n();
</script>

<template>
    <div class="d-flex gap-2 flex-wrap">
        <span
            v-for="item in locales"
            :key="item.locale"
            class="badge"
            :class="item.complete ? 'text-bg-success' : 'text-bg-warning'"
        >
            {{ item.locale.toUpperCase() }}:
            {{
                item.complete
                    ? t('components.translationCompleteness.complete')
                    : t('components.translationCompleteness.incomplete')
            }}
        </span>
    </div>
</template>
