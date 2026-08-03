<script setup lang="ts">
import type { TranslatableValue } from '@/types/translatable';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();

const props = defineProps<{
    label: string;
    availableLocales: string[];
    multiline?: boolean;
    id?: string;
}>();

const model = defineModel<TranslatableValue>({ required: true });

const activeLocale = ref(props.availableLocales[0] ?? '');

// A computed get/set pair, not v-model bound straight to model[activeLocale]:
// mutating a nested property of a single object model doesn't reliably
// trigger defineModel's emit, only reassigning the whole ref does -- same
// reasoning as the block Editor components.
const currentValue = computed<string>({
    get: () => model.value[activeLocale.value] ?? '',
    set: (value: string) => {
        model.value = { ...model.value, [activeLocale.value]: value };
    },
});

const fieldId = computed(() => props.id ?? `locale-field-${props.label.toLowerCase().replace(/\s+/g, '-')}`);
</script>

<template>
    <div class="mb-3">
        <div class="d-flex justify-content-between align-items-center mb-1">
            <label :for="fieldId" class="form-label mb-0">{{ label }}</label>
            <select
                v-if="availableLocales.length > 1"
                v-model="activeLocale"
                class="form-select form-select-sm w-auto"
                :aria-label="t('components.localeField.languageFor', { label })"
            >
                <option v-for="locale in availableLocales" :key="locale" :value="locale">
                    {{ locale.toUpperCase() }}
                </option>
            </select>
        </div>
        <textarea v-if="multiline" :id="fieldId" v-model="currentValue" class="form-control" rows="4"></textarea>
        <input v-else :id="fieldId" v-model="currentValue" type="text" class="form-control" />
    </div>
</template>
